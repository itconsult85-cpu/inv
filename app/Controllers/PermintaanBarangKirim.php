<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Modelbarang;
use App\Models\ModelDetailPermintaanBarang;
use App\Models\Modelstok;
use \Hermawan\DataTables\DataTable;
use App\Models\ModelDetailPermintaanKirim;
use App\Models\ModelPermintaanBarangKirim;

class PermintaanBarangKirim extends BaseController
{
    /**
     * Jenis Pengiriman / PIC Pengirim / Nominal sekarang boleh diisi
     * belakangan kapan aja lewat sini -- dulu wajib diisi pas tahap "Proses"
     * (yang sekarang udah dihapus, semuanya langsung terkirim pas Input).
     */
    public function updateHeader()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $faktur = trim((string) $this->request->getPost('faktur'));
        $jenisPengiriman = trim((string) $this->request->getPost('jenispengiriman'));
        $picPengirim = trim((string) $this->request->getPost('picpengirim'));
        $nominal = (float) str_replace(',', '.', (string) $this->request->getPost('nominal'));

        $modelPermintaanKirim = new ModelPermintaanBarangKirim();
        $row = $modelPermintaanKirim->find($faktur);
        if (!$row) {
            return $this->response->setJSON(['error' => 'Data pengiriman tidak ditemukan.']);
        }

        if ($nominal < 0) {
            return $this->response->setJSON(['error' => 'Nominal tidak boleh negatif.']);
        }

        $modelPermintaanKirim->update($row['faktur'], [
            'jenispengiriman' => $jenisPengiriman,
            'picpengirim' => $picPengirim,
            'nominal' => $nominal,
        ]);

