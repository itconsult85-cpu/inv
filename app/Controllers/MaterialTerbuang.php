<?php

namespace App\Controllers;

use App\Controllers\BaseController;

/**
 * Estimasi material yang bakal kebuang (waste) kalau semua pesanan yang
 * masih outstanding diproduksi. Mesin hitungnya sama persis dengan
 * KebutuhanMaterial (outstanding - stok produk = perlu produksi, dikali
 * Berat Material Terpakai per produk) -- bedanya di sini hasil kebutuhan
 * itu masih dikali persentase Wise (barang.wise) per produk buat dapetin
 * perkiraan berapa yang bakal kebuang jadi waste.
 */
class MaterialTerbuang extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = db_connect();
        $this->ensureSumberMaterialColumns();
    }

    public function index()
    {
        $pelanggans = $this->db->table('pelanggan')
            ->select('pelid, pelnama')
            ->whereNotIn('pelid', [1, 2])
            ->orderBy('pelnama', 'ASC')
            ->get()
            ->getResultArray();

        $produks = $this->db->table('barang')
            ->select("brgkode, brgnama, COALESCE(sumber_material, 'tre') AS sumber_material", false)
            ->orderBy('brgkode', 'ASC')
            ->get()
            ->getResultArray();

        $materials = $this->db->table('material')
            ->select('matid, matkode, matnama')
            ->orderBy('matkode', 'ASC')
            ->get()
            ->getResultArray();

        return view('materialterbuang/index', [
            'pelanggans' => $pelanggans,
            'produks' => $produks,
            'materials' => $materials,
        ]);
    }

    public function data()
    {
        return $this->response->setJSON($this->hitungWaste(
            $this->request->getGet()
        ));
    }

    private function hitungWaste(array $filter): array
    {
        $pelangganId = (int) ($filter['pelanggan'] ?? 0);
        $kodeProduk = trim((string) ($filter['produk'] ?? ''));
        $materialId = (int) ($filter['material'] ?? 0);

        $outstandingBuilder = $this->db->table('outstanding o')
            ->select(
                "o.kodebrg, o.idpel, b.brgnama, b.brgmat, b.wise, p.pelnama, COALESCE(b.sumber_material, 'tre') AS sumber_material,
                SUM(GREATEST(COALESCE(o.qty, 0) - COALESCE(o.terkirim, 0), 0)) AS outstanding",
                false
            )
            ->join('barang b', 'b.brgkode = o.kodebrg', 'left')
            ->join('pelanggan p', 'p.pelid = o.idpel', 'left')
            ->whereNotIn('o.idpel', [1, 2])
            ->groupBy('o.kodebrg, o.idpel, b.brgnama, b.brgmat, b.wise, p.pelnama, b.sumber_material')
            ->having('outstanding >', 0);

        if ($pelangganId > 0) {
            $outstandingBuilder->where('o.idpel', $pelangganId);
        }
        if ($kodeProduk !== '') {
            $outstandingBuilder->where('o.kodebrg', $kodeProduk);
        }

        $outstandingRows = $outstandingBuilder->get()->getResultArray();
        if (!$outstandingRows) {
            return $this->hasilKosong();
        }

        $kodeProduks = array_values(array_unique(array_column($outstandingRows, 'kodebrg')));

        $stokProdukRows = $this->db->table('stok')
            ->select('kodebarang, idpel, SUM(GREATEST(COALESCE(stok, 0), 0)) AS total_stok', false)
            ->whereIn('kodebarang', $kodeProduks)
            ->groupBy('kodebarang, idpel')
            ->get()
            ->getResultArray();

        $stokProduk = [];
        foreach ($stokProdukRows as $row) {
            $stokProduk[$this->kunciProduk($row['kodebarang'], $row['idpel'])] =
                max(0.0, (float) $row['total_stok']);
        }

        $relasiBuilder = $this->db->table('barang b')
            ->select(
                'b.brgkode, m.matid, m.matkode, m.matnama,
                m.matsatid, satuan_material.satnama AS satuan_material,
                bmtr.berat AS berat_material, brt.satuan AS satuan_berat_id',
                false
            )
            ->join('material m', 'FIND_IN_SET(m.matid, b.brgmat) > 0', 'inner', false)
            ->join('satuan satuan_material', 'satuan_material.satid = m.matsatid', 'left')
            ->join('berat_material bmtr', 'bmtr.kodeprd = b.brgkode AND bmtr.matid = m.matid', 'left')
            ->join('berat brt', 'brt.kodeprd = b.brgkode', 'left')
            ->whereIn('b.brgkode', $kodeProduks);

        $relasiRows = $relasiBuilder->get()->getResultArray();
        $relasiPerProduk = [];
        foreach ($relasiRows as $row) {
            $relasiPerProduk[$row['brgkode']][] = $row;
        }

        $hasilMaterial = [];
        $peringatan = [];
        $vendorSupply = [];
        $fullBeliJadi = [];

        foreach ($outstandingRows as $produk) {
            $outstanding = (float) $produk['outstanding'];
            $stokJadi = $stokProduk[$this->kunciProduk(
                $produk['kodebrg'],
                $produk['idpel']
            )] ?? 0.0;
            $perluProduksi = max($outstanding - $stokJadi, 0);

            if ($perluProduksi <= 0) {
                continue;
            }

            if (($produk['sumber_material'] ?? 'tre') === 'vendor') {
                if ($materialId > 0 && !in_array($materialId, $this->normalisasiMaterialProduk($produk['brgmat'] ?? ''), true)) {
                    continue;
                }

                $vendorSupply[] = [
                    'kode_produk' => (string) $produk['kodebrg'],
                    'nama_produk' => (string) ($produk['brgnama'] ?: '-'),
                    'pelanggan' => (string) ($produk['pelnama'] ?: '-'),
                    'perlu_produksi' => $perluProduksi,
                    'keterangan' => 'Material disediakan customer, tidak dihitung sebagai waste material TRE.',
                ];
                continue;
            }

            if (($produk['sumber_material'] ?? 'tre') === 'beli_jadi') {
                if ($materialId > 0 && !in_array($materialId, $this->normalisasiMaterialProduk($produk['brgmat'] ?? ''), true)) {
                    continue;
                }

                $fullBeliJadi[] = [
                    'kode_produk' => (string) $produk['kodebrg'],
                    'nama_produk' => (string) ($produk['brgnama'] ?: '-'),
                    'pelanggan' => (string) ($produk['pelnama'] ?: '-'),
                    'perlu_produksi' => $perluProduksi,
                    'keterangan' => 'Produk dibeli jadi dari vendor (PO Out), tidak ada proses produksi TRE.',
                ];
                continue;
            }

            $wiseProduk = $produk['wise'];
            $wiseValid = $wiseProduk !== null && is_numeric($wiseProduk);

            $relasiProduk = $relasiPerProduk[$produk['kodebrg']] ?? [];
            if (!$relasiProduk) {
                $pesan = "Produk {$produk['kodebrg']} belum memiliki relasi material yang dapat dihitung.";
                $peringatan[sha1($pesan)] = $pesan;
                continue;
            }

            foreach ($relasiProduk as $relasi) {
                $matid = (int) $relasi['matid'];
                if ($materialId > 0 && $matid !== $materialId) {
                    continue;
                }
                if (!isset($hasilMaterial[$matid])) {
                    $hasilMaterial[$matid] = [
                        'matid' => $matid,
                        'kode_material' => (string) $relasi['matkode'],
                        'nama_material' => (string) $relasi['matnama'],
                        'satuan' => (string) ($relasi['satuan_material'] ?: '-'),
                        'kebutuhan' => 0.0,
                        'waste' => 0.0,
                        'alasan_belum_lengkap' => [],
                        'details' => [],
                    ];
                }

                $berat = $relasi['berat_material'];
                $alasanDetail = null;
                if ($berat === null || !is_numeric($berat) || (float) $berat <= 0) {
                    $alasanDetail = "Berat material {$relasi['matkode']} untuk produk {$produk['kodebrg']} belum diisi.";
                } elseif (
                    (int) $relasi['satuan_berat_id'] !== (int) $relasi['matsatid']
                ) {
                    $alasanDetail = "Satuan berat produk {$produk['kodebrg']} tidak sama dengan satuan material {$relasi['matkode']}.";
                } elseif (!$wiseValid) {
                    $alasanDetail = "Wise untuk produk {$produk['kodebrg']} belum diisi, waste tidak bisa dihitung.";
                }

                $kebutuhan = null;
                $waste = null;
                if ($alasanDetail === null) {
                    $kebutuhan = $perluProduksi * (float) $berat;
                    $waste = $kebutuhan * ((float) $wiseProduk / 100);
                    $hasilMaterial[$matid]['kebutuhan'] += $kebutuhan;
                    $hasilMaterial[$matid]['waste'] += $waste;
                } else {
                    $hasilMaterial[$matid]['alasan_belum_lengkap'][] = $alasanDetail;
                    $peringatan[sha1($alasanDetail)] = $alasanDetail;
                }

                $hasilMaterial[$matid]['details'][] = [
                    'kode_produk' => (string) $produk['kodebrg'],
                    'nama_produk' => (string) ($produk['brgnama'] ?: '-'),
                    'pelanggan' => (string) ($produk['pelnama'] ?: '-'),
                    'perlu_produksi' => $perluProduksi,
                    'wise_persen' => $wiseValid ? (float) $wiseProduk : null,
                    'berat_per_produk' => is_numeric($berat) ? (float) $berat : null,
                    'kebutuhan' => $kebutuhan,
                    'waste' => $waste,
                    'satuan' => (string) ($relasi['satuan_material'] ?: '-'),
                    'keterangan' => $alasanDetail,
                ];
            }
        }

        $data = [];
        foreach ($hasilMaterial as $material) {
            $material['alasan_belum_lengkap'] = array_values(array_unique(
                $material['alasan_belum_lengkap']
            ));
            $material['persen_waste'] = $material['kebutuhan'] > 0
                ? ($material['waste'] / $material['kebutuhan']) * 100
                : 0.0;

            // Cuma ditampilin kalau ada waste beneran ATAU ada yang belum
            // lengkap datanya (biar kelihatan produk mana yang perlu
            // dilengkapi Wise-nya dulu).
            if ($material['waste'] <= 0 && !$material['alasan_belum_lengkap']) {
                continue;
            }

            $data[] = $material;
        }

        usort($data, static function (array $a, array $b): int {
            return $b['waste'] <=> $a['waste'];
        });

        $totalWaste = array_sum(array_column($data, 'waste'));

        return [
            'data' => $data,
            'summary' => [
                'total_material' => count($data),
                'total_waste' => $totalWaste,
                'peringatan' => count($peringatan),
                'vendor_supply' => count($vendorSupply),
                'full_beli_jadi' => count($fullBeliJadi),
            ],
            'warnings' => array_values($peringatan),
            'vendor_supply' => $vendorSupply,
            'full_beli_jadi' => $fullBeliJadi,
            'generated_at' => date('d-m-Y H:i:s'),
        ];
    }

    private function kunciProduk($kodeProduk, $pelangganId): string
    {
        return trim((string) $kodeProduk) . '|' . (int) $pelangganId;
    }

    private function normalisasiMaterialProduk($material): array
    {
        return array_values(array_unique(array_map(
            'intval',
            array_filter(explode(',', (string) $material), static fn ($id) => trim((string) $id) !== '')
        )));
    }

    private function hasilKosong(): array
    {
        return [
            'data' => [],
            'summary' => [
                'total_material' => 0,
                'total_waste' => 0,
                'peringatan' => 0,
                'vendor_supply' => 0,
                'full_beli_jadi' => 0,
            ],
            'warnings' => [],
            'vendor_supply' => [],
            'full_beli_jadi' => [],
            'generated_at' => date('d-m-Y H:i:s'),
        ];
    }
}
