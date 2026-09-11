<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Dashboard
<?= $this->endSection('judul') ?>

<?= $this->section('isi'); ?>
<script src="<?= base_url() ?>/plugins/npm/chart.js"></script>
<script src="<?= base_url() ?>/plugins/npm/sweetalert211.js"></script>

<?php
$chartStats = static function (array $chartData): array {
    $labels = $chartData['labels'] ?? [];
    $stok = $chartData['datasets'][0]['stockData'] ?? ($chartData['datasets'][0]['data'] ?? []);
    $minimal = $chartData['datasets'][0]['minimalData'] ?? ($chartData['datasets'][1]['data'] ?? []);
    $total = count($labels);
    $kurang = 0;
    $cukupPas = 0;
    $aman = 0;
    $terendah = [];

    foreach ($labels as $index => $label) {
        $stokValue = (float) ($stok[$index] ?? 0);
        $minValue = (float) ($minimal[$index] ?? 0);
        $selisih = $stokValue - $minValue;

        if ($stokValue < $minValue) {
            $kurang++;
            $terendah[] = ['label' => $label, 'stok' => $stokValue, 'minimal' => $minValue, 'selisih' => $selisih];
        } elseif ($stokValue == $minValue) {
            $cukupPas++;
        } else {
            $aman++;
        }
    }

    usort($terendah, static fn ($a, $b) => $a['selisih'] <=> $b['selisih']);

    return [
        'total' => $total,
        'kurang' => $kurang,
        'cukup_pas' => $cukupPas,
        'aman' => $aman,
        'terendah' => array_slice($terendah, 0, 5),
    ];
};

$productStats = $chartStats($prod_chart_data ?? []);
$materialStats = $chartStats($mat_chart_data ?? []);
$selectedYear = (int) ($barang_keluar_chart_data['year'] ?? date('Y'));
$selectedYearPo = (int) ($_GET['yearpo'] ?? date('Y'));
?>

<style>
    .dashboard-page {
        color: #111827;
    }

    .dashboard-summary {
        display: grid;
        gap: 1rem;
        grid-template-columns: repeat(auto-fit, minmax(12rem, 1fr));
        margin-bottom: 1rem;
    }

    .summary-tile {
        align-items: center;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-left: 4px solid #0d6efd;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(15, 23, 42, .05);
        display: flex;
        gap: .85rem;
        min-height: 5.5rem;
        padding: 1rem;
    }

    .summary-tile.warning {
        border-left-color: #ffc107;
    }

    .summary-tile.danger {
        border-left-color: #dc3545;
    }

    .summary-tile.success {
        border-left-color: #28a745;
    }

    .summary-icon {
        align-items: center;
        background: #f3f4f6;
        border-radius: 8px;
        display: flex;
        flex: 0 0 2.75rem;
        height: 2.75rem;
        justify-content: center;
        width: 2.75rem;
    }

    .summary-label {
        color: #6b7280;
        font-size: .86rem;
        margin-bottom: .15rem;
    }

    .summary-value {
        font-size: 1.45rem;
        font-weight: 700;
        line-height: 1.1;
    }

    .dashboard-grid {
        display: grid;
        gap: 1rem;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .dashboard-panel {
        background: #fff;
        border: 1px solid #eef0f5;
        border-radius: 14px;
        box-shadow: 0 2px 12px rgba(15, 23, 42, .06);
        margin-bottom: 1rem;
        overflow: hidden;
    }

    .panel-header {
        align-items: center;
        display: flex;
        gap: .75rem;
        justify-content: space-between;
        min-height: 3.75rem;
        padding: 1.05rem 1.25rem .35rem;
    }

    .panel-title {
        color: #171923;
        font-size: 1.02rem;
        font-weight: 700;
        letter-spacing: 0;
        margin: 0;
    }

    .panel-subtitle {
        color: #9ca3af;
        font-size: .8rem;
        margin-top: .15rem;
    }

    .panel-body {
        padding: .4rem 1.25rem 1.05rem;
    }

    .chart-frame {
        height: 19.5rem;
        position: relative;
    }

    .chart-frame.tall {
        height: 21rem;
    }

    .chart-empty {
        color: #6b7280;
        display: none;
        left: 50%;
        position: absolute;
        text-align: center;
        top: 50%;
        transform: translate(-50%, -50%);
    }

    .dashboard-legend {
        display: flex;
        flex-wrap: wrap;
        gap: .55rem 1.15rem;
        justify-content: center;
        margin-top: .85rem;
    }

    .legend-item {
        align-items: center;
        color: #6b7280;
        display: inline-flex;
        font-size: .88rem;
        gap: .35rem;
    }

    .legend-dot {
        border-radius: 999px;
        display: inline-block;
        height: .55rem;
        width: .55rem;
    }

    .stock-watch {
        border-top: 1px solid #eef0f3;
        margin-top: 1rem;
        padding-top: .75rem;
    }

    .stock-watch-title {
        color: #374151;
        font-size: .86rem;
        font-weight: 700;
        margin-bottom: .5rem;
    }

    .stock-watch-list {
        display: grid;
        gap: .4rem;
        margin: 0;
        padding: 0;
    }

    .stock-watch-item {
        align-items: center;
        background: #f9fafb;
        border: 1px solid #edf0f3;
        border-radius: 6px;
        display: flex;
        justify-content: space-between;
        list-style: none;
        padding: .45rem .6rem;
    }

    .stock-watch-item span:first-child {
        font-weight: 600;
    }

    .year-control {
        align-items: center;
        display: flex;
        gap: .5rem;
    }

    .year-control label {
        color: #6b7280;
        font-size: .86rem;
        margin: 0;
        white-space: nowrap;
    }

    .year-control select {
        min-width: 6rem;
    }

    @media (max-width: 992px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }

        .chart-frame,
        .chart-frame.tall {
            height: 20rem;
        }
    }
