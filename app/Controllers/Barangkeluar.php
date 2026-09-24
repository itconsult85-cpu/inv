<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Modelbarang;
use App\Models\ModelBarangKeluar;
use App\Models\Modelberat;
use App\Models\Modelgudang;
use App\Models\ModelDetailBarangKeluar;
use App\Models\ModelDetailPermintaanPengiriman;
use App\Models\Modeldetailpo;
use App\Models\Modeloutstand;
use App\Models\Modelstok;
use App\Models\ModelTempBarangKeluar;
use \Hermawan\DataTables\DataTable;

class Barangkeluar extends BaseController
{
    public function data()
    {
        return view('barangkeluar/viewdata');
    }

    public function listData()
    {
        if ($this->request->isAJAX()) {
            $tglawal = $this->request->getPost('tglawal');
            $tglakhir = $this->request->getPost('tglakhir');

            $db = \Config\Database::connect();
            $builder = $db->table('barangkeluar bk')
                ->select('bk.faktur, bk.detpo, bk.tglfaktur, bk.idpel, p.pelnama, bk.qtykeluar, bk.totalberatbarang, g.gdgnama')
                ->join('gudang g', 'g.gdgid = bk.gudang')
                ->join('pelanggan p', 'p.pelid = bk.idpel');

            if ($tglawal && $tglakhir) {
                $builder->whereIn('bk.faktur', function ($subQuery) use ($tglawal, $tglakhir) {
                    $subQuery->select('barangkeluar.faktur')
                        ->from('barangkeluar')
                        ->where('barangkeluar.tglfaktur >=', $tglawal)
                        ->where('barangkeluar.tglfaktur <=', $tglakhir);
                });
            }

            return DataTable::of($builder)
                ->addNumbering('nomor')
                ->add('aksi', function ($row) {
                    $hash = sha1($row->faktur);
                    // Tombol Hapus dipindah ke tab List Pengiriman (aksi
                    // hapusPengirimanLangsung), jadi di sini cuma cetak.
                    return "<div class=\"d-flex justify-content-center align-items-center\" style=\"gap:4px;\">"
                        . "<button type=\"button\" class=\"btn btn-sm btn-info\" title=\"Lihat Delivery Order\" onclick=\"lihatDo('{$hash}')\"><i class=\"fa fa-eye\"></i></button>"
                        . "<button type=\"button\" class=\"btn btn-sm btn-success\" title=\"Print Delivery Order\" onclick=\"cetakDo('{$hash}')\"><i class=\"fa fa-print\"></i></button>"
                        . "</div>";
                })
                ->add('jenis_badge', function ($row) {
                    $jenis = $this->jenisPengiriman((string) $row->faktur, (string) $row->detpo);
                    $class = $jenis === 'Permintaan' ? 'badge-primary' : 'badge-success';
                    return '<span class="badge ' . $class . '">' . $jenis . '</span>';
                })
                ->format('qtykeluar', function ($value) {
                    return number_format($value, 0, ',', '.');
                })
                ->format('tglfaktur', function ($value) {
                    return date('d-m-Y', strtotime($value));
                })
                ->toJson(true);
        }
    }

    private function jenisPengiriman(string $noDo, string $noPo): string
    {
        $db = \Config\Database::connect();
        $row = $db->table('rencana_pengiriman rp')
            ->select('pp.keterangan')
            ->join('permintaan_pengiriman pp', 'pp.id = rp.permintaan_id', 'left')
            ->where('rp.no_do', $noDo)
            ->where('rp.no_po', $noPo)
            ->where('rp.status', 1)
            ->orderBy('rp.id', 'ASC')
            ->get()
            ->getRowArray();

        if (!$row) {
            return 'Langsung';
        }

        return (string) ($row['keterangan'] ?? '') === 'Kirim langsung' ? 'Langsung' : 'Permintaan';
    }

    public function listDataPengiriman()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $db = \Config\Database::connect();
        $this->ensureBtbFileColumns();
        $draw = (int) $this->request->getPost('draw');
        $start = max(0, (int) $this->request->getPost('start'));
        $length = max(1, (int) $this->request->getPost('length'));
        $searchData = $this->request->getPost('search');
        $search = strtolower(trim((string) ($searchData['value'] ?? '')));
        $tglawal = trim((string) $this->request->getPost('tglawal'));
        $tglakhir = trim((string) $this->request->getPost('tglakhir'));

        // 1 baris = 1 No Surat Jalan (bukan lagi 1 baris = 1 Permintaan
        // Pengiriman) -- biar permintaan yang riwayatnya punya banyak surat
        // jalan berbeda-beda kelihatan kepisah-pisah, bukan numpuk jadi 1
        // baris. Item yang belum ada No Surat Jalan-nya (draft awal, kolom
        // no_do masih kosong) tetap dikelompokkan per-permintaan seperti
        // biasa, soalnya belum ada surat jalan buat jadi pemisahnya.
        // Tanggal yang ditampilkan per baris itu tanggal SURAT JALAN aslinya
        // (barangkeluar.tglfaktur), bukan tanggal permintaan_pengiriman
        // dibuat -- soalnya buat data migrasi/riwayat lama, tanggal
        // permintaan-nya bisa beda jauh sama tanggal pengiriman yang
        // sebenarnya (yang itu yang ditampilkan juga di halaman Edit
        // Pengiriman). Baris Draft (belum ada no_do/barangkeluar) tetap
        // pakai tanggal permintaan sebagai fallback.
        $rows = $db->table('permintaan_pengiriman pp')
            ->select("
                pp.id,
                pp.tanggal,
                pp.keterangan,
                users.usernama,
                MAX(NULLIF(rp.no_do, '')) AS grup_do,
                MAX(bk.tglfaktur) AS tglfaktur_asli,
                COALESCE(SUM(rp.qty), 0) AS total_grup,
                COALESCE(SUM(CASE WHEN rp.status = 0 THEN rp.qty ELSE 0 END), 0) AS qty_rencana,
                COALESCE(SUM(CASE WHEN rp.status = 1 THEN rp.qty ELSE 0 END), 0) AS qty_terkirim,
                GROUP_CONCAT(DISTINCT NULLIF(rp.no_po, '') ORDER BY rp.no_po SEPARATOR ', ') AS daftar_po,
                COALESCE(SUM(CASE WHEN rp.status = 1 AND COALESCE(NULLIF(rp.no_btb, ''), '') = '' AND COALESCE(rp.btb_file, '') = '' THEN 1 ELSE 0 END), 0) AS item_tanpa_btb
            ", false)
            ->join('users', 'users.id = pp.iduser', 'left')
            ->join('rencana_pengiriman rp', 'rp.permintaan_id = pp.id', 'left')
            ->join('barangkeluar bk', "bk.faktur = NULLIF(rp.no_do, '')", 'left')
            ->groupBy("pp.id, pp.tanggal, pp.keterangan, users.usernama, COALESCE(NULLIF(rp.no_do, ''), CONCAT('__draft_', pp.id))")
            ->orderBy('pp.tanggal', 'DESC')
            ->orderBy('pp.id', 'DESC')
            ->orderBy('grup_do', 'ASC')
            ->get()
            ->getResultArray();

        $mappedRows = array_map(static function (array $row): array {
            $total = (int) $row['total_grup'];
            $rencana = (int) $row['qty_rencana'];
            $terkirim = (int) $row['qty_terkirim'];
            $belum = max($total - $terkirim, 0);
            $status = 'Kosong';
            $statusClass = 'badge-secondary';

            if ($terkirim >= $total && $total > 0) {
                $status = 'Terkirim';
                $statusClass = 'badge-success';
            } elseif ($terkirim > 0) {
                $status = 'Sebagian';
                $statusClass = 'badge-info';
            } elseif ($rencana > 0) {
                // Sudah di-"Simpan" (ada rencana_pengiriman) tapi belum pernah
                // "Save dan Kirim" -- belum ada barangkeluar beneran, jadi
                // Edit-nya harus lanjut ke Input Pengiriman, bukan ke edit faktur.
                $status = 'Draft';
                $statusClass = 'badge-warning';
            } elseif ($total > 0) {
                $status = 'Permintaan';
                $statusClass = 'badge-primary';
            }

            $noDo = trim((string) ($row['grup_do'] ?? ''));
            $daftarPo = trim((string) ($row['daftar_po'] ?? ''));
            $kirimLangsung = ($row['keterangan'] ?? '') === 'Kirim langsung';

            if ($status === 'Draft') {
                // Kirim Langsung: draft-nya dikelola di halaman Input Pengiriman.
                // Buat Permintaan bertahap: draft-nya dikelola di halaman Proses
                // Permintaan yang sama seperti biasa (sudah otomatis nunjukin
                // rencana yang belum dikirim), jadi tombolnya arahnya beda.
                $tombolEdit = $kirimLangsung
                    ? '<button type="button" class="btn btn-sm btn-primary" title="Lanjutkan Input Pengiriman" onclick="lanjutkanPengiriman(\'' . sha1((int) $row['id']) . '\')"><i class="fa fa-edit"></i></button>'
                    : '<a class="btn btn-sm btn-primary" title="Lanjutkan Proses Permintaan" href="/permintaanPengiriman/proses/' . sha1((int) $row['id']) . '"><i class="fa fa-edit"></i></a>';
                $tombolEditDokumen = '<button type="button" class="btn btn-sm btn-secondary" title="Belum ada surat jalan resmi" disabled><i class="fa fa-file-signature"></i></button>';
                $tombolCetakDo = '<button type="button" class="btn btn-sm btn-secondary" title="Belum ada surat jalan resmi" disabled><i class="fa fa-print"></i></button>';
            } else {
                // Sekarang $noDo itu punya baris ini sendiri (bukan lagi
                // "yang pertama dari daftar gabungan"), jadi tombol Edit
                // selalu ngebuka surat jalan yang beneran sesuai barisnya.
                $tombolEdit = $noDo !== ''
                    ? '<button type="button" class="btn btn-sm btn-primary" title="Edit Pengiriman" onclick="edit(\'' . sha1($noDo) . '\')"><i class="fa fa-edit"></i></button>'
                    : '<button type="button" class="btn btn-sm btn-secondary" title="Belum ada surat jalan" disabled><i class="fa fa-edit"></i></button>';
                $itemTanpaBtb = (int) ($row['item_tanpa_btb'] ?? 0);
                $badgeBtb = $itemTanpaBtb > 0
                    ? '<span class="badge badge-danger badge-pill" style="position:absolute; top:-6px; right:-6px; font-size:10px; color:#fff;">' . $itemTanpaBtb . '</span>'
                    : '';
                $tombolEditDokumen = $noDo !== ''
                    ? '<button type="button" class="btn btn-sm btn-warning position-relative" title="Dokumen BTB' . ($itemTanpaBtb > 0 ? ' -- ' . $itemTanpaBtb . ' item belum diisi' : '') . '" onclick="editDokumenPengiriman(\'' . sha1($noDo) . '\')"><i class="fa fa-file-signature"></i>' . $badgeBtb . '</button>'
                    : '<button type="button" class="btn btn-sm btn-secondary" title="Belum ada surat jalan" disabled><i class="fa fa-file-signature"></i></button>';
                $tombolCetakDo = $noDo !== ''
                    ? '<button type="button" class="btn btn-sm btn-info" title="Lihat Delivery Order" onclick="lihatDo(\'' . sha1($noDo) . '\')"><i class="fa fa-eye"></i></button>'
                    . '<button type="button" class="btn btn-sm btn-primary" title="Print Delivery Order" onclick="cetakDo(\'' . sha1($noDo) . '\')"><i class="fa fa-print"></i></button>'
                    : '<button type="button" class="btn btn-sm btn-secondary" title="Belum ada surat jalan" disabled><i class="fa fa-print"></i></button>';
            }

            if (!\App\Libraries\AccessControl::can('produk.keluar.delete')) {
                $tombolHapus = '';
            } elseif ($noDo !== '') {
                // Ada No Surat Jalan-nya -- hapus scoped ke surat jalan INI
                // aja (hapusSuratJalanLangsung), bukan seluruh sesi. Sesi
                // Input Pengiriman yang sama sekarang boleh berisi beberapa
                // No Surat Jalan sekaligus (HOTFIX128), jadi hapus per-ID
                // permintaan yang lama bisa ikut ngehapus surat jalan lain
                // yang harusnya nggak disentuh.
                $tombolHapus = '<button type="button" class="btn btn-sm btn-danger" title="Hapus Surat Jalan ini" onclick="hapusSuratJalan(\'' . sha1($noDo) . '\')"><i class="fa fa-trash-alt"></i></button>';
            } else {
                // Belum ada No Surat Jalan (masih Draft) -- nggak ada yang
                // bisa di-scope, jadi tetap hapus seluruh sesi/permintaan.
                $tombolHapus = '<button type="button" class="btn btn-sm btn-danger" title="Hapus (seluruh permintaan ini)" onclick="hapusPengirimanLangsung(' . (int) $row['id'] . ')"><i class="fa fa-trash-alt"></i></button>';
            }

            $tanggalTampil = trim((string) ($row['tglfaktur_asli'] ?? '')) !== ''
                ? (string) $row['tglfaktur_asli']
                : (string) $row['tanggal'];

            return [
                'id' => (int) $row['id'],
                'hash' => sha1((int) $row['id']),
                'tanggal_sort' => $tanggalTampil,
                'tanggal' => date('d-m-Y', strtotime($tanggalTampil)),
                'status' => $status,
                'status_badge' => '<span class="badge ' . $statusClass . '">' . $status . '</span>',
                'dokumen' => $noDo !== '' ? $noDo : '-',
                'po' => $daftarPo !== '' ? $daftarPo : '-',
                'total_produk' => number_format($total, 0, ',', '.'),
                'rencana' => number_format($rencana, 0, ',', '.'),
                'terkirim' => number_format($terkirim, 0, ',', '.'),
                'belum' => number_format($belum, 0, ',', '.'),
                'usernama' => $row['usernama'] ?: '-',
                'keterangan' => $row['keterangan'] ?: '-',
                'aksi' => '<div class="d-flex justify-content-center align-items-center" style="gap:4px;">' . $tombolEdit . $tombolEditDokumen . $tombolCetakDo . $tombolHapus . '</div>',
            ];
        }, $rows);

        // Yang belum ada rencana maupun kiriman sama sekali (status Permintaan/Kosong)
        // bukan urusan tab List Pengiriman -- itu ditampilkan di tab List Permintaan.
        $mappedRows = array_values(array_filter($mappedRows, static function (array $row): bool {
            return !in_array($row['status'], ['Permintaan', 'Kosong'], true);
        }));

        if ($tglawal !== '' && $tglakhir !== '') {
            $mappedRows = array_values(array_filter($mappedRows, static function (array $row) use ($tglawal, $tglakhir): bool {
                return $row['tanggal_sort'] >= $tglawal && $row['tanggal_sort'] <= $tglakhir;
            }));
        }

        $recordsTotal = count($mappedRows);
        if ($search !== '') {
            $mappedRows = array_values(array_filter($mappedRows, static function (array $row) use ($search): bool {
                return str_contains(strtolower(implode(' ', [
                    $row['tanggal'],
                    $row['status'],
                    $row['dokumen'],
                    $row['po'],
                    $row['usernama'],
                    $row['keterangan'],
                ])), $search);
            }));
        }

        $recordsFiltered = count($mappedRows);
        $pagedRows = array_slice($mappedRows, $start, $length);
        foreach ($pagedRows as $index => &$row) {
            $row['nomor'] = $start + $index + 1;
        }
        unset($row);

        return $this->response->setJSON([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $pagedRows,
        ]);
    }

