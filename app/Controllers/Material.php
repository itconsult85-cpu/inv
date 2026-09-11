<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Modelmaterial;
use App\Models\Modelkategori;
use App\Models\Modelsatuan;
use App\Models\ModelStokMaterial;
use App\Models\ModelSupplier;
use \Hermawan\DataTables\DataTable;

class Material extends BaseController
{
    protected $material;

    public function __construct()
    {
        $this->material = new Modelmaterial();
    }

    private function relasiMaterial(int $id): array
    {
        return $this->periksaRelasiMaster([
            ['table' => 'barang', 'column' => 'brgmat', 'label' => 'Produk', 'csv' => true],
            ['table' => 'berat', 'column' => 'kodemat', 'label' => 'Berat/Ukuran Bersih'],
            ['table' => 'berat_material', 'column' => 'matid', 'label' => 'Detail Berat/Ukuran Bersih'],
            ['table' => 'detail_barangmasuk', 'column' => 'detmatkode', 'label' => 'Produk Masuk'],
            ['table' => 'detail_materialmasuk', 'column' => 'detmatkode', 'label' => 'Material Masuk'],
            ['table' => 'detail_materialkeluar', 'column' => 'detmatkode', 'label' => 'Material Keluar'],
            ['table' => 'ngdata', 'column' => 'matjenis', 'label' => 'Data Raw/NG'],
            ['table' => 'temp_barangmasuk', 'column' => 'detmatkode', 'label' => 'Draft Produk Masuk'],
            ['table' => 'temp_materialmasuk', 'column' => 'detmatkode', 'label' => 'Draft Material Masuk'],
            ['table' => 'temp_materialkeluar', 'column' => 'detmatkode', 'label' => 'Draft Material Keluar'],
            ['table' => 'stokmaterial', 'column' => 'materialid', 'label' => 'Stok Material', 'where' => ['stok >' => 0]],
        ], $id);
    }

    public function pemakaian()
    {
        $hash = (string) $this->request->getGet('hash');
        $cekId = $this->material->cekId($hash);

        if ($cekId->getNumRows() === 0) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Data material tidak ditemukan.']);
        }