</style>

<div class="dashboard-page">
    <div class="dashboard-summary">
        <div class="summary-tile success">
            <div class="summary-icon"><i class="fas fa-boxes text-success"></i></div>
            <div>
                <div class="summary-label">Produk Terpantau</div>
                <div class="summary-value"><?= number_format($productStats['total'], 0, ',', '.') ?></div>
            </div>
        </div>
        <div class="summary-tile danger">
            <div class="summary-icon"><i class="fas fa-exclamation-triangle text-danger"></i></div>
            <div>
                <div class="summary-label">Produk Stok Kurang</div>
                <div class="summary-value"><?= number_format($productStats['kurang'], 0, ',', '.') ?></div>
            </div>
        </div>
        <div class="summary-tile success">
            <div class="summary-icon"><i class="fas fa-dolly-flatbed text-success"></i></div>
            <div>
                <div class="summary-label">Material Terpantau</div>
                <div class="summary-value"><?= number_format($materialStats['total'], 0, ',', '.') ?></div>
            </div>
        </div>
        <div class="summary-tile warning">
            <div class="summary-icon"><i class="fas fa-bell text-warning"></i></div>
            <div>
                <div class="summary-label">Material Perlu Dicek</div>
                <div class="summary-value"><?= number_format($materialStats['kurang'] + $materialStats['cukup_pas'], 0, ',', '.') ?></div>
            </div>
        </div>
    </div>

    <div class="dashboard-grid">
        <section class="dashboard-panel">
            <div class="panel-header">
                <div>
                    <h3 class="panel-title">Stok Produk dengan Minimal Stok</h3>
                    <div class="panel-subtitle">Perbandingan stok aktual produk terhadap batas minimal.</div>
                </div>
            </div>
            <div class="panel-body">
                <div class="chart-frame">
                    <canvas id="prod-stok-chart"></canvas>
                    <div id="prod-chart-tooltip" class="chart-empty">Data Stok Belum Tersedia.</div>
                </div>
                <div class="dashboard-legend">
                    <span class="legend-item"><i class="legend-dot" style="background:#0d6efd"></i> Stok</span>
                    <span class="legend-item"><i class="legend-dot" style="background:#ffc107"></i> Stok Cukup</span>
                    <span class="legend-item"><i class="legend-dot" style="background:#dc3545"></i> Stok Kurang</span>
                    <span class="legend-item"><i class="legend-dot" style="background:#6c757d"></i> Minimal Stok</span>
                </div>
                <div class="stock-watch">
                    <div class="stock-watch-title">Produk paling perlu perhatian</div>
                    <?php if (!empty($productStats['terendah'])) : ?>
                        <ul class="stock-watch-list">
                            <?php foreach ($productStats['terendah'] as $item) : ?>
                                <li class="stock-watch-item">
                                    <span><?= esc($item['label']) ?></span>
                                    <small>Stok <?= number_format($item['stok'], 0, ',', '.') ?> / Min <?= number_format($item['minimal'], 0, ',', '.') ?></small>
                                </li>
                            <?php endforeach ?>
                        </ul>
                    <?php else : ?>
                        <small class="text-muted">Tidak ada produk di bawah minimal stok.</small>
                    <?php endif ?>
                </div>
            </div>
        </section>

        <section class="dashboard-panel">
            <div class="panel-header">
                <div>
                    <h3 class="panel-title">Stok Material dengan Minimal Stok</h3>
                    <div class="panel-subtitle">Pantauan material berdasarkan minimal stok yang sudah disetel.</div>
                </div>
            </div>
            <div class="panel-body">
                <div class="chart-frame">
                    <canvas id="mat-stok-chart"></canvas>
                    <div id="mat-chart-tooltip" class="chart-empty">Data Material Belum Tersedia.</div>
                </div>
                <div class="dashboard-legend">
                    <span class="legend-item"><i class="legend-dot" style="background:#0d6efd"></i> Stok</span>
                    <span class="legend-item"><i class="legend-dot" style="background:#ffc107"></i> Stok Cukup</span>
                    <span class="legend-item"><i class="legend-dot" style="background:#dc3545"></i> Stok Kurang</span>
                    <span class="legend-item"><i class="legend-dot" style="background:#6c757d"></i> Minimal Stok</span>
                </div>
                <div class="stock-watch">
                    <div class="stock-watch-title">Material paling perlu perhatian</div>
                    <?php if (!empty($materialStats['terendah'])) : ?>
                        <ul class="stock-watch-list">
                            <?php foreach ($materialStats['terendah'] as $item) : ?>
                                <li class="stock-watch-item">
                                    <span><?= esc($item['label']) ?></span>
                                    <small>Stok <?= number_format($item['stok'], 0, ',', '.') ?> / Min <?= number_format($item['minimal'], 0, ',', '.') ?></small>
                                </li>
                            <?php endforeach ?>
                        </ul>
                    <?php else : ?>
                        <small class="text-muted">Tidak ada material di bawah minimal stok.</small>
                    <?php endif ?>
                </div>
            </div>
        </section>
    </div>

    <section class="dashboard-panel">
        <div class="panel-header">
            <div>
                <h3 class="panel-title">Produk Terbanyak Keluar</h3>
                <div class="panel-subtitle">Perbandingan transaksi produk keluar per bulan.</div>
            </div>
            <div class="year-control">
                <label for="year">Tahun</label>
                <select id="year" name="year" class="form-control form-control-sm">
                    <?php for ($i = date('Y'); $i >= 2015; $i--) : ?>
                        <option value="<?= $i ?>" <?= $i === $selectedYear ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
        </div>
        <div class="panel-body">
            <div class="chart-frame tall">
                <canvas id="barangKeluarChart"></canvas>
            </div>
            <div class="dashboard-legend">
                <span class="legend-item"><i class="legend-dot" style="background:#6f5de8"></i> Tahun <span id="current-year"><?= $selectedYear ?></span></span>
                <span class="legend-item"><i class="legend-dot" style="background:#cfd6ff"></i> Tahun <span id="previous-year"><?= $selectedYear - 1 ?></span></span>
            </div>
        </div>
    </section>

    <section class="dashboard-panel">
        <div class="panel-header">
            <div>
                <h3 class="panel-title">5 Produk Terlaris</h3>
                <div class="panel-subtitle">Produk terlaris per bulan dibandingkan dengan tahun sebelumnya.</div>
            </div>
            <div class="year-control">
                <label for="yearpo">Tahun</label>
                <select id="yearpo" name="yearpo" class="form-control form-control-sm">
                    <?php for ($i = date('Y'); $i >= 2015; $i--) : ?>
                        <option value="<?= $i ?>" <?= $i === $selectedYearPo ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
        </div>
        <div class="panel-body">
            <div class="chart-frame tall">
                <canvas id="poMasukChart"></canvas>
            </div>
            <div class="dashboard-legend">
                <span class="legend-item"><i class="legend-dot" style="background:#6f5de8"></i> Tahun <span id="current-yearpo"><?= $selectedYearPo ?></span></span>
                <span class="legend-item"><i class="legend-dot" style="background:#cfd6ff"></i> Tahun <span id="previous-yearpo"><?= $selectedYearPo - 1 ?></span></span>
            </div>
        </div>
    </section>
