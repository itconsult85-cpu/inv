<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Upload File Invoice In
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>
<a href="<?= site_url('invoiceIn/detail/' . $invoice['id']) ?>" class="btn btn-warning">
    <i class="fas fa-undo"></i> Kembali
</a>
<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<?php if (session('error')) : ?>
    <div class="alert alert-danger"><?= session('error') ?></div>
<?php endif ?>

<div class="card">
    <div class="card-header">
        <strong>Upload File Invoice Supplier</strong>
    </div>
    <div class="card-body">
        <table class="table table-sm table-borderless mb-4">
            <tr>
                <th style="width: 180px;">No. Invoice</th>
                <td><?= esc($invoice['invoice_no']) ?></td>
            </tr>
            <tr>
                <th>Supplier</th>
                <td><?= esc($invoice['supplier_name']) ?></td>
            </tr>
            <tr>
                <th>Grand Total</th>
                <td>Rp <?= number_format((float) $invoice['grand_total'], 0, ',', '.') ?></td>
            </tr>
            <tr>
                <th>Status</th>
                <td><?= esc($invoice['status']) ?></td>
            </tr>
            <?php if (!empty($invoice['invoice_file'])) : ?>
                <tr>
                    <th>File Saat Ini</th>
                    <td>
                        <a href="<?= site_url('invoiceIn/file/' . $invoice['id']) ?>" target="_blank">
                            <?= esc($invoice['invoice_original_name'] ?: $invoice['invoice_file']) ?>
                        </a>
                    </td>
                </tr>
            <?php endif ?>
        </table>

        <?= form_open_multipart('/invoiceIn/simpanFileInvoice/' . $invoice['id']) ?>
            <div class="form-group">
                <label>File Invoice</label>
                <input type="file" name="invoice_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                <small class="form-text text-muted">Format PDF, JPG, JPEG, atau PNG. Maksimal 10 MB.</small>
            </div>
            <button type="submit" class="btn btn-success">
                <i class="fas fa-upload"></i> Upload File Invoice
            </button>
        <?= form_close() ?>
    </div>
</div>
<?= $this->endSection('isi') ?>
