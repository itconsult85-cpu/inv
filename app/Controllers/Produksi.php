<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Modelbarang;
use App\Models\Modelgudang;
use App\Models\Modelstok;
use App\Models\ModelStokMaterial;
use App\Models\ModelDataStokMaterial;
use App\Models\ModelProduksi;
use App\Models\ModelProduksiProduk;
use App\Models\ModelDetailProduksi;
use App\Models\ModelTempProduksi;
use \Hermawan\DataTables\DataTable;
use Config\Services;

class Produksi extends BaseController
{
    private $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    public function data()
    {
        return view('produksi/viewdata');
    }

    /**
     * Form input produksi minimalis: staf cuma isi produk + qty diproduksi,
     * nggak perlu tahu/isi rincian material satu-satu. Material yang kepakai
     * dihitung & disimpan otomatis dari data Kebutuhan Material lewat
     * simpanOtomatis().
     */
    public function input()
    {
        $modelgudang = new Modelgudang();
        return view('produksi/inputotomatis', [
            'datagudang' => $modelgudang->findAll(),
        ]);
    }

    /**
     * Lookup produk khusus form produksi.
     *
     * Jangan memakai Barangmasuk::ambilDataBarang() di sini karena method lama
     * mewajibkan baris tabel berat. Produk dengan berat per material pada
     * berat_material tetap valid walaupun baris berat lama belum ada.
     */
    public function ambilDataBarangProduksi()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $kodeProduk = trim((string) ($this->request->getPost('kodebarang') ?? ''));
        $gudang = (int) ($this->request->getPost('idgudang') ?? $this->request->getPost('gudang'));
        $produk = (new Modelbarang())->find($kodeProduk);

        if (!$produk) {
            return $this->response->setJSON(['error' => 'Maaf data barang tidak ditemukan.']);
        }

