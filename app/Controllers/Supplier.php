<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ModelSupplier;
use \Hermawan\DataTables\DataTable;

class Supplier extends BaseController
{
    private function jsArg($value): string
    {
        return json_encode((string) ($value ?? ''), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    }

    private function relasiSupplier(int $id): array
    {
        return $this->periksaRelasiMaster([
            ['table' => 'barangmasuk', 'column' => 'idsup', 'label' => 'Produk Masuk'],
            ['table' => 'detail_barangmasuk', 'column' => 'idsup', 'label' => 'Detail Produk Masuk'],
            ['table' => 'materialmasuk', 'column' => 'idsup', 'label' => 'Material Masuk'],
            ['table' => 'detail_materialmasuk', 'column' => 'idsup', 'label' => 'Detail Material Masuk'],
            ['table' => 'materialkeluar', 'column' => 'idsup', 'label' => 'Material Keluar'],
            ['table' => 'detail_materialkeluar', 'column' => 'idsup', 'label' => 'Detail Material Keluar'],
            ['table' => 'ngdata', 'column' => 'idsup', 'label' => 'Data Raw/NG'],
            ['table' => 'temp_barangmasuk', 'column' => 'idsup', 'label' => 'Draft Produk Masuk'],
            ['table' => 'temp_materialmasuk', 'column' => 'idsup', 'label' => 'Draft Material Masuk'],
            ['table' => 'temp_materialkeluar', 'column' => 'idsup', 'label' => 'Draft Material Keluar'],
        ], $id);
    }

    public function pemakaian()
    {
        $id = (int) $this->request->getGet('id');
        return $this->response->setJSON(['pemakaian' => $this->relasiSupplier($id)]);
    }

    public function index()
    {
        return view('supplier/viewdata');
    }

    public function listData()
    {
        if ($this->request->isAJAX()) {
            $db = \Config\Database::connect();
            $builder = $db->table('supplier')->select('supid,supnama,suppic,supemail,suptelp,alamat');
            if ($this->request->getGet('exclude_internal') === '1') {
                $builder->whereNotIn('supid', [1, 2]);
            }

            return DataTable::of($builder)
                ->addNumbering('nomor')
                ->add('aksi', function ($row) {
                    $pilih = "pilih(" . $this->jsArg($row->supid) . "," . $this->jsArg($row->supnama) . ")";
                    $edit = "editData(" . $this->jsArg($row->supid) . "," . $this->jsArg($row->supnama) . "," . $this->jsArg($row->suppic) . "," . $this->jsArg($row->supemail) . "," . $this->jsArg($row->suptelp) . "," . $this->jsArg($row->alamat) . ")";
                    $hapus = "hapus(" . $this->jsArg($row->supid) . "," . $this->jsArg($row->supnama) . ")";
                    if (\App\Libraries\AccessControl::can('master.supplier.delete')) {
                        return "<button type=\"button\" class=\"btn btn-sm btn-info\" title=\"Pilih Data\" onclick='{$pilih}'><i class=\"fa fa-check\"></i></button>&nbsp
                    <button type=\"button\" class=\"btn btn-sm btn-primary\" title=\"Edit Data\" id=\"tombolEditSupplier\" onclick='{$edit}'><i class=\"fa fa-edit\"></i></button>&nbsp
                    <button type=\"button\" class=\"btn btn-sm btn-danger\" title=\"Hapus Data\" onclick='{$hapus}'><i class=\"fa fa-trash-alt\"></i></button>";
                    }
                    return "<button type=\"button\" class=\"btn btn-sm btn-info\" title=\"Pilih Data\" onclick='{$pilih}'><i class=\"fa fa-check\"></i></button>&nbsp
                    <button type=\"button\" class=\"btn btn-sm btn-primary\" title=\"Edit Data\" id=\"tombolEditSupplier\" onclick='{$edit}'><i class=\"fa fa-edit\"></i></button>";
                })
                ->toJson(true);
        }
    }

    public function listDataView()
    {
        if ($this->request->isAJAX()) {
            $db = \Config\Database::connect();
            $builder = $db->table('supplier')->select('supid,supnama,suppic,supemail,suptelp,alamat');

            return DataTable::of($builder)
                ->addNumbering('nomor')
                ->add('aksi', function ($row) {
                    $edit = "editData(" . $this->jsArg($row->supid) . "," . $this->jsArg($row->supnama) . "," . $this->jsArg($row->suppic) . "," . $this->jsArg($row->supemail) . "," . $this->jsArg($row->suptelp) . "," . $this->jsArg($row->alamat) . ")";
                    $hapus = "hapus(" . $this->jsArg($row->supid) . "," . $this->jsArg($row->supnama) . ")";
                    return "<button type=\"button\" class=\"btn btn-sm btn-primary\" title=\"Edit Data\" id=\"tombolEditSupplier\" onclick='{$edit}'><i class=\"fa fa-edit\"></i></button>&nbsp
                    <button type=\"button\" class=\"btn btn-sm btn-danger\" title=\"Hapus Data\" onclick='{$hapus}'><i class=\"fa fa-trash-alt\"></i></button>";
                })
                ->toJson(true);
        }
    }

    public function formtambah()
    {
        $json = ['data' => view('supplier/modaltambah')];
        echo json_encode($json);
    }

    public function simpan()
    {
        $namasupplier = $this->request->getPost('namasup');
        $namapic = $this->request->getPost('namapic');
        $email = trim((string) $this->request->getPost('email'));
        $telp = $this->request->getPost('telp');
        $alamat = $this->request->getPost('alamat');

        $validation = \Config\Services::validation();

        $valid = $this->validate([
            'namasup' => [
                'rules' => 'required|is_unique[supplier.supnama]',
                'label' => 'Nama Supplier',
                'errors' => [
                    'required' => '{field} tidak boleh kosong',
                    'is_unique' => '{field} sudah digunakan Supplier lain'
                ]
            ],
            'namapic' => [
                'rules' => 'required',
                'label' => 'Nama PIC',
                'errors' => [
                    'required' => '{field} tidak boleh kosong'
                ]
            ],
            'email' => [
                'rules' => 'permit_empty|valid_email|is_unique[supplier.supemail]',
                'label' => 'Email',
                'errors' => [
                    'valid_email' => '{field} tidak valid',
                    'is_unique' => '{field} sudah digunakan Supplier lain'
                ]
            ],
            'telp' => [
                'rules' => 'required|numeric|is_unique[supplier.suptelp]',
                'label' => 'No Telp / Handphone',
                'errors' => [
                    'required' => '{field} tidak boleh kosong',
                    'numeric' => '{field} harus berisi angka',
                    'is_unique' => '{field} sudah digunakan Supplier lain'
                ]
            ],
            'alamat' => [
                'rules' => 'required',
                'label' => 'Alamat',
                'errors' => [
                    'required' => '{field} tidak boleh kosong'
                ]
            ]
        ]);

        if (!$valid) {
            $json = [
                'error' => [
                    'errNamaSupplier' => $validation->getError('namasup'),
                    'errNamaPic' => $validation->getError('namapic'),
                    'errEmail' => $validation->getError('email'),
                    'errTelp' => $validation->getError('telp'),
                    'errAlamat' => $validation->getError('alamat'),
                ]
            ];
        } else {
            $modelSupplier = new ModelSupplier();

            $idSupplier = $modelSupplier->insert([
                'supnama' => $namasupplier,
                'suppic' => $namapic,
                'supemail' => $email !== '' ? $email : null,
                'suptelp' => $telp,
                'alamat' => $alamat
            ]);

            $json = [
                'sukses' => 'Data Supplier Berhasil di Simpan',
                'supplier' => [
                    'id' => (string) $idSupplier,
                    'text' => $namasupplier,
                ],
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
    }

    public function modalData()
    {
        if ($this->request->isAJAX()) {
            $json = [
                'data' => view('supplier/modaldata', [
                    'excludeInternal' => $this->request->getGet('exclude_internal') === '1',
                ])
            ];
            echo json_encode($json);
        }
    }

    public function update()
    {
        $idSupplier = $this->request->getPost('id_supplier');
        $namasupplier = $this->request->getPost('editNamaSupplier');
        $namapic = $this->request->getPost('editNamaPic');
        $email = trim((string) $this->request->getPost('editEmail'));
        $telp = $this->request->getPost('editTelp');
        $alamat = $this->request->getPost('editAlamat');

        $modelSupplier = new ModelSupplier();
        $supplierLama = $modelSupplier->find($idSupplier);
        if (!$supplierLama) {
            return $this->response->setJSON([
                'error' => [
                    'errNamaSupplier' => 'Data Supplier tidak ditemukan',
                ],
            ]);
        }

        $pemakaian = $this->relasiSupplier((int) $idSupplier);
        if ($pemakaian && trim((string) $namasupplier) !== trim((string) $supplierLama['supnama'])) {
            return $this->response->setJSON([
                'error' => [
                    'errNamaSupplier' => $this->pesanRelasiMaster('supplier', $pemakaian) . '<br>Nama supplier tidak boleh diubah, tapi email/PIC/telp/alamat masih bisa diperbarui.',
                ],
            ]);
        }

        $validation = \Config\Services::validation();

        $valid = $this->validate([
            'editNamaSupplier' => [
                'rules' => 'required|is_unique[supplier.supnama,supid,' . $idSupplier . ']',
                'label' => 'Nama Supplier',
                'errors' => [
                    'required' => '{field} tidak boleh kosong',
                    'is_unique' => '{field} sudah digunakan Supplier lain'
                ]
            ],
            'editNamaPic' => [
                'rules' => 'required',
                'label' => 'Nama PIC',
                'errors' => [
                    'required' => '{field} tidak boleh kosong'
                ]
            ],
            'editEmail' => [
                'rules' => 'permit_empty|valid_email|is_unique[supplier.supemail,supid,' . $idSupplier . ']',
                'label' => 'Email',
                'errors' => [
                    'valid_email' => '{field} tidak valid',
                    'is_unique' => '{field} sudah digunakan Supplier lain'
                ]
            ],
            'editTelp' => [
                'rules' => 'required|numeric|is_unique[supplier.suptelp,supid,' . $idSupplier . ']',
                'label' => 'No Telp / Handphone',
                'errors' => [
                    'required' => '{field} tidak boleh kosong',
                    'numeric' => '{field} harus berisi angka',
                    'is_unique' => '{field} sudah digunakan Supplier lain'
                ]
            ],
            'editAlamat' => [
                'rules' => 'required',
                'label' => 'Alamat',
                'errors' => [
                    'required' => '{field} tidak boleh kosong'
                ]
            ]
        ]);

        if (!$valid) {
            $json = [
                'error' => [
                    'errNamaSupplier' => $validation->getError('editNamaSupplier'),
                    'errNamaPic' => $validation->getError('editNamaPic'),
                    'errEmail' => $validation->getError('editEmail'),
                    'errTelp' => $validation->getError('editTelp'),
                    'errAlamat' => $validation->getError('editAlamat'),
                ]
            ];
        } else {
            $modelSupplier->update($idSupplier, [
                'supnama' => $namasupplier,
                'suppic' => $namapic,
                'supemail' => $email !== '' ? $email : null,
                'suptelp' => $telp,
                'alamat' => $alamat
            ]);

            $json = [
                'sukses' => 'Data Supplier Berhasil di Update'
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
    }

    function hapus()
    {
        if ($this->request->isAJAX()) {
            $id = $this->request->getPost('id');

            $db = \Config\Database::connect();

            $pemakaian = $this->relasiSupplier((int) $id);

            if ($pemakaian) {
                $json = [
                    'error' => $this->pesanRelasiMaster('supplier', $pemakaian)
                ];
            } else {
                $modelSupplier = new ModelSupplier();
                $modelSupplier->delete($id);

                $json = [
                    'sukses' => 'Data Supplier Berhasil di Hapus'
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
        }
    }
}
