<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class KebutuhanMaterial extends BaseController
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

        return view('kebutuhanmaterial/index', [
            'pelanggans' => $pelanggans,
            'produks' => $produks,
            'materials' => $materials,
        ]);
    }

    public function data()
    {
        return $this->response->setJSON($this->hitungKebutuhan(
            $this->request->getGet()
        ));
    }

    public function cetak()
    {
        $hasil = $this->hitungKebutuhan($this->request->getGet());

        return view('kebutuhanmaterial/cetak', [
            'hasil' => $hasil,
            'filter' => $this->request->getGet(),
        ]);
    }

    private function hitungKebutuhan(array $filter): array
    {
        $pelangganId = (int) ($filter['pelanggan'] ?? 0);
        $kodeProduk = trim((string) ($filter['produk'] ?? ''));
        $materialId = (int) ($filter['material'] ?? 0);
        $statusFilter = strtolower(trim((string) ($filter['status'] ?? '')));
        if (!in_array($statusFilter, ['', 'aman', 'kurang', 'belum_lengkap'], true)) {
            $statusFilter = '';
        }

        $outstandingBuilder = $this->db->table('outstanding o')
            ->select(
                "o.kodebrg, o.idpel, b.brgnama, b.brgmat, p.pelnama, COALESCE(b.sumber_material, 'tre') AS sumber_material,
                SUM(GREATEST(COALESCE(o.qty, 0) - COALESCE(o.terkirim, 0), 0)) AS outstanding",
                false
            )
            ->join('barang b', 'b.brgkode = o.kodebrg', 'left')
            ->join('pelanggan p', 'p.pelid = o.idpel', 'left')
            ->whereNotIn('o.idpel', [1, 2])
            ->groupBy('o.kodebrg, o.idpel, b.brgnama, b.brgmat, p.pelnama, b.sumber_material')
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
                'b.brgkode, m.matid, m.matkode, m.matnama, m.minmat,
                m.matsatid, satuan_material.satnama AS satuan_material,
                bmtr.berat AS berat_material, brt.satuan AS satuan_berat_id,
                satuan_berat.satnama AS satuan_berat',
                false
            )
            ->join('material m', 'FIND_IN_SET(m.matid, b.brgmat) > 0', 'inner', false)
            ->join('satuan satuan_material', 'satuan_material.satid = m.matsatid', 'left')
            ->join('berat_material bmtr', 'bmtr.kodeprd = b.brgkode AND bmtr.matid = m.matid', 'left')
            ->join('berat brt', 'brt.kodeprd = b.brgkode', 'left')
            ->join('satuan satuan_berat', 'satuan_berat.satid = brt.satuan', 'left')
            ->whereIn('b.brgkode', $kodeProduks);

        $relasiRows = $relasiBuilder->get()->getResultArray();
        $relasiPerProduk = [];
        foreach ($relasiRows as $row) {
            $relasiPerProduk[$row['brgkode']][] = $row;
        }

        $stokMaterialRows = $this->db->table('stokmaterial')
            ->select(
                'materialid,
                SUM(CASE WHEN gudang = 1 THEN stok ELSE 0 END) AS stok_cikarang,
                SUM(CASE WHEN gudang = 2 THEN stok ELSE 0 END) AS stok_cirebon,
                SUM(stok) AS total_stok',
                false
            )
            ->groupBy('materialid')
            ->get()
            ->getResultArray();

        $stokMaterial = [];
        foreach ($stokMaterialRows as $row) {
            $stokMaterial[(int) $row['materialid']] = [
                'cikarang' => (float) $row['stok_cikarang'],
                'cirebon' => (float) $row['stok_cirebon'],
                'total' => (float) $row['total_stok'],
            ];
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
                    'outstanding' => $outstanding,
                    'stok_produk' => $stokJadi,
                    'perlu_produksi' => $perluProduksi,
                    'keterangan' => 'Material disediakan customer, tidak dihitung sebagai kebutuhan material TRE.',
                ];
                continue;
            }

            // Produk yang emang full dibeli barang jadi dari vendor (PO Out
            // jenis Produk) -- nggak pernah diproduksi di TRE sama sekali,
            // jadi ngga ada kebutuhan material TRE ataupun material
            // customer buat produk ini.
            if (($produk['sumber_material'] ?? 'tre') === 'beli_jadi') {
                if ($materialId > 0 && !in_array($materialId, $this->normalisasiMaterialProduk($produk['brgmat'] ?? ''), true)) {
                    continue;
                }

                $fullBeliJadi[] = [
                    'kode_produk' => (string) $produk['kodebrg'],
                    'nama_produk' => (string) ($produk['brgnama'] ?: '-'),
                    'pelanggan' => (string) ($produk['pelnama'] ?: '-'),
                    'outstanding' => $outstanding,
                    'stok_produk' => $stokJadi,
                    'perlu_produksi' => $perluProduksi,
                    'keterangan' => 'Produk dibeli jadi dari vendor (PO Out), tidak butuh material apapun.',
                ];
                continue;
            }

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
                    $stok = $stokMaterial[$matid] ?? [
                        'cikarang' => 0.0,
                        'cirebon' => 0.0,
                        'total' => 0.0,
                    ];

                    $hasilMaterial[$matid] = [
                        'matid' => $matid,
                        'kode_material' => (string) $relasi['matkode'],
                        'nama_material' => (string) $relasi['matnama'],
                        'satuan' => (string) ($relasi['satuan_material'] ?: '-'),
                        'kebutuhan' => 0.0,
                        'stok_cikarang' => $stok['cikarang'],
                        'stok_cirebon' => $stok['cirebon'],
                        'total_stok' => $stok['total'],
                        'stok_minimum' => (float) $relasi['minmat'],
                        'kekurangan' => 0.0,
                        'saran_beli' => 0.0,
                        'status' => 'aman',
                        'alasan_belum_lengkap' => [],
                        'details' => [],
                    ];

                    if (!array_key_exists($matid, $stokMaterial)) {
                        $alasan = "Material {$relasi['matkode']} belum memiliki baris stok material.";
                        $hasilMaterial[$matid]['alasan_belum_lengkap'][] = $alasan;
                        $peringatan[sha1($alasan)] = $alasan;
                    }
                }

                $berat = $relasi['berat_material'];
                $alasanDetail = null;
                if ($berat === null || !is_numeric($berat) || (float) $berat <= 0) {
                    $alasanDetail = "Berat material {$relasi['matkode']} untuk produk {$produk['kodebrg']} belum diisi.";
                } elseif (
                    (int) $relasi['satuan_berat_id'] !== (int) $relasi['matsatid']
                ) {
                    $alasanDetail = "Satuan berat produk {$produk['kodebrg']} ({$relasi['satuan_berat']}) tidak sama dengan satuan material {$relasi['matkode']} ({$relasi['satuan_material']}).";
                }

                $kebutuhan = null;
                if ($alasanDetail === null) {
                    $kebutuhan = $perluProduksi * (float) $berat;
                    $hasilMaterial[$matid]['kebutuhan'] += $kebutuhan;
                } else {
                    $hasilMaterial[$matid]['alasan_belum_lengkap'][] = $alasanDetail;
                    $peringatan[sha1($alasanDetail)] = $alasanDetail;
                }

                $hasilMaterial[$matid]['details'][] = [
                    'kode_produk' => (string) $produk['kodebrg'],
                    'nama_produk' => (string) ($produk['brgnama'] ?: '-'),
                    'pelanggan' => (string) ($produk['pelnama'] ?: '-'),
                    'outstanding' => $outstanding,
                    'stok_produk' => $stokJadi,
                    'perlu_produksi' => $perluProduksi,
                    'berat_per_produk' => is_numeric($berat) ? (float) $berat : null,
                    'kebutuhan' => $kebutuhan,
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

            $material['kekurangan'] = max(
                $material['kebutuhan'] - $material['total_stok'],
                0
            );
            $material['saran_beli'] = max(
                $material['kebutuhan'] + $material['stok_minimum'] - $material['total_stok'],
                0
            );

            if ($material['alasan_belum_lengkap']) {
                $material['status'] = 'belum_lengkap';
            } elseif ($material['kekurangan'] > 0) {
                $material['status'] = 'kurang';
            } else {
                $material['status'] = 'aman';
            }

            if ($statusFilter !== '' && $material['status'] !== $statusFilter) {
                continue;
            }
            $data[] = $material;
        }

        $urutanStatus = ['belum_lengkap' => 0, 'kurang' => 1, 'aman' => 2];
        usort($data, static function (array $a, array $b) use ($urutanStatus): int {
            $bandingStatus = ($urutanStatus[$a['status']] ?? 9)
                <=> ($urutanStatus[$b['status']] ?? 9);
            return $bandingStatus !== 0
                ? $bandingStatus
                : strcasecmp($a['kode_material'], $b['kode_material']);
        });

        $ringkasan = [
            'total' => count($data),
            'aman' => 0,
            'kurang' => 0,
            'belum_lengkap' => 0,
            'peringatan' => count($peringatan),
            'vendor_supply' => count($vendorSupply),
            'full_beli_jadi' => count($fullBeliJadi),
        ];
        foreach ($data as $material) {
            $ringkasan[$material['status']]++;
        }

        return [
            'data' => $data,
            'summary' => $ringkasan,
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
            array_filter(explode(',', (string) $material), static fn($id) => trim((string) $id) !== '')
        )));
    }

    private function hasilKosong(): array
    {
        return [
            'data' => [],
            'summary' => [
                'total' => 0,
                'aman' => 0,
                'kurang' => 0,
                'belum_lengkap' => 0,
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
