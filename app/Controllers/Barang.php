<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Modelbarang;
use App\Models\Modelberat;
use App\Models\Modelmaterial;
use App\Models\Modelkategori;
use App\Models\Modelsatuan;
use App\Models\ModelPelanggan;
use App\Models\ModelBarangRiwayat;
use \Hermawan\DataTables\DataTable;
use App\Models\Modelstok;

class Barang extends BaseController
{
    protected $barang;
    protected $stok;
    protected $modelPelanggan;

    public function __construct()
    {
        $this->barang = new Modelbarang();
        $this->stok = new Modelstok();
        $this->modelPelanggan = new ModelPelanggan();
        $this->ensureTanpaBeratColumn();
        $this->ensureSumberMaterialColumns();
        $this->ensureMaterialColumnsNullable();
        $this->ensureWiseColumn();
    }

    /**
     * "Wise" -- angka pengurang pcs/kg (susut/waste pas produksi, beda-beda
     * tiap produk tergantung bentuk potongannya) yang dulunya cuma diketik
     * manual di rumus Excel (=1/berat - wise). Dipakai bareng Berat 1 Pcs
     * Produk Jadi buat hitung PCS/KG & Harga Material/PCS di form produk.
     */
    private function ensureWiseColumn(): void
    {
        $db = \Config\Database::connect();
        if ($db->tableExists('barang') && !$db->fieldExists('wise', 'barang')) {
            \Config\Database::forge()->addColumn('barang', [
                'wise' => [
                    'type' => 'DECIMAL',
                    'constraint' => '10,4',
                    'null' => true,
                    'after' => 'satuanberat',
                ],
            ]);
        }
    }

