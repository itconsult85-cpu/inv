<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Keuangan
<?= $this->endSection('judul') ?>

<?= $this->section('isi') ?>

<?php
    $bisaInvoiceOut = \App\Libraries\AccessControl::can('order.invoice_out.view');
    $bisaInvoiceIn = \App\Libraries\AccessControl::can('order.invoice_in.view');
    $bisaReporting = \App\Libraries\AccessControl::can('order.invoice_hub.reporting');
?>

<div class="row">
    <?php if ($bisaInvoiceOut) : ?>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-file-invoice-dollar fa-3x text-success mb-3"></i>
                    <h5>Invoice Out</h5>
                    <p class="text-muted">Terbitkan &amp; kelola invoice tagihan ke pelanggan.</p>
                    <a href="<?= site_url('invoiceOut/data') ?>" class="btn btn-success btn-block">Buka</a>
                </div>
            </div>
        </div>
    <?php endif ?>
    <?php if ($bisaInvoiceIn) : ?>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-file-import fa-3x text-info mb-3"></i>
                    <h5>Invoice In</h5>
                    <p class="text-muted">Terbitkan &amp; kelola invoice tagihan dari supplier.</p>
                    <a href="<?= site_url('invoiceIn/data') ?>" class="btn btn-info btn-block">Buka</a>
                </div>
            </div>
        </div>
    <?php endif ?>
    <?php if ($bisaReporting) : ?>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-chart-line fa-3x text-primary mb-3"></i>
                    <h5>Reporting</h5>
                    <p class="text-muted">Qty, harga jual, harga modal, dan margin per produk.</p>
                    <a href="<?= site_url('invoiceHub/reporting') ?>" class="btn btn-primary btn-block">Buka</a>
                </div>
            </div>
        </div>
    <?php endif ?>
    <?php if (!$bisaInvoiceOut && !$bisaInvoiceIn && !$bisaReporting) : ?>
        <div class="col-12">
            <p class="text-muted text-center">Belum ada fitur Invoice yang bisa diakses akun ini.</p>
        </div>
    <?php endif ?>
</div>

<?= $this->endSection('isi') ?>
