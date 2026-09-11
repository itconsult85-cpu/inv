<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ModelMaterialPackaging;
use \Hermawan\DataTables\DataTable;

class Packaging extends BaseController
{
    public function __construct()
    {
        $this->packaging = new ModelMaterialPackaging();
    }

    public function index()
    {
        return view('packaging/viewdatamaterialpackaging');
    }

    public function cekdata()
    {
        $kodematerial = $this->request->getVar('kodematerial');
        $namamaterial = $this->request->getVar('namamaterial');

        $materialModel = new ModelMaterialPackaging();

        // Cek apakah data dengan kodebarang sudah ada dalam tabel
        $existKode = $materialModel->where('matpkode', $kodematerial)->countAllResults() > 0;
        $existNama = $materialModel->where('matpnama', $namamaterial)->countAllResults() > 0;

        return $this->response->setJSON(['existKode' => $existKode, 'existNama' => $existNama]);
    }

    public function listData()
    {
        if ($this->request->isAJAX()) {
            // $db = db_connect();
            $db = \Config\Database::connect();
            $builder = $db->table('materialpackaging')->select('matpid,matpkode,matpnama,matpstok');

            return DataTable::of($builder)
                ->addNumbering('nomor')
                ->add('aksi', function ($row) {
                    return "<button type=\"button\" class=\"btn btn-sm btn-primary\" title=\"Edit Data\" onclick=\"edit('" . ($row->matpid) . "')\"><i class=\"fa fa-edit\"></i></button>&nbsp
                    <button type=\"button\" class=\"btn btn-sm btn-danger\" title=\"Hapus Data\" onclick=\"hapus('" . $row->matpid . "','" . $row->matpkode . "')\"><i class=\"fa fa-trash-alt\"></i></button>";
                })
                ->toJson(true);
        }
    }

    public function tambah()
    {
        return view('packaging/formtambah');
    }

    public function simpandata()
    {
        if ($this->request->isAJAX()) {
            $kodematerial = $this->request->getVar('kodematerial');
            $namamaterial = $this->request->getVar('namamaterial');
            $stok = $this->request->getVar('stok');

            $validation = \Config\Services::validation();

            $valid = $this->validate([
                'kodematerial' => [
                    'rules' => 'required|is_unique[materialpackaging.matpkode]',
                    'label' => 'Kode Material',
                    'errors' => [
                        'required' => '{field} tidak boleh kosong',
                        'is_unique' => '{field} sudah terpakai'
                    ]
                ],
                'namamaterial' => [
                    'rules' => 'required|is_unique[materialpackaging.matpnama]',
                    'label' => 'Nama Material',
                    'errors' => [
                        'required' => '{field} tidak boleh kosong',
                        'is_unique' => '{field} sudah terpakai'
                    ]
                ],
                'stok' => [
                    'rules' => 'required|numeric',
                    'label' => 'Stok',
                    'errors' => [
                        'required' => '{field} tidak boleh kosong',
                        'numeric' => '{numeric} hanya dalam bentuk angka'
                    ]
                ],

                
            ]);

            if (!$valid) {
                $json = [
                    'error' => 'Maaf, ' . $validation->listErrors() . ''
                ];
            } else {
                $this->packaging->insert([
                    'matpkode' => $kodematerial,
                    'matpnama' => $namamaterial,
                    'matpstok' => $stok
                ]);
                $json = [
                    'sukses' => 'Item berhasil di tambahkan'
                ];
            }
            echo json_encode($json);
        } else {
            exit('Maaf tidak bisa dipanggil');
        }
    }

    public function edit($id)
    {
        $cekData = $this->packaging->find($id);

        if ($cekData) {


            $data = [
                'id' => $id,
                'kodematerial' => $cekData['matpkode'],
                'namamaterial' => $cekData['matpnama'],
                'stok' => $cekData['matpstok'],
            ];
            return view('packaging/formedit', $data);
        } else {
            // exit('Maaf data tidak di temukan...!!!');
            $pesan_error = [
                'error' => '<div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-ban"></i> Error!</h5>
                Data material tidak di temukan...!!!
              </div>'
            ];

            session()->setFlashdata($pesan_error);
            return redirect()->to('/packaging/index');
        }
    }

    public function updatedata()
    {
        // if ($this->request->isAJAX()) {
            $idmaterial = $this->request->getVar('idmaterial');
            $kodematerial = $this->request->getVar('kodematerial');
            $namamaterial = $this->request->getVar('namamaterial');
            $stok = $this->request->getVar('stok');

            $validation = \Config\Services::validation();

            $valid = $this->validate([
                'kodematerial' => [
                    'rules' => 'required|is_unique[materialpackaging.matpkode,matpid,' . $idmaterial . ']',
                    'label' => 'Kode Material',
                    'errors' => [
                        'required' => '{field} tidak boleh kosong',
                        'is_unique' => '{field} sudah terpakai material packaging lain',
                    ]
                ],
                'namamaterial' => [
                    'rules' => 'required|is_unique[materialpackaging.matpnama,matpid,' . $idmaterial . ']',
                    'label' => 'Nama Material',
                    'errors' => [
                        'required' => '{field} tidak boleh kosong',
                        'is_unique' => '{field} sudah terpakai material packaging lain',
                    ]
                ],
                'stok' => [
                    'rules' => 'required|numeric',
                    'label' => 'Stok',
                    'errors' => [
                        'required' => '{field} tidak boleh kosong',
                        'numeric' => '{numeric} hanya dalam bentuk angka'
                    ]
                ],
            ]);

            if (!$valid) {
                $sess_Pesan = [
                    'error' => '<div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-ban"></i> Error!</h5>
                ' . $validation->listErrors() . '
              </div>'
                ];

                session()->setFlashdata($sess_Pesan);
                return redirect()->to('/packaging/edit/' . $idmaterial);
            } else {
                $this->packaging->update($idmaterial, [
                    'id' => $idmaterial,
                    'matpkode' => $kodematerial,
                    'matpnama' => $namamaterial,
                    'matpstok' => $stok,
                ]);

                $pesan_sukses = [
                    'sukses' => '<div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-check"></i> Berhasil!</h5>
                Data Material dengan kode <strong>' . $kodematerial . '</strong> berhasil di update
              </div>'
                ];

                session()->setFlashdata($pesan_sukses);
                return redirect()->to('/packaging/index');
            }
        // }
    }

    function hapus()
    {
        if ($this->request->isAJAX()) {
            $kode = $this->request->getPost('kode');

            $modelmaterial = new ModelMaterialPackaging();

            $modelmaterial->delete($kode);

            $json = [
                'sukses' => 'Data Material Berhasil di Hapus'
            ];
            echo json_encode($json);
        }
    }
}