        $id = (int) $cekId->getRowArray()['matid'];
        return $this->response->setJSON(['pemakaian' => $this->relasiMaterial($id)]);
    }

    public function index()
    {
        return view('material/viewdatamaterial');
    }

    public function cekdata()
    {
        $kodematerial = $this->request->getVar('kodematerial');
        $namamaterial = $this->request->getVar('namamaterial');

        $materialModel = new Modelmaterial();

        $existKode = $materialModel->where('matkode', $kodematerial)->countAllResults() > 0;
        $existNama = $materialModel->where('matnama', $namamaterial)->countAllResults() > 0;

        return $this->response->setJSON(['existKode' => $existKode, 'existNama' => $existNama]);
    }

    public function listData()
    {
        if ($this->request->isAJAX()) {
            $db = \Config\Database::connect();
            $builder = $db->table('material')->select('matid,matkode,matnama,katnama,satnama,matstok,CONCAT(matstok, " ", satnama) AS mat_satuan')
                ->join('kategori', 'katid=matkatid')
                ->join('satuan', 'satid=matsatid');

            return DataTable::of($builder)
                ->addNumbering('nomor')
                ->add('aksi', function ($row) {
                    return "<button type=\"button\" class=\"btn btn-sm btn-primary\" title=\"Edit Data\" onclick=\"edit('" . sha1($row->matid) . "')\"><i class=\"fa fa-edit\"></i></button>&nbsp
                    <button type=\"button\" class=\"btn btn-sm btn-info\" title=\"Label Nama per Supplier (buat PO Keluar)\" onclick=\"labelSupplier('" . sha1($row->matid) . "')\"><i class=\"fa fa-tag\"></i></button>&nbsp
                    <button type=\"button\" class=\"btn btn-sm btn-danger\" title=\"Hapus Data\" onclick=\"hapus('" . $row->matid . "','" . $row->matkode . "')\"><i class=\"fa fa-trash-alt\"></i></button>";
                })
                ->toJson(true);
        }
    }

    public function tambah()
    {
        $modelkategori = new Modelkategori();
        $modelsatuan = new Modelsatuan();

        $data = [
            'datakategori' => $modelkategori->findAll(),
            'datasatuan' => $modelsatuan->findAll(),
        ];
        return view('material/formtambah', $data);
    }

    public function simpandata()
    {
        if ($this->request->isAJAX()) {
            $request = $this->request->getVar();

            $validation = \Config\Services::validation();

            $valid = $this->validate([
                'kodematerial' => [
                    'rules' => 'required|is_unique[material.matkode]',
                    'label' => 'Kode Material',
                    'errors' => [
                        'required' => '{field} tidak boleh kosong',
                        'is_unique' => '{field} sudah terpakai'
                    ]
                ],
                'namamaterial' => [
                    'rules' => 'required|is_unique[material.matnama]',
                    'label' => 'Nama Material',
                    'errors' => [
                        'required' => '{field} tidak boleh kosong',
                        'is_unique' => '{field} sudah terpakai'
                    ]
                ],
                'kategori' => 'required',
                'satuan' => 'required',
                'minstok' => [
                    'rules' => 'required|numeric',
                    'label' => 'Minimal Stok',
                    'errors' => [
                        'required' => '{field} tidak boleh kosong',
                        'numeric' => '{field} hanya dalam bentuk angka'
                    ]
                ],
            ]);

            if (!$valid) {
                $json = [
                    'error' => 'Maaf, ' . $validation->listErrors()
                ];
            } else {
                $materialModel = new Modelmaterial();
                $materialId = $materialModel->insert([
                    'matkode' => $request['kodematerial'],
                    'matnama' => $request['namamaterial'],
                    'matkatid' => $request['kategori'],
                    'matsatid' => $request['satuan'],
                    'minmat' => $request['minstok'],
                    'matstok' => 0,
                    'matgudang' => 1,
                ]);

                if ($materialId) {
                    $dataStok = [
                        [
                            'kodematerial' => $request['kodematerial'],
                            'namamaterial' => $request['namamaterial'],
                            'materialid' => $materialId,
                            'materialkatid' => $request['kategori'],
                            'materialsatid' => $request['satuan'],
                            'gudang' => 1,
                            'stok' => 0,
                        ],
                        [
                            'kodematerial' => $request['kodematerial'],
                            'namamaterial' => $request['namamaterial'],
                            'materialid' => $materialId,
                            'materialkatid' => $request['kategori'],
                            'materialsatid' => $request['satuan'],
                            'gudang' => 2,
                            'stok' => 0,
                        ]
                    ];

                    $modelStok = new ModelStokMaterial();
                    $modelStok->updateOrInsertBatch($dataStok);

                    $json = [
                        'sukses' => 'Item berhasil ditambahkan'
                    ];
                }
            }
            echo json_encode($json);
        } else {
            exit('Maaf tidak bisa dipanggil');
        }
    }

    public function edit($id)
    {
        $modelMaterial = new Modelmaterial();
        $cekId = $modelMaterial->cekId($id);

        if ($cekId->getNumRows() > 0) {
            $row = $cekId->getRowArray();

            $modelkategori = new Modelkategori();
            $modelsatuan = new Modelsatuan();

            $data = [
                'id' => $id,
                'kodematerial' => $row['matkode'],
                'namamaterial' => $row['matnama'],
                'kategori' => $row['matkatid'],
                'satuan' => $row['matsatid'],
                'stok' => $row['matstok'],
                'minstok' => $row['minmat'],
                'datakategori' => $modelkategori->findAll(),
                'datasatuan' => $modelsatuan->findAll(),
            ];
            return view('material/formedit', $data);
        } else {
            $pesan_error = [
                'error' => '<div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-ban"></i> Error!</h5>
                Data material tidak di temukan...!!!
              </div>'
            ];

            session()->setFlashdata($pesan_error);
            return redirect()->to('/material/index');
        }
    }

    public function updatedata()
    {
        $request = $this->request->getVar();
        $validation = \Config\Services::validation();
        $modelMaterial = new Modelmaterial();
        $cekId = $modelMaterial->cekId($request['idmaterial']);

        if ($cekId->getNumRows() === 0) {
            session()->setFlashdata('error', $this->generateAlert('danger', 'Gagal!', 'Data Material Tidak Ditemukan'));
            return redirect()->to('/material/index');
        }

        $idAsli = $cekId->getRowArray()['matid'];

        // matid adalah key yang dipakai di semua relasi (barang, berat,
        // transaksi, dst) -- matkode cuma label tampilan, jadi aman diedit
        // kapan saja tanpa perlu ngecek pemakaian dulu.
        $rules = [
            'kodematerial' => [
                'rules' => 'required|is_unique[material.matkode,matid,' . $idAsli . ']',
                'label' => 'Kode Material',
                'errors' => [
                    'required' => '{field} tidak boleh kosong',
                    'is_unique' => '{field} sudah terpakai material lain'
                ]
            ],
            'namamaterial' => [
                'rules' => 'required|is_unique[material.matnama,matid,' . $idAsli . ']',
                'label' => 'Nama Material',
                'errors' => [
                    'required' => '{field} tidak boleh kosong',
                    'is_unique' => '{field} sudah terpakai material lain'
                ]
            ],
            'kategori' => [
                'rules' => 'required',
                'label' => 'Kategori',
                'errors' => ['required' => '{field} belum dipilih']
            ],
            'satuan' => [
                'rules' => 'required',
                'label' => 'Satuan',
                'errors' => ['required' => '{field} belum dipilih']
            ],
            'stok' => [
                'rules' => 'required|numeric',
                'label' => 'Stok',
                'errors' => [
                    'required' => '{field} tidak boleh kosong',
                    'numeric' => '{field} hanya dalam bentuk angka'
                ]
            ],
            'minstok' => [
                'rules' => 'required|numeric',
                'label' => 'Minimal Stok',
                'errors' => [
                    'required' => '{field} tidak boleh kosong',
                    'numeric' => '{field} hanya dalam bentuk angka'
                ]
            ],
        ];

        if (!$this->validate($rules)) {
            session()->setFlashdata('error', $this->generateAlert('danger', 'Error!', $validation->listErrors()));
            return redirect()->to('/material/edit/' . $request['idmaterial']);
        }

        if ($cekId->getNumRows() > 0) {
            $materialData = [
                'id' => $request['idmaterial'],
                'matkode' => $request['kodematerial'],
                'matnama' => $request['namamaterial'],
                'matkatid' => $request['kategori'],
                'matsatid' => $request['satuan'],
                'matstok' => $request['stok'],
                'minmat' => $request['minstok'],
            ];

            if ($modelMaterial->update($idAsli, $materialData)) {
                $this->updateStokMaterial($idAsli, $request);
                session()->setFlashdata('sukses', $this->generateAlert('success', 'Berhasil!', 'Data Material Berhasil di Edit'));
            } else {
                session()->setFlashdata('error', $this->generateAlert('danger', 'Gagal!', 'Data Material Gagal di Edit'));
            }
        } else {
            session()->setFlashdata('error', $this->generateAlert('danger', 'Gagal!', 'Data Material Tidak Ditemukan'));
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

        return redirect()->to('/material/index');
    }

    private function updateStokMaterial($idAsli, $request)
    {
        $modelStokMaterial = new ModelStokMaterial();
        $stokData = $modelStokMaterial->where('materialid', $idAsli)->findAll();

        if ($stokData) {
            foreach ($stokData as &$data) {
                $data['kodematerial'] = $request['kodematerial'];
                $data['namamaterial'] = $request['namamaterial'];
                $data['materialkatid'] = $request['kategori'];
                $data['materialsatid'] = $request['satuan'];
            }
            $modelStokMaterial->updateBatch($stokData, 'id');
        }
    }

    private function generateAlert($type, $title, $message)
    {
        return '<div class="alert alert-' . $type . ' alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h5><i class="icon fas fa-' . ($type == 'success' ? 'check' : 'ban') . '"></i> ' . $title . '!</h5>
        ' . $message . '
    </div>';
    }


    public function deletedata($idmaterial)
    {
        $modelMaterial = new Modelmaterial();
        $modelStokMaterial = new ModelStokMaterial();

        $cekId = $modelMaterial->cekId($idmaterial);
        if ($cekId->getNumRows() > 0) {
            $pemakaian = $this->relasiMaterial((int) $idmaterial);
            if ($pemakaian) {
                session()->setFlashdata(['error' => '<div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-ban"></i> Gagal!</h5>
                ' . $this->pesanRelasiMaster('material ini', $pemakaian) . '
              </div>']);
                return redirect()->to('/material/index');
            }

            $modelStokMaterial->where('materialid', $idmaterial)->delete();

            $delete = $modelMaterial->delete($idmaterial);

            if ($delete) {
                $pesan = [
                    'sukses' => '<div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-check"></i> Berhasil!</h5>
                Data Material Berhasil di Hapus
              </div>'
                ];
                session()->setFlashdata($pesan);
            } else {
                $pesan = [
                    'error' => '<div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-ban"></i> Gagal!</h5>
                Data Material Gagal di Hapus
              </div>'
                ];
                session()->setFlashdata($pesan);
            }
        } else {
            $pesan = [
                'error' => '<div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-ban"></i> Gagal!</h5>
            Data Material Tidak Ditemukan
          </div>'
            ];
            session()->setFlashdata($pesan);
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

        return redirect()->to('/material/index');
    }


    /**
     * Label Nama per Supplier -- dipakai pas bikin PO Keluar. Material
     * fisiknya sama (1 matnama di master), tapi supplier yang beda-beda
     * kadang butuh nama yang beda di dokumen PO (sesuai istilah yang mereka
     * kenal). Kalau supplier di PO Keluar gak punya label khusus, sistem
     * fallback ke matnama biasa.
     */
    private function ensureMaterialLabelSupplierTable(): void
    {
        $db = \Config\Database::connect();
        if ($db->tableExists('material_label_supplier')) {
            return;
        }

        $forge = \Config\Database::forge();
        $forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'material_id' => ['type' => 'INT', 'constraint' => 11],
            'supplier_id' => ['type' => 'INT', 'constraint' => 11],
            'label_nama' => ['type' => 'VARCHAR', 'constraint' => 150],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $forge->addKey('id', true);
        $forge->addUniqueKey(['material_id', 'supplier_id']);
        $forge->createTable('material_label_supplier');
    }

    public function labelSupplier()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $this->ensureMaterialLabelSupplierTable();

        $hash = trim((string) $this->request->getPost('hash'));
        $cekId = $this->material->cekId($hash);
        if ($cekId->getNumRows() === 0) {
            return $this->response->setJSON(['error' => 'Data material tidak ditemukan.']);
        }
        $material = $cekId->getRowArray();

        $db = \Config\Database::connect();
        $labels = $db->table('material_label_supplier mls')
            ->select('mls.id, mls.supplier_id, s.supnama, mls.label_nama')
            ->join('supplier s', 's.supid = mls.supplier_id')
            ->where('mls.material_id', $material['matid'])
            ->orderBy('s.supnama', 'ASC')
            ->get()->getResultArray();

        $suppliers = (new ModelSupplier())->orderBy('supnama', 'ASC')->findAll();

        return $this->response->setJSON([
            'sukses' => true,
            'material_nama' => $material['matnama'],
            'labels' => $labels,
            'suppliers' => array_map(static fn (array $s): array => [
                'id' => (int) $s['supid'],
                'nama' => $s['supnama'],
            ], $suppliers),
        ]);
    }

    public function simpanLabelSupplier()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $this->ensureMaterialLabelSupplierTable();

        $hash = trim((string) $this->request->getPost('hash'));
        $cekId = $this->material->cekId($hash);
        if ($cekId->getNumRows() === 0) {
            return $this->response->setJSON(['error' => 'Data material tidak ditemukan.']);
        }
        $material = $cekId->getRowArray();

        $supplierId = (int) $this->request->getPost('supplier_id');
        $labelNama = trim((string) $this->request->getPost('label_nama'));

        if ($supplierId <= 0) {
            return $this->response->setJSON(['error' => 'Supplier harus dipilih.']);
        }
        if ($labelNama === '') {
            return $this->response->setJSON(['error' => 'Label Nama tidak boleh kosong.']);
        }

        $supplier = (new ModelSupplier())->find($supplierId);
        if (!$supplier) {
            return $this->response->setJSON(['error' => 'Supplier tidak ditemukan.']);
        }

        $db = \Config\Database::connect();
        $existing = $db->table('material_label_supplier')
            ->where('material_id', $material['matid'])
            ->where('supplier_id', $supplierId)
            ->get()->getRowArray();

        if ($existing) {
            $db->table('material_label_supplier')->where('id', $existing['id'])->update([
                'label_nama' => $labelNama,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        } else {
            $db->table('material_label_supplier')->insert([
                'material_id' => $material['matid'],
                'supplier_id' => $supplierId,
                'label_nama' => $labelNama,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return $this->response->setJSON([
            'sukses' => 'Label Nama untuk supplier ' . $supplier['supnama'] . ' berhasil disimpan.',
        ]);
    }

    public function hapusLabelSupplier()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $this->ensureMaterialLabelSupplierTable();

        $id = (int) $this->request->getPost('id');
        if ($id <= 0) {
            return $this->response->setJSON(['error' => 'Data tidak valid.']);
        }

        \Config\Database::connect()->table('material_label_supplier')->where('id', $id)->delete();

        return $this->response->setJSON(['sukses' => 'Label Nama berhasil dihapus.']);
    }

    function hapus()
    {
        if ($this->request->isAJAX()) {
            $kode = $this->request->getPost('kode');
            $nama = $this->request->getPost('nama');

            $db = \Config\Database::connect();

            $pemakaian = $this->relasiMaterial((int) $kode);

            if ($pemakaian) {
                $json = [
                    'error' => $this->pesanRelasiMaster('material ' . $nama, $pemakaian)
                ];
            } else {
                $db->transStart();
                $modelMaterial = new Modelmaterial();
                $modelStokMaterial = new ModelStokMaterial();
                $modelStokMaterial->where('materialid', $kode)->delete();
                $modelMaterial->delete($kode);
                $db->transComplete();

                $json = $db->transStatus()
                    ? ['sukses' => "Data Material {$nama} Berhasil di Hapus"]
                    : ['error' => "Data Material {$nama} gagal dihapus"];
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
}
