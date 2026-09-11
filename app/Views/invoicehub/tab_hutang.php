<?php
    $totalHutang = 0.0;
    foreach ($hutangRows as $row) {
        $totalHutang += (float) ($row['sisa'] ?? 0);
    }
?>
<?php if ($periodeDipilih) : ?>
    <p class="reporting-periode-info">Periode <?= date('d-m-Y', strtotime($tglawal)) ?> s/d <?= date('d-m-Y', strtotime($tglakhir)) ?></p>
<?php endif ?>
<div class="reporting-summary-grid">
    <div class="reporting-summary-card">
        <div class="reporting-summary-label">Invoice In Belum Lunas</div>
        <div class="reporting-summary-value"><?= number_format(count($hutangRows), 0, ',', '.') ?></div>
    </div>
    <div class="reporting-summary-card">
        <div class="reporting-summary-label">Total Hutang</div>
        <div class="reporting-summary-value">Rp <?= number_format($totalHutang, 0, ',', '.') ?></div>
    </div>
</div>
<?php if (!$periodeDipilih) : ?>
    <div class="reporting-empty">Silakan pilih periode di atas, lalu klik Tampilkan.</div>
<?php elseif (empty($hutangRows)) : ?>
    <div class="reporting-empty">Tidak ada hutang supplier/vendor pada periode ini.</div>
<?php else : ?>
    <div class="table-responsive">
        <table class="table table-bordered reporting-data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>No Invoice</th>
                    <th>Tanggal</th>
                    <th>Sumber</th>
                    <th>Supplier/Vendor</th>
                    <th class="text-right">Grand Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($hutangRows as $i => $row) : ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= esc($row['invoice_no']) ?></td>
                        <td><?= date('d-m-Y', strtotime($row['invoice_date'])) ?></td>
                        <td><?= esc($row['source_type'] . ' - ' . $row['source_no']) ?></td>
                        <td><?= esc($row['supplier_name']) ?></td>
                        <td class="text-right font-weight-bold">Rp <?= number_format($row['grand_total'], 0, ',', '.') ?></td>
                        <td><?= esc($row['status']) ?></td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif ?>
