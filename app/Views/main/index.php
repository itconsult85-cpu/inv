<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Dashboard
<?= $this->endSection('judul') ?>

<?= $this->section('isi'); ?>
<script src="<?= base_url() ?>/plugins/npm/chart.js"></script>
<script src="<?= base_url() ?>/plugins/npm/sweetalert211.js"></script>

<?php
$dashboardSummary = static function (array $chartData): array {
    $labels = $chartData['labels'] ?? [];
    $movement = $chartData['datasets'][0]['data'] ?? [];
    $stock = $chartData['datasets'][0]['stockData'] ?? $movement;
    $minimal = $chartData['datasets'][0]['minimalData'] ?? ($chartData['datasets'][1]['data'] ?? []);
    $totalMoving = array_sum(array_map('floatval', $movement));
    $kurang = 0;
    $topLabel = '-';
    $topValue = 0;

    foreach ($labels as $index => $label) {
        $stockValue = (float) ($stock[$index] ?? 0);
        $minimalValue = (float) ($minimal[$index] ?? 0);
        $movingValue = (float) ($movement[$index] ?? 0);

        if ($stockValue < $minimalValue) {
            $kurang++;
        }

        if ($movingValue >= $topValue) {
            $topValue = $movingValue;
            $topLabel = (string) $label;
        }
    }

    return [
        'count' => count($labels),
        'total_moving' => $totalMoving,
        'kurang' => $kurang,
        'top_label' => $topLabel,
        'top_value' => $topValue,
    ];
};

$productSummary = $dashboardSummary($prod_chart_data ?? []);
$materialSummary = $dashboardSummary($mat_chart_data ?? []);
$inventorySummary = $inventory_summary ?? [
    'total_produk' => $productSummary['count'],
    'total_material' => $materialSummary['count'],
    'produk_perlu_restok' => $productSummary['kurang'],
    'material_perlu_restok' => $materialSummary['kurang'],
    'list_produk_perlu_restok' => [],
    'list_material_perlu_restok' => [],
];
$poInSummary = $po_in_summary ?? [
    'berjalan' => 0,
    'selesai' => 0,
    'list_berjalan' => [],
];
$dashboardPeriod = $dashboard_period ?? [
    'start_date' => date('Y-m-d', strtotime('-30 days')),
    'end_date' => date('Y-m-d'),
    'is_all_time' => false,
];
$isAllTimePeriod = !empty($dashboardPeriod['is_all_time']);
$periodLabel = $isAllTimePeriod
    ? 'Semua data (tidak dibatasi tanggal)'
    : date('d M', strtotime($dashboardPeriod['start_date'])) . ' - ' . date('d M Y', strtotime($dashboardPeriod['end_date']));
?>

