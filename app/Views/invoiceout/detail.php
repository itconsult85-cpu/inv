<?= $this->extend('main/layout') ?>
<?= $this->section('judul') ?>Detail Invoice Out<?= $this->endSection('judul') ?>
<?= $this->section('subjudul') ?>
<a href="<?= site_url('invoiceOut/data') ?>" class="btn btn-warning"><i class="fas fa-undo"></i> Kembali</a>
<a href="<?= site_url('invoiceOut/cetak/' . $invoice['id']) ?>" target="_blank" class="btn btn-primary"><i class="fas fa-print"></i> Print</a>
<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<?php if (session('message')) : ?><div class="alert alert-success"><?= esc(session('message')) ?></div><?php endif ?>
<?php if (session('error')) : ?><div class="alert alert-danger"><?= esc(session('error')) ?></div><?php endif ?>
<?php
$paidTotal = (float) ($invoice['paid_total'] ?? (($invoice['status_bayar'] ?? '') === 'Lunas' ? $invoice['grand_total'] : 0));
$remaining = max((float) $invoice['grand_total'] - $paidTotal, 0);
$statusBayar = $invoice['status_bayar'] ?? 'Belum Lunas';
$badgeBayar = $statusBayar === 'Lunas' ? 'success' : ($statusBayar === 'Dibayar Sebagian' ? 'info' : 'warning');
$statusTampil = ($invoice['status'] ?? '') === 'DIBATALKAN'
    ? 'DIBATALKAN'
    : ($statusBayar === 'Lunas' ? 'SELESAI' : 'AKTIF');
$badgeStatus = $statusTampil === 'SELESAI'
    ? 'primary'
    : ($statusTampil === 'AKTIF' ? 'success' : 'secondary');
?>

<div class="row">
    <div class="col-md-6">
        <table class="table table-sm">
            <tr><th>No. Invoice</th><td><?= esc($invoice['invoice_no']) ?></td></tr>
            <tr><th>Tanggal</th><td><?= date('d-m-Y', strtotime($invoice['invoice_date'])) ?></td></tr>
            <tr><th>Status</th><td><span class="badge badge-<?= $badgeStatus ?>"><?= esc($statusTampil) ?></span></td></tr>
            <tr>
                <th>Status Bayar</th>
                <td>
                    <span class="badge badge-<?= $badgeBayar ?>"><?= esc($statusBayar) ?></span>
                    <?php if ($invoice['status'] === 'AKTIF' && $statusBayar !== 'Lunas') : ?>
                        <form action="<?= site_url('invoiceOut/tandaiLunas/' . $invoice['id']) ?>" method="post" class="d-inline ml-2" onsubmit="return confirm('Tandai invoice ini sudah Lunas?');">
                            <?= csrf_field() ?><button class="btn btn-success btn-sm"><i class="fas fa-money-check-alt"></i> Tandai Lunas</button>
                        </form>
                    <?php endif ?>
                </td>
            </tr>
        </table>
    </div>
    <div class="col-md-6">
        <table class="table table-sm">
            <tr><th>No. PO</th><td><?= esc($invoice['po_no']) ?></td></tr>
            <tr><th>Tanggal PO</th><td><?= date('d-m-Y', strtotime($invoice['po_date'])) ?></td></tr>
            <tr><th>Pelanggan</th><td><?= esc($invoice['customer_name']) ?></td></tr>
            <tr><th>Penandatangan</th><td><?= esc($invoice['signer_name']) ?> - <?= esc($invoice['signer_position']) ?></td></tr>
            <tr><th>Bank</th><td><?= esc($invoice['bank_name']) ?> / <?= esc($invoice['bank_account']) ?></td></tr>
        </table>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr><th>No</th><th>No. Surat Jalan</th><th>Produk</th><th>Qty</th><th>UoM</th><th>Harga</th><th>Amount</th></tr>
        </thead>
        <tbody>
            <?php foreach ($details as $i => $detail) : ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= esc($detail['source_no'] ?? '-') ?></td>
                    <td><?= esc($detail['product_code'] . ' - ' . $detail['product_name']) ?></td>
                    <td class="text-right"><?= number_format($detail['qty'], 0, ',', '.') ?></td>
                    <td><?= esc($detail['unit']) ?></td>
                    <td class="text-right">Rp <?= number_format($detail['unit_price'], 0, ',', '.') ?></td>
                    <td class="text-right">Rp <?= number_format($detail['amount'], 0, ',', '.') ?></td>
                </tr>
            <?php endforeach ?>
        </tbody>
        <tfoot>
            <tr><th colspan="6" class="text-right">Subtotal</th><th class="text-right">Rp <?= number_format($invoice['subtotal'], 0, ',', '.') ?></th></tr>
            <?php if ((int) ($invoice['ppn_enabled'] ?? 1) === 1) : ?>
                <tr><th colspan="6" class="text-right">PPN <?= number_format((float) ($invoice['ppn_percent'] ?? 11), 0, ',', '.') ?>%</th><th class="text-right">Rp <?= number_format($invoice['ppn'], 0, ',', '.') ?></th></tr>
            <?php endif ?>
            <?php if ((int) ($invoice['pph_enabled'] ?? 1) === 1) : ?>
                <tr><th colspan="6" class="text-right">PPh 23 (<?= number_format((float) ($invoice['pph_percent'] ?? 2), 0, ',', '.') ?>%)</th><th class="text-right">Rp <?= number_format($invoice['pph23'], 0, ',', '.') ?></th></tr>
            <?php endif ?>
            <?php if ((int) ($invoice['dp_enabled'] ?? 0) === 1) : ?>
                <tr><th colspan="6" class="text-right">DP <?= number_format((float) ($invoice['dp_percent'] ?? 50), 0, ',', '.') ?>%</th><th class="text-right">Rp <?= number_format((float) ($invoice['dp_amount'] ?? 0), 0, ',', '.') ?></th></tr>
            <?php endif ?>
            <tr><th colspan="6" class="text-right">Grand Total</th><th class="text-right">Rp <?= number_format($invoice['grand_total'], 0, ',', '.') ?></th></tr>
            <tr><th colspan="6" class="text-right">Sudah Dibayar</th><th class="text-right">Rp <?= number_format($paidTotal, 0, ',', '.') ?></th></tr>
            <tr><th colspan="6" class="text-right">Sisa Tagihan</th><th class="text-right">Rp <?= number_format($remaining, 0, ',', '.') ?></th></tr>
        </tfoot>
    </table>
</div>

<?php if (!empty($payments)) : ?>
    <h5>Riwayat Pembayaran</h5>
    <div class="table-responsive">
        <table class="table table-bordered table-sm">
            <thead>
                <tr><th>No</th><th>No. Pembayaran</th><th>Tanggal</th><th>Alokasi ke Invoice Ini</th><th>Dicatat Oleh</th><th>Keterangan</th></tr>
            </thead>
            <tbody>
                <?php foreach ($payments as $i => $payment) : ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= esc($payment['payment_no']) ?></td>
                        <td><?= date('d-m-Y', strtotime($payment['payment_date'])) ?></td>
                        <td class="text-right">Rp <?= number_format((float) $payment['allocated_amount'], 0, ',', '.') ?></td>
                        <td><?= esc($payment['created_by'] ?? '-') ?></td>
                        <td><?= esc($payment['note'] ?: '-') ?></td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif ?>
<?= $this->endSection('isi') ?>
