<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Modelbarang;
use App\Models\Modelberat;
use App\Models\ModelDetailPermintaanBarang;
use App\Models\Modelstok;
use App\Models\ModelTempPermintaanBarang;
use App\Models\ModelDataStok;
use App\Models\ModelDetailPermintaanKirim;
use App\Models\Modelgudang;
use App\Models\ModelPermintaanBarang;
use App\Models\ModelPermintaanBarangKirim;
use App\Models\Modelmaterial;
use \Hermawan\DataTables\DataTable;
use Config\Services;
use App\Models\Modeluser;

class PermintaanBarang extends BaseController
{
    public function cetak(string $hash)
    {
        $db = \Config\Database::connect();
        $header = $db->table('permintaanbarang p')
            ->select('p.*, u.usernama')
            ->join('users u', 'u.id = p.iduser', 'left')
            ->where('SHA1(p.id)', $hash)
            ->get()
            ->getRowArray();

        if (!$header) {
            return $this->response->setStatusCode(404, 'Data permintaan transfer tidak ditemukan');
        }

        $totalTerkirim = (float) ($db->table('permintaanbarangkirim')
            ->selectSum('qtykirim', 'total')
            ->where('detpermintaan', $header['permintaan'])
            ->get()
            ->getRowArray()['total'] ?? 0);

        $totalNominal = (float) ($db->table('permintaanbarangkirim')
            ->selectSum('nominal', 'total')
            ->where('detpermintaan', $header['permintaan'])
            ->get()
            ->getRowArray()['total'] ?? 0);

        if ($totalTerkirim < (float) $header['qtypermintaan']) {
            return $this->response->setStatusCode(403, 'Permintaan transfer belum selesai dikirim dan belum dapat dicetak');
        }

        $kolomFakturDetail = $db->fieldExists('detfaktur', 'detail_permintaanbarangkirim')
            ? 'detfaktur'
            : 'defaktur';

        $rows = $db->table('permintaanbarangkirim pb')
            ->select('pb.faktur, pb.tglfaktur, pb.qtykirim, pb.gudang, pb.picpengirim, pb.nominal, g.gdgnama, u.usernama, d.id AS detail_id, d.detkodebrg, d.detqty, d.jenis_item')
            ->join('detail_permintaanbarangkirim d', "d.{$kolomFakturDetail} = pb.faktur", 'inner')
            ->join('gudang g', 'g.gdgid = pb.gudang', 'left')
            ->join('users u', 'u.id = pb.iduser', 'left')
            ->where('pb.detpermintaan', $header['permintaan'])
            ->orderBy('pb.tglfaktur', 'ASC')
            ->orderBy('pb.faktur', 'ASC')
            ->orderBy('d.id', 'ASC')
            ->get()
            ->getResultArray();

        if (!$rows) {
            return $this->response->setStatusCode(404, 'Detail pengiriman transfer tidak ditemukan');
        }

        $gudangAsal = array_values(array_unique(array_filter(array_column($rows, 'gdgnama'))));
        $semuaGudang = array_column((new Modelgudang())->orderBy('gdgnama', 'ASC')->findAll(), 'gdgnama');
        $gudangTujuan = array_values(array_diff($semuaGudang, $gudangAsal));

        return view('reqbarang/cetak', [
            'header' => $header,
            'rows' => $rows,
            'gudangAsal' => $gudangAsal ? implode(', ', $gudangAsal) : '-',
            'gudangTujuan' => count($gudangTujuan) === 1 ? $gudangTujuan[0] : '-',
            'totalTerkirim' => $totalTerkirim,
            'totalNominal' => $totalNominal,
        ]);
    }

    public function input()
    {
        $currentUser = (new Modeluser())
            ->select('id, userid, usernama')
            ->where('userid', (string) session()->get('userid'))
            ->first();

        if (!$currentUser) {
            return redirect()->to('/login/keluar')
                ->with('error', 'Sesi user tidak valid. Silakan login kembali.');
        }

        return view('reqbarang/forminput', [
            'currentUser' => $currentUser,
            'databarang' => (new Modelbarang())
                ->select('brgkode, brgnama')
                ->orderBy('brgkode', 'ASC')
                ->findAll(),
            'datamaterial' => (new Modelmaterial())
                ->select('matid, matkode, matnama')
                ->orderBy('matkode', 'ASC')
                ->findAll(),
            'datagudang' => (new Modelgudang())
                ->orderBy('gdgnama', 'ASC')
                ->findAll(),
        ]);
    }

    public function modalCariBarang()
    {
        if ($this->request->isAJAX()) {
            $json = [
                'data' => view('reqbarang/modalcaribarang')
            ];
            echo json_encode($json);
        }
    }