<style>
    .tre-dashboard {
        display: grid;
        gap: 1.15rem;
    }

    .tre-stat-grid {
        display: grid;
        gap: .9rem;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        grid-template-areas:
            "period-filter period-filter total-product total-material"
            "po-running po-done product-restock material-restock";
    }

    .tre-area-period-filter {
        grid-area: period-filter;
    }

    .tre-area-total-product {
        grid-area: total-product;
    }

    .tre-area-total-material {
        grid-area: total-material;
    }

    .tre-area-po-running {
        grid-area: po-running;
    }

    .tre-area-po-done {
        grid-area: po-done;
    }

    .tre-area-product-restock {
        grid-area: product-restock;
    }

    .tre-area-material-restock {
        grid-area: material-restock;
    }

    .tre-period-form {
        display: contents;
    }

    .tre-period-card {
        align-items: center;
    }

    .tre-stat-card {
        align-items: center;
        background: rgba(255, 255, 255, .92);
        border: 1px solid #e7edf3;
        border-radius: 18px;
        box-shadow: 0 12px 28px rgba(15, 23, 42, .06);
        display: flex;
        gap: .85rem;
        min-height: 6.1rem;
        padding: 1rem;
    }


    body.manual-preview-mode .tre-stat-card,
    body.manual-preview-mode .payroll-style-card,
    body.manual-preview-mode .tre-period-input,
    body.manual-preview-mode .tre-period-submit,
    body.manual-preview-mode .tre-period-reset,
    body.manual-preview-mode .tre-stat-link,
    body.manual-preview-mode .po-running-toggle,
    body.manual-preview-mode .payroll-legend,
    body.manual-preview-mode .payroll-legend-item {
        cursor: help;
    }

    .manual-dashboard-tooltip {
        background: rgba(15, 23, 42, .94);
        border-radius: 10px;
        box-shadow: 0 14px 34px rgba(15, 23, 42, .26);
        color: #fff;
        font-size: .86rem;
        line-height: 1.45;
        max-width: 280px;
        opacity: 0;
        padding: 10px 12px;
        pointer-events: none;
        position: fixed;
        transform: translate(12px, 12px);
        transition: opacity .16s ease, transform .16s ease;
        z-index: 9999;
    }

    .manual-dashboard-tooltip.is-visible {
        opacity: 1;
        transform: translate(12px, 8px);
    }

    .tre-stat-content {
        flex: 1 1 auto;
        min-width: 0;
    }

    .tre-stat-icon {
        align-items: center;
        background: #fff1f2;
        border-radius: 16px;
        color: #ef3e4a;
        display: flex;
        flex: 0 0 3rem;
        height: 3rem;
        justify-content: center;
        width: 3rem;
    }

    .tre-stat-card.is-blue .tre-stat-icon {
        background: #e8f6fa;
        color: #147a91;
    }

    .tre-stat-card.is-amber .tre-stat-icon {
        background: #fff7e6;
        color: #d97706;
    }

    .tre-stat-card.is-green .tre-stat-icon {
        background: #eaf7ee;
        color: #218838;
    }

    .tre-stat-card.is-period .tre-stat-icon {
        background: #eef2ff;
        color: #4f46e5;
    }

    .tre-stat-label {
        color: #718096;
        font-size: .82rem;
        font-weight: 700;
        margin-bottom: .2rem;
    }

    .tre-stat-value {
        color: #111827;
        font-size: 1.35rem;
        font-weight: 800;
        line-height: 1.1;
    }

    .tre-stat-hint {
        color: #94a3b8;
        font-size: .78rem;
        margin-top: .2rem;
    }

    .tre-period-control {
        align-items: center;
        display: flex;
        gap: .6rem;
        margin-top: .35rem;
    }

    .tre-period-field {
        flex: 1 1 0;
        min-width: 0;
    }

    .tre-period-field-label {
        color: #8b98a8;
        font-size: .72rem;
        font-weight: 700;
        margin-bottom: .2rem;
    }

    .tre-period-actions {
        align-items: end;
        display: flex;
        flex: 0 0 auto;
        gap: .45rem;
    }

    .tre-period-input {
        background: #f8fafc;
        border: 1px solid #dbe4ee;
        border-radius: 10px;
        color: #111827;
        font-size: .82rem;
        font-weight: 700;
        min-width: 0;
        padding: .42rem .5rem;
        width: 100%;
    }

    .tre-period-submit,
    .tre-period-reset {
        align-items: center;
        border: 0;
        border-radius: 10px;
        color: #fff;
        display: inline-flex;
        flex: 0 0 2.15rem;
        height: 2.15rem;
        justify-content: center;
        width: 2.15rem;
    }

    .tre-period-submit {
        background: #147a91;
    }

    .tre-period-reset {
        background: #94a3b8;
    }

    .tre-period-submit:hover,
    .tre-period-reset:hover {
        color: #fff;
        filter: brightness(.95);
        text-decoration: none;
    }

    .tre-stat-link {
        align-items: center;
        background: transparent;
        border: 0;
        color: #147a91;
        display: inline-flex;
        font-size: .78rem;
        font-weight: 700;
        gap: .25rem;
        margin-top: .2rem;
        padding: 0;
    }

    .tre-stat-link:hover {
        color: #0f6173;
        text-decoration: underline;
    }

    .tre-stat-link i {
        transition: transform .2s ease;
    }

    .tre-stat-link[aria-expanded="true"] i {
        transform: rotate(180deg);
    }

    .dashboard-charts {
        display: grid;
        gap: 1.15rem;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .payroll-style-card {
        background: rgba(255, 255, 255, .95);
        border: 1px solid #e7edf3;
        border-radius: 22px;
        box-shadow: 0 16px 36px rgba(15, 23, 42, .07);
        overflow: hidden;
    }

    .payroll-style-header {
        align-items: center;
        display: flex;
        justify-content: space-between;
        min-height: 4rem;
        padding: 1.05rem 1.35rem .25rem;
    }

    .payroll-style-title {
        color: #171923;
        font-size: 1.04rem;
        font-weight: 700;
        letter-spacing: 0;
        margin: 0;
    }

    .payroll-style-note {
        color: #8b98a8;
        font-size: .86rem;
        white-space: nowrap;
    }

    .payroll-style-body {
        padding: .35rem 1.35rem 1rem;
    }

    .payroll-chart-frame {
        height: 18.5rem;
        position: relative;
    }

    .payroll-chart-empty {
        color: #8a8f98;
        display: none;
        left: 50%;
        position: absolute;
        text-align: center;
        top: 50%;
        transform: translate(-50%, -50%);
    }

    .payroll-legend {
        display: flex;
        flex-wrap: wrap;
        gap: .55rem 1.15rem;
        justify-content: center;
        margin-top: .85rem;
    }

    .payroll-legend-item {
        align-items: center;
        color: #6b7280;
        display: inline-flex;
        font-size: .9rem;
        gap: .38rem;
    }

    .payroll-legend-dot {
        border-radius: 999px;
        display: inline-block;
        height: .55rem;
        width: .55rem;
    }

    .dashboard-detail-table-wrap {
        max-height: 18rem;
        overflow: auto;
    }

    .po-running-table-wrap {
        max-height: 18rem;
        overflow: auto;
    }

    .po-running-table {
        margin-bottom: 0;
    }

    .po-running-table th {
        background: #f8fafc;
        color: #334155;
        font-size: .82rem;
        position: sticky;
        top: 0;
        z-index: 1;
    }

    .po-running-table td {
        color: #475569;
        font-size: .86rem;
        vertical-align: middle;
    }

    .po-running-panel {
        max-height: 32rem;
        opacity: 1;
        overflow: hidden;
        transform: translateY(0);
        transition: max-height .28s ease, opacity .22s ease, transform .22s ease, margin .22s ease;
    }

    .po-running-panel.is-hidden {
        margin-bottom: 0;
        max-height: 0;
        opacity: 0;
        pointer-events: none;
        transform: translateY(-.35rem);
    }

    .po-running-toggle {
        border: 0;
        background: transparent;
        color: #8b98a8;
        cursor: pointer;
        font-size: 1rem;
        padding: .25rem;
    }

    .po-running-toggle:hover {
        color: #111827;
    }

    @media (max-width: 1200px) {
        .tre-stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            grid-template-areas:
                "period-filter period-filter"
                "total-product total-material"
                "po-running po-done"
                "product-restock material-restock";
        }

        .dashboard-charts {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .tre-stat-grid,
        .dashboard-charts {
            grid-template-columns: 1fr;
        }

        .tre-stat-grid {
            grid-template-areas:
                "period-filter"
                "total-product"
                "total-material"
                "po-running"
                "po-done"
                "product-restock"
                "material-restock";
        }

        .tre-period-card {
            align-items: flex-start;
        }

        .tre-period-control {
            align-items: stretch;
            flex-direction: column;
        }

        .tre-period-actions {
            align-items: center;
        }

        .payroll-style-header {
            align-items: flex-start;
            flex-direction: column;
            gap: .15rem;
        }

        .payroll-chart-frame {
            height: 19rem;
        }
    }
</style>

<div class="tre-dashboard">
    <section class="tre-stat-grid">
        <form class="tre-period-form" method="get" action="<?= current_url() ?>">
            <div class="tre-stat-card is-period tre-period-card tre-area-period-filter">
                <div class="tre-stat-icon"><i class="fas fa-calendar-alt"></i></div>
                <div class="tre-stat-content">
                    <div class="tre-stat-label">Periode Dashboard</div>
                    <div class="tre-period-control">
                        <div class="tre-period-field">
                            <div class="tre-period-field-label">Tanggal Awal</div>
                            <input type="date" class="tre-period-input" name="start_date" value="<?= $isAllTimePeriod ? '' : esc($dashboardPeriod['start_date']) ?>">
                        </div>
                        <div class="tre-period-field">
                            <div class="tre-period-field-label">Tanggal Akhir</div>
                            <input type="date" class="tre-period-input" name="end_date" value="<?= $isAllTimePeriod ? '' : esc($dashboardPeriod['end_date']) ?>">
                        </div>
                        <div class="tre-period-actions">
                            <button type="submit" class="tre-period-submit" title="Terapkan periode">
                                <i class="fas fa-filter"></i>
                            </button>
                            <a href="<?= current_url() ?>" class="tre-period-reset" title="Reset periode">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </div>
                    <div class="tre-stat-hint"><?= esc($periodLabel) ?></div>
                </div>
            </div>
        </form>

        <div class="tre-stat-card is-blue tre-area-total-product">
            <div class="tre-stat-icon"><i class="fas fa-box"></i></div>
            <div class="tre-stat-content">
                <div class="tre-stat-label">Total Jenis Produk</div>
                <div class="tre-stat-value"><?= number_format($inventorySummary['total_produk'], 0, ',', '.') ?></div>
                <div class="tre-stat-hint">Jenis produk terdaftar</div>
            </div>
        </div>
        <div class="tre-stat-card is-blue tre-area-total-material">
            <div class="tre-stat-icon"><i class="fas fa-cubes"></i></div>
            <div class="tre-stat-content">
                <div class="tre-stat-label">Total Jenis Material</div>
                <div class="tre-stat-value"><?= number_format($inventorySummary['total_material'], 0, ',', '.') ?></div>
                <div class="tre-stat-hint">Jenis material terdaftar</div>
            </div>
        </div>
        <div class="tre-stat-card is-amber tre-area-po-running">
            <div class="tre-stat-icon"><i class="fas fa-file-invoice"></i></div>
            <div class="tre-stat-content">
                <div class="tre-stat-label">PO In Berjalan</div>
                <div class="tre-stat-value"><?= number_format($poInSummary['berjalan'], 0, ',', '.') ?></div>
                <button type="button" class="tre-stat-link" id="poInBerjalanToggle" aria-controls="po-in-berjalan-list" aria-expanded="false">
                    <span id="poInBerjalanText">Lihat daftar berjalan</span>
                    <i class="fa fa-chevron-down"></i>
                </button>
            </div>
        </div>
        <div class="tre-stat-card is-green tre-area-po-done">
            <div class="tre-stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="tre-stat-content">
                <div class="tre-stat-label">PO In Selesai</div>
                <div class="tre-stat-value"><?= number_format($poInSummary['selesai'], 0, ',', '.') ?></div>
                <div class="tre-stat-hint">Terkirim penuh periode ini</div>
            </div>
        </div>
        <div class="tre-stat-card tre-area-product-restock">
            <div class="tre-stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="tre-stat-content">
                <div class="tre-stat-label">Produk Perlu Restok</div>
                <div class="tre-stat-value"><?= number_format($inventorySummary['produk_perlu_restok'], 0, ',', '.') ?></div>
                <button type="button" class="tre-stat-link" id="productRestockToggle" aria-controls="product-restock-list" aria-expanded="false">
                    <span id="productRestockText">Lihat daftar restok</span>
                    <i class="fa fa-chevron-down"></i>
                </button>
            </div>
        </div>
        <div class="tre-stat-card tre-area-material-restock">
            <div class="tre-stat-icon"><i class="fas fa-tools"></i></div>
            <div class="tre-stat-content">
                <div class="tre-stat-label">Material Perlu Restok</div>
                <div class="tre-stat-value"><?= number_format($inventorySummary['material_perlu_restok'], 0, ',', '.') ?></div>
                <button type="button" class="tre-stat-link" id="materialRestockToggle" aria-controls="material-restock-list" aria-expanded="false">
                    <span id="materialRestockText">Lihat daftar restok</span>
                    <i class="fa fa-chevron-down"></i>
                </button>
            </div>
        </div>
    </section>

    <section class="payroll-style-card po-running-panel is-hidden" id="po-in-berjalan-list" aria-hidden="true">
        <div class="payroll-style-header">
            <div>
                <h3 class="payroll-style-title">Daftar PO In Berjalan</h3>
                <span class="payroll-style-note">PO yang belum terkirim penuh</span>
            </div>
            <div>
                <span class="payroll-style-note"><?= number_format($poInSummary['berjalan'], 0, ',', '.') ?> PO</span>
                <button type="button" class="po-running-toggle" id="poInBerjalanClose" title="Tutup daftar">
                    <i class="fa fa-times"></i>
                </button>
            </div>
        </div>
        <div class="payroll-style-body">
            <div class="table-responsive po-running-table-wrap">
                <table class="table table-sm table-bordered po-running-table">
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th>No PO</th>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th class="text-right">Qty PO</th>
                            <th class="text-right">Terkirim</th>
                            <th class="text-right">Sisa</th>
                            <th class="text-center">#</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($poInSummary['list_berjalan'])) : ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">Tidak ada PO In yang masih berjalan.</td>
                            </tr>
                        <?php endif ?>
                        <?php foreach (($poInSummary['list_berjalan'] ?? []) as $index => $po) : ?>
                            <tr>
                                <td class="text-center"><?= $index + 1 ?></td>
                                <td><?= esc($po['nopo']) ?></td>
                                <td><?= !empty($po['tglpo']) ? date('d-m-Y', strtotime($po['tglpo'])) : '-' ?></td>
                                <td><?= esc($po['pelnama'] ?: '-') ?></td>
                                <td class="text-right"><?= number_format((float) $po['total_qty'], 0, ',', '.') ?></td>
                                <td class="text-right"><?= number_format((float) $po['total_kirim'], 0, ',', '.') ?></td>
                                <td class="text-right text-danger font-weight-bold"><?= number_format((float) $po['sisa_qty'], 0, ',', '.') ?></td>
                                <td class="text-center">
                                    <a class="btn btn-sm btn-info" href="/po/progress/<?= sha1($po['nopo']) ?>" title="Lihat Progress">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="payroll-style-card po-running-panel is-hidden" id="product-restock-list" aria-hidden="true">
        <div class="payroll-style-header">
            <div>
                <h3 class="payroll-style-title">Daftar Produk Perlu Restok</h3>
                <span class="payroll-style-note">Kebutuhan PO periode <?= esc($periodLabel) ?></span>
            </div>
            <div>
                <span class="payroll-style-note"><?= number_format($inventorySummary['produk_perlu_restok'], 0, ',', '.') ?> produk</span>
                <button type="button" class="po-running-toggle" id="productRestockClose" title="Tutup daftar">
                    <i class="fa fa-times"></i>
                </button>
            </div>
        </div>
        <div class="payroll-style-body">
            <div class="table-responsive dashboard-detail-table-wrap">
                <table class="table table-sm table-bordered po-running-table">
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th>Kode</th>
                            <th>Nama Produk</th>
                            <th class="text-right">Sisa PO</th>
                            <th class="text-right">Stok</th>
                            <th class="text-right">Kurang</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($inventorySummary['list_produk_perlu_restok'])) : ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">Tidak ada produk yang perlu restok pada periode ini.</td>
                            </tr>
                        <?php endif ?>
                        <?php foreach (($inventorySummary['list_produk_perlu_restok'] ?? []) as $index => $produk) : ?>
                            <tr>
                                <td class="text-center"><?= $index + 1 ?></td>
                                <td><?= esc($produk['kode'] ?? '-') ?></td>
                                <td><?= esc($produk['nama'] ?? '-') ?></td>
                                <td class="text-right"><?= number_format((float) ($produk['sisa_po'] ?? 0), 0, ',', '.') ?></td>
                                <td class="text-right"><?= number_format((float) ($produk['total_stok'] ?? 0), 0, ',', '.') ?></td>
                                <td class="text-right text-danger font-weight-bold"><?= number_format((float) ($produk['kurang_stok'] ?? 0), 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="payroll-style-card po-running-panel is-hidden" id="material-restock-list" aria-hidden="true">
        <div class="payroll-style-header">
            <div>
                <h3 class="payroll-style-title">Daftar Material Perlu Restok</h3>
                <span class="payroll-style-note">Kebutuhan produksi PO periode <?= esc($periodLabel) ?></span>
            </div>
            <div>
                <span class="payroll-style-note"><?= number_format($inventorySummary['material_perlu_restok'], 0, ',', '.') ?> material</span>
                <button type="button" class="po-running-toggle" id="materialRestockClose" title="Tutup daftar">
                    <i class="fa fa-times"></i>
                </button>
            </div>
        </div>
        <div class="payroll-style-body">
            <div class="table-responsive dashboard-detail-table-wrap">
                <table class="table table-sm table-bordered po-running-table">
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th>Kode</th>
                            <th>Nama Material</th>
                            <th class="text-right">Kebutuhan</th>
                            <th class="text-right">Stok</th>
                            <th class="text-right">Kurang</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($inventorySummary['list_material_perlu_restok'])) : ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">Tidak ada material yang perlu restok pada periode ini.</td>
                            </tr>
                        <?php endif ?>
                        <?php foreach (($inventorySummary['list_material_perlu_restok'] ?? []) as $index => $material) : ?>
                            <tr>
                                <td class="text-center"><?= $index + 1 ?></td>
                                <td><?= esc($material['kode'] ?? '-') ?></td>
                                <td><?= esc($material['nama'] ?? '-') ?></td>
                                <td class="text-right"><?= number_format((float) ($material['kebutuhan'] ?? 0), 2, ',', '.') ?></td>
                                <td class="text-right"><?= number_format((float) ($material['total_stok'] ?? 0), 2, ',', '.') ?></td>
                                <td class="text-right text-danger font-weight-bold"><?= number_format((float) ($material['kurang_stok'] ?? 0), 2, ',', '.') ?></td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <div class="dashboard-charts">
        <section class="payroll-style-card tre-area-product-chart">
            <div class="payroll-style-header">
                <div>
                    <h3 class="payroll-style-title">Stok Produk dengan Minimal Stok</h3>
                    <span class="payroll-style-note">Top 5 pergerakan stok produk</span>
                </div>
                <span class="payroll-style-note">Stok saat ini</span>
            </div>
            <div class="payroll-style-body">
                <div class="payroll-chart-frame">
                    <canvas id="prod-stok-chart"></canvas>
                    <div id="prod-chart-tooltip" class="payroll-chart-empty">Data Stok Belum Tersedia.</div>
                </div>
                <div class="payroll-legend">
                    <span class="payroll-legend-item"><i class="payroll-legend-dot" style="background:#28a745"></i> Stok Aman</span>
                    <span class="payroll-legend-item"><i class="payroll-legend-dot" style="background:#ffc107"></i> Stok Cukup</span>
                    <span class="payroll-legend-item"><i class="payroll-legend-dot" style="background:#dc3545"></i> Stok Kurang</span>
                </div>
            </div>
        </section>

        <section class="payroll-style-card tre-area-material-chart">
            <div class="payroll-style-header">
                <div>
                    <h3 class="payroll-style-title">Stok Material dengan Minimal Stok</h3>
                    <span class="payroll-style-note">Top 5 pergerakan stok material</span>
                </div>
                <span class="payroll-style-note">Stok saat ini</span>
            </div>
            <div class="payroll-style-body">
                <div class="payroll-chart-frame">
                    <canvas id="mat-stok-chart"></canvas>
                    <div id="mat-chart-tooltip" class="payroll-chart-empty">Data Material Belum Tersedia.</div>
                </div>
                <div class="payroll-legend">
                    <span class="payroll-legend-item"><i class="payroll-legend-dot" style="background:#28a745"></i> Stok Aman</span>
                    <span class="payroll-legend-item"><i class="payroll-legend-dot" style="background:#ffc107"></i> Stok Cukup</span>
                    <span class="payroll-legend-item"><i class="payroll-legend-dot" style="background:#dc3545"></i> Stok Kurang</span>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
    const dashboardChartGrid = {
        color: 'rgba(148, 163, 184, .16)'
    };
    const dashboardChartTick = {
        color: '#8a8f98',
        font: {
            size: 11
        }
    };
    const dashboardTooltip = {
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

    function formatDashboardNumber(value) {
        return Number(value || 0).toLocaleString('id-ID');
    }

    function showDashboardEmptyState(chartData, canvasId, emptyId) {
        const hasData = chartData.labels && chartData.labels.length > 0;
        document.getElementById(canvasId).style.display = hasData ? 'block' : 'none';
        document.getElementById(emptyId).style.display = hasData ? 'none' : 'block';
        return hasData;
    }

    function buildStatusStockData(sourceData) {
        const movement = sourceData.datasets && sourceData.datasets[0] ? sourceData.datasets[0] : {};
        const minimal = sourceData.datasets && sourceData.datasets[1] ? sourceData.datasets[1] : {};
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

            return '#28a745';
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
            labels: sourceData.labels || [],
            datasets: [
                {
                    label: 'Stok Saat Ini',
                    data: stockValues,
                    movementData: movementValues,
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
                }
            ]
        };
    }

    function dashboardStockChartOptions() {
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
                tooltip: Object.assign({}, dashboardTooltip, {
                    callbacks: {
                        label: context => [
                            `Stok Saat Ini: ${formatDashboardNumber(context.parsed.y)}`,
                            `Minimal Stok: ${formatDashboardNumber(context.dataset.minimalData[context.dataIndex])}`,
                            `Pergerakan Stok: ${formatDashboardNumber(context.dataset.movementData[context.dataIndex])}`,
                            `Status: ${context.dataset.statusLabels[context.dataIndex]}`
                        ]
                    }
                })
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: dashboardChartTick.color,
                        font: dashboardChartTick.font,
                        maxRotation: 45,
                        minRotation: 25
                    }
                },
                y: {
                    beginAtZero: true,
                    border: {
                        display: false
                    },
                    grid: dashboardChartGrid,
                    ticks: dashboardChartTick
                }
            }
        };
    }

    document.addEventListener('DOMContentLoaded', function() {
        function setupDashboardPanelToggle(config) {
            const toggle = document.getElementById(config.toggleId);
            const panel = document.getElementById(config.panelId);
            const text = document.getElementById(config.textId);
            const close = document.getElementById(config.closeId);

            if (!toggle || !panel || !text) {
                return;
            }

            function togglePanel(forceOpen = null) {
                const willOpen = forceOpen === null ? panel.classList.contains('is-hidden') : forceOpen;
                panel.classList.toggle('is-hidden', !willOpen);
                panel.setAttribute('aria-hidden', willOpen ? 'false' : 'true');
                toggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                text.textContent = willOpen ? config.openText : config.closedText;

                if (willOpen) {
                    window.setTimeout(function() {
                        panel.scrollIntoView({
                            behavior: 'smooth',
                            block: 'nearest'
                        });
                    }, 140);
                }
            }

            toggle.addEventListener('click', function() {
                togglePanel();
            });

            if (close) {
                close.addEventListener('click', function(event) {
                    event.stopPropagation();
                    togglePanel(false);
                });
            }
        }

        setupDashboardPanelToggle({
            toggleId: 'poInBerjalanToggle',
            panelId: 'po-in-berjalan-list',
            textId: 'poInBerjalanText',
            closeId: 'poInBerjalanClose',
            closedText: 'Lihat daftar berjalan',
            openText: 'Tutup daftar berjalan'
        });
        setupDashboardPanelToggle({
            toggleId: 'productRestockToggle',
            panelId: 'product-restock-list',
            textId: 'productRestockText',
            closeId: 'productRestockClose',
            closedText: 'Lihat daftar restok',
            openText: 'Tutup daftar restok'
        });
        setupDashboardPanelToggle({
            toggleId: 'materialRestockToggle',
            panelId: 'material-restock-list',
            textId: 'materialRestockText',
            closeId: 'materialRestockClose',
            closedText: 'Lihat daftar restok',
            openText: 'Tutup daftar restok'
        });

        const productData = buildStatusStockData({
            labels: <?= json_encode($prod_chart_data['labels']); ?>,
            datasets: <?= json_encode($prod_chart_data['datasets']); ?>
        });

        if (showDashboardEmptyState(productData, 'prod-stok-chart', 'prod-chart-tooltip')) {
            new Chart(document.getElementById('prod-stok-chart').getContext('2d'), {
                type: 'bar',
                data: productData,
                options: dashboardStockChartOptions()
            });
        }

        const materialData = buildStatusStockData({
            labels: <?= json_encode($mat_chart_data['labels']); ?>,
            datasets: <?= json_encode($mat_chart_data['datasets']); ?>
        });

        if (showDashboardEmptyState(materialData, 'mat-stok-chart', 'mat-chart-tooltip')) {
            new Chart(document.getElementById('mat-stok-chart').getContext('2d'), {
                type: 'bar',
                data: materialData,
                options: dashboardStockChartOptions()
            });
        }
    });
