<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\PoPdfParser;
use App\Models\Modelbarang;
use App\Models\Modelberat;
use App\Models\ModelDataStok;
use App\Models\Modeldetailpo;
use App\Models\Modeloutstand;
use App\Models\ModelPelanggan;
use App\Models\Modelpo;
use App\Models\Modelstok;
use App\Models\Modeltemppo;
use App\Models\ModelPoCloseLog;
use \Hermawan\DataTables\DataTable;
use Config\Services;

class Po extends BaseController
{
    protected $db;
    protected $modelPelanggan;

    public function __construct()
    {
        $this->db = db_connect();
        $this->modelPelanggan = new ModelPelanggan();
        $this->ensurePoMigrationColumns();
        $this->ensurePoCloseLogTable();
        $this->ensurePoQtyAdjustmentLogTable();
    }

    /**
     * Tabel log "Close Item/PO" -- dibuat self-healing di sini (bukan cuma
     * lewat migration) biar langsung kepakai walau migration belum sempat
     * dijalankan manual di server, konsisten sama pola ensure*() lain.
     */
    private function ensurePoCloseLogTable(): void
    {
        $forge = \Config\Database::forge();

        if (!$this->db->tableExists('po_close_log')) {
            $forge->addField([
                'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
                'nopo_asal' => ['type' => 'VARCHAR', 'constraint' => 255],
                'kodebrg' => ['type' => 'CHAR', 'constraint' => 100],
                'namabarang' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
                'qty_dipindah' => ['type' => 'DOUBLE'],
                'harga_satuan' => ['type' => 'DOUBLE', 'default' => 0],
                'nopo_tujuan' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'ditutup_oleh' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
                'ditutup_pada' => ['type' => 'DATETIME', 'null' => true],
                'reopened_by' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
                'reopened_at' => ['type' => 'DATETIME', 'null' => true],
                'reopen_note' => ['type' => 'TEXT', 'null' => true],
            ]);
            $forge->addPrimaryKey('id');
            $forge->addKey('nopo_asal');
            $forge->addKey('nopo_tujuan');
            $forge->createTable('po_close_log', true);
            return;
        }

        // nopo_tujuan dulunya NOT NULL -- sekarang boleh NULL buat catat
        // alokasi Metode 2 (sesuaikan qty, tidak nyambung ke PO manapun).
        try {
            $column = $this->db->query(
                'SELECT IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
                ['po_close_log', 'nopo_tujuan']
            )->getRowArray();

            if (strtoupper($column['IS_NULLABLE'] ?? '') !== 'YES') {
                $forge->modifyColumn('po_close_log', [
                    'nopo_tujuan' => [
                        'name' => 'nopo_tujuan',
                        'type' => 'VARCHAR',
                        'constraint' => 255,
                        'null' => true,
                    ],
                ]);
            }
        } catch (\Throwable $e) {
            log_message('error', 'Gagal mengubah kolom po_close_log.nopo_tujuan jadi nullable: {message}', ['message' => $e->getMessage()]);
        }

        $missingColumns = [];
        if (!$this->db->fieldExists('reopened_by', 'po_close_log')) {
            $missingColumns['reopened_by'] = ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true];
        }
        if (!$this->db->fieldExists('reopened_at', 'po_close_log')) {
            $missingColumns['reopened_at'] = ['type' => 'DATETIME', 'null' => true];
        }
        if (!$this->db->fieldExists('reopen_note', 'po_close_log')) {
            $missingColumns['reopen_note'] = ['type' => 'TEXT', 'null' => true];
        }

