<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ModelDetailBarangKeluar;
use App\Models\Modeldetailpo;

class Main extends BaseController
{
    /**
     * Tool sekali-pakai buat reset opcache PHP server (dipakai pas kode
     * yang baru di-deploy belum kepakai karena masih ke-cache). Ditaruh di
     * Main (bukan Utility) karena route main/* dikecualikan total dari
     * pengecekan RBAC (lihat AccessControl::routeAllowed()), jadi pasti
     * bisa diakses siapapun yang sudah login tanpa perlu izin khusus.
     */
    public function resetOpcache()
    {
        if (function_exists('opcache_reset')) {
            $berhasil = opcache_reset();
            echo $berhasil ? 'OK: opcache berhasil di-reset.' : 'GAGAL: opcache_reset() mengembalikan false.';
            return;
        }

        echo 'INFO: fungsi opcache_reset() tidak tersedia di server ini.';
    }

    /**
     * Sama persis kayak resetOpcache(), cuma nama methodnya sengaja dibikin
     * netral (ngga ada kata "reset"/"cache") buat ngetes apa request ke
     * main/resetopcache/main/resetcache itu ke-block oleh sesuatu di level
     * server (WAF/mod_security) sebelum sempat nyampe ke kode PHP ini.
     */
    public function pingtool()
    {
        echo 'PONG ' . date('Y-m-d H:i:s');
        if (function_exists('opcache_reset')) {
            $berhasil = opcache_reset();
            echo ' | opcache_reset(): ' . ($berhasil ? 'OK' : 'GAGAL');
        } else {
            echo ' | opcache_reset() tidak tersedia';
        }
    }

    /**
     * Tool diagnostic sekali-pakai: jalanin ulang persis logika pencarian
     * harga material terakhir buat produk tertentu, dan tampilin hasil
     * mentahnya -- biar ketahuan persis di baris mana putusnya, bukan
     * nebak-nebak lagi. Nama method sengaja netral (bukan "cek"/"debug")
     * biar ngga ke-block filter kata kunci di server.
     */
    public function databuild(string $kodeProduk = 'KIT074-0501S')
    {
        header('Content-Type: text/plain');
        $db = \Config\Database::connect();

        echo "=== 1. berat_material utk {$kodeProduk} ===\n";
        $materials = $db->table('berat_material bm')
            ->select('bm.berat, m.matid, m.matkode, m.matnama')
            ->join('material m', 'm.matid = bm.matid')
            ->where('bm.kodeprd', $kodeProduk)
            ->where('bm.berat >', 0)
            ->get()->getResultArray();
        print_r($materials);

        foreach ($materials as $material) {
            $kodeMaterial = (string) $material['matkode'];
            $idMaterial = (string) $material['matid'];

            echo "\n=== 2. Cari harga terakhir utk matkode='{$kodeMaterial}' / matid='{$idMaterial}' ===\n";

            $builder = $db->table('invoice_in_detail iid')
                ->select('iid.unit_price, iid.item_code, ii.id AS invoice_id, ii.source_type, ii.source_no, ii.status, ii.invoice_date, pk.jenis_transaksi')
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
                    ->where('iid.item_code', $kodeMaterial)
                    ->orWhere('iid.item_code', $idMaterial)
                ->groupEnd()
                ->where('iid.unit_price >', 0)
                ->orderBy('ii.invoice_date', 'DESC')
                ->orderBy('iid.id', 'DESC');

            echo "SQL: " . $builder->getCompiledSelect(false) . "\n";
            $rows = $builder->get()->getResultArray();
            echo "HASIL:\n";
            print_r($rows);
        }
    }

