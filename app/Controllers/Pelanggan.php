<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ModelPelanggan;
use \Hermawan\DataTables\DataTable;

class Pelanggan extends BaseController
{
    protected $db;
    protected $modelPelanggan;

    public function __construct()
    {
        $this->db = db_connect();
        $this->modelPelanggan = new ModelPelanggan();
    }

    private function relasiPelanggan(int $id): array
    {
        return $this->periksaRelasiMaster([
            ['table' => 'barang', 'column' => 'idpel', 'label' => 'Produk'],
            ['table' => 'po', 'column' => 'idpel', 'label' => 'PO'],
            ['table' => 'detail_po', 'column' => 'detidpel', 'label' => 'Detail PO'],
            ['table' => 'outstanding', 'column' => 'idpel', 'label' => 'Outstanding PO'],
            ['table' => 'barangkeluar', 'column' => 'idpel', 'label' => 'Produk Keluar'],
            ['table' => 'detail_barangkeluar', 'column' => 'detidpel', 'label' => 'Detail Produk Keluar'],
            ['table' => 'permintaan_pengiriman', 'column' => 'idpel', 'label' => 'Permintaan Pengiriman'],
            ['table' => 'detail_permintaan_pengiriman', 'column' => 'idpel', 'label' => 'Detail Permintaan Pengiriman'],
            ['table' => 'temp_po', 'column' => 'detidpel', 'label' => 'Draft PO'],
            ['table' => 'temp_barangkeluar', 'column' => 'detidpel', 'label' => 'Draft Produk Keluar'],
        ], $id);
    }

    public function pemakaian()
    {
        $id = (int) $this->request->getGet('id');
        return $this->response->setJSON(['pemakaian' => $this->relasiPelanggan($id)]);
    }

