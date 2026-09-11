<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Modelberat;
use App\Models\Modelbarang;
use App\Models\Modelmaterial;
use App\Models\Modelsatuan;
use \Hermawan\DataTables\DataTable;

class Berat extends BaseController
{
    protected $berat;

    public function __construct()
    {
        $this->berat = new Modelberat();
    }

    private function materialProduk(?string $kodeProduk = null): array
    {
        $builder = \Config\Database::connect()
            ->table('barang b')
            ->select('b.brgkode, m.matid, m.matkode, m.matnama')
            ->join('material m', 'FIND_IN_SET(m.matid, b.brgmat) > 0', 'inner', false)
            ->orderBy('m.matnama', 'ASC');

        if ($kodeProduk !== null) {
            $builder->where('b.brgkode', $kodeProduk);
        }

        $hasil = [];
        foreach ($builder->get()->getResultArray() as $row) {
            $hasil[$row['brgkode']][] = $row;
        }

        return $kodeProduk === null ? $hasil : ($hasil[$kodeProduk] ?? []);
    }

    private function normalisasiBeratMaterial(
        string $kodeProduk,
        $inputBerat
    ): array {
        $materials = $this->materialProduk($kodeProduk);
        if (!$materials) {
            throw new \InvalidArgumentException(
                'Produk belum memiliki relasi material.'
            );
        }

        if (!is_array($inputBerat)) {
            throw new \InvalidArgumentException(
                'Berat setiap material wajib diisi.'
            );
        }

        $detail = [];
        $total = 0.0;
        foreach ($materials as $material) {
            $matid = (int) $material['matid'];
            $nilai = $inputBerat[$matid] ?? null;

            if ($nilai === null || $nilai === '' || !is_numeric($nilai)) {
                throw new \InvalidArgumentException(
                    "Berat material {$material['matnama']} wajib diisi dengan angka."
                );
            }

            $berat = (float) $nilai;
            if ($berat <= 0) {
                throw new \InvalidArgumentException(
                    "Berat material {$material['matnama']} harus lebih besar dari 0."
                );
            }

            $detail[] = [
                'kodeprd' => $kodeProduk,
                'matid' => $matid,
                'berat' => $berat,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            $total += $berat;
        }

        $barang = (new Modelbarang())->find($kodeProduk);
        $materialIds = array_column($detail, 'matid');
        $materialUtama = $this->materialUtama((string) ($barang['brgmat'] ?? ''));
        if (!in_array($materialUtama, $materialIds, true)) {
            $materialUtama = (int) $materialIds[0];
        }

        return [
            'detail' => $detail,
            'total' => $total,
            'materialUtama' => $materialUtama,
        ];
    }

    private function pesanError(string $pesan): array
    {
        return [
            'error' => '<div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h5><i class="icon fas fa-ban"></i> Error!</h5>'
                . esc($pesan)
                . '</div>',
        ];
    }

    private function materialUtama(string $brgmat): int
    {
        $ids = array_values(array_filter(array_map(
            'intval',
            explode(',', $brgmat)
        )));

        return (int) ($ids[0] ?? 0);
    }

    public function index()
    {
        return view('berat/viewdataberat');
    }

    public function listData()
    {
        if ($this->request->isAJAX()) {
            $db = \Config\Database::connect();

            $materialQuery = $db->table('barang bmat')
                ->select("
                    bmat.brgkode,
                    GROUP_CONCAT(
                        DISTINCT m.matnama
                        ORDER BY m.matnama
                        SEPARATOR ', '
                    ) AS matnama
                ", false)
                ->join('material m', 'FIND_IN_SET(m.matid, bmat.brgmat) > 0', 'left', false)
                ->groupBy('bmat.brgkode')
                ->getCompiledSelect();

            $builder = $db->table('berat')
                ->select("
                    berat.kodeprd,
                    barang.brgnama,
                    material_produk.matnama,
                    berat.berat,
                    satuan.satnama,
                    CONCAT(berat.berat, ' ', satuan.satnama) AS berat_satuan
                ", false)
                ->join('barang', 'barang.brgkode = berat.kodeprd')
                ->join('satuan', 'satuan.satid = berat.satuan')
                ->join(
                    "($materialQuery) AS material_produk",
                    'material_produk.brgkode = berat.kodeprd',
                    'left',
                    false
                );

            return DataTable::of($builder)
                ->setSearchableColumns([
                    'berat.kodeprd',
                    'barang.brgnama',
                    'material_produk.matnama'
                ])
                ->addNumbering('nomor')
                ->add('aksi', function ($row) {
                    return "
                        <button type=\"button\"
                            class=\"btn btn-sm btn-primary\"
                            title=\"Edit Data\"
                            onclick=\"edit('" . sha1($row->kodeprd) . "')\">
                            <i class=\"fa fa-edit\"></i>
                        </button>&nbsp;

                        <button type=\"button\"
                            class=\"btn btn-sm btn-danger\"
                            title=\"Hapus Data\"
                            onclick=\"hapus('" . $row->kodeprd . "')\">
                            <i class=\"fa fa-trash-alt\"></i>
                        </button>
                    ";
                })
                ->toJson(true);
        }
    }

    public function tambah()
    {
        $modelbarang = new Modelbarang();
        $modelsatuan = new Modelsatuan();
        $modelmaterial = new Modelmaterial();
        $modelberat = new Modelberat();

        $data = [
            'databarang'      => $modelbarang->findAll(),
            'datasatuan'      => $modelsatuan->findAll(),
            'datamaterial'    => $modelmaterial->findAll(),
            'existingBerat'   => $modelberat->getDataBeratExisting(),
            'materialProduk'  => $this->materialProduk(),
        ];

        return view('berat/formtambah', $data);
    }

    public function simpandata()
    {
        $kodeprd = $this->request->getVar('kodeprd');
        $satuan = $this->request->getVar('satuan');

        $validation = \Config\Services::validation();

        $valid = $this->validate([
            'kodeprd' => [
                'rules' => 'required|is_unique[berat.kodeprd]',
                'label' => 'Kode Barang',
                'errors' => [
                    'required' => '{field} tidak boleh kosong',
                    'is_unique' => '{field} sudah terpakai'
                ]
            ],
            'satuan' => [
                'rules' => 'required',
                'label' => 'Satuan',
                'errors' => [
                    'required' => '{field} tidak boleh kosong',
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
            return redirect()->to('/berat/tambah');
        } else {
            try {
                $hasilBerat = $this->normalisasiBeratMaterial(
                    (string) $kodeprd,
                    $this->request->getPost('berat_material')
                );
            } catch (\InvalidArgumentException $e) {
                session()->setFlashdata($this->pesanError($e->getMessage()));
                return redirect()->to('/berat/tambah');
            }

            $db = \Config\Database::connect();
            try {
                $db->transBegin();
                $db->table('berat')->insert([
                    'kodeprd' => $kodeprd,
                    'kodemat' => $hasilBerat['materialUtama'],
                    'satuan' => $satuan,
                    'berat' => $hasilBerat['total'],
                ]);
                $db->table('berat_material')->insertBatch($hasilBerat['detail']);
                $db->table('stok')
                    ->where('kodebarang', $kodeprd)
                    ->update(['berat' => $hasilBerat['total']]);

                if ($db->transStatus() === false) {
                    throw new \RuntimeException('Transaksi database gagal.');
                }
                $db->transCommit();
            } catch (\Throwable $e) {
                $db->transRollback();
                log_message('error', 'Gagal menyimpan berat material: {pesan}', [
                    'pesan' => $e->getMessage(),
                ]);
                session()->setFlashdata($this->pesanError(
                    'Data berat gagal disimpan. Tidak ada perubahan yang diterapkan.'
                ));
                return redirect()->to('/berat/tambah');
            }

            $pesan_sukses = [
                'sukses' => '<div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-check"></i> Berhasil!</h5>
                Data Barang dengan kode <strong>' . $kodeprd . '</strong> berhasil di simpan
              </div>'
            ];

            session()->setFlashdata($pesan_sukses);

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

            return redirect()->to('/berat/tambah/');
        }
    }

    public function edit($kode)
    {
        $modelBerat = new Modelberat();
        $cekKode = $modelBerat->cekKode($kode);

        if ($cekKode->getNumRows() === 0) {
            exit('Data tidak ditemukan');
        }

        $row = $cekKode->getRowArray();
        $db = \Config\Database::connect();

        $materialProduk = $db->table('barang b')
            ->select('m.matid, m.matkode, m.matnama, COALESCE(bmtr.berat, 0) AS berat_material', false)
            ->join('material m', 'FIND_IN_SET(m.matid, b.brgmat) > 0', 'inner', false)
            ->join(
                'berat_material bmtr',
                'bmtr.kodeprd = b.brgkode AND bmtr.matid = m.matid',
                'left'
            )
            ->where('b.brgkode', $row['kodeprd'])
            ->orderBy('m.matnama', 'ASC')
            ->get()
            ->getResultArray();

        // Data lama sebelum migration tetap menampilkan total pada material utama.
        if ($materialProduk) {
            $totalDetail = array_sum(array_column($materialProduk, 'berat_material'));
            if ((float) $totalDetail <= 0 && (float) $row['berat'] > 0) {
                $materialUtamaDitemukan = false;
                foreach ($materialProduk as &$material) {
                    if ((int) $material['matid'] === (int) $row['kodemat']) {
                        $material['berat_material'] = $row['berat'];
                        $materialUtamaDitemukan = true;
                        break;
                    }
                }
                unset($material);

                if (!$materialUtamaDitemukan) {
                    $materialProduk[0]['berat_material'] = $row['berat'];
                }
            }
        }

        $data = [
            'kodeprd'       => $row['kodeprd'],
            'kodemat'       => $row['kodemat'],
            'materialProduk' => $materialProduk,
            'satuan'        => $row['satuan'],
            'berat'         => $row['berat'],
            'datasatuan'    => (new Modelsatuan())->findAll(),
        ];

        return view('berat/formedit', $data);
    }

    public function updatedata()
    {
        $kodeprd = $this->request->getVar('kodeprd');
        $satuan = $this->request->getVar('satuan');

        $validation = \Config\Services::validation();

        $valid = $this->validate([
            'kodeprd' => [
                'rules' => 'required|is_not_unique[berat.kodeprd]',
                'label' => 'Kode Produk',
            ],
            'satuan' => [
                'rules' => 'required',
                'label' => 'Satuan',
                'errors' => [
                    'required' => '{field} tidak boleh kosong',
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
            return redirect()->to('/berat/edit/' . sha1((string) $kodeprd));
        } else {
            try {
                $hasilBerat = $this->normalisasiBeratMaterial(
                    (string) $kodeprd,
                    $this->request->getPost('berat_material')
                );
            } catch (\InvalidArgumentException $e) {
                session()->setFlashdata($this->pesanError($e->getMessage()));
                return redirect()->to('/berat/edit/' . sha1((string) $kodeprd));
            }

            $db = \Config\Database::connect();
            try {
                $db->transBegin();
                $db->table('berat')->where('kodeprd', $kodeprd)->update([
                    'kodemat' => $hasilBerat['materialUtama'],
                    'satuan' => $satuan,
                    'berat' => $hasilBerat['total'],
                ]);
                $db->table('berat_material')->where('kodeprd', $kodeprd)->delete();
                $db->table('berat_material')->insertBatch($hasilBerat['detail']);
                $db->table('stok')
                    ->where('kodebarang', $kodeprd)
                    ->update(['berat' => $hasilBerat['total']]);

                if ($db->transStatus() === false) {
                    throw new \RuntimeException('Transaksi database gagal.');
                }
                $db->transCommit();
            } catch (\Throwable $e) {
                $db->transRollback();
                log_message('error', 'Gagal memperbarui berat material: {pesan}', [
                    'pesan' => $e->getMessage(),
                ]);
                session()->setFlashdata($this->pesanError(
                    'Data berat gagal diperbarui. Tidak ada perubahan yang diterapkan.'
                ));
                return redirect()->to('/berat/edit/' . sha1((string) $kodeprd));
            }

            $pesan_sukses = [
                'sukses' => '<div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-check"></i> Berhasil!</h5>
            Data Berat dengan kode <strong>' . $kodeprd . '</strong> berhasil di update
          </div>'
            ];

            session()->setFlashdata($pesan_sukses);

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

            return redirect()->to('/berat/index/');
        }
    }


    public function hapus()
    {
        if ($this->request->isAJAX()) {
            $kode = $this->request->getPost('kode');

            $db = \Config\Database::connect();

            $modelBerat = new Modelberat();
            $db->transStart();
            $db->table('berat_material')->where('kodeprd', $kode)->delete();
            $modelBerat->delete($kode);
            $db->transComplete();

            $json = $db->transStatus()
                ? ['sukses' => "Data Berat <b>{$kode}</b> Berhasil di Hapus"]
                : ['error' => "Data Berat <b>{$kode}</b> gagal dihapus"];

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