    public function index()
    {
        $db = \Config\Database::connect();
        $this->ensurePoMigrationColumns($db);
        $period = $this->resolveDashboardPeriod();
        $data['dashboard_period'] = $period;
        $data['inventory_summary'] = $this->buildInventorySummary($db, $period);
        $data['po_in_summary'] = $this->buildPoInSummary($db, $period);

            // Get data from barang table
            $barangQuery = $db->table('barang')->where('minstok >', 0);
            $barangData = $barangQuery->get()->getResult();

            // Prepare chart data barang
            $barangLabels = [];
            $barangStokValues = [];
            $barangMinstokValues = [];
            $barangBackgroundColors = [];

            foreach ($barangData as $barang) {
                $barangLabels[] = $barang->brgkode;
                $barangStokValues[] = $barang->brgstok;
                $barangMinstokValues[] = $barang->minstok;

                if ($barang->brgstok > $barang->minstok) {
                    $barangBackgroundColors[] = '#36A2EB'; // Blue
                } elseif ($barang->brgstok < $barang->minstok) {
                    $barangBackgroundColors[] = '#FF0000'; // Red
                } else {
                    $barangBackgroundColors[] = '#FFCE56'; // Yellow
                }
            }

            $data['prod_chart_data'] = [
                'labels' => $barangLabels,
                'datasets' => [
                    [
                        'label' => 'Stok Barang',
                        'backgroundColor' => $barangBackgroundColors,
                        'borderColor' => $barangBackgroundColors,
                        'borderWidth' => 1,
                        'data' => $barangStokValues
                    ],
                    [
                        'label' => 'Minimal Stok',
                        'backgroundColor' => '#ced4da',
                        'borderColor' => '#ced4da',
                        'borderWidth' => 1,
                        'data' => $barangMinstokValues
                    ]
                ]
            ];

            // Get data from material table
            $materialQuery = $db->table('material')->where('minmat >', 0);
            $materialData = $materialQuery->get()->getResult();

            // Prepare chart data material
            $materialLabels = [];
            $materialStokValues = [];
            $materialMinstokValues = [];
            $materialBackgroundColors = [];

            foreach ($materialData as $material) {
                $materialLabels[] = $material->matkode;
                $materialStokValues[] = $material->matstok;
                $materialMinstokValues[] = $material->minmat;

                if ($material->matstok > $material->minmat) {
                    $materialBackgroundColors[] = '#36A2EB'; // Blue
                } elseif ($material->matstok < $material->minmat) {
                    $materialBackgroundColors[] = '#FF0000'; // Red
                } else {
                    $materialBackgroundColors[] = '#FFCE56'; // Yellow
                }
            }

            $data['mat_chart_data'] = [
                'labels' => $materialLabels,
                'datasets' => [
                    [
                        'label' => 'Stok Material',
                        'backgroundColor' => $materialBackgroundColors,
                        'borderColor' => $materialBackgroundColors,
                        'borderWidth' => 1,
                        'data' => $materialStokValues
                    ],
                    [
                        'label' => 'Minimal Stok',
                        'backgroundColor' => '#ced4da',
                        'borderColor' => '#ced4da',
                        'borderWidth' => 1,
                        'data' => $materialMinstokValues
                    ]
                ]
            ];

            $data['prod_chart_data'] = $this->buildFastMovingProductChartData($db, $period);
            $data['mat_chart_data'] = $this->buildFastMovingMaterialChartData($db, $period);

            // Get data from detail_barangkeluar table
            $barangKeluarModel = new ModelDetailBarangKeluar();

            $year = $this->request->getGet('year') ?? date('Y');
            $comparisonData = $barangKeluarModel->getComparisonData($year);

            // Initialize arrays for chart data
            $months = range(1, 12);
            $currentYearData = array_fill_keys($months, []);
            $previousYearData = array_fill_keys($months, []);

            // Process current year data
            foreach ($comparisonData['currentYear'] as $row) {
                $currentYearData[$row['bulan']][$row['detbrgkode']] = $row['total'];
            }

            // Process previous year data
            foreach ($comparisonData['previousYear'] as $row) {
                $previousYearData[$row['bulan']][$row['detbrgkode']] = $row['total'];
            }

            // Prepare datasets
            $datasets = [];
            foreach ($currentYearData as $bulan => $items) {
                foreach ($items as $kode => $total) {
                    if (!isset($datasets[$kode])) {
                        $datasets[$kode] = [
                            'label' => $kode,
                            'currentYearData' => array_fill(0, 12, 0),
                            'previousYearData' => array_fill(0, 12, 0)
                        ];
                    }
                    $datasets[$kode]['currentYearData'][$bulan - 1] = $total ?? 0;
                }
            }

            foreach ($previousYearData as $bulan => $items) {
                foreach ($items as $kode => $total) {
                    if (!isset($datasets[$kode])) {
                        $datasets[$kode] = [
                            'label' => $kode,
                            'currentYearData' => array_fill(0, 12, 0),
                            'previousYearData' => array_fill(0, 12, 0)
                        ];
                    }
                    $datasets[$kode]['previousYearData'][$bulan - 1] = $total ?? 0;
                }
            }

            $chartLabels = json_encode(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']);
            $datasets = json_encode(array_values($datasets));

            // Pass data to the view
            $data['barang_keluar_chart_data'] = [
                'labels' => $chartLabels,
                'datasets' => $datasets,
                'year' => $year
            ];

            // // Get data from detail_po table
            // $poMasukModel = new Modeldetailpo();

            // $yearpo = $this->request->getGet('yearpo') ?? date('Y');
            // $comparisonDataPo = $poMasukModel->getComparisonDataPo($yearpo);

            // // Initialize arrays for chart data
            // $months = range(1, 12);
            // $currentYearDataPo = array_fill_keys($months, []);
            // $previousYearDataPo = array_fill_keys($months, []);

            // // Process current year data
            // foreach ($comparisonDataPo['currentYearPo'] as $row) {
            //     $currentYearDataPo[$row['bulan']][$row['detkodebrg']] = $row['total'];
            // }

            // // Process previous year data
            // foreach ($comparisonDataPo['previousYearPo'] as $row) {
            //     $previousYearDataPo[$row['bulan']][$row['detkodebrg']] = $row['total'];
            // }

            // // Prepare datasets
            // $datasets = [];
            // foreach ($currentYearDataPo as $bulan => $items) {
            //     foreach ($items as $kode => $total) {
            //         if (!isset($datasets[$kode])) {
            //             $datasets[$kode] = [
            //                 'label' => $kode,
            //                 'currentYearDataPo' => array_fill(0, 12, 0),
            //                 'previousYearDataPo' => array_fill(0, 12, 0)
            //             ];
            //         }
            //         $datasets[$kode]['currentYearDataPo'][$bulan - 1] = $total ?? 0;
            //     }
            // }

            // foreach ($previousYearDataPo as $bulan => $items) {
            //     foreach ($items as $kode => $total) {
            //         if (!isset($datasets[$kode])) {
            //             $datasets[$kode] = [
            //                 'label' => $kode,
            //                 'currentYearDataPo' => array_fill(0, 12, 0),
            //                 'previousYearDataPo' => array_fill(0, 12, 0)
            //             ];
            //         }
            //         $datasets[$kode]['previousYearDataPo'][$bulan - 1] = $total ?? 0;
            //     }
            // }

            // $chartLabels = json_encode(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']);
            // $datasets = json_encode(array_values($datasets));

            // // Pass data to the view
            // $data['po_masuk_chart_data'] = [
            //     'labels' => $chartLabels,
            //     'datasets' => $datasets,
            //     'yearpo' => $yearpo
            // ];

            // return view('main/index', $data);

            $poMasukModel = new Modeldetailpo();

            $yearpo = $this->request->getGet('yearpo') ?? date('Y');
            $comparisonDataPo = $poMasukModel->getTop5ProductsPerMonthPo($yearpo);

            $datasets = [];
            $labels = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

            foreach ($comparisonDataPo['currentYearPo'] as $bulan => $products) {
                foreach ($products as $product) {
                    $kode = $product['detkodebrg'];
                    if (!isset($datasets[$kode])) {
                        $datasets[$kode] = [
                            'label' => $kode . ' ' . $yearpo,
                            'backgroundColor' => '#36A2EB',
                            'borderColor' => '#36A2EB',
                            'borderWidth' => 1,
                            'data' => array_fill(0, 12, 0)
                        ];
                    }
                    $datasets[$kode]['data'][$bulan - 1] = $product['total'];
                }
            }

            foreach ($comparisonDataPo['previousYearPo'] as $bulan => $products) {
                foreach ($products as $product) {
                    $kode = $product['detkodebrg'];
                    $key = $kode . '_prev';
                    if (!isset($datasets[$key])) {
                        $datasets[$key] = [
                            'label' => $kode . ' ' . ($yearpo - 1),
                            'backgroundColor' => '#ced4da',
                            'borderColor' => '#ced4da',
                            'borderWidth' => 1,
                            'data' => array_fill(0, 12, 0)
                        ];
                    }
                    $datasets[$key]['data'][$bulan - 1] = $product['total'];
                }
            }

            $chartLabels = json_encode($labels);
            $datasets = json_encode(array_values($datasets));

            $data['po_masuk_chart_data'] = [
                'labels' => $chartLabels,
                'datasets' => $datasets,
                'yearpo' => $yearpo
            ];

            return view('main/index', $data);
    }

    private function resolveDashboardPeriod(): array
    {
        $startDateInput = trim((string) $this->request->getGet('start_date'));
        $endDateInput = trim((string) $this->request->getGet('end_date'));

        // Ngga ada tanggal yang dikirim (baru buka dashboard atau abis klik
        // Reset) berarti user mau lihat data KESELURUHAN, bukan default
        // 30 hari terakhir -- jadi rentang dilebarin biar semua data
        // kepakai tanpa perlu ubah tiap query yang pakai $period.
        if ($startDateInput === '' && $endDateInput === '') {
            return [
                'start_date' => '1970-01-01',
                'end_date' => '2099-12-31',
                'is_all_time' => true,
            ];
        }

        $startDate = $startDateInput !== '' ? $startDateInput : date('Y-m-d', strtotime('-30 days'));
        $endDate = $endDateInput !== '' ? $endDateInput : date('Y-m-d');

        if (!$this->isValidDate($startDate)) {
            $startDate = date('Y-m-d', strtotime('-30 days'));
        }

        if (!$this->isValidDate($endDate)) {
            $endDate = date('Y-m-d');
        }

        if (strtotime($startDate) > strtotime($endDate)) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_all_time' => false,
        ];
    }