</script>

<?php if (service('request')->getGet('manual_preview') === 'dashboard') : ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tooltipItems = [
            ['.tre-area-period-filter', 'Filter periode dashboard. Dipakai untuk mengatur tanggal awal dan akhir laporan transaksi yang ingin dilihat.'],
            ['input[name="start_date"].tre-period-input', 'Tanggal awal periode laporan dashboard. Kosongkan kalau ingin melihat semua data.'],
            ['input[name="end_date"].tre-period-input', 'Tanggal akhir periode laporan dashboard. Dashboard akan menghitung transaksi sampai tanggal ini.'],
            ['.tre-period-submit', 'Terapkan filter periode agar ringkasan PO dan transaksi mengikuti tanggal yang dipilih.'],
            ['.tre-period-reset', 'Reset periode agar dashboard kembali menampilkan semua data tanpa batas tanggal.'],
            ['.tre-area-total-product', 'Total jenis produk yang sudah terdaftar di master produk. Angka ini tidak mengikuti filter periode.'],
            ['.tre-area-total-material', 'Total jenis material yang sudah terdaftar di master material. Angka ini tidak mengikuti filter periode.'],
            ['.tre-area-po-running', 'Jumlah PO masuk yang masih berjalan atau belum terkirim penuh pada periode yang dipilih.'],
            ['#poInBerjalanToggle', 'Buka daftar rincian PO masuk yang masih berjalan.'],
            ['.tre-area-po-done', 'Jumlah PO masuk yang sudah selesai terkirim penuh pada periode yang dipilih.'],
            ['.tre-area-product-restock', 'Produk yang stoknya kurang dibanding kebutuhan PO, sehingga perlu disiapkan atau diproduksi lagi.'],
            ['#productRestockToggle', 'Buka daftar produk yang perlu restok beserta sisa PO, stok, dan jumlah kekurangannya.'],
            ['.tre-area-material-restock', 'Material yang stoknya kurang dibanding kebutuhan produksi dari PO, sehingga perlu restok.'],
            ['#materialRestockToggle', 'Buka daftar material yang perlu restok beserta kebutuhan, stok, dan jumlah kekurangannya.'],
            ['#po-in-berjalan-list', 'Daftar rincian PO masuk yang masih berjalan, termasuk qty PO, terkirim, dan sisa yang belum terkirim.'],
            ['#poInBerjalanClose', 'Tutup daftar PO masuk berjalan.'],
            ['#product-restock-list', 'Daftar produk yang perlu restok berdasarkan sisa PO dan stok produk saat ini.'],
            ['#productRestockClose', 'Tutup daftar produk perlu restok.'],
            ['#material-restock-list', 'Daftar material yang perlu restok berdasarkan kebutuhan produksi dan stok material saat ini.'],
            ['#materialRestockClose', 'Tutup daftar material perlu restok.'],
            ['.tre-area-product-chart', 'Grafik top 5 pergerakan stok produk terhadap batas minimal stok. Warna menunjukkan status aman, cukup, atau kurang.'],
            ['#prod-stok-chart', 'Bar grafik produk. Tinggi bar menunjukkan jumlah stok saat ini dibanding batas minimal stok.'],
            ['.tre-area-material-chart', 'Grafik top 5 pergerakan stok material terhadap batas minimal stok. Warna menunjukkan status aman, cukup, atau kurang.'],
            ['#mat-stok-chart', 'Bar grafik material. Tinggi bar menunjukkan jumlah stok material saat ini dibanding batas minimal stok.'],
            ['.payroll-legend', 'Keterangan warna grafik: hijau stok aman, kuning stok cukup, merah stok kurang.']
        ];

        const tooltip = document.createElement('div');
        tooltip.className = 'manual-dashboard-tooltip';
        document.body.appendChild(tooltip);

        function moveTooltip(event) {
            const padding = 14;
            const width = tooltip.offsetWidth || 280;
            const height = tooltip.offsetHeight || 70;
            let left = event.clientX + 14;
            let top = event.clientY + 14;

            if (left + width + padding > window.innerWidth) {
                left = event.clientX - width - 14;
            }
            if (top + height + padding > window.innerHeight) {
                top = event.clientY - height - 14;
            }

            tooltip.style.left = Math.max(padding, left) + 'px';
            tooltip.style.top = Math.max(padding, top) + 'px';
        }

        tooltipItems.forEach(function(item) {
            document.querySelectorAll(item[0]).forEach(function(element) {
                element.addEventListener('mouseenter', function(event) {
                    tooltip.textContent = item[1];
                    tooltip.classList.add('is-visible');
                    moveTooltip(event);
                });
                element.addEventListener('mousemove', moveTooltip);
                element.addEventListener('mouseleave', function() {
                    tooltip.classList.remove('is-visible');
                });
            });
        });
    });
</script>
<?php endif ?>
<?= $this->endSection('isi') ?>
