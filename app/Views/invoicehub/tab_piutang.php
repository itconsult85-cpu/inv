<?php
    $totalPiutang = 0.0;
    foreach ($piutangRows as $row) {
        $totalPiutang += (float) ($row['sisa'] ?? 0);
    }
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
        <div class="reporting-summary-label">Invoice Belum Lunas</div>
        <div class="reporting-summary-value"><?= number_format(count($piutangRows), 0, ',', '.') ?></div>
    </div>
    <div class="reporting-summary-card">
        <div class="reporting-summary-label">Total Piutang</div>
        <div class="reporting-summary-value">Rp <?= number_format($totalPiutang, 0, ',', '.') ?></div>
    </div>
</div>
<?php if (!$periodeDipilih) : ?>
    <div class="reporting-empty">Silakan pilih periode di atas, lalu klik Tampilkan.</div>
<?php elseif (empty($piutangRows)) : ?>
    <div class="reporting-empty">Tidak ada piutang pada periode ini.</div>
<?php else : ?>
    <div class="table-responsive">
        <table class="table table-bordered reporting-data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>No Invoice</th>
                    <th>Tanggal</th>
                    <th>No PO</th>
                    <th>Pelanggan</th>
                    <th class="text-right">Grand Total</th>
                    <th class="text-right">Sudah Bayar</th>
                    <th class="text-right">Sisa</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($piutangRows as $i => $row) : ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= esc($row['invoice_no']) ?></td>
                        <td><?= date('d-m-Y', strtotime($row['invoice_date'])) ?></td>
                        <td><?= esc($row['po_no']) ?></td>
                        <td><?= esc($row['customer_name']) ?></td>
                        <td class="text-right">Rp <?= number_format($row['grand_total'], 0, ',', '.') ?></td>
                        <td class="text-right">Rp <?= number_format($row['paid_total'] ?? 0, 0, ',', '.') ?></td>
                        <td class="text-right font-weight-bold">Rp <?= number_format($row['sisa'], 0, ',', '.') ?></td>
                        <td><?= esc($row['status_bayar'] ?? 'Belum Lunas') ?></td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif ?>