    private function daftarGudang(): array
    {
        return $this->db->table('gudang')
            ->select('gdgid, gdgnama')
            ->orderBy('gdgnama', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function index()
    {
        return view('pelanggan/viewdata', [
            'gudang' => $this->daftarGudang()
        ]);
    }

    public function formtambah()
    {
        $json = [
            'data' => view('pelanggan/modaltambah', [
                'gudang' => $this->daftarGudang()
            ])
        ];

        return $this->response->setJSON($json);
    }

    public function listData()
    {
        if ($this->request->isAJAX()) {
            $db = \Config\Database::connect();
            $builder = $db->table('pelanggan p')
                ->select("p.pelid, p.pelnama, COALESCE(p.pelpic, '-') AS pelpic, COALESCE(p.pelemail, '-') AS pelemail, COALESCE(p.pelalamat, '-') AS pelalamat, p.peltelp, COALESCE(p.pelfax, '-') AS pelfax, COALESCE(p.pelto, '-') AS pelto, p.gdgid, COALESCE(g.gdgnama, 'Belum dipilih') AS gdgnama", false)
                ->join('gudang g', 'g.gdgid = p.gdgid', 'left')
                ->whereNotIn('p.pelid', [1, 2]);

            return DataTable::of($builder)
                ->addNumbering('nomor')
                ->add('aksi', function ($row) {
                    $args = array_map(static fn ($value) => json_encode((string) $value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT), [
                        $row->pelid,
                        $row->pelnama,
                        $row->pelpic,
                        $row->pelemail,
                        $row->pelalamat,
                        $row->peltelp,
                        $row->gdgid,
                        $row->pelfax,
                        $row->pelto,
                    ]);

                    return "<button type=\"button\" class=\"btn btn-sm btn-info\" title=\"Pilih Data\" onclick=\"pilih('" . $row->pelid . "','" . $row->pelnama . "')\"><i class=\"fa fa-check\"></i></button>&nbsp
                    <button type=\"button\" class=\"btn btn-sm btn-primary\" title=\"Edit Data\" id=\"tombolEditPelanggan\" onclick='editData(" . implode(',', $args) . ")'><i class=\"fa fa-edit\"></i></button>&nbsp
                    <button type=\"button\" class=\"btn btn-sm btn-danger\" title=\"Hapus Data\" onclick=\"hapus('" . $row->pelid . "','" . $row->pelnama . "')\"><i class=\"fa fa-trash-alt\"></i></button>";
                })
                ->toJson(true);
        }
    }

    public function listDataView()
    {
        if ($this->request->isAJAX()) {
            $db = \Config\Database::connect();
            $builder = $db->table('pelanggan p')
                ->select("p.pelid, p.pelnama, COALESCE(p.pelpic, '-') AS pelpic, COALESCE(p.pelemail, '-') AS pelemail, COALESCE(p.pelalamat, '-') AS pelalamat, p.peltelp, COALESCE(p.pelfax, '-') AS pelfax, COALESCE(p.pelto, '-') AS pelto, p.gdgid, COALESCE(g.gdgnama, 'Belum dipilih') AS gdgnama", false)
                ->join('gudang g', 'g.gdgid = p.gdgid', 'left')
                ->whereNotIn('p.pelid', [1, 2]);

            return DataTable::of($builder)
                ->addNumbering('nomor')
                ->add('aksi', function ($row) {
                    $args = array_map(static fn ($value) => json_encode((string) $value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT), [
                        $row->pelid,
                        $row->pelnama,
                        $row->pelpic,
                        $row->pelemail,
                        $row->pelalamat,
                        $row->peltelp,
                        $row->gdgid,
                        $row->pelfax,
                        $row->pelto,
                    ]);

                    return "<button type=\"button\" class=\"btn btn-sm btn-primary\" title=\"Edit Data\" id=\"tombolEditPelanggan\" onclick='editData(" . implode(',', $args) . ")'><i class=\"fa fa-edit\"></i></button>&nbsp
                    <button type=\"button\" class=\"btn btn-sm btn-danger\" title=\"Hapus Data\" onclick=\"hapus('" . $row->pelid . "','" . $row->pelnama . "')\"><i class=\"fa fa-trash-alt\"></i></button>";
                })
                ->toJson(true);
        }
    }

    public function simpan()
    {
        $namapelanggan = $this->request->getPost('namapel');
        $namapic = $this->request->getPost('namapic');
        $email = $this->request->getPost('email');
        $alamat = $this->request->getPost('alamat');
        $telp = $this->request->getPost('telp');
        $fax = trim((string) $this->request->getPost('fax'));
        $to = trim((string) $this->request->getPost('to'));
        $gdgid = $this->request->getPost('gdgid');

        $validation = \Config\Services::validation();

        $valid = $this->validate([
            'namapel' => [
                'rules' => 'required|is_unique[pelanggan.pelnama]',
                'label' => 'Nama Pelanggan',
                'errors' => [
                    'required' => '{field} tidak boleh kosong',
                    'is_unique' => '{field} sudah digunakan Pelanggan lain'
                ]
            ],
            'telp' => [
                'rules' => 'required|numeric|is_unique[pelanggan.peltelp]',
                'label' => 'No Telp / Handphone',
                'errors' => [
                    'required' => '{field} tidak boleh kosong',
                    'numeric' => '{field} harus berisi angka',
                    'is_unique' => '{field} sudah digunakan Pelanggan lain'
                ]
            ],
            'namapic' => [
                'rules' => 'required|max_length[100]',
                'label' => 'Nama PIC',
                'errors' => [
                    'required' => '{field} tidak boleh kosong',
                    'max_length' => '{field} maksimal 100 karakter'
                ]
            ],
            'email' => [
                'rules' => 'required|valid_email|max_length[150]|is_unique[pelanggan.pelemail]',
                'label' => 'Email',
                'errors' => [
                    'required' => '{field} tidak boleh kosong',
                    'valid_email' => 'Format {field} tidak valid',
                    'max_length' => '{field} maksimal 150 karakter',
                    'is_unique' => '{field} sudah digunakan pelanggan lain'
                ]
            ],
            'alamat' => [
                'rules' => 'required|max_length[500]',
                'label' => 'Alamat',
                'errors' => [
                    'required' => '{field} tidak boleh kosong',
                    'max_length' => '{field} maksimal 500 karakter'
                ]
            ],
            'fax' => [
                'rules' => 'permit_empty|max_length[100]',
                'label' => 'Fax',
                'errors' => [
                    'max_length' => '{field} maksimal 100 karakter'
                ]
            ],
            'to' => [
                'rules' => 'permit_empty|max_length[100]',
                'label' => 'To (Bagian/PIC Penerima)',
                'errors' => [
                    'max_length' => '{field} maksimal 100 karakter'
                ]
            ],
            'gdgid' => [
                'rules' => 'required|integer|is_not_unique[gudang.gdgid]',
                'label' => 'Gudang',
                'errors' => [
                    'required' => '{field} wajib dipilih',
                    'integer' => '{field} tidak valid',
                    'is_not_unique' => '{field} tidak ditemukan'
                ]
            ]
        ]);

        if (!$valid) {
            $json = [
                'error' => [
                    'errNamaPelanggan' => $validation->getError('namapel'),
                    'errNamaPic' => $validation->getError('namapic'),
                    'errEmail' => $validation->getError('email'),
                    'errAlamat' => $validation->getError('alamat'),
                    'errTelp' => $validation->getError('telp'),
                    'errFax' => $validation->getError('fax'),
                    'errTo' => $validation->getError('to'),
                    'errGudang' => $validation->getError('gdgid'),
                ]
            ];
        } else {
            $modelPelanggan = new ModelPelanggan();

            $modelPelanggan->insert([
                'pelnama' => $namapelanggan,
                'pelpic' => $namapic,
                'pelemail' => $email,
                'pelalamat' => $alamat,
                'peltelp' => $telp,
                'pelfax' => $fax !== '' ? $fax : null,
                'pelto' => $to !== '' ? $to : null,
                "gdgid" => $gdgid
            ]);

            $json = [
                'sukses' => 'Data Pelanggan Berhasil di Simpan',
                'pelanggan' => [
                    'id' => (string) $modelPelanggan->getInsertID(),
                    'text' => (string) $namapelanggan,
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
                'data' => view('pelanggan/modaldata', [
                    'gudang' => $this->daftarGudang()
                ])
            ];

            return $this->response->setJSON($json);
        }
    }

    public function update()
    {
        $idPelanggan = $this->request->getPost('id_pelanggan');
        $namapelanggan = $this->request->getPost('editNamaPelanggan');
        $namapic = $this->request->getPost('editNamaPic');
        $email = $this->request->getPost('editEmail');
        $alamat = $this->request->getPost('editAlamat');
        $telp = $this->request->getPost('editTelp');
        $fax = trim((string) $this->request->getPost('editFax'));
        $to = trim((string) $this->request->getPost('editTo'));
        $gdgid = $this->request->getPost('editGudang');

        // pelid adalah key yang dipakai di semua relasi, jadi aman diedit
        // kapan saja tanpa perlu ngecek pemakaian dulu (beda dengan hapus()
        // yang tetap perlu dicek karena datanya beneran hilang).
        $validation = \Config\Services::validation();

        $valid = $this->validate([
            'editNamaPelanggan' => [
                'rules' => 'required|is_unique[pelanggan.pelnama,pelid,' . $idPelanggan . ']',
                'label' => 'Nama Pelanggan',
                'errors' => [
                    'required' => '{field} tidak boleh kosong',
                    'is_unique' => '{field} sudah digunakan oleh pelanggan lain'
                ]
            ],
            'editTelp' => [
                'rules' => 'required|numeric|is_unique[pelanggan.peltelp,pelid,' . $idPelanggan . ']',
                'label' => 'No Telp / Handphone',
                'errors' => [
                    'required' => '{field} tidak boleh kosong',
                    'numeric' => '{field} harus berisi angka',
                    'is_unique' => '{field} sudah digunakan oleh pelanggan lain'
                ]
            ],
            'editNamaPic' => [
                'rules' => 'required|max_length[100]',
                'label' => 'Nama PIC',
                'errors' => [
                    'required' => '{field} tidak boleh kosong',
                    'max_length' => '{field} maksimal 100 karakter'
                ]
            ],
            'editEmail' => [
                'rules' => 'required|valid_email|max_length[150]|is_unique[pelanggan.pelemail,pelid,' . $idPelanggan . ']',
                'label' => 'Email',
                'errors' => [
                    'required' => '{field} tidak boleh kosong',
                    'valid_email' => 'Format {field} tidak valid',
                    'max_length' => '{field} maksimal 150 karakter',
                    'is_unique' => '{field} sudah digunakan pelanggan lain'
                ]
            ],
            'editAlamat' => [
                'rules' => 'required|max_length[500]',
                'label' => 'Alamat',
                'errors' => [
                    'required' => '{field} tidak boleh kosong',
                    'max_length' => '{field} maksimal 500 karakter'
                ]
            ],
            'editFax' => [
                'rules' => 'permit_empty|max_length[100]',
                'label' => 'Fax',
                'errors' => [
                    'max_length' => '{field} maksimal 100 karakter'
                ]
            ],
            'editTo' => [
                'rules' => 'permit_empty|max_length[100]',
                'label' => 'To (Bagian/PIC Penerima)',
                'errors' => [
                    'max_length' => '{field} maksimal 100 karakter'
                ]
            ],
            'editGudang' => [
                'rules' => 'required|integer|is_not_unique[gudang.gdgid]',
                'label' => 'Gudang',
                'errors' => [
                    'required' => '{field} wajib dipilih',
                    'integer' => '{field} tidak valid',
                    'is_not_unique' => '{field} tidak ditemukan'
                ]
            ],
        ]);

        if (!$valid) {
            $json = [
                'error' => [
                    'errNamaPelanggan' => $validation->getError('editNamaPelanggan'),
                    'errNamaPic' => $validation->getError('editNamaPic'),
                    'errEmail' => $validation->getError('editEmail'),
                    'errAlamat' => $validation->getError('editAlamat'),
                    'errTelp' => $validation->getError('editTelp'),
                    'errFax' => $validation->getError('editFax'),
                    'errTo' => $validation->getError('editTo'),
                    'errGudang' => $validation->getError('editGudang'),
                ]
            ];
        } else {
            $modelPelanggan = new ModelPelanggan();

            $modelPelanggan->update($idPelanggan, [
                'pelnama' => $namapelanggan,
                'pelpic'  => $namapic,
                'pelemail' => $email,
                'pelalamat' => $alamat,
                'peltelp' => $telp,
                'pelfax' => $fax !== '' ? $fax : null,
                'pelto' => $to !== '' ? $to : null,
                'gdgid'   => $gdgid,
            ]);

            $json = [
                'sukses' => 'Data Pelanggan Berhasil di Update'
            ];
        }

        echo json_encode($json);
    }

    public function hapus()
    {
        if ($this->request->isAJAX()) {
            $id = $this->request->getPost('id');

            $db = \Config\Database::connect();

            $pemakaian = $this->relasiPelanggan((int) $id);

            if ($pemakaian) {
                $json = [
                    'error' => $this->pesanRelasiMaster('pelanggan', $pemakaian)
                ];
            } else {
                $modelPelanggan = new ModelPelanggan();
                $modelPelanggan->delete($id);

                $json = [
                    'sukses' => 'Data Pelanggan Berhasil di Hapus'
                ];
            }

            echo json_encode($json);
        }
    }
}