        return $this->response->setJSON(['sukses' => 'Info pengiriman berhasil disimpan.']);
    }

    public function dataKirim()
    {
        $modelGudang = new \App\Models\Modelgudang();

        return view('reqbarang/viewdatakirim', [
            'dataGudang' => $modelGudang
                ->orderBy('gdgnama', 'ASC')
                ->findAll()
        ]);
    }

    public function listDataKirim()
    {
        if ($this->request->isAJAX()) {
            $tglawal = $this->request->getPost('tglawal');
            $tglakhir = $this->request->getPost('tglakhir');

            $db = \Config\Database::connect();
            $builder = $db->table('permintaanbarangkirim p')
                ->select('p.faktur, p.detpermintaan, p.tglfaktur, p.iduser, p.qtykirim, p.totalberatbarang, p.gudang, p.jenispengiriman, p.picpengirim, p.nominal, u.usernama, g.gdgnama')
                ->join('gudang g', 'g.gdgid = p.gudang')
                ->join('users u', 'u.id = p.iduser');

            if ($tglawal && $tglakhir) {
                $builder->where('p.tglfaktur >=', $tglawal)
                    ->where('p.tglfaktur <=', $tglakhir);
            }

            return DataTable::of($builder)
                ->addNumbering('nomor')
                ->add('aksi', function ($row) {
                    if (\App\Libraries\AccessControl::can('produk.transfer.delete')) {
                        return "<button type=\"button\" class=\"btn btn-sm btn-primary\" onclick=\"edit('" . sha1($row->faktur) . "')\"><i class=\"fa fa-edit\"></i></button>&nbsp
                        <button type=\"button\" class=\"btn btn-sm btn-danger\" onclick=\"hapus('" . $row->faktur . "')\"><i class=\"fa fa-trash-alt\"></i></button>";
                    }
                    return "<button type=\"button\" class=\"btn btn-sm btn-primary\" onclick=\"edit('" . sha1($row->faktur) . "')\"><i class=\"fa fa-edit\"></i></button>";
                })
                ->format('qtykirim', function ($value) {
                    return number_format($value, 0, ',', '.');
                })
                ->format('tglfaktur', function ($value) {
                    return date('d-m-Y', strtotime($value));
                })
                ->format('nominal', function ($value) {
                    return 'Rp ' . number_format((float) $value, 2, ',', '.');
                })
                // ->filter(function ($builder, $request) {

                //     if ($request->tglpo)
                //         $builder->where('tglpo', $request->tglpo);
                // })
                ->toJson(true);
        }
    }

    public function editProses($faktur)
    {
        $modelPermintaanBarangKirim = new ModelPermintaanBarangKirim();

        $cekFaktur = $modelPermintaanBarangKirim->cekFaktur($faktur);

        if ($cekFaktur->getNumRows() === 0) {
            exit('Data tidak ditemukan');
        }

        $row = $cekFaktur->getRowArray();
        $detailPengiriman = (new ModelDetailPermintaanKirim())->tampilDataTemp(
            $row['faktur'],
            $row['detpermintaan'],
            $row['tglfaktur'],
            $row['gudang'],
            $row['qtykirim']
        );

        $data = [
            'nofaktur' => $row['faktur'],
            'permintaan' => $row['detpermintaan'],
            'tanggal' => $row['tglfaktur'],
            'namauser' => $row['usernama'],
            'iduser' => $row['iduser'],
            'idgudang' => $row['gudang'],
            'namagudang' => $row['gdgnama'],
            'jenisPengiriman' => (string) ($row['jenispengiriman'] ?? ''),
            'picPengirim' => (string) ($row['picpengirim'] ?? ''),
            'nominal' => (float) ($row['nominal'] ?? 0),
            'totalQtyKirim' => (int) $row['qtykirim'],
            'detailPengiriman' => $detailPengiriman,
            'databarang' => (new Modelbarang())
                ->select('brgkode, brgnama')
                ->orderBy('brgkode', 'ASC')
                ->findAll(),
            'datamaterial' => (new \App\Models\Modelmaterial())
                ->select('matid, matkode, matnama')
                ->orderBy('matkode', 'ASC')
                ->findAll(),
            'datagudang' => (new \App\Models\Modelgudang())
                ->orderBy('gdgnama', 'ASC')
                ->findAll(),
        ];

        return view('reqbarang/formeditproses', $data);
    }

    function ambilTotalQtyProses()
    {
        if ($this->request->isAJAX()) {
            $nofaktur = $this->request->getPost('nofaktur');
            $modelDetail = new ModelDetailPermintaanKirim();
            $totalQty = $modelDetail->ambilTotalQty($nofaktur);

            $json = [
                'totalqty' => "Total : " . number_format($totalQty, 0, ",", ".") . " " . "Pcs"
            ];
            echo json_encode($json);
        }
    }

    function tampilDataDetailProses()
    {
        if ($this->request->isAJAX()) {
            $nofaktur = $this->request->getPost('nofaktur');

            $modelDetail = new ModelDetailPermintaanKirim();
            $dataTemp = $modelDetail->tampilDataTemp($nofaktur);
            $data = [
                'tampildata' => $dataTemp
            ];

            $json = [
                'data' => view('reqbarang/datadetailkirim', $data)
            ];
            echo json_encode($json);
        }
    }

    /**
     * Hapus item yang udah terkirim. Sama kayak editItemProses(): sisi asal
     * produk dibalikin otomatis lewat trigger stok_tri_delete (keyed ke
     * idbarang), tapi sisi tujuan dan material (idbarang selalu 0, nggak
     * ke-cover trigger manapun) wajib dibalikin manual.
     */
    function hapusItemDetailProses()
    {
        if (!$this->request->isAJAX()) {
            return;
        }

        $id = $this->request->getPost('id');

        $modelDetail = new ModelDetailPermintaanKirim();
        $modelPermintaanBarangKirim = new ModelPermintaanBarangKirim();

        $rowData = $modelDetail->find($id);
        if (!$rowData) {
            return $this->response->setJSON(['error' => 'Data item tidak ditemukan.']);
        }
        $noFaktur = $rowData['detfaktur'];
        $jenisItem = $rowData['jenis_item'] ?? 'produk';
        $kodebarang = $rowData['detkodebrg'];
        $qty = (float) $rowData['detqty'];
        $gudangAsal = (int) $rowData['gudang'];
        $gudangTujuan = $gudangAsal === 1 ? 2 : 1;

        $db = \Config\Database::connect();
        $modelStok = new Modelstok();

        $db->transStart();

        if ($jenisItem === 'material') {
            $stokAsal = $db->table('stokmaterial')->where('materialid', $rowData['material'])->where('gudang', $gudangAsal)->get()->getRowArray();
            if ($stokAsal) {
                $db->table('stokmaterial')->set('stok', 'stok + ' . $qty, false)->where('id', $stokAsal['id'])->update();
            }
            $stokTujuan = $db->table('stokmaterial')->where('materialid', $rowData['material'])->where('gudang', $gudangTujuan)->get()->getRowArray();
            if ($stokTujuan) {
                $db->table('stokmaterial')->set('stok', 'stok - ' . $qty, false)->where('id', $stokTujuan['id'])->update();
            }
        } else {
            // Sisi asal dibalikin otomatis lewat trigger stok_tri_delete pas baris dihapus di bawah.
            $stokTujuanRow = $modelStok->where('kodebarang', $kodebarang)->where('gudang', $gudangTujuan)->first();
            if ($stokTujuanRow) {
                $modelStok->where('id', $stokTujuanRow['id'])->set('stok', 'stok - ' . $qty, false)->update();
            }
        }

        $modelDetail->delete($id);

        $totalBerat = $modelDetail->ambilTotalBerat($noFaktur);
        $totalQty = $modelDetail->ambilTotalQty($noFaktur);

        $modelPermintaanBarangKirim->update($noFaktur, [
            'totalberatbarang' => $totalBerat,
            'qtykirim' => $totalQty
        ]);

        (new ModelDetailPermintaanBarang())->updateDetailPermintaanKurang();

        $db->transComplete();

        $json = $db->transStatus() === false
            ? ['error' => 'Item gagal dihapus. Tidak ada data yang diubah.']
            : ['sukses' => 'Item Berhasil di Hapus'];
        echo json_encode($json);
    }

    /**
     * Edit qty item yang udah terkirim. Stok gudang ASAL untuk produk
     * dikoreksi otomatis lewat trigger `stok_tri_update` (dia cocokin ke
     * `idbarang`, yang cuma keisi buat produk). Tapi trigger itu nggak
     * pernah nyentuh gudang TUJUAN, dan material sama sekali nggak ke-cover
     * trigger manapun (idbarang-nya selalu 0) -- jadi keduanya wajib
     * disesuaikan manual di sini berdasarkan selisih qty lama vs baru.
     */
    public function editItemProses()
    {
        if (!$this->request->isAJAX()) {
            return;
        }

        $iddetail = $this->request->getPost('iddetail');
        $jml = (float) $this->request->getPost('jml');

        $modelDetail = new ModelDetailPermintaanKirim();
        $modelPermintaanBarang = new ModelPermintaanBarangKirim();

        $rowData = $modelDetail->find($iddetail);
        if (!$rowData) {
            echo json_encode(['error' => 'Data detail tidak ditemukan.']);
            return;
        }

        $noFaktur = $rowData['detfaktur'];
        $berat = (float) $rowData['detberat'];
        $qtyLama = (float) $rowData['detqty'];
        $jenisItem = $rowData['jenis_item'] ?? 'produk';
        $kodebarang = $rowData['detkodebrg'];
        $gudangAsal = (int) $rowData['gudang'];
        $gudangTujuan = $gudangAsal === 1 ? 2 : 1;

        if ($jml <= 0) {
            echo json_encode(['error' => 'Qty harus lebih besar dari 0']);
            return;
        }

        $db = \Config\Database::connect();
        $modelStok = new Modelstok();

        if ($jenisItem === 'material') {
            $stokAsal = $db->table('stokmaterial')->where('materialid', $rowData['material'])->where('gudang', $gudangAsal)->get()->getRowArray();
            $stokTersedia = (float) ($stokAsal['stok'] ?? 0);
        } else {
            $stokAsalRow = $modelStok->where('kodebarang', $kodebarang)->where('gudang', $gudangAsal)->first();
            $stokTersedia = (float) ($stokAsalRow['stok'] ?? 0);
        }

        // Headroom = stok yang tersedia sekarang + qty lama (seolah qty lama dikembalikan dulu)
        $jmlStok = $stokTersedia + $qtyLama;
        if ($jml > $jmlStok) {
            echo json_encode(['error1' => 'Stok di gudang asal tidak mencukupi (tersedia ' . $jmlStok . ').']);
            return;
        }

        $selisih = $jml - $qtyLama;

        $db->transStart();

        if ($selisih != 0) {
            if ($jenisItem === 'material') {
                if ($stokAsal) {
                    $db->table('stokmaterial')->set('stok', 'stok - ' . $selisih, false)->where('id', $stokAsal['id'])->update();
                }
                $stokTujuan = $db->table('stokmaterial')->where('materialid', $rowData['material'])->where('gudang', $gudangTujuan)->get()->getRowArray();
                if ($stokTujuan) {
                    $db->table('stokmaterial')->set('stok', 'stok + ' . $selisih, false)->where('id', $stokTujuan['id'])->update();
                } elseif ($selisih > 0 && $stokAsal) {
                    $db->table('stokmaterial')->insert([
                        'materialid' => $stokAsal['materialid'],
                        'kodematerial' => $stokAsal['kodematerial'],
                        'namamaterial' => $stokAsal['namamaterial'],
                        'materialkatid' => $stokAsal['materialkatid'],
                        'materialsatid' => $stokAsal['materialsatid'],
                        'gudang' => $gudangTujuan,
                        'stok' => $selisih,
                    ]);
                }
            } else {
                // Sisi asal dikoreksi otomatis lewat trigger stok_tri_update pas detqty diupdate di bawah.
                $stokTujuanRow = $modelStok->where('kodebarang', $kodebarang)->where('gudang', $gudangTujuan)->first();
                if ($stokTujuanRow) {
                    $modelStok->where('id', $stokTujuanRow['id'])->set('stok', 'stok + ' . $selisih, false)->update();
                } elseif ($selisih > 0) {
                    $modelStok->insert([
                        'kodebarang' => $kodebarang,
                        'namabarang' => $rowData['namabarang'],
                        'material' => $rowData['material'],
                        'gudang' => $gudangTujuan,
                        'berat' => $berat,
                        'stok' => (int) $selisih,
                        'harga' => $stokAsalRow['harga'] ?? 0,
                        'idpel' => $stokAsalRow['idpel'] ?? null,
                    ]);
                }
            }
        }

        $modelDetail->update($iddetail, [
            'detqty' => $jml,
            'detsubtotal' => $jml * $berat
        ]);

        $totalBerat = $modelDetail->ambilTotalBerat($noFaktur);
        $totalQty = $modelDetail->ambilTotalQty($noFaktur);

        $modelPermintaanBarang->update($noFaktur, [
            'totalberatbarang' => $totalBerat,
            'qtykirim' => $totalQty
        ]);

        (new ModelDetailPermintaanBarang())->updateDetailPermintaanKurang();

        $db->transComplete();

        $json = $db->transStatus() === false
            ? ['error' => 'Perubahan qty gagal disimpan. Tidak ada data yang diubah.']
            : ['sukses' => 'Item Berhasil di Update'];
        echo json_encode($json);
    }

    /**
     * Tambah item BARU ke transaksi Antar Gudang yang udah ada -- beda dari
     * versi lama yang cuma bisa "tambah kirim" buat item yang emang udah
     * ada di permintaan aslinya. Sekarang item apapun (produk/material,
     * nggak wajib pernah ada di permintaan itu) bisa langsung ditambahin
     * dan langsung dikirim/motong stok saat itu juga, sama kayak alur Input
     * Antar Gudang yang baru.
     */
    function simpanItemDetailProses()
    {
        if (!$this->request->isAJAX()) {
            return;
        }

        $nofaktur = trim((string) $this->request->getPost('nofaktur'));
        $permintaan = trim((string) $this->request->getPost('permintaan'));
        $kodebarang = trim((string) $this->request->getPost('kodebarang'));
        $namabarang = trim((string) $this->request->getPost('namabarang'));
        $jenisItem = $this->request->getPost('jenis_item') === 'material' ? 'material' : 'produk';
        $idmaterial = (int) $this->request->getPost('idmaterial');
        $berat = (float) $this->request->getPost('berat');
        $iduser = (int) $this->request->getPost('iduser');
        $idgudang = (int) $this->request->getPost('idgudang');
        $jml = (float) $this->request->getPost('jml');
        $tanggal = $this->request->getPost('tanggal');

        if ($kodebarang === '') {
            return $this->response->setJSON(['error' => 'Kode item wajib dipilih.']);
        }
        if ($idgudang <= 0) {
            return $this->response->setJSON(['error' => 'Gudang asal wajib dipilih.']);
        }
        if ($jml <= 0) {
            return $this->response->setJSON(['error' => 'Qty harus lebih dari 0.']);
        }

        $modelStok = new Modelstok();
        $db = \Config\Database::connect();

        if ($jenisItem === 'material') {
            $stokMaterial = $db->table('stokmaterial')
                ->where('materialid', $idmaterial)
                ->where('gudang', $idgudang)
                ->get()->getRowArray();
            $stokTersedia = (float) ($stokMaterial['stok'] ?? 0);
        } else {
            $stokTersedia = $modelStok->getStokByGudang($kodebarang, $idgudang);
        }

        if ($jml > $stokTersedia) {
            return $this->response->setJSON(['error' => 'Stok tidak mencukupi (tersedia ' . $stokTersedia . ').']);
        }

        $db->transStart();

        // detail_permintaanbarang wajib cuma 1 baris per (permintaan, kode
        // item) -- trigger di detail_permintaanbarangkirim nge-update baris
        // ini berdasarkan SUM tanpa LIMIT, jadi kalau lebih dari 1 baris
        // buat kode item yang sama, angkanya bakal salah/dobel.
        $modelDetailPermintaan = new ModelDetailPermintaanBarang();
        $existingPermintaanDetail = $modelDetailPermintaan
            ->where('detpermintaan', $permintaan)
            ->where('detkodebrg', $kodebarang)
            ->where('jenis_item', $jenisItem)
            ->first();

        if ($existingPermintaanDetail) {
            $modelDetailPermintaan->update($existingPermintaanDetail['id'], [
                'detqty' => (float) $existingPermintaanDetail['detqty'] + $jml,
                'detsubtotal' => (float) $existingPermintaanDetail['detsubtotal'] + ($jml * $berat),
            ]);
        } else {
            $modelDetailPermintaan->insert([
                'detpermintaan' => $permintaan,
                'dettglpermintaan' => $tanggal,
                'detkodebrg' => $kodebarang,
                'namabarang' => $namabarang,
                'jenis_item' => $jenisItem,
                'material' => $idmaterial,
                'detberat' => $berat,
                'detqty' => $jml,
                'detkirim' => 0,
                'detkurang' => $jml,
                'iduser' => $iduser,
                'detsubtotal' => $jml * $berat,
                'gudang' => $idgudang,
            ]);
        }

        $idbarang = 0;
        $gudangTujuan = $idgudang === 1 ? 2 : 1;

        if ($jenisItem === 'material') {
            // idbarang sengaja 0 -- stok material dipindah manual di bawah,
            // trigger stok produk (keyed by idbarang) nggak boleh kesenggol.
            $stokMaterialAsal = $db->table('stokmaterial')
                ->where('materialid', $idmaterial)
                ->where('gudang', $idgudang)
                ->get()->getRowArray();

            $db->table('stokmaterial')->set('stok', 'stok - ' . $jml, false)
                ->where('id', $stokMaterialAsal['id'])->update();

            $stokMaterialTujuan = $db->table('stokmaterial')
                ->where('materialid', $idmaterial)
                ->where('gudang', $gudangTujuan)->get()->getRowArray();
            if ($stokMaterialTujuan) {
                $db->table('stokmaterial')->set('stok', 'stok + ' . $jml, false)
                    ->where('id', $stokMaterialTujuan['id'])->update();
            } else {
                $db->table('stokmaterial')->insert([
                    'materialid' => $stokMaterialAsal['materialid'],
                    'kodematerial' => $stokMaterialAsal['kodematerial'],
                    'namamaterial' => $stokMaterialAsal['namamaterial'],
                    'materialkatid' => $stokMaterialAsal['materialkatid'],
                    'materialsatid' => $stokMaterialAsal['materialsatid'],
                    'gudang' => $gudangTujuan,
                    'stok' => $jml,
                ]);
            }
        } else {
            // Stok gudang asal dikurangi otomatis lewat trigger pas
            // detail_permintaanbarangkirim di-insert (keyed by idbarang).
            $idbarang = $modelStok->getBarangIdByKodeGudang($kodebarang, $idgudang);

            $stokAsal = $modelStok
                ->where('kodebarang', $kodebarang)
                ->where('gudang', $idgudang)
                ->first();

            $modelStok->updateOrInsertBatch([[
                'kodebarang' => $kodebarang,
                'namabarang' => $namabarang,
                'material' => $idmaterial,
                'gudang' => $gudangTujuan,
                'berat' => $berat,
                'stok' => (int) $jml,
                'harga' => $stokAsal['harga'] ?? 0,
                'idpel' => $stokAsal['idpel'] ?? null,
            ]]);
        }

        // Baris detail_permintaanbarangkirim baru selalu dibikin baru
        // (bukan digabung ke baris lama) -- lebih aman, soalnya baris lama
        // buat kode item yang sama bisa aja gudang asalnya beda.
        (new ModelDetailPermintaanKirim())->insert([
            'detfaktur' => $nofaktur,
            'detpermintaan' => $permintaan,
            'dettglkirim' => $tanggal,
            'detkodebrg' => $kodebarang,
            'namabarang' => $namabarang,
            'jenis_item' => $jenisItem,
            'material' => $idmaterial,
            'idbarang' => $idbarang,
            'iduser' => $iduser,
            'detberat' => $berat,
            'gudang' => $idgudang,
            'detqty' => $jml,
            'detsubtotal' => $jml * $berat,
        ]);

        $modelTempPermintaanKirim = new ModelDetailPermintaanKirim();
        $totalBerat = $modelTempPermintaanKirim->ambilTotalBerat($nofaktur);
        $totalQty = $modelTempPermintaanKirim->ambilTotalQty($nofaktur);

        (new ModelPermintaanBarangKirim())->update($nofaktur, [
            'totalberatbarang' => $totalBerat,
            'qtykirim' => $totalQty,
        ]);

        $modelDetailPermintaan->updateDetailPermintaanKurang();

        $db->transComplete();

        $json = $db->transStatus() === false
            ? ['error' => 'Item gagal ditambahkan. Tidak ada data yang diubah.']
            : ['sukses' => 'Item berhasil ditambahkan dan langsung dikirim.'];

        echo json_encode($json);
    }
}
