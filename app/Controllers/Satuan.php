<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Modelsatuan;
use \Hermawan\DataTables\DataTable;

class Satuan extends BaseController
{
    protected $satuan;

    public function __construct()
    {
        $this->satuan = new Modelsatuan();
    }

    private function daftarPemakaian(int $kode): array
    {
        $db = \Config\Database::connect();
        $sumber = [
            ['table' => 'barang', 'column' => 'brgsatid', 'label' => 'Data Produk (Satuan)', 'select' => 'brgkode, brgnama', 'fields' => ['brgkode', 'brgnama']],
            ['table' => 'barang', 'column' => 'satuanberat', 'label' => 'Data Produk (Satuan Berat)', 'select' => 'brgkode, brgnama', 'fields' => ['brgkode', 'brgnama']],
            ['table' => 'material', 'column' => 'matsatid', 'label' => 'Data Material', 'select' => 'matkode, matnama', 'fields' => ['matkode', 'matnama']],
            ['table' => 'berat', 'column' => 'satuan', 'label' => 'Berat/Ukuran Bersih', 'select' => 'kodeprd, kodemat', 'fields' => ['kodeprd', 'kodemat']],
            ['table' => 'stok', 'column' => 'satuan', 'label' => 'Stok Produk (Satuan)', 'select' => 'kodebarang, namabarang', 'fields' => ['kodebarang', 'namabarang']],
            ['table' => 'stok', 'column' => 'satuanberat', 'label' => 'Stok Produk (Satuan Berat)', 'select' => 'kodebarang, namabarang', 'fields' => ['kodebarang', 'namabarang']],
            ['table' => 'stokmaterial', 'column' => 'materialsatid', 'label' => 'Stok Material', 'select' => 'kodematerial, namamaterial', 'fields' => ['kodematerial', 'namamaterial']],
            ['table' => 'barangmasuk', 'column' => 'satuan', 'label' => 'Data Produk Masuk', 'select' => 'faktur', 'fields' => ['faktur']],
            ['table' => 'barangkeluar', 'column' => 'satuan', 'label' => 'Data Produk Keluar', 'select' => 'faktur', 'fields' => ['faktur']],
            ['table' => 'materialmasuk', 'column' => 'satuan', 'label' => 'Data Material Masuk', 'select' => 'faktur', 'fields' => ['faktur']],
            ['table' => 'materialkeluar', 'column' => 'satuan', 'label' => 'Data Material Keluar', 'select' => 'faktur', 'fields' => ['faktur']],
            ['table' => 'permintaanbarangkirim', 'column' => 'satuan', 'label' => 'Data Pengiriman Transfer', 'select' => 'faktur', 'fields' => ['faktur']],
            ['table' => 'ngdata', 'column' => 'satuan', 'label' => 'Data Raw Produk'],
            ['table' => 'temp_materialmasuk', 'column' => 'detmatsatid', 'label' => 'Draft Material Masuk'],
            ['table' => 'temp_materialkeluar', 'column' => 'detmatsatid', 'label' => 'Draft Material Keluar'],
        ];

        $pemakaian = [];
        foreach ($sumber as $item) {
            if (
                !$db->tableExists($item['table']) ||
                !$db->fieldExists($item['column'], $item['table'])
            ) {
                continue;
            }

            $jumlah = $db->table($item['table'])
                ->where($item['column'], $kode)
                ->countAllResults();

            if ($jumlah === 0) {
                continue;
            }

            $contoh = [];
            if (isset($item['select'], $item['fields'])) {
                $fieldsTersedia = true;
                foreach ($item['fields'] as $field) {
                    if (!$db->fieldExists($field, $item['table'])) {
                        $fieldsTersedia = false;
                        break;
                    }
                }

                if ($fieldsTersedia) {
                    $rows = $db->table($item['table'])
                        ->select($item['select'])
                        ->where($item['column'], $kode)
                        ->limit(5)
                        ->get()
                        ->getResultArray();

                    foreach ($rows as $row) {
                        $bagian = [];
                        foreach ($item['fields'] as $field) {
                            if (!empty($row[$field])) {
                                $bagian[] = $row[$field];
                            }
                        }
                        if ($bagian) {
                            $contoh[] = implode(' - ', $bagian);
                        }
                    }
                    $contoh = array_values(array_unique($contoh));
                }
            }

            $pemakaian[] = [
                'label' => $item['label'],
                'jumlah' => $jumlah,
                'contoh' => $contoh,
            ];
        }

        return $pemakaian;
    }

    public function index()
    {
        return view('satuan/viewdatasatuan');
    }

    public function listData()
    {
        if ($this->request->isAJAX()) {
            $db = \Config\Database::connect();
            $builder = $db->table('satuan')->select('satid,satnama');

            return DataTable::of($builder)
                ->addNumbering('nomor')
                ->add('aksi', function ($row) {
                    $kode = htmlspecialchars((string) $row->satid, ENT_QUOTES, 'UTF-8');
                    $hash = htmlspecialchars(sha1($row->satid), ENT_QUOTES, 'UTF-8');
                    $nama = htmlspecialchars((string) $row->satnama, ENT_QUOTES, 'UTF-8');

                    return "<button type=\"button\" class=\"btn btn-sm btn-primary\" title=\"Edit Data\" data-kode=\"{$kode}\" data-hash=\"{$hash}\" data-nama=\"{$nama}\" onclick=\"editSatuan(this)\"><i class=\"fa fa-edit\"></i></button>&nbsp;
                    <button type=\"button\" class=\"btn btn-sm btn-danger\" title=\"Hapus Data\" data-kode=\"{$kode}\" data-nama=\"{$nama}\" onclick=\"hapusSatuan(this)\"><i class=\"fa fa-trash-alt\"></i></button>";
                })
                ->toJson(true);
        }
    }