    public function modalCariMaterial()
    {
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'data' => view('reqbarang/modalcarimaterial'),
            ]);
        }
    }

    public function listDataMaterial()
    {
        if ($this->request->isAJAX()) {
            $db = \Config\Database::connect();
            $builder = $db->table('material m')
                ->select('m.matid, m.matkode, m.matnama, s.satnama, COALESCE(SUM(CASE WHEN sm.gudang = 1 THEN sm.stok ELSE 0 END), 0) stok_cikarang, COALESCE(SUM(CASE WHEN sm.gudang = 2 THEN sm.stok ELSE 0 END), 0) stok_cirebon')
                ->join('stokmaterial sm', 'sm.materialid = m.matid', 'left')
                ->join('satuan s', 's.satid = m.matsatid', 'left')
                ->groupBy('m.matid, m.matkode, m.matnama, s.satnama');

            return DataTable::of($builder)
                ->addNumbering('nomor')
                ->add('aksi', static function ($row) {
                    return '<button type="button" class="btn btn-sm btn-info" onclick="pilihMaterial(\'' . esc($row->matkode, 'js') . '\')">Pilih</button>';
                })
                ->format('stok_cikarang', static fn($value) => number_format((float) $value, 0, ',', '.'))
                ->format('stok_cirebon', static fn($value) => number_format((float) $value, 0, ',', '.'))
                ->toJson(true);
        }
    }

    /**
     * Stok yang ditampilkan sekarang per-gudang (bukan total semua gudang
     * lagi) -- soalnya Gudang Asal sekarang dipilih di sini, di form Input,
     * bukan belakangan pas "Proses" (langkah "Proses" udah dihapus, semua
     * kejadian sekaligus pas Selesai Transaksi).
     */
    function ambilDataBarang()
    {
        if ($this->request->isAJAX()) {
            $kodebarang = $this->request->getPost('kodebarang');
            $idgudang = (int) $this->request->getPost('idgudang');

            $modelBarang = new Modelbarang();
            $modelStok = new Modelstok();
            $modelBerat = new Modelberat();

            $cekDataBarang = $modelBarang->find($kodebarang);
            $cekDataBerat = $modelBerat->find($kodebarang);

            if ($cekDataBarang == null) {
                $json = [
                    'error' => 'Maaf, data barang tidak ditemukan'
                ];
            } elseif ($cekDataBerat == null || empty($cekDataBerat['berat'])) {
                $json = [
                    'error' => 'Maaf, berat barang belum diinput'
                ];
            } elseif ($idgudang <= 0) {
                $json = [
                    'error' => 'Gudang asal wajib dipilih'
                ];
            } else {
                $stok = $modelStok->getStokByGudang($kodebarang, $idgudang);
                $harga = $modelStok->getHargaByKodeBarang($kodebarang);

                $data = [
                    'namabarang' => $cekDataBarang['brgnama'],
                    'stok' => $stok,
                    'harga' => $harga,
                    'berat' => $cekDataBerat['berat']
                ];

                $json = [
                    'sukses' => $data
                ];
            }
            echo json_encode($json);
        }
    }

    public function ambilDataMaterial()
    {
        if ($this->request->isAJAX()) {
            $kode = trim((string) $this->request->getPost('kodebarang'));
            $idgudang = (int) $this->request->getPost('idgudang');

            if ($idgudang <= 0) {
                return $this->response->setJSON(['error' => 'Gudang asal wajib dipilih']);
            }

            $db = \Config\Database::connect();
            $material = $db->table('material m')
                ->select('m.matid, m.matkode, m.matnama, m.matsatid, s.satnama, COALESCE(sm.stok, 0) stok')
                ->join('stokmaterial sm', 'sm.materialid = m.matid AND sm.gudang = ' . $idgudang, 'left')
                ->join('satuan s', 's.satid = m.matsatid', 'left')
                ->where('m.matkode', $kode)
                ->get()->getRowArray();

            if (!$material) {
                return $this->response->setJSON(['error' => 'Maaf, data material tidak ditemukan']);
            }

            return $this->response->setJSON(['sukses' => [
                'namabarang' => $material['matnama'],
                'stok' => $material['stok'],
                'berat' => 1,
                'idmaterial' => $material['matid'],
                'satuan' => $material['satnama'] ?: 'Kg',
            ]]);
        }
    }

    function listDataBarang()
    {
        $request = Services::request();
        $datamodel = new ModelDataStok($request);
        if ($request->getMethod(true) == 'POST') {
            $lists = $datamodel->get_datatables();
            $data = [];
            $no = $request->getPost("start");
            foreach ($lists as $list) {
                $no++;
                $row = [];

                $stokGudang1 = $datamodel->where('kodebarang', $list->kodebarang)->where('gudang', 1)->first();
                $stokGudang2 = $datamodel->where('kodebarang', $list->kodebarang)->where('gudang', 2)->first();

                $stokGudang1Value = isset($stokGudang1['stok']) ? $stokGudang1['stok'] : 0;
                $stokGudang2Value = isset($stokGudang2['stok']) ? $stokGudang2['stok'] : 0;
                $stokIdGudang1Value = isset($stokGudang1['id']) ? $stokGudang1['id'] : 0;
                $stokIdGudang2Value = isset($stokGudang2['id']) ? $stokGudang2['id'] : 0;

                $tombolPilih = "<button type=\"button\" class=\"btn btn-sm btn-info\" onclick=\"pilih('" . $list->kodebarang . "')\">Pilih</button>";

                $row[] = $no;
                $row[] = $list->kodebarang;
                $row[] = $list->namabarang;
                $row[] = '<input type="hidden" name="idgudang" value="' . $stokIdGudang1Value . '">' . number_format($stokGudang1Value, 0, ",", ".");
                $row[] = '<input type="hidden" name="idgudang" value="' . $stokIdGudang2Value . '">' . number_format($stokGudang2Value, 0, ",", ".");
                $row[] = $tombolPilih;
                $data[] = $row;
            }
            $output = [
                "draw" => $request->getPost('draw'),
                "recordsTotal" => $datamodel->count_all(),
                "recordsFiltered" => $datamodel->count_filtered(),
                "data" => $data
            ];
            echo json_encode($output);
        }
    }

    function simpanItem()
    {
        if ($this->request->isAJAX()) {
            $tglpermintaan = $this->request->getPost('tglpermintaan');
            $permintaan = $this->request->getPost('permintaan');
            $kodebarang = $this->request->getPost('kodebarang');
            $namabarang = $this->request->getPost('namabarang');
            $jml = $this->request->getPost('jml');
            $berat = $this->request->getPost('berat');
            $jenisItem = $this->request->getPost('jenis_item') === 'material' ? 'material' : 'produk';
            $idmaterial = (int) $this->request->getPost('idmaterial');
            $idgudang = (int) $this->request->getPost('idgudang');

            $modelTemp = new ModelTempPermintaanBarang();

            $validation = \Config\Services::validation();
            $valid = $this->validate([
                'permintaan' => [
                    'rules' => 'required|is_unique[permintaanbarang.permintaan]',
                    'label' => 'No Surat Jalan',
                    'errors' => [
                        'required' => '{field} tidak boleh kosong',
                        'is_unique' => '{field} sudah terpakai'
                    ]
                ],
            ]);

            if (!$valid) {
                $json = [
                    'error1' => '' . $validation->listErrors() . ''
                ];
            } elseif ($idgudang <= 0) {
                $json = [
                    'error1' => 'Gudang asal wajib dipilih'
                ];
            } else {
                $existingItem = $modelTemp
                    ->where('detpermintaan', $permintaan)
                    ->where('detkodebrg', $kodebarang)
                    ->where('jenis_item', $jenisItem)
                    ->where('gudang', $idgudang)
                    ->first();

                if ($existingItem) {
                    $newQty = $existingItem['detqty'] + $jml;
                    $newSubtotal = $existingItem['detsubtotal'] + ($jml * $berat);

                    $modelTemp->update($existingItem['id'], [
                        'detqty' => $newQty,
                        'detsubtotal' => $newSubtotal
                    ]);
                } else {
                    $modelTemp->insert([
                        'detpermintaan' => $permintaan,
                        'dettglpermintaan' => $tglpermintaan,
                        'detkodebrg' => $kodebarang,
                        'namabarang' => $namabarang,
                        'jenis_item' => $jenisItem,
                        'material' => $idmaterial,
                        'detberat' => $berat,
                        'detqty' => $jml,
                        'detsubtotal' => $jml * $berat,
                        'gudang' => $idgudang,
                    ]);
                }
                // echo '<pre>';
                // print_r($modelTemp->getLastQuery()->getQuery());
                // echo '</pre>';
                // die();
                $json = ['sukses' => 'Item Berhasil di Tambahkan'];
            }
            echo json_encode($json);
        }
    }

    public function tampilDataTemp()
    {
        if ($this->request->isAJAX()) {
            $permintaan = $this->request->getPost('permintaan');

            $modelTemp = new ModelTempPermintaanBarang();
            $dataTemp = $modelTemp->tampilDataTemp($permintaan);
            $data = [
                'tampildata' => $dataTemp
            ];

            $json = [
                'data' => view('reqbarang/datatemp', $data)
            ];
            echo json_encode($json);
        }
    }

    /**
     * Dulu cuma bikin data "Permintaan" (belum ada gudang/belum kirim),
     * masih butuh langkah "Proses" terpisah belakangan. Sekarang langsung
     * dieksekusi sekaligus di sini: bikin Permintaan + Pengiriman + mindahin
     * stok gudang asal->tujuan, satu klik kelar -- soalnya Gudang Asal udah
     * dipilih dari awal per item (lihat simpanItem()).
     */
    function selesaiTransaksi()
    {
        if (!$this->request->isAJAX()) {
            exit('Maaf tidak bisa dipanggil');
        }

        $tglpermintaan = $this->request->getPost('tglpermintaan');
        $permintaan = $this->request->getPost('permintaan');
        $keterangan = trim((string) $this->request->getPost('keterangan'));

        $currentUser = (new Modeluser())
            ->select('id, userid, usernama')
            ->where('userid', (string) session()->get('userid'))
            ->first();

        if (!$currentUser) {
            echo json_encode(['error' => 'Sesi user tidak valid. Silakan login kembali.']);
            return;
        }
        $iduser = $currentUser['id'];

        $modelTemp = new ModelTempPermintaanBarang();
        $dataTemp = $modelTemp->getWhere(['detpermintaan' => $permintaan])->getResultArray();

        if (!$dataTemp) {
            echo json_encode(['error' => 'Maaf, belum ada item yang ditambahkan. Klik tombol simpan (disket) dulu sebelum Selesai Transaksi.']);
            return;
        }

        $modelStok = new Modelstok();
        $db = db_connect();

        // Validasi stok cukup DULU, sebelum nyimpen apapun -- item Antar
        // Gudang sekarang langsung terkirim & motong stok saat ini juga,
        // nggak ada lagi tahap "Proses" terpisah belakangan buat ngecek ulang.
        foreach ($dataTemp as $row) {
            $idgudang = (int) $row['gudang'];
            if ($idgudang <= 0) {
                echo json_encode(['error' => 'Item ' . $row['detkodebrg'] . ' belum punya Gudang Asal.']);
                return;
            }

            $jenisItem = $row['jenis_item'] ?? 'produk';
            if ($jenisItem === 'material') {
                $stokMaterial = $db->table('stokmaterial')
                    ->where('materialid', $row['material'])
                    ->where('gudang', $idgudang)
                    ->get()->getRowArray();
                $stokTersedia = (float) ($stokMaterial['stok'] ?? 0);
            } else {
                $stokTersedia = $modelStok->getStokByGudang($row['detkodebrg'], $idgudang);
            }

            if ((float) $row['detqty'] > $stokTersedia) {
                echo json_encode(['error' => 'Stok ' . $row['detkodebrg'] . ' di gudang asal tidak mencukupi (tersedia ' . $stokTersedia . ', dibutuhkan ' . $row['detqty'] . ').']);
                return;
            }
        }

        $db->transStart();

        $totalSubQty = 0;
        $totalSubTotal = 0;
        foreach ($dataTemp as $row) {
            $totalSubQty += (float) $row['detqty'];
            $totalSubTotal += (float) $row['detsubtotal'];
        }

        (new ModelPermintaanBarang())->insert([
            'permintaan' => $permintaan,
            'tglpermintaan' => $tglpermintaan,
            'iduser' => $iduser,
            'qtypermintaan' => $totalSubQty,
            'ketpermintaan' => $keterangan,
            'jenispengiriman' => '',
            'picpengirim' => '',
            'nominal' => 0,
        ]);

        $fieldDetail = [];
        foreach ($dataTemp as $row) {
            $fieldDetail[] = [
                'detpermintaan' => $row['detpermintaan'],
                'dettglpermintaan' => $row['dettglpermintaan'],
                'detkodebrg' => $row['detkodebrg'],
                'namabarang' => $row['namabarang'],
                'jenis_item' => $row['jenis_item'] ?? 'produk',
                'material' => $row['material'] ?? null,
                'detberat' => $row['detberat'],
                'detqty' => $row['detqty'],
                'iduser' => $iduser,
                'detsubtotal' => $row['detsubtotal'],
                'gudang' => $row['gudang'],
            ];
        }

        $modelDetail = new ModelDetailPermintaanBarang();
        $modelDetail->insertBatch($fieldDetail);

        // Bikin data pengiriman & pindahin stoknya langsung -- ini yang
        // dulu baru kejadian belakangan pas user klik "Proses".
        $nofaktur = 'KRM-' . date('ymdHis') . '-' . str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT);

        (new ModelPermintaanBarangKirim())->insert([
            'faktur' => $nofaktur,
            'detpermintaan' => $permintaan,
            'tglfaktur' => $tglpermintaan,
            'iduser' => $iduser,
            'qtykirim' => $totalSubQty,
            'totalberatbarang' => $totalSubTotal,
            'gudang' => $dataTemp[0]['gudang'],
            'jenispengiriman' => '',
            'picpengirim' => '',
            'nominal' => 0,
        ]);

        $fieldDetailKirim = [];
        $stokMasukTujuan = [];
        foreach ($dataTemp as $row) {
            $jenisItem = $row['jenis_item'] ?? 'produk';
            $gudangAsal = (int) $row['gudang'];
            $gudangTujuan = $gudangAsal === 1 ? 2 : 1;
            $qty = (float) $row['detqty'];
            $idbarang = 0;

            if ($jenisItem === 'material') {
                // idbarang sengaja 0 -- biar trigger pemotong stok PRODUK
                // (yang jalan lewat idbarang) nggak ikut kesenggol; stok
                // material dipindah manual di bawah ini.
                $stokMaterialAsal = $db->table('stokmaterial')
                    ->where('materialid', $row['material'])
                    ->where('gudang', $gudangAsal)
                    ->get()->getRowArray();

                $db->table('stokmaterial')->set('stok', 'stok - ' . $qty, false)
                    ->where('id', $stokMaterialAsal['id'])->update();

                $stokMaterialTujuan = $db->table('stokmaterial')
                    ->where('materialid', $row['material'])
                    ->where('gudang', $gudangTujuan)->get()->getRowArray();
                if ($stokMaterialTujuan) {
                    $db->table('stokmaterial')->set('stok', 'stok + ' . $qty, false)
                        ->where('id', $stokMaterialTujuan['id'])->update();
                } else {
                    $db->table('stokmaterial')->insert([
                        'materialid' => $stokMaterialAsal['materialid'],
                        'kodematerial' => $stokMaterialAsal['kodematerial'],
                        'namamaterial' => $stokMaterialAsal['namamaterial'],
                        'materialkatid' => $stokMaterialAsal['materialkatid'],
                        'materialsatid' => $stokMaterialAsal['materialsatid'],
                        'gudang' => $gudangTujuan,
                        'stok' => $qty,
                    ]);
                }
            } else {
                // Stok gudang asal dikurangi otomatis lewat trigger pas
                // detail_permintaanbarangkirim di-insert (keyed by idbarang).
                $idbarang = $modelStok->getBarangIdByKodeGudang($row['detkodebrg'], $gudangAsal);

                $stokAsal = $modelStok
                    ->where('kodebarang', $row['detkodebrg'])
                    ->where('gudang', $gudangAsal)
                    ->first();

                $stokMasukTujuan[] = [
                    'kodebarang' => $row['detkodebrg'],
                    'namabarang' => $row['namabarang'],
                    'material' => $row['material'],
                    'gudang' => $gudangTujuan,
                    'berat' => $row['detberat'],
                    'stok' => (int) $qty,
                    'harga' => $stokAsal['harga'] ?? 0,
                    'idpel' => $stokAsal['idpel'] ?? null,
                ];
            }

            $fieldDetailKirim[] = [
                'detfaktur' => $nofaktur,
                'dettglkirim' => $tglpermintaan,
                'detpermintaan' => $permintaan,
                'iduser' => $iduser,
                'detkodebrg' => $row['detkodebrg'],
                'namabarang' => $row['namabarang'],
                'jenis_item' => $jenisItem,
                'material' => $row['material'],
                'idbarang' => $idbarang,
                'detberat' => $row['detberat'],
                'detqty' => $qty,
                'gudang' => $gudangAsal,
                'detsubtotal' => $row['detsubtotal'],
            ];
        }

        (new ModelDetailPermintaanKirim())->insertBatch($fieldDetailKirim);

        if ($stokMasukTujuan) {
            $modelStok->updateOrInsertBatch($stokMasukTujuan);
        }

        $modelDetail->updateDetailPermintaanKurang();

        $modelTemp->hapusData($permintaan);
        $db->transComplete();

        $json = $db->transStatus() === false
            ? ['error' => 'Permintaan Transfer gagal disimpan. Tidak ada data yang diubah.']
            : ['sukses' => 'Transaksi berhasil disimpan dan dikirim.'];

        echo json_encode($json);
    }

    function hapusItem()
    {
        if ($this->request->isAJAX()) {
            $id = $this->request->getPost('id');

            $modelTemppo = new ModelTempPermintaanBarang();
            $modelTemppo->delete($id);

            $json = [
                'sukses' => 'Item Berhasil di Hapus'
            ];
            echo json_encode($json);
        }
    }

    function hapusTransaksi()
    {
        if ($this->request->isAJAX()) {
            $permintaan = $this->request->getPost('permintaan');
            $id = $this->request->getPost('id');

            $modelpermintaan = new ModelPermintaanBarang();

            $db = \Config\Database::connect();

            $cekBarangKeluar = $db->table('permintaanbarangkirim')->where('detpermintaan', $permintaan)->countAllResults();

            if ($cekBarangKeluar > 0) {
                $json = [
                    'error' => 'Data tidak bisa dihapus karena masih terkait dengan data Pengiriman Transfer'
                ];
            } else {
                $db->transStart();
                $db->table('detail_permintaanbarang')->delete(['detpermintaan' => $permintaan]);
                $modelpermintaan->delete($id);
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
                    ? ['error' => 'Permintaan Transfer gagal dihapus. Tidak ada data yang diubah.']
                    : ['sukses' => 'Transaksi berhasil di Hapus'];
            }

            echo json_encode($json);
        }
    }

    public function edit($id)
    {
        $modelpermintaan = new ModelPermintaanBarang();
        $cekPermintaan = $modelpermintaan->cekPermintaan($id);
        if ($cekPermintaan->getNumRows() > 0) {
            $row = $cekPermintaan->getRowArray();
            $data = [
                'idpermintaan' => $id,
                'permintaan' => $row['permintaan'],
                'tanggal' => $row['tglpermintaan'],
                'namauser' => $row['usernama'],
                'iduser' => $row['iduser'],
                'keterangan' => $row['ketpermintaan'],
                'jenisPengiriman' => $row['jenispengiriman'] ?? '-',
                'picPengirim' => $row['picpengirim'] ?? '-',
                'nominal' => $row['nominal'] ?? 0,
            ];
            return view('reqbarang/formedit', $data);
        } else {
            exit('Data tidak ditemukan');
        }
    }

    function ambilTotalQty()
    {
        if ($this->request->isAJAX()) {
            $permintaan = $this->request->getPost('permintaan');
            $modelDetail = new ModelDetailPermintaanBarang();
            $totalQty = $modelDetail->ambilTotalQty($permintaan);

            $json = [
                'totalqty' => "Total : " . number_format($totalQty, 0, ",", ".") . " " . "Pcs"
            ];
            echo json_encode($json);
        }
    }

    function tampilDataDetail()
    {
        if ($this->request->isAJAX()) {
            $permintaan = $this->request->getPost('permintaan');

            $modelDetail = new ModelDetailPermintaanBarang();
            $dataTemp = $modelDetail->tampilDataTemp($permintaan);
            $data = [
                'tampildata' => $dataTemp
            ];

            $json = [
                'data' => view('reqbarang/datadetail', $data)
            ];
            echo json_encode($json);
        }
    }

    function hapusItemDetail()
    {
        if ($this->request->isAJAX()) {
            $id = $this->request->getPost('id');

            $modelDetail = new ModelDetailPermintaanBarang();
            $modelpermintaan = new ModelPermintaanBarang();

            $rowData = $modelDetail->find($id);
            if (!$rowData) {
                return $this->response->setJSON(['error' => 'Data item tidak ditemukan.']);
            }
            $permintaan = $rowData['detpermintaan'];

            $db = db_connect();
            $db->transStart();

            $modelDetail->delete($id);

            $totalQty = $modelDetail->ambilTotalQty($permintaan);

            $modelpermintaan->where('permintaan', $permintaan)->set(['qtypermintaan' => $totalQty])->update();
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
                ? ['error' => 'Item Permintaan Transfer gagal dihapus. Tidak ada data yang diubah.']
                : ['sukses' => 'Item Berhasil di Hapus'];
            echo json_encode($json);
        }
    }

    function editItem()
    {
        if ($this->request->isAJAX()) {
            $iddetail = $this->request->getPost('iddetail');
            $permintaan = $this->request->getPost('permintaan');
            $jml = $this->request->getPost('jml');

            $modelDetail = new ModelDetailPermintaanBarang();
            $modelpermintaan = new ModelPermintaanBarang();

            $rowData = $modelDetail->find($iddetail);
            if (!$rowData) {
                return $this->response->setJSON(['error' => 'Data item tidak ditemukan.']);
            }
            $berat = $rowData['detberat'];

            if ($jml < 0) {
                $json = [
                    'error' => 'Jumlah tidak boleh 0'
                ];
            } else {
                $db = db_connect();
                $db->transStart();

                $modelDetail->update($iddetail, [
                    'detqty' => $jml,
                    'detsubtotal' => $jml * $berat,
                    'detsubtotal' => $jml * $berat,
                ]);

                $modelDetail->updateDetailPermintaanKurang();

                $totalQty = $modelDetail->ambilTotalQty($permintaan);

                $modelpermintaan->where('permintaan', $permintaan)->set(['qtypermintaan' => $totalQty])->update();
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
                    ? ['error' => 'Item Permintaan Transfer gagal diperbarui. Tidak ada data yang diubah.']
                    : ['sukses' => 'Item Berhasil di Update'];
            }
            echo json_encode($json);
        }
    }

    public function updatePermintaan()
    {
        if ($this->request->isAJAX()) {
            $newPermintaan = $this->request->getPost('newPermintaan');
            $originalPermintaan = $this->request->getPost('originalPermintaan');

            if (is_string($newPermintaan) && !empty($newPermintaan)) {
                $modelPermintaan = new ModelPermintaanBarang();
                $modelDetailPermintaan = new ModelDetailPermintaanBarang();

                $sudahDigunakan = $newPermintaan !== $originalPermintaan
                    && $modelPermintaan->where('permintaan', $newPermintaan)->first();

                if ($sudahDigunakan) {
                    $json = ['error' => 'No. Surat Jalan sudah digunakan'];
                } else {
                    $db = db_connect();
                    $db->transStart();

                    $modelPermintaan
                        ->where('permintaan', $originalPermintaan)
                        ->set(['permintaan' => $newPermintaan])
                        ->update();
                    $modelDetailPermintaan
                        ->where('detpermintaan', $originalPermintaan)
                        ->set(['detpermintaan' => $newPermintaan])
                        ->update();

                    $db->transComplete();

                    $json = $db->transStatus() === false
                        ? ['error' => 'Gagal mengubah No. Surat Jalan. Tidak ada data yang diubah.']
                        : [
                            'sukses' => 'No. Surat Jalan berhasil diubah',
                            'newPermintaan' => $newPermintaan
                        ];
                }
            } else {
                $json = ['error' => 'No. Surat Jalan baru tidak valid'];
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

    public function simpanItemDetail()
    {
        if ($this->request->isAJAX()) {
            $permintaan = $this->request->getPost('permintaan');
            $kodebarang = $this->request->getPost('kodebarang');
            $namabarang = $this->request->getPost('namabarang');
            $berat = $this->request->getPost('berat');
            $jml = $this->request->getPost('jml');
            $iduser = $this->request->getPost('iduser');
            $tglpermintaan = $this->request->getPost('tglpermintaan');

            $modeltemppermintaan = new ModelDetailPermintaanBarang();
            $modelpermintaan = new ModelPermintaanBarang();

            $db = db_connect();
            $db->transStart();

            $existingRowDetailPo = $modeltemppermintaan
                ->where('detkodebrg', $kodebarang)
                ->where('detpermintaan', $permintaan)
                ->first();

            if ($existingRowDetailPo) {
                $newJmlDetailPo = $existingRowDetailPo['detqty'] + $jml;
                $newSubtotal = $existingRowDetailPo['detsubtotal'] + ($jml * $berat);

                $modeltemppermintaan->update($existingRowDetailPo['id'], [
                    'detqty' => $newJmlDetailPo,
                    'detsubtotal' => $newSubtotal,
                ]);
            } else {
                $modeltemppermintaan->insert([
                    'detpermintaan' => $permintaan,
                    'detkodebrg' => $kodebarang,
                    'dettglpo' => $tglpermintaan,
                    'namabarang' => $namabarang,
                    'detqty' => $jml,
                    'iduser' => $iduser,
                    'detsubtotal' => $jml * $berat,
                ]);
            }

            $totalQty = $modeltemppermintaan->ambilTotalQty($permintaan);
            $modelpermintaan->where('permintaan', $permintaan)->set(['qtypermintaan' => $totalQty])->update();
            $db->transComplete();


            // // Debugging output
            // echo '<pre>';
            // echo 'Permintaan: ' . $permintaan . "\n";
            // echo 'Total Qty: ' . $totalQty . "\n";

            // // Debugging before update
            // echo 'Checking for Permintaan: ' . $permintaan . "\n";
            // $existingPermintaan = $modelpermintaan->where('permintaan', $permintaan)->first();
            // echo 'Data Before Update: ';
            // print_r($existingPermintaan);
            // echo "\n";

            // $updateResult = $modelpermintaan->where('permintaan', $permintaan)->set(['qtypermintaan' => $totalQty])->update();

            // // Debugging update result
            // echo 'Update Result: ';
            // var_dump($updateResult);
            // echo "\n";

            // // Debugging after update
            // $updatedPermintaan = $modelpermintaan->where('permintaan', $permintaan)->first();
            // echo 'Data After Update: ';
            // print_r($updatedPermintaan);
            // echo '</pre>';

            // Supaya hasil echo terlihat, kita hentikan script di sini
            // exit();

            $json = $db->transStatus() === false
                ? ['error' => 'Item Permintaan Transfer gagal ditambahkan. Tidak ada data yang diubah.']
                : ['sukses' => 'Item Berhasil di Tambahkan'];
            echo json_encode($json);
        }
    }

    public function hapusTransaksiProses()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $faktur = trim((string) $this->request->getPost('faktur'));

        if ($faktur === '') {
            return $this->response->setJSON([
                'error' => 'Nomor surat jalan tidak valid.'
            ]);
        }

        $db = \Config\Database::connect();

        $headerKirim = $db->table('permintaanbarangkirim')
            ->where('faktur', $faktur)
            ->get()
            ->getRowArray();

        if (!$headerKirim) {
            return $this->response->setJSON([
                'error' => 'Data pengiriman tidak ditemukan.'
            ]);
        }

        /*
        * Detail harus diambil sebelum dihapus karena berisi:
        * - kode produk
        * - qty yang ditransfer
        * - gudang asal
        *
        * Bisa aja udah kosong kalau semua item-nya sebelumnya dihapus satu-satu
        * lewat halaman Edit Antar Gudang -- stoknya udah dibalikin penuh waktu itu,
        * jadi di sini tinggal hapus header yang udah kosong ini, nggak perlu
        * ngembaliin stok lagi (nggak ada apa-apa buat dibalikin).
        */
        $detailTransfer = $db->table('detail_permintaanbarangkirim')
            ->select('detkodebrg, detqty, gudang, material, jenis_item')
            ->where('detfaktur', $faktur)
            ->get()
            ->getResultArray();

        /*
        * Gabungkan qty jika produk yang sama muncul lebih dari sekali.
        */
        $stokTujuanYangDikurangi = [];

        foreach ($detailTransfer as $detail) {
            $gudangAsal = (int) $detail['gudang'];

            if ($gudangAsal === 1) {
                $gudangTujuan = 2;
            } elseif ($gudangAsal === 2) {
                $gudangTujuan = 1;
            } else {
                return $this->response->setJSON([
                    'error' => 'Gudang asal pada detail transfer tidak valid.'
                ]);
            }

            $kodeBarang = (string) $detail['detkodebrg'];
            $qty = (int) $detail['detqty'];
            $jenisItem = $detail['jenis_item'] ?? 'produk';
            $key = $jenisItem . '|' . $kodeBarang . '|' . $gudangTujuan;

            if (!isset($stokTujuanYangDikurangi[$key])) {
                $stokTujuanYangDikurangi[$key] = [
                    'kodebarang' => $kodeBarang,
                    'gudang' => $gudangTujuan,
                    'qty' => 0,
                    'jenis_item' => $jenisItem,
                    'material' => (int) $detail['material'],
                    'gudang_asal' => $gudangAsal,
                ];
            }

            $stokTujuanYangDikurangi[$key]['qty'] += $qty;
        }

        $db->transBegin();

        try {
            /*
            * Kurangi stok yang sebelumnya masuk ke gudang tujuan.
            * Kondisi stok >= qty mencegah stok menjadi negatif.
            */
            foreach ($stokTujuanYangDikurangi as $item) {
                $qty = (int) $item['qty'];

                if ($item['jenis_item'] === 'material') {
                    $db->table('stokmaterial')->set('stok', "stok - {$qty}", false)
                        ->where('materialid', $item['material'])
                        ->where('gudang', $item['gudang'])
                        ->where('stok >=', $qty)->update();
                    if ($db->affectedRows() === 0) {
                        throw new \RuntimeException('Stok gudang tujuan tidak mencukupi untuk membatalkan transfer material ' . $item['kodebarang'] . '.');
                    }
                    $db->table('stokmaterial')->set('stok', "stok + {$qty}", false)
                        ->where('materialid', $item['material'])
                        ->where('gudang', $item['gudang_asal'])->update();
                    continue;
                }

                $db->table('stok')
                    ->set('stok', "stok - {$qty}", false)
                    ->where('kodebarang', $item['kodebarang'])
                    ->where('gudang', $item['gudang'])
                    ->where('stok >=', $qty)
                    ->update();

                if ($db->affectedRows() === 0) {
                    throw new \RuntimeException(
                        'Stok gudang tujuan tidak mencukupi untuk membatalkan ' .
                        'transfer produk ' . $item['kodebarang'] . '.'
                    );
                }
            }

            /*
            * Saat detail dihapus, trigger stok_tri_delete akan
            * mengembalikan stok ke gudang asal berdasarkan idbarang.
            */
            $db->table('detail_permintaanbarangkirim')
                ->where('detfaktur', $faktur)
                ->delete();

            $db->table('permintaanbarangkirim')
                ->where('faktur', $faktur)
                ->delete();

            /*
            * Permintaan (No Surat Jalan) dan detailnya cuma dibuat sebagai
            * pasangan 1:1 dari kirim record ini (alur 1-langkah, dibikin
            * bareng di selesaiTransaksi()) -- kalau nggak ikut dihapus di sini,
            * baris ini nyangkut selamanya dan bikin No Surat Jalan yang sama
            * nggak bisa dipakai lagi (kena validasi is_unique di simpanItem()).
            */
            $db->table('detail_permintaanbarang')
                ->where('detpermintaan', $headerKirim['detpermintaan'])
                ->delete();

            $db->table('permintaanbarang')
                ->where('permintaan', $headerKirim['detpermintaan'])
                ->delete();

            /*
            * Hitung ulang jumlah terkirim dan kekurangan permintaan.
            */
            $modelDetailPermintaan = new ModelDetailPermintaanBarang();
            $modelDetailPermintaan->updateDetailPermintaanKurang();

            if ($db->transStatus() === false) {
                throw new \RuntimeException(
                    'Terjadi kegagalan saat menghapus transaksi transfer.'
                );
            }

            $db->transCommit();

            return $this->response->setJSON([
                'sukses' =>
                    'Pengiriman transfer berhasil dihapus dan stok kedua gudang ' .
                    'sudah dikembalikan.'
            ]);
        } catch (\Throwable $e) {
            $db->transRollback();

            log_message(
                'error',
                'Gagal menghapus transfer {faktur}: {pesan}',
                [
                    'faktur' => $faktur,
                    'pesan' => $e->getMessage(),
                ]
            );

            return $this->response->setJSON([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function cetakPeriode()
    {
        $tglAwal  = $this->request->getGet('tglawal');
        $tglAkhir = $this->request->getGet('tglakhir');
        $idGudang = (int) $this->request->getGet('gudang');

        if (
            empty($tglAwal) ||
            empty($tglAkhir) ||
            $idGudang <= 0 ||
            strtotime($tglAwal) === false ||
            strtotime($tglAkhir) === false
        ) {
            return $this->response
                ->setStatusCode(400)
                ->setBody('Periode tanggal atau asal gudang tidak valid.');
        }

        if ($tglAwal > $tglAkhir) {
            return $this->response
                ->setStatusCode(400)
                ->setBody('Tanggal awal tidak boleh melebihi tanggal akhir.');
        }

        $db = \Config\Database::connect();

        $gudang = $db->table('gudang')
            ->where('gdgid', $idGudang)
            ->get()
            ->getRowArray();

        if (!$gudang) {
            return $this->response
                ->setStatusCode(404)
                ->setBody('Data gudang tidak ditemukan.');
        }

        $totalTerkirimSql = "
            COALESCE((
                SELECT SUM(pb.qtykirim)
                FROM permintaanbarangkirim pb
                WHERE pb.detpermintaan = p.permintaan
            ), 0)
        ";

        $data = $db->table('permintaanbarang p')
            ->select("
                p.id,
                p.permintaan,
                p.tglpermintaan,
                pb_periode.faktur,
                pb_periode.tglfaktur AS tglpengiriman,
                pb_periode.qtykirim AS qtypengiriman,
                pb_periode.jenispengiriman,
                pb_periode.picpengirim,
                pb_periode.nominal,
                p.ketpermintaan,
                u.usernama,
                {$totalTerkirimSql} AS total_terkirim
            ", false)
            ->join('users u', 'u.id = p.iduser', 'left')
            ->join('permintaanbarangkirim pb_periode', 'pb_periode.detpermintaan = p.permintaan', 'inner')
            ->where('p.tglpermintaan >=', $tglAwal)
            ->where('p.tglpermintaan <=', $tglAkhir)
            ->where('pb_periode.gudang', $idGudang)

            // Hanya permintaan yang sudah selesai dikirim.
            ->where(
                "{$totalTerkirimSql} >= p.qtypermintaan",
                null,
                false
            )

            ->orderBy('p.tglpermintaan', 'ASC')
            ->orderBy('p.id', 'ASC')
            ->orderBy('pb_periode.tglfaktur', 'ASC')
            ->orderBy('pb_periode.faktur', 'ASC')
            ->get()
            ->getResultArray();

        return view('reqbarang/cetakperiode', [
            'dataPermintaan' => $data,
            'tglAwal'        => $tglAwal,
            'tglAkhir'       => $tglAkhir,
            'gudang'         => $gudang
        ]);
    }
}
