<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Detail PO Keluar
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>
<div class="po-detail-header">
    <p class="po-detail-subtitle mb-0"><?= esc($po['no_po']) ?></p>
    <div class="po-detail-actions">
        <a href="<?= site_url('poKeluar/data') ?>" class="btn btn-outline-secondary">
            <i class="fa fa-arrow-left"></i> Kembali
        </a>
        <a href="<?= site_url('poKeluar/cetak/' . $po['id']) ?>" target="_blank" class="btn btn-info">
            <i class="fa fa-print"></i> Cetak PO
        </a>
    </div>
</div>
<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<?php
$jenisPoLabel = [
    'produk' => 'PO Produk',
    'jasa' => 'PO Jasa',
    'material' => 'PO Material',
][strtolower((string) ($po['jenis_po'] ?? 'material'))] ?? 'PO Material';

$totalPesan = 0.0;
$totalMasuk = 0.0;
$satuanUtama = '';
foreach ($details as $detail) {
    $totalPesan += (float) $detail['qty_pesan'];
    $totalMasuk += (float) $detail['qty_masuk'];
    if ($satuanUtama === '') {
        $satuanUtama = (string) $detail['satuan'];
    }
}
$persenTerima = $totalPesan > 0 ? min(100, round(($totalMasuk / $totalPesan) * 100)) : 0;
?>

<?php if (session('message')) : ?>
    <div class="alert alert-success"><?= session('message') ?></div>
<?php endif ?>

<div class="po-stat-row">
    <div class="po-stat-card">
        <div class="po-stat-icon"><i class="fa fa-file-alt"></i></div>
        <div>
            <div class="po-stat-label">Status PO</div>
            <div class="po-stat-value"><?= esc($po['status']) ?></div>
        </div>
    </div>
    <div class="po-stat-card">
        <div class="po-stat-icon"><i class="fa fa-box"></i></div>
        <div>
            <div class="po-stat-label">Penerimaan</div>
            <div class="po-stat-value"><?= esc($po['status_penerimaan']) ?></div>
        </div>
    </div>
    <div class="po-stat-card">
        <div class="po-stat-icon"><i class="fa fa-building"></i></div>
        <div>
            <div class="po-stat-label">Supplier</div>
            <div class="po-stat-value"><?= esc($po['supplier_nama']) ?></div>
        </div>
    </div>
    <div class="po-stat-card">
        <div class="po-stat-icon"><i class="fa fa-calendar-alt"></i></div>
        <div>
            <div class="po-stat-label">Tanggal PO</div>
            <div class="po-stat-value"><?= date('d M Y', strtotime($po['tgl_po'])) ?></div>
        </div>
    </div>
</div>

