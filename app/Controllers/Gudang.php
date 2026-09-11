<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Modelgudang;
use \Hermawan\DataTables\DataTable;

class Gudang extends BaseController
{
    protected $gudang;

    public function __construct()
    {
        $this->gudang = new Modelgudang();
    }

    public function index()
    {
        return view('gudang/viewdatagudang');
    }

    public function listData()
    {
        if ($this->request->isAJAX()) {
            $db = \Config\Database::connect();
            $builder = $db->table('gudang')
                ->select('gdgid,gdgnama');

            return DataTable::of($builder)
                ->addNumbering('nomor')
                ->add('aksi', function ($row) {
                    return "<button type=\"button\" class=\"btn btn-sm btn-primary\" title=\"Edit Data\" onclick=\"edit('" . sha1($row->gdgid) . "')\"><i class=\"fa fa-edit\"></i></button>&nbsp
                    <button type=\"button\" class=\"btn btn-sm btn-danger\" title=\"Hapus Data\" onclick=\"hapus('" . $row->gdgid . "','" . $row->gdgnama . "')\"><i class=\"fa fa-trash-alt\"></i></button>";
                })
                ->toJson(true);
        }
    }

    public function formtambah()
    {
        return view('gudang/formtambah');
    }

    public function simpandata()
    {
        $namagudang = $this->request->getVar('namagudang');

        $validation = \Config\Services::validation();

        $valid = $this->validate([
            'namagudang' => [
                'rules' => 'required|is_unique[gudang.gdgnama]',
                'label' => 'Nama Gudang',
                'errors' => [
                    'required' => '{field} tidak boleh kosong',
                    'is_unique' => '{field} sudah terpakai'
                ]
            ]
        ]);

        if (!$valid) {
            $pesan = [
                'errorNamaGudang' => '<br><div class="alert alert-danger">' . $validation->listErrors() . '</div>'
            ];

            session()->setFlashdata($pesan);
            return redirect()->to('/gudang/formtambah');
        } else {
            $this->gudang->insert([
                'gdgnama' => $namagudang
            ]);

            $pesan = [
                'sukses' => '<div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-check"></i> Behasil !</h5>
                Data Gudang Berhasil di Tambahkan
              </div>'
            ];

            session()->setFlashdata($pesan);

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

            return redirect()->to('/gudang/index');
        }
    }

    public function formedit($id)
    {
        $modelGudang = new Modelgudang();
        $cekId = $modelGudang->cekId($id);

        if ($cekId->getNumRows() > 0) {
            $row = $cekId->getRowArray();
            $data = [
                'id' => $id,
                'nama' => $row['gdgnama']
            ];
            return view('gudang/formedit', $data);
        } else {
            exit('Data tidak ditemukan');
        }
    }

    public function updatedata()
    {
        $idgudang = $this->request->getVar('idgudang');
        $namagudang = $this->request->getVar('namagudang');
        $modelGudang = new Modelgudang();
        $cekId = $modelGudang->cekId($idgudang);

        if ($cekId->getNumRows() === 0) {
            return redirect()->to('/gudang/index')->with('error', 'Data gudang tidak ditemukan.');
        }

        $idAsli = $cekId->getRowArray()['gdgid'];

        $validation = \Config\Services::validation();

        $valid = $this->validate([
            'namagudang' => [
                'rules' => 'required|is_unique[gudang.gdgnama,gdgid,' . $idAsli . ']',
                'label' => 'Nama Gudang',
                'errors' => [
                    'required' => '{field} tidak boleh kosong',
                    'is_unique' => '{field} sudah terpakai gudang lain'
                ]
            ]
        ]);

        if (!$valid) {
            $pesan = [
                'errorNamaGudang' => '<br><div class="alert alert-danger">' . $validation->getError('namagudang') . '</div>'
            ];

            session()->setFlashdata($pesan);
            return redirect()->to('/gudang/formedit/' . $idgudang);
        } else {
            $modelGudang = new Modelgudang();

            $cekId = $modelGudang->cekId($idgudang);
            if ($cekId->getNumRows() > 0) {
                $row = $cekId->getRowArray();
                $idAsli = $row['gdgid'];

                $gudangData = [
                    'gdgnama' => $namagudang
                ];

                $update = $modelGudang->update($idAsli, $gudangData);

                if ($update) {
                    $pesan = [
                        'sukses' => '<div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-check"></i> Berhasil!</h5>
                    Data Gudang Berhasil di Edit
                  </div>'
                    ];
                    session()->setFlashdata($pesan);
                } else {
                    $pesan = [
                        'error' => '<div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-ban"></i> Gagal!</h5>
                    Data Gudang Gagal di Edit
                  </div>'
                    ];
                    session()->setFlashdata($pesan);
                }
            } else {
                $pesan = [
                    'error' => '<div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-ban"></i> Gagal!</h5>
                Data Gudang Tidak Ditemukan
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

            return redirect()->to('/gudang/index');
        }
    }

    public function hapus()
    {
        if ($this->request->isAJAX()) {
            $kode = $this->request->getPost('id');
            $nama = $this->request->getPost('nama');

            $db = \Config\Database::connect();

            $cekTabel = [
                'stok' => 'gudang',
                'stokmaterial' => 'gudang',
                'barangmasuk' => 'gudang',
                'materialmasuk' => 'gudang',
                'barangkeluar' => 'gudang',
                'materialkeluar' => 'gudang',
            ];

            $diGunakan = false;

            foreach ($cekTabel as $table => $column) {
                $count = $db->table($table)->where($column, $kode)->countAllResults();
                log_message('debug', "Cek $table untuk id: $kode, hasil: $count");
                if ($count > 0) {
                    $diGunakan = true;
                    break;
                }
            }

            if ($diGunakan) {
                $json = [
                    'error' => "Data Gudang <b>{$nama}</b> tidak bisa dihapus karena masih terkait dengan data di tabel lain"
                ];
            } else {
                $modelGudang = new Modelgudang();
                $modelGudang->delete($kode);

                $json = [
                    'sukses' => "Data Gudang <b>{$nama}</b> Berhasil di Hapus"
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
