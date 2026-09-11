<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Upload Bukti Transfer Invoice In
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
        <strong>Upload Bukti Transfer</strong>
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
            <?php if (!empty($invoice['bukti_transfer_file'])) : ?>
                <tr>
                    <th>Bukti Saat Ini</th>
                    <td>
                        <a href="<?= site_url('invoiceIn/buktiTransfer/' . $invoice['id']) ?>" target="_blank">
                            <?= esc($invoice['bukti_transfer_original_name'] ?: $invoice['bukti_transfer_file']) ?>
                        </a>
                    </td>
                </tr>
            <?php endif ?>
        </table>

        <?= form_open_multipart('/invoiceIn/simpanBuktiTransfer/' . $invoice['id']) ?>
            <div class="form-group">
                <label>Bukti Transfer</label>
                <input type="file" name="bukti_transfer" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                <small class="form-text text-muted">Format PDF, JPG, JPEG, atau PNG. Maksimal 5 MB. Setelah disimpan, status invoice otomatis menjadi Lunas.</small>
            </div>
            <button type="submit" class="btn btn-success">
                <i class="fas fa-upload"></i> Upload & Tandai Lunas
            </button>
        <?= form_close() ?>
    </div>
</div>
<?= $this->endSection('isi') ?>