        if ($missingColumns) {
            try {
                $forge->addColumn('po_close_log', $missingColumns);
            } catch (\Throwable $e) {
                log_message('error', 'Gagal menambah kolom reopen po_close_log: {message}', ['message' => $e->getMessage()]);
            }
        }
    }

    /**
     * Log koreksi qty PO Masuk yang sudah terkunci transaksi lanjutan.
     * Qty asli boleh berubah, tapi jejak koreksinya tetap tersimpan.
     */
    private function ensurePoQtyAdjustmentLogTable(): void
    {
        $forge = \Config\Database::forge();

        if (!$this->db->tableExists('po_qty_adjust_log')) {
            $forge->addField([
                'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
                'nopo' => ['type' => 'VARCHAR', 'constraint' => 255],
                'detail_po_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
                'kodebrg' => ['type' => 'CHAR', 'constraint' => 100],
                'namabarang' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
                'qty_sebelum' => ['type' => 'DOUBLE'],
                'qty_sesudah' => ['type' => 'DOUBLE'],
                'qty_delta' => ['type' => 'DOUBLE'],
                'harga_satuan' => ['type' => 'DOUBLE', 'default' => 0],
                'harga_sebelum' => ['type' => 'DOUBLE', 'default' => 0],
                'harga_sesudah' => ['type' => 'DOUBLE', 'default' => 0],
                'alasan' => ['type' => 'TEXT', 'null' => true],
                'dibuat_oleh' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
                'dibuat_pada' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $forge->addPrimaryKey('id');
            $forge->addKey('nopo');
            $forge->addKey('kodebrg');
            $forge->createTable('po_qty_adjust_log', true);
        }
    }

    private function ensurePoMigrationColumns(): void
    {
        $forge = \Config\Database::forge();

        if ($this->db->tableExists('po') && !$this->db->fieldExists('is_migrasi', 'po')) {
            $forge->addColumn('po', [
                'is_migrasi' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                    'after' => 'hargapo',
                ],
            ]);
        }

        if ($this->db->tableExists('detail_po') && !$this->db->fieldExists('detkirim_awal', 'detail_po')) {
            $forge->addColumn('detail_po', [
                'detkirim_awal' => [
                    'type' => 'DOUBLE',
                    'default' => 0,
                    'after' => 'detkirim',
                ],
            ]);
        }

        if ($this->db->tableExists('detail_po') && !$this->db->fieldExists('detinvoice_awal', 'detail_po')) {
            $forge->addColumn('detail_po', [
                'detinvoice_awal' => [
                    'type' => 'DOUBLE',
                    'default' => 0,
                    'after' => 'detkirim_awal',
                ],
            ]);
        }

        if ($this->db->tableExists('temp_po') && !$this->db->fieldExists('detkirim_awal', 'temp_po')) {
            $forge->addColumn('temp_po', [
                'detkirim_awal' => [
                    'type' => 'DOUBLE',
                    'default' => 0,
                    'after' => 'detqty',
                ],
            ]);
        }

        if ($this->db->tableExists('temp_po') && !$this->db->fieldExists('detinvoice_awal', 'temp_po')) {
            $forge->addColumn('temp_po', [
                'detinvoice_awal' => [
                    'type' => 'DOUBLE',
                    'default' => 0,
                    'after' => 'detkirim_awal',
                ],
            ]);
        }
    }

    public function data()
    {
        $pelanggans = $this->modelPelanggan->whereNotIn('pelid', [1, 2])->findAll();
        return view('po/viewdata', ['pelanggans' => $pelanggans]);
    }

    public function listData()
    {
            $tglawal = $this->request->getPost('tglawal');
            $tglakhir = $this->request->getPost('tglakhir');
            $progressFilter = (string) $this->request->getPost('progress');

            $db = \Config\Database::connect();
            $builder = $db->table('po')->select('nopo,tglpo,idpel,pelnama,qty,hargapo')
                ->join('pelanggan', 'pelid=idpel')
                ->whereNotIn('po.idpel', [1, 2]);

            if ($tglawal && $tglakhir) {
                $builder->whereIn('po.nopo', function ($subQuery) use ($tglawal, $tglakhir) {
                    $subQuery->select('po.nopo')
                        ->from('po')
                        ->where('po.tglpo >=', $tglawal)
                        ->where('po.tglpo <=', $tglakhir);
                });
            }

            return DataTable::of($builder)
                ->addNumbering('nomor')
                ->filter(function ($builder, $request) use ($tglawal, $tglakhir, $progressFilter) {
                    if ($request->pelanggan) {
                        $builder->where('po.idpel', $request->pelanggan);
                    }

                    if ($progressFilter !== '') {
                        $progressQuery = $this->db->table('po')
                            ->select('po.nopo')
                            ->join('pelanggan', 'pelid=idpel')
                            ->whereNotIn('po.idpel', [1, 2]);

                        if ($tglawal && $tglakhir) {
                            $progressQuery->where('po.tglpo >=', $tglawal)
                                ->where('po.tglpo <=', $tglakhir);
                        }

                        if ($request->pelanggan) {
                            $progressQuery->where('po.idpel', $request->pelanggan);
                        }

                        $matchingPo = [];
                        foreach ($progressQuery->get()->getResultArray() as $poRow) {
                            $latestProgress = $this->progressTerakhirPo($this->hitungProgressPo($poRow['nopo']));
                            if ($latestProgress['key'] === $progressFilter) {
                                $matchingPo[] = $poRow['nopo'];
                            }
                        }

                        if ($matchingPo) {
                            $builder->whereIn('po.nopo', $matchingPo);
                        } else {
                            $builder->where('po.nopo', '__no_matching_progress__');
                        }
                    }
                })
                ->add('progress', function ($row) {
                    $latestProgress = $this->progressTerakhirPo($this->hitungProgressPo($row->nopo));
                    return "<span class=\"badge badge-{$latestProgress['class']}\">" . esc($latestProgress['label']) . '</span>';
                })
                ->add('aksi', function ($row) {
                    $tombolProgress = "<a class=\"btn btn-sm btn-info\" href=\"/po/progress/" . sha1($row->nopo) . "\" title=\"Lihat Progress\"><i class=\"fa fa-eye\"></i></a>";
                    if (\App\Libraries\AccessControl::can('order.po_masuk.delete')) {
                        return "<div class=\"po-action-buttons\">
                        $tombolProgress
                        <button type=\"button\" class=\"btn btn-sm btn-primary\" title=\"Edit PO\" onclick=\"edit('" . sha1($row->nopo) . "')\"><i class=\"fa fa-edit\"></i></button>
                        <button type=\"button\" class=\"btn btn-sm btn-danger\" title=\"Hapus PO\" onclick=\"hapus('" . $row->nopo . "')\"><i class=\"fa fa-trash-alt\"></i></button>
                        </div>";
                    }
                    return "<div class=\"po-action-buttons\">$tombolProgress<button type=\"button\" class=\"btn btn-sm btn-primary\" title=\"Edit PO\" onclick=\"edit('" . sha1($row->nopo) . "')\"><i class=\"fa fa-edit\"></i></button></div>";
                })
                ->format('qty', function ($value) {
                    return number_format($value, 0, ',', '.');
                })
                ->format('hargapo', function ($value) {
                    return number_format($value, 0, ',', '.');
                })
                ->format('tglpo', function ($value) {
                    return date('d-m-Y', strtotime($value));
                })
                // ->filter(function ($builder, $request) {

                //     if ($request->tglpo)
                //         $builder->where('tglpo', $request->tglpo);
                // })
                ->toJson(true);
    }

    public function input()
    {
        return view('po/forminput', [
            'datapelanggan' => $this->modelPelanggan
                ->select('pelid, pelnama')
                ->whereNotIn('pelid', [1, 2])
                ->orderBy('pelnama', 'ASC')
                ->findAll(),
            'databarang' => (new Modelbarang())
                ->select('brgkode, brgnama')
                ->orderBy('brgkode', 'ASC')
                ->findAll(),
        ]);
    }

    public function import()
    {
        return view('po/import', [
            'mode' => 'upload',
        ]);
    }

    public function previewImport()
    {
        $file = $this->request->getFile('file_po');

        if (!$file || !$file->isValid()) {
            return redirect()->to('/po/import')->with('error', 'File PO belum dipilih atau tidak valid.');
        }

        if (strtolower($file->getExtension()) !== 'pdf') {
            return redirect()->to('/po/import')->with('error', 'File PO harus berformat PDF.');
        }

        try {
            $parser = new PoPdfParser();
            $parsed = $parser->parse($file->getTempName());
        } catch (\Throwable $e) {
            return redirect()->to('/po/import')->with('error', $e->getMessage());
        }

        $modelBarang = new Modelbarang();
        $modelPelanggan = new ModelPelanggan();

        $produk = $modelBarang
            ->select('brgkode, brgnama, harga')
            ->orderBy('brgnama', 'ASC')
            ->findAll();

        $pelanggan = $modelPelanggan
            ->whereNotIn('pelid', [1, 2])
            ->orderBy('pelnama', 'ASC')
            ->findAll();

        $items = [];
        foreach ($parsed['items'] as $item) {
            $matchedProduct = $this->matchImportedProduct($item, $produk);
            $items[] = array_merge($item, [
                'matched_brgkode' => $matchedProduct['brgkode'] ?? '',
                // Harga selalu diambil dari PDF PO, bukan dari master data,
                // supaya tetap sesuai PO meskipun produknya sudah pernah diinput
                // dengan harga yang berbeda (mis. harga lama/placeholder).
                'matched_harga' => $item['unit_price'],
            ]);
        }

        return view('po/import', [
            'mode' => 'preview',
            'parsed' => $parsed,
            'items' => $items,
            'produk' => $produk,
            'pelanggan' => $pelanggan,
            'matchedPelanggan' => $this->matchImportedPelanggan($parsed['pelanggan_hint'], $pelanggan),
        ]);
    }

    public function simpanImport()
    {
        $nopo = trim((string) $this->request->getPost('nopo'));
        $tglpo = $this->request->getPost('tglpo');
        $idpelanggan = $this->request->getPost('idpelanggan');
        $kodeBarangList = $this->request->getPost('kodebarang') ?? [];
        $qtyList = $this->request->getPost('qty') ?? [];
        $hargaList = $this->request->getPost('harga') ?? [];
        $terkirimAwalList = $this->request->getPost('terkirim_awal') ?? [];
        $invoiceAwalList = $this->request->getPost('invoice_awal') ?? [];

        $validation = \Config\Services::validation();
        $valid = $this->validate([
            'nopo' => [
                'rules' => 'required|is_unique[po.nopo]',
                'label' => 'No PO',
                'errors' => [
                    'required' => '{field} tidak boleh kosong',
                    'is_unique' => '{field} sudah terpakai',
                ],
            ],
            'tglpo' => [
                'rules' => 'required|valid_date[Y-m-d]',
                'label' => 'Tanggal PO',
            ],
            'idpelanggan' => [
                'rules' => 'required',
                'label' => 'Pelanggan',
            ],
        ]);

        if (!$valid) {
            return $this->redirectGagalImport(strip_tags($validation->listErrors()));
        }

        $detailRows = [];
        $totalQty = 0;
        $totalHarga = 0;
        $modelBarang = new Modelbarang();

        foreach ($kodeBarangList as $index => $kodeBarang) {
            $kodeBarang = trim((string) $kodeBarang);
            $qty = (float) str_replace(',', '.', (string) ($qtyList[$index] ?? 0));
            $harga = (float) str_replace(',', '.', (string) ($hargaList[$index] ?? 0));
            $terkirimAwal = (float) str_replace(',', '.', (string) ($terkirimAwalList[$index] ?? 0));
            $invoiceAwal = (float) str_replace(',', '.', (string) ($invoiceAwalList[$index] ?? 0));

            if ($kodeBarang === '' || $qty <= 0) {
                continue;
            }
            $terkirimAwal = max(0, min($terkirimAwal, $qty));
            $invoiceAwal = max(0, $invoiceAwal);

            $barang = $modelBarang->find($kodeBarang);
            if (!$barang) {
                continue;
            }

            $berat = $this->ambilBeratProduk($kodeBarang);
            if ($harga <= 0) {
                $harga = (float) ($barang['harga'] ?? 0);
            }
            $invoiceAwal = min($invoiceAwal, $terkirimAwal * $harga);

            $subtotalHarga = $qty * $harga;
            $totalQty += $qty;
            $totalHarga += $subtotalHarga;

            $detailRows[] = [
                'detnopo' => $nopo,
                'dettglpo' => $tglpo,
                'detkodebrg' => $kodeBarang,
                'namabarang' => $barang['brgnama'],
                'detberat' => $berat,
                'detqty' => $qty,
                'detkirim' => 0,
                'detkirim_awal' => $terkirimAwal,
                'detinvoice_awal' => $invoiceAwal,
                'detkurang' => max($qty - $terkirimAwal, 0),
                'detidpel' => $idpelanggan,
                'detsubtotal' => $qty * $berat,
                'detharga' => $subtotalHarga,
            ];
        }

        if (count($detailRows) === 0) {
            return $this->redirectGagalImport('Minimal satu item PO harus dipilih dan qty harus lebih dari 0.');
        }

        $db = db_connect();
        $db->transStart();

        (new Modelpo())->insert([
            'nopo' => $nopo,
            'tglpo' => $tglpo,
            'idpel' => $idpelanggan,
            'qty' => $totalQty,
            'hargapo' => $totalHarga,
            'is_migrasi' => ($this->request->getPost('po_migrasi') === '1' || array_sum(array_map('floatval', $terkirimAwalList)) > 0 || array_sum(array_map('floatval', $invoiceAwalList)) > 0) ? 1 : 0,
        ]);

        (new Modeldetailpo())->insertBatch($detailRows);
        (new Modeloutstand())->sinkronByPo($nopo);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->redirectGagalImport('Import PO gagal disimpan. Tidak ada data yang diubah.');
        }

        return redirect()->to('/po/data')->with('sukses', 'Import PO berhasil disimpan.');
    }

    private function redirectGagalImport(string $pesan)
    {
        return redirect()->to('/po/import')->withInput()->with('error', $pesan);
    }

    public function tampilDataTemp()
    {
        $nopo = $this->request->getPost('nopo');

        $modalTemppo = new Modeltemppo();
        $dataTemp = $modalTemppo->tampilDataTemp($nopo);
        $data = [
            'tampildata' => $dataTemp
        ];

        $json = [
            'data' => view('po/datatemp', $data),
            'jumlah' => $dataTemp->getNumRows(),
        ];

        return $this->response->setJSON($json);
    }

    public function hapusDraftPo()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $nopo = trim((string) $this->request->getPost('nopo'));
        if ($nopo === '') {
            return $this->response->setJSON(['error' => 'No PO tidak boleh kosong']);
        }

        (new Modeltemppo())->hapusData($nopo);
        return $this->response->setJSON(['sukses' => 'Draft lama untuk PO ini sudah dikosongkan']);
    }

    function ambilDataBarang()
    {
        if ($this->request->isAJAX()) {
            $kodebarang = $this->request->getPost('kodebarang');

            $modelBarang = new Modelbarang();
            $modelStok = new Modelstok();
            $modelBerat = new Modelberat();

            $cekDataBarang = $modelBarang->find($kodebarang);
            $cekDataBerat = $modelBerat->find($kodebarang);

            if ($cekDataBarang == null) {
                $json = [
                    'error' => 'Maaf, data barang tidak ditemukan'
                ];
            } elseif (($cekDataBerat == null || empty($cekDataBerat['berat'])) && !$this->produkTanpaBerat($kodebarang)) {
                $json = [
                    'error' => 'Maaf, berat barang belum diinput'
                ];
            } else {
                $stok = $modelStok->getTotalStokByKodeBarang($kodebarang);
                $harga = $modelStok->getHargaByKodeBarang($kodebarang);

                $data = [
                    'namabarang' => $cekDataBarang['brgnama'],
                    'stok' => $stok,
                    'harga' => $harga,
                    'berat' => $cekDataBerat['berat'] ?? 0
                ];

                $json = [
                    'sukses' => $data
                ];
            }
            return $this->response->setJSON($json);
        }
    }

    function listDataBarang()
    {
        $request = Services::request();
        $datamodel = new ModelDataStok($request);
        if ($request->getMethod(true) == 'POST') {
            $lists = $datamodel->get_datatables();
            $data = [];
            $no = $request->getPost("start");
            foreach ($lists as $list) {
                $no++;
                $row = [];

                $stokGudang1 = $datamodel->where('kodebarang', $list->kodebarang)->where('gudang', 1)->first();
                $stokGudang2 = $datamodel->where('kodebarang', $list->kodebarang)->where('gudang', 2)->first();

                $stokGudang1Value = isset($stokGudang1['stok']) ? $stokGudang1['stok'] : 0;
                $stokGudang2Value = isset($stokGudang2['stok']) ? $stokGudang2['stok'] : 0;
                $stokIdGudang1Value = isset($stokGudang1['id']) ? $stokGudang1['id'] : 0;
                $stokIdGudang2Value = isset($stokGudang2['id']) ? $stokGudang2['id'] : 0;

                $tombolPilih = "<button type=\"button\" class=\"btn btn-sm btn-info\" onclick=\"pilih('" . $list->kodebarang . "')\">Pilih</button>";

                $row[] = $no;
                $row[] = $list->kodebarang;
                $row[] = $list->namabarang;
                $row[] = '<input type="hidden" name="idgudang" value="' . $stokIdGudang1Value . '">' . number_format($stokGudang1Value, 0, ",", ".");
                $row[] = '<input type="hidden" name="idgudang" value="' . $stokIdGudang2Value . '">' . number_format($stokGudang2Value, 0, ",", ".");
                $row[] = $tombolPilih;
                $data[] = $row;
            }
            $output = [
                "draw" => $request->getPost('draw'),
                "recordsTotal" => $datamodel->count_all(),
                "recordsFiltered" => $datamodel->count_filtered(),
                "data" => $data
            ];
            return $this->response->setJSON($output);
        }
    }

    function simpanItem()
    {
        if ($this->request->isAJAX()) {
            $nopo = $this->request->getPost('nopo');
            $tglpo = $this->request->getPost('tglpo');
            $kodebarang = $this->request->getPost('kodebarang');
            $namabarang = $this->request->getPost('namabarang');
            $berat = $this->request->getPost('berat');
            $jml = $this->request->getPost('jml');
            $idpelanggan = $this->request->getPost('idpelanggan');
            $harga = $this->request->getPost('harga');
            $jmlFloat = max(0, (float) str_replace(',', '.', (string) $jml));
            $hargaFloat = max(0, (float) str_replace(',', '.', (string) $harga));
            $terkirimAwal = max(0, (float) str_replace(',', '.', (string) $this->request->getPost('terkirim_awal')));
            $terkirimAwal = min($terkirimAwal, $jmlFloat);
            $invoiceAwal = max(0, (float) str_replace(',', '.', (string) $this->request->getPost('invoice_awal')));
            $invoiceAwal = min($invoiceAwal, $terkirimAwal * $hargaFloat);

            $modelTemppo = new Modeltemppo();

            $validation = \Config\Services::validation();
            $valid = $this->validate([
                'nopo' => [
                    'rules' => 'required|is_unique[po.nopo]',
                    'label' => 'No PO',
                    'errors' => [
                        'required' => '{field} tidak boleh kosong',
                        'is_unique' => '{field} sudah terpakai'
                    ]
                ],
                'idpelanggan' => [
                    'rules' => 'required',
                    'label' => 'Data Pelanggan',
                    'errors' => [
                        'required' => '{field} Belum Tepilih',
                    ]
                ],
            ]);

            if (!$valid) {
                $json = [
                    'error1' => '' . $validation->listErrors() . ''
                ];
            } else {
                $existingItem = $modelTemppo
                    ->where('detnopo', $nopo)
                    ->where('detkodebrg', $kodebarang)
                    ->first();

                if ($existingItem) {
                    $newQty = (float) $existingItem['detqty'] + $jmlFloat;
                    $newTerkirimAwal = min(((float) ($existingItem['detkirim_awal'] ?? 0)) + $terkirimAwal, $newQty);
                    $newSubtotal = (float) $existingItem['detsubtotal'] + ($jmlFloat * (float) $berat);
                    $newSubharga = (float) $existingItem['detharga'] + ($jmlFloat * $hargaFloat);
                    $newUnitPrice = $newQty > 0 ? $newSubharga / $newQty : 0;
                    $newInvoiceAwal = min(((float) ($existingItem['detinvoice_awal'] ?? 0)) + $invoiceAwal, $newTerkirimAwal * $newUnitPrice);

                    $modelTemppo->update($existingItem['id'], [
                        'detqty' => $newQty,
                        'detkirim_awal' => $newTerkirimAwal,
                        'detinvoice_awal' => $newInvoiceAwal,
                        'detsubtotal' => $newSubtotal,
                        'detharga' => $newSubharga,
                    ]);
                } else {
                    $modelTemppo->insert([
                        'detnopo' => $nopo,
                        'dettglpo' => $tglpo,
                        'detkodebrg' => $kodebarang,
                        'namabarang' => $namabarang,
                        'detberat' => $berat,
                        'detqty' => $jml,
                        'detkirim_awal' => $terkirimAwal,
                        'detinvoice_awal' => $invoiceAwal,
                        'detidpel' => $idpelanggan,
                        'detsubtotal' => $jmlFloat * (float) $berat,
                        'detharga' => $jmlFloat * $hargaFloat
                    ]);
                }
                $json = ['sukses' => 'Item Berhasil di Tambahkan'];
            }
            return $this->response->setJSON($json);
        }
    }

    function selesaiTransaksi()
    {
        if ($this->request->isAJAX()) {
            $nopo = $this->request->getPost('nopo');
            $tglpo = $this->request->getPost('tglpo');
            $idpelanggan = $this->request->getPost('idpelanggan');
            $qty = $this->request->getPost('qty');
            $hargapo = $this->request->getPost('hargapo');
            $poMigrasi = $this->request->getPost('po_migrasi') === '1' ? 1 : 0;

            $modelTemp = new Modeltemppo();
            $dataTemp = $modelTemp->getWhere(['detnopo' => $nopo]);

            if ($dataTemp->getNumRows() == 0) {
                $json = [
                    'error' => 'Maaf, data item untuk po ini belum ada'
                ];
            } else {
                $db = db_connect();
                $db->transStart();

                $modelpo = new Modelpo();
                $totalSubQty = 0;
                $totalSubharga = 0;
                foreach ($dataTemp->getResultArray() as $total) :
                    $totalSubQty += intval($total['detqty']);
                    $totalSubharga += intval($total['detharga']);
                endforeach;

                $modelpo->insert([
                    'nopo' => $nopo,
                    'tglpo' => $tglpo,
                    'idpel' => $idpelanggan,
                    'qty' => $qty,
                    'hargapo' => $hargapo,
                    'is_migrasi' => $poMigrasi,
                ]);

                $fieldDetail = [];
                foreach ($dataTemp->getResultArray() as $row) {
                    $fieldDetail[] = [
                        'detnopo' => $row['detnopo'],
                        'dettglpo' => $row['dettglpo'],
                        'detkodebrg' => $row['detkodebrg'],
                        'namabarang' => $row['namabarang'],
                        'detberat' => $row['detberat'],
                        'detqty' => $row['detqty'],
                        'detkirim' => 0,
                        'detkirim_awal' => (float) ($row['detkirim_awal'] ?? 0),
                        'detinvoice_awal' => (float) ($row['detinvoice_awal'] ?? 0),
                        'detkurang' => max((float) $row['detqty'] - (float) ($row['detkirim_awal'] ?? 0), 0),
                        'detidpel' => $row['detidpel'],
                        'detsubtotal' => $row['detsubtotal'],
                        'detharga' => $row['detharga'],
                    ];
                }

                $modelDetail = new Modeldetailpo();
                $modelDetail->insertBatch($fieldDetail);

                $fieldDetailOutstanding = [];
                foreach ($dataTemp->getResultArray() as $row) {
                    $fieldDetailOutstanding[] = [
                        'nopo' => $row['detnopo'],
                        'tgl' => $row['dettglpo'],
                        'kodebrg' => $row['detkodebrg'],
                        'idbarang' => $row['idbarang'],
                        'qty' => $row['detqty'],
                        'terkirim' => (float) ($row['detkirim_awal'] ?? 0),
                        'kekurangan' => max((float) $row['detqty'] - (float) ($row['detkirim_awal'] ?? 0), 0),
                        'idpel' => $row['detidpel'],
                    ];
                }

                $modelDetailOutstanding = new Modeloutstand();
                $modelDetailOutstanding->insertBatch($fieldDetailOutstanding);
                $modelDetailOutstanding->updateDetailOutstandingKurang();

                $modelTemp->hapusData($nopo);
                $db->transComplete();

                $json = $db->transStatus() === false
                    ? ['error' => 'Transaksi PO gagal disimpan. Tidak ada data yang diubah.']
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

            return $this->response->setJSON($json);
        } else {
            exit('Maaf tidak bisa dipanggil');
        }
    }

    function hapusItem()
    {
        if ($this->request->isAJAX()) {
            $id = $this->request->getPost('id');

            $modelTemppo = new Modeltemppo();
            $modelTemppo->delete($id);

            $json = [
                'sukses' => 'Item Berhasil di Hapus'
            ];
            return $this->response->setJSON($json);
        }
    }

    public function modalCariBarang()
    {
        if ($this->request->isAJAX()) {
            $json = [
                'data' => view('po/modalcaribarang')
            ];
            return $this->response->setJSON($json);
        }
    }

    function modalPo()
    {
        $nopo = $this->request->getPost('nopo');
        $tglpo = $this->request->getPost('tglpo');
        $idpelanggan = $this->request->getPost('idpelanggan');
        $totalberatbarang = $this->request->getPost('totalberatbarang');

        $modelTemp = new Modeltemppo();
        $cekdata = $modelTemp->tampilDataTemp($nopo);

        if ($cekdata->getNumrows() > 0) {
            $data = [
                'nopo' => $nopo,
                'totalberatbarang' => $totalberatbarang,
                'tglpo' => $tglpo,
                'idpelanggan' => $idpelanggan,
            ];

            $json = [
                'data' => view('po/modalbarangpo', $data)
            ];
        } else {
            $json = [
                'error' => 'Maaf item belum ada'
            ];
        }
        return $this->response->setJSON($json);
    }

    function hapusTransaksi()
    {
        if ($this->request->isAJAX()) {
            $nopo = $this->request->getPost('nopo');

            $modelpo = new Modelpo();
            $modeloutstanding = new Modeloutstand();

            $db = \Config\Database::connect();

            $cekBarangKeluar = $db->table('barangkeluar')->where('detpo', $nopo)->countAllResults();

            if ($cekBarangKeluar > 0) {
                $json = [
                    'error' => 'Data tidak bisa dihapus karena masih terkait dengan data di tabel Pengiriman'
                ];
            } else {
                $db->transStart();
                // Bersihkan riwayat terkirim yang dokumen Produk Keluarnya sudah dihapus.
                $db->table('rencana_pengiriman')
                    ->where('no_po', $nopo)
                    ->where('status', 1)
                    ->where('NOT EXISTS (SELECT 1 FROM detail_barangkeluar dk WHERE dk.detfaktur = rencana_pengiriman.no_do AND dk.detpo = rencana_pengiriman.no_po AND dk.detbrgkode = rencana_pengiriman.kode_produk)', null, false)
                    ->delete();
                $db->table('detail_po')->delete(['detnopo' => $nopo]);
                $modelpo->delete($nopo);
                $db->table('outstanding')->delete(['nopo' => $nopo]);
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
                    ? ['error' => 'Transaksi PO gagal dihapus. Tidak ada data yang diubah.']
                    : ['sukses' => 'Transaksi berhasil di Hapus'];
            }

            return $this->response->setJSON($json);
        }
    }

    public function edit($po)
    {
        $modelpo = new Modelpo();
        $cekPo = $modelpo->cekPo($po);
        if ($cekPo->getNumRows() > 0) {
            $row = $cekPo->getRowArray();
            $lockReasons = $this->poMasukEditLockReasons($row['nopo']);
            $data = [
                'nopo' => $row['nopo'],
                'tanggal' => $row['tglpo'],
                'namapelanggan' => $row['pelnama'],
                'idpelanggan' => $row['idpel'],
                'poLocked' => !empty($lockReasons),
                'poLockReasons' => $lockReasons,
                'datapelanggan' => $this->modelPelanggan
                    ->whereNotIn('pelid', [1, 2])
                    ->orderBy('pelnama', 'ASC')
                    ->findAll(),
                'databarang' => (new Modelbarang())
                    ->select('brgkode, brgnama')
                    ->orderBy('brgkode', 'ASC')
                    ->findAll(),
            ];
            return view('po/formedit', $data);
        } else {
            exit('Data tidak ditemukan');
        }
    }

    public function progress($po)
    {
        $modelpo = new Modelpo();
        $cekPo = $modelpo->cekPo($po);
        if ($cekPo->getNumRows() === 0) {
            exit('Data tidak ditemukan');
        }
        $row = $cekPo->getRowArray();
        return view('po/progress', [
            'nopo' => $row['nopo'],
            'tanggal' => $row['tglpo'],
            'namapelanggan' => $row['pelnama'],
            'progress' => $this->hitungProgressPo($row['nopo']),
        ]);
    }

    /**
     * Progress PO Masuk dalam 5 tahap: PO dibuat, material siap (kalau ada
     * pembelian lewat PO Keluar yang dikait-in), dikirim ke pelanggan,
     * ditagih, lunas. Dihitung langsung dari data transaksi yang sudah ada
     * (bukan kolom status tersendiri) supaya selalu sinkron dan tidak perlu
     * step tambahan buat "menutup" PO secara manual.
     */
    private function hitungProgressPo(string $nopo): array
    {
        $poKeluarRows = $this->db->table('po_keluar pk')
            ->select('pk.no_po, pk.supplier_nama, pk.tgl_po, pk.kirim_langsung,
                COALESCE(SUM(dpk.qty_pesan), 0) AS total_pesan, COALESCE(SUM(dpk.qty_masuk), 0) AS total_masuk')
            ->join('detail_po_keluar dpk', 'dpk.po_keluar_id = pk.id', 'left')
            ->where('pk.po_masuk_terkait', $nopo)
            ->groupBy('pk.id')
            ->orderBy('pk.tgl_po', 'ASC')
            ->get()->getResultArray();

        $materialList = [];
        $materialSelesai = 0;
        $materialAdaProgress = false;
        foreach ($poKeluarRows as $row) {
            if ((int) $row['kirim_langsung'] === 1) {
                $status = 'Kirim Langsung';
            } elseif ((float) $row['total_masuk'] <= 0) {
                $status = 'Belum Diterima';
            } elseif ((float) $row['total_masuk'] >= (float) $row['total_pesan']) {
                $status = 'Diterima Lengkap';
                $materialSelesai++;
            } else {
                $status = 'Diterima Sebagian';
                $materialAdaProgress = true;
            }
            if ($status === 'Kirim Langsung') {
                $materialSelesai++;
            }
            if ((float) $row['total_masuk'] > 0) {
                $materialAdaProgress = true;
            }
            $materialList[] = [
                'no_po' => $row['no_po'],
                'supplier_nama' => $row['supplier_nama'],
                'tgl_po' => $row['tgl_po'],
                'status' => $status,
            ];
        }

        if (!$materialList) {
            $materialStatus = 'tidak_ada';
        } elseif ($materialSelesai === count($materialList)) {
            $materialStatus = 'selesai';
        } elseif ($materialAdaProgress) {
            $materialStatus = 'sebagian';
        } else {
            $materialStatus = 'belum';
        }

        $totalKirim = $this->db->table('detail_po')
            ->selectSum('detqty', 'total_qty')
            ->select('SUM(COALESCE(detkirim_awal, 0) + COALESCE(detkirim, 0)) AS total_kirim', false)
            ->where('detnopo', $nopo)
            ->get()->getRowArray();
        $totalQty = (float) ($totalKirim['total_qty'] ?? 0);
        $totalTerkirim = (float) ($totalKirim['total_kirim'] ?? 0);
        $persenKirim = $totalQty > 0 ? min(100, round($totalTerkirim / $totalQty * 100)) : 0;

        if ($totalTerkirim <= 0) {
            $kirimStatus = 'belum';
        } elseif ($totalTerkirim >= $totalQty && $totalQty > 0) {
            $kirimStatus = 'selesai';
        } else {
            $kirimStatus = 'sebagian';
        }

        $shipments = $this->db->table('barangkeluar')
            ->select('faktur, tglfaktur, qtykeluar')
            ->where('detpo', $nopo)
            ->orderBy('tglfaktur', 'ASC')
            ->orderBy('faktur', 'ASC')
            ->get()->getResultArray();

        $this->ensureBtbFileColumnsPo();
        foreach ($shipments as &$shipment) {
            $dokumenRows = $this->db->table('rencana_pengiriman')
                ->select('id, no_btb, btb_file, btb_original_name')
                ->where('no_do', $shipment['faktur'])
                ->where('no_po', $nopo)
                ->where('status', 1)
                ->get()->getResultArray();

            // 1 no_do sekarang bisa punya lebih dari 1 item, dan tiap item
            // bisa diisi BTB berbeda-beda -- di-dedupe biar BTB yang sama
            // (dipakai bareng banyak item) gak muncul berkali-kali.
            $dokumenUnik = [];
            foreach ($dokumenRows as $dok) {
                if (($dok['no_btb'] ?? '') === '' && empty($dok['btb_file'])) {
                    continue;
                }

                $kunci = ($dok['no_btb'] ?? '') . '|' . ($dok['btb_file'] ?? '');
                if (isset($dokumenUnik[$kunci])) {
                    continue;
                }

                $dokumenUnik[$kunci] = [
                    'no_btb' => $dok['no_btb'] ?? '',
                    'btb_original_name' => $dok['btb_original_name'] ?? '',
                    'btb_file_url' => !empty($dok['btb_file']) ? site_url('barangkeluar/file-btb/' . $dok['id']) : '',
                ];
            }

            $shipment['btb_list'] = array_values($dokumenUnik);
        }
        unset($shipment);

        $invoices = $this->db->table('invoice_out')
            ->select('id, invoice_no, invoice_date, grand_total, status, status_bayar')
            ->where('po_no', $nopo)
            ->where('status', 'AKTIF')
            ->orderBy('invoice_date', 'ASC')
            ->get()->getResultArray();

        $tagihStatus = $invoices ? 'sudah' : 'belum';
        $lunasStatus = 'belum';
        if ($invoices) {
            $semuaLunas = true;
            foreach ($invoices as $invoice) {
                if (($invoice['status_bayar'] ?? 'Belum Lunas') !== 'Lunas') {
                    $semuaLunas = false;
                    break;
                }
            }
            $lunasStatus = $semuaLunas ? 'lunas' : 'belum';
        }

        $s2Done = in_array($materialStatus, ['tidak_ada', 'selesai'], true);
        $s3Done = $kirimStatus === 'selesai';
        $s4Done = $tagihStatus === 'sudah';
        $s5Done = $lunasStatus === 'lunas';

        $stateUntuk = function (bool $done, bool $isFirstBelumDone) {
            if ($done) {
                return 'done';
            }
            return $isFirstBelumDone ? 'active' : 'pending';
        };

        $doneFlags = [true, $s2Done, $s3Done, $s4Done, $s5Done];
        $firstBelumDoneIndex = array_search(false, $doneFlags, true);

        $badgeMaterial = [
            'tidak_ada' => 'Material Sudah Dibeli',
            'belum' => 'Dipesan, Belum Datang',
            'sebagian' => 'Diterima Sebagian',
            'selesai' => 'Diterima Lengkap',
        ][$materialStatus];

        $badgeKirim = [
            'belum' => 'Belum Dikirim',
            'sebagian' => 'Dikirim Sebagian ' . $persenKirim . '%',
            'selesai' => 'Dikirim Lengkap',
        ][$kirimStatus];

        $badgeTagih = $tagihStatus === 'sudah' ? 'Sudah Ditagih' : 'Belum Ditagih';
        $badgeLunas = $lunasStatus === 'lunas' ? 'Lunas' : 'Belum Lunas';

        return [
            'steps' => [
                ['key' => 'dibuat', 'label' => 'PO dibuat', 'state' => 'done', 'badge' => 'Dibuat'],
                ['key' => 'material', 'label' => 'Material siap', 'state' => $stateUntuk($s2Done, $firstBelumDoneIndex === 1), 'badge' => $badgeMaterial],
                ['key' => 'kirim', 'label' => 'Dikirim', 'state' => $stateUntuk($s3Done, $firstBelumDoneIndex === 2), 'badge' => $badgeKirim],
                ['key' => 'tagih', 'label' => 'Ditagih', 'state' => $stateUntuk($s4Done, $firstBelumDoneIndex === 3), 'badge' => $badgeTagih],
                ['key' => 'lunas', 'label' => 'Lunas', 'state' => $stateUntuk($s5Done, $firstBelumDoneIndex === 4), 'badge' => $badgeLunas],
            ],
            'material' => ['status' => $materialStatus, 'badge' => $badgeMaterial, 'list' => $materialList],
            'kirim' => ['status' => $kirimStatus, 'badge' => $badgeKirim, 'persen' => $persenKirim, 'total_qty' => $totalQty, 'total_kirim' => $totalTerkirim, 'shipments' => $shipments],
            'tagih' => ['status' => $tagihStatus, 'badge' => $badgeTagih, 'lunas_status' => $lunasStatus, 'lunas_badge' => $badgeLunas, 'invoices' => $invoices],
            'closing' => $this->ambilRiwayatClosingPo($nopo),
        ];
    }

    /**
     * Riwayat Close Item/PO -- dilihat dari 2 sisi: item yang DITUTUP dari PO
     * ini (dipindah keluar) dan qty yang DITERIMA PO ini karena penutupan
     * dari PO lain, biar bisa dilacak dari kedua PO yang terlibat.
     */
    private function ambilRiwayatClosingPo(string $nopo): array
    {
        if (!$this->db->tableExists('po_close_log')) {
            return ['keluar' => [], 'masuk' => []];
        }

        $keluar = $this->db->table('po_close_log')
            ->where('nopo_asal', $nopo)
            ->orderBy('ditutup_pada', 'DESC')
            ->get()->getResultArray();

        $masuk = $this->db->table('po_close_log')
            ->where('nopo_tujuan', $nopo)
            ->orderBy('ditutup_pada', 'DESC')
            ->get()->getResultArray();

        return ['keluar' => $keluar, 'masuk' => $masuk];
    }

    private function ambilRiwayatKoreksiQtyPo(string $nopo): array
    {
        if (!$this->db->tableExists('po_qty_adjust_log')) {
            return [];
        }

        return $this->db->table('po_qty_adjust_log')
            ->where('nopo', $nopo)
            ->orderBy('dibuat_pada', 'DESC')
            ->get()->getResultArray();
    }

    private function progressTerakhirPo(array $progress): array
    {
        if (($progress['tagih']['lunas_status'] ?? '') === 'lunas') {
            return ['key' => 'lunas', 'label' => $progress['tagih']['lunas_badge'], 'class' => 'success'];
        }

        if (($progress['tagih']['status'] ?? '') === 'sudah') {
            return ['key' => 'belum_lunas', 'label' => $progress['tagih']['lunas_badge'], 'class' => 'warning'];
        }

        if (($progress['kirim']['status'] ?? '') === 'selesai') {
            return ['key' => 'belum_tagih', 'label' => $progress['tagih']['badge'], 'class' => 'secondary'];
        }

        if (($progress['kirim']['status'] ?? '') === 'sebagian') {
            return ['key' => 'kirim_sebagian', 'label' => $progress['kirim']['badge'], 'class' => 'warning'];
        }

        if (($progress['material']['status'] ?? '') === 'belum') {
            return ['key' => 'material_belum', 'label' => $progress['material']['badge'], 'class' => 'warning'];
        }

        if (($progress['material']['status'] ?? '') === 'sebagian') {
            return ['key' => 'material_sebagian', 'label' => $progress['material']['badge'], 'class' => 'warning'];
        }

        return ['key' => 'belum_kirim', 'label' => $progress['kirim']['badge'], 'class' => 'secondary'];
    }

    /**
     * Sama seperti Barangkeluar::ensureBtbFileColumns(), dobel di sini biar
     * controller ini gak bergantung urutan controller mana yang kepanggil
     * duluan.
     */
    private function ensureBtbFileColumnsPo(): void
    {
        $db = \Config\Database::connect();
        $forge = \Config\Database::forge();

        if (!$db->fieldExists('btb_file', 'rencana_pengiriman')) {
            $forge->addColumn('rencana_pengiriman', [
                'btb_file' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                    'after' => 'no_btb',
                ],
            ]);
        }

        if (!$db->fieldExists('btb_original_name', 'rencana_pengiriman')) {
            $forge->addColumn('rencana_pengiriman', [
                'btb_original_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                    'after' => 'btb_file',
                ],
            ]);
        }
    }

    function ambilTotalBerat()
    {
        if ($this->request->isAJAX()) {
            $nopo = $this->request->getPost('nopo');
            $modelDetail = new Modeldetailpo();
            $totalBerat = $modelDetail->ambilTotalBerat($nopo);

            $json = [
                'totalberat' => "Total : " . number_format($totalBerat, 0, ",", ".") . " " . "Pcs"
            ];
            return $this->response->setJSON($json);
        }
    }

    function ambilTotalHarga()
    {
        if ($this->request->isAJAX()) {
            $nopo = $this->request->getPost('nopo');
            $modelDetail = new Modeldetailpo();
            $totalharga = $modelDetail->ambilTotalharga($nopo);

            $json = [
                'totalharga' => "Harga : " . number_format($totalharga, 0, ",", ".") . " " . ""
            ];
            return $this->response->setJSON($json);
        }
    }

    function tampilDataDetail()
    {
        if ($this->request->isAJAX()) {
            $nopo = $this->request->getPost('nopo');

            $modelDetail = new Modeldetailpo();
            $dataTemp = $modelDetail->tampilDataTemp($nopo);
            $data = [
                'tampildata' => $dataTemp,
                'poLocked' => !empty($this->poMasukEditLockReasons($nopo)),
                'closing' => $this->ambilRiwayatClosingPo($nopo),
            ];

            $json = [
                'data' => view('po/datadetail', $data)
            ];
            return $this->response->setJSON($json);
        }
    }

    function hapusItemDetail()
    {
        if ($this->request->isAJAX()) {
            $id = $this->request->getPost('id');

            $modelDetail = new Modeldetailpo();
            $modelpo = new Modelpo();
            $modeloutstanding = new Modeloutstand();

            $rowData = $modelDetail->find($id);
            if (!$rowData) {
                return $this->response->setJSON(['error' => 'Item PO tidak ditemukan']);
            }

            $nopo = $rowData['detnopo'];
            if ($this->poMasukSudahDipakai($nopo)) {
                return $this->response->setJSON(['error' => 'PO sudah dipakai transaksi lanjutan, item tidak bisa dihapus.']);
            }

            $kodebrg = $rowData['detkodebrg'];

            $db = db_connect();
            $db->transStart();

            $modelDetail->delete($id);
            $modeloutstanding->where(['nopo' => $nopo, 'kodebrg' => $kodebrg])->delete();

            $totalBerat = $modelDetail->ambilTotalBerat($nopo);
            $totalharga = $modelDetail->ambilTotalharga($nopo);

            $modelpo->update($nopo, [
                'qty' => $totalBerat,
                'hargapo' => $totalharga
            ]);
            $modeloutstanding->sinkronByPo($nopo);
            $db->transComplete();
            // update outstanding

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
                ? ['error' => 'Item PO gagal dihapus. Tidak ada data yang diubah.']
                : ['sukses' => 'Item Berhasil di Hapus'];
            return $this->response->setJSON($json);
        }
    }

    /**
     * Daftar PO Masuk lain milik pelanggan yang sama, buat dropdown "PO
     * Tujuan" pas Close Item/PO -- sengaja TIDAK difilter harus sudah punya
     * baris item yang sama (beda dari pola poTujuanSplit() di
     * PermintaanPengiriman.php), soalnya kalau item belum ada di PO tujuan
     * baris barunya tinggal dibikin baru.
     */
    /**
     * Daftar PO Masuk lain milik pelanggan yang sama yang SUDAH punya item
     * (kode_produk) yang sama -- dipakai buat dropdown "PO Tujuan" pas Close
     * Item/PO Metode 1 (catatan). Beda dari versi lama: sekarang wajib udah
     * ada baris itemnya, karena Metode 1 sekarang cuma nempelin catatan,
     * bukan bikin baris baru atau nambah qty PO tujuan.
     */
    public function daftarPoTujuanClose()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $idpelanggan = (int) $this->request->getPost('idpelanggan');
        $noPoKecuali = trim((string) $this->request->getPost('no_po_kecuali'));
        $kodeProduk = trim((string) $this->request->getPost('kode_produk'));

        if ($idpelanggan <= 0 || $kodeProduk === '') {
            return $this->response->setJSON(['data' => []]);
        }

        $rows = $this->db->table('detail_po dp')
            ->select('dp.detnopo AS nopo, po.tglpo')
            ->join('po', 'po.nopo = dp.detnopo')
            ->where('dp.detkodebrg', $kodeProduk)
            ->where('po.idpel', $idpelanggan)
            ->where('dp.detnopo !=', $noPoKecuali)
            ->groupBy('dp.detnopo, po.tglpo')
            ->orderBy('po.tglpo', 'DESC')
            ->get()->getResultArray();

        return $this->response->setJSON(['data' => $rows]);
    }

    /**
     * Close 1 baris detail_po -- sisa qty (yang belum dikirim) dipecah jadi
     * beberapa alokasi, tiap alokasi salah satu dari:
     *  - Metode 1 (catatan): $alokasi['nopo_tujuan'] diisi PO lain milik
     *    pelanggan yang sama yang SUDAH punya item ini. Qty/nilai PO tujuan
     *    SAMA SEKALI TIDAK DIUBAH -- cuma dicatat di po_close_log sebagai
     *    penjelasan "sekian qty dari baris ini sebenarnya bagian dari PO
     *    lain", biar nggak keliatan seolah2 PO tujuan nambah pesanan
     *    padahal pelanggan nggak pernah minta segitu.
     *  - Metode 2 (sesuaikan): $alokasi['nopo_tujuan'] kosong, dipakai kalau
     *    qty PO ini emang salah input dari awal -- qty itu cuma "dihapus"
     *    dari PO asal, nggak nyambung kemana-mana.
     * Total qty semua alokasi BOLEH lebih kecil dari sisa qty baris ini
     * (nutup sebagian aja) -- sisanya tetap outstanding seperti biasa di PO
     * ini, nggak wajib dihabiskan sekaligus. Baris detail_po ASAL dikurangi
     * qty & nilainya (proporsional, biar harga satuan tetap konsisten)
     * sejumlah yang BENERAN dialokasikan -- baris PO manapun yang jadi
     * tujuan Metode 1 TIDAK PERNAH disentuh sama sekali.
     *
     * @param array<int, array{qty: float, nopo_tujuan: ?string}> $alokasi
     * @throws \RuntimeException kalau validasi gagal
     */
    private function tutupItemPo(array $detailAsal, array $alokasi, Modeldetailpo $modelDetail, Modelpo $modelPo, ModelPoCloseLog $modelLog): array
    {
        $qtySisa = max((float) $detailAsal['detqty'] - (float) ($detailAsal['detkirim_awal'] ?? 0) - (float) ($detailAsal['detkirim'] ?? 0), 0);
        if ($qtySisa <= 0) {
            throw new \RuntimeException('Item ' . $detailAsal['detkodebrg'] . ' sudah tidak ada sisa qty yang bisa ditutup.');
        }

        if (!$alokasi) {
            throw new \RuntimeException('Item ' . $detailAsal['detkodebrg'] . ' belum ada rincian penutupan.');
        }

        $totalAlokasi = 0.0;
        foreach ($alokasi as $baris) {
            $qty = (float) ($baris['qty'] ?? 0);
            if ($qty <= 0) {
                throw new \RuntimeException('Semua qty rincian penutupan item ' . $detailAsal['detkodebrg'] . ' harus lebih dari 0.');
            }
            $totalAlokasi += $qty;
        }

        if ($totalAlokasi - $qtySisa > 0.0001) {
            throw new \RuntimeException('Total rincian penutupan item ' . $detailAsal['detkodebrg'] . ' (' . $totalAlokasi . ') tidak boleh lebih dari sisa qty (' . $qtySisa . ').');
        }

        $detqtyAsal = (float) $detailAsal['detqty'];
        $unitPrice = $detqtyAsal > 0 ? (float) $detailAsal['detharga'] / $detqtyAsal : 0.0;

        $catatanTujuan = [];
        foreach ($alokasi as $baris) {
            $qty = (float) $baris['qty'];
            $nopoTujuan = trim((string) ($baris['nopo_tujuan'] ?? ''));

            if ($nopoTujuan === '') {
                // Metode 2: sesuaikan, tidak nyambung kemana-mana.
                $modelLog->insert([
                    'nopo_asal' => $detailAsal['detnopo'],
                    'kodebrg' => $detailAsal['detkodebrg'],
                    'namabarang' => $detailAsal['namabarang'],
                    'qty_dipindah' => $qty,
                    'harga_satuan' => $unitPrice,
                    'nopo_tujuan' => null,
                    'ditutup_oleh' => session()->get('namauser'),
                    'ditutup_pada' => date('Y-m-d H:i:s'),
                ]);
                continue;
            }

            if ($nopoTujuan === $detailAsal['detnopo']) {
                throw new \RuntimeException('PO tujuan untuk item ' . $detailAsal['detkodebrg'] . ' tidak boleh sama dengan PO asal.');
            }

            $poTujuan = $modelPo->find($nopoTujuan);
            if (!$poTujuan) {
                throw new \RuntimeException('PO tujuan ' . $nopoTujuan . ' tidak ditemukan.');
            }
            if ((int) $poTujuan['idpel'] !== (int) $detailAsal['detidpel']) {
                throw new \RuntimeException('PO tujuan ' . $nopoTujuan . ' bukan milik pelanggan yang sama.');
            }

            $adaItemDiTujuan = $modelDetail
                ->where('detnopo', $nopoTujuan)
                ->where('detkodebrg', $detailAsal['detkodebrg'])
                ->countAllResults() > 0;
            if (!$adaItemDiTujuan) {
                throw new \RuntimeException('PO tujuan ' . $nopoTujuan . ' belum punya item ' . $detailAsal['detkodebrg'] . '.');
            }

            $modelLog->insert([
                'nopo_asal' => $detailAsal['detnopo'],
                'kodebrg' => $detailAsal['detkodebrg'],
                'namabarang' => $detailAsal['namabarang'],
                'qty_dipindah' => $qty,
                'harga_satuan' => $unitPrice,
                'nopo_tujuan' => $nopoTujuan,
                'ditutup_oleh' => session()->get('namauser'),
                'ditutup_pada' => date('Y-m-d H:i:s'),
            ]);
            $catatanTujuan[$nopoTujuan] = true;
        }

        $detberat = (float) ($detailAsal['detberat'] ?? 0);
        $newDetqtyAsal = $detqtyAsal - $totalAlokasi;
        $modelDetail->update($detailAsal['id'], [
            'detqty' => $newDetqtyAsal,
            'detharga' => (float) $detailAsal['detharga'] - ($totalAlokasi * $unitPrice),
            'detsubtotal' => $newDetqtyAsal * $detberat,
        ]);

        return [
            'kodebrg' => $detailAsal['detkodebrg'],
            'qty' => $totalAlokasi,
            'sisa_belum_dialokasikan' => max($qtySisa - $totalAlokasi, 0),
            'nopo_tujuan_list' => array_keys($catatanTujuan),
        ];
    }

    /**
     * Koreksi qty item PO Masuk yang sudah terkunci. Transaksi lama tidak
     * diubah; qty PO disesuaikan selama tidak lebih kecil dari qty terkirim.
     */
    public function koreksiQtyItem()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $id = (int) $this->request->getPost('id');
        $qtyBaru = max(0, (float) str_replace(',', '.', (string) $this->request->getPost('qty_baru')));
        $alasan = trim((string) $this->request->getPost('alasan'));

        if ($id <= 0) {
            return $this->response->setJSON(['error' => 'Item PO tidak valid.']);
        }
        if ($qtyBaru <= 0) {
            return $this->response->setJSON(['error' => 'Qty baru harus lebih dari 0.']);
        }
        if ($alasan === '') {
            return $this->response->setJSON(['error' => 'Alasan koreksi qty wajib diisi.']);
        }

        $modelDetail = new Modeldetailpo();
        $modelOutstanding = new Modeloutstand();
        $detail = $modelDetail->find($id);

        if (!$detail) {
            return $this->response->setJSON(['error' => 'Item PO tidak ditemukan.']);
        }

        $qtyLama = (float) $detail['detqty'];
        if (abs($qtyBaru - $qtyLama) < 0.0001) {
            return $this->response->setJSON(['error' => 'Qty baru masih sama dengan qty lama.']);
        }

        $qtyTerkirim = (float) ($detail['detkirim_awal'] ?? 0) + (float) ($detail['detkirim'] ?? 0);
        if ($qtyBaru + 0.0001 < $qtyTerkirim) {
            return $this->response->setJSON([
                'error' => 'Qty baru tidak boleh lebih kecil dari qty yang sudah terkirim (' . number_format($qtyTerkirim, 0, ',', '.') . ' pcs).',
            ]);
        }

        $hargaLama = (float) ($detail['detharga'] ?? 0);
        $hargaSatuan = $qtyLama > 0 ? $hargaLama / $qtyLama : 0.0;
        $hargaBaru = $qtyBaru * $hargaSatuan;
        $detberat = (float) ($detail['detberat'] ?? 0);

        $db = db_connect();
        $db->transStart();

        $modelDetail->update($id, [
            'detqty' => $qtyBaru,
            'detkurang' => max($qtyBaru - $qtyTerkirim, 0),
            'detsubtotal' => $qtyBaru * $detberat,
            'detharga' => $hargaBaru,
        ]);

        $db->table('po_qty_adjust_log')->insert([
            'nopo' => $detail['detnopo'],
            'detail_po_id' => $id,
            'kodebrg' => $detail['detkodebrg'],
            'namabarang' => $detail['namabarang'],
            'qty_sebelum' => $qtyLama,
            'qty_sesudah' => $qtyBaru,
            'qty_delta' => $qtyBaru - $qtyLama,
            'harga_satuan' => $hargaSatuan,
            'harga_sebelum' => $hargaLama,
            'harga_sesudah' => $hargaBaru,
            'alasan' => $alasan,
            'dibuat_oleh' => session()->get('namauser'),
            'dibuat_pada' => date('Y-m-d H:i:s'),
        ]);

        $modelOutstanding->sinkronByPo($detail['detnopo']);
        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['error' => 'Qty PO gagal dikoreksi. Tidak ada data yang diubah.']);
        }

        return $this->response->setJSON([
            'sukses' => 'Qty item ' . $detail['detkodebrg'] . ' berhasil dikoreksi dari ' . number_format($qtyLama, 0, ',', '.') . ' pcs menjadi ' . number_format($qtyBaru, 0, ',', '.') . ' pcs.',
        ]);
    }

    /**
     * Close 1 item (1 baris detail_po) -- sengaja TIDAK dicek
     * poMasukSudahDipakai(), sama kayak updateTanggal(): closing dianggap
     * operasi aman dilakukan kapan pun walau PO udah "terkunci" transaksi
     * lanjutan, karena cuma mindahin sisa qty yang belum dikirim.
     *
     * POST alokasi[] = array of {qty, nopo_tujuan} (nopo_tujuan boleh kosong
     * buat Metode 2/sesuaikan).
     */
    public function closeItem()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $id = (int) $this->request->getPost('id');
        $alokasi = (array) $this->request->getPost('alokasi');

        $modelDetail = new Modeldetailpo();
        $modelPo = new Modelpo();
        $modelOutstanding = new Modeloutstand();
        $modelLog = new ModelPoCloseLog();

        $detailAsal = $modelDetail->find($id);
        if (!$detailAsal) {
            return $this->response->setJSON(['error' => 'Item PO tidak ditemukan.']);
        }

        $db = db_connect();
        $db->transStart();

        try {
            $hasil = $this->tutupItemPo($detailAsal, $alokasi, $modelDetail, $modelPo, $modelLog);
        } catch (\RuntimeException $e) {
            $db->transRollback();
            return $this->response->setJSON(['error' => $e->getMessage()]);
        }

        $modelOutstanding->sinkronByPo($detailAsal['detnopo']);
        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['error' => 'Item PO gagal ditutup. Tidak ada data yang diubah.']);
        }

        $keterangan = $hasil['nopo_tujuan_list']
            ? ', dicatat sebagian ke PO ' . implode(', ', $hasil['nopo_tujuan_list'])
            : '';
        $sisaBelum = $hasil['sisa_belum_dialokasikan'] > 0.0001
            ? ' Sisa ' . number_format($hasil['sisa_belum_dialokasikan'], 0, ',', '.') . ' pcs masih terbuka di PO ini.'
            : '';
        return $this->response->setJSON([
            'sukses' => 'Item ' . $hasil['kodebrg'] . ' -- ' . number_format($hasil['qty'], 0, ',', '.') . ' pcs berhasil ditutup' . $keterangan . '.' . $sisaBelum,
        ]);
    }

    /**
     * Buka kembali close item/PO. Tidak menghapus transaksi lama; hanya
     * mengembalikan sisa qty yang sebelumnya ditutup ke item PO asal.
     */
    public function reopenCloseLog()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $id = (int) $this->request->getPost('id');
        $note = trim((string) $this->request->getPost('note'));

        if ($id <= 0) {
            return $this->response->setJSON(['error' => 'Data close tidak valid.']);
        }
        if ($note === '') {
            return $this->response->setJSON(['error' => 'Catatan buka close wajib diisi.']);
        }

        $modelLog = new ModelPoCloseLog();
        $modelDetail = new Modeldetailpo();
        $modelOutstanding = new Modeloutstand();

        $log = $modelLog->find($id);
        if (!$log) {
            return $this->response->setJSON(['error' => 'Riwayat close tidak ditemukan.']);
        }
        if (!empty($log['reopened_at'])) {
            return $this->response->setJSON(['error' => 'Riwayat close ini sudah pernah dibuka kembali.']);
        }

        $detailAsal = $modelDetail
            ->where('detnopo', $log['nopo_asal'])
            ->where('detkodebrg', $log['kodebrg'])
            ->orderBy('id', 'ASC')
            ->first();

        if (!$detailAsal) {
            return $this->response->setJSON(['error' => 'Item PO asal tidak ditemukan, jadi close belum bisa dibuka.']);
        }

        $qtyDibuka = (float) $log['qty_dipindah'];
        if ($qtyDibuka <= 0) {
            return $this->response->setJSON(['error' => 'Qty close tidak valid.']);
        }

        $db = db_connect();
        $db->transStart();

        $detberat = (float) ($detailAsal['detberat'] ?? 0);
        $newDetqty = (float) $detailAsal['detqty'] + $qtyDibuka;
        $hargaTambah = $qtyDibuka * (float) ($log['harga_satuan'] ?? 0);

        $modelDetail->update($detailAsal['id'], [
            'detqty' => $newDetqty,
            'detharga' => (float) ($detailAsal['detharga'] ?? 0) + $hargaTambah,
            'detsubtotal' => $newDetqty * $detberat,
        ]);

        $modelLog->update($id, [
            'reopened_by' => session()->get('namauser'),
            'reopened_at' => date('Y-m-d H:i:s'),
            'reopen_note' => $note,
        ]);

        $modelOutstanding->sinkronByPo($log['nopo_asal']);
        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['error' => 'Close gagal dibuka. Tidak ada data yang diubah.']);
        }

        return $this->response->setJSON([
            'sukses' => 'Close item ' . $log['kodebrg'] . ' berhasil dibuka. Qty ' . number_format($qtyDibuka, 0, ',', '.') . ' pcs kembali masuk outstanding PO.',
        ]);
    }

    /**
     * Close 1 PO utuh -- item yang mau ditutup tetap all-or-nothing per
     * request ini (kalau salah satu item yang DIISI rincian penutupannya
     * ternyata invalid, seluruh aksi dibatalkan, tidak ada perubahan sama
     * sekali), TAPI item boleh dilewati/dibiarkan (nggak wajib SEMUA item
     * yang masih ada sisa harus diisi -- yang nggak diisi rincian
     * penutupannya cukup dibiarkan tetap outstanding seperti biasa). Sama
     * kayak Close Item, tiap item juga boleh cuma ditutup sebagian.
     * $items dikirim sebagai array asosiatif
     * [id_detail_po => [alokasi => [[qty, nopo_tujuan], ...]]].
     */
    public function closePo()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $nopo = trim((string) $this->request->getPost('nopo'));
        $items = (array) $this->request->getPost('items');

        $modelDetail = new Modeldetailpo();
        $modelPo = new Modelpo();
        $modelOutstanding = new Modeloutstand();
        $modelLog = new ModelPoCloseLog();

        $semuaBaris = $modelDetail->where('detnopo', $nopo)->findAll();
        $barisPerluDitutup = array_values(array_filter($semuaBaris, function ($row) use ($items) {
            $sisa = max((float) $row['detqty'] - (float) ($row['detkirim_awal'] ?? 0) - (float) ($row['detkirim'] ?? 0), 0);

            return $sisa > 0 && !empty($items[$row['id']]['alokasi']);
        }));

        if (!$barisPerluDitutup) {
            return $this->response->setJSON(['error' => 'Belum ada item yang diisi rincian penutupannya.']);
        }

        $db = db_connect();
        $db->transStart();

        try {
            foreach ($barisPerluDitutup as $row) {
                $alokasi = (array) $items[$row['id']]['alokasi'];
                $this->tutupItemPo($row, $alokasi, $modelDetail, $modelPo, $modelLog);
            }
        } catch (\RuntimeException $e) {
            $db->transRollback();
            return $this->response->setJSON(['error' => $e->getMessage()]);
        }

        $modelOutstanding->sinkronByPo($nopo);
        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['error' => 'PO gagal ditutup. Tidak ada data yang diubah.']);
        }

        return $this->response->setJSON([
            'sukses' => count($barisPerluDitutup) . ' item berhasil ditutup.',
        ]);
    }

    function editItem()
    {
        if ($this->request->isAJAX()) {
            $iddetail = $this->request->getPost('iddetail');
            $jml = $this->request->getPost('jml');
            $harga = $this->request->getPost('harga');
            $jmlFloat = max(0, (float) str_replace(',', '.', (string) $jml));
            $hargaFloat = max(0, (float) str_replace(',', '.', (string) $harga));
            $terkirimAwal = max(0, (float) str_replace(',', '.', (string) $this->request->getPost('terkirim_awal')));
            $invoiceAwal = max(0, (float) str_replace(',', '.', (string) $this->request->getPost('invoice_awal')));

            $modelDetail = new Modeldetailpo();
            $modelpo = new Modelpo();
            $modeloutstanding = new Modeloutstand();

            $rowData = $modelDetail->find($iddetail);
            if (!$rowData) {
                return $this->response->setJSON(['error' => 'Item PO tidak ditemukan']);
            }

            $nopo = $rowData['detnopo'];
            if ($this->poMasukSudahDipakai($nopo)) {
                return $this->response->setJSON(['error' => 'PO sudah dipakai transaksi lanjutan, item tidak bisa diedit.']);
            }

            $berat = $rowData['detberat'];
            $kodebrg = $rowData['detkodebrg'];

            if ($jmlFloat <= 0) {
                $json = [
                    'error' => 'Jumlah harus lebih dari 0'
                ];
            } elseif ($terkirimAwal > $jmlFloat) {
                $json = [
                    'error' => 'Qty terkirim tidak boleh lebih besar dari Qty PO item.'
                ];
            } elseif ($invoiceAwal > ($terkirimAwal * $hargaFloat)) {
                $json = [
                    'error' => 'Nilai sudah ditagihkan tidak boleh lebih besar dari QTY terkirim dikali harga satuan.'
                ];
            } else {
                $db = db_connect();
                $db->transStart();

                $modelDetail->update($iddetail, [
                    'detqty' => $jmlFloat,
                    'detkirim_awal' => $terkirimAwal,
                    'detinvoice_awal' => $invoiceAwal,
                    'detkurang' => max($jmlFloat - $terkirimAwal - (float) ($rowData['detkirim'] ?? 0), 0),
                    'detsubtotal' => $jmlFloat * (float) $berat,
                    'detharga' => $jmlFloat * $hargaFloat
                ]);

                $modelDetail->updateDetailPoKurang();

                $totalBerat = $modelDetail->ambilTotalBerat($nopo);
                $totalharga = $modelDetail->ambilTotalharga($nopo);

                $modelpo->update($nopo, [
                    'qty' => $totalBerat,
                    'hargapo' => $totalharga
                ]);

                $modeloutstanding->sinkronByPo($nopo);
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
                    ? ['error' => 'Item PO gagal diperbarui. Tidak ada data yang diubah.']
                    : ['sukses' => 'Item Berhasil di Update'];
            }
            return $this->response->setJSON($json);
        }
    }

    public function updateNopo()
    {
        if ($this->request->isAJAX()) {
            $newNopo = $this->request->getPost('newNopo');
            $originalNopoSha1 = $this->request->getPost('originalNopoSha1');

            if (is_string($newNopo) && !empty($newNopo)) {
                $modelPo = new Modelpo();
                $poLama = $modelPo->where('sha1(nopo)', $originalNopoSha1)->first();

                if (!$poLama) {
                    $json = ['error' => 'Data Purchase Order tidak ditemukan'];
                } else {
                    $nopoLama = $poLama['nopo'];
                    $db = db_connect();
                    $sudahDigunakan = $newNopo !== $nopoLama && $modelPo->find($newNopo);
                    $sudahDipakaiTransaksi = $this->poMasukSudahDipakai($nopoLama);

                    if ($sudahDigunakan) {
                        $json = ['error' => 'No. Purchase Order sudah digunakan'];
                    } elseif ($sudahDipakaiTransaksi) {
                        $json = ['error' => 'No. Purchase Order tidak bisa diubah karena PO sudah dipakai transaksi lanjutan.'];
                    } else {
                        $db->transStart();

                        $modelPo->where('nopo', $nopoLama)->set(['nopo' => $newNopo])->update();
                        $db->table('detail_po')->where('detnopo', $nopoLama)->update(['detnopo' => $newNopo]);
                        $db->table('outstanding')->where('nopo', $nopoLama)->update(['nopo' => $newNopo]);

                        $db->transComplete();

                        $json = $db->transStatus() === false
                            ? ['error' => 'Gagal mengubah No. Purchase Order. Tidak ada data yang diubah.']
                            : [
                                'sukses' => 'No. Purchase Order berhasil diubah',
                                'newNopoSha1' => sha1($newNopo)
                            ];
                    }
                }
            } else {
                $json = ['error' => 'No. Purchase Order baru tidak valid'];
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

            return $this->response->setJSON($json);
        }
    }

    public function simpanItemDetail()
    {
        if ($this->request->isAJAX()) {
            $nopo = $this->request->getPost('nopo');
            $kodebarang = $this->request->getPost('kodebarang');
            $namabarang = $this->request->getPost('namabarang');
            $berat = $this->request->getPost('berat');
            $jml = $this->request->getPost('jml');
            $harga = $this->request->getPost('harga');
            $jmlFloat = max(0, (float) str_replace(',', '.', (string) $jml));
            $hargaFloat = max(0, (float) str_replace(',', '.', (string) $harga));
            $terkirimAwal = max(0, (float) str_replace(',', '.', (string) $this->request->getPost('terkirim_awal')));
            $invoiceAwal = max(0, (float) str_replace(',', '.', (string) $this->request->getPost('invoice_awal')));
            $idpelanggan = $this->request->getPost('idpelanggan');
            $tglfaktur = $this->request->getPost('tglfaktur');

            if ($this->poMasukSudahDipakai($nopo)) {
                return $this->response->setJSON(['error' => 'PO sudah dipakai transaksi lanjutan, item tidak bisa ditambahkan.']);
            }

            if ($jmlFloat <= 0) {
                return $this->response->setJSON(['error' => 'Jumlah harus lebih dari 0']);
            }
            if ($terkirimAwal > $jmlFloat) {
                return $this->response->setJSON(['error' => 'Qty terkirim tidak boleh lebih besar dari Qty PO item.']);
            }
            if ($invoiceAwal > ($terkirimAwal * $hargaFloat)) {
                return $this->response->setJSON(['error' => 'Nilai sudah ditagihkan tidak boleh lebih besar dari QTY terkirim dikali harga satuan.']);
            }

            $modeloutstanding = new Modeloutstand();
            $modeltemppo = new Modeldetailpo();
            $modelpo = new Modelpo();

            $db = db_connect();
            $db->transStart();

            $existingRow = $modeloutstanding
                ->where('nopo', $nopo)
                ->where('kodebrg', $kodebarang)
                ->first();

            if ($existingRow) {
                $newJml = (float) $existingRow['qty'] + $jmlFloat;

                $modeloutstanding->update($existingRow['id'], [
                    'qty' => $newJml
                ]);
            } else {
                $modeloutstanding->insert([
                    'nopo' => $nopo,
                    'tgl' => $tglfaktur,
                    'kodebrg' => $kodebarang,
                    'qty' => $jmlFloat,
                    'idpel' => $idpelanggan
                ]);
            }

            $existingRowDetailPo = $modeltemppo
                ->where('detkodebrg', $kodebarang)
                ->where('detnopo', $nopo)
                ->first();

            if ($existingRowDetailPo) {
                $newJmlDetailPo = (float) $existingRowDetailPo['detqty'] + $jmlFloat;
                $newTerkirimAwal = min((float) ($existingRowDetailPo['detkirim_awal'] ?? 0) + $terkirimAwal, $newJmlDetailPo);
                $newSubtotal = (float) $existingRowDetailPo['detsubtotal'] + ($jmlFloat * (float) $berat);
                $newSubharga = (float) $existingRowDetailPo['detharga'] + ($jmlFloat * $hargaFloat);
                $newUnitPrice = $newJmlDetailPo > 0 ? $newSubharga / $newJmlDetailPo : 0;
                $newInvoiceAwal = min((float) ($existingRowDetailPo['detinvoice_awal'] ?? 0) + $invoiceAwal, $newTerkirimAwal * $newUnitPrice);

                $modeltemppo->update($existingRowDetailPo['id'], [
                    'detqty' => $newJmlDetailPo,
                    'detkirim_awal' => $newTerkirimAwal,
                    'detinvoice_awal' => $newInvoiceAwal,
                    'detsubtotal' => $newSubtotal,
                    'detharga' => $newSubharga,
                    'detkurang' => max($newJmlDetailPo - $newTerkirimAwal - (float) $existingRowDetailPo['detkirim'], 0),
                ]);
            } else {
                $modeltemppo->insert([
                    'detnopo' => $nopo,
                    'detkodebrg' => $kodebarang,
                    'dettglpo' => $tglfaktur,
                    'namabarang' => $namabarang,
                    'detberat' => $berat,
                    'detqty' => $jmlFloat,
                    'detkirim' => 0,
                    'detkirim_awal' => $terkirimAwal,
                    'detinvoice_awal' => $invoiceAwal,
                    'detkurang' => max($jmlFloat - $terkirimAwal, 0),
                    'detidpel' => $idpelanggan,
                    'detsubtotal' => $jmlFloat * (float) $berat,
                    'detharga' => $jmlFloat * $hargaFloat
                ]);
            }
            $totalBerat = $modeltemppo->ambilTotalBerat($nopo);
            $totalharga = $modeltemppo->ambilTotalharga($nopo);
            $modelpo->update($nopo, ['qty' => $totalBerat]);
            $modelpo->update($nopo, ['hargapo' => $totalharga]);

            $modeloutstanding->sinkronByPo($nopo);
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
                ? ['error' => 'Item PO gagal ditambahkan. Tidak ada data yang diubah.']
                : ['sukses' => 'Item Berhasil di Tambahkan'];
            return $this->response->setJSON($json);
        }
    }

    public function updatePelanggan()
    {
        if ($this->request->isAJAX()) {
            $nopoSha1 = (string) $this->request->getPost('nopoSha1');
            $idpelanggan = (int) $this->request->getPost('idpelanggan');

            $modelPo = new Modelpo();
            $po = $modelPo->where('sha1(nopo)', $nopoSha1)->first();
            if (!$po) {
                return $this->response->setJSON(['error' => 'Data Purchase Order tidak ditemukan']);
            }

            if ($this->poMasukSudahDipakai($po['nopo'])) {
                return $this->response->setJSON(['error' => 'Pelanggan tidak bisa diubah karena PO sudah dipakai transaksi lanjutan.']);
            }

            $pelanggan = $this->modelPelanggan->find($idpelanggan);
            if (!$pelanggan || in_array($idpelanggan, [1, 2], true)) {
                return $this->response->setJSON(['error' => 'Pelanggan tidak valid']);
            }

            $db = db_connect();
            $db->transStart();
            $modelPo->update($po['nopo'], ['idpel' => $idpelanggan]);
            $db->table('detail_po')->where('detnopo', $po['nopo'])->update(['detidpel' => $idpelanggan]);
            $db->table('outstanding')->where('nopo', $po['nopo'])->update(['idpel' => $idpelanggan]);
            $db->transComplete();

            return $this->response->setJSON($db->transStatus() === false
                ? ['error' => 'Pelanggan PO gagal diubah. Tidak ada data yang diubah.']
                : ['sukses' => 'Pelanggan PO berhasil diubah', 'namaPelanggan' => $pelanggan['pelnama']]);
        }
    }

    /**
     * Tanggal PO sengaja TIDAK ikut aturan kunci (poMasukSudahDipakai) kayak
     * No. PO/Pelanggan -- soalnya tanggal ngga dipakai sebagai kunci
     * penghubung ke tabel lain (beda dari nopo yang jadi acuan di banyak
     * tabel), jadi aman diubah kapan aja. detail_po.dettglpo (salinan
     * tanggal per item), outstanding.tgl (turunan dari dettglpo/tglpo), dan
     * invoice_out.po_date (foto tanggal PO pas invoice itu di-generate)
     * ikut disamain biar konsisten di semua tempat -- termasuk invoice yang
     * udah lebih dulu dibuat dari PO ini.
     */
    public function updateTanggal()
    {
        if ($this->request->isAJAX()) {
            $nopoSha1 = (string) $this->request->getPost('nopoSha1');
            $tanggal = trim((string) $this->request->getPost('tanggal'));

            $modelPo = new Modelpo();
            $po = $modelPo->where('sha1(nopo)', $nopoSha1)->first();
            if (!$po) {
                return $this->response->setJSON(['error' => 'Data Purchase Order tidak ditemukan']);
            }

            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal) || !strtotime($tanggal)) {
                return $this->response->setJSON(['error' => 'Format tanggal tidak valid']);
            }

            $db = db_connect();
            $db->transStart();
            $modelPo->update($po['nopo'], ['tglpo' => $tanggal]);
            $db->table('detail_po')->where('detnopo', $po['nopo'])->update(['dettglpo' => $tanggal]);
            $db->table('outstanding')->where('nopo', $po['nopo'])->update(['tgl' => $tanggal]);
            if ($db->tableExists('invoice_out') && $db->fieldExists('po_date', 'invoice_out')) {
                $db->table('invoice_out')->where('po_no', $po['nopo'])->update(['po_date' => $tanggal]);
            }
            $db->transComplete();

            return $this->response->setJSON($db->transStatus() === false
                ? ['error' => 'Tanggal PO gagal diubah. Tidak ada data yang diubah.']
                : ['sukses' => 'Tanggal PO berhasil diubah']);
        }
    }

    private function poMasukSudahDipakai(string $nopo): bool
    {
        return !empty($this->poMasukEditLockReasons($nopo));
    }

    private function poMasukEditLockReasons(string $nopo): array
    {
        $nopo = trim($nopo);
        if ($nopo === '') {
            return [];
        }

        $reasons = [];
        $db = $this->db ?? db_connect();

        if ($db->tableExists('barangkeluar') && $db->fieldExists('detpo', 'barangkeluar') && $db->table('barangkeluar')->where('detpo', $nopo)->countAllResults() > 0) {
            $reasons[] = 'Pengiriman barang';
        }

        if ($db->tableExists('detail_barangkeluar') && $db->fieldExists('detpo', 'detail_barangkeluar') && $db->table('detail_barangkeluar')->where('detpo', $nopo)->countAllResults() > 0) {
            $reasons[] = 'Detail pengiriman barang';
        }

        if ($db->tableExists('invoice_out') && $db->fieldExists('po_no', 'invoice_out') && $db->table('invoice_out')->where('po_no', $nopo)->countAllResults() > 0) {
            $reasons[] = 'Invoice keluar';
        }

        if ($db->tableExists('rencana_pengiriman') && $db->fieldExists('no_po', 'rencana_pengiriman') && $db->table('rencana_pengiriman')->where('no_po', $nopo)->countAllResults() > 0) {
            $reasons[] = 'Permintaan pengiriman';
        }

        if ($db->tableExists('po_keluar') && $db->fieldExists('po_masuk_terkait', 'po_keluar') && $db->table('po_keluar')->where('po_masuk_terkait', $nopo)->countAllResults() > 0) {
            $reasons[] = 'PO Keluar terkait';
        }

        if ($db->tableExists('detail_po') && $db->fieldExists('detkirim', 'detail_po') && $db->table('detail_po')->where('detnopo', $nopo)->where('COALESCE(detkirim, 0) >', 0)->countAllResults() > 0) {
            $reasons[] = 'Progress pengiriman';
        }

        return array_values(array_unique($reasons));
    }

    private function matchImportedProduct(array $item, array $produk): ?array
    {
        $description = strtoupper((string) ($item['description'] ?? ''));
        $normalizedDescription = $this->normalisasiTeksProduk($description);
        $bestMatch = null;
        $bestLength = 0;

        // User ingin "Produk di Sistem" dibaca dari nama produk, bukan dari
        // kode. Ambil kecocokan nama terpanjang supaya nama yang lebih spesifik
        // menang ketika beberapa nama punya kata umum yang sama.
        foreach ($produk as $row) {
            $nama = $this->normalisasiTeksProduk((string) $row['brgnama']);
            if ($nama !== '' && strpos($normalizedDescription, $nama) !== false && strlen($nama) > $bestLength) {
                $bestMatch = $row;
                $bestLength = strlen($nama);
            }
        }

        return $bestMatch;
    }

    private function normalisasiTeksProduk(string $value): string
    {
        $value = strtoupper($value);
        $value = preg_replace('/[^A-Z0-9]+/', ' ', $value) ?? '';
        return trim(preg_replace('/\s+/', ' ', $value) ?? '');
    }

    private function matchImportedPelanggan(string $hint, array $pelanggan): string
    {
        $hint = strtoupper($hint);
        if ($hint === '') {
            return '';
        }

        foreach ($pelanggan as $row) {
            $nama = strtoupper((string) $row['pelnama']);
            if ($nama !== '' && (strpos($hint, $nama) !== false || strpos($nama, $hint) !== false)) {
                return (string) $row['pelid'];
            }
        }

        return '';
    }

    private function ambilBeratProduk(string $kodeBarang): float
    {
        $row = $this->db->table('berat')
            ->selectSum('berat', 'total_berat')
            ->where('kodeprd', $kodeBarang)
            ->get()
            ->getRowArray();

        return (float) ($row['total_berat'] ?? 0);
    }
}
