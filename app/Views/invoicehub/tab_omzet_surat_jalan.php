<style>
    .report-bulanan-grid {
        display: grid;
        gap: 1rem;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        margin-bottom: 1rem;
    }

    .report-bulanan-week {
        border: 1px solid #e3ebf2;
        border-radius: .8rem;
        overflow: hidden;
    }

    .report-bulanan-week-header {
        color: #071226;
        font-weight: 800;
        padding: .55rem .9rem;
    }

    .report-bulanan-week table {
        margin-bottom: 0;
        width: 100%;
    }

    .report-bulanan-week td {
        padding: .4rem .9rem;
        font-size: .9rem;
    }

    .report-bulanan-week td.report-bulanan-nominal {
        text-align: right;
        font-variant-numeric: tabular-nums;
    }

    .report-bulanan-week tr.report-bulanan-total td {
        font-weight: 800;
        border-top: 2px solid rgba(0, 0, 0, .15);
    }

    .report-bulanan-total-keseluruhan {
        background: #d9f2d0;
        border-radius: .8rem;
        padding: .9rem 1.1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-weight: 800;
    }

    .report-bulanan-kategori {
        border: 1px solid #e3ebf2;
        border-radius: .8rem;
        overflow: hidden;
    }

    .report-bulanan-kategori-header {
        background: #dbe9f7;
        text-align: center;
        padding: .5rem;
    }

    .report-bulanan-kategori-header .label-utama {
        font-weight: 800;
        display: block;
    }

    .report-bulanan-kategori-header .label-tahap {
        font-size: .82rem;
        color: #445;
    }

    .report-bulanan-kategori table {
        margin-bottom: 0;
        width: 100%;
    }

    .report-bulanan-kategori td {
        padding: .5rem .9rem;
        font-size: .9rem;
    }

    .report-bulanan-kategori td.report-bulanan-nominal {
        text-align: right;
        font-variant-numeric: tabular-nums;
    }

    .report-bulanan-kategori tr.report-bulanan-total td {
        background: #b7e4b0;
        font-weight: 800;
    }

    .report-bulanan-grand-total {
        background: #cfe8fb;
        border-radius: .8rem;
        padding: 1.1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-weight: 800;
        font-size: 1.1rem;
        margin-top: 1rem;
    }

    @media (max-width: 991.98px) {
        .report-bulanan-grid {
            grid-template-columns: 1fr;
        }

        /* Di layar sempit cuma 1 kolom -- balikin ke urutan DOM alami
        (Pertama, Kedua, Ketiga, Keempat) daripada posisi grid 2-kolom. */
        .report-bulanan-week-posisi {
            grid-row: auto !important;
            grid-column: auto !important;
        }
    }
</style>

<?php
    $warnaMinggu = ['#f6b26b', '#ffff00', '#e06fce', '#f6b26b'];

    $formatRupiah = static function (float $angka): string {
        return 'Rp' . number_format($angka, 0, ',', '.');
    };
?>

<?php if (!$periodeDipilih || $report === null) : ?>
    <div class="reporting-empty">Silakan klik Tampilkan untuk memuat laporan.</div>
<?php else : ?>
    <p class="reporting-periode-info">
        REPORT <?= strtoupper(esc($report['namaBulan'])) ?> <?= esc($report['tahun']) ?>
        <span class="text-muted">(<?= esc($report['rentangLabel']) ?>)</span>
        <?php if ($namaPelanggan !== '') : ?>
            <span class="text-muted">| Pelanggan: <?= esc($namaPelanggan) ?></span>
        <?php endif ?>
    </p>

    <?php
        // Posisi grid desktop (2 kolom): [Pertama, Ketiga] baris atas, [Kedua,
        // Keempat] baris bawah -- supaya kolom KIRI selalu Tahap 1
        // (Pertama+Kedua) dan kolom KANAN selalu Tahap 2 (Ketiga+Keempat),
        // sejalan lurus dengan kotak Total Keseluruhan & Per Kategori di
        // bawahnya. Urutan DOM tetap kronologis (0,1,2,3) -- cuma posisi
        // visualnya yang diatur lewat CSS grid-row/grid-column, supaya di
        // mobile (1 kolom) tetap kestack urut Pertama->Kedua->Ketiga->Keempat.
        $posisiGrid = [
            0 => 'grid-row:1; grid-column:1;', // Pertama
            1 => 'grid-row:2; grid-column:1;', // Kedua
            2 => 'grid-row:1; grid-column:2;', // Ketiga
            3 => 'grid-row:2; grid-column:2;', // Keempat
        ];
    ?>
    <div class="report-bulanan-grid">
        <?php foreach ($report['weeks'] as $i => $week) : ?>
            <div class="report-bulanan-week report-bulanan-week-posisi" style="<?= esc($posisiGrid[$i]) ?>">
                <div class="report-bulanan-week-header" style="background: <?= esc($warnaMinggu[$i]) ?>;">
                    <?= esc(strtoupper($week['label'])) ?>
                </div>
                <table>
                    <tbody>
                        <?php foreach ($week['days'] as $day) : ?>
                            <tr>
                                <td><?= esc($day['nama_hari']) ?>, <?= date('d M Y', strtotime($day['tanggal'])) ?></td>
                                <td class="report-bulanan-nominal"><?= $formatRupiah($day['total']) ?></td>
                            </tr>
                        <?php endforeach ?>
                        <tr class="report-bulanan-total">
                            <td>TOTAL</td>
                            <td class="report-bulanan-nominal"><?= $formatRupiah($week['total']) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endforeach ?>
    </div>

    <div class="report-bulanan-grid">
        <?php foreach ($report['tahap'] as $t) : ?>
            <div class="report-bulanan-total-keseluruhan">
                <span>TOTAL KESELURUHAN</span>
                <span><?= $formatRupiah($t['total']) ?></span>
            </div>
        <?php endforeach ?>
    </div>

    <div class="report-bulanan-grid">
        <?php foreach ($report['kategoriPerTahap'] as $kat) : ?>
            <div class="report-bulanan-kategori">
                <div class="report-bulanan-kategori-header">
                    <span class="label-utama">Per Kategori</span>
                    <span class="label-tahap"><?= esc($kat['label']) ?></span>
                </div>
                <table>
                    <tbody>
                        <?php if (empty($kat['categories'])) : ?>
                            <tr>
                                <td colspan="2" class="text-muted">Tidak ada omzet pada periode ini.</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($kat['categories'] as $cat) : ?>
                                <tr>
                                    <td><?= esc($cat['nama']) ?></td>
                                    <td class="report-bulanan-nominal"><?= $formatRupiah($cat['total']) ?></td>
                                </tr>
                            <?php endforeach ?>
                        <?php endif ?>
                        <tr class="report-bulanan-total">
                            <td>TOTAL</td>
                            <td class="report-bulanan-nominal">Rp <?= number_format($kat['total'], 0, ',', '.') ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endforeach ?>
    </div>

    <div class="report-bulanan-grand-total">
        <span>TOTAL TAGIHAN BULAN <?= strtoupper(esc($report['namaBulan'])) ?></span>
        <span><?= $formatRupiah($report['grandTotal']) ?></span>
    </div>
<?php endif ?>