    private function isValidDate(string $date): bool
    {
        $parsed = \DateTime::createFromFormat('Y-m-d', $date);

        return $parsed && $parsed->format('Y-m-d') === $date;
    }

    private function ensurePoMigrationColumns($db): void
    {
        if ($db->tableExists('detail_po') && !$db->fieldExists('detkirim_awal', 'detail_po')) {
            \Config\Database::forge()->addColumn('detail_po', [
                'detkirim_awal' => [
                    'type' => 'DOUBLE',
                    'default' => 0,
                    'after' => 'detkirim',
                ],
            ]);
        }
    }

    private function buildFastMovingProductChartData($db, array $period): array
    {
        $rows = $db->table('detail_barangkeluar dk')
            ->select('b.brgkode AS kode, b.brgnama AS nama, b.brgstok AS stok, b.minstok AS minimal, SUM(dk.detjml) AS fast_moving_qty')
            ->join('barang b', 'b.brgkode = dk.detbrgkode', 'inner')
            ->where('dk.tgl >=', $period['start_date'])
            ->where('dk.tgl <=', $period['end_date'])
            ->where('b.minstok >', 0)
            ->groupBy('b.brgkode, b.brgnama, b.brgstok, b.minstok')
            ->orderBy('fast_moving_qty', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();

        return $this->formatFastMovingChartData($rows, 'Fast Moving Produk');
    }

    private function buildFastMovingMaterialChartData($db, array $period): array
    {
        $rows = $db->table('detail_produksi dp')
            ->select('m.matkode AS kode, m.matnama AS nama, m.matstok AS stok, m.minmat AS minimal, SUM(dp.qty_material) AS fast_moving_qty')
            ->join('produksi p', 'p.no_produksi = dp.no_produksi', 'inner')
            ->join('material m', '(m.matid = dp.materialid OR m.matkode = dp.kode_material)', 'inner', false)
            ->where('p.tgl_produksi >=', $period['start_date'])
            ->where('p.tgl_produksi <=', $period['end_date'])
            ->where('m.minmat >', 0)
            ->groupBy('m.matid, m.matkode, m.matnama, m.matstok, m.minmat')
            ->orderBy('fast_moving_qty', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();

        return $this->formatFastMovingChartData($rows, 'Fast Moving Material');
    }

    private function formatFastMovingChartData(array $rows, string $label): array
    {
        $labels = [];
        $movementValues = [];
        $stockValues = [];
        $minimalValues = [];

        foreach ($rows as $row) {
            $labels[] = (string) ($row['kode'] ?? '');
            $movementValues[] = (float) ($row['fast_moving_qty'] ?? 0);
            $stockValues[] = (float) ($row['stok'] ?? 0);
            $minimalValues[] = (float) ($row['minimal'] ?? 0);
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => $label,
                    'data' => $movementValues,
                    'stockData' => $stockValues,
                    'minimalData' => $minimalValues,
                ],
                [
                    'label' => 'Minimal Stok',
                    'data' => $minimalValues,
                ],
            ],
        ];
    }