    /**
     * Produk "Beli Barang Jadi" boleh disimpan tanpa material sama sekali,
     * jadi kolom-kolom penentu material (barang.brgmat, berat.kodemat)
     * harus boleh NULL -- dulunya NOT NULL karena semua produk lain wajib
     * punya material.
     */
    private function ensureMaterialColumnsNullable(): void
    {
        $db = \Config\Database::connect();
        $forge = \Config\Database::forge();

        foreach ([
            ['table' => 'barang', 'column' => 'brgmat'],
            ['table' => 'berat', 'column' => 'kodemat'],
            ['table' => 'stok', 'column' => 'material'],
        ] as $target) {
            $table = $target['table'];
            $columnName = $target['column'];

            if (!$db->tableExists($table) || !$db->fieldExists($columnName, $table)) {
                continue;
            }

            try {
                $column = $db->query(
                    'SELECT IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
                    [$table, $columnName]
                )->getRowArray();

                if (strtoupper($column['IS_NULLABLE'] ?? '') === 'YES') {
                    continue;
                }

                $forge->modifyColumn($table, [
                    $columnName => [
                        'name' => $columnName,
                        'type' => 'INT',
                        'constraint' => 11,
                        'null' => true,
                    ],
                ]);
            } catch (\Throwable $e) {
                log_message('error', 'Gagal mengubah kolom {column} pada {table} jadi nullable: {message}', [
                    'column' => $columnName,
                    'table' => $table,
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }

    private function ensureTanpaBeratColumn(): void
    {
        $db = \Config\Database::connect();
        if ($db->tableExists('barang') && !$db->fieldExists('tanpa_berat', 'barang')) {
            \Config\Database::forge()->addColumn('barang', [
                'tanpa_berat' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                    'after' => 'brgmat',
                ],
            ]);
        }
    }

    private function relasiProduk(string $kode): array
    {
        return $this->periksaRelasiMaster([
            ['table' => 'detail_po', 'column' => 'detkodebrg', 'label' => 'Detail PO'],
            ['table' => 'outstanding', 'column' => 'kodebrg', 'label' => 'Outstanding PO'],
            ['table' => 'detail_barangmasuk', 'column' => 'detbrgkode', 'label' => 'Produk Masuk'],
            ['table' => 'detail_barangkeluar', 'column' => 'detbrgkode', 'label' => 'Produk Keluar'],
            ['table' => 'detail_permintaanbarang', 'column' => 'detkodebrg', 'label' => 'Permintaan Transfer'],
            ['table' => 'detail_permintaanbarangkirim', 'column' => 'detkodebrg', 'label' => 'Pengiriman Transfer'],
            ['table' => 'detail_permintaan_pengiriman', 'column' => 'kode_produk', 'label' => 'Permintaan Pengiriman'],
            ['table' => 'rencana_pengiriman', 'column' => 'kode_produk', 'label' => 'Rencana Pengiriman'],
            ['table' => 'temp_po', 'column' => 'detkodebrg', 'label' => 'Draft PO'],
            ['table' => 'temp_barangmasuk', 'column' => 'detbrgkode', 'label' => 'Draft Produk Masuk'],
            ['table' => 'temp_barangkeluar', 'column' => 'detbrgkode', 'label' => 'Draft Produk Keluar'],
            ['table' => 'temp_permintaanbarang', 'column' => 'detkodebrg', 'label' => 'Draft Permintaan Transfer'],
            ['table' => 'temp_permintaanbarangkirim', 'column' => 'detkodebrg', 'label' => 'Draft Pengiriman Transfer'],
        ], $kode);
    }

    private function relasiProdukTransaksi(string $kode): array
    {
        return $this->periksaRelasiMaster([
            ['table' => 'detail_po', 'column' => 'detkodebrg', 'label' => 'Detail PO'],
            ['table' => 'outstanding', 'column' => 'kodebrg', 'label' => 'Outstanding PO'],
            ['table' => 'detail_barangmasuk', 'column' => 'detbrgkode', 'label' => 'Produk Masuk'],
            ['table' => 'detail_barangkeluar', 'column' => 'detbrgkode', 'label' => 'Produk Keluar'],
            ['table' => 'detail_permintaanbarang', 'column' => 'detkodebrg', 'label' => 'Permintaan Transfer'],
            ['table' => 'detail_permintaanbarangkirim', 'column' => 'detkodebrg', 'label' => 'Pengiriman Transfer'],
            ['table' => 'detail_permintaan_pengiriman', 'column' => 'kode_produk', 'label' => 'Permintaan Pengiriman'],
            ['table' => 'rencana_pengiriman', 'column' => 'kode_produk', 'label' => 'Rencana Pengiriman'],
            ['table' => 'temp_po', 'column' => 'detkodebrg', 'label' => 'Draft PO'],
            ['table' => 'temp_barangmasuk', 'column' => 'detbrgkode', 'label' => 'Draft Produk Masuk'],
            ['table' => 'temp_barangkeluar', 'column' => 'detbrgkode', 'label' => 'Draft Produk Keluar'],
            ['table' => 'temp_permintaanbarang', 'column' => 'detkodebrg', 'label' => 'Draft Permintaan Transfer'],
            ['table' => 'temp_permintaanbarangkirim', 'column' => 'detkodebrg', 'label' => 'Draft Pengiriman Transfer'],
        ], $kode);
    }

    private function labelSumberMaterial(?string $sumberMaterial): string
    {
        return match ($sumberMaterial) {
            'vendor' => 'Material dari Customer',
            'beli_jadi' => 'Beli Barang Jadi (Full dari Vendor)',
            default => 'Material TRE',
        };
    }

    public function pemakaian()
    {
        $hash = (string) $this->request->getGet('hash');
        $cekId = $this->barang->cekId($hash);

        if ($cekId->getNumRows() === 0) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Data produk tidak ditemukan.']);
        }

        $kode = $cekId->getRowArray()['brgkode'];
        return $this->response->setJSON(['pemakaian' => $this->relasiProdukTransaksi($kode)]);
    }

    public function index()
    {
        return view('barang/viewdatabarang', [
            'datakategori' => (new Modelkategori())->orderBy('katnama', 'ASC')->findAll(),
            'datamaterial' => (new Modelmaterial())->orderBy('matnama', 'ASC')->findAll(),
        ]);
    }

    public function hargaProduk()
    {
        return view('barang/viewhargaproduk');
    }

    public function listDataHarga()
    {
        if ($this->request->isAJAX()) {
            $db = \Config\Database::connect();

            $builder = $db->table('barang')
                ->select("
                    barang.brgkode,
                    barang.brgnama,
                    kategori.katnama,
                    satuan.satnama,
                    barang.harga
                ", false)
                ->join('kategori', 'kategori.katid = barang.brgkatid')
                ->join('satuan', 'satuan.satid = barang.brgsatid');

            return DataTable::of($builder)
                ->setSearchableColumns([
                    'barang.brgkode',
                    'barang.brgnama',
                    'kategori.katnama',
                    'satuan.satnama',
                ])
                ->addNumbering('nomor')
                ->format('harga', function ($value) {
                    return 'Rp ' . number_format($value, 0, ',', '.');
                })
                ->toJson(true);
        }
    }

    public function listData()
    {
        if ($this->request->isAJAX()) {
            $filterKategori = $this->request->getPost('filter_kategori');
            $filterMaterial = $this->request->getPost('filter_material');
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

            $builder = $db->table('barang')
                ->select("
                    barang.brgkode,
                    barang.brgnama,
                    barang.tanpa_berat,
                    COALESCE(barang.sumber_material, 'tre') AS sumber_material,
                    material_produk.matnama,
                    kategori.katnama,
                    satuan.satnama,
                    barang.idpel,
                    barang.brgstok,
                    barang.harga
                ", false)
                ->join(
                    "($materialQuery) AS material_produk",
                    'material_produk.brgkode = barang.brgkode',
                    'left',
                    false
                )
                ->join(
                    'kategori',
                    'kategori.katid = barang.brgkatid'
                )
                ->join(
                    'satuan',
                    'satuan.satid = barang.brgsatid'
                )
                ->join(
                    'pelanggan',
                    'pelanggan.pelid = barang.idpel'
                );

            if ($filterKategori) {
                $builder->where('barang.brgkatid', $filterKategori);
            }
            if ($filterMaterial) {
                $builder->where('FIND_IN_SET(' . $db->escape((string) $filterMaterial) . ', barang.brgmat) > 0', null, false);
            }

            return DataTable::of($builder)
                ->setSearchableColumns([
                    'barang.brgkode',
                    'barang.brgnama',
                    'material_produk.matnama',
                    'kategori.katnama',
                    'satuan.satnama'
                ])
                ->addNumbering('nomor')
                ->add('matnama_label', function ($row) {
                    $material = trim((string) ($row->matnama ?? ''));
                    if (($row->sumber_material ?? 'tre') === 'vendor') {
                        return ($material !== '' ? esc($material) . '<br>' : '')
                            . '<span class="badge badge-info">Material dari Customer</span>';
                    }

                    if (($row->sumber_material ?? 'tre') === 'beli_jadi') {
                        return '<span class="badge badge-dark">Beli Barang Jadi</span>';
                    }

                    if ((int) ($row->tanpa_berat ?? 0) === 1) {
                        return ($material !== '' ? esc($material) . '<br>' : '')
                            . '<span class="badge badge-info">Jasa / Tanpa Berat</span>';
                    }

                    return esc($material !== '' ? $material : '-');
                })
                ->add('aksi', function ($row) {
                    return "
                        <button type=\"button\"
                            class=\"btn btn-sm btn-primary\"
                            title=\"Edit Data\"
                            onclick=\"edit('" . sha1($row->brgkode) . "')\">
                            <i class=\"fa fa-edit\"></i>
                        </button>&nbsp;

                        <button type=\"button\"
                            class=\"btn btn-sm btn-secondary\"
                            title=\"Riwayat Perubahan\"
                            onclick=\"riwayat('" . sha1($row->brgkode) . "')\">
                            <i class=\"fa fa-history\"></i>
                        </button>&nbsp;

                        <button type=\"button\"
                            class=\"btn btn-sm btn-danger\"
                            title=\"Hapus Data\"
                            onclick=\"hapus('" . $row->brgkode . "')\">
                            <i class=\"fa fa-trash-alt\"></i>
                        </button>
                    ";
                })
                ->format('brgstok', function ($value) {
                    return number_format($value, 0, ',', '.');
                })
                ->format('harga', function ($value) {
                    return number_format($value, 0, ',', '.');
                })
                ->toJson(true);
        }
    }

    public function tambah()
    {
        $modelkategori = new Modelkategori();
        $modelsatuan = new Modelsatuan();
        $modelmaterial = new Modelmaterial();
        $pelanggans = $this->modelPelanggan->whereNotIn('pelid', [1, 2])->findAll();

        $data = [
            'datakategori' => $modelkategori->findAll(),
            'datasatuan' => $modelsatuan->findAll(),
            'datamaterial' => $modelmaterial->findAll(),
            'datapelanggan' => $pelanggans,
        ];

        return view('barang/formtambah', $data);
    }

    public function simpandata()
    {
        $kodebarang = $this->request->getVar('kodebarang');
        $namabarang = $this->request->getVar('namabarang');
        $materials = $this->materialDariRequest();
        $materialUtama = $this->materialUtama($materials);
        $materialProduk = implode(',', $materials);
        $kategori = $this->request->getVar('kategori');
        $satuan = $this->request->getVar('satuan');
        $satuanberat = $this->request->getVar('satuanberat');
        $harga = $this->request->getVar('harga');
        $minstok = $this->request->getVar('minstok');
        $idpel = $this->request->getVar('idpel');
        $wise = $this->request->getPost('wise');
        $wise = ($wise !== null && $wise !== '' && is_numeric($wise)) ? (float) $wise : null;
        $sumberMaterial = in_array($this->request->getPost('sumber_material'), ['vendor', 'beli_jadi'], true)
            ? $this->request->getPost('sumber_material')
            : 'tre';
        // "beli_jadi" (full beli barang jadi dari vendor) TETAP punya berat
        // asli (dipakai buat hitung berat kirim dll), beda dari "vendor"
        // (Material dari Customer) yang emang udah lama dianggap otomatis
        // tanpa-berat.
        $tanpaBerat = ($sumberMaterial === 'vendor' || $this->request->getPost('tanpa_berat') === '1') ? 1 : 0;

        $validation = \Config\Services::validation();

        $valid = $this->validate([
            'kodebarang' => [
                'rules' => 'required|is_unique[barang.brgkode]',
                'label' => 'Kode Barang',
                'errors' => [
                    'required' => '{field} tidak boleh kosong',
                    'is_unique' => '{field} sudah terpakai'
                ]
            ],
            'namabarang' => [
                'rules' => 'required|is_unique[barang.brgnama]',
                'label' => 'Nama Barang',
                'errors' => [
                    'required' => '{field} tidak boleh kosong',
                    'is_unique' => '{field} sudah terpakai'
                ]
            ],
            'kategori' => [
                'rules' => 'required',
                'label' => 'Kategori',
                'errors' => [
                    'required' => '{field} tidak boleh kosong',
                ]
            ],
            'idpel' => [
                'rules' => 'required',
                'label' => 'Pelanggan',
                'errors' => [
                    'required' => '{field} tidak boleh kosong',
                ]
            ],
            'satuan' => [
                'rules' => 'required',
                'label' => 'Satuan',
                'errors' => [
                    'required' => '{field} tidak boleh kosong',
                ]
            ],
            'harga' => [
                'rules' => 'required',
                'label' => 'Harga',
                'errors' => [
                    'required' => '{field} tidak boleh kosong',
                    'numeric' => '{field} harus berupa angka',
                ]
            ],
        ]);

        if ($sumberMaterial === 'tre' && !$this->request->getPost('material_utama')) {
            $valid = false;
            $validation->setError('material_utama', 'Material inti wajib dipilih untuk produk TRE.');
        }
        if (empty($materials) && $sumberMaterial !== 'beli_jadi') {
            $valid = false;
            $validation->setError('material', 'Material wajib dipilih, termasuk untuk produk dari customer/jasa sebagai referensi.');
        }

        if (!$valid) {
            $sess_Pesan = [
                'error' => '<div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-ban"></i> Error!</h5>
                ' . $validation->listErrors() . '
              </div>'
            ];

            session()->setFlashdata($sess_Pesan);
            return redirect()->to('/barang/tambah');
        } else {
            $db = \Config\Database::connect();
            $db->transStart();

            // Baris `barang` harus dibuat DULU sebelum simpanBerat(), karena
            // simpanBerat() nulis ke tabel `berat` yang FK-nya nunjuk ke
            // barang.brgkode -- kalau kebalik, insert ke `berat` gagal FK
            // constraint karena baris produknya sendiri belum ada.
            $this->barang->insert([
                'brgkode' => $kodebarang,
                'brgnama' => $namabarang,
                'brgmat' => $materialProduk !== '' ? $materialProduk : null,
                'tanpa_berat' => $tanpaBerat,
                'sumber_material' => $sumberMaterial,
                'brgkatid' => $kategori,
                'brgsatid' => $satuan,
                'brgstok' => 0,
                'minstok' => $minstok,
                'satuanberat' => $satuanberat,
                'wise' => $wise,
                'harga' => $harga,
                'idpel' => $idpel,
            ]);

            try {
                $beratProdukJadi = $this->simpanBerat($kodebarang, $materials, (bool) $tanpaBerat, $sumberMaterial === 'beli_jadi');
            } catch (\InvalidArgumentException $e) {
                $db->transRollback();
                session()->setFlashdata('error', '<div class="alert alert-danger">' . esc($e->getMessage()) . '</div>');
                return redirect()->to('/barang/tambah')->withInput();
            }

            $data = array(
                array(
                    'kodebarang' => $kodebarang,
                    'namabarang' => $namabarang,
                    'material' => $materialUtama,
                    'satuan' => $satuan,
                    'satuanberat' => $satuanberat,
                    'gudang' => 1,
                    'berat' => $beratProdukJadi,
                    'stok' => 0,
                    'harga' => $harga,
                    'idpel' => $idpel,
                ),

                array(
                    'kodebarang' => $kodebarang,
                    'namabarang' => $namabarang,
                    'material' => $materialUtama,
                    'satuan' => $satuan,
                    'satuanberat' => $satuanberat,
                    'gudang' => 2,
                    'berat' => $beratProdukJadi,
                    'stok' => 0,
                    'harga' => $harga,
                    'idpel' => $idpel,
                )
            );

            $modelstok = new Modelstok();

            $modelstok->updateOrInsertBatch($data);

            $db->transComplete();

            if (!$db->transStatus()) {
                return redirect()->back()->withInput()->with('error', 'Data produk gagal disimpan.');
            }

            $pesan_sukses = [
                'sukses' => '<div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-check"></i> Berhasil!</h5>
                Data Barang dengan kode <strong>' . $kodebarang . '</strong> berhasil di simpan
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

            session()->setFlashdata($pesan_sukses);
            return redirect()->to('/barang/tambah/');
        }
    }

    public function riwayat($kode)
    {
        $cekId = $this->barang->cekId($kode);
        if ($cekId->getNumRows() === 0) {
            exit('Data tidak ditemukan');
        }
        $row = $cekId->getRowArray();

        $riwayat = (new ModelBarangRiwayat())
            ->where('brgkode', $row['brgkode'])
            ->orderBy('diubah_pada', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll();

        foreach ($riwayat as &$item) {
            $item['data_lama'] = json_decode($item['data_lama'] ?? '{}', true) ?: [];
            $item['data_baru'] = json_decode($item['data_baru'] ?? '{}', true) ?: [];
        }

        return view('barang/riwayat', [
            'kodebarang' => $row['brgkode'],
            'namabarang' => $row['brgnama'],
            'riwayat' => $riwayat,
        ]);
    }

    public function edit($kode)
    {
        $modelBarang = new Modelbarang();
        $cekId = $modelBarang->cekId($kode);

        if ($cekId->getNumRows() > 0) {
            $row = $cekId->getRowArray();

            $materialTerpilih = $this->normalisasiMaterial($row['brgmat'] ?? '');

            $modelkategori = new Modelkategori();
            $modelsatuan = new Modelsatuan();
            $modelmaterial = new Modelmaterial();
            $pelanggans = $this->modelPelanggan->whereNotIn('pelid', [1, 2])->findAll();

            $beratMaterialRows = \Config\Database::connect()
                ->table('berat_material')
                ->where('kodeprd', $row['brgkode'])
                ->get()->getResultArray();
            $beratMaterial = [];
            foreach ($beratMaterialRows as $bm) {
                $beratMaterial[(int) $bm['matid']] = $bm['berat'];
            }

            $beratProduk = (new Modelberat())->find($row['brgkode']);
            $pemakaianTransaksi = $this->relasiProdukTransaksi($row['brgkode']);

            $data = [
                'kodebarang' => $row['brgkode'],
                'kodeDapatDiubah' => empty($pemakaianTransaksi),
                'pemakaianKodeProduk' => $pemakaianTransaksi,
                'namabarang' => $row['brgnama'],
                'material' => array_map('intval', $materialTerpilih),
                'materialUtama' => $materialTerpilih[0] ?? null,
                'materialAlternatif' => array_map('intval', array_slice($materialTerpilih, 1)),
                'tanpaBerat' => (int) ($row['tanpa_berat'] ?? 0) === 1,
                'sumberMaterial' => in_array($row['sumber_material'] ?? 'tre', ['vendor', 'beli_jadi'], true) ? $row['sumber_material'] : 'tre',
                'kategori' => $row['brgkatid'],
                'satuan' => $row['brgsatid'],
                'satuanberat' => $row['satuanberat'],
                'wise' => $row['wise'] ?? null,
                'harga' => $row['harga'],
                'minstok' => $row['minstok'],
                'idpel' => $row['idpel'],
                'stok' => 0,
                'datakategori' => $modelkategori->findAll(),
                'datasatuan' => $modelsatuan->findAll(),
                'datamaterial' => $modelmaterial->findAll(),
                'datapelanggan' => $pelanggans,
                'beratMaterial' => $beratMaterial,
                'beratProdukJadi' => $beratProduk['berat'] ?? null,
            ];
            return view('barang/formedit', $data);
        } else {
            exit('Data tidak ditemukan');
        }
    }

    public function updatedata()
    {
        $kodebarang_lama = trim((string) $this->request->getVar('old_kodebarang'));
        $kodebarang_input = trim((string) $this->request->getVar('kodebarang'));

        $cekLama = $this->barang->cekId(sha1($kodebarang_lama));
        if ($cekLama->getNumRows() === 0) {
            return redirect()->back()->withInput()->with('error', 'Data produk tidak ditemukan.');
        }
        $dataLama = $cekLama->getRowArray();
        $pemakaianTransaksi = $this->relasiProdukTransaksi($kodebarang_lama);
        $kodeDapatDiubah = empty($pemakaianTransaksi);
        $kodebarang_baru = $kodeDapatDiubah ? $kodebarang_input : $kodebarang_lama;

        if (!$kodeDapatDiubah && $kodebarang_input !== '' && $kodebarang_input !== $kodebarang_lama) {
            return redirect()->back()->withInput()->with('error', 'Kode produk tidak bisa diubah karena sudah dipakai transaksi.');
        }

        // Validasi input
        $sumberMaterial = in_array($this->request->getPost('sumber_material'), ['vendor', 'beli_jadi'], true)
            ? $this->request->getPost('sumber_material')
            : 'tre';
        $tanpaBerat = ($sumberMaterial === 'vendor' || $this->request->getPost('tanpa_berat') === '1') ? 1 : 0;
        $rules = [
            'kodebarang' => 'required',
            'namabarang' => 'required|is_unique[barang.brgnama,brgkode,' . $kodebarang_lama . ']',
            'kategori' => 'required',
            'satuan' => 'required',
            'harga' => 'required|numeric',
        ];

        if ($kodeDapatDiubah && $kodebarang_baru !== $kodebarang_lama) {
            $rules['kodebarang'] .= '|is_unique[barang.brgkode]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', $this->validator->getErrors());
        }

        $materials = $this->materialDariRequest();
        if ($sumberMaterial === 'tre' && !$this->request->getPost('material_utama')) {
            return redirect()->back()->withInput()->with('error', '<div class="alert alert-danger">Material inti wajib dipilih untuk produk TRE.</div>');
        }
        if (empty($materials) && $sumberMaterial !== 'beli_jadi') {
            return redirect()->back()->withInput()->with('error', '<div class="alert alert-danger">Material wajib dipilih, termasuk untuk produk dari customer/jasa sebagai referensi.</div>');
        }

        $materialUtama = $this->materialUtama($materials);
        $materialProduk = implode(',', $materials);
        $wise = $this->request->getPost('wise');
        $wise = ($wise !== null && $wise !== '' && is_numeric($wise)) ? (float) $wise : null;
        $db = \Config\Database::connect();
        $db->transStart();

        // Kolom brgmat menyimpan satu atau banyak material dalam format CSV,
        // contoh: "4" atau "4,15".
        $barangData = [
            'brgkode' => $kodebarang_baru,
            'brgnama' => $this->request->getVar('namabarang'),
            'brgmat' => $materialProduk !== '' ? $materialProduk : null,
            'tanpa_berat' => $tanpaBerat,
            'sumber_material' => $sumberMaterial,
            'brgkatid' => $this->request->getVar('kategori'),
            'brgsatid' => $this->request->getVar('satuan'),
            'satuanberat' => $this->request->getVar('satuanberat'),
            'wise' => $wise,
            'harga' => $this->request->getVar('harga'),
            'minstok' => $this->request->getVar('minstok'),
            'idpel' => $this->request->getVar('idpel'),
        ];
        // Rename/update baris barang DULUAN, sebelum simpanBerat() -- soalnya
        // simpanBerat() nyimpen ke tabel `berat` pakai $kodebarang_baru, dan
        // tabel itu punya FK ke barang.brgkode. Kalau baris barang.brgkode
        // yang baru belum ada (kode produk lagi diganti), insert ke `berat`
        // bakal ditolak FK constraint-nya.
        $this->barang->update($kodebarang_lama, $barangData); // Menggunakan brgkode sebagai key

        try {
            $beratProdukJadi = $this->simpanBerat($kodebarang_baru, $materials, (bool) $tanpaBerat, $sumberMaterial === 'beli_jadi');
        } catch (\InvalidArgumentException $e) {
            $db->transRollback();
            session()->setFlashdata('error', '<div class="alert alert-danger">' . esc($e->getMessage()) . '</div>');
            return redirect()->back()->withInput();
        }

        // Catat riwayat perubahan supaya kelihatan apa aja yang berubah dan
        // kapan -- transaksi lama tetap pakai snapshot data yang mereka
        // punya sendiri, cuma barang.* yang jadi acuan transaksi baru ke
        // depannya yang berubah.
        (new ModelBarangRiwayat())->insert([
            'brgkode' => $kodebarang_baru,
            'data_lama' => json_encode([
                'Kode Produk' => $dataLama['brgkode'],
                'Nama Produk' => $dataLama['brgnama'],
                'Kategori' => $dataLama['katnama'] ?? $dataLama['brgkatid'],
                'Satuan' => $dataLama['satnama'] ?? $dataLama['brgsatid'],
                'Harga' => $dataLama['harga'],
                'Min. Stok' => $dataLama['minstok'],
                'Tanpa Berat' => ((int) ($dataLama['tanpa_berat'] ?? 0) === 1) ? 'Ya' : 'Tidak',
                'Sumber Material' => $this->labelSumberMaterial($dataLama['sumber_material'] ?? 'tre'),
            ]),
            'data_baru' => json_encode([
                'Kode Produk' => $kodebarang_baru,
                'Nama Produk' => $barangData['brgnama'],
                'Kategori' => (new Modelkategori())->find($barangData['brgkatid'])['katnama'] ?? $barangData['brgkatid'],
                'Satuan' => (new Modelsatuan())->find($barangData['brgsatid'])['satnama'] ?? $barangData['brgsatid'],
                'Harga' => $barangData['harga'],
                'Min. Stok' => $barangData['minstok'],
                'Tanpa Berat' => $tanpaBerat ? 'Ya' : 'Tidak',
                'Sumber Material' => $this->labelSumberMaterial($sumberMaterial),
            ]),
            'diubah_oleh' => session()->get('namauser'),
            'diubah_pada' => date('Y-m-d H:i:s'),
        ]);

        // Update tabel `stok` -- pakai $kodebarang_baru (bukan lama) karena
        // FK stok.kodebarang -> barang.brgkode ON UPDATE CASCADE udah duluan
        // nge-rename baris stok ini pas $this->barang->update() di atas,
        // jadi kalau kode-nya diganti, baris `stok` udah ganti nama tapi
        // field lain (namabarang, harga, berat, dst) masih perlu disamain.
        $stokModel = new Modelstok();
        $stokModel->where('kodebarang', $kodebarang_baru)
            ->set([
                'kodebarang' => $kodebarang_baru,
                'namabarang' => $this->request->getVar('namabarang'),
                'material' => $materialUtama,
                'satuan' => $this->request->getVar('satuan'),
                'satuanberat' => $this->request->getVar('satuanberat'),
                'harga' => $this->request->getVar('harga'),
                'berat' => $beratProdukJadi,
                'idpel' => $this->request->getVar('idpel'),
            ])
            ->update();

        if ($kodebarang_baru !== $kodebarang_lama) {
            $this->bersihkanKodeProdukLama($db, $kodebarang_lama, $kodebarang_baru);
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            return redirect()->back()->withInput()->with('error', 'Data produk gagal diperbarui.');
        }

        return redirect()->to('/barang/index')->with('message', 'Data berhasil diperbarui');
    }

    public function hapus()
    {
        if ($this->request->isAJAX()) {
            $kode = $this->request->getPost('kode');

            $db = \Config\Database::connect();

            $pemakaian = $this->relasiProduk($kode);

            if ($pemakaian) {
                $json = [
                    'error' => $this->pesanRelasiMaster('produk ' . $kode, $pemakaian)
                ];
            } else {
                $db->transStart();
                $modelBarang = new ModelBarang();
                (new Modelstok())->where('kodebarang', $kode)->delete();
                $db->table('berat')->where('kodeprd', $kode)->delete();
                $db->table('berat_material')->where('kodeprd', $kode)->delete();
                $modelBarang->delete($kode);
                $db->transComplete();

                $json = $db->transStatus()
                    ? ['sukses' => "Data Produk {$kode} Berhasil di Hapus"]
                    : ['error' => "Data Produk {$kode} gagal dihapus"];
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

    private function normalisasiMaterial($material): array
    {
        $materials = is_array($material)
            ? $material
            : explode(',', (string) $material);

        return array_values(array_unique(array_filter(
            array_map('intval', $materials),
            static fn($id) => $id > 0
        )));
    }

    /**
     * Material inti selalu ditempatkan paling depan, sedangkan material
     * alternatif disimpan setelahnya di barang.brgmat. Format CSV ini tetap
     * dipertahankan karena banyak laporan lama membacanya dengan FIND_IN_SET.
     * Payload `material[]` lama tetap diterima untuk kompatibilitas.
     */
    private function materialDariRequest(): array
    {
        $materialUtama = $this->normalisasiMaterial($this->request->getPost('material_utama'));
        $materialAlternatif = $this->normalisasiMaterial($this->request->getPost('material_alternatif'));

        if (!$materialUtama && !$materialAlternatif) {
            return $this->normalisasiMaterial($this->request->getPost('material'));
        }

        return array_values(array_unique(array_merge(
            array_slice($materialUtama, 0, 1),
            $materialAlternatif
        )));
    }

    private function materialUtama(array $materials): ?int
    {
        return $materials[0] ?? null;
    }

    private function bersihkanKodeProdukLama($db, string $kodeLama, string $kodeBaru): void
    {
        foreach ([
            ['table' => 'stokcikarang', 'column' => 'brgkode'],
            ['table' => 'stokcirebon', 'column' => 'brgkode'],
        ] as $target) {
            if ($db->tableExists($target['table']) && $db->fieldExists($target['column'], $target['table'])) {
                $db->table($target['table'])
                    ->where($target['column'], $kodeLama)
                    ->update([$target['column'] => $kodeBaru]);
            }
        }

        if ($db->tableExists('berat') && $db->fieldExists('kodeprd', 'berat')) {
            $db->table('berat')->where('kodeprd', $kodeLama)->delete();
        }

        if ($db->tableExists('berat_material') && $db->fieldExists('kodeprd', 'berat_material')) {
            $db->table('berat_material')->where('kodeprd', $kodeLama)->delete();
        }

        if ($db->tableExists('barang_riwayat') && $db->fieldExists('brgkode', 'barang_riwayat')) {
            $db->table('barang_riwayat')
                ->where('brgkode', $kodeLama)
                ->update(['brgkode' => $kodeBaru]);
        }
    }

    /**
     * Simpan berat/ukuran bersih produk -- dulunya dikelola lewat menu
     * Berat terpisah, sekarang jadi bagian dari form Tambah/Edit Produk.
     * Upsert ke `berat_material` (rincian material yang TERPAKAI per pcs --
     * sekadar referensi/bill of material) dan `berat` (berat produk JADI
     * per pcs, diisi manual). Dua angka ini sengaja dipisah karena berat
     * material yang masuk proses produksi bisa lebih besar dari berat
     * produk jadi (ada yang kepotong/susut) -- dan yang dipakai buat
     * hitung berat pengiriman (Barangkeluar) itu berat produk jadi, bukan
     * berat material.
     *
     * @throws \InvalidArgumentException kalau berat material/produk jadi belum lengkap/valid
     */
    /**
     * Berat produk/material di form ini selalu diinput dalam gram lalu
     * dikonversi & disimpan dalam Kg (lihat JS di formtambah/formedit).
     * satuan_berat karenanya selalu Kg -- bukan hasil pilihan user -- jadi
     * di-resolve dari nama di sini, bukan mengandalkan field hidden
     * "satuanberat" yang cuma ke-update saat dropdown Material di-ubah
     * (kalau produk cuma diedit tanpa ganti material, nilainya jadi basi/0
     * dan bikin Produksi::simpanOtomatis() salah bandingkan satuan).
     */
    private function simpanBeratProdukJadi(string $kodebarang, ?int $materialUtama, float $beratProdukJadi, $satuanBerat): void
    {
        $db = \Config\Database::connect();
        $data = [
            'kodeprd' => $kodebarang,
            'kodemat' => $materialUtama,
            'berat' => $beratProdukJadi,
            'satuan' => $satuanBerat,
        ];

        $existing = $db->table('berat')
            ->where('kodeprd', $kodebarang)
            ->get()
            ->getRowArray();

        if ($existing) {
            $db->table('berat')->where('kodeprd', $kodebarang)->update($data);
        } else {
            $db->table('berat')->insert($data);
        }

        $db->table('stok')
            ->where('kodebarang', $kodebarang)
            ->update(['berat' => $beratProdukJadi]);
    }

    private function simpanBerat(string $kodebarang, array $materialIds, bool $tanpaBerat = false, bool $materialTidakWajib = false): float
    {
        $satuanKg = (new Modelsatuan())->where('satnama', 'Kg')->first();
        $satuanBerat = $satuanKg['satid'] ?? null;
        $db = \Config\Database::connect();

        if ($tanpaBerat) {
            $db->table('berat_material')->where('kodeprd', $kodebarang)->delete();
            $materialUtama = $this->materialUtama($materialIds);
            if ($materialUtama !== null) {
                $this->simpanBeratProdukJadi($kodebarang, $materialUtama, 0.0, $satuanBerat);
            } else {
                (new Modelberat())->where('kodeprd', $kodebarang)->delete();
            }

            return 0.0;
        }

        $beratProdukJadi = $this->request->getPost('berat_produk_jadi');
        if ($beratProdukJadi === null || $beratProdukJadi === '' || !is_numeric($beratProdukJadi) || (float) $beratProdukJadi <= 0) {
            throw new \InvalidArgumentException('Berat 1 Pcs Produk Jadi wajib diisi dengan angka lebih besar dari 0.');
        }
        $beratProdukJadi = (float) $beratProdukJadi;

        // Produk "Beli Barang Jadi": material bukan komponen produksi TRE
        // (nggak pernah dipakai/diolah di TRE sama sekali), jadi rincian
        // berat per material dilewati -- material boleh kosong. Berat
        // produk jadi tetap wajib karena masih dipakai buat hitung berat
        // pengiriman.
        if ($materialTidakWajib) {
            $db->table('berat_material')->where('kodeprd', $kodebarang)->delete();
            $this->simpanBeratProdukJadi($kodebarang, $this->materialUtama($materialIds), $beratProdukJadi, $satuanBerat);

            return $beratProdukJadi;
        }

        $inputBerat = $this->request->getPost('berat_material');
        if (!is_array($inputBerat)) {
            throw new \InvalidArgumentException('Berat setiap material wajib diisi.');
        }

        $modelMaterial = new Modelmaterial();
        $detail = [];

        foreach ($materialIds as $matid) {
            $nilai = $inputBerat[$matid] ?? null;
            $namaMaterial = $modelMaterial->find($matid)['matnama'] ?? "material #{$matid}";

            if ($nilai === null || $nilai === '' || !is_numeric($nilai)) {
                throw new \InvalidArgumentException("Berat material {$namaMaterial} wajib diisi dengan angka.");
            }

            $berat = (float) $nilai;
            if ($berat <= 0) {
                throw new \InvalidArgumentException("Berat material {$namaMaterial} harus lebih besar dari 0.");
            }

            $detail[] = [
                'kodeprd' => $kodebarang,
                'matid' => $matid,
                'berat' => $berat,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }

        $db->table('berat_material')->where('kodeprd', $kodebarang)->delete();
        if ($detail) {
            $db->table('berat_material')->insertBatch($detail);
        }

        $this->simpanBeratProdukJadi(
            $kodebarang,
            $this->materialUtama($materialIds),
            $beratProdukJadi,
            $satuanBerat
        );

        return $beratProdukJadi;
    }

    /**
     * Harga material terakhir per KG, buat kalkulasi PCS/KG & Harga
     * Material/PCS di form produk (pakai Wise). Dipanggil AJAX tiap kali
     * material yang dipilih berubah. Sumbernya sama kayak yang dipakai
     * InvoiceHub buat Reporting Margin -- harga terakhir dari Invoice In
     * (pembelian material asli, baik lewat PO Material atau PO Keluar Beli).
     */
    public function hargaMaterialTerakhir()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $matid = (int) $this->request->getGet('matid');
        if ($matid <= 0) {
            return $this->response->setJSON(['harga' => null]);
        }

        $material = (new Modelmaterial())->find($matid);
        if (!$material) {
            return $this->response->setJSON(['harga' => null]);
        }

        $row = \Config\Database::connect()->table('invoice_in_detail iid')
            ->select('iid.unit_price')
            ->join('invoice_in ii', 'ii.id = iid.invoice_id')
            ->join('po_keluar pk', "ii.source_type = 'po_keluar' AND pk.no_po = ii.source_no", 'left')
            ->where('ii.status !=', 'DIBATALKAN')
            ->groupStart()
                ->where('ii.source_type', 'material')
                ->orGroupStart()
                    ->where('ii.source_type', 'po_keluar')
                    ->where('pk.jenis_transaksi', 'Beli')
                ->groupEnd()
            ->groupEnd()
            ->groupStart()
                ->where('iid.item_code', $material['matkode'])
                ->orWhere('iid.item_code', (string) $matid)
            ->groupEnd()
            ->where('iid.unit_price >', 0)
            ->orderBy('ii.invoice_date', 'DESC')
            ->orderBy('iid.id', 'DESC')
            ->get()->getRowArray();

        return $this->response->setJSON([
            'harga' => $row ? (float) $row['unit_price'] : null,
        ]);
    }
}