        $modelStok = new Modelstok();
        return $this->response->setJSON([
            'sukses' => [
                'namabarang' => $produk['brgnama'],
                'stok' => $gudang > 0 ? $modelStok->getStokByGudang($kodeProduk, $gudang) : 0,
                'harga' => $modelStok->getHargaByKodeBarang($kodeProduk),
                'idbarang' => $gudang > 0 ? $modelStok->getBarangIdByKodeGudang($kodeProduk, $gudang) : null,
            ],
        ]);
    }

    /**
     * Mengembalikan pilihan material untuk satu produk. Material pertama di
     * barang.brgmat adalah material inti/default; sisanya adalah alternatif.
     * Berat disajikan per 1 pcs produk dan stok disajikan untuk gudang yang
     * sedang dipilih.
     */
    public function materialProduk()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $kodeProduk = trim((string) $this->request->getPost('kodebarang'));
        $gudang = (int) $this->request->getPost('gudang');
        if ($kodeProduk === '') {
            return $this->response->setJSON(['error' => 'Produk belum dipilih.']);
        }

        $tanpaBerat = $this->produkTanpaBerat($kodeProduk);
        $rows = $this->ambilRelasiMaterialProduk($kodeProduk);
        if (!$rows && $tanpaBerat) {
            return $this->response->setJSON([
                'sukses' => [
                    'materials' => [],
                    'default_material_id' => null,
                    'tanpa_berat' => true,
                ],
            ]);
        }
        if (!$rows) {
            return $this->response->setJSON([
                'error' => "Produk {$kodeProduk} belum memiliki material inti/alternatif. Lengkapi data produk terlebih dahulu.",
            ]);
        }

        $modelStokMaterial = new ModelStokMaterial();
        $materials = [];
        foreach ($rows as $row) {
            $berat = $row['berat_material'];
            $satuanCocok = $berat !== null
                && (int) $row['satuan_berat_id'] === (int) $row['matsatid'];
            $materials[] = [
                'materialid' => (int) $row['matid'],
                'kodematerial' => (string) $row['matkode'],
                'namamaterial' => (string) $row['matnama'],
                'satuan' => (string) ($row['satuan_material'] ?? ''),
                'berat_per_pcs' => $berat !== null ? (float) $berat : null,
                'stok' => $gudang > 0
                    ? (float) $modelStokMaterial->getStokByMaterialIdGudang($row['matid'], $gudang)
                    : null,
                'default' => (int) $row['is_default'] === 1,
                'satuan_sesuai' => $satuanCocok,
            ];
        }

        $default = $materials[0]['materialid'] ?? null;
        foreach ($materials as $material) {
            if ($material['default']) {
                $default = $material['materialid'];
                break;
            }
        }

        return $this->response->setJSON([
            'sukses' => [
                'materials' => $materials,
                'default_material_id' => $default,
                'tanpa_berat' => $tanpaBerat,
            ],
        ]);
    }

    public function simpanOtomatis()
    {
        if (!$this->request->isAJAX()) {
            exit('Maaf tidak bisa dipanggil');
        }

        $noProduksi = trim((string) $this->request->getPost('no_produksi'));
        $tglProduksi = $this->request->getPost('tgl_produksi');
        $gudang = $this->request->getPost('gudang');
        $kodeProduk = trim((string) $this->request->getPost('kodebarang'));
        $namaProduk = trim((string) $this->request->getPost('namabarang'));
        $qtyProduk = (float) $this->request->getPost('qty_produk');

        if ($tglProduksi === '' || !$gudang || $kodeProduk === '' || $qtyProduk <= 0) {
            echo json_encode(['error' => 'Tanggal, Gudang, Produk, dan Qty Diproduksi harus diisi dengan benar.']);
            return;
        }

        $modelProduksi = new ModelProduksi();
        if ($noProduksi !== '' && !$modelProduksi->find($noProduksi)) {
            echo json_encode(['error' => 'No Produksi batch tidak ditemukan. Muat ulang halaman.']);
            return;
        }

        $modelProduksiProduk = new ModelProduksiProduk();
        $existingLine = $noProduksi !== ''
            ? $modelProduksiProduk->where('no_produksi', $noProduksi)->where('kode_produk', $kodeProduk)->first()
            : null;

        $adaStokProduk = (new Modelstok())->where('kodebarang', $kodeProduk)->where('gudang', $gudang)->countAllResults() > 0;
        if (!$adaStokProduk) {
            echo json_encode(['error' => "Data stok untuk produk {$kodeProduk} di gudang ini belum ada. Tambahkan dulu lewat menu Barang Masuk sebelum bisa diproduksi di sini."]);
            return;
        }

        $tanpaBerat = $this->produkTanpaBerat($kodeProduk);
        $detailProduksi = [];
        $peringatan = [];

        if (!$tanpaBerat) {
            $relasi = $this->ambilRelasiMaterialProduk($kodeProduk);
            if (!$relasi) {
                echo json_encode(['error' => "Produk {$kodeProduk} belum punya material inti/alternatif. Lengkapi dulu di form Produk."]);
                return;
            }

            $materialIdInput = (int) $this->request->getPost('materialid');
            $materialDipilih = null;
            foreach ($relasi as $row) {
                if ((int) $row['matid'] === $materialIdInput) {
                    $materialDipilih = $row;
                    break;
                }
            }
            if ($materialDipilih === null && $materialIdInput > 0) {
                echo json_encode(['error' => 'Material yang dipilih tidak terdaftar sebagai material inti/alternatif produk ini.']);
                return;
            }
            if ($materialDipilih === null) {
                foreach ($relasi as $row) {
                    if ((int) $row['is_default'] === 1) {
                        $materialDipilih = $row;
                        break;
                    }
                }
            }
            $materialDipilih ??= $relasi[0];

            $berat = $materialDipilih['berat_material'];
            if ($berat === null || !is_numeric($berat) || (float) $berat <= 0) {
                echo json_encode(['error' => "Berat material {$materialDipilih['matnama']} belum diisi untuk produk ini."]);
                return;
            }
            if ((int) $materialDipilih['satuan_berat_id'] !== (int) $materialDipilih['matsatid']) {
                echo json_encode(['error' => "Satuan berat produk untuk material {$materialDipilih['matkode']} tidak sama dengan satuan materialnya."]);
                return;
            }

            $qtyMaterial = $qtyProduk * (float) $berat;
            $modelStokMaterial = new ModelStokMaterial();
            $stokTersedia = (float) $modelStokMaterial->getStokByMaterialIdGudang($materialDipilih['matid'], $gudang);
            if ($stokTersedia < $qtyMaterial) {
                echo json_encode([
                    'error' => "Stok material {$materialDipilih['matnama']} tidak cukup (sisa {$stokTersedia}, butuh {$qtyMaterial}). Pilih material alternatif yang stoknya tersedia.",
                ]);
                return;
            }

            $detailProduksi[] = [
                'kode_material' => $materialDipilih['matkode'],
                'materialid' => $materialDipilih['matid'],
                'nama_material' => $materialDipilih['matnama'],
                'satuan' => $materialDipilih['satuan_material'],
                'qty_material' => $qtyMaterial,
            ];
        }

        $this->db->transStart();

        if ($noProduksi === '') {
            do {
                $noProduksi = 'PRD-' . date('Ymd-His') . '-' . random_int(100, 999);
            } while ($modelProduksi->find($noProduksi));

            $modelProduksi->insert([
                'no_produksi' => $noProduksi,
                'tgl_produksi' => $tglProduksi,
                'gudang' => $gudang,
                'iduser' => session()->get('userid'),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } else {
            $modelProduksi->update($noProduksi, [
                'tgl_produksi' => $tglProduksi,
                'gudang' => $gudang,
            ]);
        }

        if ($existingLine) {
            $produksiProdukId = (int) $existingLine['id'];
            $qtyProdukTotal = (float) $existingLine['qty_produk'] + $qtyProduk;
            $modelProduksiProduk->update($produksiProdukId, [
                'nama_produk' => $namaProduk,
                'qty_produk' => $qtyProdukTotal,
            ]);
        } else {
            $qtyProdukTotal = $qtyProduk;
            $produksiProdukId = $modelProduksiProduk->insert([
                'no_produksi' => $noProduksi,
                'kode_produk' => $kodeProduk,
                'nama_produk' => $namaProduk,
                'qty_produk' => $qtyProduk,
                'created_at' => date('Y-m-d H:i:s'),
            ], true);
        }

        if ($detailProduksi) {
            $modelDetailProduksi = new ModelDetailProduksi();
            foreach ($detailProduksi as $row) {
                $existingDetail = $existingLine
                    ? $modelDetailProduksi->where('produksi_produk_id', $produksiProdukId)->where('materialid', $row['materialid'])->first()
                    : null;

                if ($existingDetail) {
                    $modelDetailProduksi->update($existingDetail['id'], [
                        'qty_material' => (float) $existingDetail['qty_material'] + $row['qty_material'],
                    ]);
                } else {
                    $modelDetailProduksi->insert(array_merge(['no_produksi' => $noProduksi, 'produksi_produk_id' => $produksiProdukId], $row));
                }

                $stokBerhasilDikurangi = $this->db->table('stokmaterial')
                    ->where('materialid', $row['materialid'])
                    ->where('gudang', $gudang)
                    ->where('stok >=', (float) $row['qty_material'])
                    ->set('stok', 'stok - ' . (float) $row['qty_material'], false)
                    ->update();
                if (!$stokBerhasilDikurangi || $this->db->affectedRows() < 1) {
                    $this->db->transRollback();
                    echo json_encode(['error' => "Stok material {$row['nama_material']} berubah atau tidak cukup saat transaksi disimpan. Silakan muat ulang lalu pilih material lain."]);
                    return;
                }
            }
        }

        (new Modelstok())->updateOrInsertBatch([
            ['kodebarang' => $kodeProduk, 'gudang' => $gudang, 'stok' => $qtyProduk],
        ]);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            echo json_encode(['error' => 'Transaksi Produksi gagal disimpan. Tidak ada data yang diubah.']);
            return;
        }

        $pesan = $tanpaBerat
            ? "Produksi {$qtyProduk} pcs {$namaProduk} berhasil disimpan (tanpa pemakaian material)."
            : "Produksi {$qtyProduk} pcs {$namaProduk} berhasil disimpan.";

        $json = [
            'sukses' => $pesan,
            'no_produksi' => $noProduksi,
            'produksi_produk_id' => $produksiProdukId,
            'kodebarang' => $kodeProduk,
            'namabarang' => $namaProduk,
            'qty_produk' => $qtyProdukTotal,
            'detail' => (new ModelDetailProduksi())->where('produksi_produk_id', $produksiProdukId)->findAll(),
        ];
        if ($peringatan) {
            $json['peringatan'] = $peringatan;
        }
        echo json_encode($json);
    }

    public function listData()
    {
        if ($this->request->isAJAX()) {
            $tglawal = $this->request->getPost('tglawal');
            $tglakhir = $this->request->getPost('tglakhir');

            $builder = $this->db->table('produksi_produk pp')
                ->select("pp.id AS produksi_produk_id, p.no_produksi, p.tgl_produksi, pp.kode_produk, pp.nama_produk, pp.qty_produk, g.gdgnama, p.keterangan, COALESCE(u.usernama, (SELECT u_lama.usernama FROM users u_lama WHERE u_lama.id = CAST(p.iduser AS UNSIGNED) LIMIT 1), NULLIF(p.iduser, ''), '-') AS user_input", false)
                ->join('produksi p', 'p.no_produksi = pp.no_produksi', 'inner')
                ->join('gudang g', 'g.gdgid = p.gudang', 'left')
                ->join('users u', 'BINARY u.userid = BINARY CAST(p.iduser AS CHAR)', 'left', false)
                // Tampilkan transaksi dengan tanggal input terbaru di bagian atas.
                // Nomor batch dan ID menjadi tie-breaker agar urutannya stabil.
                ->orderBy('p.tgl_produksi', 'DESC')
                ->orderBy('p.no_produksi', 'DESC')
                ->orderBy('pp.id', 'DESC');

            if ($tglawal && $tglakhir) {
                $builder->where('p.tgl_produksi >=', $tglawal)->where('p.tgl_produksi <=', $tglakhir);
            }

            return DataTable::of($builder)
                ->addNumbering('nomor')
                ->add('aksi', function ($row) {
                    return "<button type=\"button\" class=\"btn btn-sm btn-info\" onclick=\"editProduksi('" . sha1($row->no_produksi) . "')\"><i class=\"fa fa-edit\"></i></button>&nbsp;<button type=\"button\" class=\"btn btn-sm btn-danger\" onclick=\"hapusProduksi(" . $row->produksi_produk_id . ")\"><i class=\"fa fa-trash-alt\"></i></button>";
                })
                ->format('keterangan', function ($value) {
                    return $value !== null && $value !== '' ? esc($value) : '-';
                })
                ->format('user_input', function ($value) {
                    return esc($value !== null && $value !== '' ? $value : '-');
                })
                ->format('qty_produk', function ($value) {
                    return number_format($value, 0, ',', '.');
                })
                ->format('tgl_produksi', function ($value) {
                    return date('d-m-Y', strtotime($value));
                })
                ->toJson(true);
        }
    }

    public function ambilDataMaterial()
    {
        if ($this->request->isAJAX()) {
            $materialid = $this->request->getPost('materialid');
            $idgudang = $this->request->getPost('idgudang');

            $modelStokMaterial = new ModelStokMaterial();
            $cekData = $modelStokMaterial->where('materialid', $materialid)->first();

            if ($cekData == null) {
                echo json_encode(['error' => 'Maaf data material tidak ditemukan']);
                return;
            }

            $stokGudang = $modelStokMaterial->getStokByMaterialIdGudang($materialid, $idgudang);
            $satuan = $this->db->table('satuan')->select('satnama')->where('satid', $cekData['materialsatid'])->get()->getRowArray();

            echo json_encode([
                'sukses' => [
                    'materialid' => $cekData['materialid'],
                    'kodematerial' => $cekData['kodematerial'],
                    'namamaterial' => $cekData['namamaterial'],
                    'satuan' => $satuan['satnama'] ?? '',
                    'stok' => $stokGudang,
                ],
            ]);
        }
    }

    public function modalCariMaterial()
    {
        if ($this->request->isAJAX()) {
            echo json_encode(['data' => view('produksi/modalcarimaterial')]);
        }
    }

    /**
     * Hitung otomatis material yang dipakai berdasarkan data Kebutuhan Material
     * (BOM di tabel berat_material: qty material per 1 pcs produk), lalu isi
     * ke temp_produksi -- menggantikan cara lama yang mengharuskan tambah
     * material satu-satu secara manual. Baris temp untuk no_produksi ini
     * di-reset dulu supaya hasilnya konsisten kalau tombol dipencet ulang
     * (misalnya qty produk diubah). Material yang BOM-nya belum lengkap
     * (berat belum diisi / satuan tidak cocok) dilewati dan dilaporkan
     * sebagai peringatan, tidak menggagalkan seluruh perhitungan.
     */
    public function hitungOtomatis()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $noProduksi = trim((string) $this->request->getPost('no_produksi'));
        $kodeProduk = trim((string) $this->request->getPost('kodebarang'));
        $gudang = $this->request->getPost('gudang');
        $qtyProduk = (float) $this->request->getPost('qty_produk');

        if ($noProduksi === '' || $kodeProduk === '' || !$gudang || $qtyProduk <= 0) {
            echo json_encode(['error' => 'Gudang, Produk, dan Qty Diproduksi harus diisi dengan benar.']);
            return;
        }

        if ($this->produkTanpaBerat($kodeProduk)) {
            (new ModelTempProduksi())->hapusData($noProduksi);
            echo json_encode([
                'sukses' => 'Produk ini ditandai jasa/tanpa berat, jadi tidak perlu hitung material otomatis.',
                'peringatan' => [],
            ]);
            return;
        }

        $relasi = $this->ambilRelasiMaterialProduk($kodeProduk);

        if (!$relasi) {
            echo json_encode(['error' => "Produk {$kodeProduk} belum punya data Kebutuhan Material (BOM). Tambahkan material secara manual di bawah."]);
            return;
        }

        $materialIdInput = (int) $this->request->getPost('materialid');
        $materialDipilih = null;
        foreach ($relasi as $row) {
            if ($materialIdInput > 0 && (int) $row['matid'] === $materialIdInput) {
                $materialDipilih = $row;
                break;
            }
        }
        if ($materialIdInput > 0 && $materialDipilih === null) {
            echo json_encode(['error' => 'Material yang dipilih tidak terdaftar sebagai material inti/alternatif produk ini.']);
            return;
        }
        if ($materialDipilih === null) {
            foreach ($relasi as $row) {
                if ((int) $row['is_default'] === 1) {
                    $materialDipilih = $row;
                    break;
                }
            }
        }
        $relasi = [$materialDipilih ?? $relasi[0]];

        $modelTemp = new ModelTempProduksi();
        $modelTemp->hapusData($noProduksi);

        $peringatan = [];
        $ditambahkan = 0;

        foreach ($relasi as $row) {
            $berat = $row['berat_material'];
            if ($berat === null || !is_numeric($berat) || (float) $berat <= 0) {
                $peringatan[] = "Material {$row['matkode']}: berat per produk belum diisi di Kebutuhan Material, dilewati.";
                continue;
            }
            if ((int) $row['satuan_berat_id'] !== (int) $row['matsatid']) {
                $peringatan[] = "Material {$row['matkode']}: satuan berat produk tidak sama dengan satuan material, dilewati.";
                continue;
            }

            $modelTemp->insert([
                'det_no_produksi' => $noProduksi,
                'det_kode_material' => $row['matkode'],
                'det_materialid' => $row['matid'],
                'det_nama_material' => $row['matnama'],
                'det_satuan' => $row['satuan_material'],
                'det_qty' => $qtyProduk * (float) $berat,
                'gudang' => $gudang,
            ]);
            $ditambahkan++;
        }

        if ($ditambahkan === 0) {
            echo json_encode(['error' => 'Data Kebutuhan Material (BOM) untuk produk ini belum lengkap, tidak ada yang bisa dihitung otomatis. Tambahkan material secara manual di bawah.']);
            return;
        }

        echo json_encode([
            'sukses' => "{$ditambahkan} material berhasil dihitung otomatis dari data Kebutuhan Material.",
            'peringatan' => $peringatan,
        ]);
    }

    public function listDataMaterial()
    {
        $request = Services::request();
        $datamodel = new ModelDataStokMaterial($request);
        if ($request->getMethod(true) == 'POST') {
            $lists = $datamodel->get_datatables();
            $data = [];
            $no = $request->getPost('start');
            foreach ($lists as $list) {
                $no++;
                $stokGudang1 = $datamodel->where('materialid', $list->materialid)->where('gudang', 1)->first();
                $stokGudang2 = $datamodel->where('materialid', $list->materialid)->where('gudang', 2)->first();

                $stokGudang1Value = $stokGudang1['stok'] ?? 0;
                $stokGudang2Value = $stokGudang2['stok'] ?? 0;

                $tombolPilih = "<button type=\"button\" class=\"btn btn-sm btn-info\" onclick=\"pilihMaterial('" . $list->materialid . "')\">Pilih</button>";

                $data[] = [
                    $no,
                    $list->kodematerial,
                    $list->namamaterial,
                    number_format($stokGudang1Value, 0, ',', '.'),
                    number_format($stokGudang2Value, 0, ',', '.'),
                    $tombolPilih,
                ];
            }
            echo json_encode([
                'draw' => $request->getPost('draw'),
                'recordsTotal' => $datamodel->count_all(),
                'recordsFiltered' => $datamodel->count_filtered(),
                'data' => $data,
            ]);
        }
    }

    public function tampilDataTemp()
    {
        if ($this->request->isAJAX()) {
            $noProduksi = trim((string) $this->request->getPost('no_produksi'));
            $modelTemp = new ModelTempProduksi();
            $dataTemp = $modelTemp->tampilDataTemp($noProduksi);

            echo json_encode([
                'data' => view('produksi/datatemp', ['tampildata' => $dataTemp]),
                'jumlah' => $dataTemp->getNumRows(),
            ]);
        }
    }

    public function simpanItem()
    {
        if (!$this->request->isAJAX()) {
            exit('Maaf tidak bisa dipanggil');
        }

        $noProduksi = trim((string) $this->request->getPost('no_produksi'));
        $materialid = $this->request->getPost('materialid');
        $kodematerial = $this->request->getPost('kodematerial');
        $namamaterial = $this->request->getPost('namamaterial');
        $satuan = $this->request->getPost('satuan');
        $gudang = $this->request->getPost('gudang');
        $jml = (float) $this->request->getPost('jml');

        if ($noProduksi === '') {
            echo json_encode(['error' => 'No Produksi belum siap, muat ulang halaman.']);
            return;
        }
        if (!$materialid || $jml <= 0) {
            echo json_encode(['error' => 'Material dan Qty harus diisi dengan benar.']);
            return;
        }

        $modelTemp = new ModelTempProduksi();
        $existingRow = $modelTemp
            ->where('det_no_produksi', $noProduksi)
            ->where('det_materialid', $materialid)
            ->first();

        if ($existingRow) {
            $modelTemp->update($existingRow['id'], [
                'det_qty' => $existingRow['det_qty'] + $jml,
            ]);
        } else {
            $modelTemp->insert([
                'det_no_produksi' => $noProduksi,
                'det_kode_material' => $kodematerial,
                'det_materialid' => $materialid,
                'det_nama_material' => $namamaterial,
                'det_satuan' => $satuan,
                'det_qty' => $jml,
                'gudang' => $gudang,
            ]);
        }

        echo json_encode(['sukses' => 'Material berhasil ditambahkan']);
    }

    public function hapusItem()
    {
        if ($this->request->isAJAX()) {
            $id = $this->request->getPost('id');
            $modelTemp = new ModelTempProduksi();
            if (!$modelTemp->find($id)) {
                return $this->response->setJSON(['error' => 'Item material produksi tidak ditemukan']);
            }

            $modelTemp->delete($id);
            return $this->response->setJSON(['sukses' => 'Item berhasil dihapus']);
        }

        return $this->response->setStatusCode(404);
    }

    public function hapusSemuaTemp()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $noProduksi = trim((string) $this->request->getPost('no_produksi'));
        if ($noProduksi !== '') {
            (new ModelTempProduksi())->hapusData($noProduksi);
        }

        return $this->response->setJSON(['sukses' => true]);
    }

    public function selesaiTransaksi()
    {
        if (!$this->request->isAJAX()) {
            exit('Maaf tidak bisa dipanggil');
        }

        $noProduksi = trim((string) $this->request->getPost('no_produksi'));
        $tglProduksi = $this->request->getPost('tgl_produksi');
        $gudang = $this->request->getPost('gudang');
        $kodeProduk = trim((string) $this->request->getPost('kodebarang'));
        $namaProduk = trim((string) $this->request->getPost('namabarang'));
        $qtyProduk = (float) $this->request->getPost('qty_produk');

        if ($noProduksi === '' || $tglProduksi === '' || $gudang === '' || $kodeProduk === '' || $qtyProduk <= 0) {
            echo json_encode(['error' => 'Tanggal, Gudang, Produk, dan Qty Produk harus diisi dengan benar.']);
            return;
        }

        if ((new ModelProduksi())->find($noProduksi)) {
            echo json_encode(['error' => 'No Produksi ini sudah pernah disimpan. Muat ulang halaman.']);
            return;
        }

        $modelTemp = new ModelTempProduksi();
        $dataTemp = $modelTemp->tampilDataTemp($noProduksi);
        $rowsTemp = $dataTemp->getResultArray();

        if (!$rowsTemp) {
            echo json_encode(['error' => 'Maaf, material yang dipakai untuk produksi ini belum ada.']);
            return;
        }

        $adaStokProduk = (new Modelstok())->where('kodebarang', $kodeProduk)->where('gudang', $gudang)->countAllResults() > 0;
        if (!$adaStokProduk) {
            echo json_encode(['error' => "Data stok untuk produk {$kodeProduk} di gudang ini belum ada. Tambahkan dulu lewat menu Barang Masuk sebelum bisa diproduksi di sini."]);
            return;
        }

        $modelStokMaterial = new ModelStokMaterial();
        $kurangStok = [];
        foreach ($rowsTemp as $row) {
            $stokTersedia = (float) $modelStokMaterial->getStokByMaterialIdGudang($row['det_materialid'], $gudang);
            if ($stokTersedia < (float) $row['det_qty']) {
                $kurangStok[] = $row['det_nama_material'] . ' (sisa ' . $stokTersedia . ', butuh ' . $row['det_qty'] . ')';
            }
        }

        if ($kurangStok) {
            echo json_encode(['error' => 'Stok material tidak cukup: ' . implode(', ', $kurangStok)]);
            return;
        }

        $this->db->transStart();

        (new ModelProduksi())->insert([
            'no_produksi' => $noProduksi,
            'tgl_produksi' => $tglProduksi,
            'gudang' => $gudang,
            'iduser' => session()->get('userid'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $produksiProdukId = (new ModelProduksiProduk())->insert([
            'no_produksi' => $noProduksi,
            'kode_produk' => $kodeProduk,
            'nama_produk' => $namaProduk,
            'qty_produk' => $qtyProduk,
            'created_at' => date('Y-m-d H:i:s'),
        ], true);

        $detailProduksi = [];
        foreach ($rowsTemp as $row) {
            $detailProduksi[] = [
                'no_produksi' => $noProduksi,
                'produksi_produk_id' => $produksiProdukId,
                'kode_material' => $row['det_kode_material'],
                'materialid' => $row['det_materialid'],
                'nama_material' => $row['det_nama_material'],
                'satuan' => $row['det_satuan'],
                'qty_material' => $row['det_qty'],
            ];

            $stokBerhasilDikurangi = $this->db->table('stokmaterial')
                ->where('materialid', $row['det_materialid'])
                ->where('gudang', $gudang)
                ->where('stok >=', (float) $row['det_qty'])
                ->set('stok', 'stok - ' . (float) $row['det_qty'], false)
                ->update();
            if (!$stokBerhasilDikurangi || $this->db->affectedRows() < 1) {
                $this->db->transRollback();
                echo json_encode(['error' => 'Stok material berubah atau tidak cukup saat transaksi disimpan. Silakan ulangi.']);
                return;
            }
        }
        (new ModelDetailProduksi())->insertBatch($detailProduksi);

        (new Modelstok())->updateOrInsertBatch([
            ['kodebarang' => $kodeProduk, 'gudang' => $gudang, 'stok' => $qtyProduk],
        ]);

        $modelTemp->hapusData($noProduksi);

        $this->db->transComplete();

        $json = $this->db->transStatus() === false
            ? ['error' => 'Transaksi Produksi gagal disimpan. Tidak ada data yang diubah.']
            : ['sukses' => 'Produksi berhasil disimpan. Stok material berkurang, stok produk bertambah.'];
        echo json_encode($json);
    }

    public function edit($hash)
    {
        $produksi = (new ModelProduksi())->cekProduksi($hash)->getRowArray();
        if (!$produksi) {
            exit('Data produksi tidak ditemukan');
        }

        $lines = (new ModelProduksiProduk())
            ->where('no_produksi', $produksi['no_produksi'])
            ->orderBy('id', 'ASC')
            ->findAll();

        foreach ($lines as &$line) {
            $line['materials'] = $this->db->table('detail_produksi')
                ->where('produksi_produk_id', $line['id'])
                ->orderBy('id', 'ASC')
                ->get()
                ->getResultArray();
        }
        unset($line);

        $stokProduk = $lines
            ? (new Modelstok())->getStokByGudang($lines[0]['kode_produk'], $produksi['gudang'])
            : 0;

        return view('produksi/edit', [
            'produksi' => $produksi,
            'lines' => $lines,
            'datagudang' => (new Modelgudang())->findAll(),
            'stok_produk' => $stokProduk,
            'databarang' => (new Modelbarang())->select('brgkode, brgnama')->orderBy('brgkode', 'ASC')->findAll(),
        ]);
    }

    private function ambilRelasiMaterialProduk(string $kodeProduk): array
    {
        return $this->db->table('barang b')
            ->select(
                'm.matid, m.matkode, m.matnama, m.matsatid,
                satuan_material.satnama AS satuan_material,
                bmtr.berat AS berat_material, brt.satuan AS satuan_berat_id,
                CASE WHEN FIND_IN_SET(m.matid, b.brgmat) = 1 THEN 1 ELSE 0 END AS is_default',
                false
            )
            ->join('material m', 'FIND_IN_SET(m.matid, b.brgmat) > 0', 'inner', false)
            ->join('satuan satuan_material', 'satuan_material.satid = m.matsatid', 'left')
            ->join('berat_material bmtr', 'bmtr.kodeprd = b.brgkode AND bmtr.matid = m.matid', 'left')
            ->join('berat brt', 'brt.kodeprd = b.brgkode', 'left')
            ->where('b.brgkode', $kodeProduk)
            ->orderBy('FIND_IN_SET(m.matid, b.brgmat)', 'ASC', false)
            ->get()
            ->getResultArray();
    }

    private function hitungDetailProduksi(string $kodeProduk, float $qtyProduk, $gudang, array $stokTambahan = [], ?int $materialId = null): array
    {
        if ($this->produkTanpaBerat($kodeProduk)) {
            return ['details' => [], 'peringatan' => [], 'error' => null];
        }

        $relasi = $this->ambilRelasiMaterialProduk($kodeProduk);

        if (!$relasi) {
            return ['details' => [], 'peringatan' => [], 'error' => "Produk {$kodeProduk} belum punya data Kebutuhan Material (BOM). Lengkapi dulu di menu Kebutuhan Material."];
        }

        $materialDipilih = null;
        if ($materialId !== null && $materialId > 0) {
            foreach ($relasi as $row) {
                if ((int) $row['matid'] === $materialId) {
                    $materialDipilih = $row;
                    break;
                }
            }
        }
        if ($materialDipilih === null && $materialId !== null && $materialId > 0) {
            return ['details' => [], 'peringatan' => [], 'error' => 'Material yang dipilih tidak terdaftar sebagai material inti/alternatif produk ini.'];
        }
        if ($materialDipilih === null) {
            foreach ($relasi as $row) {
                if ((int) $row['is_default'] === 1) {
                    $materialDipilih = $row;
                    break;
                }
            }
        }
        $materialDipilih ??= $relasi[0];

        $berat = $materialDipilih['berat_material'];
        if ($berat === null || !is_numeric($berat) || (float) $berat <= 0) {
            return ['details' => [], 'peringatan' => [], 'error' => "Berat material {$materialDipilih['matnama']} belum diisi untuk produk ini."];
        }
        if ((int) $materialDipilih['satuan_berat_id'] !== (int) $materialDipilih['matsatid']) {
            return ['details' => [], 'peringatan' => [], 'error' => "Satuan berat produk untuk material {$materialDipilih['matkode']} tidak sama dengan satuan materialnya."];
        }

        $qtyMaterial = $qtyProduk * (float) $berat;
        $stokTersedia = (float) (new ModelStokMaterial())->getStokByMaterialIdGudang($materialDipilih['matid'], $gudang);
        $stokTersedia += (float) ($stokTambahan[$materialDipilih['matid']] ?? 0);
        if ($stokTersedia < $qtyMaterial) {
            return [
                'details' => [],
                'peringatan' => [],
                'error' => "Stok material {$materialDipilih['matnama']} tidak cukup (sisa {$stokTersedia}, butuh {$qtyMaterial}). Pilih material alternatif yang stoknya tersedia.",
            ];
        }

        return [
            'details' => [[
                'kode_material' => $materialDipilih['matkode'],
                'materialid' => $materialDipilih['matid'],
                'nama_material' => $materialDipilih['matnama'],
                'satuan' => $materialDipilih['satuan_material'],
                'qty_material' => $qtyMaterial,
            ]],
            'peringatan' => [],
            'error' => null,
        ];
    }

    /**
     * Edit 1 baris produk (produksi_produk) di dalam sebuah batch. Gudang
     * gak bisa diubah lewat sini karena 1 no_produksi (batch) cuma boleh
     * punya 1 gudang buat semua produknya -- kalau mau pindah gudang,
     * hapus baris ini terus tambahkan ulang di gudang yang benar.
     */
    public function update()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $id = (int) $this->request->getPost('id');
        $tglProduksi = $this->request->getPost('tgl_produksi');
        $gudang = $this->request->getPost('gudang');
        $kodeProduk = trim((string) $this->request->getPost('kodebarang'));
        $namaProduk = trim((string) $this->request->getPost('namabarang'));
        $qtyProduk = (float) $this->request->getPost('qty_produk');

        if (!$id || $tglProduksi === '' || $gudang === '' || $kodeProduk === '' || $qtyProduk <= 0) {
            echo json_encode(['error' => 'Tanggal, Gudang, Produk, dan Qty Diproduksi harus diisi dengan benar.']);
            return;
        }

        $modelLine = new ModelProduksiProduk();
        $lineLama = $modelLine->find($id);
        if (!$lineLama) {
            echo json_encode(['error' => 'Data produksi tidak ditemukan']);
            return;
        }

        $modelProduksi = new ModelProduksi();
        $header = $modelProduksi->find($lineLama['no_produksi']);
        if (!$header) {
            echo json_encode(['error' => 'Data produksi tidak ditemukan']);
            return;
        }

        if ((string) $header['gudang'] !== (string) $gudang) {
            echo json_encode(['error' => 'Gudang tidak bisa diubah lewat edit 1 produk, karena 1 No Produksi berlaku untuk 1 gudang bersama semua produknya. Hapus baris ini lalu tambahkan ulang di gudang yang benar.']);
            return;
        }

        // Batch "Material Pelanggan" dibuat sebagai catatan dari Produk Masuk.
        // Stok produk sudah ditambah oleh transaksi Produk Masuk, sehingga
        // proses edit di sini tidak boleh menghitung BOM atau menyesuaikan
        // stok sekali lagi. Batch biasa tetap memakai alur stok normal.
        $isMaterialPelanggan = ($header['keterangan'] ?? null) === 'Material Pelanggan';

        $stokProdukLama = $this->db->table('stok')
            ->select('id, stok')
            ->where('kodebarang', $lineLama['kode_produk'])
            ->where('gudang', $header['gudang'])
            ->get()
            ->getRowArray();

        if (!$isMaterialPelanggan && (!$stokProdukLama || (float) $stokProdukLama['stok'] < (float) $lineLama['qty_produk'])) {
            echo json_encode(['error' => 'Data produksi tidak bisa diedit karena stok produk hasil produksi sudah dipakai transaksi lain.']);
            return;
        }

        $adaStokProdukBaru = (new Modelstok())->where('kodebarang', $kodeProduk)->where('gudang', $gudang)->countAllResults() > 0;
        if (!$adaStokProdukBaru) {
            echo json_encode(['error' => "Data stok untuk produk {$kodeProduk} di gudang ini belum ada. Tambahkan dulu lewat menu Barang Masuk sebelum bisa diproduksi di sini."]);
            return;
        }

        $detailLama = $this->db->table('detail_produksi')
            ->where('produksi_produk_id', $id)
            ->get()
            ->getResultArray();

        $materialIdDipilih = (int) $this->request->getPost('materialid');
        $stokTambahan = [];
        foreach ($detailLama as $detail) {
            $materialIdLama = (int) $detail['materialid'];
            $stokTambahan[$materialIdLama] = ($stokTambahan[$materialIdLama] ?? 0) + (float) $detail['qty_material'];
        }
        // Transaksi lama belum memiliki payload pilihan material di form.
        // Pertahankan material yang tersimpan agar edit tidak berpindah diam-diam
        // ke material inti/default.
        if ($materialIdDipilih <= 0 && $detailLama) {
            $materialIdDipilih = (int) $detailLama[0]['materialid'];
        }

        $hasilDetail = $isMaterialPelanggan
            ? ['details' => [], 'peringatan' => [], 'error' => null]
            : $this->hitungDetailProduksi($kodeProduk, $qtyProduk, $gudang, $stokTambahan, $materialIdDipilih > 0 ? $materialIdDipilih : null);
        if ($hasilDetail['error'] !== null) {
            echo json_encode(['error' => $hasilDetail['error']]);
            return;
        }

        $this->db->transStart();

        if (!$isMaterialPelanggan) {
            foreach ($detailLama as $detail) {
                $this->db->table('stokmaterial')
                    ->where('materialid', $detail['materialid'])
                    ->where('gudang', $header['gudang'])
                    ->set('stok', 'stok + ' . (float) $detail['qty_material'], false)
                    ->update();
            }
        }

        // Stok produk tetap harus membalik qty lama, termasuk untuk
        // Material Pelanggan, karena edit dilakukan dari menu Produksi.
        $this->db->table('stok')
            ->where('id', $stokProdukLama['id'])
            ->set('stok', 'GREATEST(stok - ' . (float) $lineLama['qty_produk'] . ', 0)', false)
            ->update();

        $modelProduksi->update($lineLama['no_produksi'], [
            'tgl_produksi' => $tglProduksi,
        ]);

        $modelLine->update($id, [
            'kode_produk' => $kodeProduk,
            'nama_produk' => $namaProduk,
            'qty_produk' => $qtyProduk,
        ]);

        $this->db->table('detail_produksi')->where('produksi_produk_id', $id)->delete();

        if ($hasilDetail['details']) {
            $detailBaru = [];
            foreach ($hasilDetail['details'] as $detail) {
                $detailBaru[] = array_merge(['no_produksi' => $lineLama['no_produksi'], 'produksi_produk_id' => $id], $detail);

                $stokBerhasilDikurangi = $this->db->table('stokmaterial')
                    ->where('materialid', $detail['materialid'])
                    ->where('gudang', $gudang)
                    ->where('stok >=', (float) $detail['qty_material'])
                    ->set('stok', 'stok - ' . (float) $detail['qty_material'], false)
                    ->update();
                if (!$stokBerhasilDikurangi || $this->db->affectedRows() < 1) {
                    $this->db->transRollback();
                    echo json_encode(['error' => "Stok material {$detail['nama_material']} berubah atau tidak cukup saat edit disimpan. Silakan ulangi."]);
                    return;
                }
            }
            (new ModelDetailProduksi())->insertBatch($detailBaru);
        }

        // Tambahkan kembali qty baru ke stok produk. Untuk kode produk yang
        // sama, hasil akhirnya adalah stok lama + (qty baru - qty lama).
        (new Modelstok())->updateOrInsertBatch([
            ['kodebarang' => $kodeProduk, 'gudang' => $gudang, 'stok' => $qtyProduk],
        ]);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            echo json_encode(['error' => 'Data produksi gagal diperbarui. Tidak ada data yang diubah.']);
            return;
        }

        $json = [
            'sukses' => $isMaterialPelanggan
                ? 'Data produksi Material Pelanggan berhasil diperbarui. Stok produk sudah disesuaikan.'
                : 'Data produksi berhasil diperbarui. Stok material dan stok produk sudah disesuaikan.',
            'no_produksi' => $lineLama['no_produksi'],
            'produksi_produk_id' => $id,
            'kodebarang' => $kodeProduk,
            'namabarang' => $namaProduk,
            'qty_produk' => $qtyProduk,
            'detail' => $hasilDetail['details'],
        ];
        if ($hasilDetail['peringatan']) {
            $json['peringatan'] = $hasilDetail['peringatan'];
        }
        echo json_encode($json);
    }
    /**
     * Hapus 1 baris produk (produksi_produk) dari batch-nya. Header
     * produksi (batch) cuma ikut dihapus kalau itu baris terakhir di
     * batch tersebut.
     */
    public function hapusTransaksi()
    {
        if (!$this->request->isAJAX()) {
            return;
        }

        $id = (int) $this->request->getPost('id');
        $modelLine = new ModelProduksiProduk();
        $line = $modelLine->find($id);
        if (!$line) {
            echo json_encode(['error' => 'Data produksi tidak ditemukan']);
            return;
        }

        $modelProduksi = new ModelProduksi();
        $header = $modelProduksi->find($line['no_produksi']);
        if (!$header) {
            echo json_encode(['error' => 'Data produksi tidak ditemukan']);
            return;
        }

        // Batch "Material Pelanggan" itu cuma catatan/laporan yang otomatis
        // dibuat dari Input Produk Masuk (sumber "Produksi Material
        // Pelanggan") -- stok produknya udah ditambahin lewat transaksi
        // Produk Masuk itu sendiri, BUKAN lewat baris produksi_produk ini.
        // Jadi hapus baris ini nggak boleh ikut ngurangin stok produk lagi
        // (kalau ikut ngurangin, jadi ke-hitung dobel kalau transaksi Produk
        // Masuk aslinya juga dihapus/masih ada).
        $isMaterialPelanggan = ($header['keterangan'] ?? null) === 'Material Pelanggan';

        $details = $isMaterialPelanggan
            ? []
            : $this->db->table('detail_produksi')->where('produksi_produk_id', $id)->get()->getResultArray();
        $stokProduk = $this->db->table('stok')
            ->select('id, stok')
            ->where('kodebarang', $line['kode_produk'])
            ->where('gudang', $header['gudang'])
            ->get()
            ->getRowArray();

        if (!$stokProduk || (float) $stokProduk['stok'] < (float) $line['qty_produk']) {
            echo json_encode([
                'error' => 'Data produksi tidak bisa dihapus karena stok produk hasil produksi sudah dipakai transaksi lain.'
            ]);
            return;
        }
        $this->db->transStart();
        // Material Pelanggan tidak memiliki detail pemakaian material TRE,
        // tetapi stok produk tetap harus dikurangi saat baris produksi dihapus.
        foreach ($details as $detail) {
            $this->db->table('stokmaterial')
                ->where('materialid', $detail['materialid'])
                ->where('gudang', $header['gudang'])
                ->set('stok', 'stok + ' . (float) $detail['qty_material'], false)
                ->update();
        }
        $this->db->table('stok')
            ->where('id', $stokProduk['id'])
            ->set('stok', 'GREATEST(stok - ' . (float) $line['qty_produk'] . ', 0)', false)
            ->update();

        $this->db->table('detail_produksi')->where('produksi_produk_id', $id)->delete();
        $modelLine->delete($id);

        $sisaLine = $modelLine->where('no_produksi', $line['no_produksi'])->countAllResults();
        $batchDihapus = false;
        if ($sisaLine === 0) {
            $modelProduksi->delete($line['no_produksi']);
            $batchDihapus = true;
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            echo json_encode(['error' => 'Data produksi gagal dihapus.']);
            return;
        }

        $keteranganStok = $isMaterialPelanggan
            ? 'stok produk dikurangi sesuai qty yang dihapus, tanpa mengubah stok material TRE'
            : 'stok dikembalikan seperti semula';

        $pesan = $batchDihapus
            ? "Data produksi berhasil dihapus, {$keteranganStok}. Ini baris terakhir di batch ini jadi No Produksi ikut dihapus."
            : "Data produksi berhasil dihapus, {$keteranganStok}.";

        echo json_encode([
            'sukses' => $pesan,
            'no_produksi' => $line['no_produksi'],
            'batch_dihapus' => $batchDihapus,
        ]);
    }
}