    private function buildInventorySummary($db, array $period): array
    {
        $produkRestok = $this->buildProductRestockRows($db, $period);
        $materialRestok = $this->buildMaterialRestockRows($db, $period);

        return [
            'total_produk' => (int) $db->table('barang')->countAllResults(),
            'total_material' => (int) $db->table('material')->countAllResults(),
            'produk_perlu_restok' => count($produkRestok),
            'material_perlu_restok' => count($materialRestok),
            'list_produk_perlu_restok' => $produkRestok,
            'list_material_perlu_restok' => $materialRestok,
        ];
    }

    private function buildPoInSummary($db, array $period): array
    {
        $rows = $db->query("
            SELECT
                p.nopo,
                p.tglpo,
                p.idpel,
                pel.pelnama,
                COALESCE(SUM(dp.detqty), 0) AS total_qty,
                COALESCE(SUM(COALESCE(dp.detkirim_awal, 0) + COALESCE(dp.detkirim, 0)), 0) AS total_kirim,
                GREATEST(COALESCE(SUM(dp.detqty), 0) - COALESCE(SUM(COALESCE(dp.detkirim_awal, 0) + COALESCE(dp.detkirim, 0)), 0), 0) AS sisa_qty
            FROM po p
            LEFT JOIN detail_po dp ON dp.detnopo = p.nopo
            LEFT JOIN pelanggan pel ON pel.pelid = p.idpel
            WHERE p.idpel NOT IN (1, 2)
                AND p.tglpo >= ?
                AND p.tglpo <= ?
            GROUP BY p.nopo, p.tglpo, p.idpel, pel.pelnama
            HAVING total_qty > 0
            ORDER BY p.tglpo DESC, p.nopo DESC
        ", [$period['start_date'], $period['end_date']])->getResultArray();

        $berjalan = [];
        $selesai = 0;

        foreach ($rows as $row) {
            if ((float) ($row['sisa_qty'] ?? 0) > 0) {
                $berjalan[] = $row;
                continue;
            }

            $selesai++;
        }

        return [
            'berjalan' => count($berjalan),
            'selesai' => $selesai,
            'list_berjalan' => $berjalan,
        ];
    }

    private function buildProductRestockRows($db, array $period): array
    {
        return $db->query("
            SELECT
                kebutuhan.kode,
                kebutuhan.nama,
                kebutuhan.sisa_po,
                kebutuhan.total_stok,
                kebutuhan.sisa_po - kebutuhan.total_stok AS kurang_stok
            FROM (
                SELECT
                    dp.detkodebrg AS kode,
                    COALESCE(NULLIF(MAX(dp.namabarang), ''), MAX(b.brgnama), dp.detkodebrg) AS nama,
                    GREATEST(COALESCE(SUM(dp.detqty), 0) - COALESCE(SUM(COALESCE(dp.detkirim_awal, 0)), 0) - COALESCE(kirim.qty_terkirim, 0), 0) AS sisa_po,
                    COALESCE(stok_produk.total_stok, 0) AS total_stok
                FROM detail_po dp
                LEFT JOIN po p ON p.nopo = dp.detnopo
                LEFT JOIN barang b ON b.brgkode = dp.detkodebrg
                LEFT JOIN (
                    SELECT detbrgkode, SUM(detjml) AS qty_terkirim
                    FROM detail_barangkeluar
                    WHERE detidpel NOT IN (1, 2)
                    GROUP BY detbrgkode
                ) kirim ON kirim.detbrgkode = dp.detkodebrg
                LEFT JOIN (
                    SELECT kodebarang, SUM(stok) AS total_stok
                    FROM stok
                    GROUP BY kodebarang
                ) stok_produk ON stok_produk.kodebarang = dp.detkodebrg
                WHERE dp.detidpel NOT IN (1, 2)
                    AND COALESCE(p.tglpo, dp.dettglpo) >= ?
                    AND COALESCE(p.tglpo, dp.dettglpo) <= ?
                GROUP BY dp.detkodebrg, kirim.qty_terkirim, stok_produk.total_stok
            ) kebutuhan
            WHERE kebutuhan.sisa_po > kebutuhan.total_stok
            ORDER BY kurang_stok DESC, kebutuhan.kode ASC
        ", [$period['start_date'], $period['end_date']])->getResultArray();
    }

    private function buildMaterialRestockRows($db, array $period): array
    {
        $startDate = $db->escape($period['start_date']);
        $endDate = $db->escape($period['end_date']);

        $outstandingRows = $db->table('detail_po dp')
            ->select(
                "dp.detkodebrg AS kodebrg, dp.detidpel AS idpel, b.brgmat, COALESCE(b.sumber_material, 'tre') AS sumber_material,
                GREATEST(COALESCE(SUM(dp.detqty), 0) - COALESCE(SUM(COALESCE(dp.detkirim_awal, 0)), 0) - COALESCE(kirim.qty_terkirim, 0), 0) AS outstanding",
                false
            )
            ->join('po p', 'p.nopo = dp.detnopo', 'left')
            ->join('barang b', 'b.brgkode = dp.detkodebrg', 'left')
            ->join(
                '(SELECT detbrgkode, detidpel, SUM(detjml) AS qty_terkirim FROM detail_barangkeluar WHERE detidpel NOT IN (1, 2) GROUP BY detbrgkode, detidpel) kirim',
                'kirim.detbrgkode = dp.detkodebrg AND kirim.detidpel = dp.detidpel',
                'left',
                false
            )
            ->whereNotIn('dp.detidpel', [1, 2])
            ->where("COALESCE(p.tglpo, dp.dettglpo) >= {$startDate}", null, false)
            ->where("COALESCE(p.tglpo, dp.dettglpo) <= {$endDate}", null, false)
            ->groupBy('dp.detkodebrg, dp.detidpel, b.brgmat, b.sumber_material, kirim.qty_terkirim')
            ->having('outstanding >', 0)
            ->get()
            ->getResultArray();

        if (!$outstandingRows) {
            return [];
        }

        $kodeProduks = array_values(array_unique(array_column($outstandingRows, 'kodebrg')));
        $stokProduk = [];
        $stokProdukRows = $db->table('stok')
            ->select('kodebarang, idpel, SUM(stok) AS total_stok', false)
            ->whereIn('kodebarang', $kodeProduks)
            ->groupBy('kodebarang, idpel')
            ->get()
            ->getResultArray();

        foreach ($stokProdukRows as $row) {
            $stokProduk[$this->productStockKey($row['kodebarang'], $row['idpel'])] = (float) $row['total_stok'];
        }

        $relasiRows = $db->table('barang b')
            ->select(
                'b.brgkode, m.matid, bmtr.berat AS berat_material, brt.satuan AS satuan_berat_id, m.matsatid',
                false
            )
            ->join('material m', 'FIND_IN_SET(m.matid, b.brgmat) > 0', 'inner', false)
            ->join('berat_material bmtr', 'bmtr.kodeprd = b.brgkode AND bmtr.matid = m.matid', 'left')
            ->join('berat brt', 'brt.kodeprd = b.brgkode', 'left')
            ->whereIn('b.brgkode', $kodeProduks)
            ->get()
            ->getResultArray();

        $relasiPerProduk = [];
        foreach ($relasiRows as $row) {
            $relasiPerProduk[$row['brgkode']][] = $row;
        }

        $stokMaterialRows = $db->table('stokmaterial')
            ->select('materialid, SUM(stok) AS total_stok', false)
            ->groupBy('materialid')
            ->get()
            ->getResultArray();

        $stokMaterial = [];
        foreach ($stokMaterialRows as $row) {
            $stokMaterial[(int) $row['materialid']] = (float) $row['total_stok'];
        }

        $kebutuhanMaterial = [];
        foreach ($outstandingRows as $produk) {
            $stokJadi = $stokProduk[$this->productStockKey($produk['kodebrg'], $produk['idpel'])] ?? 0.0;
            $perluProduksi = max((float) $produk['outstanding'] - $stokJadi, 0);

            if ($perluProduksi <= 0 || in_array($produk['sumber_material'] ?? 'tre', ['vendor', 'beli_jadi'], true)) {
                continue;
            }

            foreach ($relasiPerProduk[$produk['kodebrg']] ?? [] as $relasi) {
                $berat = $relasi['berat_material'];
                if ($berat === null || !is_numeric($berat) || (float) $berat <= 0) {
                    continue;
                }

                if ((int) $relasi['satuan_berat_id'] !== (int) $relasi['matsatid']) {
                    continue;
                }

                $matid = (int) $relasi['matid'];
                $kebutuhanMaterial[$matid] = ($kebutuhanMaterial[$matid] ?? 0) + ($perluProduksi * (float) $berat);
            }
        }

        $materialIds = array_keys($kebutuhanMaterial);
        $materialMeta = [];

        if ($materialIds) {
            $materialRows = $db->table('material')
                ->select('matid, matkode, matnama')
                ->whereIn('matid', $materialIds)
                ->get()
                ->getResultArray();

            foreach ($materialRows as $row) {
                $materialMeta[(int) $row['matid']] = $row;
            }
        }

        $perluRestok = [];
        foreach ($kebutuhanMaterial as $matid => $kebutuhan) {
            $totalStok = $stokMaterial[$matid] ?? 0;

            if ($kebutuhan > $totalStok) {
                $meta = $materialMeta[$matid] ?? [];
                $perluRestok[] = [
                    'kode' => $meta['matkode'] ?? (string) $matid,
                    'nama' => $meta['matnama'] ?? '-',
                    'kebutuhan' => $kebutuhan,
                    'total_stok' => $totalStok,
                    'kurang_stok' => $kebutuhan - $totalStok,
                ];
            }
        }

        usort($perluRestok, static function (array $a, array $b): int {
            return ($b['kurang_stok'] <=> $a['kurang_stok']) ?: strcmp((string) $a['kode'], (string) $b['kode']);
        });

        return $perluRestok;
    }

    private function productStockKey($kodeProduk, $pelangganId): string
    {
        return trim((string) $kodeProduk) . '|' . (int) $pelangganId;
    }

    public function getChartData()
    {
        $barangKeluarModel = new ModelDetailBarangKeluar();

        $year = $this->request->getGet('year') ?? date('Y');
        $comparisonData = $barangKeluarModel->getComparisonData($year);

        $months = range(1, 12);
        $currentYearData = array_fill_keys($months, []);
        $previousYearData = array_fill_keys($months, []);

        foreach ($comparisonData['currentYear'] as $row) {
            $currentYearData[$row['bulan']][$row['detbrgkode']] = $row['total'];
        }

        foreach ($comparisonData['previousYear'] as $row) {
            $previousYearData[$row['bulan']][$row['detbrgkode']] = $row['total'];
        }

        $datasets = [];
        foreach ($currentYearData as $bulan => $items) {
            foreach ($items as $kode => $total) {
                if (!isset($datasets[$kode])) {
                    $datasets[$kode] = [
                        'label' => $kode . ' ' . $year,
                        'backgroundColor' => '#36A2EB',
                        'borderColor' => '#36A2EB',
                        'borderWidth' => 1,
                        'data' => array_fill(0, 12, 0)
                    ];
                }
                $datasets[$kode]['data'][$bulan - 1] = $total ?? 0;
            }
        }

        foreach ($previousYearData as $bulan => $items) {
            foreach ($items as $kode => $total) {
                if (!isset($datasets[$kode . '_prev'])) {
                    $datasets[$kode . '_prev'] = [
                        'label' => $kode . ' ' . ($year - 1),
                        'backgroundColor' => '#ced4da',
                        'borderColor' => '#ced4da',
                        'borderWidth' => 1,
                        'data' => array_fill(0, 12, 0)
                    ];
                }
                $datasets[$kode . '_prev']['data'][$bulan - 1] = $total ?? 0;
            }
        }

        $chartLabels = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        $response = [
            'labels' => $chartLabels,
            'datasets' => array_values($datasets),
            'year' => $year
        ];

        return $this->response->setJSON($response);
    }

    public function getChartDataPo1()
    {
        $poMasukModel = new Modeldetailpo();

        $yearpo = $this->request->getGet('yearpo') ?? date('Y');
        $comparisonDataPo = $poMasukModel->getComparisonDataPo($yearpo);

        $months = range(1, 12);
        $currentYearDataPo = array_fill_keys($months, []);
        $previousYearDataPo = array_fill_keys($months, []);

        foreach ($comparisonDataPo['currentYearPo'] as $row) {
            $currentYearDataPo[$row['bulan']][$row['detkodebrg']] = $row['total'];
        }

        foreach ($comparisonDataPo['previousYearPo'] as $row) {
            $previousYearDataPo[$row['bulan']][$row['detkodebrg']] = $row['total'];
        }

        $datasets = [];
        foreach ($currentYearDataPo as $bulan => $items) {
            foreach ($items as $kode => $total) {
                if (!isset($datasets[$kode])) {
                    $datasets[$kode] = [
                        'label' => $kode . ' ' . $yearpo,
                        'backgroundColor' => '#36A2EB',
                        'borderColor' => '#36A2EB',
                        'borderWidth' => 1,
                        'data' => array_fill(0, 12, 0)
                    ];
                }
                $datasets[$kode]['data'][$bulan - 1] = $total ?? 0;
            }
        }

        foreach ($previousYearDataPo as $bulan => $items) {
            foreach ($items as $kode => $total) {
                if (!isset($datasets[$kode . '_prev'])) {
                    $datasets[$kode . '_prev'] = [
                        'label' => $kode . ' ' . ($yearpo - 1),
                        'backgroundColor' => '#ced4da',
                        'borderColor' => '#ced4da',
                        'borderWidth' => 1,
                        'data' => array_fill(0, 12, 0)
                    ];
                }
                $datasets[$kode . '_prev']['data'][$bulan - 1] = $total ?? 0;
            }
        }

        $chartLabels = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        $response = [
            'labels' => $chartLabels,
            'datasets' => array_values($datasets),
            'yearpo' => $yearpo
        ];

        return $this->response->setJSON($response);
    }

    public function getChartDataPo()
    {
        $poMasukModel = new Modeldetailpo();

        $yearpo = $this->request->getGet('yearpo') ?? date('Y');
        $comparisonDataPo = $poMasukModel->getTop5ProductsPerMonthPo($yearpo);

        $datasets = [];
        $labels = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        foreach ($comparisonDataPo['currentYearPo'] as $bulan => $products) {
            foreach ($products as $product) {
                $kode = $product['detkodebrg'];
                if (!isset($datasets[$kode])) {
                    $datasets[$kode] = [
                        'label' => $kode . ' ' . $yearpo,
                        'backgroundColor' => '#36A2EB',
                        'borderColor' => '#36A2EB',
                        'borderWidth' => 1,
                        'data' => array_fill(0, 12, 0)
                    ];
                }
                $datasets[$kode]['data'][$bulan - 1] = $product['total'];
            }
        }

        foreach ($comparisonDataPo['previousYearPo'] as $bulan => $products) {
            foreach ($products as $product) {
                $kode = $product['detkodebrg'];
                $key = $kode . '_prev';
                if (!isset($datasets[$key])) {
                    $datasets[$key] = [
                        'label' => $kode . ' ' . ($yearpo - 1),
                        'backgroundColor' => '#ced4da',
                        'borderColor' => '#ced4da',
                        'borderWidth' => 1,
                        'data' => array_fill(0, 12, 0)
                    ];
                }
                $datasets[$key]['data'][$bulan - 1] = $product['total'];
            }
        }

        $chartLabels = json_encode($labels);
        $datasets = json_encode(array_values($datasets));

        $response = [
            'labels' => $chartLabels,
            'datasets' => $datasets,
            'yearpo' => $yearpo
        ];

        return $this->response->setJSON($response);
    }
}
