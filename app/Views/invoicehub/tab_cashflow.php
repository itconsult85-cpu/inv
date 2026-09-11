<?php
    $cashMasuk = 0.0;
    $cashKeluar = 0.0;
    foreach ($cashflowRows as $row) {
        if (($row['jenis'] ?? '') === 'Masuk') {
            $cashMasuk += (float) ($row['nominal'] ?? 0);
        } else {
            $cashKeluar += (float) ($row['nominal'] ?? 0);
        }
    }
    $cashNet = $cashMasuk - $cashKeluar;
?>
<?php if ($periodeDipilih) : ?>
    <p class="reporting-periode-info">
        Periode <?= date('d-m-Y', strtotime($tglawal)) ?> s/d <?= date('d-m-Y', strtotime($tglakhir)) ?>
        <?php if ($namaPelanggan !== '') : ?>
            <span class="text-muted">| Pelanggan: <?= esc($namaPelanggan) ?></span>
        <?php endif ?>
    </p>
<?php endif ?>
<div class="reporting-summary-grid">
    <div class="reporting-summary-card">
        <div class="reporting-summary-label">Kas Masuk</div>
        <div class="reporting-summary-value">Rp <?= number_format($cashMasuk, 0, ',', '.') ?></div>
    </div>
    <div class="reporting-summary-card">
        <div class="reporting-summary-label">Kas Keluar</div>
        <div class="reporting-summary-value">Rp <?= number_format($cashKeluar, 0, ',', '.') ?></div>
    </div>
    <div class="reporting-summary-card">
        <div class="reporting-summary-label">Net Cashflow</div>
        <div class="reporting-summary-value">Rp <?= number_format($cashNet, 0, ',', '.') ?></div>
    </div>
</div>
<?php if (!$periodeDipilih) : ?>
    <div class="reporting-empty">Silakan pilih periode di atas, lalu klik Tampilkan.</div>
<?php elseif (empty($cashflowRows)) : ?>
    <div class="reporting-empty">Tidak ada pembayaran masuk/keluar pada periode ini.</div>
<?php else : ?>
    <div class="table-responsive">
        <table class="table table-bordered reporting-data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Jenis</th>
                    <th>No Bukti/Invoice</th>
                    <th>Pihak</th>
                    <th>Keterangan</th>
                    <th class="text-right">Nominal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cashflowRows as $i => $row) : ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= !empty($row['tanggal']) ? date('d-m-Y', strtotime($row['tanggal'])) : '-' ?></td>
                        <td><?= esc($row['jenis']) ?></td>
                        <td><?= esc($row['nomor']) ?></td>
                        <td><?= esc($row['pihak']) ?></td>
                        <td><?= esc($row['keterangan'] ?? '-') ?></td>
                        <td class="text-right font-weight-bold">Rp <?= number_format($row['nominal'], 0, ',', '.') ?></td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif ?>
