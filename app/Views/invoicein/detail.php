<?= $this->extend('main/layout') ?>
<?= $this->section('judul') ?>Detail Invoice In<?= $this->endSection('judul') ?>
<?= $this->section('subjudul') ?>
<a href="<?= site_url('invoiceIn/data') ?>" class="btn btn-warning"><i class="fas fa-undo"></i> Kembali</a>
<?= $this->endSection('subjudul') ?>
<?= $this->section('isi') ?>
<?php if (session('message')) : ?><div class="alert alert-success"><?= esc(session('message')) ?></div><?php endif ?>
<?php if (session('error')) : ?><div class="alert alert-danger"><?= esc(session('error')) ?></div><?php endif ?>
<div class="row">
    <div class="col-md-6">
        <table class="table table-sm">
            <tr>
                <th>No. Invoice Supplier</th>
                <td><?= esc($invoice['invoice_no']) ?></td>
            </tr>
            <tr>
                <th>Tanggal</th>
                <td><?= date('d-m-Y', strtotime($invoice['invoice_date'])) ?></td>
            </tr>
            <tr>
                <th>Status</th>
                <td><?= esc($invoice['status']) ?></td>
            </tr>
            <tr>
                <th>Tanggal Lunas</th>
                <td><?= !empty($invoice['tanggal_lunas']) ? date('d-m-Y H:i', strtotime($invoice['tanggal_lunas'])) : '-' ?></td>
            </tr>
            <tr>
                <th>File Invoice</th>
                <td>
                    <?php if (!empty($invoice['invoice_file'])) : ?>
                        <a href="<?= site_url('invoiceIn/file/' . $invoice['id']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-paperclip"></i> Lihat
                        </a>
                    <?php endif ?>
                    <?php if ($invoice['status'] !== 'DIBATALKAN') : ?>
                        <a href="<?= site_url('invoiceIn/uploadFileInvoice/' . $invoice['id']) ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-upload"></i> <?= !empty($invoice['invoice_file']) ? 'Ganti' : 'Upload' ?>
                        </a>
                        <?php if (!empty($invoice['invoice_file'])) : ?>
                            <?= form_open('/invoiceIn/hapusFileInvoice/' . $invoice['id'], ['class' => 'd-inline', 'onsubmit' => "return confirm('Hapus file invoice ini? Data Invoice In tetap tersimpan.');"]) ?>
                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            <?= form_close() ?>
                            <?php if (!empty($invoice['invoice_uploaded_at'])) : ?>
                                <small class="text-muted ml-2">Upload: <?= date('d-m-Y H:i', strtotime($invoice['invoice_uploaded_at'])) ?></small>
                            <?php endif ?>
                        <?php endif ?>
                    <?php elseif (empty($invoice['invoice_file'])) : ?>
                        -
                    <?php endif ?>
                </td>
            </tr>
            <tr>
                <th>Bukti Transfer</th>
                <td>
                    <?php if (!empty($invoice['bukti_transfer_file'])) : ?>
                        <a href="<?= site_url('invoiceIn/buktiTransfer/' . $invoice['id']) ?>" target="_blank" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-receipt"></i> Lihat
                        </a>
                    <?php endif ?>
                    <?php if ($invoice['status'] !== 'DIBATALKAN') : ?>
                        <a href="<?= site_url('invoiceIn/uploadBuktiTransfer/' . $invoice['id']) ?>" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-upload"></i> <?= !empty($invoice['bukti_transfer_file']) ? 'Ganti' : 'Upload' ?>
                        </a>
                    <?php elseif (empty($invoice['bukti_transfer_file'])) : ?>
                        -
                    <?php endif ?>
                </td>
            </tr>
        </table>
    </div>
    <div class="col-md-6">
        <?php $sourceNo = (string) ($invoice['source_no'] ?? ''); $sourceParts = explode('||', $sourceNo, 2); $sourceLabel = ($invoice['source_type'] ?? '') === 'po_keluar' ? 'PO Keluar' : ucfirst((string) ($invoice['source_type'] ?? '')) . ' Masuk'; ?>
        <table class="table table-sm">
            <tr>
                <th>Supplier</th>
                <td><?= esc($invoice['supplier_name']) ?></td>
            </tr>
            <tr>
                <th>Sumber</th>
                <td><?= esc($sourceLabel) ?></td>
            </tr>
            <tr>
                <th>No. Transaksi</th>
                <td><?= esc($sourceParts[0]) ?><?= isset($sourceParts[1]) ? ' (SJ ' . esc($sourceParts[1]) . ')' : '' ?></td>
            </tr>
        </table>
    </div>
</div>
<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>No. Surat Jalan</th>
                <th>Item</th>
                <th>Qty</th>
                <th>UoM</th>
                <th>Harga</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($details as $i => $d) : ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= esc($d['no_surat_jalan'] ?: '-') ?></td>
                    <td><?= esc($d['item_code'] . ' - ' . $d['item_name']) ?></td>
                    <td class="text-right"><?= number_format($d['qty'], 0, ',', '.') ?></td>
                    <td><?= esc($d['unit']) ?></td>
                    <td class="text-right">Rp <?= number_format($d['unit_price'], 0, ',', '.') ?></td>
                    <td class="text-right">Rp <?= number_format($d['amount'], 0, ',', '.') ?></td>
                </tr>
            <?php endforeach ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="6" class="text-right">Subtotal</th>
                <th class="text-right">Rp <?= number_format($invoice['subtotal'], 0, ',', '.') ?></th>
            </tr>
            <?php if ((int) ($invoice['ppn_enabled'] ?? 1) === 1) : ?>
                <tr>
                    <th colspan="6" class="text-right">PPN <?= number_format((float) ($invoice['ppn_percent'] ?? 11), 0, ',', '.') ?>%</th>
                    <th class="text-right">Rp <?= number_format($invoice['ppn'], 0, ',', '.') ?></th>
                </tr>
            <?php endif ?>
            <?php if ((int) ($invoice['pph_enabled'] ?? 1) === 1) : ?>
                <tr>
                    <th colspan="6" class="text-right">PPh 23 (<?= number_format((float) ($invoice['pph_percent'] ?? 2), 0, ',', '.') ?>%)</th>
                    <th class="text-right">Rp <?= number_format($invoice['pph23'], 0, ',', '.') ?></th>
                </tr>
            <?php endif ?>
            <?php if ((int) ($invoice['dp_enabled'] ?? 0) === 1) : ?>
                <tr>
                    <th colspan="6" class="text-right"><?= ($invoice['dp_mode'] ?? 'percent') === 'amount' ? 'DP Nominal' : 'DP ' . number_format((float) ($invoice['dp_percent'] ?? 50), 0, ',', '.') . '%' ?></th>
                    <th class="text-right">Rp <?= number_format((float) ($invoice['dp_amount'] ?? 0), 0, ',', '.') ?></th>
                </tr>
            <?php endif ?>
            <tr>
                <th colspan="6" class="text-right">Grand Total</th>
                <th class="text-right">Rp <?= number_format($invoice['grand_total'], 0, ',', '.') ?></th>
            </tr>
        </tfoot>
    </table>
</div>
<?= $this->endSection('isi') ?>