    public function pemakaian()
    {
        $kode = (int) $this->request->getGet('kode');
        $satuan = $this->satuan->find($kode);

        if (!$satuan) {
            return $this->response->setStatusCode(404)->setJSON([
                'error' => 'Data satuan tidak ditemukan',
            ]);
        }

        $pemakaian = $this->daftarPemakaian($kode);

        return $this->response->setJSON([
            'nama' => $satuan['satnama'],
            'digunakan' => !empty($pemakaian),
            'pemakaian' => $pemakaian,
        ]);
    }

    public function formtambah()
    {
        return view('satuan/formtambah');
    }

    public function simpandata()
    {
        $namasatuan = $this->request->getVar('namasatuan');

        $validation = \Config\Services::validation();

        $valid = $this->validate([
            'namasatuan' => [
                'rules' => 'required|is_unique[satuan.satnama]',
                'label' => 'Nama Satuan',
                'errors' => [
                    'required' => '{field} tidak boleh kosong',
                    'is_unique' => '{field} sudah terpakai'
                ]
            ]
        ]);

        if (!$valid) {
            $pesan = [
                'errorNamaSatuan' => '<br><div class="alert alert-danger">' . $validation->listErrors() . '</div>'
            ];

            session()->setFlashdata($pesan);
            return redirect()->to('/satuan/formtambah');
        } else {
            $this->satuan->insert([
                'satnama' => $namasatuan
            ]);

            $pesan = [
                'sukses' => '<div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-check"></i> Behasil !</h5>
                Data Satuan Berhasil di Tambahkan
              </div>'
            ];

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

            session()->setFlashdata($pesan);
            return redirect()->to('/satuan/index');
        }
    }

    public function formedit($id)
    {
        $modelSatuan = new Modelsatuan();
        $cekId = $modelSatuan->cekId($id);

        if ($cekId->getNumRows() > 0) {
            $row = $cekId->getRowArray();
            $data = [
                'id' => $id,
                'nama' => $row['satnama']
            ];
            return view('satuan/formedit', $data);
        } else {
            exit('Data tidak ditemukan');
        }
    }

    public function updatedata()
    {
        $idsatuan = $this->request->getVar('idsatuan');
        $namasatuan = $this->request->getVar('namasatuan');
        $modelSatuan = new Modelsatuan();
        $cekId = $modelSatuan->cekId($idsatuan);

        if ($cekId->getNumRows() === 0) {
            return redirect()->to('/satuan/index')->with('error', 'Data satuan tidak ditemukan.');
        }

        $idAsli = $cekId->getRowArray()['satid'];

        $validation = \Config\Services::validation();

        $valid = $this->validate([
            'namasatuan' => [
                'rules' => 'required|is_unique[satuan.satnama,satid,' . $idAsli . ']',
                'label' => 'Nama satuan',
                'errors' => [
                    'required' => '{field} tidak boleh kosong',
                    'is_unique' => '{field} sudah terpakai satuan lain'
                ]
            ]
        ]);

        if (!$valid) {
            $pesan = [
                'errorNamaSatuan' => '<br><div class="alert alert-danger">' . $validation->getError('namasatuan') . '</div>'
            ];

            session()->setFlashdata($pesan);
            return redirect()->to('/satuan/formedit/' . $idsatuan);
        } else {
            $modelSatuan = new Modelsatuan();

            $cekId = $modelSatuan->cekId($idsatuan);
            if ($cekId->getNumRows() > 0) {
                $row = $cekId->getRowArray();
                $idAsli = $row['satid'];

                $satuanData = [
                    'satnama' => $namasatuan
                ];

                $update = $modelSatuan->update($idAsli, $satuanData);

                if ($update) {
                    $pesan = [
                        'sukses' => '<div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-check"></i> Berhasil!</h5>
                    Data Satuan Berhasil di Edit
                  </div>'
                    ];
                    session()->setFlashdata($pesan);
                } else {
                    $pesan = [
                        'error' => '<div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-ban"></i> Gagal!</h5>
                    Data Satuan Gagal di Edit
                  </div>'
                    ];
                    session()->setFlashdata($pesan);
                }
            } else {
                $pesan = [
                    'error' => '<div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-ban"></i> Gagal!</h5>
                Data Satuan Tidak Ditemukan
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

            return redirect()->to('/satuan/index');
        }
    }

    public function hapus()
    {
        if ($this->request->isAJAX()) {
            $kode = (int) $this->request->getPost('kode');
            $satuan = $this->satuan->find($kode);

            if (!$satuan) {
                return $this->response->setJSON([
                    'error' => 'Data satuan tidak ditemukan',
                    'pemakaian' => [],
                ]);
            }

            $namaHtml = htmlspecialchars($satuan['satnama'], ENT_QUOTES, 'UTF-8');
            $pemakaian = $this->daftarPemakaian($kode);

            if ($pemakaian) {
                $json = [
                    'error' => "Data Satuan <b>{$namaHtml}</b> tidak bisa dihapus karena masih digunakan",
                    'pemakaian' => $pemakaian,
                ];
            } else {
                $modelSatuan = new ModelSatuan();
                $modelSatuan->delete($kode);

                $json = [
                    'sukses' => "Data Satuan <b>{$namaHtml}</b> berhasil dihapus"
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