    public function cetakDo(string $hash)
    {
        $db = \Config\Database::connect();
        $header = $db->query(
            "SELECT bk.faktur, bk.detpo, bk.tglfaktur, bk.idpel, bk.qtykeluar, bk.totalberatbarang,
                    p.pelnama, p.pelpic, p.peltelp, p.pelemail, p.pelalamat,
                    g.gdgnama,
                    po.tglpo
             FROM barangkeluar bk
             JOIN pelanggan p ON p.pelid = bk.idpel
             LEFT JOIN gudang g ON g.gdgid = bk.gudang
             LEFT JOIN po ON po.nopo = bk.detpo
             WHERE SHA1(bk.faktur) = ?",
            [$hash]
        )->getRowArray();

        if (!$header) {
            return $this->response->setStatusCode(404, 'Data tidak ditemukan');
        }

        $details = $this->getDetailDoRows((string) $header['faktur']);

        // 1 Surat Jalan sekarang boleh nampung item dari beberapa No. PO --
        // ambil semua No. PO yang tercakup (bukan cuma header.detpo yang
        // cuma nyimpen PO pertama) buat ditampilin di kop surat.
        $daftarPo = $db->table('detail_barangkeluar')
            ->distinct()
            ->select('detpo')
            ->where('detfaktur', (string) $header['faktur'])
            ->where('detpo IS NOT NULL', null, false)
            ->where('detpo !=', '')
            ->orderBy('detpo', 'ASC')
            ->get()
            ->getResultArray();
        $header['daftar_po'] = array_column($daftarPo, 'detpo') ?: [$header['detpo']];

        $format = strtolower(trim((string) $this->request->getGet('format')));
        if (!in_array($format, ['lengkap', 'ringkas'], true)) {
            $format = 'lengkap';
        }

        return view('barangkeluar/cetakdo', [
            'header' => $header,
            'details' => $details,
            'format' => $format,
            'noKendaraan' => trim((string) $this->request->getGet('kendaraan')),
            'pengirimBarang' => trim((string) ($this->request->getGet('pengirimBarang') ?? $this->request->getGet('pengirim'))),
            'penerimaBarang' => trim((string) $this->request->getGet('penerimaBarang')),
            'notes' => json_decode((string) $this->request->getGet('notes'), true) ?: [],
            'preview' => $this->request->getGet('preview') === '1',
        ]);
    }

    public function detailDo(string $hash)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $db = \Config\Database::connect();
        $header = $db->query(
            "SELECT bk.faktur, p.pelnama
             FROM barangkeluar bk
             JOIN pelanggan p ON p.pelid = bk.idpel
             WHERE SHA1(bk.faktur) = ?",
            [$hash]
        )->getRowArray();

        if (!$header) {
            return $this->response->setJSON([
                'error' => 'Data DO tidak ditemukan.'
            ]);
        }