</div>

<script>
    const chartGrid = {
        color: 'rgba(148, 163, 184, .16)'
    };
    const chartTick = {
        color: '#8a8f98',
        font: {
            size: 11
        }
    };
    const chartPalette = ['#6f5de8', '#cfd6ff', '#9b8cf4', '#e4e8ff'];
    const roundedBarStyle = {
        borderWidth: 0,
        borderSkipped: false,
        borderRadius: {
            topLeft: 10,
            topRight: 10,
            bottomLeft: 2,
            bottomRight: 2
        },
        barPercentage: .66,
        categoryPercentage: .7,
        maxBarThickness: 48
    };
    const floatingTooltip = {
        backgroundColor: '#fff',
        borderColor: '#e5e7eb',
        borderWidth: 1,
        bodyColor: '#4b5563',
        bodyFont: {
            size: 12,
            weight: '600'
        },
        boxHeight: 8,
        boxWidth: 8,
        caretPadding: 8,
        cornerRadius: 8,
        displayColors: true,
        padding: 12,
        titleColor: '#111827',
        titleFont: {
            size: 12,
            weight: '700'
        },
        usePointStyle: true
    };

    function formatNumber(value) {
        return Number(value || 0).toLocaleString('id-ID');
    }

    function styleDashboardData(chartData, palette = chartPalette) {
        return {
            labels: chartData.labels || [],
            datasets: (chartData.datasets || []).map((dataset, index) => ({
                ...dataset,
                ...roundedBarStyle,
                backgroundColor: palette[index % palette.length],
                borderColor: palette[index % palette.length],
                hoverBackgroundColor: index === 0 ? '#5b48d8' : '#bfc8ff',
                pointStyle: 'circle'
            }))
        };
    }

    function styleStatusStockData(chartData) {
        const movement = chartData.datasets && chartData.datasets[0] ? chartData.datasets[0] : {};
        const minimal = chartData.datasets && chartData.datasets[1] ? chartData.datasets[1] : {};
        const movementValues = movement.data || [];
        const stockValues = movement.stockData || movementValues;
        const minimalValues = movement.minimalData || minimal.data || [];
        const colors = stockValues.map((value, index) => {
            const stockValue = Number(value || 0);
            const minimalValue = Number(minimalValues[index] || 0);

            if (stockValue < minimalValue) {
                return '#dc3545';
            }

            if (stockValue === minimalValue) {
                return '#ffc107';
            }

            return '#0d6efd';
        });
        const statusLabels = stockValues.map((value, index) => {
            const stockValue = Number(value || 0);
            const minimalValue = Number(minimalValues[index] || 0);

            if (stockValue < minimalValue) {
                return 'Stok Kurang';
            }

            if (stockValue === minimalValue) {
                return 'Stok Cukup';
            }

            return 'Stok Aman';
        });

        return {
            labels: chartData.labels || [],
            datasets: [{
                label: movement.label || 'Fast Moving',
                data: movementValues,
                stockData: stockValues,
                minimalData: minimalValues,
                statusLabels: statusLabels,
                backgroundColor: colors,
                borderColor: colors,
                borderSkipped: false,
                borderWidth: 0,
                borderRadius: {
                    topLeft: 10,
                    topRight: 10,
                    bottomLeft: 2,
                    bottomRight: 2
                },
                barPercentage: .62,
                categoryPercentage: .7,
                hoverBackgroundColor: colors,
                maxBarThickness: 42,
                pointStyle: 'circle'
            }]
        };
    }

    function showEmptyState(chartData, canvasId, tooltipId) {
        const hasData = chartData.labels && chartData.labels.length > 0;
        document.getElementById(canvasId).style.display = hasData ? 'block' : 'none';
        document.getElementById(tooltipId).style.display = hasData ? 'none' : 'block';
        return hasData;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const productData = styleStatusStockData({
            labels: <?= json_encode($prod_chart_data['labels']); ?>,
            datasets: <?= json_encode($prod_chart_data['datasets']); ?>
        });

        if (showEmptyState(productData, 'prod-stok-chart', 'prod-chart-tooltip')) {
            new Chart(document.getElementById('prod-stok-chart').getContext('2d'), {
                type: 'bar',
                data: productData,
                options: stockChartOptions('Produk')
            });
        }

        const materialData = styleStatusStockData({
            labels: <?= json_encode($mat_chart_data['labels']); ?>,
            datasets: <?= json_encode($mat_chart_data['datasets']); ?>
        });

        if (showEmptyState(materialData, 'mat-stok-chart', 'mat-chart-tooltip')) {
            new Chart(document.getElementById('mat-stok-chart').getContext('2d'), {
                type: 'bar',
                data: materialData,
                options: stockChartOptions('Material')
            });
        }

        const ctxBarangKeluar = document.getElementById('barangKeluarChart').getContext('2d');
        const barangKeluarChart = new Chart(ctxBarangKeluar, {
            type: 'bar',
            data: styleDashboardData({
                labels: <?= $barang_keluar_chart_data['labels']; ?>,
                datasets: <?= $barang_keluar_chart_data['datasets']; ?>
            }),
            options: comparisonChartOptions()
        });

        function loadChartData(year) {
            fetch(`/dashboard/getChartData?year=${year}`)
                .then(response => response.json())
                .then(data => {
                    barangKeluarChart.data.labels = data.labels;
                    barangKeluarChart.data.datasets = data.datasets.flatMap(dataset => [{
                            label: `${dataset.label} (${year})`,
                            data: dataset.data,
                            ...roundedBarStyle,
                            backgroundColor: '#6f5de8',
                            borderColor: '#6f5de8',
                            hoverBackgroundColor: '#5b48d8',
                            pointStyle: 'circle'
                        },
                        {
                            label: `${dataset.label} (${year - 1})`,
                            data: dataset.previousYearData,
                            ...roundedBarStyle,
                            backgroundColor: '#cfd6ff',
                            borderColor: '#cfd6ff',
                            hoverBackgroundColor: '#bfc8ff',
                            pointStyle: 'circle'
                        }
                    ]);
                    barangKeluarChart.update();
                    document.getElementById('current-year').textContent = year;
                    document.getElementById('previous-year').textContent = year - 1;
                });
        }

        const initialYear = document.getElementById('year').value;
        loadChartData(initialYear);
        document.getElementById('year').addEventListener('change', function() {
            loadChartData(this.value);
        });

        const ctxPoMasuk = document.getElementById('poMasukChart').getContext('2d');
        const poMasukChart = new Chart(ctxPoMasuk, {
            type: 'bar',
            data: {
                labels: [],
                datasets: []
            },
            options: topProductChartOptions()
        });

        function loadChartDataPo(yearpo) {
            fetch(`/dashboard/getChartDataPo5?yearpo=${yearpo}`)
                .then(response => response.json())
                .then(data => {
                    poMasukChart.data.labels = data.labels;
                    poMasukChart.data.datasets = styleDashboardData({
                        labels: data.labels,
                        datasets: data.datasets
                    }).datasets;
                    poMasukChart.update();
                    document.getElementById('current-yearpo').textContent = data.yearpo;
                    document.getElementById('previous-yearpo').textContent = data.yearpo - 1;
                });
        }

        const initialYearpo = document.getElementById('yearpo').value;
        loadChartDataPo(initialYearpo);
        document.getElementById('yearpo').addEventListener('change', function() {
            loadChartDataPo(this.value);
        });
    });

    function stockChartOptions(label) {
        return {
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: {
                    top: 12,
                    right: 8,
                    left: 4,
                    bottom: 0
                }
            },
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: Object.assign({}, floatingTooltip, {
                    callbacks: {
                        label: context => [
                            `Fast Moving: ${formatNumber(context.parsed.y)}`,
                            `Stok Saat Ini: ${formatNumber(context.dataset.stockData[context.dataIndex])}`,
                            `Minimal Stok: ${formatNumber(context.dataset.minimalData[context.dataIndex])}`,
                            `Status: ${context.dataset.statusLabels[context.dataIndex]}`
                        ]
                    }
                }),
                title: {
                    display: false,
                    text: `Stok ${label} dan Minimal Stok`
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: chartTick.color,
                        font: chartTick.font,
                        maxRotation: 55,
                        minRotation: 25
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: chartGrid,
                    ticks: chartTick
                }
            }
        };
    }

    function comparisonChartOptions() {
        return {
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: {
                    top: 12,
                    right: 8,
                    left: 4,
                    bottom: 0
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: Object.assign({}, floatingTooltip, {
                    callbacks: {
                        label: context => `${context.dataset.label}: ${formatNumber(context.parsed.y)}`
                    }
                })
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: chartTick
                },
                y: {
                    beginAtZero: true,
                    grid: chartGrid,
                    ticks: chartTick
                }
            }
        };
    }

    function topProductChartOptions() {
        return {
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: {
                    top: 12,
                    right: 8,
                    left: 4,
                    bottom: 0
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: Object.assign({}, floatingTooltip, {
                    callbacks: {
                        label: context => `${context.dataset.label || ''}: ${formatNumber(context.raw)}`
                    }
                })
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: chartTick,
                    title: {
                        display: true,
                        text: 'Bulan',
                        color: '#6b7280'
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: chartGrid,
                    ticks: chartTick,
                    title: {
                        display: true,
                        text: 'Total',
                        color: '#6b7280'
                    }
                }
            }
        };
    }
</script>
<?= $this->endSection('isi') ?>