<div class="po-detail-grid">
    <div class="po-card">
        <div class="po-card-header">
            <h4>Rincian Item</h4>
            <span class="po-card-badge"><?= count($details) ?> item</span>
        </div>
        <div class="table-responsive">
            <table class="po-item-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Item</th>
                        <th>Satuan</th>
                        <th class="text-right">Jumlah Pesan</th>
                        <th class="text-right">Jumlah Masuk</th>
                        <th class="text-right">Harga</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($details as $i => $detail) : ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= esc($detail['nama_item']) ?></td>
                            <td><?= esc($detail['satuan']) ?></td>
                            <td class="text-right"><?= number_format((float) $detail['qty_pesan'], 0, ',', '.') ?></td>
                            <td class="text-right"><?= number_format((float) $detail['qty_masuk'], 0, ',', '.') ?></td>
                            <td class="text-right">Rp <?= number_format((float) $detail['harga'], 0, ',', '.') ?></td>
                            <td class="text-right">Rp <?= number_format((float) $detail['subtotal'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
        <div class="po-item-total-row">
            <span class="label">Total</span>
            <span class="value">Rp <?= number_format((float) $po['total_nominal'], 0, ',', '.') ?></span>
        </div>
    </div>

    <div>
        <div class="po-card mb-3">
            <div class="po-card-header po-card-toggle" data-toggle="po-collapse">
                <h4>Informasi PO</h4>
                <i class="fa fa-chevron-down po-card-toggle-icon"></i>
            </div>
            <div class="po-card-body-collapsible po-info-list">
                <div class="po-info-row">
                    <span class="po-info-label">No. PO</span>
                    <span class="po-info-value"><?= esc($po['no_po']) ?></span>
                </div>
                <div class="po-info-row">
                    <span class="po-info-label">Tanggal PO</span>
                    <span class="po-info-value"><?= date('d-m-Y', strtotime($po['tgl_po'])) ?></span>
                </div>
                <div class="po-info-row">
                    <span class="po-info-label">Supplier / Vendor</span>
                    <span class="po-info-value"><?= esc($po['supplier_nama']) ?></span>
                </div>
                <div class="po-info-row">
                    <span class="po-info-label">Jenis PO</span>
                    <span class="po-info-value"><?= esc($jenisPoLabel) ?></span>
                </div>
                <div class="po-info-row">
                    <span class="po-info-label">Jenis Transaksi</span>
                    <span class="po-info-value"><?= esc($po['jenis_transaksi'] ?: 'Beli') ?></span>
                </div>
                <?php if (strtolower((string) ($po['jenis_po'] ?? 'material')) === 'produk') : ?>
                    <div class="po-info-row">
                        <span class="po-info-label">Material Produksi</span>
                        <span class="po-info-value"><?= (($po['sumber_material_produksi'] ?? 'tre') === 'vendor') ? 'Material dari Customer' : 'Material TRE' ?></span>
                    </div>
                <?php endif ?>
                <div class="po-info-row">
                    <span class="po-info-label">PO Asal</span>
                    <span class="po-info-value"><?= $po['po_asal'] ? esc($po['po_asal']) : '&mdash;' ?></span>
                </div>
                <div class="po-info-row">
                    <span class="po-info-label">Kirim Langsung</span>
                    <span class="po-info-value"><?= ((int) ($po['kirim_langsung'] ?? 0) === 1) ? 'Ya (tidak resmi masuk stok TRE)' : 'Tidak' ?></span>
                </div>
                <div class="po-info-row">
                    <span class="po-info-label">PO Masuk Terkait</span>
                    <span class="po-info-value"><?= $po['po_masuk_terkait'] ? esc($po['po_masuk_terkait']) : '&mdash;' ?></span>
                </div>
                <div class="po-info-row">
                    <span class="po-info-label">Keterangan</span>
                    <span class="po-info-value"><?= $po['keterangan'] ? esc($po['keterangan']) : '&mdash;' ?></span>
                </div>
            </div>
        </div>

        <div class="po-card mb-3">
            <div class="po-card-header po-card-toggle" data-toggle="po-collapse">
                <h4>Progres Penerimaan</h4>
                <i class="fa fa-chevron-down po-card-toggle-icon"></i>
            </div>
            <div class="po-card-body-collapsible po-progress-body">
                <div class="po-progress-bar-track">
                    <div class="po-progress-bar-fill" style="width: <?= $persenTerima ?>%"></div>
                </div>
                <div class="po-progress-caption">
                    <?= $persenTerima ?>% diterima &mdash; <?= number_format($totalMasuk, 0, ',', '.') ?> dari <?= number_format($totalPesan, 0, ',', '.') ?> <?= esc($satuanUtama) ?>
                </div>

                <div class="po-progress-split">
                    <div class="po-progress-split-box po-progress-split-done">
                        <span class="po-progress-split-label">Sudah Diterima</span>
                        <span class="po-progress-split-value"><?= number_format($totalMasuk, 0, ',', '.') ?> <?= esc($satuanUtama) ?></span>
                    </div>
                    <div class="po-progress-split-box po-progress-split-remaining">
                        <span class="po-progress-split-label">Sisa Belum Diterima</span>
                        <span class="po-progress-split-value"><?= number_format(max($totalPesan - $totalMasuk, 0), 0, ',', '.') ?> <?= esc($satuanUtama) ?></span>
                    </div>
                </div>

                <div class="po-info-row po-progress-info-row">
                    <span class="po-info-label">Status Payment</span>
                    <span class="po-badge <?= esc($statusPayment['class']) ?>"><?= esc($statusPayment['label']) ?></span>
                </div>
                <div class="po-info-row po-progress-info-row">
                    <span class="po-info-label">Termin</span>
                    <span class="po-info-value"><?= $po['top'] ? esc($po['top']) : '&mdash;' ?></span>
                </div>
                <div class="po-info-row po-progress-info-row">
                    <span class="po-info-label">Shipping To</span>
                    <span class="po-info-value"><?= $po['shipping_to'] ? esc($po['shipping_to']) : '&mdash;' ?></span>
                </div>
            </div>
        </div>

        <div class="po-card">
            <div class="po-card-header po-card-toggle" data-toggle="po-collapse">
                <div class="po-card-header-title">
                    <h4>Keterangan Material Masuk</h4>
                    <span class="po-card-badge"><?= count($materialMasuk) ?> surat jalan</span>
                </div>
                <i class="fa fa-chevron-down po-card-toggle-icon"></i>
            </div>
            <?php if (!$materialMasuk) : ?>
                <div class="po-card-body-collapsible po-progress-body">
                    <p class="po-empty-state mb-0">Belum ada material masuk. Surat jalan &amp; tanggal penerimaan akan tampil di sini.</p>
                </div>
            <?php else : ?>
                <div class="po-card-body-collapsible po-material-masuk-list">
                    <?php foreach ($materialMasuk as $mm) : ?>
                        <div class="po-info-row">
                            <span class="po-info-label"><?= $mm['no_do'] ? esc($mm['no_do']) : esc($mm['faktur']) ?></span>
                            <span class="po-info-value"><?= $mm['tglfaktur'] ? date('d-m-Y', strtotime($mm['tglfaktur'])) : '&mdash;' ?></span>
                        </div>
                    <?php endforeach ?>
                </div>
            <?php endif ?>
        </div>
    </div>
</div>

<style>
    .content-wrapper .content > .card { background: transparent; border: 0; box-shadow: none; }
    .content-wrapper .content > .card > .card-header { background: transparent; border: 0; padding: 0 0 1.25rem; }
    .content-wrapper .content > .card > .card-header .card-title { width: 100%; margin: 0; float: none; }
    .content-wrapper .content > .card > .card-body { padding: 0; }
    .content-wrapper .content > .card > .card-footer { display: none; }

    .po-detail-header { align-items: center; display: flex; flex-wrap: wrap; gap: .75rem; justify-content: space-between; width: 100%; }
    .po-detail-subtitle { color: #8a94a6; font-family: 'SFMono-Regular', Consolas, monospace; font-size: .92rem; }
    .po-detail-actions { display: flex; gap: .5rem; }
    .po-detail-actions .btn { border-radius: 999px; font-weight: 600; padding: .5rem 1.15rem; }
    .po-detail-actions .btn-outline-secondary { background: #fff; border: 1px solid #e2e5ea; color: #212529; }
    .po-detail-actions .btn-outline-secondary:hover { background: #f8f9fa; }

    .po-stat-row { display: grid; gap: 1rem; grid-template-columns: repeat(4, 1fr); margin-bottom: 1.25rem; }
    .po-stat-card { align-items: center; background: #fff; border-radius: 14px; box-shadow: 0 2px 10px rgba(15, 23, 42, .05); display: flex; gap: .75rem; padding: 1rem 1.15rem; }
    .po-stat-icon { align-items: center; background: #eef2f7; border-radius: 10px; color: #6c757d; display: flex; flex-shrink: 0; height: 40px; justify-content: center; width: 40px; }
    .po-stat-label { color: #8a94a6; font-size: .7rem; font-weight: 700; letter-spacing: .04em; margin-bottom: .15rem; text-transform: uppercase; }
    .po-stat-value { color: #1c2333; font-size: 1rem; font-weight: 700; }

    .po-detail-grid { align-items: start; display: grid; gap: 1.25rem; grid-template-columns: minmax(0, 1fr) 320px; }
    .po-card { background: #fff; border-radius: 14px; box-shadow: 0 2px 10px rgba(15, 23, 42, .05); overflow: hidden; }
    .po-card-header { align-items: center; border-bottom: 1px solid #f0f2f5; display: flex; justify-content: space-between; padding: 1rem 1.25rem; }
    .po-card-header h4 { font-size: 1rem; font-weight: 700; margin: 0; }
    .po-card-badge { background: #f1f3f6; border-radius: 999px; color: #6c757d; font-size: .75rem; font-weight: 600; padding: .2rem .65rem; }
    .po-card-header-title { align-items: center; display: flex; gap: .6rem; }

    .po-card-toggle { cursor: pointer; user-select: none; }
    .po-card-toggle:hover { background: #fafbfc; }
    .po-card-toggle-icon { color: #b7bec9; flex-shrink: 0; font-size: .8rem; margin-left: .75rem; transition: transform .18s ease; }
    .po-card.is-collapsed .po-card-toggle-icon { transform: rotate(-90deg); }
    .po-card.is-collapsed .po-card-toggle { border-bottom: 0; }
    .po-card.is-collapsed .po-card-body-collapsible { display: none; }

    .po-item-table { border-collapse: collapse; width: 100%; }
    .po-item-table th { border-bottom: 1px solid #f0f2f5; color: #8a94a6; font-size: .7rem; font-weight: 700; letter-spacing: .03em; padding: .6rem 1.25rem; text-align: left; text-transform: uppercase; white-space: nowrap; }
    .po-item-table td { border-bottom: 1px solid #f6f7f9; padding: .8rem 1.25rem; vertical-align: middle; }
    .po-item-table th.text-right, .po-item-table td.text-right { text-align: right; }
    .po-item-total-row { align-items: center; background: #f8f9fb; display: flex; gap: 1rem; justify-content: flex-end; padding: .9rem 1.25rem; }
    .po-item-total-row .label { color: #8a94a6; font-size: .85rem; font-weight: 600; }
    .po-item-total-row .value { font-size: 1.1rem; font-weight: 700; }

    .po-info-row { border-bottom: 1px solid #f6f7f9; display: flex; gap: .75rem; justify-content: space-between; padding: .65rem 1.25rem; }
    .po-info-row:last-child { border-bottom: 0; }
    .po-info-label { color: #b07a3e; flex-shrink: 0; font-size: .82rem; font-weight: 600; }
    .po-info-value { color: #1c2333; font-size: .9rem; font-weight: 700; text-align: right; }

    .po-progress-body { padding: 1.1rem 1.25rem; }
    .po-progress-bar-track { background: #eef1f5; border-radius: 999px; height: 8px; margin-bottom: .6rem; overflow: hidden; }
    .po-progress-bar-fill { background: #16a37a; border-radius: 999px; height: 100%; }
    .po-progress-caption { color: #6c757d; font-size: .82rem; }

    .po-progress-split { display: grid; gap: .75rem; grid-template-columns: 1fr 1fr; margin: 1.1rem 0; }
    .po-progress-split-box { border-radius: 10px; padding: .7rem .9rem; }
    .po-progress-split-done { background: #f1f3f6; }
    .po-progress-split-remaining { background: #fbf1e2; }
    .po-progress-split-label { color: #8a94a6; display: block; font-size: .78rem; margin-bottom: .2rem; }
    .po-progress-split-value { color: #1c2333; font-size: 1.05rem; font-weight: 700; }

    .po-progress-info-row.po-info-row { padding: .55rem 0; }

    .po-badge { border-radius: 999px; font-size: .78rem; font-weight: 700; padding: .25rem .8rem; }
    .po-badge-secondary { background: #eef1f5; color: #6c757d; }
    .po-badge-warning { background: #fbe9cf; color: #a86a1c; }
    .po-badge-success { background: #d9f2e6; color: #17805a; }

    .po-empty-state { color: #8a94a6; font-size: .85rem; }

    @media (max-width: 991.98px) {
        .po-stat-row { grid-template-columns: repeat(2, 1fr); }
        .po-detail-grid { grid-template-columns: 1fr; }
    }
</style>
<script>
    document.querySelectorAll('[data-toggle="po-collapse"]').forEach(function(header) {
        header.addEventListener('click', function() {
            header.closest('.po-card').classList.toggle('is-collapsed');
        });
    });
</script>
<?= $this->endSection('isi') ?>
