<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Modelmaterial;
use App\Models\ModelMaterialKeluar;
use App\Models\ModelDataStokMaterial;
use App\Models\ModelDetailMaterialKeluar;
use App\Models\Modelgudang;
use App\Models\Modelng;
use App\Models\ModelStokMaterial;
use App\Models\ModelTempMaterialKeluar;
use App\Models\ModelSupplier;
use App\Libraries\NoDoChecker;
use \Hermawan\DataTables\DataTable;
use Config\Services;

class Materialkeluar extends BaseController
{
    private function cariPemakaianNoDo(string $noDo): ?string
    {
        return (new NoDoChecker())->findSource($noDo);
    }

    public function cekNoDo()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $noDo = trim((string) $this->request->getPost('nofaktur'));
        $sumber = $this->cariPemakaianNoDo($noDo);

        return $this->response->setJSON([
            'terpakai' => $sumber !== null,
            'pesan' => $sumber !== null
                ? "No. Transaksi {$noDo} sudah digunakan pada {$sumber}"
                : '',
        ]);
    }

    public function data()
    {
        $modelgudang = new Modelgudang();
        $data = [
            'datagudang' => $modelgudang->findAll(),
        ];
        return view('materialkeluar/viewdata', $data);
    }

    /**
     * Menu "Material Keluar" awalnya buat nyatet material yang dikirim ke
     * Supplier (titip proses), tapi alur itu sekarang sudah pindah lewat
     * PO Keluar dan menu ini jadi tidak terpakai. Di-alih fungsi jadi
     * laporan pemakaian material dari Produksi (dp.qty_material yang
     * otomatis kepotong tiap "Selesai Produksi"), yang sebelumnya cuma
     * kelihatan di halaman Produksi itu sendiri. Tabel/kode Material
     * Keluar yang lama sengaja tidak dihapus, cuma tidak dipakai lagi.
     */
    public function listData()
    {
        if ($this->request->isAJAX()) {
            $tglawal = $this->request->getPost('tglawal');
            $tglakhir = $this->request->getPost('tglakhir');

            $db = \Config\Database::connect();
            $builder = $db->table('detail_produksi dp')
                ->select('dp.no_produksi, p.tgl_produksi, pp.kode_produk, pp.nama_produk, dp.kode_material, dp.nama_material, dp.qty_material, dp.satuan, g.gdgnama')
                ->join('produksi p', 'p.no_produksi = dp.no_produksi')
                ->join('produksi_produk pp', 'pp.id = dp.produksi_produk_id', 'left')
                ->join('gudang g', 'g.gdgid = p.gudang', 'left');

            if ($tglawal && $tglakhir) {
                $builder->where('p.tgl_produksi >=', $tglawal)->where('p.tgl_produksi <=', $tglakhir);
            }

            return DataTable::of($builder)
                ->addNumbering('nomor')
                ->format('qty_material', function ($value) {
                    return number_format($value, 2, ',', '.');
                })
                ->format('tgl_produksi', function ($value) {
                    return date('d-m-Y', strtotime($value));
                })
                ->toJson(true);
        }
    }

    public function input()
    {
        $modelgudang = new Modelgudang();
        $data = [
            'datagudang' => $modelgudang->findAll(),
            'datasupplier' => (new ModelSupplier())->orderBy('supnama', 'ASC')->findAll(),
        ];
        return view('materialkeluar/forminput', $data);
    }

    public function tampilDataTemp()
    {
        if ($this->request->isAJAX()) {
            $nofaktur = trim((string) $this->request->getPost('nofaktur'));

            $modalTempMaterialKeluar = new ModelTempMaterialKeluar();

            $dataTemp = $modalTempMaterialKeluar->tampilDataTemp($nofaktur);
            $data = [
                'tampildata' => $dataTemp
            ];

            $json = [
                'data' => view('materialkeluar/datatemp', $data)
            ];
            echo json_encode($json);
        }
    }

    function ambilDataMaterial()
    {
        if ($this->request->isAJAX()) {
            $materialid = $this->request->getPost('materialid');
            $idgudang = $this->request->getPost('idgudang');

            $modelStokMaterial = new ModelStokMaterial();

            $cekData = $modelStokMaterial->where('materialid', $materialid)->first();

            if ($cekData == null) {
                $json = [
                    'error' => 'Maaf data material tidak ditemukan'
                ];
            } else {
                $stokGudang = $modelStokMaterial->getStokByMaterialIdGudang($materialid, $idgudang);
                $idMat = $modelStokMaterial->getIdByMaterialGudang($materialid, $idgudang);

                $data = [
                    'namamaterial' => $cekData['namamaterial'],
                    'kodematerial' => $cekData['kodematerial'],
                    'materialkatid' => $cekData['materialkatid'],
                    'materialsatid' => $cekData['materialsatid'],
                    'materialid' => $cekData['materialid'],
                    'stok' => $stokGudang,
                    'idmat' => $idMat,
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
            $nofaktur = trim((string) $this->request->getPost('nofaktur'));
            $tglfaktur = $this->request->getPost('tglfaktur');
            $materialid = $this->request->getPost('materialid');
            $idgudang = $this->request->getPost('idgudang');
            $idmat = $this->request->getPost('idmat');
            $stok = $this->request->getPost('stok');
            $materialkatid = $this->request->getPost('materialkatid');
            $materialsatid = $this->request->getPost('materialsatid');
            $jml = $this->request->getPost('jml');

            $modelTempMaterialKeluar = new ModelTempMaterialKeluar();
            $modelStokMaterial = new ModelStokMaterial();

            $stokGudang = $modelStokMaterial->getStokByMaterialIdGudang($materialid, $idgudang);
            $validation = \Config\Services::validation();

            $valid = $this->validate([
                'nofaktur' => [
                    'rules' => 'required|is_unique[materialkeluar.faktur]',
                    'label' => 'No Transaksi',
                    'errors' => [
                        'required' => '{field} tidak boleh kosong',
                        'is_unique' => '{field} sudah terpakai'
                    ]
                ],
            ]);

            if (!$valid) {
                $json = [
                    'error' => 'Maaf, ' . $validation->listErrors() . ''
                ];
            } else if (($sumber = $this->cariPemakaianNoDo($nofaktur)) !== null) {
                $json = [
                    'error' => "No. Transaksi {$nofaktur} sudah digunakan pada {$sumber}"
                ];
            } else if ($jml > intval($stokGudang)) {
                $json = [
                    'error' => 'Stok tidak mencukupi'
                ];
            } else if ($jml <= 0) {
                $json = [
                    'error' => 'Jumlah harus lebih dari 0'
                ];
            } else {
                $existingRow = $modelTempMaterialKeluar
                    ->where('detfaktur', $nofaktur)
                    ->where('detmatkode', $materialid)
                    ->first();

                if ($existingRow) {
                    $newJml = $existingRow['detjml'] + $jml;
                    if ($newJml > intval($stokGudang)) {
                        $json = [
                            'error' => 'Stok tidak mencukupi'
                        ];
                    } else {
                        $newSubtotal = $existingRow['detsubtotal'] + $jml;
                        $modelTempMaterialKeluar->update($existingRow['id'], [
                            'detjml' => $newJml,
                            'detsubtotal' => $newSubtotal
                        ]);
                        $json = [
                            'sukses' => 'Item Berhasil di Tambahkan'
                        ];
                    }
                } else {
                    if ($jml > intval($stok)) {
                        $json = [
                            'error' => 'Stok tidak mencukupi'
                        ];
                    } else {
                        $modelTempMaterialKeluar->insert([
                            'detfaktur' => $nofaktur,
                            'tgl' => $tglfaktur,
                            'idmat' => $idmat,
                            'detmatkode' => $materialid,
                            'detmatkatid' => $materialkatid,
                            'detmatsatid' => $materialsatid,
                            'detjml' => $jml,
                            'detsubtotal' => intval($jml)
                        ]);
                        $json = [
                            'sukses' => 'Item Berhasil di Tambahkan'
                        ];
                    }
                }
            }

            echo json_encode($json);
        }
    }

    function hapusItem()
    {
        if ($this->request->isAJAX()) {
            $id = $this->request->getPost('id');

            $modelTempMaterialKeluar = new ModelTempMaterialKeluar();
            $modelTempMaterialKeluar->delete($id);

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

    public function modalCariMaterial()
    {
        if ($this->request->isAJAX()) {
            $json = [
                'data' => view('materialkeluar/modalcarimaterial')
            ];
            echo json_encode($json);
        }
    }

    function listDataMaterial()
    {
        $request = Services::request();
        $datamodel = new ModelDataStokMaterial($request);
        if ($request->getMethod(true) == 'POST') {
            $lists = $datamodel->get_datatables();
            $data = [];
            $no = $request->getPost("start");
            foreach ($lists as $list) {
                $no++;
                $row = [];

                $stokGudang1 = $datamodel->where('materialid', $list->materialid)->where('gudang', 1)->first();
                $stokGudang2 = $datamodel->where('materialid', $list->materialid)->where('gudang', 2)->first();

                $stokGudang1Value = isset($stokGudang1['stok']) ? $stokGudang1['stok'] : 0;
                $stokGudang2Value = isset($stokGudang2['stok']) ? $stokGudang2['stok'] : 0;

                $tombolPilih = "<button type=\"button\" class=\"btn btn-sm btn-info\" onclick=\"pilih('" . $list->materialid . "','" . $list->kodematerial . "','" . $list->materialkatid . "','" . $list->materialsatid . "')\">Pilih</button>";

                $row[] = $no;
                $row[] = $list->kodematerial;
                $row[] = $list->namamaterial;
                $row[] = number_format($stokGudang1Value, 0, ",", ".");
                $row[] = number_format($stokGudang2Value, 0, ",", ".");
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

    function selesaiTransaksi()
    {
        if ($this->request->isAJAX()) {
            $nofaktur = trim((string) $this->request->getPost('nofaktur'));
            $tglfaktur = $this->request->getPost('tglfaktur');
            $idsupplier = $this->request->getPost('idsupplier');
            $idgudang = $this->request->getPost('idgudang');
            $keterangan = $this->request->getPost('keterangan');

            $modelTemp = new ModelTempMaterialKeluar();
            $dataTemp = $modelTemp->getWhere(['detfaktur' => $nofaktur]);
            $validation = \Config\Services::validation();

            $valid = $this->validate([
                'idsupplier' => [
                    'rules' => 'required',
                    'label' => 'Supplier',
                    'errors' => [
                        'required' => '{field} belum di pilih'
                    ]
                ],
            ]);

            if (!$valid) {
                $json = [
                    'error' => 'Maaf, ' . $validation->listErrors() . ''
                ];
            } else if (($sumber = $this->cariPemakaianNoDo($nofaktur)) !== null) {
                $json = [
                    'error' => "No. Transaksi {$nofaktur} sudah digunakan pada {$sumber}"
                ];
            } else if ($dataTemp->getNumRows() == 0) {
                $json = [
                    'error' => 'Maaf, data item untuk faktur ini belum ada'
                ];
            } else {
                $db = db_connect();
                $db->transStart();

                $modelMaterialKeluar = new ModelMaterialKeluar();
                $totalSubTotal = 0;
                foreach ($dataTemp->getResultArray() as $total) :
                    $totalSubTotal += intval($total['detsubtotal']);
                endforeach;

                $modelMaterialKeluar->insert([
                    'faktur' => $nofaktur,
                    'tglfaktur' => $tglfaktur,
                    'idsup' => $idsupplier,
                    'gudang' => $idgudang,
                    'totalberatmaterial' => $totalSubTotal,
                    'keterangan' => $keterangan,
                ]);

                $fieldDetail = [];
                foreach ($dataTemp->getResultArray() as $row) {
                    $fieldDetail[] = [
                        'detfaktur' => $row['detfaktur'],
                        'tgl' => $tglfaktur,
                        'detmatkode' => $row['detmatkode'],
                        'idmat' => $row['idmat'],
                        'idsup' => $idsupplier,
                        'gudang' => $idgudang,
                        'detjml' => $row['detjml'],
                        'detsubtotal' => $row['detsubtotal']
                    ];
                }
                // echo '<pre>';
                // print_r($fieldDetail);
                // echo '</pre>';
                // die();
                $modelDetail = new ModelDetailMaterialKeluar();
                $modelDetail->insertBatch($fieldDetail);

                //Hapus Temp
                $modelTemp->hapusData($nofaktur);
                $db->transComplete();

                $json = $db->transStatus() === false
                    ? ['error' => 'Transaksi Material Keluar gagal disimpan. Tidak ada data yang diubah.']
                    : ['sukses' => 'Transaksi Berhasil di Simpan'];
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

    function hapusTransaksi()
    {
        if ($this->request->isAJAX()) {
            $faktur = $this->request->getPost('faktur');

            $modelMaterialKeluar = new ModelMaterialKeluar();

            $db = \Config\Database::connect();
            $db->transStart();

            $db->table('detail_materialkeluar')->delete(['detfaktur' => $faktur]);
            $db->table('ngdata')->delete(['detfaktur' => $faktur]);
            $modelMaterialKeluar->delete($faktur);
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
                ? ['error' => 'Transaksi Material Keluar gagal dihapus. Tidak ada data yang diubah.']
                : ['sukses' => 'Transaksi berhasil di Hapus'];
            echo json_encode($json);
        }
    }

    public function edit($faktur)
    {
        $modelMaterialKeluar = new ModelMaterialKeluar();
        $cekFaktur = $modelMaterialKeluar->cekFaktur($faktur);

        if ($cekFaktur->getNumRows() > 0) {
            $row = $cekFaktur->getRowArray();

            $data = [
                'nofaktur' => $row['faktur'],
                'tanggal' => $row['tglfaktur'],
                'namasupplier' => $row['supnama'],
                'idsupplier' => $row['supid'],
                'namagudang' => $row['gdgnama'],
                'idgudang' => $row['gdgid'],
                'keterangan' => $row['keterangan'],
            ];
            return view('materialkeluar/formedit', $data);
        } else {
            exit('Data tidak ditemukan');
        }

        // $modelMaterialKeluar = new ModelMaterialKeluar();
        // $modelSupplier = new ModelSupplier();
        // $rowData = $modelMaterialKeluar->find($faktur);
        // $rowSupplier = $modelSupplier->find($rowData['idsup']);

        // $data = [
        //     'nofaktur' => $faktur,
        //     'tanggal' => $rowData['tglfaktur'],
        //     'namasupplier' => $rowSupplier['supnama'],
        //     'idsupplier' => $rowSupplier['supid'],
        // ];

        // return view('materialkeluar/formedit', $data);
    }

    function ambilTotalBerat()
    {
        if ($this->request->isAJAX()) {
            $nofaktur = $this->request->getPost('nofaktur');
            $modelDetail = new ModelDetailMaterialKeluar();
            $totalBerat = $modelDetail->ambilTotalBerat($nofaktur);

            $json = [
                'totalberat' => "Total : " . number_format($totalBerat, 0, ",", ".") . " " . "KG"
            ];
            echo json_encode($json);
        }
    }

    function tampilDataDetail()
    {
        if ($this->request->isAJAX()) {
            $nofaktur = $this->request->getPost('nofaktur');

            $modelDetail = new ModelDetailMaterialKeluar();
            $dataTemp = $modelDetail->tampilDataTemp($nofaktur);
            $data = [
                'tampildata' => $dataTemp
            ];

            $json = [
                'data' => view('materialkeluar/datadetail', $data)
            ];
            echo json_encode($json);
        }
    }

    function hapusItemDetail()
    {
        if ($this->request->isAJAX()) {
            $id = $this->request->getPost('id');
            $tglfaktur = $this->request->getPost('tglfaktur');
            $nofaktur = $this->request->getPost('nofaktur');
            $idsupplier = $this->request->getPost('idsupplier');
            $materialid = $this->request->getPost('materialid');

            $modelDetail = new ModelDetailMaterialKeluar();
            $modelNg = new Modelng();
            $modelMaterialKeluar = new ModelMaterialKeluar();

            $rowData = $modelDetail->find($id);
            if (!$rowData) {
                return $this->response->setJSON(['error' => 'Data item tidak ditemukan.']);
            }
            $noFaktur = $rowData['detfaktur'];

            $stokData = $modelNg->select('id')
                ->where('detfaktur', $nofaktur)
                ->where('tgl', $tglfaktur)
                ->where('idsup', $idsupplier)
                ->where('matjenis', $materialid)
                ->first();

            $existingRow = $modelNg->where('matjenis', $materialid)->where('detfaktur', $noFaktur)->first();
            $db = db_connect();
            $db->transStart();

            if ($stokData) {
                $materialid = $stokData['id'];
            }
            $modelDetail->delete($id);
            if ($existingRow) {
                $modelNg->delete($materialid);
            }

            $totalBerat = $modelDetail->ambilTotalBerat($noFaktur);

            $modelMaterialKeluar->update($noFaktur, [
                'totalberatmaterial' => $totalBerat
            ]);
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
                ? ['error' => 'Item Material Keluar gagal dihapus. Tidak ada data yang diubah.']
                : ['sukses' => 'Item Berhasil di Hapus'];
            echo json_encode($json);
        }
    }

    function editItem()
    {
        if ($this->request->isAJAX()) {
            $iddetail = $this->request->getPost('iddetail');
            $materialid = $this->request->getPost('materialid');
            $jml = $this->request->getPost('jml');

            $modelDetail = new ModelDetailMaterialKeluar();
            $modelMaterialKeluar = new ModelMaterialKeluar();
            $modelStok = new ModelStokMaterial();

            $rowData = $modelDetail->find($iddetail);
            if (!$rowData) {
                return $this->response->setJSON(['error' => 'Data item tidak ditemukan.']);
            }
            $noFaktur = $rowData['detfaktur'];
            $gudang = $rowData['gudang'];
            $jumlahSebelumnya = $rowData['detjml'];

            $stokMaterial = $modelStok->getStokByMaterialIdGudang($materialid, $gudang);

            $jmlStok = intval($stokMaterial) + intval($jumlahSebelumnya);

            if ($jml > intval($jmlStok)) {
                $json = [
                    'error' => 'Stok tidak mencukupi'
                ];
            } else {
                $db = db_connect();
                $db->transStart();

                $modelDetail->update($iddetail, [
                    'detjml' => $jml,
                    'detsubtotal' => $jml
                ]);

                $totalBerat = $modelDetail->ambilTotalBerat($noFaktur);

                $modelMaterialKeluar->update($noFaktur, [
                    'totalberatmaterial' => $totalBerat
                ]);
                $db->transComplete();

                $json = $db->transStatus() === false
                    ? ['error' => 'Item Material Keluar gagal diperbarui. Tidak ada data yang diubah.']
                    : ['sukses' => 'Item Berhasil di Update'];
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
            $tglfaktur = $this->request->getPost('tglfaktur');
            $materialid = $this->request->getPost('materialid');
            $idsupplier = $this->request->getPost('idsupplier');
            $idmat = $this->request->getPost('idmat');
            $idgudang = $this->request->getPost('idgudang');
            $jml = $this->request->getPost('jml');

            $modelTempMaterialKeluar = new ModelDetailMaterialKeluar();
            $modelMaterialKeluar = new ModelMaterialKeluar();
            $modelMaterial = new Modelmaterial();

            $ambilDataMaterial = $modelMaterial->find($materialid);
            if (!$ambilDataMaterial) {
                return $this->response->setJSON(['error' => 'Data material tidak ditemukan.']);
            }
            $stokMaterial = $ambilDataMaterial['matstok'];

            if ($jml > intval($stokMaterial)) {
                return $this->response->setJSON([
                    'error' => 'Stok tidak mencukupi'
                ]);
            }

            $db = db_connect();
            $db->transStart();

            $existingRow = $modelTempMaterialKeluar->where('detmatkode', $materialid)->where('detfaktur', $nofaktur)
                ->first();

            if ($existingRow) {
                $newJml = $existingRow['detjml'] + $jml;
                $newSubtotal = $existingRow['detsubtotal'] + $jml;

                $modelTempMaterialKeluar->update($existingRow['id'], [
                    'detjml' => $newJml,
                    'detsubtotal' => $newSubtotal
                ]);
            } else {
                $modelTempMaterialKeluar->insert([
                    'detfaktur' => $nofaktur,
                    'detmatkode' => $materialid,
                    'idmat' => $idmat,
                    'gudang' => $idgudang,
                    'tgl' => $tglfaktur,
                    'idsup' => $idsupplier,
                    'detjml' => $jml,
                    'detsubtotal' => $jml
                ]);
            }

            $totalBerat = $modelTempMaterialKeluar->ambilTotalBerat($nofaktur);

            $modelMaterialKeluar->update($nofaktur, [
                'totalberatmaterial' => $totalBerat
            ]);
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
                ? ['error' => 'Item Material Keluar gagal ditambahkan. Tidak ada data yang diubah.']
                : ['sukses' => 'Item Berhasil di Tambahkan'];
            echo json_encode($json);
        }
    }
}
