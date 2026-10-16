<style>
    .report-bulanan-grid { display: grid; gap: 1rem; grid-template-columns: repeat(2, minmax(0, 1fr)); margin-bottom: 1rem; }
    .report-bulanan-week, .report-bulanan-kategori, .report-bulanan-due { border: 1px solid #e3ebf2; border-radius: .8rem; overflow: hidden; }
    .report-bulanan-week-header { color: #071226; font-weight: 800; padding: .55rem .9rem; }
    .report-bulanan-kategori-header, .report-bulanan-due-header { background: #dbe9f7; color: #071226; font-weight: 800; padding: .5rem; text-align: center; }
    .report-bulanan-week table, .report-bulanan-kategori table, .report-bulanan-due table { margin: 0; width: 100%; }
    .report-bulanan-week td, .report-bulanan-kategori td, .report-bulanan-kategori th, .report-bulanan-due td, .report-bulanan-due th { padding: .45rem .8rem; font-size: .9rem; }
    .report-bulanan-nominal { font-variant-numeric: tabular-nums; text-align: right; }
    .report-bulanan-total td { border-top: 2px solid rgba(0, 0, 0, .15); font-weight: 800; }
    .report-bulanan-kategori tr.total-row td { background: #b7e4b0; font-weight: 800; }
    .report-bulanan-total-keseluruhan { background: #d9f2d0; border-radius: .8rem; display: flex; justify-content: space-between; padding: .9rem 1.1rem; font-weight: 800; }
    .report-bulanan-grand-total { background: #cfe8fb; border-radius: .8rem; display: flex; justify-content: space-between; margin-top: 1rem; padding: 1.1rem; font-size: 1.1rem; font-weight: 800; }
    .report-bulanan-kategori th, .report-bulanan-due th { background: #f5f8fb; white-space: nowrap; }
    .report-bulanan-month { border-top: 1px solid #e3ebf2; }
    .report-bulanan-month:first-child { border-top: 0; }
    .report-bulanan-month-title { background: #f5f8fb; color: #071226; font-weight: 800; padding: .55rem .8rem; }
    @media (max-width: 991.98px) {
        .report-bulanan-grid { grid-template-columns: 1fr; }
        .report-bulanan-week-posisi { grid-row: auto !important; grid-column: auto !important; }
    }
</style>
<?php $formatRupiah = static fn($angka) => 'Rp' . number_format((float) $angka, 0, ',', '.'); ?>
<?php if (!$periodeDipilih || $report === null) : ?>
    <div class="reporting-empty">Silakan pilih tanggal awal dan tanggal akhir untuk memuat laporan omzet.</div>
<?php else : ?>
    <p class="reporting-periode-info">
        REPORT <?= esc(strtoupper($report['namaBulan'])) ?> <?= esc($report['tahun']) ?>
        <span class="text-muted">(<?= esc($report['rentangLabel']) ?>)</span>
        <?php if ($namaPelanggan !== '') : ?><span class="text-muted">| Pelanggan: <?= esc($namaPelanggan) ?></span><?php endif ?>
    </p>

    <?php
        $warnaMinggu = ['#f6b26b', '#ffff00', '#e06fce', '#f6b26b'];
        $posisiGrid = [0 => 'grid-row:1; grid-column:1;', 1 => 'grid-row:2; grid-column:1;', 2 => 'grid-row:1; grid-column:2;', 3 => 'grid-row:2; grid-column:2;'];
    ?>
    <div class="report-bulanan-grid">
        <?php foreach ($report['weeks'] as $i => $week) : ?>
            <div class="report-bulanan-week report-bulanan-week-posisi" style="<?= esc($posisiGrid[$i] ?? '') ?>">
                <div class="report-bulanan-week-header" style="background: <?= esc($warnaMinggu[$i] ?? '#dbe9f7') ?>;"><?= esc(strtoupper($week['label'])) ?></div>
                <table><tbody>
                    <?php foreach ($week['days'] as $day) : ?>
                        <tr><td><?= esc($day['nama_hari']) ?>, <?= esc(date('d M Y', strtotime($day['tanggal']))) ?></td><td class="report-bulanan-nominal"><?= $formatRupiah($day['total']) ?></td></tr>
                    <?php endforeach ?>
                    <tr class="report-bulanan-total"><td>TOTAL</td><td class="report-bulanan-nominal"><?= $formatRupiah($week['total']) ?></td></tr>
                </tbody></table>
            </div>
        <?php endforeach ?>
    </div>
    <div class="report-bulanan-grid">
        <?php foreach ($report['tahap'] as $tahap) : ?>
            <div class="report-bulanan-total-keseluruhan"><span>TOTAL KESELURUHAN</span><span><?= $formatRupiah($tahap['total']) ?></span></div>
        <?php endforeach ?>
    </div>

    <div class="report-bulanan-grid">
        <div class="report-bulanan-kategori">
            <div class="report-bulanan-kategori-header">Omzet Invoice per Kategori per Bulan</div>
            <?php foreach ($report['months'] as $month) : ?>
                <div class="report-bulanan-month">
                    <div class="report-bulanan-month-title"><?= esc(strtoupper($month['label'])) ?></div>
                    <table><thead><tr><th>Kategori</th><th class="report-bulanan-nominal">Total Omzet</th></tr></thead><tbody>
                        <?php if (empty($month['categories'])) : ?>
                            <tr><td colspan="2" class="text-muted">Tidak ada omzet pada bulan ini.</td></tr>
                        <?php else : ?>
                            <?php foreach ($month['categories'] as $category) : ?>
                                <tr><td><?= esc($category['nama']) ?></td><td class="report-bulanan-nominal"><?= $formatRupiah($category['total']) ?></td></tr>
                            <?php endforeach ?>
                            <tr class="total-row"><td>TOTAL <?= esc(strtoupper($month['label'])) ?></td><td class="report-bulanan-nominal"><?= $formatRupiah($month['total']) ?></td></tr>
                        <?php endif ?>
                    </tbody></table>
                </div>
            <?php endforeach ?>
        </div>

        <div class="report-bulanan-due">
            <div class="report-bulanan-due-header">Total Tagihan Invoice per Due Date</div>
            <table><thead><tr><th>Due Date</th><th class="text-center">Invoice</th><th class="report-bulanan-nominal">Total Tagihan</th></tr></thead><tbody>
                <?php if (empty($report['dueDates'])) : ?>
                    <tr><td colspan="3" class="text-center text-muted">Tidak ada invoice aktif pada periode ini.</td></tr>
                <?php else : ?>
                    <?php foreach ($report['dueDates'] as $due) : ?>
                        <tr><td><?= esc(date('d-m-Y', strtotime($due['due_date']))) ?></td><td class="text-center"><?= number_format((int) $due['invoice_count'], 0, ',', '.') ?></td><td class="report-bulanan-nominal"><?= $formatRupiah($due['total']) ?></td></tr>
                    <?php endforeach ?>
                <?php endif ?>
            </tbody></table>
        </div>
    </div>

    <div class="report-bulanan-grand-total"><span>TOTAL OMZET PERIODE</span><span><?= $formatRupiah($report['grandTotal']) ?></span></div>
<?php endif ?>
