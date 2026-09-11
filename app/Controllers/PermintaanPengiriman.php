<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ModelBarangKeluar;
use App\Models\Modelbarang;
use App\Models\Modelberat;
use App\Models\ModelDataStok;
use App\Models\ModelDetailBarangKeluar;
use App\Models\ModelDetailPermintaanPengiriman;
use App\Models\Modeldetailpo;
use App\Models\Modelgudang;
use App\Models\Modeloutstand;
use App\Models\ModelPelanggan;
use App\Models\ModelPermintaanPengiriman;
use App\Models\Modelpo;
use App\Models\ModelRencanaPengiriman;
use App\Models\Modelstok;
use App\Models\ModelTempPermintaanPengiriman;
use App\Models\Modeluser;
use App\Libraries\NoDoChecker;

class PermintaanPengiriman extends BaseController
{
    /**
     * Kolom buat fitur "qty migrasi jadi surat jalan asli": rencana_pengiriman.sumber
     * nandain 1 baris rencana itu dari qty baru ('baru') atau dari qty migrasi
     * PO yang belum ada surat jalannya ('migrasi'); detail_barangkeluar.dari_migrasi
     * nandain baris hasil kirim itu perlu potong stok (0) atau tidak (1) --
     * dipakai lagi pas hapus transaksi biar tau harus balikin stok atau
     * balikin detkirim_awal.
     */
    private function ensureMigrasiPengirimanColumns(): void
    {
        $db = \Config\Database::connect();
        $forge = \Config\Database::forge();

        if (!$db->fieldExists('sumber', 'rencana_pengiriman')) {
            $forge->addColumn('rencana_pengiriman', [
                'sumber' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'null' => false,
                    'default' => 'baru',
                    'after' => 'qty',
                ],
            ]);
        }

