<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Modelkategori;
use \Hermawan\DataTables\DataTable;

class Kategori extends BaseController
{
    protected $kategori;

    public function __construct()
    {
        $this->kategori = new Modelkategori();
    }

    private function daftarPemakaian(int $kode): array
    {
        $db = \Config\Database::connect();
        $sumber = [
            [
                'table' => 'barang',
                'column' => 'brgkatid',
                'label' => 'Data Produk',
                'select' => 'brgkode, brgnama',
                'fields' => ['brgkode', 'brgnama'],
            ],
            [
                'table' => 'material',
                'column' => 'matkatid',
                'label' => 'Data Material',
                'select' => 'matkode, matnama',
                'fields' => ['matkode', 'matnama'],
            ],
            [
                'table' => 'stokmaterial',
                'column' => 'materialkatid',
                'label' => 'Stok Material',
                'select' => 'kodematerial, namamaterial',
                'fields' => ['kodematerial', 'namamaterial'],
            ],
            [
                'table' => 'temp_materialmasuk',
                'column' => 'detmatkatid',
                'label' => 'Draft Material Masuk',
            ],
            [
                'table' => 'temp_materialkeluar',
                'column' => 'detmatkatid',
                'label' => 'Draft Material Keluar',
            ],
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
        return view('kategori/viewdatakategori');
    }

    public function listData()
    {
        if ($this->request->isAJAX()) {
            $db = \Config\Database::connect();
            $builder = $db->table('kategori')->select('katid,katnama');

            return DataTable::of($builder)
                ->addNumbering('nomor')
                ->add('aksi', function ($row) {
                    $kode = htmlspecialchars((string) $row->katid, ENT_QUOTES, 'UTF-8');
                    $hash = htmlspecialchars(sha1($row->katid), ENT_QUOTES, 'UTF-8');
                    $nama = htmlspecialchars((string) $row->katnama, ENT_QUOTES, 'UTF-8');

                    return "<button type=\"button\" class=\"btn btn-sm btn-primary\" title=\"Edit Data\" data-kode=\"{$kode}\" data-hash=\"{$hash}\" data-nama=\"{$nama}\" onclick=\"editKategori(this)\"><i class=\"fa fa-edit\"></i></button>&nbsp;
                    <button type=\"button\" class=\"btn btn-sm btn-danger\" title=\"Hapus Data\" data-kode=\"{$kode}\" data-nama=\"{$nama}\" onclick=\"hapusKategori(this)\"><i class=\"fa fa-trash-alt\"></i></button>";
                })
                ->toJson(true);
        }
    }

    public function pemakaian()
    {
        $kode = (int) $this->request->getGet('kode');
        $kategori = $this->kategori->find($kode);

        if (!$kategori) {
            return $this->response->setStatusCode(404)->setJSON([
                'error' => 'Data kategori tidak ditemukan',
            ]);
        }

        $pemakaian = $this->daftarPemakaian($kode);

        return $this->response->setJSON([
            'nama' => $kategori['katnama'],
            'digunakan' => !empty($pemakaian),
            'pemakaian' => $pemakaian,
        ]);
    }

    public function formtambah()
    {
        return view('kategori/formtambah');
    }

    public function simpandata()
    {
        $namakategori = $this->request->getVar('namakategori');

        $validation = \Config\Services::validation();

        $valid = $this->validate([
            'namakategori' => [
                'rules' => 'required|is_unique[kategori.katnama]',
                'label' => 'Nama Kategori',
                'errors' => [
                    'required' => '{field} tidak boleh kosong',
                    'is_unique' => '{field} sudah terpakai'
                ]
            ]
        ]);

        if (!$valid) {
            $pesan = [
                'errorNamaKategori' => '<br><div class="alert alert-danger">
                ' . $validation->listErrors() . '</div>'
            ];

            session()->setFlashdata($pesan);
            return redirect()->to('/kategori/formtambah');
        } else {
            $this->kategori->insert([
                'katnama' => $namakategori
            ]);

            $pesan = [
                'sukses' => '<div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-check"></i> Behasil !</h5>
                Data Kategori Berhasil di Tambahkan
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
            return redirect()->to('/kategori/index');
        }
    }

    public function formedit($id)
    {
        $modelKategori = new Modelkategori();
        $cekId = $modelKategori->cekId($id);

        if ($cekId->getNumRows() > 0) {
            $row = $cekId->getRowArray();
            $data = [
                'id' => $id,
                'nama' => $row['katnama']
            ];
            return view('kategori/formedit', $data);
        } else {
            exit('Data tidak ditemukan');
        }
    }

    public function updatedata()
    {
        $idkategori = $this->request->getVar('idkategori');
        $namakategori = $this->request->getVar('namakategori');
        $modelKategori = new Modelkategori();
        $cekId = $modelKategori->cekId($idkategori);

        if ($cekId->getNumRows() === 0) {
            return redirect()->to('/kategori/index')->with('error', 'Data kategori tidak ditemukan.');
        }

        $idAsli = $cekId->getRowArray()['katid'];

        $validation = \Config\Services::validation();

        $valid = $this->validate([
            'namakategori' => [
                'rules' => 'required|is_unique[kategori.katnama,katid,' . $idAsli . ']',
                'label' => 'Nama Kategori',
                'errors' => [
                    'required' => '{field} tidak boleh kosong',
                    'is_unique' => '{field} sudah terpakai kategori lain'
                ]
            ]
        ]);

        if (!$valid) {
            $pesan = [
                'errorNamaKategori' => '<br><div class="alert alert-danger">' . $validation->getError('namakategori') . '</div>'
            ];

            session()->setFlashdata($pesan);
            return redirect()->to('/kategori/formedit/' . $idkategori);
        } else {
            $modelKategori = new Modelkategori();
            $cekId = $modelKategori->cekId($idkategori);
            if ($cekId->getNumRows() > 0) {
                $row = $cekId->getRowArray();
                $idAsli = $row['katid'];

                $kategoriData = [
                    'katnama' => $namakategori
                ];

                $update = $modelKategori->update($idAsli, $kategoriData);

                if ($update) {
                    $pesan = [
                        'sukses' => '<div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-check"></i> Berhasil!</h5>
                    Data Kategori Berhasil di Edit
                  </div>'
                    ];
                    session()->setFlashdata($pesan);
                } else {
                    $pesan = [
                        'error' => '<div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-ban"></i> Gagal!</h5>
                    Data Kategori Gagal di Edit
                  </div>'
                    ];
                    session()->setFlashdata($pesan);
                }
            } else {
                $pesan = [
                    'error' => '<div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-ban"></i> Gagal!</h5>
                Data Kategori Tidak Ditemukan
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

            return redirect()->to('/kategori/index');
        }
    }

    public function hapus()
    {
        if ($this->request->isAJAX()) {
            $kode = (int) $this->request->getPost('kode');
            $kategori = $this->kategori->find($kode);

            if (!$kategori) {
                return $this->response->setJSON([
                    'error' => 'Data kategori tidak ditemukan',
                    'pemakaian' => [],
                ]);
            }

            $nama = $kategori['katnama'];
            $namaHtml = htmlspecialchars($nama, ENT_QUOTES, 'UTF-8');

            $pemakaian = $this->daftarPemakaian($kode);

            if ($pemakaian) {
                $json = [
                    'error' => "Data Kategori <b>{$namaHtml}</b> tidak bisa dihapus karena masih digunakan",
                    'pemakaian' => $pemakaian,
                ];
            } else {
                $modelKategori = new Modelkategori();
                $modelKategori->delete($kode);

                $json = [
                    'sukses' => "Data Kategori <b>{$namaHtml}</b> Berhasil di Hapus"
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