        return $this->response->setJSON([
            'details' => array_map(static function (array $row): array {
                return [
                    'id' => (string) $row['id'],
                    'kode' => (string) $row['detbrgkode'],
                    'nama' => (string) $row['namabarang'],
                    'qty' => number_format((float) $row['detjml'], 0, ',', '.'),
                ];
            }, $this->getDetailDoRows((string) $header['faktur']))
        ]);
    }

    private function getDetailDoRows(string $faktur): array
    {
        $db = \Config\Database::connect();

        return $db->query(
            "SELECT dk.id, dk.detbrgkode, COALESCE(b.brgnama, dk.namabarang) AS namabarang,
                    dk.detjml, COALESCE(s.satnama, 'Pcs') AS satnama,
                    m.matkode, m.matnama
             FROM detail_barangkeluar dk
             LEFT JOIN barang b ON b.brgkode = dk.detbrgkode
             LEFT JOIN satuan s ON s.satid = b.brgsatid
             LEFT JOIN material m ON m.matid = dk.material
             WHERE dk.detfaktur = ?
             ORDER BY dk.id ASC",
            [$faktur]
        )->getResultArray();
    }

    public function input()
    {
        $modelgudang = new Modelgudang();
        $modelpo = new Modeldetailpo();
        $data = [
            'datapo' => $modelpo->findAll(),
            'datagudang' => $modelgudang->findAll(),
        ];
        return view('barangkeluar/forminput', $data);
    }

    public function tampilDataTemp()
    {
        if ($this->request->isAJAX()) {
            $nopo = $this->request->getPost('nopo');

            $modalDetail = new Modeldetailpo();
            $dataTemp = $modalDetail->tampilDataTemp($nopo);

            $data = [
                'tampildata' => $dataTemp,
            ];

            $json = [
                'data' => view('barangkeluar/datatemp', $data)
            ];
            echo json_encode($json);
        }
    }

    public function tampilDataTempKeluar()
    {
        if ($this->request->isAJAX()) {
            $nofaktur = $this->request->getPost('nofaktur');

            $modalTempBarangKeluar = new ModelTempBarangKeluar();

            $dataTempKeluar = $modalTempBarangKeluar->tampilDataTemp($nofaktur);
            $data = [
                'tampildatakeluar' => $dataTempKeluar
            ];

            $json = [
                'data' => view('barangkeluar/datatempkeluar', $data)
            ];
            echo json_encode($json);
        }
    }

    public function listDataPo()
    {
        if ($this->request->isAJAX()) {
            $db = \Config\Database::connect();
            $builder = $db->table('outstanding o')
                ->select('o.nopo, o.kodebrg, o.idbarang, o.tgl, o.qty, o.terkirim, o.kekurangan, o.idpel, p.pelnama, p.gdgid')
                ->join('pelanggan p', 'p.pelid = o.idpel')
                ->where('o.kekurangan !=', 0)
                ->orderBy('o.nopo', 'ASC')
                ->orderBy('o.kodebrg', 'ASC');

            return DataTable::of($builder)
                ->addNumbering('nomor')
                ->setSearchableColumns(['o.nopo', 'o.tgl', 'o.kodebrg'])
                ->add('aksi', function ($row) {
                    $nopo = htmlspecialchars((string) $row->nopo, ENT_QUOTES, 'UTF-8');
                    $idPelanggan = htmlspecialchars((string) $row->idpel, ENT_QUOTES, 'UTF-8');
                    $namaPelanggan = htmlspecialchars((string) $row->pelnama, ENT_QUOTES, 'UTF-8');
                    $gudangPelanggan = htmlspecialchars((string) ($row->gdgid ?? ''), ENT_QUOTES, 'UTF-8');

                    return "<button type=\"button\" class=\"btn btn-sm btn-info\" title=\"Pilih\"
                        data-nopo=\"{$nopo}\"
                        data-id-pelanggan=\"{$idPelanggan}\"
                        data-nama-pelanggan=\"{$namaPelanggan}\"
                        data-gudang-pelanggan=\"{$gudangPelanggan}\"
                        onclick=\"pilih(this.dataset.nopo, this.dataset.idPelanggan, this.dataset.namaPelanggan, this.dataset.gudangPelanggan)\">
                        Pilih
                    </button>";
                })->format('qty', function ($value) {
                    return number_format((float) $value, 0, ',', '.');
                })->format('terkirim', function ($value) {
                    return number_format((float) $value, 0, ',', '.');
                })->format('kekurangan', function ($value) {
                    $formattedValue = number_format(abs($value), 0, ",", ".");

                    if ($value < 0) {
                        return '<span style="color: red;">' . $formattedValue . '</span>';
                    }

                    return $formattedValue;
                })
                ->format('tgl', function ($value) {
                    return date('d-m-Y', strtotime($value));
                })
                ->toJson(true);
        }
    }

    public function ambilDataBarang()
    {
        if ($this->request->isAJAX()) {
            $kodebarang = $this->request->getPost('kodebarang');
            $idgudang = $this->request->getPost('idgudang');
            $nofaktur = $this->request->getPost('nofaktur');
            $nopo = $this->request->getPost('nopo');

            $modelStok = new Modelstok();
            $modelBarang = new Modelbarang();
            $modelBerat = new Modelberat();

            $cekNama = $modelBarang->find($kodebarang);
            $cekDataBerat = $modelBerat->find($kodebarang);

            if ($cekNama == null) {
                $json = [
                    'error' => 'Maaf data barang tidak ditemukan'
                ];
            } else if ($cekDataBerat == null && !$this->produkTanpaBerat($kodebarang)) {
                $json = [
                    'error' => 'Maaf data Berat/Ukuran Bersih untuk produk ini belum diisi'
                ];
            } else {
                // Kalau lagi memproses PO tertentu, pakai data yang tersimpan
                // di detail_po (kesepakatan awal PO itu) supaya tidak
                // kebingungan kalau nama/material produk diedit belakangan.
                // Stok tetap dibaca live karena itu memang harus terkini.
                $detailPo = null;
                if (!empty($nopo)) {
                    $detailPo = db_connect()->table('detail_po')
                        ->where('detnopo', $nopo)
                        ->where('detkodebrg', $kodebarang)
                        ->get()->getRowArray();
                }

                $namabarang = $detailPo['namabarang'] ?? $cekNama['brgnama'];
                $material = $detailPo['material'] ?? $cekNama['brgmat'];
                $berat = $detailPo['detberat'] ?? ($cekDataBerat['berat'] ?? 0);

                $stokGudang = $modelStok->getStokByGudang($kodebarang, $idgudang);
                $idBarang = $modelStok->getBarangIdByKodeGudang($kodebarang, $idgudang);
                $qtySementara = 0;

                if (!empty($nofaktur)) {
                    $temp = db_connect()
                        ->table('temp_barangkeluar')
                        ->selectSum('detjml')
                        ->where('detfaktur', $nofaktur)
                        ->where('detbrgkode', $kodebarang)
                        ->where('gudang', $idgudang)
                        ->get()
                        ->getRowArray();

                    $qtySementara = intval($temp['detjml'] ?? 0);
                }

                $data = [
                    'namabarang' => $namabarang,
                    'idmaterial' => $this->materialUtamaProduk($material),
                    'berat' => $berat,
                    'stok' => max(0, intval($stokGudang) - $qtySementara),
                    'idbarang' => $idBarang
                ];

                $json = [
                    'sukses' => $data
                ];
            }
            echo json_encode($json);
        }
    }

    function simpanItem()
    {
        if ($this->request->isAJAX()) {
            $nofaktur = $this->request->getPost('nofaktur');
            $tglfaktur = $this->request->getPost('tglfaktur');
            $nopo = $this->request->getPost('nopo');
            $kodebarang = $this->request->getPost('kodebarang');
            $namabarang = $this->request->getPost('namabarang');
            $idgudang = $this->request->getPost('idgudang');
            $idbarang = $this->request->getPost('idbarang');
            $idpelanggan = $this->request->getPost('idpelanggan');
            $idmaterial = $this->request->getPost('idmaterial');
            $berat = $this->request->getPost('detberat');
            $detqty = $this->request->getPost('detqty');
            $kirim = $this->request->getPost('kirim');
            $stok = $this->request->getPost('stok');
            $jml = $this->request->getPost('jml');

            $modelTempBarangKeluar = new ModelTempBarangKeluar();
            $gudangPelanggan = $this->gudangPelanggan($idpelanggan);

            if ($gudangPelanggan === null) {
                return $this->response->setJSON([
                    'error' => 'Gudang pelanggan belum ditentukan pada master pelanggan'
                ]);
            }

            $idgudang = $gudangPelanggan;
            $stokGudang = (new Modelstok())->getStokByGudang($kodebarang, $idgudang);

            $validation = \Config\Services::validation();

            $valid = $this->validate([
                'nofaktur' => [
                    'rules' => 'required|is_unique[barangkeluar.faktur]',
                    'label' => 'No Surat Jalan',
                    'errors' => [
                        'required' => '{field} tidak boleh kosong',
                        'is_unique' => '{field} sudah terpakai'
                    ]
                ],
                'kodebarang' => [
                    'rules' => 'required',
                    'label' => 'Kode barang',
                    'errors' => [
                        'required' => '{field} tidak boleh kosong',
                    ]
                ],
                'namabarang' => [
                    'rules' => 'required',
                    'label' => 'Nama barang',
                    'errors' => [
                        'required' => '{field} tidak boleh kosong',
                    ]
                ],
            ]);
            if (!$valid) {
                $json = [
                    'error' => '' . $validation->listErrors() . ''
                ];
            } else if ($jml > intval($stokGudang)) {
                $json = [
                    'error1' => 'Stok tidak mencukupi'
                ];
            } else if ($jml <= 0) {
                $json = [
                    'error2' => 'Qty harus lebih besar dari 0'
                ];
            } else if ($jml > intval($detqty)) {
                $json = [
                    'error3' => 'Qty tidak sesuai dengan PO'
                ];
            } else if ($jml > intval($kirim)) {
                $json = [
                    'error4' => 'Qty melebihi sisa PO'
                ];
            } else {
                $existingRow = $modelTempBarangKeluar
                    ->where('idbarang', $idbarang)
                    ->where('detfaktur', $nofaktur)
                    ->where('detpo', $nopo)
                    ->first();

                if ($existingRow) {
                    $newJml = $existingRow['detjml'] + $jml;
                    $newSubtotal = $existingRow['detsubtotal'] + ($jml * $berat);

                    if ($newJml > intval($stokGudang)) {
                        return $this->response->setJSON([
                            'error1' => 'Stok tidak mencukupi'
                        ]);
                    }

                    $modelTempBarangKeluar->update($existingRow['id'], [
                        'detjml' => $newJml,
                        'detsubtotal' => $newSubtotal
                    ]);
                } else {
                    $modelTempBarangKeluar->insert([
                        'detfaktur' => $nofaktur,
                        'tgl' => $tglfaktur,
                        'detpo' => $nopo,
                        'detidpel' => $idpelanggan,
                        'detbrgkode' => $kodebarang,
                        'namabarang' => $namabarang,
                        'material' => $idmaterial,
                        'idbarang' => $idbarang,
                        'detberat' => $berat,
                        'gudang' => $idgudang,
                        'detjml' => $jml,
                        'gudang' => $idgudang,
                        'detsubtotal' => $jml * $berat
                    ]);
                }
                // echo '<pre>';
                // print_r($modelTempBarangKeluar->getLastQuery()->getQuery());
                // echo '</pre>';
                // die();
                $modelOutstanding = new Modeloutstand();
                $modelOutstanding->updateDetailOutstandingKurang();

                $json = [
                    'sukses' => 'Item Berhasil di Tambahkan',
                    'stokSisa' => intval($stokGudang) - intval($existingRow['detjml'] ?? 0) - intval($jml)
                ];
            }

            echo json_encode($json);
        }
    }

    function hapusItem()
    {
        if ($this->request->isAJAX()) {
            $id = $this->request->getPost('id');

            $modelTempBarangKeluar = new ModelTempBarangKeluar();
            $modelTempBarangKeluar->delete($id);

            $json = [
                'sukses' => 'Item Berhasil di Hapus'
            ];
            echo json_encode($json);
        }
    }

    public function modalCariBarang()
    {
        if ($this->request->isAJAX()) {
            $json = [
                'data' => view('barangkeluar/modalcaribarang')
            ];
            echo json_encode($json);
        }
    }

    public function modalData()
    {
        if ($this->request->isAJAX()) {
            $json = [
                'data' => view('barangkeluar/modaldata')
            ];
            echo json_encode($json);
        }
    }

    function selesaiTransaksi()
    {
        if ($this->request->isAJAX()) {
            $nofaktur = $this->request->getPost('nofaktur');
            $nopo = $this->request->getPost('nopo');
            $tglfaktur = $this->request->getPost('tglfaktur');
            $idpelanggan = $this->request->getPost('idpelanggan');
            $idgudang = $this->request->getPost('idgudang');
            $totalberatbarang = $this->request->getPost('totalberatbarang');
            $gudangPelanggan = $this->gudangPelanggan($idpelanggan);

            if ($gudangPelanggan === null) {
                return $this->response->setJSON([
                    'error' => 'Gudang pelanggan belum ditentukan pada master pelanggan'
                ]);
            }

            $idgudang = $gudangPelanggan;

            $modelTemp = new ModelTempBarangKeluar();
            $dataTemp = $modelTemp->getWhere(['detfaktur' => $nofaktur]);

            if ($dataTemp->getNumRows() == 0) {
                $json = [
                    'error' => 'Maaf, data item untuk faktur ini belum ada'
                ];
            } else {
                $db = db_connect();
                $db->transStart();

                $modelBarangKeluar = new ModelBarangKeluar();
                $totalSubTotal = 0;
                foreach ($dataTemp->getResultArray() as $total) :
                    $totalSubTotal += intval($total['detsubtotal']);
                endforeach;

                $totalqtykeluar = 0;
                foreach ($dataTemp->getResultArray() as $totqty) :
                    $totalqtykeluar += intval($totqty['detjml']);
                endforeach;

                $fieldDetail = [];
                foreach ($dataTemp->getResultArray() as $row) {
                    $fieldDetail[] = [
                        'detfaktur' => $row['detfaktur'],
                        'tgl' => $row['tgl'],
                        'detpo' => $row['detpo'],
                        'detidpel' => $row['detidpel'],
                        'detbrgkode' => $row['detbrgkode'],
                        'namabarang' => $row['namabarang'],
                        'material' => $row['material'],
                        'idbarang' => $row['idbarang'],
                        'detberat' => $row['detberat'],
                        'detjml' => $row['detjml'],
                        'gudang' => $row['gudang'],
                        'detsubtotal' => $row['detsubtotal']
                    ];
                }

                /*
                 * Stok produk sudah dikurangi oleh trigger database ketika
                 * detail_barangkeluar disimpan. Controller hanya perlu
                 * memastikan stok masih mencukupi sebelum insert, supaya stok
                 * tidak berkurang dua kali.
                 */
                foreach ($fieldDetail as $detail) {
                    $stokTersedia = $db->table('stok')
                        ->select('stok')
                        ->where('id', $detail['idbarang'])
                        ->where('kodebarang', $detail['detbrgkode'])
                        ->where('gudang', $detail['gudang'])
                        ->get()
                        ->getRowArray();

                    if (!$stokTersedia || (int) $stokTersedia['stok'] < (int) $detail['detjml']) {
                        $db->transRollback();

                        return $this->response->setJSON([
                            'error' => 'Stok ' . $detail['detbrgkode'] . ' tidak mencukupi di gudang asal'
                        ]);
                    }
                }
                // echo '<pre>';
                // print_r($fieldDetail);
                // echo '</pre>';
                // die();
                // Cari data di tabel outstanding berdasarkan kodebrg dan nopo
                $modelOutstanding = new Modeloutstand();
                foreach ($fieldDetail as $detailOutstanding) {
                    $outstandingData = $modelOutstanding->where(['kodebrg' => $detailOutstanding['detbrgkode'], 'nopo' => $detailOutstanding['detpo']])->first();

                    if ($outstandingData) {
                        $outstandingData['terkirim'] += $detailOutstanding['detjml'];
                        $modelOutstanding->save($outstandingData);
                    } else {
                        $modelOutstanding->insert([
                            'kodebrg' => $detailOutstanding['detbrgkode'],
                            'nopo' => $detailOutstanding['detpo'],
                            'terkirim' => $detailOutstanding['detjml'],
                        ]);
                    }
                }
                $modelOutstanding->updateDetailOutstandingKurang();

                $modelBarangKeluar->insert([
                    'faktur' => $nofaktur,
                    'detpo' => $nopo,
                    'tglfaktur' => $tglfaktur,
                    'idpel' => $idpelanggan,
                    'qtykeluar' => $totalqtykeluar,
                    'totalberatbarang' => $totalberatbarang,
                    'gudang' => $idgudang,
                ]);

                $modelDetail = new ModelDetailBarangKeluar();
                $modelDetail->insertBatch($fieldDetail);

                $myModel = new Modeldetailpo();
                $myModel->updateDetailPoKurang();
                (new Modeloutstand())->sinkronByPoList([$nopo]);

                $modelTemp->hapusData($nofaktur);
                $db->transComplete();

                if ($db->transStatus() === false) {
                    return $this->response->setJSON([
                        'error' => 'Transaksi gagal disimpan. Stok tidak berubah.'
                    ]);
                }

                $json = [
                    'sukses' => 'Transaksi Berhasil di Simpan'
                ];
            }

            // // Integrasi Pusher
            // $options = array(
            //     'cluster' => 'ap1',
            //     'useTLS' => true
            // );
            // $pusher = new \Pusher\Pusher(
            //     '8f027ac11961f0fa1906',
            //     '21c84fc4aee41d737c58',
            //     '1826619',
            //     $options
            // );

            // $data['message'] = 'success';
            // $pusher->trigger('my-channel', 'my-event', $data);

            echo json_encode($json);
        } else {
            exit('Maaf tidak bisa dipanggil');
        }
    }

    private function gudangPelanggan($idPelanggan): ?string
    {
        $pelanggan = db_connect()
            ->table('pelanggan')
            ->select('gdgid')
            ->where('pelid', $idPelanggan)
            ->get()
            ->getRowArray();

        if (!$pelanggan || empty($pelanggan['gdgid'])) {
            return null;
        }

        return (string) $pelanggan['gdgid'];
    }

    private function snapshotPemulihanStok(array $detailRows): array
    {
        $db = db_connect();
        $snapshots = [];

        foreach ($detailRows as $detail) {
            $builder = $db->table('stok')
                ->where('kodebarang', $detail['detbrgkode'])
                ->where('gudang', $detail['gudang']);

            if (!empty($detail['idbarang'])) {
                $builder->where('id', $detail['idbarang']);
            }

            $stok = $builder->get()->getRowArray();
            if (!$stok) {
                return [
                    'error' => 'Stok ' . $detail['detbrgkode'] . ' tidak ditemukan di gudang asal',
                    'data' => [],
                ];
            }

            $stokId = (int) $stok['id'];
            if (!isset($snapshots[$stokId])) {
                $snapshots[$stokId] = [
                    'id' => $stokId,
                    'kode' => $detail['detbrgkode'],
                    'stok_awal' => (int) $stok['stok'],
                    'qty' => 0,
                ];
            }

            $snapshots[$stokId]['qty'] += (int) $detail['detjml'];
        }

        return [
            'error' => null,
            'data' => $snapshots,
        ];
    }

    private function pulihkanStokSetelahHapus(array $stokSnapshots): ?string
    {
        $db = db_connect();

        foreach ($stokSnapshots as $snapshot) {
            $stok = $db->table('stok')
                ->where('id', $snapshot['id'])
                ->get()
                ->getRowArray();

            if (!$stok) {
                return 'Stok ' . $snapshot['kode'] . ' tidak ditemukan saat pemulihan stok';
            }

            $stokSeharusnya = (int) $snapshot['stok_awal'] + (int) $snapshot['qty'];

            if ((int) $stok['stok'] !== $stokSeharusnya) {
                $db->table('stok')
                    ->where('id', $snapshot['id'])
                    ->update(['stok' => $stokSeharusnya]);
            }
        }

        return null;
    }

    /**
     * Hapus 1 transaksi pengiriman (barangkeluar + detail_barangkeluar) by
     * faktur/no_do: balikin stok, hapus rencana_pengiriman yang pakai DO
     * itu, sinkronkan outstanding/detail_po/status permintaan terkait.
     * Dipakai bareng oleh hapusTransaksi() (tab Cetak Surat Jalan) dan
     * hapusPengirimanLangsung() (tab List Pengiriman) -- transaksi DB
     * dikelola oleh pemanggil, bukan method ini.
     *
     * @return string|null null kalau sukses, pesan error kalau gagal.
     */
    private function hapusBarangKeluarByFaktur(string $faktur): ?string
    {
        $this->ensureDariMigrasiColumn();

        $db = \Config\Database::connect();
        $detailTerdampak = $db->table('detail_barangkeluar')
            ->select('detpo, idbarang, detbrgkode, gudang, detjml, dari_migrasi')
            ->where('detfaktur', $faktur)
            ->get()
            ->getResultArray();

        if (!$detailTerdampak) {
            return null;
        }

        $poTerdampak = array_column($detailTerdampak, 'detpo');

        // Baris yang asalnya dari qty migrasi PO gak pernah motong stok pas
        // dibuat (lihat PermintaanPengiriman::eksekusiKirim()), jadi pas
        // dihapus juga gak boleh nambah stok -- yang perlu dibalikin itu
        // jatah migrasinya di detail_po, bukan stoknya.
        $detailStokNormal = array_values(array_filter($detailTerdampak, static fn($row) => (int) ($row['dari_migrasi'] ?? 0) === 0));
        $detailMigrasi = array_values(array_filter($detailTerdampak, static fn($row) => (int) ($row['dari_migrasi'] ?? 0) === 1));

        $snapshotStok = $this->snapshotPemulihanStok($detailStokNormal);
        if ($snapshotStok['error'] !== null) {
            return $snapshotStok['error'];
        }

        $db->table('detail_barangkeluar')->delete(['detfaktur' => $faktur]);
        (new ModelBarangKeluar())->delete($faktur);

        // PENTING: delete di atas otomatis NAMBAH stok lewat trigger DB
        // (tri_delete_detail_stok DAN tri_delete_detailBarangKeluar),
        // TERLEPAS baris itu tadinya dari_migrasi atau bukan. Baris migrasi
        // gak pernah motong stok pas dibuat, jadi penambahan otomatis ini
        // salah buat baris itu -- batalkan lagi di `stok` per-gudang SAJA.
        // Trigger tri_data_barang_update di tabel `stok` otomatis nyinkronin
        // ulang `barang.brgstok` (=SUM stok semua gudang) tiap kali baris
        // `stok` di-UPDATE, jadi JANGAN update `barang.brgstok` manual juga
        // di sini, nanti kompensasinya kehitung dobel.
        foreach ($detailMigrasi as $row) {
            if (!empty($row['idbarang'])) {
                $db->table('stok')
                    ->where('id', $row['idbarang'])
                    ->set('stok', 'stok - ' . (float) $row['detjml'], false)
                    ->update();
            }
        }

        // Hapus rencana pengiriman yang menggunakan DO yang dibatalkan.
        $db->table('rencana_pengiriman')
            ->where('no_do', $faktur)
            ->delete();

        $errorStok = $this->pulihkanStokSetelahHapus($snapshotStok['data']);
        if ($errorStok !== null) {
            return $errorStok;
        }

        if ($detailMigrasi) {
            $modelDetailPo = new Modeldetailpo();
            $tambahanMigrasi = [];
            foreach ($detailMigrasi as $row) {
                $key = $row['detpo'] . '||' . $row['detbrgkode'];
                $tambahanMigrasi[$key] = ($tambahanMigrasi[$key] ?? 0) + (float) $row['detjml'];
            }

            foreach ($tambahanMigrasi as $key => $qty) {
                [$noPo, $kodeBarang] = explode('||', $key, 2);
                $detailPo = $modelDetailPo
                    ->where('detnopo', $noPo)
                    ->where('detkodebrg', $kodeBarang)
                    ->first();

                if ($detailPo) {
                    $modelDetailPo->update($detailPo['id'], [
                        'detkirim_awal' => (float) ($detailPo['detkirim_awal'] ?? 0) + $qty,
                    ]);
                }
            }
        }

        $modelOutstanding = new Modeloutstand();
        $modelOutstanding->sinkronByPoList($poTerdampak);
        $this->sinkronStatusPermintaanPengirimanByPo($poTerdampak);

        (new Modeldetailpo())->updateDetailPoKurang();
        $modelOutstanding->updateDetailOutstandingKurang();

        return null;
    }

    function hapusTransaksi()
    {
        if ($this->request->isAJAX()) {
            $faktur = $this->request->getPost('faktur');
            $db = \Config\Database::connect();

            $db->transBegin();
            $error = $this->hapusBarangKeluarByFaktur($faktur);
            if ($error !== null) {
                $db->transRollback();
                echo json_encode(['error' => $error]);
                return;
            }
            $db->transCommit();

            echo json_encode(['sukses' => 'Transaksi berhasil di Hapus']);
        }
    }

    /**
     * Hapus 1 baris di tab List Pengiriman -- baik "Kirim langsung" maupun
     * "Buat Permintaan" bertahap (termasuk yang statusnya "Sebagian", udah
     * ada barang yang benar-benar terkirim). Semua DO yang tercatat di
     * rencana_pengiriman buat permintaan ini dibalikin lewat
     * hapusBarangKeluarByFaktur() (aman dipanggil meski DO itu ternyata
     * belum ada transaksi barangkeluar-nya -- langsung no-op). Beda dari
     * hapusTransaksi() (yang cuma hapus DO/barangkeluar-nya) -- ini juga
     * ikut hapus header permintaan_pengiriman-nya, biar gak nyisain baris
     * "hantu" (status Permintaan, tanpa No Surat Jalan) di tab List
     * Pengiriman kayak yang kejadian kalau hapus cuma lewat tab satunya.
     */
    public function hapusPengirimanLangsung()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $id = (int) $this->request->getPost('id');
        $db = \Config\Database::connect();

        $header = $db->table('permintaan_pengiriman')->where('id', $id)->get()->getRowArray();
        if (!$header) {
            return $this->response->setJSON(['error' => 'Data pengiriman tidak ditemukan.']);
        }

        $rencanaRows = $db->table('rencana_pengiriman')
            ->select('no_do')
            ->where('permintaan_id', $id)
            ->get()
            ->getResultArray();
        $noDoList = array_values(array_unique(array_filter(array_column($rencanaRows, 'no_do'))));

        $db->transBegin();

        foreach ($noDoList as $noDo) {
            $error = $this->hapusBarangKeluarByFaktur($noDo);
            if ($error !== null) {
                $db->transRollback();
                return $this->response->setJSON(['error' => $error]);
            }
        }

        $db->table('detail_permintaan_pengiriman')->where('permintaan_id', $id)->delete();
        $db->table('rencana_pengiriman')->where('permintaan_id', $id)->delete();
        $db->table('permintaan_pengiriman')->where('id', $id)->delete();

        $db->transCommit();

        return $this->response->setJSON(['sukses' => 'Pengiriman berhasil dihapus, stok sudah dikembalikan.']);
    }

    /**
     * Hapus 1 No Surat Jalan SAJA -- beda dari hapusPengirimanLangsung()
     * yang menghapus SELURUH sesi (permintaan_pengiriman), termasuk No
     * Surat Jalan lain yang kebetulan dibuat bareng dalam sesi Input
     * Pengiriman yang sama (lihat HOTFIX128, 1 sesi sekarang boleh berisi
     * beberapa No Surat Jalan sekaligus). Cuma porsi produk yang ada di
     * No Surat Jalan ini yang dikurangi dari detail_permintaan_pengiriman;
     * No Surat Jalan lain dalam sesi yang sama tidak ikut tersentuh.
     * Kalau ini kebetulan satu-satunya No Surat Jalan yang tersisa di sesi
     * itu, header permintaan_pengiriman-nya ikut dibersihkan juga (biar
     * nggak nyisain baris "hantu"), sama seperti hapusPengirimanLangsung().
     */
    public function hapusSuratJalanLangsung()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $hash = trim((string) $this->request->getPost('hash'));
        if ($hash === '') {
            return $this->response->setJSON(['error' => 'No Surat Jalan tidak valid.']);
        }

        $db = \Config\Database::connect();

        // Dicari dari rencana_pengiriman (BUKAN dari barangkeluar) -- baris
        // ini bisa aja masih status Draft (item sudah "Simpan" dan No Surat
        // Jalan sudah diisi, tapi belum pernah beneran "Save dan Kirim"),
        // jadi barangkeluar-nya belum ada sama sekali.
        $rencanaTerdampak = $db->table('rencana_pengiriman')
            ->select('id, permintaan_id, detail_id, qty, no_do')
            ->where('SHA1(no_do)', $hash)
            ->get()
            ->getResultArray();

        if (!$rencanaTerdampak) {
            return $this->response->setJSON(['error' => 'Data pengiriman tidak ditemukan.']);
        }

        $noDo = (string) $rencanaTerdampak[0]['no_do'];
        $permintaanId = (int) $rencanaTerdampak[0]['permintaan_id'];

        $db->transBegin();

        // Kalau surat jalan ini beneran udah "Save dan Kirim" (ada
        // barangkeluar-nya), fungsi ini yang ngurusin balikin stok + hapus
        // barangkeluar/detail_barangkeluar + hapus rencana_pengiriman-nya
        // sekalian. Kalau masih murni Draft (belum pernah dikirim beneran),
        // fungsi ini no-op (nggak ada stok yang perlu dibalikin) -- makanya
        // rencana_pengiriman-nya tetap dihapus manual di bawah biar nggak
        // nyisa baris "hantu" apapun hasilnya.
        $error = $this->hapusBarangKeluarByFaktur($noDo);
        if ($error !== null) {
            $db->transRollback();
            return $this->response->setJSON(['error' => $error]);
        }

        // Idempotent -- kalau tadi beneran udah dihapus di dalam
        // hapusBarangKeluarByFaktur() (kasus udah pernah dikirim), ini
        // tinggal hapus 0 baris. Kalau tadi no-op (kasus Draft), ini yang
        // beneran ngebersihin rencana_pengiriman-nya.
        $db->table('rencana_pengiriman')->where('no_do', $noDo)->delete();

        // Kurangi qty di detail_permintaan_pengiriman cuma sebesar porsi
        // yang barusan dibatalkan -- kalau produk yang sama juga ada di No
        // Surat Jalan lain dalam sesi ini, porsi punya surat jalan itu tetap
        // utuh, nggak ikut kesentuh.
        $detailModel = new ModelDetailPermintaanPengiriman();
        $porsiPerDetail = [];
        foreach ($rencanaTerdampak as $row) {
            $detailId = (int) $row['detail_id'];
            $porsiPerDetail[$detailId] = ($porsiPerDetail[$detailId] ?? 0) + (int) $row['qty'];
        }

        foreach ($porsiPerDetail as $detailId => $qtyDikurangi) {
            $detail = $detailModel->find($detailId);
            if (!$detail) {
                continue;
            }
            $qtyBaru = (int) $detail['qty'] - $qtyDikurangi;
            if ($qtyBaru <= 0) {
                $detailModel->delete($detailId);
            } else {
                $detailModel->update($detailId, ['qty' => $qtyBaru]);
            }
        }

        $masihAdaLain = $db->table('rencana_pengiriman')
            ->where('permintaan_id', $permintaanId)
            ->countAllResults() > 0;

        if (!$masihAdaLain) {
            $db->table('detail_permintaan_pengiriman')->where('permintaan_id', $permintaanId)->delete();
            $db->table('permintaan_pengiriman')->where('id', $permintaanId)->delete();
        } else {
            $totalProduk = (int) ($detailModel->selectSum('qty')->where('permintaan_id', $permintaanId)->first()['qty'] ?? 0);
            $db->table('permintaan_pengiriman')->where('id', $permintaanId)->update(['total_produk' => $totalProduk]);
        }

        $db->transCommit();

        return $this->response->setJSON(['sukses' => 'Surat Jalan ' . $noDo . ' berhasil dihapus, stok sudah dikembalikan.']);
    }

    public function edit($faktur)
    {
        $modelBarangKeluar = new ModelBarangKeluar();
        $modelDetailPo = new ModelDetailPo();
        $modelStok = new ModelStok();
        $db = \Config\Database::connect();

        $cekFaktur = $modelBarangKeluar->cekFaktur($faktur);

        if ($cekFaktur->getNumRows() > 0) {
            $row = $cekFaktur->getRowArray();
            $poRows = $db->table('detail_barangkeluar')
                ->select('detpo')
                ->where('detfaktur', $row['faktur'])
                ->groupBy('detpo')
                ->get()
                ->getResultArray();

            $poList = array_values(array_unique(array_filter(array_column($poRows, 'detpo'))));
            if (empty($poList)) {
                $poList = [$row['detpo']];
            }

            $cekPo = $modelDetailPo->whereIn('detnopo', $poList)->findAll();

            $idGudang = $row['gudang'];

            if (!empty($cekPo)) {
                foreach ($cekPo as &$po) {
                    $idStok = $modelStok->cariIdStok($po['detkodebrg'], $idGudang);
                    $idMaterial = $modelStok->cariMaterial($po['detkodebrg'], $idGudang);

                    if ($idStok != null) {
                        $stok = $modelStok->cariStokKeluar($idStok);
                        $po['stok'] = $stok;
                        $po['idbarang'] = $idStok;
                        $po['material'] = $idMaterial;
                    } else {
                        $po['stok'] = 0;
                        $po['idbarang'] = null;
                        $po['material'] = null;
                    }
                    $po['gudang'] = $idGudang;
                    $po['material'] = $idMaterial;
                }
                unset($po);

                $data = [
                    'nofaktur' => $row['faktur'],
                    'nopo' => $poList[0],
                    'tanggal' => $row['tglfaktur'],
                    'namapelanggan' => $row['pelnama'],
                    'idpelanggan' => $row['detidpel'],
                    'detqty' => $row['detqty'],
                    'detkurang' => $row['detkurang'],
                    'idgudang' => $idGudang,
                    'namagudang' => $row['gdgnama'],
                    'datapo' => $cekPo,
                ];

                return view('barangkeluar/formedit', $data);
            } else {
                exit('Data PO tidak ditemukan');
            }
        } else {
            exit('Data tidak ditemukan');
        }
    }

    /**
     * Ganti No Surat Jalan (barangkeluar.faktur, PK) ke nomor baru. Dipakai
     * kalau nomornya salah ketik pas input awal. detail_barangkeluar.detfaktur
     * ikut kesesuain otomatis lewat FK ON UPDATE CASCADE, tapi
     * rencana_pengiriman.no_do harus disesuaikan manual di sini karena kolom
     * itu tidak diikat FK ke barangkeluar.
     */
    public function ubahNoSuratJalan()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $fakturLama = trim((string) $this->request->getPost('nofaktur_lama'));
        $fakturBaru = trim((string) $this->request->getPost('nofaktur_baru'));

        if ($fakturLama === '' || $fakturBaru === '') {
            return $this->response->setJSON(['error' => 'No Surat Jalan tidak boleh kosong.']);
        }

        if ($fakturLama === $fakturBaru) {
            return $this->response->setJSON(['sukses' => 'Tidak ada perubahan.']);
        }

        $modelBarangKeluar = new ModelBarangKeluar();

        if (!$modelBarangKeluar->find($fakturLama)) {
            return $this->response->setJSON(['error' => 'Data Surat Jalan lama tidak ditemukan.']);
        }

        if ($modelBarangKeluar->find($fakturBaru)) {
            return $this->response->setJSON(['error' => "No Surat Jalan {$fakturBaru} sudah dipakai transaksi lain."]);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $db->table('barangkeluar')
            ->where('faktur', $fakturLama)
            ->update(['faktur' => $fakturBaru]);

        // detail_barangkeluar.detfaktur ikut berubah otomatis lewat FK CASCADE
        // di atas -- tapi rencana_pengiriman.no_do tidak diikat FK, jadi
        // disamain manual biar List Pengiriman tetap nyambung ke DO ini.
        $db->table('rencana_pengiriman')
            ->where('no_do', $fakturLama)
            ->update(['no_do' => $fakturBaru]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['error' => 'No Surat Jalan gagal diubah.']);
        }

        return $this->response->setJSON(['sukses' => 'No Surat Jalan berhasil diubah.']);
    }

    function ambilTotalBerat()
    {
        if ($this->request->isAJAX()) {
            $nofaktur = $this->request->getPost('nofaktur');
            $modelDetail = new ModelDetailBarangKeluar();
            $totalBerat = $modelDetail->ambilTotalBerat($nofaktur);

            $json = [
                'totalberat' => "Total : " . number_format($totalBerat, 4, ",", ".") . " " . "KG"
            ];
            echo json_encode($json);
        }
    }

    function tampilDataDetail()
    {
        if ($this->request->isAJAX()) {
            $nofaktur = $this->request->getPost('nofaktur');

            $modelDetail = new ModelDetailBarangKeluar();
            $dataTemp = $modelDetail->tampilDataTemp($nofaktur);
            $data = [
                'tampildata' => $dataTemp
            ];

            $json = [
                'data' => view('barangkeluar/datadetail', $data)
            ];
            echo json_encode($json);
        }
    }

    function hapusItemDetail()
    {
        if ($this->request->isAJAX()) {
            $id = $this->request->getPost('id');

            $modelDetail = new ModelDetailBarangKeluar();
            $modelBarangKeluar = new ModelBarangKeluar();

            $rowData = $modelDetail->find($id);
            if (!$rowData) {
                return $this->response->setJSON(['error' => 'Data item tidak ditemukan.']);
            }
            $noFaktur = $rowData['detfaktur'];
            $poTerdampak = [$rowData['detpo'] ?? ''];
            $snapshotStok = $this->snapshotPemulihanStok([$rowData]);
            if ($snapshotStok['error'] !== null) {
                return $this->response->setJSON(['error' => $snapshotStok['error']]);
            }

            $db = \Config\Database::connect();
            $db->transBegin();

            $modelDetail->delete($id);

            // Baris rencana_pengiriman yang PERSIS jadi hasil item ini
            // (disambungin lewat detail_barangkeluar_id) ikut dihapus.
            // Presisi ke 1 baris -- ngga nebak dari kombinasi no_do+no_po+
            // kode_produk lagi, yang bisa keliru kalau 1 No Surat Jalan
            // dipakai buat beberapa proses (item lain dengan kombinasi yang
            // sama harus tetap aman, ngga ikut kehapus).
            $db->table('rencana_pengiriman')
                ->where('detail_barangkeluar_id', $id)
                ->delete();

            // Fallback buat data lama (dibuat sebelum kolom
            // detail_barangkeluar_id ada) yang belum punya link presisi --
            // tetap pakai cara lama, cuma kalau emang udah ngga ada sisa
            // detail_barangkeluar dengan kombinasi yang sama.
            $detailDokumenMasihAda = $db->table('detail_barangkeluar')
                ->where('detfaktur', $rowData['detfaktur'])
                ->where('detpo', $rowData['detpo'])
                ->where('detbrgkode', $rowData['detbrgkode'])
                ->countAllResults() > 0;
            if (!$detailDokumenMasihAda) {
                $db->table('rencana_pengiriman')
                    ->where('no_do', $rowData['detfaktur'])
                    ->where('no_po', $rowData['detpo'])
                    ->where('kode_produk', $rowData['detbrgkode'])
                    ->where('status', 1)
                    ->where('detail_barangkeluar_id', null)
                    ->delete();
            }

            $errorStok = $this->pulihkanStokSetelahHapus($snapshotStok['data']);
            if ($errorStok !== null) {
                $db->transRollback();
                return $this->response->setJSON(['error' => $errorStok]);
            }

            $totalBerat = $modelDetail->ambilTotalBerat($noFaktur);
            $totalQty = $modelDetail->ambilTotalQty($noFaktur);

            $modelBarangKeluar->update($noFaktur, [
                'totalberatbarang' => $totalBerat,
                'qtykeluar' => $totalQty,
            ]);

            $myModel = new Modeldetailpo();
            $myModel->updateDetailPoKurang();

            $modelOutstanding = new Modeloutstand();
            $modelOutstanding->sinkronByPoList($poTerdampak);
            $this->sinkronStatusPermintaanPengirimanByPo($poTerdampak);
            $modelOutstanding->updateDetailOutstandingKurang();

            $db->transCommit();

            // // Integrasi Pusher
            // $options = array(
            //     'cluster' => 'ap1',
            //     'useTLS' => true
            // );
            // $pusher = new \Pusher\Pusher(
            //     '8f027ac11961f0fa1906',
            //     '21c84fc4aee41d737c58',
            //     '1826619',
            //     $options
            // );

            // $data['message'] = 'success';
            // $pusher->trigger('my-channel', 'my-event', $data);

            $json = [
                'sukses' => 'Item Berhasil di Hapus'
            ];
            echo json_encode($json);
        }
    }

    public function editItem()
    {
        if ($this->request->isAJAX()) {
            $nopo = $this->request->getPost('nopo');
            $iddetail = $this->request->getPost('iddetail');
            $jml = $this->request->getPost('jml');
            $detkurang = $this->request->getPost('detkurang');
            $idbarang = $this->request->getPost('idbarang');
            $detqty = $this->request->getPost('detqty');
            $detjml = $this->request->getPost('detjml');
            $stok = $this->request->getPost('stok');

            $modelDetail = new ModelDetailBarangKeluar();
            $modelBarangKeluar = new ModelBarangKeluar();

            $rowData = $modelDetail->find($iddetail);

            $noFaktur = $rowData['detfaktur'] ?? null;
            $berat = $rowData['detberat'] ?? null;
            $poTerdampak = [$rowData['detpo'] ?? $nopo];

            $missingFields = [];
            if ($noFaktur === null) {
                $missingFields[] = 'detfaktur';
            }
            if ($berat === null) {
                $missingFields[] = 'detberat';
            }

            if (!empty($missingFields)) {
                $json = [
                    'error' => 'Data detail tidak ditemukan atau tidak lengkap',
                    'rowData' => $rowData,
                    'missingFields' => $missingFields
                ];
                echo json_encode($json);
                return;
            }

            $jmlPO = intval($detqty);
            $jmlSisaPO = intval($detqty) + intval($detjml);
            $jmlStok = intval($stok) + intval($detjml);

            if ($jml <= 0) {
                $json = [
                    'error' => 'Qty harus lebih besar dari 0'
                ];
            } else if ($jml > $jmlStok) {
                $json = [
                    'error1' => 'Stok tidak mencukupi'
                ];
            } else {
                if ($jml > $jmlPO) {
                    $json = [
                        'error2' => 'Qty melebihi PO'
                    ];
                } else {
                    if ($jml > $jmlSisaPO) {
                        $json = [
                            'error3' => 'Qty melebihi sisa PO'
                        ];
                    } else {
                        $db = db_connect();
                        $db->transStart();

                        // Trigger UPDATE detail_barangkeluar menyinkronkan selisih stok.
                        $modelDetail->update($iddetail, [
                            'detjml' => $jml,
                            'detsubtotal' => $jml * $berat
                        ]);

                        // PENTING: trigger tri_update_detailBarangKeluar/tri_update_detail_stok
                        // di atas otomatis nyesuaiin brgstok/stok berdasarkan selisih qty,
                        // TERLEPAS baris ini dari_migrasi atau bukan. Baris migrasi gak boleh
                        // pengaruhin stok sama sekali -- batalkan penyesuaian itu di `stok`
                        // per-gudang SAJA. Trigger tri_data_barang_update di tabel `stok`
                        // otomatis nyinkronin ulang `barang.brgstok` (=SUM stok semua gudang)
                        // tiap kali baris `stok` di-UPDATE, jadi JANGAN update
                        // `barang.brgstok` manual juga di sini, nanti kompensasinya kehitung
                        // dobel.
                        if ((int) ($rowData['dari_migrasi'] ?? 0) === 1) {
                            $deltaJml = (float) $jml - (float) ($rowData['detjml'] ?? 0);
                            if ($deltaJml !== 0.0 && !empty($rowData['idbarang'])) {
                                $db->table('stok')
                                    ->where('id', $rowData['idbarang'])
                                    ->set('stok', 'stok + (' . $deltaJml . ')', false)
                                    ->update();
                            }
                        }

                        $totalBerat = $modelDetail->ambilTotalBerat($noFaktur);
                        $totalQty = $modelDetail->ambilTotalQty($noFaktur);

                        $modelBarangKeluar->update($noFaktur, [
                            'totalberatbarang' => $totalBerat,
                            'qtykeluar' => $totalQty
                        ]);

                        $myModel = new Modeldetailpo();
                        $myModel->updateDetailPoKurang();

                        $modelOutstanding = new Modeloutstand();
                        $modelOutstanding->sinkronByPoList($poTerdampak);
                        $this->sinkronStatusPermintaanPengirimanByPo($poTerdampak);
                        $modelOutstanding->updateDetailOutstandingKurang();
                        $db->transComplete();

                        $json = $db->transStatus() === false
                            ? ['error' => 'Item Produk Keluar gagal diperbarui. Tidak ada data yang diubah.']
                            : [
                                'sukses' => 'Item Berhasil di Update',
                                'data' => [
                                    'nopo' => $nopo,
                                    'iddetail' => $iddetail,
                                    'jml' => $jml,
                                    'detkurang' => $detkurang,
                                    'idbarang' => $idbarang,
                                    'detqty' => $detqty,
                                    'stok' => $stok,
                                    'totalBerat' => $totalBerat,
                                    'totalQty' => $totalQty,
                                ]
                            ];
                    }
                }
            }

            // // Integrasi Pusher
            // $options = array(
            //     'cluster' => 'ap1',
            //     'useTLS' => true
            // );
            // $pusher = new \Pusher\Pusher(
            //     '8f027ac11961f0fa1906',
            //     '21c84fc4aee41d737c58',
            //     '1826619',
            //     $options
            // );

            // $data['message'] = 'success';
            // $pusher->trigger('my-channel', 'my-event', $data);

            echo json_encode($json);
        }
    }

    function simpanItemDetail()
    {
        if ($this->request->isAJAX()) {
            $nofaktur = $this->request->getPost('nofaktur');
            $nopo = $this->request->getPost('nopo');
            $kodebarang = $this->request->getPost('kodebarang');
            $idbarang = $this->request->getPost('idbarang');
            $idmaterial = $this->request->getPost('idmaterial');
            $namabarang = $this->request->getPost('namabarang');
            $idpelanggan = $this->request->getPost('idpelanggan');
            $berat = $this->request->getPost('berat');
            $idgudang = $this->request->getPost('idgudang');
            $jml = $this->request->getPost('jml');
            $detkurang = $this->request->getPost('detkurang');
            $detqty = $this->request->getPost('detqty');
            $stok = $this->request->getPost('stok');
            $tanggal = $this->request->getPost('tanggal');

            log_message('debug', 'Tanggal diterima: ' . $tanggal);

            $modelTempBarangKeluar = new ModelDetailBarangKeluar();
            $modelBarangKeluar = new ModelBarangKeluar();
            $modelBarang = new Modelbarang();

            $ambilDataBarang = $modelBarang->find($kodebarang);
            if (!$ambilDataBarang) {
                return $this->response->setJSON(['error' => 'Data produk tidak ditemukan.']);
            }
            $stokBarang = $ambilDataBarang['brgstok'];

            if ($jml > intval($stokBarang)) {
                $json = [
                    'error' => 'Stok tidak mencukupi'
                ];
            } else if ($jml > intval($stok)) {
                $json = [
                    'error1' => 'Stok tidak mencukupi'
                ];
            } else if ($jml > $detkurang) {
                $json = [
                    'error2' => 'Qty melebihi sisa PO'
                ];
            } else if ($jml > $detqty) {
                $json = [
                    'error3' => 'Qty melebihi PO'
                ];
            } else {
                $db = db_connect();
                $db->transStart();

                $existingRow = $modelTempBarangKeluar
                    ->where('idbarang', $idbarang)
                    ->where('detfaktur', $nofaktur)
                    ->first();

                if ($existingRow) {
                    $newJml = $existingRow['detjml'] + $jml;
                    $newSubtotal = $existingRow['detsubtotal'] + ($jml * $berat);

                    $modelTempBarangKeluar->update($existingRow['id'], [
                        'detjml' => $newJml,
                        'detsubtotal' => $newSubtotal,
                        'tgl' => $tanggal
                    ]);
                } else {
                    $modelTempBarangKeluar->insert([
                        'detfaktur' => $nofaktur,
                        'detpo' => $nopo,
                        'detbrgkode' => $kodebarang,
                        'namabarang' => $namabarang,
                        'material' => $idmaterial,
                        'idbarang' => $idbarang,
                        'detidpel' => $idpelanggan,
                        'detberat' => $berat,
                        'gudang' => $idgudang,
                        'detjml' => $jml,
                        'detsubtotal' => $jml * $berat,
                        'tgl' => $tanggal
                    ]);
                }
                $modelOutstanding = new Modeloutstand();
                $modelOutstanding->sinkronByPoList([$nopo]);
                $this->sinkronStatusPermintaanPengirimanByPo([$nopo]);
                $modelOutstanding->updateDetailOutstandingKurang();

                $totalBerat = $modelTempBarangKeluar->ambilTotalBerat($nofaktur);
                $totalQty = $modelTempBarangKeluar->ambilTotalQty($nofaktur);

                $modelBarangKeluar->update($nofaktur, [
                    'totalberatbarang' => $totalBerat,
                    'qtykeluar' => $totalQty
                ]);

                $myModel = new Modeldetailpo();
                $myModel->updateDetailPoKurang();
                $db->transComplete();

                // // Integrasi Pusher
                // $options = array(
                //     'cluster' => 'ap1',
                //     'useTLS' => true
                // );
                // $pusher = new \Pusher\Pusher(
                //     '8f027ac11961f0fa1906',
                //     '21c84fc4aee41d737c58',
                //     '1826619',
                //     $options
                // );

                // $data['message'] = 'success';
                // $pusher->trigger('my-channel', 'my-event', $data);

                $json = $db->transStatus() === false
                    ? ['error' => 'Item Produk Keluar gagal ditambahkan. Tidak ada data yang diubah.']
                    : ['sukses' => 'Item Berhasil di Tambahkan'];
            }

            echo json_encode($json);
        }
    }

    public function dokumenPengiriman()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $this->ensureBtbFileColumns();

        $hash = trim((string) $this->request->getPost('no_do_hash'));
        $rows = $this->rencanaPengirimanDokumenList($hash);

        if (!$rows) {
            return $this->response->setJSON([
                'error' => 'Data dokumen pengiriman tidak ditemukan.',
            ]);
        }

        return $this->response->setJSON([
            'sukses' => true,
            'data' => array_map(static function (array $row) {
                return [
                    'id' => (int) $row['id'],
                    'no_do' => $row['no_do'] ?: '-',
                    'no_po' => $row['no_po'] ?: '-',
                    'tanggal_po' => $row['tanggal_po'] ?: '-',
                    'kode_produk' => $row['kode_produk'] ?: '-',
                    'qty' => (float) $row['qty'],
                    'no_btb' => $row['no_btb'] ?: '',
                    'btb_original_name' => $row['btb_original_name'] ?: '',
                    'btb_file_url' => !empty($row['btb_file']) ? site_url('barangkeluar/file-btb/' . $row['id']) : '',
                ];
            }, $rows),
        ]);
    }

    /**
     * Balikin id rencana_pengiriman yang bener2 tervalidasi milik No Surat
     * Jalan ini (ids dari request cuma dipercaya kalau match SHA1(no_do)+status
     * -- nyegah id dari surat jalan lain ikut ke-update).
     *
     * @return int[]
     */
    private function idPengirimanTerpilih(string $hash): array
    {
        $ids = array_map('intval', (array) $this->request->getPost('ids'));
        $ids = array_values(array_unique(array_filter($ids, static fn($id) => $id > 0)));

        if ($hash === '' || !$ids) {
            return [];
        }

        $rows = \Config\Database::connect()
            ->table('rencana_pengiriman')
            ->select('id')
            ->where('SHA1(no_do)', $hash)
            ->where('status', 1)
            ->whereIn('id', $ids)
            ->get()
            ->getResultArray();

        return array_map('intval', array_column($rows, 'id'));
    }

    public function simpanDokumenPengiriman()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $this->ensureBtbFileColumns();

        $hash = trim((string) $this->request->getPost('no_do_hash'));
        $noBtb = trim((string) $this->request->getPost('no_btb'));
        $ids = $this->idPengirimanTerpilih($hash);

        if (!$ids) {
            return $this->response->setJSON([
                'error' => 'Pilih minimal 1 item yang akan diisi BTB-nya.',
            ]);
        }

        $rules = [
            'no_btb' => [
                'label' => 'No BTB',
                'rules' => 'permit_empty|max_length[100]',
            ],
        ];

        $file = $this->request->getFile('btb_file');
        $hasUploadedFile = $file && $file->getError() !== UPLOAD_ERR_NO_FILE;
        if ($hasUploadedFile) {
            $rules['btb_file'] = [
                'label' => 'File BTB',
                'rules' => 'uploaded[btb_file]|max_size[btb_file,5120]|ext_in[btb_file,pdf,jpg,jpeg,png]|mime_in[btb_file,application/pdf,image/jpg,image/jpeg,image/png]',
            ];
        }

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'error' => implode('<br>', $this->validator->getErrors()),
            ]);
        }

        $db = \Config\Database::connect();
        $uploadDir = $this->btbUploadDir();
        $uploadedFileName = null;
        $originalName = null;

        if ($hasUploadedFile && $file && $file->isValid()) {
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }

            $extension = strtolower($file->getClientExtension() ?: $file->getExtension() ?: 'dat');
            $uploadedFileName = bin2hex(random_bytes(16)) . '.' . $extension;
            $originalName = $file->getClientName();

            try {
                $file->move($uploadDir, $uploadedFileName);
            } catch (\Throwable $e) {
                log_message('error', 'Gagal upload file BTB pengiriman: {message}', ['message' => $e->getMessage()]);
                return $this->response->setJSON([
                    'error' => 'File BTB gagal diupload. ' . $e->getMessage(),
                ]);
            }
        }

        $matchedRows = $db->table('rencana_pengiriman')
            ->select('id, btb_file')
            ->whereIn('id', $ids)
            ->get()
            ->getResultArray();

        $updateData = ['no_btb' => $noBtb];

        if ($uploadedFileName !== null) {
            $updateData['btb_file'] = $uploadedFileName;
            $updateData['btb_original_name'] = $originalName ?: $uploadedFileName;
        }

        $db->transStart();
        $db->table('rencana_pengiriman')
            ->whereIn('id', $ids)
            ->update($updateData);
        $db->transComplete();

        if ($db->transStatus() === false) {
            if ($uploadedFileName !== null) {
                $uploadedPath = $uploadDir . DIRECTORY_SEPARATOR . $uploadedFileName;
                if (is_file($uploadedPath)) {
                    unlink($uploadedPath);
                }
            }

            return $this->response->setJSON([
                'error' => 'Dokumen pengiriman gagal disimpan.',
            ]);
        }

        if ($uploadedFileName !== null) {
            foreach ($matchedRows as $matchedRow) {
                $oldFile = (string) ($matchedRow['btb_file'] ?? '');
                if ($oldFile === '' || $oldFile === $uploadedFileName) {
                    continue;
                }

                $oldPath = $uploadDir . DIRECTORY_SEPARATOR . $oldFile;
                if (is_file($oldPath)) {
                    unlink($oldPath);
                }
            }
        }

        return $this->response->setJSON([
            'sukses' => 'Dokumen pengiriman berhasil disimpan.',
        ]);
    }

    /**
     * Hapus No BTB + file BTB yang sudah diupload (misal salah upload) --
     * beda dari simpanDokumenPengiriman() yang selalu butuh isi baru,
     * di sini emang sengaja dikosongkan lagi. Scoping berdasarkan item
     * (rencana_pengiriman.id) yang dicentang user, bukan per No. PO --
     * 1 No Surat Jalan sekarang bisa punya banyak item lintas PO.
     */
    public function hapusDokumenPengiriman()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $this->ensureBtbFileColumns();

        $hash = trim((string) $this->request->getPost('no_do_hash'));
        $ids = $this->idPengirimanTerpilih($hash);

        if (!$ids) {
            return $this->response->setJSON([
                'error' => 'Pilih minimal 1 item yang akan dihapus BTB-nya.',
            ]);
        }

        $db = \Config\Database::connect();
        $matchedRows = $db->table('rencana_pengiriman')
            ->select('id, btb_file')
            ->whereIn('id', $ids)
            ->get()
            ->getResultArray();

        $db->transStart();
        $db->table('rencana_pengiriman')
            ->whereIn('id', $ids)
            ->update([
                'no_btb' => '',
                'btb_file' => null,
                'btb_original_name' => null,
            ]);
        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON([
                'error' => 'Dokumen BTB gagal dihapus.',
            ]);
        }

        $uploadDir = $this->btbUploadDir();
        foreach ($matchedRows as $matchedRow) {
            $oldFile = (string) ($matchedRow['btb_file'] ?? '');
            if ($oldFile === '') {
                continue;
            }

            $oldPath = $uploadDir . DIRECTORY_SEPARATOR . $oldFile;
            if (is_file($oldPath)) {
                unlink($oldPath);
            }
        }

        return $this->response->setJSON([
            'sukses' => 'Dokumen BTB berhasil dihapus.',
        ]);
    }

    public function fileBtb(int $id)
    {
        $this->ensureBtbFileColumns();

        $db = \Config\Database::connect();
        $row = $db->table('rencana_pengiriman')
            ->select('btb_file, btb_original_name')
            ->where('id', $id)
            ->get()
            ->getRowArray();

        if (!$row || empty($row['btb_file'])) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('File BTB tidak ditemukan.');
        }

        $path = $this->btbUploadDir() . DIRECTORY_SEPARATOR . $row['btb_file'];
        if (!is_file($path)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('File BTB tidak ditemukan.');
        }

        return $this->response
            ->download($path, null)
            ->setFileName($row['btb_original_name'] ?: $row['btb_file']);
    }

    /**
     * Balikin SEMUA baris item (rencana_pengiriman) yang tercakup dalam 1 No
     * Surat Jalan -- dipakai buat modal Edit Dokumen BTB supaya user bisa
     * milih per ITEM (checkbox), bukan per No. PO, karena 1 PO bisa punya
     * lebih dari 1 item dan 1 surat jalan sekarang boleh gabungan PO.
     */
    private function rencanaPengirimanDokumenList(string $hash): array
    {
        if ($hash === '') {
            return [];
        }

        return \Config\Database::connect()
            ->table('rencana_pengiriman')
            ->select('id, no_do, no_po, tanggal_po, kode_produk, qty, no_btb, btb_file, btb_original_name')
            ->where('SHA1(no_do)', $hash)
            ->where('status', 1)
            ->orderBy('no_po', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * detail_barangkeluar.dari_migrasi nandain baris kirim itu asalnya dari
     * qty migrasi PO (gak potong stok pas dibuat) atau qty baru biasa (potong
     * stok seperti biasa) -- dipakai buat mutusin cara hapusnya. Sama persis
     * dengan PermintaanPengiriman::ensureMigrasiPengirimanColumns(), dobel di
     * sini biar controller ini gak bergantung urutan controller mana yang
     * kepanggil duluan.
     */
    private function ensureDariMigrasiColumn(): void
    {
        $db = \Config\Database::connect();
        $forge = \Config\Database::forge();

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

    private function ensureBtbFileColumns(): void
    {
        $db = \Config\Database::connect();
        $forge = \Config\Database::forge();

        if (!$db->fieldExists('btb_file', 'rencana_pengiriman')) {
            $forge->addColumn('rencana_pengiriman', [
                'btb_file' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                    'after' => 'no_btb',
                ],
            ]);
        }

        if (!$db->fieldExists('btb_original_name', 'rencana_pengiriman')) {
            $forge->addColumn('rencana_pengiriman', [
                'btb_original_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                    'after' => 'btb_file',
                ],
            ]);
        }
    }

    private function btbUploadDir(): string
    {
        return WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'btb_pengiriman';
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

            // rencana_pengiriman.qty itu angka yang ke-simpen sekali pas
            // rencana diproses -- kalau qty aslinya di detail_barangkeluar
            // diedit belakangan (mis. lewat Barangkeluar::editItem()), qty
            // di sini jadi basi dan bikin kolom "Terkirim" di List Pengiriman
            // ngga ikut berubah.
            //
            // Disamain lagi lewat detail_barangkeluar_id -- link presisi 1:1
            // ke baris detail_barangkeluar yang jadi hasil eksekusi rencana
            // ini (lihat PermintaanPengiriman::eksekusiKirim()). Ngga pakai
            // kombinasi teks (no_do+no_po+kode_produk) lagi karena itu bisa
            // ambigu kalau 1 No Surat Jalan dipakai buat beberapa kali
            // proses -- dengan ID langsung, ngga ada tebak-tebakan lagi.
            $db->query("
                UPDATE rencana_pengiriman rp
                JOIN detail_barangkeluar dk ON dk.id = rp.detail_barangkeluar_id
                SET rp.qty = dk.detjml
                WHERE rp.permintaan_id = ? AND rp.status = 1 AND rp.detail_barangkeluar_id IS NOT NULL
            ", [$permintaanId]);

            // Fallback buat baris lama (dibuat sebelum kolom
            // detail_barangkeluar_id ada) yang belum punya link presisi --
            // tetap pakai cara lama, TAPI cuma kalau kombinasinya unik (biar
            // ngga dobel-hitung kayak sebelumnya).
            $db->query("
                UPDATE rencana_pengiriman rp
                JOIN (
                    SELECT detfaktur, detpo, detbrgkode, SUM(detjml) AS qty_aktual
                    FROM detail_barangkeluar
                    GROUP BY detfaktur, detpo, detbrgkode
                ) dk ON dk.detfaktur = rp.no_do AND dk.detpo = rp.no_po AND dk.detbrgkode = rp.kode_produk
                JOIN (
                    SELECT no_do, no_po, kode_produk
                    FROM rencana_pengiriman
                    WHERE permintaan_id = ? AND status = 1 AND detail_barangkeluar_id IS NULL
                    GROUP BY no_do, no_po, kode_produk
                    HAVING COUNT(*) = 1
                ) unik ON unik.no_do = rp.no_do AND unik.no_po = rp.no_po AND unik.kode_produk = rp.kode_produk
                SET rp.qty = dk.qty_aktual
                WHERE rp.permintaan_id = ? AND rp.status = 1 AND rp.detail_barangkeluar_id IS NULL
            ", [$permintaanId, $permintaanId]);

            $totalPermintaan = (int) ($db->table('detail_permintaan_pengiriman')
                ->selectSum('qty', 'qty')
                ->where('permintaan_id', $permintaanId)
                ->get()
                ->getRowArray()['qty'] ?? 0);
            $totalTerkirim = $this->hitungTerkirimAktualPermintaanPengiriman($permintaanId);

            $db->table('permintaan_pengiriman')
                ->where('id', $permintaanId)
                ->update([
                    'status' => $totalPermintaan > 0 && $totalTerkirim >= $totalPermintaan ? 1 : 0,
                ]);
        }
    }

    private function hitungTerkirimAktualPermintaanPengiriman(int $permintaanId): int
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

    private function isPelangganAop(string $namaPelanggan): bool
    {
        $namaPelanggan = strtoupper($namaPelanggan);

        return str_contains($namaPelanggan, 'AOP')
            || str_contains($namaPelanggan, 'ASTRA OTOPART')
            || str_contains($namaPelanggan, 'ASTRA OTOPARTS');
    }
}