        if (!$db->fieldExists('dari_migrasi', 'detail_barangkeluar')) {
            $forge->addColumn('detail_barangkeluar', [
                'dari_migrasi' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'null' => false,
                    'default' => 0,
                    'after' => 'detsubtotal',
                ],
            ]);
        }
    }

    public function data()
    {
        return redirect()->to('/barangkeluar/data#permintaan');
    }

    public function listData()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $db = \Config\Database::connect();
        $draw = (int) $this->request->getPost('draw');
        $start = max(0, (int) $this->request->getPost('start'));
        $length = max(1, (int) $this->request->getPost('length'));
        $searchData = $this->request->getPost('search');
        $search = trim((string) ($searchData['value'] ?? ''));

        $baseBuilder = static function () use ($db) {
            return $db->table('permintaan_pengiriman')
                ->select(
                    "permintaan_pengiriman.*, users.usernama,
                    (SELECT COALESCE(SUM(rp.qty), 0) FROM rencana_pengiriman rp
                     WHERE rp.permintaan_id = permintaan_pengiriman.id AND rp.status = 1) AS qty_terkirim",
                    false
                )
                ->join('users', 'users.id = permintaan_pengiriman.iduser', 'left')
                ->groupStart()
                ->where('permintaan_pengiriman.keterangan !=', 'Kirim langsung')
                ->orWhere('permintaan_pengiriman.keterangan IS NULL', null, false)
                // Sesi "Kirim Langsung" yang belum sempat punya rencana pengiriman
                // sama sekali (misal batal/gagal di tengah jalan) itu murni
                // masih berupa permintaan -- tampilkan di sini, bukan di List Pengiriman.
                ->orWhere(
                    'NOT EXISTS (SELECT 1 FROM rencana_pengiriman rp WHERE rp.permintaan_id = permintaan_pengiriman.id)',
                    null,
                    false
                )
                ->groupEnd();
        };

        $recordsTotal = $baseBuilder()->countAllResults();
        $filteredBuilder = $baseBuilder();

        if ($search !== '') {
            $filteredBuilder->like('users.usernama', $search);
        }

        $recordsFiltered = $filteredBuilder->countAllResults(false);
        $order = $this->request->getPost('order');
        $orderColumns = [
            1 => 'permintaan_pengiriman.tanggal',
            2 => 'users.usernama',
            3 => 'permintaan_pengiriman.total_produk',
            4 => 'qty_terkirim',
        ];

        $orderColumn = (int) ($order[0]['column'] ?? 1);
        $orderDirection = strtolower((string) ($order[0]['dir'] ?? 'desc')) === 'asc' ? 'ASC' : 'DESC';
        $filteredBuilder->orderBy($orderColumns[$orderColumn] ?? 'permintaan_pengiriman.tanggal', $orderDirection);

        $rows = $filteredBuilder
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        $data = [];
        foreach ($rows as $index => $row) {
            // Status permintaan dibandingin dari total qty yang udah kekirim
            // (rencana_pengiriman.status = 1) vs total_produk yang diminta --
            // bukan dari jumlah rencana yang ada, soalnya rencana yang
            // dibuat aja bisa masih sebagian dari total permintaan.
            $totalProduk = (float) $row['total_produk'];
            $qtyTerkirim = (float) ($row['qty_terkirim'] ?? 0);

            if ($qtyTerkirim <= 0) {
                $statusBadge = '<span class="badge badge-warning">Menunggu</span>';
            } elseif ($qtyTerkirim >= $totalProduk) {
                $statusBadge = '<span class="badge badge-success">Terkirim</span>';
            } else {
                $statusBadge = '<span class="badge badge-info">Sebagian</span>';
            }

            $data[] = [
                'nomor' => $start + $index + 1,
                'tanggal' => date('d-m-Y', strtotime($row['tanggal'])),
                'usernama' => $row['usernama'] ?? '-',
                'total_produk' => number_format($row['total_produk'], 0, ',', '.'),
                'status' => $statusBadge,
                'aksi' => '<button class="btn btn-sm btn-primary" title="Edit / Proses Permintaan" onclick="proses(\'' . sha1($row['id']) . '\')"><i class="fa fa-edit"></i></button>&nbsp;'
                    . '<button class="btn btn-sm btn-primary" title="Print" onclick="cetak(\'' . sha1($row['id']) . '\')"><i class="fa fa-print"></i></button>&nbsp;'
                    . '<button class="btn btn-sm btn-danger" title="Hapus" onclick="hapusPengiriman(' . $row['id'] . ')"><i class="fa fa-trash-alt"></i></button>',
            ];
        }

        return $this->response->setJSON([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function input()
    {
        $currentUser = (new Modeluser())
            ->select('id, userid, usernama')
            ->where('userid', (string) session()->get('userid'))
            ->first();

        if (!$currentUser) {
            return redirect()->to('/login/logout')
                ->with('error', 'Sesi user tidak valid. Silakan login kembali.');
        }

        $produk = (new Modelbarang())
            ->select('brgkode, brgnama')
            ->orderBy('brgkode', 'ASC')
            ->findAll();

        return view('permintaanpengiriman/forminput', [
            'token' => bin2hex(random_bytes(16)),
            'currentUser' => $currentUser,
            'produk' => $produk,
        ]);
    }

    public function modalCariBarang()
    {
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'data' => view('permintaanpengiriman/modalcaribarang'),
            ]);
        }
    }

    public function listDataBarang()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $request = \Config\Services::request();
        $datamodel = new ModelDataStok($request);
        $lists = $datamodel->get_datatables();
        $data = [];
        $no = (int) $request->getPost('start');

        foreach ($lists as $list) {
            $no++;

            $stokGudang1 = $datamodel->where('kodebarang', $list->kodebarang)->where('gudang', 1)->first();
            $stokGudang2 = $datamodel->where('kodebarang', $list->kodebarang)->where('gudang', 2)->first();

            $stokGudang1Value = isset($stokGudang1['stok']) ? (int) $stokGudang1['stok'] : 0;
            $stokGudang2Value = isset($stokGudang2['stok']) ? (int) $stokGudang2['stok'] : 0;
            $outstanding = $this->hitungOutstandingProduk((string) $list->kodebarang);
            $tombolPilih = "<button type=\"button\" class=\"btn btn-sm btn-info\" onclick=\"pilih('" . $list->kodebarang . "')\">Pilih</button>";

            $data[] = [
                $no,
                $list->kodebarang,
                $list->namabarang,
                number_format($stokGudang1Value, 0, ',', '.'),
                number_format($stokGudang2Value, 0, ',', '.'),
                number_format($outstanding, 0, ',', '.'),
                $tombolPilih,
            ];
        }

        return $this->response->setJSON([
            'draw' => $request->getPost('draw'),
            'recordsTotal' => $datamodel->count_all(),
            'recordsFiltered' => $datamodel->count_filtered(),
            'data' => $data,
        ]);
    }

    public function ambilDataBarang()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $kode = (string) $this->request->getPost('kodebarang');
        $barang = (new Modelbarang())->find($kode);
        $berat = (new Modelberat())->find($kode);

        if (!$barang) {
            return $this->response->setJSON(['error' => 'Produk tidak ditemukan']);
        }

        return $this->response->setJSON([
            'sukses' => [
                'namabarang' => $barang['brgnama'],
                'stok' => (new Modelstok())->getTotalStokByKodeBarang($kode) ?? 0,
                'berat' => $berat['berat'] ?? 0,
            ],
        ]);
    }

    private function hitungOutstandingProduk(string $kodeProduk): int
    {
        $db = \Config\Database::connect();
        $totalPo = (int) ($db->table('detail_po')
            ->selectSum('detqty', 'qty')
            ->where('detkodebrg', $kodeProduk)
            ->whereNotIn('detidpel', [1, 2])
            ->get()
            ->getRowArray()['qty'] ?? 0);

        $totalKirim = (int) ($db->table('detail_barangkeluar')
            ->selectSum('detjml', 'qty')
            ->where('detbrgkode', $kodeProduk)
            ->whereNotIn('detidpel', [1, 2])
            ->get()
            ->getRowArray()['qty'] ?? 0);

        return $totalPo - $totalKirim;
    }

    public function simpanItem()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $token = (string) $this->request->getPost('token');
        $kode = trim((string) $this->request->getPost('kodebarang'));
        $nama = trim((string) $this->request->getPost('namabarang'));
        $qty = (int) $this->request->getPost('qty');
        $berat = (float) $this->request->getPost('berat');

        if ($token === '' || $kode === '' || $nama === '' || $qty <= 0) {
            return $this->response->setJSON(['error' => 'Produk dan Qty harus diisi dengan benar']);
        }

        $model = new ModelTempPermintaanPengiriman();
        $existing = $model->where('token', $token)->where('kode_produk', $kode)->first();

        if ($existing) {
            $model->update($existing['id'], ['qty' => (int) $existing['qty'] + $qty]);
        } else {
            $model->insert([
                'token' => $token,
                'kode_produk' => $kode,
                'nama_produk' => $nama,
                'berat' => $berat,
                'qty' => $qty,
            ]);
        }

        return $this->response->setJSON(['sukses' => 'Produk ditambahkan']);
    }

    public function tampilTemp()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $token = (string) $this->request->getPost('token');
        return $this->response->setJSON([
            'data' => view('permintaanpengiriman/datatemp', [
                'items' => (new ModelTempPermintaanPengiriman())->where('token', $token)->orderBy('id')->findAll(),
            ]),
        ]);
    }

    public function hapusItem()
    {
        if ($this->request->isAJAX()) {
            (new ModelTempPermintaanPengiriman())->delete($this->request->getPost('id'));
            return $this->response->setJSON(['sukses' => 'Produk dihapus']);
        }
    }

    public function selesai()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $token = (string) $this->request->getPost('token');
        $tempModel = new ModelTempPermintaanPengiriman();
        $items = $tempModel->where('token', $token)->findAll();

        // Identitas pembuat selalu berasal dari session, bukan dari input browser.
        $currentUser = (new Modeluser())
            ->select('id, userid, usernama')
            ->where('userid', (string) session()->get('userid'))
            ->first();

        if (!$currentUser) {
            return $this->response->setJSON([
                'error' => 'Sesi user tidak valid. Silakan login kembali.',
            ])->setStatusCode(401);
        }

        $rules = [
            'tanggal' => 'required|valid_date',
            'keterangan' => 'permit_empty|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON(['error' => strip_tags($this->validator->listErrors())]);
        }

        if (!$items) {
            return $this->response->setJSON(['error' => 'Tambahkan minimal satu produk']);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $header = new ModelPermintaanPengiriman();
        $header->insert([
            'tanggal' => $this->request->getPost('tanggal'),
            'iduser' => $currentUser['id'],
            'total_produk' => array_sum(array_column($items, 'qty')),
            'jenis_pengiriman' => '',
            'pic_pengirim' => '',
            'nominal' => 0,
            'keterangan' => trim((string) $this->request->getPost('keterangan')),
        ]);

        $permintaanId = $header->getInsertID();
        $detail = [];
        foreach ($items as $item) {
            $detail[] = [
                'permintaan_id' => $permintaanId,
                'kode_produk' => $item['kode_produk'],
                'nama_produk' => $item['nama_produk'],
                'berat' => $item['berat'],
                'qty' => $item['qty'],
            ];
        }

        (new ModelDetailPermintaanPengiriman())->insertBatch($detail);
        $tempModel->where('token', $token)->delete();
        $db->transComplete();

        if (!$db->transStatus()) {
            return $this->response->setJSON(['error' => 'Permintaan gagal disimpan']);
        }

        return $this->response->setJSON(['sukses' => 'Permintaan Pengiriman berhasil disimpan']);
    }

    public function hapus()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $id = (int) $this->request->getPost('id');
        $db = \Config\Database::connect();
        $db->transStart();
        $db->table('rencana_pengiriman')->delete(['permintaan_id' => $id]);
        $db->table('detail_permintaan_pengiriman')->delete(['permintaan_id' => $id]);
        $db->table('permintaan_pengiriman')->delete(['id' => $id]);
        $db->transComplete();

        return $this->response->setJSON(['sukses' => 'Permintaan Pengiriman dihapus']);
    }

    public function proses(string $hash)
    {
        $headerQuery = (new ModelPermintaanPengiriman())->cekHash($hash);
        if ($headerQuery->getNumRows() === 0) {
            return $this->response->setStatusCode(404, 'Data tidak ditemukan');
        }

        $header = $headerQuery->getRowArray();
        $db = \Config\Database::connect();
        $rencanaTerkirimSubquery = $db->table('rencana_pengiriman')
            ->distinct()
            ->select('detail_id, no_do, no_po, kode_produk')
            ->where('status', 1)
            ->where('no_do IS NOT NULL', null, false)
            ->where('no_do <>', '')
            ->where('no_po IS NOT NULL', null, false)
            ->where('no_po <>', '')
            ->getCompiledSelect();

        $details = $db
            ->table('detail_permintaan_pengiriman d')
            ->select('d.*, COALESCE(SUM(dk.detjml), 0) AS terkirim', false)
            ->select("(SELECT rp.no_po FROM rencana_pengiriman rp WHERE rp.detail_id = d.id AND rp.no_po IS NOT NULL AND rp.no_po <> '' ORDER BY rp.id DESC LIMIT 1) AS no_po_detail", false)
            ->select("COALESCE((SELECT rp.tanggal_po FROM rencana_pengiriman rp WHERE rp.detail_id = d.id AND rp.no_po IS NOT NULL AND rp.no_po <> '' ORDER BY rp.id DESC LIMIT 1), d.tanggal_po, (SELECT po.tglpo FROM rencana_pengiriman rp JOIN po ON po.nopo = rp.no_po WHERE rp.detail_id = d.id AND rp.no_po IS NOT NULL AND rp.no_po <> '' ORDER BY rp.id DESC LIMIT 1)) AS tanggal_po_detail", false)
            ->join("({$rencanaTerkirimSubquery}) rt", 'rt.detail_id = d.id', 'left', false)
            ->join('detail_barangkeluar dk', 'dk.detfaktur = rt.no_do AND dk.detpo = rt.no_po AND dk.detbrgkode = rt.kode_produk', 'left')
            ->where('d.permintaan_id', $header['id'])
            ->groupBy('d.id')
            ->get()
            ->getResultArray();

        foreach ($details as &$detail) {
            $noPoAktif = (string) ($detail['no_po_detail'] ?? '');
            $poList = $this->poListUntukProduk((string) $detail['kode_produk'], $noPoAktif);

            $poAktif = null;
            foreach ($poList as $po) {
                if ($po['nopo'] === $noPoAktif) {
                    $poAktif = $po;
                    break;
                }
            }

            $detail['po_list'] = $poList;
            $detail['pelnama_terpilih'] = $poAktif['pelnama'] ?? '-';
            $detail['idpel_terpilih'] = (int) ($poAktif['idpel'] ?? 0);
        }
        unset($detail);

        $produk = (new Modelbarang())
            ->select('brgkode, brgnama')
            ->orderBy('brgkode', 'ASC')
            ->findAll();

        return view('permintaanpengiriman/proses', [
            'header' => $header,
            'details' => $details,
            'gudang' => (new Modelgudang())->findAll(),
            'pelanggan' => (new ModelPelanggan())->orderBy('pelnama', 'ASC')->findAll(),
            'produk' => $produk,
            'rencana' => $this->rencanaData((int) $header['id']),
            'riwayat' => $this->riwayatPengirimanData((int) $header['id']),
        ]);
    }

    /**
     * Daftar PO yang masih punya sisa (detkurang > 0) untuk sebuah produk --
     * dipakai buat dropdown "No. PO" di halaman Proses Permintaan Pengiriman
     * (baik baris yang sudah ada maupun produk baru yang baru dicari).
     * $noPoTerpilihSaatIni tetap disertakan di daftar walau sisanya sudah
     * habis, supaya pilihan yang sudah ada sebelumnya gak hilang dari dropdown.
     */
    private function poListUntukProduk(string $kodeProduk, string $noPoTerpilihSaatIni = ''): array
    {
        $kodeProduk = trim($kodeProduk);
        if ($kodeProduk === '') {
            return [];
        }

        $db = \Config\Database::connect();
        $builder = static function () use ($db) {
            return $db->table('detail_po dp')
                ->select('dp.detnopo AS nopo, dp.detkurang AS sisa, po.tglpo, po.idpel, pl.pelnama', false)
                ->join('po', 'po.nopo = dp.detnopo')
                ->join('pelanggan pl', 'pl.pelid = po.idpel', 'left');
        };

        $daftar = $builder()
            ->where('dp.detkodebrg', $kodeProduk)
            ->where('dp.detkurang >', 0)
            ->orderBy('dp.detnopo', 'ASC')
            ->get()
            ->getResultArray();

        $noPoTerpilihSaatIni = trim($noPoTerpilihSaatIni);
        if ($noPoTerpilihSaatIni !== '' && !in_array($noPoTerpilihSaatIni, array_column($daftar, 'nopo'), true)) {
            $poLama = $builder()
                ->where('dp.detkodebrg', $kodeProduk)
                ->where('dp.detnopo', $noPoTerpilihSaatIni)
                ->get()
                ->getRowArray();

            if ($poLama) {
                array_unshift($daftar, $poLama);
            }
        }

        return $daftar;
    }

    public function poListProduk()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $kodeProduk = trim((string) $this->request->getPost('kode_produk'));

        return $this->response->setJSON([
            'sukses' => $this->poListUntukProduk($kodeProduk),
        ]);
    }

    public function cetak(string $hash)
    {
        $headerQuery = (new ModelPermintaanPengiriman())->cekHash($hash);
        if ($headerQuery->getNumRows() === 0) {
            return $this->response->setStatusCode(404, 'Data tidak ditemukan');
        }

        $header = $headerQuery->getRowArray();
        $po = trim((string) $this->request->getGet('po'));
        if ($po === '' || $po === 'semua') {
            return redirect()->to('/permintaanPengiriman/pilih-cetak/' . $hash);
        }

        $dataCetak = $this->dataCetakPermintaan((int) $header['id'], $po);
        if (!$dataCetak['ditemukan']) {
            return $this->response->setStatusCode(404, 'Data PO tidak ditemukan pada permintaan ini');
        }

        return view('permintaanpengiriman/cetak', [
            'header' => $header,
            'rows' => $dataCetak['rows'],
            'poDipilih' => $po,
            'namaPelanggan' => $dataCetak['namaPelanggan'],
            'tanggalPo' => $dataCetak['tanggalPo'],
            'totalQty' => $dataCetak['totalQty'],
            'totalTerkirim' => $dataCetak['totalTerkirim'],
            'totalOutstanding' => $dataCetak['totalOutstanding'],
        ]);
    }

    public function pilihCetak(string $hash)
    {
        $headerQuery = (new ModelPermintaanPengiriman())->cekHash($hash);
        if ($headerQuery->getNumRows() === 0) {
            return $this->response->setStatusCode(404, 'Data tidak ditemukan');
        }

        $header = $headerQuery->getRowArray();
        $poList = \Config\Database::connect()
            ->table('rencana_pengiriman r')
            ->select('r.no_po, MAX(p.pelnama) AS pelnama, COUNT(DISTINCT dk.detfaktur) AS jumlah_pengiriman', false)
            ->join('detail_permintaan_pengiriman d', 'd.id = r.detail_id')
            ->join('pelanggan p', 'p.pelid = d.idpel', 'left')
            ->join('detail_barangkeluar dk', 'dk.detfaktur = r.no_do AND dk.detpo = r.no_po AND dk.detbrgkode = r.kode_produk', 'left')
            ->where('r.permintaan_id', $header['id'])
            ->where('r.no_po IS NOT NULL', null, false)
            ->where('r.no_po <>', '')
            ->groupBy('r.no_po')
            ->orderBy('r.no_po', 'ASC')
            ->get()
            ->getResultArray();

        return view('permintaanpengiriman/pilihcetak', [
            'header' => $header,
            'hash' => $hash,
            'poList' => $poList,
        ]);
    }

    private function dataCetakPermintaan(int $permintaanId, string $poDipilih): array
    {
        $db = \Config\Database::connect();
        $relasiPo = $db->table('rencana_pengiriman')
            ->select('detail_id, no_po, SUM(qty) AS jumlah_po, MAX(tanggal_po) AS tanggal_po', false)
            ->where('permintaan_id', $permintaanId)
            ->where('no_po IS NOT NULL', null, false)
            ->where('no_po', $poDipilih)
            ->groupBy('detail_id, no_po');

        $relasiPoSql = $relasiPo->getCompiledSelect();
        $details = $db->table('detail_permintaan_pengiriman d')
            ->select('d.id, d.kode_produk, d.nama_produk, rel.jumlah_po AS jumlah, rel.no_po, p.pelnama, COALESCE(rel.tanggal_po, po.tglpo, d.tanggal_po) AS tanggal_po', false)
            ->join("({$relasiPoSql}) rel", 'rel.detail_id = d.id', 'inner', false)
            ->join('pelanggan p', 'p.pelid = d.idpel', 'left')
            ->join('po', 'po.nopo = rel.no_po', 'left')
            ->where('d.permintaan_id', $permintaanId)
            ->orderBy('d.id', 'ASC')
            ->get()
            ->getResultArray();

        if (!$details) {
            return [
                'ditemukan' => false,
                'rows' => [],
                'namaPelanggan' => '-',
                'tanggalPo' => null,
                'totalQty' => 0,
                'totalTerkirim' => 0,
                'totalOutstanding' => 0,
            ];
        }

        $relasiKirim = $db->table('rencana_pengiriman')
            ->distinct()
            ->select('detail_id, no_po, no_do, kode_produk')
            ->where('permintaan_id', $permintaanId)
            ->where('status', 1)
            ->where('no_po', $poDipilih)
            ->where('no_do IS NOT NULL', null, false)
            ->where('no_do <>', '');

        $relasiKirimSql = $relasiKirim->getCompiledSelect();
        $pengiriman = $db->table('detail_barangkeluar dk')
            ->select('rk.detail_id, rk.no_do, rk.kode_produk, MIN(dk.tgl) AS tanggal_pengiriman, COALESCE(SUM(dk.detjml), 0) AS terkirim', false)
            ->join("({$relasiKirimSql}) rk", 'dk.detfaktur = rk.no_do AND dk.detpo = rk.no_po AND dk.detbrgkode = rk.kode_produk', 'inner', false)
            ->groupBy('rk.detail_id, rk.no_do, rk.kode_produk')
            ->orderBy('tanggal_pengiriman', 'ASC')
            ->orderBy('rk.no_do', 'ASC')
            ->get()
            ->getResultArray();

        $namaProduk = [];
        foreach ($details as $detail) {
            $namaProduk[(int) $detail['id']] = $detail['nama_produk'];
        }

        $rows = [];
        foreach ($pengiriman as $kirim) {
            $rows[] = [
                'tanggal_pengiriman' => $kirim['tanggal_pengiriman'],
                'no_do' => $kirim['no_do'],
                'kode_produk' => $kirim['kode_produk'],
                'nama_produk' => $namaProduk[(int) $kirim['detail_id']] ?? '-',
                'terkirim' => (int) $kirim['terkirim'],
            ];
        }

        $namaPelanggan = array_values(array_unique(array_filter(array_column($details, 'pelnama'))));
        $totalQty = array_sum(array_map(static fn ($detail) => (int) $detail['jumlah'], $details));
        $totalTerkirim = array_sum(array_map(static fn ($row) => (int) $row['terkirim'], $rows));

        return [
            'ditemukan' => true,
            'rows' => $rows,
            'namaPelanggan' => $namaPelanggan ? implode(', ', $namaPelanggan) : '-',
            'tanggalPo' => $details[0]['tanggal_po'] ?? null,
            'totalQty' => $totalQty,
            'totalTerkirim' => $totalTerkirim,
            'totalOutstanding' => max(0, $totalQty - $totalTerkirim),
        ];
    }

    public function ambilStok()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $kode = (string) $this->request->getPost('kode_produk');
        $gudang = (int) $this->request->getPost('gudang_id');

        return $this->response->setJSON([
            'stok' => (new Modelstok())->getStokByGudang($kode, $gudang),
        ]);
    }

    /**
     * Dipakai dari "Input Pengiriman": user isi No. PO, sistem tunjukkan
     * item-item PO tersebut (beserta sisa qty yang belum terkirim) supaya
     * bisa langsung dipilih -- bukan cari produk bebas lepas dari PO.
     */
    public function itemPo()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $noPo = trim((string) $this->request->getPost('no_po'));
        if ($noPo === '') {
            return $this->response->setJSON(['error' => 'No. PO wajib diisi']);
        }

        $db = \Config\Database::connect();
        $po = $db->table('po p')
            ->select('p.nopo, p.tglpo, p.idpel, pl.pelnama')
            ->join('pelanggan pl', 'pl.pelid = p.idpel', 'left')
            ->where('p.nopo', $noPo)
            ->get()
            ->getRowArray();

        if (!$po) {
            return $this->response->setJSON([
                'error' => 'PO tidak ditemukan. Pastikan No. PO ini sudah tercatat lewat menu PO Masuk.',
            ]);
        }

        $items = $db->table('detail_po')
            ->select('id, detkodebrg AS kode_produk, namabarang AS nama_produk, detqty AS qty, COALESCE(detkirim_awal, 0) + COALESCE(detkirim, 0) AS terkirim, detkurang AS sisa, COALESCE(detkirim_awal, 0) AS migrasi', false)
            ->where('detnopo', $noPo)
            ->orderBy('detkodebrg', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'sukses' => [
                'nopo' => $po['nopo'],
                'tglpo' => $po['tglpo'],
                'idpel' => (int) $po['idpel'],
                'pelnama' => $po['pelnama'],
                'items' => $items,
            ],
        ]);
    }

    /**
     * Cek cepat dipanggil pas No. Surat Jalan di-blur di halaman Input
     * Pengiriman -- biar user langsung tau kalau nomornya sudah kepakai,
     * gak perlu nunggu sampai klik Simpan buat ketauan.
     */
    public function cekNoDo()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $noDo = trim((string) $this->request->getPost('no_do'));
        $permintaanId = (int) $this->request->getPost('permintaan_id');

        $duplikat = $this->cekNoDoDuplikat($noDo, $permintaanId);

        return $this->response->setJSON([
            'duplikat' => $duplikat !== null,
            'sumber' => $duplikat,
        ]);
    }

    public function simpanRencana()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $this->ensureMigrasiPengirimanColumns();

        $db = \Config\Database::connect();
        $db->transStart();

        $permintaanId = (int) $this->request->getPost('permintaan_id');
        $detailId = (int) $this->request->getPost('detail_id');
        $kodeProdukBaru = trim((string) $this->request->getPost('kode_produk_baru'));
        $qty = (int) $this->request->getPost('qty');
        $gudangId = (int) $this->request->getPost('gudang_id');
        $noDo = trim((string) $this->request->getPost('no_do'));
        $noBtb = trim((string) $this->request->getPost('no_btb'));
        $noPo = trim((string) $this->request->getPost('no_po'));
        $tanggalPo = trim((string) $this->request->getPost('tanggal_po'));
        $tanggalPengiriman = trim((string) $this->request->getPost('tanggal_pengiriman'));
        $idPelanggan = (int) $this->request->getPost('idpelanggan');
        $sumber = $this->request->getPost('sumber') === 'migrasi' ? 'migrasi' : 'baru';

        // Belum ada permintaan sama sekali (baru buka "Input Pengiriman") --
        // header-nya baru dibuat sekarang, pas user beneran menyimpan item
        // pertamanya. Supaya sekadar membuka halamannya tanpa isi apa-apa
        // tidak menyisakan data kosong di database.
        if ($permintaanId <= 0) {
            $currentUser = (new Modeluser())
                ->select('id, userid, usernama')
                ->where('userid', (string) session()->get('userid'))
                ->first();

            if (!$currentUser) {
                $db->transRollback();
                return $this->response->setJSON(['error' => 'Sesi user tidak valid. Silakan login kembali.'])->setStatusCode(401);
            }

            $headerModel = new ModelPermintaanPengiriman();
            $headerModel->insert([
                'tanggal' => date('Y-m-d'),
                'iduser' => $currentUser['id'],
                'total_produk' => 0,
                'jenis_pengiriman' => '',
                'pic_pengirim' => '',
                'nominal' => 0,
                'keterangan' => 'Kirim langsung',
            ]);
            $permintaanId = (int) $headerModel->getInsertID();
        }

        // Produk dipilih lewat "Cari Produk" (belum ada baris detail permintaan
        // untuk produk ini) -- buat/tambah dulu barisnya di sini, supaya "Kirim
        // Langsung" dan alur bertahap sama-sama lewat satu jalur ini.
        if ($detailId <= 0 && $kodeProdukBaru !== '') {
            if ($permintaanId <= 0 || $qty <= 0) {
                $db->transRollback();
                return $this->response->setJSON(['error' => 'Produk dan Qty wajib diisi dengan benar']);
            }

            $barang = (new Modelbarang())->find($kodeProdukBaru);
            if (!$barang) {
                $db->transRollback();
                return $this->response->setJSON(['error' => 'Produk tidak ditemukan']);
            }

            $berat = (new Modelberat())->find($kodeProdukBaru);
            if (!$berat && !$this->produkTanpaBerat($kodeProdukBaru)) {
                $db->transRollback();
                return $this->response->setJSON(['error' => 'Berat/Ukuran Bersih untuk produk ini belum diisi']);
            }

            $detailModel = new ModelDetailPermintaanPengiriman();
            $existing = $detailModel
                ->where('permintaan_id', $permintaanId)
                ->where('kode_produk', $kodeProdukBaru)
                ->first();

            if ($existing) {
                $detailId = (int) $existing['id'];
                $detailModel->update($detailId, ['qty' => (int) $existing['qty'] + $qty]);
            } else {
                $detailModel->insert([
                    'permintaan_id' => $permintaanId,
                    'kode_produk' => $kodeProdukBaru,
                    'nama_produk' => $barang['brgnama'],
                    'berat' => (float) ($berat['berat'] ?? 0),
                    'qty' => $qty,
                ]);
                $detailId = (int) $detailModel->getInsertID();
            }

            $totalProduk = (int) ($detailModel
                ->selectSum('qty')
                ->where('permintaan_id', $permintaanId)
                ->first()['qty'] ?? 0);
            (new ModelPermintaanPengiriman())->update($permintaanId, ['total_produk' => $totalProduk]);
        }

        $detail = (new ModelDetailPermintaanPengiriman())->find($detailId);

        if (!$detail || $qty <= 0 || $gudangId <= 0 || $noPo === '' || $idPelanggan <= 0 || !$this->tanggalValid($tanggalPo) || !$this->tanggalValid($tanggalPengiriman)) {
            $db->transRollback();
            return $this->response->setJSON(['error' => 'Produk, Qty, gudang, Nama Pelanggan, No. PO, Tanggal PO dan Tanggal Pengiriman wajib diisi']);
        }

        if ($this->noPoMilikPelangganLain($noPo, $idPelanggan)) {
            $db->transRollback();
            return $this->response->setJSON([
                'error' => "No. PO {$noPo} sudah terdaftar untuk pelanggan lain."
            ]);
        }

        $detailPoTerpilih = (new Modeldetailpo())
            ->where('detnopo', $noPo)
            ->where('detkodebrg', $detail['kode_produk'])
            ->first();

        if (!$detailPoTerpilih) {
            $db->transRollback();
            return $this->response->setJSON([
                'error' => "No. PO {$noPo} tidak memiliki item {$detail['kode_produk']}."
            ]);
        }

        if ($sumber !== 'migrasi' && $qty > (float) ($detailPoTerpilih['detkurang'] ?? 0)) {
            $db->transRollback();
            return $this->response->setJSON(['error' => 'Qty melebihi sisa qty pada No. PO tersebut']);
        }

        $duplikatNoDo = $this->cekNoDoDuplikat($noDo, $permintaanId);
        if ($duplikatNoDo !== null) {
            $db->transRollback();
            return $this->response->setJSON([
                'error' => "No Surat Jalan {$noDo} sudah dipakai pada {$duplikatNoDo}."
            ]);
        }

        $pending = (int) ((new ModelRencanaPengiriman())
            ->selectSum('qty')
            ->where('detail_id', $detailId)
            ->where('status', 0)
            ->first()['qty'] ?? 0);
        $planned = $pending + $this->hitungTerkirimAktualDetail($detailId);

        if (($planned + $qty) > (int) $detail['qty']) {
            $db->transRollback();
            return $this->response->setJSON(['error' => 'Qty melebihi jumlah yang belum dikirim']);
        }

        if ($sumber === 'migrasi') {
            // Qty ini bukan pengiriman baru -- ini formalisasi qty yang
            // menurut catatan migrasi PO sudah terkirim di dunia nyata
            // sebelum ada surat jalannya. Makanya dicek ke sisa qty migrasi
            // (detail_po.detkirim_awal), bukan ke stok gudang.
            $detailPoMigrasi = (new Modeldetailpo())
                ->where('detnopo', $noPo)
                ->where('detkodebrg', $detail['kode_produk'])
                ->first();
            $sisaMigrasi = (float) ($detailPoMigrasi['detkirim_awal'] ?? 0);
            if (!$detailPoMigrasi || $qty > $sisaMigrasi) {
                $db->transRollback();
                return $this->response->setJSON(['error' => 'Qty melebihi sisa qty migrasi yang belum ada surat jalannya untuk produk ini']);
            }
        } else {
            $stok = (new Modelstok())->getStokByGudang($detail['kode_produk'], $gudangId);
            if ($qty > $stok) {
                $db->transRollback();
                return $this->response->setJSON(['error' => 'Stok gudang tidak mencukupi']);
            }
        }

        (new ModelDetailPermintaanPengiriman())->update($detailId, [
            'idpel' => $idPelanggan,
            'tanggal_po' => $tanggalPo,
        ]);

        (new ModelPermintaanPengiriman())->update($detail['permintaan_id'], [
            'tanggal_pengiriman' => $tanggalPengiriman,
        ]);

        // Item yang sama (kode produk, No. Surat Jalan, No. PO, gudang, dan
        // jenis sumber semuanya sama persis) yang ditambah lagi dalam sesi
        // yang sama digabung qty-nya ke baris yang sudah ada, bukan bikin
        // baris baru -- biar 1 surat jalan nggak punya baris duplikat buat
        // produk yang sama.
        $rencanaModel = new ModelRencanaPengiriman();
        $rencanaSama = $rencanaModel
            ->where('permintaan_id', $detail['permintaan_id'])
            ->where('detail_id', $detailId)
            ->where('no_do', $noDo)
            ->where('no_po', $noPo)
            ->where('gudang_id', $gudangId)
            ->where('sumber', $sumber)
            ->where('status', 0)
            ->first();

        if ($rencanaSama) {
            $qtyGabungan = (int) $rencanaSama['qty'] + $qty;
            $rencanaModel->update((int) $rencanaSama['id'], ['qty' => $qtyGabungan]);
            $rencanaId = (int) $rencanaSama['id'];
        } else {
            $rencanaModel->insert([
                'permintaan_id' => $detail['permintaan_id'],
                'detail_id' => $detailId,
                'kode_produk' => $detail['kode_produk'],
                'qty' => $qty,
                'gudang_id' => $gudangId,
                'no_do' => $noDo,
                'no_btb' => $noBtb,
                'no_po' => $noPo,
                'tanggal_po' => $tanggalPo,
                'status' => 0,
                'sumber' => $sumber,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $qtyGabungan = $qty;
            $rencanaId = (int) $rencanaModel->getInsertID();
        }

        $db->transComplete();

        return $this->response->setJSON([
            'sukses' => 'Barang yang akan dikirim ditambahkan',
            'permintaan_id' => (int) $detail['permintaan_id'],
            'permintaan_hash' => sha1((int) $detail['permintaan_id']),
            'rencana_id' => $rencanaId,
            'qty_total' => $qtyGabungan,
            'digabung' => $rencanaSama !== null,
        ]);
    }

    public function updateRencanaDokumen()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $id = (int) $this->request->getPost('id');
        $field = (string) $this->request->getPost('field');
        $value = trim((string) $this->request->getPost('value'));

        if ($id <= 0 || !in_array($field, ['no_do', 'no_btb', 'no_po', 'tanggal_po'], true)) {
            return $this->response->setJSON(['error' => 'Data dokumen tidak valid']);
        }

        // No BTB opsional -- boleh dikosongkan, beda dari No Do/No PO/Tanggal PO yang wajib.
        if ($field !== 'no_btb' && $value === '') {
            return $this->response->setJSON(['error' => 'Data dokumen tidak valid']);
        }

        if ($field === 'tanggal_po' && !$this->tanggalValid($value)) {
            return $this->response->setJSON(['error' => 'Tanggal PO tidak valid']);
        }

        $rencanaModel = new ModelRencanaPengiriman();
        $rencana = $rencanaModel->find($id);
        if (!$rencana || (int) $rencana['status'] !== 0) {
            return $this->response->setJSON(['error' => 'Rencana pengiriman tidak ditemukan atau sudah dikirim']);
        }

        $detail = (new ModelDetailPermintaanPengiriman())->find($rencana['detail_id']);
        if ($field === 'no_po') {
            $idPelanggan = (int) ($detail['idpel'] ?? 0);
            if ($idPelanggan <= 0) {
                return $this->response->setJSON(['error' => 'Pilih pelanggan terlebih dahulu']);
            }
            if ($this->noPoMilikPelangganLain($value, $idPelanggan)) {
                return $this->response->setJSON(['error' => "No. PO {$value} sudah terdaftar untuk pelanggan lain"]);
            }
        }

        // Satu No Surat Jalan sekarang boleh gabungan dari beberapa No. PO
        // (asal pelanggan & gudang asalnya sama -- itu 1 pengiriman fisik
        // yang sama), sama seperti aturan di eksekusiKirim().
        $noDo = $field === 'no_do' ? $value : trim((string) $rencana['no_do']);
        if ($noDo !== '') {
            $barisLain = \Config\Database::connect()
                ->table('rencana_pengiriman rp')
                ->select('rp.gudang_id, d.idpel', false)
                ->join('detail_permintaan_pengiriman d', 'd.id = rp.detail_id')
                ->where('rp.permintaan_id', $rencana['permintaan_id'])
                ->where('rp.status', 0)
                ->where('rp.no_do', $noDo)
                ->where('rp.id !=', $id)
                ->get()
                ->getResultArray();

            $gudangId = (int) $rencana['gudang_id'];
            $idPelangganBaris = (int) ($detail['idpel'] ?? 0);

            foreach ($barisLain as $baris) {
                if ((int) $baris['gudang_id'] !== $gudangId) {
                    return $this->response->setJSON(['error' => 'Gudang asal harus sama untuk semua item dalam satu No Surat Jalan']);
                }
                if ((int) $baris['idpel'] !== $idPelangganBaris) {
                    return $this->response->setJSON(['error' => 'Pelanggan harus sama untuk semua item dalam satu No Surat Jalan']);
                }
            }
        }

        $rencanaModel->update($id, [$field => $value]);

        return $this->response->setJSON(['sukses' => 'Data dokumen diperbarui']);
    }

    public function updateDetailNoPo()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $detailId = (int) $this->request->getPost('detail_id');
        $noPo = trim((string) $this->request->getPost('no_po'));

        if ($detailId <= 0) {
            return $this->response->setJSON([
                'error' => 'Data produk tidak valid.'
            ]);
        }

        if ($noPo === '') {
            return $this->response->setJSON([
                'error' => 'No. PO wajib diisi.'
            ]);
        }

        $detail = (new ModelDetailPermintaanPengiriman())
            ->find($detailId);

        if (!$detail) {
            return $this->response->setJSON([
                'error' => 'Detail permintaan tidak ditemukan.'
            ]);
        }

        $idPelanggan = (int) ($detail['idpel'] ?? 0);
        if ($idPelanggan <= 0) {
            return $this->response->setJSON(['error' => 'Pilih pelanggan terlebih dahulu.']);
        }

        if ($this->noPoMilikPelangganLain($noPo, $idPelanggan)) {
            return $this->response->setJSON([
                'error' => "No. PO {$noPo} sudah terdaftar untuk pelanggan lain."
            ]);
        }

        return $this->response->setJSON([
            'sukses' => 'No. PO dapat digunakan.'
        ]);
    }

    public function updateDetailTanggalPo()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $detailId = (int) $this->request->getPost('detail_id');
        $tanggalPo = trim((string) $this->request->getPost('tanggal_po'));
        $noPo = trim((string) $this->request->getPost('no_po'));

        if ($detailId <= 0 || !$this->tanggalValid($tanggalPo)) {
            return $this->response->setJSON(['error' => 'Tanggal PO tidak valid']);
        }

        $detailModel = new ModelDetailPermintaanPengiriman();
        if (!$detailModel->find($detailId)) {
            return $this->response->setJSON(['error' => 'Data produk tidak ditemukan']);
        }

        $db = \Config\Database::connect();
        $db->transStart();
        $detailModel->update($detailId, ['tanggal_po' => $tanggalPo]);

        if ($noPo !== '' && (new Modelpo())->find($noPo)) {
            $db->table('po')->where('nopo', $noPo)->update(['tglpo' => $tanggalPo]);
            $db->table('detail_po')->where('detnopo', $noPo)->update(['dettglpo' => $tanggalPo]);
            $db->table('outstanding')->where('nopo', $noPo)->update(['tgl' => $tanggalPo]);
        }

        $db->transComplete();
        if (!$db->transStatus()) {
            return $this->response->setJSON(['error' => 'Tanggal PO gagal diperbarui']);
        }

        return $this->response->setJSON(['sukses' => 'Tanggal PO diperbarui']);
    }

    public function updateTanggalPengiriman()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $permintaanId = (int) $this->request->getPost('permintaan_id');
        $tanggalPengiriman = trim((string) $this->request->getPost('tanggal_pengiriman'));

        if ($permintaanId <= 0 || !$this->tanggalValid($tanggalPengiriman)) {
            return $this->response->setJSON(['error' => 'Tanggal pengiriman tidak valid']);
        }

        $model = new ModelPermintaanPengiriman();
        if (!$model->find($permintaanId)) {
            return $this->response->setJSON(['error' => 'Data permintaan tidak ditemukan']);
        }

        $model->update($permintaanId, ['tanggal_pengiriman' => $tanggalPengiriman]);

        return $this->response->setJSON(['sukses' => 'Tanggal pengiriman diperbarui']);
    }

    public function updateDetailPelanggan()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $detailId = (int) $this->request->getPost('detail_id');
        $idPelanggan = (int) $this->request->getPost('idpelanggan');

        if ($detailId <= 0) {
            return $this->response->setJSON(['error' => 'Data produk tidak valid']);
        }

        $detailModel = new ModelDetailPermintaanPengiriman();
        if (!$detailModel->find($detailId)) {
            return $this->response->setJSON(['error' => 'Data produk tidak ditemukan']);
        }

        $detailModel->update($detailId, [
            'idpel' => $idPelanggan > 0 ? $idPelanggan : null,
        ]);

        return $this->response->setJSON(['sukses' => 'Nama pelanggan diperbarui']);
    }

    public function kirimProduk()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $permintaanId = (int) $this->request->getPost('permintaan_id');
        $tanggalPengiriman = (string) $this->request->getPost('tanggal_pengiriman');
        if ($permintaanId <= 0) {
            return $this->response->setJSON(['error' => 'Data permintaan tidak valid']);
        }

        if ($tanggalPengiriman === '' || strtotime($tanggalPengiriman) === false) {
            return $this->response->setJSON(['error' => 'Tanggal pengiriman wajib diisi']);
        }

        return $this->response->setJSON($this->eksekusiKirim($permintaanId, $tanggalPengiriman));
    }

    /**
     * "Kirim Langsung" dari daftar: bikin permintaan kosong lalu langsung
     * masuk ke layar Proses yang sama dengan alur bertahap -- di layar itu
     * user bisa cari produk baru dan langsung isi+kirim di satu tempat,
     * tanpa perlu halaman/form terpisah.
     */
    /**
     * Halaman "Input Pengiriman" -- murni tampilan, TIDAK menulis apa pun ke
     * database saat dibuka. Permintaan barunya baru benar-benar dibuat oleh
     * simpanRencana() pas user menyimpan item pertamanya.
     */
    public function langsung(?string $hash = null)
    {
        $currentUser = (new Modeluser())
            ->select('id, userid, usernama')
            ->where('userid', (string) session()->get('userid'))
            ->first();

        if (!$currentUser) {
            return redirect()->to('/login/keluar')
                ->with('error', 'Sesi user tidak valid. Silakan login kembali.');
        }

        $poAktif = \Config\Database::connect()
            ->table('detail_po dp')
            ->select('dp.detnopo AS nopo, MAX(pl.pelnama) AS pelnama', false)
            ->join('po', 'po.nopo = dp.detnopo')
            ->join('pelanggan pl', 'pl.pelid = po.idpel', 'left')
            ->where('dp.detkurang >', 0)
            ->groupBy('dp.detnopo')
            ->orderBy('dp.detnopo', 'ASC')
            ->get()
            ->getResultArray();

        $draft = null;
        if ($hash !== null) {
            $headerQuery = (new ModelPermintaanPengiriman())->cekHash($hash);
            if ($headerQuery->getNumRows() === 0) {
                return $this->response->setStatusCode(404, 'Data tidak ditemukan');
            }

            $header = $headerQuery->getRowArray();
            $db = \Config\Database::connect();
            $rencanaBelumKirim = $db->table('rencana_pengiriman r')
                ->select('r.id AS rencana_id, r.no_po, r.no_do, r.kode_produk, r.qty, r.gudang_id, r.sumber, d.nama_produk, d.idpel, pl.pelnama, g.gdgnama', false)
                ->join('detail_permintaan_pengiriman d', 'd.id = r.detail_id')
                ->join('pelanggan pl', 'pl.pelid = d.idpel', 'left')
                ->join('gudang g', 'g.gdgid = r.gudang_id', 'left')
                ->where('r.permintaan_id', $header['id'])
                ->where('r.status', 0)
                ->orderBy('r.id', 'ASC')
                ->get()
                ->getResultArray();

            if (!$rencanaBelumKirim) {
                return $this->response->setStatusCode(404, 'Data tidak ditemukan');
            }

            $draft = [
                'permintaan_id' => (int) $header['id'],
                'no_do' => (string) $rencanaBelumKirim[0]['no_do'],
                'idpel' => (int) $rencanaBelumKirim[0]['idpel'],
                'pelnama' => (string) ($rencanaBelumKirim[0]['pelnama'] ?? '-'),
                'tanggal_pengiriman' => (string) ($header['tanggal_pengiriman'] ?? date('Y-m-d')),
                'items' => $rencanaBelumKirim,
            ];
        }

        return view('permintaanpengiriman/langsung', [
            'gudang' => (new Modelgudang())->findAll(),
            'poAktif' => $poAktif,
            'draft' => $draft,
        ]);
    }

    private function eksekusiKirim(int $permintaanId, string $tanggalPengiriman): array
    {
        $permintaanModel = new ModelPermintaanPengiriman();
        $header = $permintaanModel->find($permintaanId);
        if (!$header) {
            return ['error' => 'Data permintaan tidak ditemukan'];
        }

        $permintaanModel->update($permintaanId, [
            'tanggal_pengiriman' => $tanggalPengiriman,
        ]);

        $rencanaModel = new ModelRencanaPengiriman();
        $jumlahRencana = $rencanaModel
            ->where('permintaan_id', $permintaanId)
            ->where('status', 0)
            ->countAllResults();
        if ($jumlahRencana === 0) {
            return ['error' => 'Belum ada barang baru yang akan dikirim'];
        }

        $dokumenKosong = $rencanaModel
            ->where('permintaan_id', $permintaanId)
            ->where('status', 0)
            ->groupStart()
                ->where('no_do', '')
                ->orWhere('no_do', null)
                ->orWhere('no_po', '')
                ->orWhere('no_po', null)
            ->groupEnd()
            ->countAllResults();

        if ($dokumenKosong > 0) {
            return ['error' => 'No. PO dan No Surat Jalan pada tabel bawah wajib diisi sebelum kirim produk'];
        }

        $db = \Config\Database::connect();
        $rencana = $db->table('rencana_pengiriman r')
            ->select('r.*, d.nama_produk, d.berat, d.qty AS total_permintaan, d.idpel AS idpelanggan_detail, COALESCE(r.tanggal_po, d.tanggal_po) AS tanggal_po_rencana', false)
            ->join('detail_permintaan_pengiriman d', 'd.id = r.detail_id')
            ->where('r.permintaan_id', $permintaanId)
            ->where('r.status', 0)
            ->orderBy('r.id')
            ->get()
            ->getResultArray();

        if (!$rencana) {
            return ['error' => 'Belum ada barang baru yang akan dikirim'];
        }

        $rencanaIds = array_map('intval', array_column($rencana, 'id'));
        $poToTanggal = [];
        $doToGudang = [];
        $doToPelanggan = [];
        foreach ($rencana as $row) {
            $noDo = trim((string) $row['no_do']);
            $noPo = trim((string) $row['no_po']);
            $tanggalPo = trim((string) ($row['tanggal_po_rencana'] ?? ''));
            $gudangId = (int) $row['gudang_id'];
            $idPelangganDetail = (int) ($row['idpelanggan_detail'] ?? 0);

            if (!$this->tanggalValid($tanggalPo)) {
                return ['error' => 'Tanggal PO untuk No. PO ' . $noPo . ' wajib diisi'];
            }

            if (isset($poToTanggal[$noPo]) && $poToTanggal[$noPo] !== $tanggalPo) {
                return ['error' => 'Tanggal untuk No. PO ' . $noPo . ' harus sama pada semua produk'];
            }

            $poToTanggal[$noPo] = $tanggalPo;

            // 1 No Surat Jalan sekarang boleh gabungan dari beberapa No. PO
            // (asal pelanggan & gudang asalnya sama -- itu 1 pengiriman fisik
            // yang sama), tapi gudang & pelanggannya tetap harus konsisten.
            if (isset($doToGudang[$noDo]) && $doToGudang[$noDo] !== $gudangId) {
                return ['error' => 'Gudang asal harus sama untuk semua item dalam satu No Surat Jalan ' . $noDo];
            }
            $doToGudang[$noDo] = $gudangId;

            if (isset($doToPelanggan[$noDo]) && $doToPelanggan[$noDo] !== $idPelangganDetail) {
                return ['error' => 'Pelanggan harus sama untuk semua item dalam satu No Surat Jalan ' . $noDo];
            }
            $doToPelanggan[$noDo] = $idPelangganDetail;

            // Surat jalan yang sama boleh diisi berkali-kali (nambah item
            // satu-satu dari sesi Input Pengiriman yang sama) -- itu bukan
            // duplikat, itu emang cara kerjanya sekarang. Yang beneran gak
            // boleh itu kalau ternyata faktur ini sudah dipakai transaksi
            // LAIN dengan gudang/pelanggan yang beda (baru genuinely bentrok).
            $barangKeluarLama = (new ModelBarangKeluar())->find($noDo);
            if ($barangKeluarLama) {
                if ((int) $barangKeluarLama['gudang'] !== $gudangId || (int) $barangKeluarLama['idpel'] !== $idPelangganDetail) {
                    return ['error' => 'No Surat Jalan ' . $noDo . ' sudah dipakai transaksi lain dengan gudang/pelanggan berbeda'];
                }
            }
        }

        $modelPo = new Modelpo();
        $modelDetailPo = new Modeldetailpo();
        $modelBarangKeluar = new ModelBarangKeluar();
        $modelDetailBarangKeluar = new ModelDetailBarangKeluar();
        $modelOutstanding = new Modeloutstand();
        $modelStok = new Modelstok();

        $db->transStart();

        $poYangBerubah = [];
        $groupBarangKeluar = [];
        $stokAwal = [];
        $stokDeltas = [];

        foreach ($rencana as $row) {
            $noPo = trim((string) $row['no_po']);
            $noDo = trim((string) $row['no_do']);
            $kodeProduk = (string) $row['kode_produk'];
            $qty = (int) $row['qty'];
            $totalPermintaan = (int) $row['total_permintaan'];
            $gudangId = (int) $row['gudang_id'];
            $idPelangganDetail = (int) ($row['idpelanggan_detail'] ?? 0);
            $tanggalPo = (string) $row['tanggal_po_rencana'];
            $berat = (float) $row['berat'];
            $subtotal = $qty * $berat;
            $sumber = trim((string) ($row['sumber'] ?? 'baru')) === 'migrasi' ? 'migrasi' : 'baru';
            $qtyProdukPo = (int) ($db->table('rencana_pengiriman')
                ->selectSum('qty')
                ->where('permintaan_id', $permintaanId)
                ->where('detail_id', $row['detail_id'])
                ->where('no_po', $noPo)
                ->get()
                ->getRowArray()['qty'] ?? 0);
            $qtyProdukPo = max($qtyProdukPo, $qty);
            $subtotalPermintaan = $qtyProdukPo * $berat;

            if ($idPelangganDetail <= 0) {
                $db->transRollback();
                return ['error' => 'Nama pelanggan untuk produk ' . $kodeProduk . ' wajib dipilih'];
            }

            $stokRow = $modelStok
                ->where('kodebarang', $kodeProduk)
                ->where('gudang', $gudangId)
                ->first();

            // Qty dari migrasi PO itu udah pernah "terkirim" di dunia nyata
            // sebelum ada surat jalannya -- stok sekarang dianggap sudah
            // memperhitungkan itu, jadi gak perlu dicek/dipotong lagi di sini.
            if ($sumber !== 'migrasi') {
                if (!$stokRow || (int) $stokRow['stok'] < $qty) {
                    $db->transRollback();
                    return ['error' => 'Stok ' . $kodeProduk . ' tidak mencukupi di gudang asal'];
                }

                $stokId = (int) $stokRow['id'];
                if (!isset($stokAwal[$stokId])) {
                    $stokAwal[$stokId] = (int) $stokRow['stok'];
                    $stokDeltas[$stokId] = 0;
                }

                $stokDeltas[$stokId] += $qty;
                if ($stokAwal[$stokId] < $stokDeltas[$stokId]) {
                    $db->transRollback();
                    return ['error' => 'Stok ' . $kodeProduk . ' tidak mencukupi di gudang asal'];
                }
            }

            $po = $modelPo->find($noPo);
            if ($po && (int) $po['idpel'] !== $idPelangganDetail) {
                $db->transRollback();
                return ['error' => 'No. PO ' . $noPo . ' sudah terdaftar untuk pelanggan lain'];
            }

            if (!$po) {
                $modelPo->insert([
                    'nopo' => $noPo,
                    'tglpo' => $tanggalPo,
                    'idpel' => $idPelangganDetail,
                    'qty' => 0,
                    'hargapo' => 0,
                ]);
            } else {
                $modelPo->update($noPo, ['tglpo' => $tanggalPo]);
            }

            $harga = (float) ($stokRow['harga'] ?? 0);
            $detailPo = $modelDetailPo
                ->where('detnopo', $noPo)
                ->where('detkodebrg', $kodeProduk)
                ->first();

            if ($detailPo) {
                $qtyPo = max((int) $detailPo['detqty'], $qtyProdukPo);
                $subtotalPo = $qtyPo * $berat;
                $modelDetailPo->update($detailPo['id'], [
                    'dettglpo' => $tanggalPo,
                    'detqty' => $qtyPo,
                    'detsubtotal' => $subtotalPo,
                    'detharga' => $qtyPo * $harga,
                    'detidpel' => $idPelangganDetail,
                    'material' => $stokRow['material'] ?? null,
                    'gudang' => $gudangId,
                ]);
            } else {
                $modelDetailPo->insert([
                    'detnopo' => $noPo,
                    'dettglpo' => $tanggalPo,
                    'detkodebrg' => $kodeProduk,
                    'namabarang' => $row['nama_produk'],
                    'detberat' => $berat,
                    'detqty' => $qtyProdukPo,
                    'detkirim' => 0,
                    'detidpel' => $idPelangganDetail,
                    'detsubtotal' => $subtotalPermintaan,
                    'detharga' => $qtyProdukPo * $harga,
                    'material' => $stokRow['material'] ?? null,
                    'gudang' => $gudangId,
                ]);
            }

            // Qty ini bukan pengiriman baru -- formalisasi qty migrasi yang
            // sebelumnya cuma angka di detkirim_awal. Kurangi jatah
            // migrasinya sekarang, biar gak dobel ke-hitung sama detkirim
            // (yang di-resync dari detail_barangkeluar) di bawah nanti.
            if ($sumber === 'migrasi') {
                $detailPoTerbaru = $modelDetailPo
                    ->where('detnopo', $noPo)
                    ->where('detkodebrg', $kodeProduk)
                    ->first();
                if ($detailPoTerbaru) {
                    $modelDetailPo->update($detailPoTerbaru['id'], [
                        'detkirim_awal' => max((float) ($detailPoTerbaru['detkirim_awal'] ?? 0) - $qty, 0),
                    ]);
                }
            }

            // Group by No Surat Jalan aja (bukan lagi noDo+noPo+gudang+idpel)
            // -- 1 barangkeluar per No Surat Jalan, sekarang boleh nampung
            // item dari beberapa No. PO sekaligus (gudang & pelanggan sudah
            // divalidasi konsisten per No Surat Jalan di atas).
            $groupKey = $noDo;
            if (!isset($groupBarangKeluar[$groupKey])) {
                $groupBarangKeluar[$groupKey] = [
                    'faktur' => $noDo,
                    'detpo' => $noPo,
                    'gudang' => $gudangId,
                    'idpel' => $idPelangganDetail,
                    'qty' => 0,
                    'total_berat' => 0,
                    'detail' => [],
                ];
            }

            $groupBarangKeluar[$groupKey]['qty'] += $qty;
            $groupBarangKeluar[$groupKey]['total_berat'] += $subtotal;
            $groupBarangKeluar[$groupKey]['detail'][] = [
                // _rencana_id bukan kolom asli detail_barangkeluar -- dipakai
                // sementara buat nyambungin baris ini balik ke rencana_pengiriman
                // yang menghasilkannya, dibuang lagi sebelum insert (lihat di
                // bawah, foreach $group['detail']).
                '_rencana_id' => (int) $row['id'],
                'detfaktur' => $noDo,
                'tgl' => $tanggalPengiriman,
                'detpo' => $noPo,
                'detidpel' => $idPelangganDetail,
                'detbrgkode' => $kodeProduk,
                'namabarang' => $row['nama_produk'],
                'material' => $stokRow['material'] ?? null,
                'idbarang' => $stokRow['id'] ?? null,
                'detberat' => $berat,
                'detjml' => $qty,
                'gudang' => $gudangId,
                'detsubtotal' => $subtotal,
                'dari_migrasi' => $sumber === 'migrasi' ? 1 : 0,
            ];

            $outstanding = $modelOutstanding
                ->where('nopo', $noPo)
                ->where('kodebrg', $kodeProduk)
                ->first();

            if ($outstanding) {
                $modelOutstanding->update($outstanding['id'], [
                    'tgl' => $tanggalPo,
                    'qty' => max((int) $outstanding['qty'], $qtyProdukPo),
                    'terkirim' => (int) $outstanding['terkirim'] + $qty,
                    'idpel' => $idPelangganDetail,
                ]);
            } else {
                $modelOutstanding->insert([
                    'nopo' => $noPo,
                    'kodebrg' => $kodeProduk,
                    'idbarang' => $stokRow['id'] ?? null,
                    'tgl' => $tanggalPo,
                    'qty' => $qtyProdukPo,
                    'terkirim' => $qty,
                    'idpel' => $idPelangganDetail,
                ]);
            }

            $poYangBerubah[$noPo] = true;
        }

        foreach ($groupBarangKeluar as $group) {
            // No Surat Jalan ini boleh jadi udah punya baris barangkeluar
            // dari panggilan eksekusiKirim() SEBELUMNYA (nambah item lain ke
            // surat jalan yang sama, dari sesi Input Pengiriman yang sama) --
            // upsert, bukan insert selalu, biar gak bentrok primary key. Kalau
            // BELUM ada, headernya harus dibikin DULU sebelum insert detail
            // (detail_barangkeluar.detfaktur itu FK ke barangkeluar.faktur).
            $barangKeluarLama = $modelBarangKeluar->find($group['faktur']);

            if (!$barangKeluarLama) {
                $modelBarangKeluar->insert([
                    'faktur' => $group['faktur'],
                    'detpo' => $group['detpo'],
                    'tglfaktur' => $tanggalPengiriman,
                    'idpel' => $group['idpel'],
                    'qtykeluar' => $group['qty'],
                    'totalberatbarang' => $group['total_berat'],
                    'gudang' => $group['gudang'],
                ]);
            }

            // Insert satu-satu (bukan insertBatch) supaya tiap baris bisa
            // langsung disambungin balik ke rencana_pengiriman yang
            // menghasilkannya lewat detail_barangkeluar_id -- dipakai nanti
            // biar edit/hapus item pengiriman presisi ke 1 baris rencana,
            // ngga ambigu lagi kalau 1 No Surat Jalan dipakai berkali-kali.
            foreach ($group['detail'] as $detail) {
                $rencanaId = (int) ($detail['_rencana_id'] ?? 0);
                unset($detail['_rencana_id']);

                $modelDetailBarangKeluar->insert($detail);
                $detailBarangKeluarId = (int) $db->insertID();

                if ($rencanaId > 0 && $detailBarangKeluarId > 0) {
                    $db->table('rencana_pengiriman')
                        ->where('id', $rencanaId)
                        ->update(['detail_barangkeluar_id' => $detailBarangKeluarId]);
                }

                // PENTING: insert ke detail_barangkeluar otomatis motong stok
                // lewat trigger DB (tri_insert_detail_stok DAN
                // tri_insert_detailBarangKeluar), TERLEPAS dari kode PHP di
                // atas. Buat baris migrasi (gak boleh potong stok), batalkan
                // potongan itu dengan nambahin balik qty-nya ke `stok`
                // per-gudang SAJA -- trigger tri_data_barang_update di tabel
                // `stok` otomatis nyinkronin ulang `barang.brgstok` (=SUM
                // stok semua gudang) tiap kali baris `stok` di-UPDATE, jadi
                // JANGAN update `barang.brgstok` manual juga di sini, nanti
                // kompensasinya kehitung dobel.
                if (($detail['dari_migrasi'] ?? 0) === 1 && !empty($detail['idbarang'])) {
                    $db->table('stok')
                        ->where('id', $detail['idbarang'])
                        ->set('stok', 'stok + ' . (float) $detail['detjml'], false)
                        ->update();
                }
            }

            // Rekap ulang total qty & berat dari SEMUA detail_barangkeluar
            // punya faktur ini (bukan cuma batch barusan) -- trigger DB
            // sendiri (tri_insert_barang_keluar) nyocokinnya pakai detpo
            // header yang cuma nyimpen 1 No. PO, jadi kurang pas kalau surat
            // jalan ini gabungan beberapa No. PO. Hitung ulang manual biar
            // selalu benar.
            $totalTerbaru = $db->table('detail_barangkeluar')
                ->select('SUM(detjml) AS qty, SUM(detsubtotal) AS berat', false)
                ->where('detfaktur', $group['faktur'])
                ->get()
                ->getRowArray();

            $modelBarangKeluar->update($group['faktur'], [
                'qtykeluar' => (float) ($totalTerbaru['qty'] ?? 0),
                'totalberatbarang' => (float) ($totalTerbaru['berat'] ?? 0),
            ]);
        }

        foreach ($stokDeltas as $stokId => $qtyKirim) {
            $stokTerkini = $modelStok->find($stokId);
            if (!$stokTerkini) {
                $db->transRollback();
                return ['error' => 'Data stok tidak ditemukan saat sinkron stok'];
            }

            $stokSebelum = (int) $stokAwal[$stokId];
            $stokSekarang = (int) $stokTerkini['stok'];
            $stokSeharusnya = $stokSebelum - (int) $qtyKirim;

            if ($stokSekarang === $stokSebelum) {
                $modelStok->update($stokId, ['stok' => $stokSeharusnya]);
            }
        }

        foreach (array_keys($poYangBerubah) as $noPo) {
            $modelOutstanding->sinkronByPo($noPo);
            $detailRows = $modelDetailPo->where('detnopo', $noPo)->findAll();
            $totalQtyPo = 0;
            $totalHargaPo = 0;

            foreach ($detailRows as $detailPo) {
                $kodeProduk = $detailPo['detkodebrg'];
                $totalKirim = (int) ($db->table('detail_barangkeluar')
                    ->selectSum('detjml')
                    ->where('detpo', $noPo)
                    ->where('detbrgkode', $kodeProduk)
                    ->get()
                    ->getRowArray()['detjml'] ?? 0);

                $modelDetailPo->update($detailPo['id'], [
                    'detkirim' => $totalKirim,
                    'detkurang' => max((int) $detailPo['detqty'] - (int) ($detailPo['detkirim_awal'] ?? 0) - $totalKirim, 0),
                ]);

                $outstanding = $modelOutstanding
                    ->where('nopo', $noPo)
                    ->where('kodebrg', $kodeProduk)
                    ->first();

                if ($outstanding) {
                    $modelOutstanding->update($outstanding['id'], [
                        'qty' => (int) $detailPo['detqty'],
                        'terkirim' => (int) ($detailPo['detkirim_awal'] ?? 0) + $totalKirim,
                        'kekurangan' => max((int) $detailPo['detqty'] - (int) ($detailPo['detkirim_awal'] ?? 0) - $totalKirim, 0),
                    ]);
                }

                $totalQtyPo += (int) $detailPo['detqty'];
                $totalHargaPo += (float) $detailPo['detharga'];
            }

            $modelPo->update($noPo, [
                'qty' => $totalQtyPo,
                'hargapo' => $totalHargaPo,
            ]);
        }

        $modelOutstanding->updateDetailOutstandingKurang();
        if ($rencanaIds) {
            $rencanaModel
                ->whereIn('id', $rencanaIds)
                ->set(['status' => 1])
                ->update();
        }

        $totalPermintaan = (int) ((new ModelDetailPermintaanPengiriman())
            ->selectSum('qty')
            ->where('permintaan_id', $permintaanId)
            ->first()['qty'] ?? 0);
        $totalTerkirim = $this->hitungTerkirimAktualPermintaan($permintaanId);

        $permintaanModel->update($permintaanId, [
            'status' => $totalTerkirim >= $totalPermintaan ? 1 : 0,
        ]);

        $db->transComplete();

        if (!$db->transStatus()) {
            return ['error' => 'Pengiriman gagal disimpan'];
        }

        return ['sukses' => 'Produk berhasil dikirim'];
    }

    /**
     * Memindahkan sebagian qty yang sudah dikirim ke PO lain yang sudah ada dan
     * sudah memiliki item yang sama, tanpa mengubah stok dan tanpa membuat PO baru.
     */
    public function pisahkanPoTerkirim()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $rencanaId = (int) $this->request->getPost('rencana_id');
        $qtyPindah = (int) $this->request->getPost('qty');
        $noPoTujuan = trim((string) $this->request->getPost('no_po_tujuan'));
        $noDoBaru = trim((string) $this->request->getPost('no_do_baru'));
        if ($rencanaId <= 0 || $qtyPindah <= 0) {
            return $this->response->setJSON(['error' => 'Data pemisahan PO tidak valid']);
        }

        if ($noPoTujuan === '') {
            return $this->response->setJSON(['error' => 'PO tujuan wajib dipilih']);
        }

        if ($noDoBaru === '') {
            return $this->response->setJSON(['error' => 'No Surat Jalan wajib diisi']);
        }

        if (mb_strlen($noDoBaru) > 100) {
            return $this->response->setJSON(['error' => 'No Surat Jalan maksimal 100 karakter']);
        }

        $db = \Config\Database::connect();
        $rencana = $db->table('rencana_pengiriman r')
            ->select('r.*, d.nama_produk, d.berat, d.idpel AS idpelanggan')
            ->join('detail_permintaan_pengiriman d', 'd.id = r.detail_id')
            ->where('r.id', $rencanaId)
            ->where('r.status', 1)
            ->get()
            ->getRowArray();

        if (!$rencana) {
            return $this->response->setJSON(['error' => 'Riwayat pengiriman tidak ditemukan']);
        }

        $qtyLama = (int) $rencana['qty'];
        if ($qtyLama <= 1 || $qtyPindah >= $qtyLama) {
            return $this->response->setJSON([
                'error' => 'Qty yang dipindah harus lebih kecil dari qty terkirim agar PO asal tetap memiliki qty',
            ]);
        }

        $noPoLama = trim((string) $rencana['no_po']);
        $noDoLama = trim((string) $rencana['no_do']);
        $kodeProduk = (string) $rencana['kode_produk'];

        if (strcasecmp($noPoTujuan, $noPoLama) === 0) {
            return $this->response->setJSON(['error' => 'PO tujuan tidak boleh sama dengan PO asal']);
        }

        $poTujuan = $db->table('po')->where('nopo', $noPoTujuan)->get()->getRowArray();
        if (!$poTujuan) {
            return $this->response->setJSON(['error' => 'PO tujuan tidak ditemukan']);
        }

        if ((int) $poTujuan['idpel'] !== (int) $rencana['idpelanggan']) {
            return $this->response->setJSON(['error' => 'PO tujuan milik pelanggan yang berbeda']);
        }

        $detailPoTujuan = $db->table('detail_po')
            ->where('detnopo', $noPoTujuan)
            ->where('detkodebrg', $kodeProduk)
            ->get()
            ->getRowArray();

        if (!$detailPoTujuan) {
            return $this->response->setJSON(['error' => 'PO tujuan belum memiliki item ' . $kodeProduk . ', pilih PO lain']);
        }

        $pakaiDoLama = strcasecmp($noDoBaru, $noDoLama) === 0;
        if (!$pakaiDoLama) {
            $duplikatDo = (new NoDoChecker())->findSource($noDoBaru);
            if ($duplikatDo !== null) {
                return $this->response->setJSON(['error' => 'No Surat Jalan baru sudah digunakan pada ' . $duplikatDo]);
            }
        }

        $invoiceAktif = $db->table('invoice_out')
            ->where('po_no', $noPoLama)
            ->where('status', 'AKTIF')
            ->countAllResults();
        if ($invoiceAktif > 0) {
            return $this->response->setJSON([
                'error' => 'PO tidak dapat dipisahkan karena sudah digunakan pada Invoice Out aktif. Batalkan invoice terlebih dahulu.',
            ]);
        }

        $detailPoLama = $db->table('detail_po')
            ->where('detnopo', $noPoLama)
            ->where('detkodebrg', $kodeProduk)
            ->get()
            ->getResultArray();
        $barangKeluarLama = $db->table('barangkeluar')->where('faktur', $noDoLama)->get()->getRowArray();
        $detailKirimLama = $db->table('detail_barangkeluar')
            ->where('detfaktur', $noDoLama)
            ->where('detpo', $noPoLama)
            ->where('detbrgkode', $kodeProduk)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        if (count($detailPoLama) !== 1 || !$barangKeluarLama || !$detailKirimLama) {
            return $this->response->setJSON([
                'error' => 'Relasi PO atau produk keluar tidak lengkap sehingga PO belum aman untuk dipisahkan',
            ]);
        }

        $detailPoLama = $detailPoLama[0];
        $qtyDetailPoLama = (int) $detailPoLama['detqty'];
        $totalKirimDokumen = array_sum(array_map(static fn(array $row): int => (int) $row['detjml'], $detailKirimLama));
        if ($qtyPindah > $totalKirimDokumen || $qtyPindah >= $qtyDetailPoLama) {
            return $this->response->setJSON(['error' => 'Qty yang dipindahkan melebihi qty PO atau riwayat pengiriman']);
        }

        $db->transBegin();
        try {
            // Kurangi alokasi riwayat lama dan buat riwayat baru untuk PO tujuan.
            $db->table('rencana_pengiriman')->where('id', $rencanaId)->update([
                'qty' => $qtyLama - $qtyPindah,
            ]);

            $rencanaBaru = $rencana;
            unset($rencanaBaru['id'], $rencanaBaru['nama_produk'], $rencanaBaru['berat'], $rencanaBaru['idpelanggan']);
            $rencanaBaru['qty'] = $qtyPindah;
            $rencanaBaru['no_po'] = $noPoTujuan;
            $rencanaBaru['no_do'] = $noDoBaru;
            $rencanaBaru['created_at'] = date('Y-m-d H:i:s');
            // detail_barangkeluar_id punya baris LAMA nunjuk ke detail yang
            // qty-nya sudah dikurangin di atas, bukan ke qty yang baru
            // dipindah -- dikosongin dulu, disambungin lagi ke baris
            // detail_barangkeluar yang BARU dibuat (di bawah) khusus buat
            // qty yang dipindah ini.
            $rencanaBaru['detail_barangkeluar_id'] = null;
            $db->table('rencana_pengiriman')->insert($rencanaBaru);
            $rencanaBaruId = (int) $db->insertID();

            // CATATAN: detail_po.detqty (kolom "Jumlah") PO asal & PO tujuan
            // SENGAJA TIDAK disentuh di sini. Pisahkan PO cuma mindahin
            // atribusi qty yang SUDAH TERKIRIM dari satu No. PO ke No. PO
            // lain (mis. salah catat PO pas kirim) -- bukan mengubah jumlah
            // yang memang sudah dipesan di masing-masing PO. Yang berubah
            // seharusnya cuma "Qty Terkirim" (detkirim), dihitung ulang di
            // bawah dari detail_barangkeluar yang sebenarnya.

            // Pindahkan qty dari dokumen keluar lama ke dokumen koreksi baru.
            $sisaDipindah = $qtyPindah;
            $contohDetailKirim = $detailKirimLama[0];
            foreach ($detailKirimLama as $detailKirim) {
                if ($sisaDipindah <= 0) {
                    break;
                }

                $qtyBaris = (int) $detailKirim['detjml'];
                $ambil = min($qtyBaris, $sisaDipindah);
                $qtySisa = $qtyBaris - $ambil;
                if ($qtySisa > 0) {
                    $db->table('detail_barangkeluar')->where('id', $detailKirim['id'])->update([
                        'detjml' => $qtySisa,
                        'detsubtotal' => $qtySisa * (float) $detailKirim['detberat'],
                    ]);
                } else {
                    $db->table('detail_barangkeluar')->where('id', $detailKirim['id'])->delete();
                }
                $sisaDipindah -= $ambil;
            }

            if ($sisaDipindah !== 0) {
                throw new \RuntimeException('Qty detail pengiriman tidak mencukupi');
            }

            if (!$pakaiDoLama) {
                $headerBaru = $barangKeluarLama;
                unset($headerBaru['time']);
                $headerBaru['faktur'] = $noDoBaru;
                $headerBaru['detpo'] = $noPoTujuan;
                $headerBaru['qtykeluar'] = $qtyPindah;
                $headerBaru['totalberatbarang'] = $qtyPindah * (float) $contohDetailKirim['detberat'];
                $db->table('barangkeluar')->insert($headerBaru);
            }

            unset($contohDetailKirim['id'], $contohDetailKirim['time']);
            $contohDetailKirim['detfaktur'] = $noDoBaru;
            $contohDetailKirim['detpo'] = $noPoTujuan;
            $contohDetailKirim['detjml'] = $qtyPindah;
            $contohDetailKirim['detsubtotal'] = $qtyPindah * (float) $contohDetailKirim['detberat'];
            $db->table('detail_barangkeluar')->insert($contohDetailKirim);
            $detailBarangKeluarBaruId = (int) $db->insertID();

            if ($rencanaBaruId > 0 && $detailBarangKeluarBaruId > 0) {
                $db->table('rencana_pengiriman')
                    ->where('id', $rencanaBaruId)
                    ->update(['detail_barangkeluar_id' => $detailBarangKeluarBaruId]);
            }

            $totalHeaderLama = $db->table('detail_barangkeluar')
                ->select('COALESCE(SUM(detjml), 0) AS qty, COALESCE(SUM(detsubtotal), 0) AS berat', false)
                ->where('detfaktur', $noDoLama)
                ->get()
                ->getRowArray();
            $db->table('barangkeluar')->where('faktur', $noDoLama)->update([
                'qtykeluar' => (int) $totalHeaderLama['qty'],
                'totalberatbarang' => (float) $totalHeaderLama['berat'],
            ]);

            // "Qty Terkirim" (detkirim) PO asal & PO tujuan dihitung ULANG
            // dari detail_barangkeluar yang sebenarnya (bukan ditambah/
            // dikurang manual) -- ini yang seharusnya berubah waktu PO
            // dipisah, bukan detqty ("Jumlah").
            foreach ([$noPoLama, $noPoTujuan] as $noPoKirimUpdate) {
                $detailPoKirimUpdate = $db->table('detail_po')
                    ->where('detnopo', $noPoKirimUpdate)
                    ->where('detkodebrg', $kodeProduk)
                    ->get()
                    ->getRowArray();

                if (!$detailPoKirimUpdate) {
                    continue;
                }

                $totalKirimPo = (float) ($db->table('detail_barangkeluar')
                    ->selectSum('detjml')
                    ->where('detpo', $noPoKirimUpdate)
                    ->where('detbrgkode', $kodeProduk)
                    ->get()
                    ->getRowArray()['detjml'] ?? 0);

                $db->table('detail_po')->where('id', $detailPoKirimUpdate['id'])->update([
                    'detkirim' => $totalKirimPo,
                    'detkurang' => max((int) $detailPoKirimUpdate['detqty'] - (int) ($detailPoKirimUpdate['detkirim_awal'] ?? 0) - $totalKirimPo, 0),
                ]);
            }

            $modelOutstanding = new Modeloutstand();
            $modelOutstanding->sinkronByPoList([$noPoLama, $noPoTujuan]);
            $modelOutstanding->updateDetailOutstandingKurang();

            // Hitung ulang total qty/harga header PO asal & PO tujuan dari seluruh detail_po-nya.
            foreach ([$noPoLama, $noPoTujuan] as $noPoUpdate) {
                $totalPo = $db->table('detail_po')
                    ->select('COALESCE(SUM(detqty), 0) AS qty, COALESCE(SUM(detharga), 0) AS harga', false)
                    ->where('detnopo', $noPoUpdate)
                    ->get()
                    ->getRowArray();
                $db->table('po')->where('nopo', $noPoUpdate)->update([
                    'qty' => (int) $totalPo['qty'],
                    'hargapo' => (float) $totalPo['harga'],
                ]);
            }

            if (!$db->transStatus()) {
                throw new \RuntimeException('Database menolak perubahan pemisahan PO');
            }
            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Gagal memisahkan PO terkirim: {message}', ['message' => $e->getMessage()]);
            return $this->response->setJSON(['error' => 'Pemisahan PO gagal disimpan. Tidak ada data yang diubah.']);
        }

        return $this->response->setJSON([
            'sukses' => 'Qty berhasil dipindahkan ke PO tujuan',
            'no_po_tujuan' => $noPoTujuan,
            'no_do_baru' => $noDoBaru,
        ]);
    }

    public function poTujuanSplit()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $kodeProduk = trim((string) $this->request->getPost('kode_produk'));
        $idPelanggan = (int) $this->request->getPost('idpelanggan');
        $noPoKecuali = trim((string) $this->request->getPost('no_po_kecuali'));

        if ($kodeProduk === '' || $idPelanggan <= 0) {
            return $this->response->setJSON(['data' => []]);
        }

        $rows = \Config\Database::connect()
            ->table('detail_po dp')
            ->select('dp.detnopo AS nopo, po.tglpo', false)
            ->join('po', 'po.nopo = dp.detnopo')
            ->where('dp.detkodebrg', $kodeProduk)
            ->where('po.idpel', $idPelanggan)
            ->where('dp.detnopo !=', $noPoKecuali)
            ->groupBy('dp.detnopo, po.tglpo')
            ->orderBy('po.tglpo', 'DESC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON(['data' => $rows]);
    }

    private function tanggalValid(string $tanggal): bool
    {
        $date = \DateTime::createFromFormat('Y-m-d', $tanggal);
        return $date !== false && $date->format('Y-m-d') === $tanggal;
    }

    public function tampilRencana()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $id = (int) $this->request->getPost('permintaan_id');
        return $this->response->setJSON([
            'data' => view('permintaanpengiriman/datarencana', [
                'rencana' => $this->rencanaData($id),
                'permintaanId' => $id,
            ]),
        ]);
    }

    public function hapusRencana()
    {
        if ($this->request->isAJAX()) {
            (new ModelRencanaPengiriman())->delete($this->request->getPost('id'));
            return $this->response->setJSON(['sukses' => 'Rencana pengiriman dihapus']);
        }
    }

    /**
     * Hapus 1 baris produk di "Tabel Item" (detail_permintaan_pengiriman).
     * Kalau produk itu belum pernah terkirim sama sekali, barisnya beneran
     * dihapus. Kalau sudah pernah terkirim sebagian, barisnya gak dihapus
     * (riwayat kirimnya harus tetap ada) -- qty-nya cuma dipangkas pas-pasan
     * ke jumlah yang sudah terkirim, jadi sisa yang belum terkirim dianggap
     * dibatalkan.
     */
    public function hapusItemPermintaan()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $detailId = (int) $this->request->getPost('detail_id');
        $detailModel = new ModelDetailPermintaanPengiriman();
        $detail = $detailModel->find($detailId);

        if (!$detail) {
            return $this->response->setJSON(['error' => 'Item permintaan tidak ditemukan.']);
        }

        $adaDraft = (new ModelRencanaPengiriman())
            ->where('detail_id', $detailId)
            ->where('status', 0)
            ->countAllResults() > 0;

        if ($adaDraft) {
            return $this->response->setJSON([
                'error' => 'Produk ini masih punya draft pengiriman yang belum dikirim. Hapus dulu draftnya di Tabel Draft Pengiriman sebelum menghapus item ini.',
            ]);
        }

        $terkirim = $this->hitungTerkirimAktualDetail($detailId);
        $permintaanId = (int) $detail['permintaan_id'];

        if ($terkirim <= 0) {
            $detailModel->delete($detailId);
            $pesan = 'Item produk berhasil dihapus dari permintaan ini.';
        } else {
            $detailModel->update($detailId, ['qty' => $terkirim]);
            $pesan = 'Sisa qty yang belum terkirim untuk produk ini dibatalkan.';
        }

        $totalProduk = (int) ($detailModel
            ->selectSum('qty')
            ->where('permintaan_id', $permintaanId)
            ->first()['qty'] ?? 0);
        (new ModelPermintaanPengiriman())->update($permintaanId, ['total_produk' => $totalProduk]);

        return $this->response->setJSON(['sukses' => $pesan]);
    }

    /**
     * Hapus 1 baris di "Riwayat Pengiriman Permintaan Ini" (satu item yang
     * sudah benar-benar terkirim) -- beda dari hapusRencana() yang cuma
     * untuk rencana yang belum dieksekusi. Cuma baris/produk itu doang yang
     * dibatalkan (bukan seluruh No Surat Jalan), stok/PO/outstanding
     * disinkronkan ulang sesuai baris yang dihapus.
     */
    public function hapusRiwayatPengiriman()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $rencanaId = (int) $this->request->getPost('rencana_id');
        $rencana = (new ModelRencanaPengiriman())->find($rencanaId);

        if (!$rencana || (int) $rencana['status'] !== 1) {
            return $this->response->setJSON(['error' => 'Data riwayat pengiriman tidak ditemukan.']);
        }

        $noDo = trim((string) $rencana['no_do']);
        $noPo = trim((string) $rencana['no_po']);
        $kodeProduk = trim((string) $rencana['kode_produk']);
        $gudangId = (int) $rencana['gudang_id'];

        if ($noDo === '' || $noPo === '' || $kodeProduk === '' || $gudangId <= 0) {
            return $this->response->setJSON(['error' => 'Data riwayat pengiriman tidak lengkap.']);
        }

        $this->ensureMigrasiPengirimanColumns();

        $db = \Config\Database::connect();
        $db->transBegin();

        $detailBarangKeluarId = (int) ($rencana['detail_barangkeluar_id'] ?? 0);

        // Kalau riwayat ini punya link presisi ke detail_barangkeluar_id,
        // pakai itu (cuma 1 baris, ngga ambigu). Kalau ngga ada (data lama),
        // fallback ke cara lama -- cocokin kombinasi teks.
        $detailQuery = $db->table('detail_barangkeluar')
            ->select('id, idbarang, detbrgkode, gudang, detjml, dari_migrasi');
        if ($detailBarangKeluarId > 0) {
            $detailQuery->where('id', $detailBarangKeluarId);
        } else {
            $detailQuery->where('detfaktur', $noDo)
                ->where('detpo', $noPo)
                ->where('detbrgkode', $kodeProduk)
                ->where('gudang', $gudangId);
        }
        $detailTerdampak = $detailQuery->get()->getResultArray();

        if (!$detailTerdampak) {
            $db->transRollback();
            return $this->response->setJSON(['error' => 'Detail pengiriman ini tidak ditemukan lagi (mungkin sudah dihapus).']);
        }

        $dariMigrasi = (int) ($detailTerdampak[0]['dari_migrasi'] ?? 0) === 1;
        $totalQty = 0.0;
        $idBarang = null;
        foreach ($detailTerdampak as $row) {
            $totalQty += (float) $row['detjml'];
            $idBarang = $idBarang ?? $row['idbarang'];
        }

        $snapshotStok = null;
        if (!$dariMigrasi) {
            $stokRow = $db->table('stok')
                ->where('kodebarang', $kodeProduk)
                ->where('gudang', $gudangId)
                ->get()
                ->getRowArray();

            if (!$stokRow) {
                $db->transRollback();
                return $this->response->setJSON(['error' => 'Stok ' . $kodeProduk . ' tidak ditemukan di gudang asal']);
            }

            $snapshotStok = ['id' => (int) $stokRow['id'], 'stok_awal' => (int) $stokRow['stok']];
        }

        if ($detailBarangKeluarId > 0) {
            $db->table('detail_barangkeluar')->where('id', $detailBarangKeluarId)->delete();
        } else {
            $db->table('detail_barangkeluar')
                ->where('detfaktur', $noDo)
                ->where('detpo', $noPo)
                ->where('detbrgkode', $kodeProduk)
                ->where('gudang', $gudangId)
                ->delete();
        }

        // Delete di atas otomatis nambah stok lewat trigger DB (tri_delete_detail_stok).
        // Baris migrasi gak pernah motong stok pas dibuat, jadi penambahan
        // otomatis ini salah buat baris itu -- batalkan lagi. Baris normal
        // dipulihkan presisi ke stok_awal + qty (jaga-jaga trigger meleset).
        if ($dariMigrasi && $idBarang) {
            $db->table('stok')->where('id', $idBarang)->set('stok', 'stok - ' . $totalQty, false)->update();
        } elseif ($snapshotStok) {
            $db->table('stok')->where('id', $snapshotStok['id'])->update([
                'stok' => $snapshotStok['stok_awal'] + $totalQty,
            ]);
        }

        // Kalau ini item terakhir di No Surat Jalan itu, header barangkeluar
        // ikut dihapus; kalau masih ada item lain, total-nya dihitung ulang.
        $sisaDetail = $db->table('detail_barangkeluar')
            ->select('SUM(detjml) AS qty, SUM(detsubtotal) AS berat', false)
            ->where('detfaktur', $noDo)
            ->get()
            ->getRowArray();

        if ((float) ($sisaDetail['qty'] ?? 0) <= 0) {
            (new ModelBarangKeluar())->delete($noDo);
        } else {
            (new ModelBarangKeluar())->update($noDo, [
                'qtykeluar' => (float) ($sisaDetail['qty'] ?? 0),
                'totalberatbarang' => (float) ($sisaDetail['berat'] ?? 0),
            ]);
        }

        // Hapus PERSIS baris rencana ini (id-nya udah kita pegang dari awal
        // request) -- ngga perlu nebak lewat kombinasi teks lagi, yang bisa
        // ambigu/salah sasaran kalau ada baris lain berbagi kombinasi yang
        // sama persis (no_do dipakai berkali-kali).
        $db->table('rencana_pengiriman')
            ->where('id', $rencanaId)
            ->delete();

        if ($dariMigrasi) {
            $modelDetailPo = new Modeldetailpo();
            $detailPo = $modelDetailPo->where('detnopo', $noPo)->where('detkodebrg', $kodeProduk)->first();
            if ($detailPo) {
                $modelDetailPo->update($detailPo['id'], [
                    'detkirim_awal' => (float) ($detailPo['detkirim_awal'] ?? 0) + $totalQty,
                ]);
            }
        }

        $modelOutstanding = new Modeloutstand();
        $modelOutstanding->sinkronByPoList([$noPo]);
        $this->sinkronStatusPermintaanPengirimanByPo([$noPo]);

        (new Modeldetailpo())->updateDetailPoKurang();
        $modelOutstanding->updateDetailOutstandingKurang();

        $db->transComplete();

        if (!$db->transStatus()) {
            return $this->response->setJSON(['error' => 'Riwayat pengiriman gagal dihapus.']);
        }

        return $this->response->setJSON(['sukses' => 'Riwayat pengiriman berhasil dihapus, stok sudah dikembalikan.']);
    }

    private function sinkronStatusPermintaanPengirimanByPo(array $noPoList): void
    {
        $noPoList = array_values(array_unique(array_filter(array_map(static fn($noPo) => trim((string) $noPo), $noPoList))));
        if (!$noPoList) {
            return;
        }

        $db = \Config\Database::connect();
        $permintaanRows = $db->table('rencana_pengiriman')
            ->distinct()
            ->select('permintaan_id')
            ->whereIn('no_po', $noPoList)
            ->get()
            ->getResultArray();

        foreach ($permintaanRows as $row) {
            $permintaanId = (int) ($row['permintaan_id'] ?? 0);
            if ($permintaanId <= 0) {
                continue;
            }

            $totalPermintaan = (int) ($db->table('detail_permintaan_pengiriman')
                ->selectSum('qty', 'qty')
                ->where('permintaan_id', $permintaanId)
                ->get()
                ->getRowArray()['qty'] ?? 0);
            $totalTerkirim = $this->hitungTerkirimAktualPermintaan($permintaanId);

            $db->table('permintaan_pengiriman')
                ->where('id', $permintaanId)
                ->update([
                    'status' => $totalPermintaan > 0 && $totalTerkirim >= $totalPermintaan ? 1 : 0,
                ]);
        }
    }

    private function rencanaData(int $permintaanId): array
    {
        return \Config\Database::connect()
            ->table('rencana_pengiriman r')
            ->select('r.*, d.nama_produk, d.berat, g.gdgnama')
            ->join('detail_permintaan_pengiriman d', 'd.id = r.detail_id')
            ->join('gudang g', 'g.gdgid = r.gudang_id', 'left')
            ->where('r.permintaan_id', $permintaanId)
            ->where('r.status', 0)
            ->orderBy('r.id')
            ->get()
            ->getResultArray();
    }

    private function riwayatPengirimanData(int $permintaanId): array
    {
        return \Config\Database::connect()
            ->table('rencana_pengiriman r')
            ->select('r.id, r.no_po, r.no_do, r.no_btb, r.tanggal_po, r.kode_produk, r.qty, d.nama_produk, d.idpel AS idpelanggan, g.gdgnama, MIN(dk.tgl) AS tanggal_pengiriman, SUM(dk.detjml) AS qty_terkirim', false)
            ->join('detail_permintaan_pengiriman d', 'd.id = r.detail_id')
            ->join('gudang g', 'g.gdgid = r.gudang_id', 'left')
            ->join('detail_barangkeluar dk', 'dk.detfaktur = r.no_do AND dk.detpo = r.no_po AND dk.detbrgkode = r.kode_produk', 'inner')
            ->where('r.permintaan_id', $permintaanId)
            ->where('r.status', 1)
            ->groupBy('r.id, r.no_po, r.no_do, r.no_btb, r.tanggal_po, r.kode_produk, r.qty, d.nama_produk, d.idpel, g.gdgnama')
            ->orderBy('tanggal_pengiriman', 'ASC')
            ->orderBy('r.id', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function hitungTerkirimAktualDetail(int $detailId): int
    {
        $db = \Config\Database::connect();
        $rencanaTerkirimSubquery = $db->table('rencana_pengiriman')
            ->distinct()
            ->select('no_do, no_po, kode_produk')
            ->where('detail_id', $detailId)
            ->where('status', 1)
            ->where('no_do IS NOT NULL', null, false)
            ->where('no_do <>', '')
            ->where('no_po IS NOT NULL', null, false)
            ->where('no_po <>', '')
            ->getCompiledSelect();

        return (int) ($db->table('detail_barangkeluar dk')
            ->selectSum('dk.detjml', 'qty')
            ->join("({$rencanaTerkirimSubquery}) rt", 'rt.no_do = dk.detfaktur AND rt.no_po = dk.detpo AND rt.kode_produk = dk.detbrgkode', 'inner', false)
            ->get()
            ->getRowArray()['qty'] ?? 0);
    }

    private function hitungTerkirimAktualPermintaan(int $permintaanId): int
    {
        $db = \Config\Database::connect();
        $rencanaTerkirimSubquery = $db->table('rencana_pengiriman')
            ->distinct()
            ->select('no_do, no_po, kode_produk')
            ->where('permintaan_id', $permintaanId)
            ->where('status', 1)
            ->where('no_do IS NOT NULL', null, false)
            ->where('no_do <>', '')
            ->where('no_po IS NOT NULL', null, false)
            ->where('no_po <>', '')
            ->getCompiledSelect();

        return (int) ($db->table('detail_barangkeluar dk')
            ->selectSum('dk.detjml', 'qty')
            ->join("({$rencanaTerkirimSubquery}) rt", 'rt.no_do = dk.detfaktur AND rt.no_po = dk.detpo AND rt.kode_produk = dk.detbrgkode', 'inner', false)
            ->get()
            ->getRowArray()['qty'] ?? 0);
    }

    private function noPoMilikPelangganLain(string $noPo, int $idPelanggan): bool
    {
        $noPo = trim($noPo);

        if ($noPo === '' || $idPelanggan <= 0) {
            return false;
        }

        $po = (new Modelpo())->find($noPo);
        return $po && (int) $po['idpel'] !== $idPelanggan;
    }

    /**
     * Cek No Surat Jalan sudah dipakai transaksi/permintaan lain atau belum.
     * Baris rencana_pengiriman milik permintaan_id yang SAMA sengaja
     * dikecualikan -- itu memang cara satu No Surat Jalan menampung
     * beberapa produk/PO dalam satu sesi input yang sama.
     */
    private function cekNoDoDuplikat(string $noDo, int $permintaanIdSaatIni): ?string
    {
        $noDo = trim($noDo);
        if ($noDo === '') {
            return null;
        }

        $db = \Config\Database::connect();
        $sources = [
            ['table' => 'materialmasuk', 'field' => 'no_do', 'label' => 'Data Material Masuk'],
            ['table' => 'materialkeluar', 'field' => 'faktur', 'label' => 'Data Material Keluar'],
            ['table' => 'barangmasuk', 'field' => 'faktur', 'label' => 'Data Produk Masuk'],
            ['table' => 'barangkeluar', 'field' => 'faktur', 'label' => 'Data Produk Keluar'],
            ['table' => 'permintaanbarang', 'field' => 'permintaan', 'label' => 'Data Permintaan Transfer'],
            ['table' => 'permintaanbarangkirim', 'field' => 'faktur', 'label' => 'Data Pengiriman Transfer'],
        ];

        foreach ($sources as $source) {
            if (!$db->tableExists($source['table']) || !$db->fieldExists($source['field'], $source['table'])) {
                continue;
            }

            if ($db->table($source['table'])->where($source['field'], $noDo)->countAllResults() > 0) {
                return $source['label'];
            }
        }

        $rencanaLain = $db->table('rencana_pengiriman')
            ->where('no_do', $noDo)
            ->where('permintaan_id !=', $permintaanIdSaatIni)
            ->countAllResults();

        if ($rencanaLain > 0) {
            return 'Permintaan Pengiriman lain (belum dikirim)';
        }

        return null;
    }
}
