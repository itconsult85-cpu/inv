<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Modelbarang;
use App\Models\Modelbarangmasuk;
use App\Models\Modelberat;
use App\Models\ModelDataStok;
use App\Models\Modeldetailbarangmasuk;
use App\Models\Modelstok;
use App\Models\Modelgudang;
use App\Models\Modelng;
use App\Models\Modeltempbarangmasuk;
use App\Models\Modelmaterial;
use App\Models\Modelkategori;
use App\Models\ModelPelanggan;
use App\Models\ModelSupplier;
use \Hermawan\DataTables\DataTable;
use Config\Services;

class Barangmasuk extends BaseController
{
    private function currentUserReference(): ?int
    {
        $userid = trim((string) session()->get('userid'));
        if ($userid === '') {
            return null;
        }

        $user = db_connect()->table('users')->select('id')->where('userid', $userid)->get()->getRowArray();
        return $user ? (int) $user['id'] : null;
    }

    public function __construct()
    {
        $this->ensureAdjustmentProdukMasukColumns();
        $this->ensureAdjustmentProdukMasukTriggers();
    }

    private function ensureAdjustmentProdukMasukColumns(): void
    {
        $db = db_connect();
        $forge = \Config\Database::forge();

        $nullableColumns = [
            ['table' => 'temp_barangmasuk', 'column' => 'detmatkode'],
            ['table' => 'barangmasuk', 'column' => 'satuan'],
            ['table' => 'barangmasuk', 'column' => 'satuanberat'],
            ['table' => 'detail_barangmasuk', 'column' => 'satuan'],
            ['table' => 'detail_barangmasuk', 'column' => 'satuanberat'],
            ['table' => 'detail_barangmasuk', 'column' => 'detmatkode'],
            ['table' => 'barangmasuk', 'column' => 'idsup'],
            ['table' => 'temp_barangmasuk', 'column' => 'idsup'],
            ['table' => 'detail_barangmasuk', 'column' => 'idsup'],
        ];

        foreach ($nullableColumns as $target) {
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
                log_message('error', 'Gagal mengubah kolom {column} pada {table}: {message}', [
                    'column' => $columnName,
                    'table' => $table,
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }

    private function ensureAdjustmentProdukMasukTriggers(): void
    {
        $db = db_connect();

        try {
            $trigger = $db->query("SHOW TRIGGERS WHERE `Trigger` = 'ngdata_insert_masuk'")->getRowArray();
            $statement = $trigger['Statement'] ?? '';

            if (!$trigger || stripos($statement, 'NEW.idsup IS NOT NULL') !== false) {
                return;
            }

            $db->query('DROP TRIGGER IF EXISTS ngdata_insert_masuk');
            $db->query("
                CREATE TRIGGER ngdata_insert_masuk
                AFTER INSERT ON detail_barangmasuk
                FOR EACH ROW
                BEGIN
                    IF NEW.idsup IS NOT NULL AND NEW.detmatkode IS NOT NULL THEN
                        INSERT INTO ngdata (detfaktur, detbrgkode, idsup, tgl, matjenis, beratmatkeluar, beratmatmasuk)
                        VALUES (NEW.detfaktur, NEW.detbrgkode, NEW.idsup, NEW.tgl, NEW.detmatkode, 0, NEW.detsubtotal);

                        UPDATE ngdata
                        SET beratng = beratmatkeluar - beratmatmasuk
                        WHERE detfaktur = NEW.detfaktur
                            AND detbrgkode = NEW.detbrgkode
                            AND tgl = NEW.tgl
                            AND idsup = NEW.idsup
                            AND matjenis = NEW.detmatkode;
                    END IF;
                END
            ");
        } catch (\Throwable $e) {
            log_message('error', 'Gagal memastikan trigger ngdata_insert_masuk support adjustment stok: {message}', [
                'message' => $e->getMessage(),
            ]);
        }
    }
    private function normalisasiMaterialAdjustment($materialId)
    {
        $materialId = (int) $materialId;
        return $materialId > 0 ? $materialId : null;
    }

    /**
     * Kolom `gudang`/`idgudang` di tabel stok itu int (gdgid), tapi kadang
     * yang kekirim dari form itu nama gudangnya (mis. "Cirebon"), bukan
     * id-nya -- biar nggak salah simpan/nyari data, cari dulu id aslinya
     * lewat nama kalau yang dikirim bukan angka.
     */
    private function resolveGudangId($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        $row = db_connect()->table('gudang')->where('gdgnama', $value)->get()->getRowArray();

        return $row ? (int) $row['gdgid'] : null;
    }

    /**
     * Sumber Produk "Produksi Material Pelanggan": produknya dibikin dari
     * material milik pelanggan sendiri (bukan stok material TRE), jadi
     * TIDAK mengurangi stokmaterial sama sekali -- tapi tetap dicatat
     * sebagai 1 batch di modul Produksi (tanpa baris detail_produksi, biar
     * kelihatan di List Produksi) dengan keterangan "Material Pelanggan"
     * biar kebeda sama produksi yang beneran makan stok material TRE.
     */
    private function buatBatchProduksiMaterialPelanggan(string $tglfaktur, $gudang, array $rowsTemp): void
    {
        $modelProduksi = new \App\Models\ModelProduksi();

        do {
            $noProduksi = 'PRDPLG-' . date('Ymd-His') . '-' . random_int(100, 999);
        } while ($modelProduksi->find($noProduksi));

        $modelProduksi->insert([
            'no_produksi' => $noProduksi,
            'tgl_produksi' => $tglfaktur,
            'gudang' => $gudang,
            'iduser' => $this->currentUserReference(),
            'created_at' => date('Y-m-d H:i:s'),
            'keterangan' => 'Material Pelanggan',
        ]);

        $modelProduksiProduk = new \App\Models\ModelProduksiProduk();
        foreach ($rowsTemp as $row) {
            $modelProduksiProduk->insert([
                'no_produksi' => $noProduksi,
                'kode_produk' => $row['detbrgkode'],
                'nama_produk' => $row['detbrgnama'],
                'qty_produk' => $row['detjml'],
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function data()
    {
        return view('barangmasuk/viewdata', [
            'materials' => (new Modelmaterial())->findAll(),
            'kategoris' => (new Modelkategori())->orderBy('katnama', 'ASC')->findAll(),
            'pelanggans' => (new ModelPelanggan())->whereNotIn('pelid', [1, 2])->findAll(),
            'bisaLihatProduksi' => \App\Libraries\AccessControl::can('material.produksi.view'),
        ]);
    }

    public function listData()
    {
        if ($this->request->isAJAX()) {
            $tglawal = $this->request->getPost('tglawal');
            $tglakhir = $this->request->getPost('tglakhir');
            $sumber = $this->request->getPost('sumber');

            $db = \Config\Database::connect();
            $builder = $db->table('barangmasuk')
                ->select("barangmasuk.faktur, barangmasuk.tglfaktur, barangmasuk.po_keluar_id, COALESCE(supplier.supnama, 'Adjustment Stok') AS supnama, barangmasuk.totalberatbarang, gudang.gdgnama, barangmasuk.qtymasuk", false)
                ->join('gudang', 'gdgid = gudang')
                ->join('supplier', 'supid = idsup', 'left');

            if ($tglawal && $tglakhir) {
                $builder->whereIn('barangmasuk.faktur', function ($subQuery) use ($tglawal, $tglakhir) {
                    $subQuery->select('barangmasuk.faktur')
                        ->from('barangmasuk')
                        ->where('barangmasuk.tglfaktur >=', $tglawal)
                        ->where('barangmasuk.tglfaktur <=', $tglakhir);
                });
            }


            if ($sumber === 'adjustment') {
                $builder->where('barangmasuk.idsup IS NULL', null, false)
                    ->where('barangmasuk.po_keluar_id IS NULL', null, false);
            } elseif ($sumber === 'supplier') {
                $builder->groupStart()
                    ->where('barangmasuk.idsup IS NOT NULL', null, false)
                    ->orWhere('barangmasuk.po_keluar_id IS NOT NULL', null, false)
                    ->groupEnd();
            }
            return DataTable::of($builder)
                ->addNumbering('nomor')
                ->add('aksi', function ($row) {
                    $retur = !empty($row->po_keluar_id) ? "<button type=\"button\" class=\"btn btn-sm btn-warning\" onclick=\"location.href='/barangmasuk/retur/" . sha1($row->faktur) . "'\" title=\"Retur NG\"><i class=\"fa fa-exchange-alt\"></i></button>&nbsp;" : '';
                    $kelolaRetur = !empty($row->po_keluar_id) ? "<button type=\"button\" class=\"btn btn-sm btn-secondary\" onclick=\"location.href='" . site_url('produkretur/kelola/' . sha1($row->faktur)) . "'\" title=\"Koreksi/Batalkan Retur NG\"><i class=\"fa fa-tools\"></i></button>&nbsp;" : '';
                    if (\App\Libraries\AccessControl::can('produk.masuk.delete')) {
                        return $retur . $kelolaRetur . "<button type=\"button\" class=\"btn btn-sm btn-primary\" onclick=\"edit('" . sha1($row->faktur) . "')\"><i class=\"fa fa-edit\"></i></button>&nbsp
                        <button type=\"button\" class=\"btn btn-sm btn-danger\" onclick=\"hapus('" . $row->faktur . "')\"><i class=\"fa fa-trash-alt\"></i></button>";
                    }
                    return $retur . $kelolaRetur . "<button type=\"button\" class=\"btn btn-sm btn-primary\" onclick=\"edit('" . sha1($row->faktur) . "')\"><i class=\"fa fa-edit\"></i></button>";
                })
                ->format('qtymasuk', function ($value) {
                    return number_format($value, 0, ',', '.');
                })
                ->format('totalberatbarang', function ($value) {
                    $value = ceil($value * 100) / 100;
                    return number_format($value, 2, ',', '.');
                })
                ->format('tglfaktur', function ($value) {
                    return date('d-m-Y', strtotime($value));
                })
                ->toJson(true);
        }
    }

    public function input()
    {
        $modelgudang = new Modelgudang();
        $penerimaanNg = (string) $this->request->getGet('penerimaan_ng') === '1';
        $poNgId = (int) $this->request->getGet('po_keluar_id');
        $poKeluar = new PoKeluar();
        $datapokeluar = $penerimaanNg ? $poKeluar->daftarPoNgUntukTipe('produk') : $poKeluar->daftarPoAktifUntukTipe('produk');
        $poNgTerpilih = null;
        foreach ($datapokeluar as $po) if ((int) $po['id'] === $poNgId) $poNgTerpilih = $po;
        $data = [
            'datagudang' => $modelgudang->findAll(),
            'datapokeluar' => $datapokeluar,
            'penerimaanNg' => $penerimaanNg,
            'poNgTerpilih' => $poNgTerpilih,
            'datasupplier' => (new ModelSupplier())->orderBy('supnama', 'ASC')->findAll(),
            'databarang' => (new Modelbarang())->select('brgkode, brgnama')->orderBy('brgkode', 'ASC')->findAll(),
            'datamaterial' => (new Modelmaterial())->select('matid, matkode, matnama')->orderBy('matkode', 'ASC')->findAll(),
        ];
        return view('barangmasuk/forminput', $data);
    }

    private function buatNomorProdukMasuk(): string
    {
        do {
            $nomor = 'BM-' . date('Ymd-His') . '-' . random_int(100, 999);
        } while (db_connect()->table('barangmasuk')->where('faktur', $nomor)->countAllResults() > 0);

        return $nomor;
    }

    private function nomorProdukMasukDariRequest(): string
    {
        $poKeluarId = (int) $this->request->getPost('po_keluar_id');
        $nomorInternal = trim((string) $this->request->getPost('faktur_internal'));

        if ($poKeluarId > 0 && $nomorInternal !== '') {
            return $nomorInternal;
        }

        $nofaktur = trim((string) $this->request->getPost('nofaktur'));
        if ($nofaktur !== '') {
            return $nofaktur;
        }

        return $nomorInternal !== '' ? $nomorInternal : $this->buatNomorProdukMasuk();
    }

    private function validasiQtyPoKeluar(int $poKeluarId, string $kodeBarang, float $qtyTambahan, float $qtyTempSaatIni = 0): ?string
    {
        if ($poKeluarId <= 0) {
            return null;
        }

        $detail = db_connect()->table('detail_po_keluar')
            ->select('qty_pesan, qty_masuk')
            ->where('po_keluar_id', $poKeluarId)
            ->where('tipe_item', 'produk')
            ->where('kode_item', $kodeBarang)
            ->get()->getRowArray();

        if (!$detail) {
            return "Produk {$kodeBarang} tidak ada di PO Keluar yang dipilih.";
        }

        $sisa = (float) $detail['qty_pesan'] - (float) $detail['qty_masuk'];
        if (($qtyTempSaatIni + $qtyTambahan) > ($sisa + 0.00001)) {
            return "Qty masuk produk {$kodeBarang} tidak boleh lebih besar dari sisa PO Keluar ({$sisa}).";
        }

        return null;
    }

    private function validasiTempPoKeluar(int $poKeluarId, array $rowsTemp): ?string
    {
        if ($poKeluarId <= 0) {
            return null;
        }

        $qtyPerKode = [];
        foreach ($rowsTemp as $row) {
            $kode = (string) $row['detbrgkode'];
            $qtyPerKode[$kode] = ($qtyPerKode[$kode] ?? 0) + (float) $row['detjml'];
        }

        foreach ($qtyPerKode as $kode => $qty) {
            $error = $this->validasiQtyPoKeluar($poKeluarId, $kode, $qty);
            if ($error !== null) {
                return $error;
            }
        }

        return null;
    }

    public function tampilDataTemp()
    {
        if ($this->request->isAJAX()) {
            $nofaktur = $this->request->getPost('nofaktur');
            $idsupplier = $this->request->getPost('idsupplier');
            $idmaterial = $this->normalisasiMaterialAdjustment($this->request->getPost('idmaterial'));

            $modalTempBarangMasuk = new Modeltempbarangmasuk();

            $dataTemp = $modalTempBarangMasuk->tampilDataTemp($nofaktur, $idsupplier, $idmaterial);
            $data = [
                'tampildata' => $dataTemp
            ];

            $json = [
                'data' => view('barangmasuk/datatemp', $data)
            ];
            echo json_encode($json);
        }
    }

    public function ambilDataBarang()
    {
        if ($this->request->isAJAX()) {
            $kodebarang = $this->request->getPost('kodebarang');
            $idgudang = $this->resolveGudangId($this->request->getPost('idgudang'));

            $modelStok = new Modelstok();
            $modelBarang = new Modelbarang();
            $modelBerat = new Modelberat();

            $cekNama = $modelBarang->find($kodebarang);
            $cekDataBerat = $modelBerat->find($kodebarang);

            if ($cekNama == null) {
                $json = [
                    'error' => 'Maaf data barang tidak ditemukan'
                ];
            } elseif (($cekDataBerat == null || empty($cekDataBerat['berat'])) && !$this->produkTanpaBerat($kodebarang)) {
                $json = [
                    'error' => 'Maaf, berat barang belum diinput'
                ];
            } else {
                $stokGudang = $modelStok->getStokByGudang($kodebarang, $idgudang);
                $idBarang = $modelStok->getBarangIdByKodeGudang($kodebarang, $idgudang);
                $harga = $modelStok->getHargaByKodeBarang($kodebarang);

                $data = [
                    'namabarang' => $cekNama['brgnama'],
                    'idmaterial' => $this->materialUtamaProduk($cekNama['brgmat']),
                    'berat' => $cekDataBerat['berat'] ?? 0,
                    'stok' => $stokGudang,
                    'harga' => $harga,
                    'idbarang' => $idBarang
                ];

                $json = [
                    'sukses' => $data
                ];
            }
            echo json_encode($json);
        }
    }

    public function listDataBarang()
    {
        $request = Services::request();
        $datamodel = new ModelDataStok($request);
        if ($request->getMethod(true) == 'POST') {
            $gdgid = $this->request->getPost('gdgid');
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

                $tombolPilih = "<button type=\"button\" class=\"btn btn-sm btn-info\" onclick=\"pilih('" . $stokIdGudang1Value . "','" . $stokIdGudang2Value . "','" . $list->kodebarang . "','" . $gdgid . "','" . $stokGudang1Value . "','" . $stokGudang2Value . "')\">Pilih</button>";

                $row[] = $no;
                $row[] = $list->kodebarang;
                $row[] = $list->namabarang;
                $row[] = number_format($stokGudang1Value, 0, ",", ".");
                $row[] = number_format($stokGudang2Value, 0, ",", ".");
                $row[] = $tombolPilih;
                $data[] = $row;
            }
            $output = [
                "draw" => $request->getPost('draw'),
                "recordsTotal" => $datamodel->count_all(),
                "recordsFiltered" => $datamodel->count_filtered(),
                "data" => $data
            ];
            echo json_encode($output);
        }
    }

    function simpanItem()
    {
        if ($this->request->isAJAX()) {
            $nofaktur = $this->nomorProdukMasukDariRequest();
            $kodebarang = $this->request->getPost('kodebarang');
            $namabarang = $this->request->getPost('namabarang');
            $berat = $this->request->getPost('berat');
            $tglfaktur = $this->request->getPost('tglfaktur');
            $idbarang = $this->request->getPost('idbarang');
            $idmaterial = $this->normalisasiMaterialAdjustment($this->request->getPost('idmaterial'));
            $jml = $this->request->getPost('jml');
            $poKeluarId = (int) $this->request->getPost('po_keluar_id');
            $sumberProdukInput = $this->request->getPost('sumber_produk');
            $sumberProduk = in_array($sumberProdukInput, ['adjustment', 'produksi', 'produksi_pelanggan'], true) ? $sumberProdukInput : 'beli';
            if ($sumberProduk === 'adjustment' || $sumberProduk === 'produksi' || $sumberProduk === 'produksi_pelanggan') {
                $poKeluarId = 0;
                $idsupplier = null;
            }

            $modelTempBarangMasuk = new Modeltempbarangmasuk();
            $existingRow = null;
            $errorQtyPoKeluar = null;

            $validation = \Config\Services::validation();

            $rules = [
                'gudang' => [
                    'rules' => 'required',
                    'label' => 'Gudang',
                    'errors' => [
                        'required' => '{field} belum di pilih'
                    ]
                ],
            ];

            if ($sumberProduk === 'beli') {
                $rules['po_keluar_id'] = [
                    'rules' => 'required|is_natural_no_zero',
                    'label' => 'No PO',
                    'errors' => [
                        'required' => '{field} belum di pilih',
                        'is_natural_no_zero' => '{field} belum di pilih',
                    ]
                ];
                $rules['idsupplier'] = [
                    'rules' => 'required',
                    'label' => 'Supplier',
                    'errors' => [
                        'required' => '{field} belum di pilih',
                    ]
                ];
            }

            $valid = $this->validate($rules);
            if (!$valid) {
                $json = [
                    'error1' => 'Maaf, ' . $validation->listErrors() . ''
                ];
            } elseif (db_connect()->table('barangmasuk')->where('faktur', $nofaktur)->countAllResults() > 0) {
                $json = [
                    'error1' => 'Maaf, No PO/Nomor transaksi sudah terpakai'
                ];
            } else {
                $existingRow = $modelTempBarangMasuk
                    ->where('detfaktur', $nofaktur)
                    ->where('idbarang', $idbarang)
                    ->first();
                $errorQtyPoKeluar = $this->validasiQtyPoKeluar(
                    $poKeluarId,
                    (string) $kodebarang,
                    (float) $jml,
                    $existingRow ? (float) $existingRow['detjml'] : 0
                );

                if ($errorQtyPoKeluar !== null) {
                    $json = [
                        'error1' => $errorQtyPoKeluar
                    ];
                } elseif ($existingRow) {
                    $newJml = $existingRow['detjml'] + $jml;
                    $newSubtotal = $existingRow['detsubtotal'] + ($jml * $berat);

                    $modelTempBarangMasuk->update($existingRow['id'], [
                        'detjml' => $newJml,
                        'detsubtotal' => $newSubtotal
                    ]);
                    $json = [
                        'sukses' => 'Item berhasil di tambahkan',
                        'nofaktur' => $nofaktur,
                    ];
                } else {
                    $modelTempBarangMasuk->insert([
                        'detfaktur' => $nofaktur,
                        'tgl' => $tglfaktur,
                        'detbrgkode' => $kodebarang,
                        'detbrgnama' => $namabarang,
                        'detmatkode' => $idmaterial,
                        'idbarang' => $idbarang,
                        'detberat' => $berat,
                        'detjml' => $jml,
                        'detsubtotal' => intval($jml) * $berat
                    ]);
                    $json = [
                        'sukses' => 'Item berhasil di tambahkan',
                        'nofaktur' => $nofaktur,
                    ];
                }
            }
            echo json_encode($json);
        } else {
            exit('Maaf tidak bisa dipanggil');
        }
    }

    function hapusItem()
    {
        if ($this->request->isAJAX()) {
            $id = $this->request->getPost('id');

            $modelTempBarangMasuk = new Modeltempbarangmasuk();
            $modelTempBarangMasuk->delete($id);

            $json = [
                'sukses' => 'Item Berhasil di Hapus'
            ];
            echo json_encode($json);
        }
    }

    public function modalCariBarang()
    {
        if ($this->request->isAJAX()) {
            $json = [
                'data' => view('barangmasuk/modalcaribarang')
            ];
            echo json_encode($json);
        }
    }

    public function pilihanPoKeluarAktif()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        return $this->response->setJSON([
            'data' => (new PoKeluar())->daftarPoAktifUntukTipe('produk'),
        ]);
    }

    public function itemPoKeluar()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $poKeluarId = (int) $this->request->getPost('po_keluar_id');
        $db = db_connect();

        $header = $db->table('po_keluar')->where('id', $poKeluarId)->get()->getRowArray();
        if (!$header) {
            return $this->response->setJSON(['error' => 'PO Keluar tidak ditemukan.']);
        }

        $items = $db->table('detail_po_keluar dpk')
            ->select('dpk.kode_item AS kodebarang, COALESCE(b.brgnama, dpk.nama_item) AS brgnama, dpk.qty_pesan, dpk.qty_masuk')
            ->join('barang b', 'b.brgkode = dpk.kode_item', 'left')
            ->where('dpk.po_keluar_id', $poKeluarId)
            ->where('dpk.tipe_item', 'produk')
            ->orderBy('dpk.id', 'ASC')
            ->get()->getResultArray();

        $ngByItem = $replacementByItem = [];
        if (strtoupper((string) ($header['status'] ?? '')) === 'NG' && $db->tableExists('retur_produk_detail')) {
            foreach ($db->table('retur_produk_detail')->select('kode_barang, SUM(qty_retur) AS qty_ng', false)->where('po_keluar_id', $poKeluarId)->groupBy('kode_barang')->get()->getResultArray() as $r) $ngByItem[(string) $r['kode_barang']] = (float) $r['qty_ng'];
            if ($db->fieldExists('sumber', 'barangmasuk')) foreach ($db->table('detail_barangmasuk d')->select('d.detbrgkode, SUM(d.detjml) AS qty_pengganti', false)->join('barangmasuk b', 'b.faktur = d.detfaktur')->where('d.detfaktur IS NOT NULL', null, false)->where('b.po_keluar_id', $poKeluarId)->where('b.sumber', 'retur_ng')->groupBy('d.detbrgkode')->get()->getResultArray() as $r) $replacementByItem[(string) $r['detbrgkode']] = (float) $r['qty_pengganti'];
        }
        foreach ($items as &$item) {
            $item['sisa'] = strtoupper((string) ($header['status'] ?? '')) === 'NG' ? max(0, ($ngByItem[(string) $item['kodebarang']] ?? 0) - ($replacementByItem[(string) $item['kodebarang']] ?? 0)) : max(0, (float) $item['qty_pesan'] - (float) $item['qty_masuk']);
        }
        unset($item);
        $items = array_values(array_filter($items, static fn(array $item): bool => (float) ($item['sisa'] ?? 0) > 0));

        return $this->response->setJSON([
            'sukses' => [
                'idsupplier' => $header['idsup'],
                'namasupplier' => $header['supplier_nama'],
                'no_po' => $header['no_po'],
                'items' => $items,
            ],
        ]);
    }

    public function selesaiTransaksi()
    {
        if ($this->request->isAJAX()) {
            $nofaktur = $this->nomorProdukMasukDariRequest();
            $tglfaktur = $this->request->getPost('tglfaktur');
            $idsupplier = $this->request->getPost('idsupplier');
            $gudang = $this->resolveGudangId($this->request->getPost('gudang'));
            $totalberatbarang = $this->request->getPost('totalberatbarang');
            $poKeluarId = (int) $this->request->getPost('po_keluar_id');
            $sumberProdukInput = $this->request->getPost('sumber_produk');
            $sumberProduk = in_array($sumberProdukInput, ['adjustment', 'produksi', 'produksi_pelanggan', 'retur_ng'], true) ? $sumberProdukInput : 'beli';
            if ($sumberProduk === 'adjustment' || $sumberProduk === 'produksi' || $sumberProduk === 'produksi_pelanggan') {
                $poKeluarId = 0;
                $idsupplier = null;
            }

            if ($sumberProduk === 'beli' && $poKeluarId <= 0) {
                echo json_encode(['error' => 'No PO wajib dipilih untuk sumber Beli dari Supplier']);
                return;
            }

            if ($gudang === null) {
                echo json_encode(['error' => 'Maaf, Lokasi Gudang tidak valid. Silakan pilih ulang lokasi gudangnya.']);
                return;
            }

            $modelTemp = new Modeltempbarangmasuk();
            $dataTemp = $modelTemp->getWhere(['detfaktur' => $nofaktur]);
            $rowsTemp = $dataTemp->getResultArray();

            $fakturTerpakai = $nofaktur !== '' && db_connect()->table('barangmasuk')
                ->where('faktur', $nofaktur)
                ->countAllResults() > 0;

            if ($fakturTerpakai) {
                $json = [
                    'error' => "No PO {$nofaktur} sudah digunakan"
                ];
            } elseif ($dataTemp->getNumRows() == 0) {
                $json = [
                    'error' => 'Maaf, data item untuk faktur ini belum ada'
                ];
            } else {
                $errorQtyPoKeluar = $this->validasiTempPoKeluar($poKeluarId, $rowsTemp);
                if ($errorQtyPoKeluar !== null) {
                    echo json_encode(['error' => $errorQtyPoKeluar]);
                    return;
                }

                $itemTanpaStok = [];
                foreach ($rowsTemp as $row) {
                    $ada = (new Modelstok())
                        ->where('kodebarang', $row['detbrgkode'])
                        ->where('gudang', $gudang)
                        ->countAllResults() > 0;
                    if (!$ada) {
                        $itemTanpaStok[] = $row['detbrgkode'];
                    }
                }

                if ($itemTanpaStok) {
                    $json = [
                        'error' => 'Data stok untuk produk ' . implode(', ', array_unique($itemTanpaStok)) . ' di gudang ini belum ada.'
                    ];
                    echo json_encode($json);
                    return;
                }

                $db = db_connect();
                $db->transStart();

                $totalSubTotal = 0;
                foreach ($rowsTemp as $total) {
                    $totalSubTotal += intval($total['detsubtotal']);
                }

                $totalqtymasuk = 0;
                foreach ($rowsTemp as $totqty) {
                    $totalqtymasuk += intval($totqty['detjml']);
                }

                $modelBarangMasuk = new Modelbarangmasuk();

                $modelBarangMasuk->insert([
                    'faktur' => $nofaktur,
                    'po_keluar_id' => $poKeluarId ?: null,
                    'sumber' => $sumberProduk,
                    'tglfaktur' => $tglfaktur,
                    'idsup' => $idsupplier,
                    'gudang' => $gudang,
                    'qtymasuk' => $totalqtymasuk,
                    'totalberatbarang' => $totalberatbarang,
                ]);

                $fieldDetail = [];
                $fieldStok = [];

                foreach ($rowsTemp as $row) {
                    $modelStok = new Modelstok();
                    $stokData = $modelStok->select('id')
                        ->where('kodebarang', $row['detbrgkode'])
                        ->where('gudang', $gudang)
                        ->first();

                    $idbarang = $stokData['id'];

                    $fieldDetail[] = [
                        'detfaktur' => $row['detfaktur'],
                        'tgl' => $tglfaktur,
                        'detbrgkode' => $row['detbrgkode'],
                        'detbrgnama' => $row['detbrgnama'],
                        'detmatkode' => $this->normalisasiMaterialAdjustment($row['detmatkode'] ?? null),
                        'idbarang' => $idbarang,
                        'idsup' => $idsupplier,
                        'gudang' => $gudang,
                        'detberat' => $row['detberat'],
                        'detjml' => $row['detjml'],
                        'detsubtotal' => $row['detsubtotal']
                    ];

                    $fieldStok[] = [
                        'kodebarang' => $row['detbrgkode'],
                        'gudang' => $gudang,
                        'stok' => $row['detjml'],
                    ];

                    if ($poKeluarId) {
                        (new PoKeluar())->terimaQty($poKeluarId, 'produk', (string) $row['detbrgkode'], (float) $row['detjml']);
                    }
                }
                // echo '<pre>';
                // print_r($fieldDetail);
                // print_r($fieldStok);
                // echo '</pre>';
                // die();
                $modelDetail = new Modeldetailbarangmasuk();
                $modelDetail->insertBatch($fieldDetail);
                if ($sumberProduk === 'retur_ng' && $poKeluarId) {
                    (new PoKeluar())->refreshStatusAfterReplacement($poKeluarId);
                }

                $modelStok = new Modelstok();
                $modelStok->updateOrInsertBatch($fieldStok);

                if ($sumberProduk === 'produksi_pelanggan') {
                    $this->buatBatchProduksiMaterialPelanggan($tglfaktur, $gudang, $rowsTemp);
                }

                $modelTemp->hapusData($nofaktur);
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
                    ? ['error' => 'Transaksi Produk Masuk gagal disimpan. Tidak ada data yang diubah.']
                    : ['sukses' => 'Transaksi Berhasil di Simpan'];

                echo json_encode($json);
            }
        } else {
            exit('Maaf tidak bisa dipanggil');
        }
    }

    function hapusTransaksi()
    {
        if ($this->request->isAJAX()) {
            $faktur = $this->request->getPost('faktur');

            $modelBarangMasuk = new Modelbarangmasuk();

            $db = \Config\Database::connect();

            $header = $db->table('barangmasuk')->where('faktur', $faktur)->get()->getRowArray();
            $details = $db->table('detail_barangmasuk')->where('detfaktur', $faktur)->get()->getResultArray();

            $db->transStart();

            $db->table('detail_barangmasuk')->delete(['detfaktur' => $faktur]);
            $db->table('ngdata')->delete(['detfaktur' => $faktur]);
            $modelBarangMasuk->delete($faktur);

            $poKeluarId = (int) ($header['po_keluar_id'] ?? 0);
            if ($poKeluarId > 0) {
                $poKeluar = new PoKeluar();
                foreach ($details as $detail) {
                    $poKeluar->batalkanQty($poKeluarId, 'produk', (string) $detail['detbrgkode'], (float) $detail['detjml']);
                }
            }

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
                ? ['error' => 'Transaksi Produk Masuk gagal dihapus. Tidak ada data yang diubah.']
                : ['sukses' => 'Transaksi berhasil di Hapus'];
            echo json_encode($json);
        }
    }

    public function edit($faktur)
    {
        $modelBarangMasuk = new Modelbarangmasuk();
        $cekFaktur = $modelBarangMasuk->cekFaktur($faktur);

        if ($cekFaktur->getNumRows() > 0) {
            $row = $cekFaktur->getRowArray();

            $data = [
                'nofaktur' => $row['faktur'],
                'tanggal' => $row['tglfaktur'],
                'namasupplier' => $row['supnama'],
                'idsupplier' => $row['supid'],
                'gudangnama' => $row['gdgnama'],
                'gdgid' => $row['gdgid'],
                'databarang' => (new Modelbarang())->select('brgkode, brgnama')->orderBy('brgkode', 'ASC')->findAll(),
            ];

            return view('barangmasuk/formedit', $data);
        } else {
            exit('Data tidak ditemukan');
        }
    }

    function ambilTotalBerat()
    {
        if ($this->request->isAJAX()) {
            $nofaktur = $this->request->getPost('nofaktur');
            $modelDetail = new Modeldetailbarangmasuk();
            $totalBerat = $modelDetail->ambilTotalBerat($nofaktur);

            $json = [
                'totalberat' => "Total : " . number_format($totalBerat, 4, ",", ".") . " " . "KG"
            ];
            echo json_encode($json);
        }
    }

    function tampilDataDetail()
    {
        if ($this->request->isAJAX()) {
            $nofaktur = $this->request->getPost('nofaktur');

            $modelDetail = new Modeldetailbarangmasuk();
            $dataTemp = $modelDetail->tampilDataTemp($nofaktur);
            $data = [
                'tampildata' => $dataTemp
            ];

            $json = [
                'data' => view('barangmasuk/datadetail', $data)
            ];
            echo json_encode($json);
        }
    }

    function hapusItemDetail()
    {
        if ($this->request->isAJAX()) {
            $id = $this->request->getPost('id');
            $tglfaktur = $this->request->getPost('tglfaktur');
            $nofaktur = $this->request->getPost('nofaktur');
            $kodebarang = $this->request->getPost('kodebarang');
            $idsupplier = $this->request->getPost('idsupplier');
            $idmaterial = $this->normalisasiMaterialAdjustment($this->request->getPost('idmaterial'));

            $modelDetail = new Modeldetailbarangmasuk();
            $modelNg = new Modelng();
            $modelBarangMasuk = new Modelbarangmasuk();

            $rowData = $modelDetail->find($id);
            $noFaktur = $rowData['detfaktur'];

            $stokData = $modelNg->select('id')
                ->where('detfaktur', $nofaktur)
                ->where('detbrgkode', $kodebarang)
                ->where('tgl', $tglfaktur)
                ->where('idsup', $idsupplier)
                ->where('matjenis', $idmaterial)
                ->first();

            $existingRow = $modelNg->where('matjenis', $idmaterial)
                ->where('detfaktur', $noFaktur)
                ->where('detbrgkode', $kodebarang)->first();

            $db = db_connect();

            $header = $db->table('barangmasuk')->where('faktur', $noFaktur)->get()->getRowArray();

            $db->transStart();

            if ($stokData) {
                $materialid = $stokData['id'];
            }
            if ($existingRow) {
                $modelNg->delete($materialid);
            }
            $modelDetail->delete($id);

            $poKeluarId = (int) ($header['po_keluar_id'] ?? 0);
            if ($poKeluarId > 0) {
                (new PoKeluar())->batalkanQty($poKeluarId, 'produk', (string) $rowData['detbrgkode'], (float) $rowData['detjml']);
            }

            $totalBerat = $modelDetail->ambilTotalBerat($noFaktur);

            $modelBarangMasuk->update($noFaktur, [
                'totalberatbarang' => $totalBerat
            ]);
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

            return $this->response->setJSON(
                $db->transStatus() === false
                    ? ['error' => 'Item Produk Masuk gagal dihapus. Tidak ada data yang diubah.']
                    : ['sukses' => 'Item Berhasil di Hapus']
            );
        }
    }

    public function editItem()
    {
        if ($this->request->isAJAX()) {
            $iddetail = $this->request->getPost('iddetail');
            $idgudang = $this->resolveGudangId($this->request->getPost('idgudang'));
            $kodebarang = $this->request->getPost('kodebarang');
            $jml = $this->request->getPost('jml');

            $modelDetail = new Modeldetailbarangmasuk();
            $modelBarangMasuk = new Modelbarangmasuk();
            $modelStok = new Modelstok();

            $rowData = $modelDetail->find($iddetail);
            if (!$rowData) {
                return $this->response->setJSON(['error' => 'Data tidak ditemukan']);
            }

            $noFaktur = $rowData['detfaktur'];
            $berat = $rowData['detberat'];
            $jumlahLama = $rowData['detjml'];

            $stokPerubahan = $jml - $jumlahLama;
            $stokBaru = $modelStok->getStokByGudang($kodebarang, $idgudang) + $stokPerubahan;

            if ($jml < 1) {
                return $this->response->setJSON(['error' => 'Jumlah tidak boleh kurang dari 1']);
            }

            $barangId = $modelStok->getBarangIdByKodeGudang($kodebarang, $idgudang);
            $db = db_connect();
            $db->transStart();

            $modelStok->update($barangId, ['stok' => $stokBaru]);

            $modelDetail->update($iddetail, [
                'detjml' => $jml,
                'detsubtotal' => $jml * $berat
            ]);

            $totalBerat = $modelDetail->ambilTotalBerat($noFaktur);
            $totalQty = $modelDetail->ambilTotalQty($noFaktur);

            $modelBarangMasuk->update($noFaktur, [
                'totalberatbarang' => $totalBerat,
                'qtymasuk' => $totalQty
            ]);
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

            return $this->response->setJSON(
                $db->transStatus() === false
                    ? ['error' => 'Item Produk Masuk gagal diperbarui. Tidak ada data yang diubah.']
                    : ['sukses' => 'Item Berhasil di Update']
            );
        }
    }

    function simpanItemDetail()
    {
        if ($this->request->isAJAX()) {
            $nofaktur = $this->request->getPost('nofaktur');
            $idgudang = $this->resolveGudangId($this->request->getPost('idgudang'));
            $gdgid = $this->request->getPost('gdgid');
            $kodebarang = $this->request->getPost('kodebarang');
            $jml = $this->request->getPost('jml');
            $berat = $this->request->getPost('berat');
            $idbarang = $this->request->getPost('idbarang');
            $kodebarang = $this->request->getPost('kodebarang');
            $namabarang = $this->request->getPost('namabarang');
            $idmaterial = $this->normalisasiMaterialAdjustment($this->request->getPost('idmaterial'));
            $idsupplier = $this->request->getPost('idsupplier');
            $tglfaktur = $this->request->getPost('tglfaktur');

            $modelTempBarangMasuk = new Modeldetailbarangmasuk();
            $modelBarangMasuk = new Modelbarangmasuk();

            $modelStok = new Modelstok();

            $stokPerubahan = $jml;
            $stokBaru = $modelStok->getStokByGudang($kodebarang, $idgudang) + $stokPerubahan;

            $db = db_connect();
            $db->transStart();

            $modelStok->update($modelStok->getBarangIdByKodeGudang($kodebarang, $idgudang), [
                'stok' => $stokBaru
            ]);

            $existingRow = $modelTempBarangMasuk->where('idbarang', $idbarang)->where('detfaktur', $nofaktur)
                ->first();

            if ($existingRow) {
                $newJml = $existingRow['detjml'] + $jml;
                $newSubtotal = $existingRow['detsubtotal'] + ($jml * $berat);

                $modelTempBarangMasuk->update($existingRow['id'], [
                    'detjml' => $newJml,
                    'detsubtotal' => $newSubtotal
                ]);
            } else {
                $modelTempBarangMasuk->insert([
                    'detfaktur' => $nofaktur,
                    'tgl' => $tglfaktur,
                    'detbrgkode' => $kodebarang,
                    'idbarang' => $idbarang,
                    'detbrgnama' => $namabarang,
                    'detmatkode' => $idmaterial,
                    'idsup' => $idsupplier,
                    'gudang' => $gdgid,
                    'detberat' => $berat,
                    'detjml' => $jml,
                    'detsubtotal' => intval($jml) * $berat
                ]);
            }
            $totalBerat = $modelTempBarangMasuk->ambilTotalBerat($nofaktur);
            $totalQty = $modelTempBarangMasuk->ambilTotalQty($nofaktur);

            $modelBarangMasuk->update($nofaktur, [
                'qtymasuk' => $totalQty,
                'totalberatbarang' => $totalBerat
            ]);
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
                ? ['error' => 'Item Produk Masuk gagal ditambahkan. Tidak ada data yang diubah.']
                : ['sukses' => 'Item berhasil di tambahkan'];
            echo json_encode($json);
        } else {
            exit('Maaf tidak bisa dipanggil');
        }
    }
}
