<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class MaterialWasteProduksi extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    public function index()
    {
        $produks = $this->db->table('barang')
            ->select('brgkode, brgnama')
            ->orderBy('brgkode', 'ASC')
            ->get()
            ->getResultArray();
        $materials = $this->db->table('material')
            ->select('matid, matkode, matnama')
            ->orderBy('matkode', 'ASC')
            ->get()
            ->getResultArray();

        return view('materialwasteproduksi/index', [
            'produks' => $produks,
            'materials' => $materials,
        ]);
    }

    public function data()
    {
        $filter = $this->request->getGet();
        $tanggalAwal = trim((string) ($filter['tanggal_awal'] ?? ''));
        $tanggalAkhir = trim((string) ($filter['tanggal_akhir'] ?? ''));
        $kodeProduk = trim((string) ($filter['produk'] ?? ''));
        $materialId = (int) ($filter['material'] ?? 0);

        $builder = $this->db->table('detail_produksi dp')
            ->select(
                "dp.id, dp.no_produksi, p.tgl_produksi, p.gudang,
                pp.kode_produk, pp.nama_produk, pp.qty_produk,
                dp.materialid, dp.kode_material, dp.nama_material, dp.satuan,
                dp.qty_material AS material_terpakai,
                COALESCE(bm.berat_produk_jadi, brt.berat) AS berat_produk_jadi,
                bm.wise AS wise_master,
                (pp.qty_produk * COALESCE(bm.berat_produk_jadi, brt.berat)) AS hasil_jadi_kg",
                false
            )
            ->join('produksi p', 'p.no_produksi = dp.no_produksi', 'inner')
            ->join('produksi_produk pp', 'pp.id = dp.produksi_produk_id', 'inner')
            ->join(
                'berat_material bm',
                'bm.kodeprd = pp.kode_produk AND bm.matid = dp.materialid',
                'left'
            )
            ->join('berat brt', 'brt.kodeprd = pp.kode_produk', 'left')
            ->orderBy('p.tgl_produksi', 'DESC')
            ->orderBy('dp.no_produksi', 'DESC')
            ->orderBy('dp.id', 'DESC');

        if ($tanggalAwal !== '') {
            $builder->where('p.tgl_produksi >=', $tanggalAwal);
        }
        if ($tanggalAkhir !== '') {
            $builder->where('p.tgl_produksi <=', $tanggalAkhir);
        }
        if ($kodeProduk !== '') {
            $builder->where('pp.kode_produk', $kodeProduk);
        }
        if ($materialId > 0) {
            $builder->where('dp.materialid', $materialId);
        }

        $rows = $builder->get()->getResultArray();
        $perMaterial = [];
        $warnings = [];
        foreach ($rows as $row) {
            $matid = (int) $row['materialid'];
            $terpakai = max((float) $row['material_terpakai'], 0);
            $hasilJadi = $row['hasil_jadi_kg'] !== null && is_numeric($row['hasil_jadi_kg'])
                ? max((float) $row['hasil_jadi_kg'], 0)
                : null;
            $wiseMaster = $row['wise_master'] !== null && is_numeric($row['wise_master'])
                ? min(max((float) $row['wise_master'], 0), 100)
                : null;
            // Wise dari kalibrasi adalah sumber utama bila tersedia. Dengan
            // begitu laporan mengikuti hasil kalibrasi Produk; jika belum ada
            // kalibrasi, tetap memakai rumus lama dari output produksi.
            $wise = $wiseMaster !== null
                ? $wiseMaster
                : ($terpakai > 0 && $hasilJadi !== null ? (($terpakai - $hasilJadi) / $terpakai) * 100 : null);
            $waste = $wise !== null
                ? $terpakai * $wise / 100
                : ($hasilJadi === null ? null : max($terpakai - $hasilJadi, 0));
            $key = (string) $matid;

            if (!isset($perMaterial[$key])) {
                $perMaterial[$key] = [
                    'matid' => $matid,
                    'kode_material' => (string) $row['kode_material'],
                    'nama_material' => (string) $row['nama_material'],
                    'satuan' => (string) ($row['satuan'] ?: '-'),
                    'total_produksi' => 0,
                    'total_qty_produk' => 0.0,
                    'material_terpakai' => 0.0,
                    'hasil_jadi_kg' => 0.0,
                    'waste_kg' => 0.0,
                    'wise_persen' => null,
                    'data_lengkap' => true,
                    'detail' => [],
                ];
            }

            $perMaterial[$key]['total_produksi']++;
            $perMaterial[$key]['total_qty_produk'] += (float) $row['qty_produk'];
            $perMaterial[$key]['material_terpakai'] += $terpakai;
            if ($hasilJadi === null && $wiseMaster === null) {
                $perMaterial[$key]['data_lengkap'] = false;
                $warning = "Berat produk jadi untuk material {$row['kode_material']} pada produk {$row['kode_produk']} belum tersedia.";
                $warnings[sha1($warning)] = $warning;
            } else {
                // Jika Wise kalibrasi tersedia, hasil jadi laporan adalah
                // material aktual dikurangi waste kalibrasi agar konsisten.
                $hasilJadiLaporan = $wiseMaster !== null
                    ? max($terpakai - $waste, 0)
                    : $hasilJadi;
                $perMaterial[$key]['hasil_jadi_kg'] += $hasilJadiLaporan;
                $perMaterial[$key]['waste_kg'] += $waste;
            }
            $perMaterial[$key]['detail'][] = [
                'no_produksi' => (string) $row['no_produksi'],
                'tanggal' => (string) $row['tgl_produksi'],
                'kode_produk' => (string) $row['kode_produk'],
                'nama_produk' => (string) $row['nama_produk'],
                'qty_produk' => (float) $row['qty_produk'],
                'material_terpakai' => $terpakai,
                'hasil_jadi_kg' => $wiseMaster !== null ? max($terpakai - $waste, 0) : $hasilJadi,
                'waste_kg' => $waste,
                'wise_persen' => $wise,
            ];
        }

        foreach ($perMaterial as &$material) {
            $material['wise_persen'] = $material['material_terpakai'] > 0 && $material['data_lengkap']
                ? ($material['waste_kg'] / $material['material_terpakai']) * 100
                : null;
            $material['detail'] = array_values($material['detail']);
        }
        unset($material);

        $data = array_values($perMaterial);
        usort($data, static function (array $a, array $b): int {
            return $b['waste_kg'] <=> $a['waste_kg'];
        });

        return $this->response->setJSON([
            'data' => $data,
            'summary' => [
                'total_material' => count($data),
                'total_produksi' => count($rows),
                'total_material_terpakai' => array_sum(array_column($data, 'material_terpakai')),
                'total_waste' => array_sum(array_column($data, 'waste_kg')),
                'peringatan' => count($warnings),
            ],
            'warnings' => array_values($warnings),
            'generated_at' => date('d-m-Y H:i:s'),
        ]);
    }
}
