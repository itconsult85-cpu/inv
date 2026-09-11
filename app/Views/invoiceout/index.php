<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>Invoice Out<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>
<a href="<?= site_url('invoiceHub') ?>" class="btn btn-warning"><i class="fas fa-undo"></i> Kembali</a>
<a href="<?= site_url('invoiceOut/create') ?>" class="btn btn-primary">
    <i class="fas fa-file-invoice-dollar"></i> Generate Invoice Out
</a>
<a href="<?= site_url('invoiceOut/pembayaran') ?>" class="btn btn-success">
    <i class="fas fa-money-check-alt"></i> Catat Pembayaran
</a>
<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<script src="<?= base_url() ?>/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<?php if (session('message')) : ?><div class="alert alert-success"><?= esc(session('message')) ?></div><?php endif ?>
<?php if (session('error')) : ?><div class="alert alert-danger"><?= esc(session('error')) ?></div><?php endif ?>

<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover" id="invoiceOutTable">
        <thead>
            <tr>
                <th>No</th><th>No. Invoice</th><th>Tanggal</th><th>No. PO</th>
                <th>Pelanggan</th><th>Grand Total</th><th>Sudah Bayar</th><th>Sisa</th><th>Status</th><th>Status Bayar</th><th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($invoices as $i => $invoice) : ?>
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
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= esc($invoice['invoice_no']) ?></td>
                    <td><?= date('d-m-Y', strtotime($invoice['invoice_date'])) ?></td>
                    <td><?= esc($invoice['po_no']) ?></td>
                    <td><?= esc($invoice['customer_name']) ?></td>
                    <td class="text-right">Rp <?= number_format($invoice['grand_total'], 0, ',', '.') ?></td>
                    <td class="text-right">Rp <?= number_format($paidTotal, 0, ',', '.') ?></td>
                    <td class="text-right">Rp <?= number_format($remaining, 0, ',', '.') ?></td>
                    <td><span class="badge badge-<?= $badgeStatus ?>"><?= esc($statusTampil) ?></span></td>
                    <td><span class="badge badge-<?= $badgeBayar ?>"><?= esc($statusBayar) ?></span></td>
                    <td class="text-nowrap">
                        <a href="<?= site_url('invoiceOut/detail/' . $invoice['id']) ?>" class="btn btn-info btn-sm" title="Lihat"><i class="fas fa-eye"></i></a>
                        <div class="btn-group d-inline-block">
                            <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown" title="Extract File">
                                <i class="fas fa-file-export"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="<?= site_url('invoiceOut/cetak/' . $invoice['id']) ?>" target="_blank"><i class="fas fa-print mr-2"></i>Print</a>
                                <a class="dropdown-item" href="<?= site_url('invoiceOut/cetakExcel/' . $invoice['id']) ?>"><i class="fas fa-file-excel mr-2"></i>Excel</a>
                            </div>
                        </div>
                        <?php if ($invoice['status'] === 'AKTIF' && $statusBayar !== 'Lunas') : ?>
                            <form action="<?= site_url('invoiceOut/tandaiLunas/' . $invoice['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Tandai invoice ini sudah Lunas?');">
                                <?= csrf_field() ?><button class="btn btn-success btn-sm" title="Tandai Lunas"><i class="fas fa-money-check-alt"></i></button>
                            </form>
                        <?php endif ?>
                        <?php if ($invoice['status'] === 'AKTIF') : ?>
                            <form action="<?= site_url('invoiceOut/cancel/' . $invoice['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Batalkan invoice ini? Qty-nya akan dapat ditagihkan kembali.');">
                                <?= csrf_field() ?><button class="btn btn-danger btn-sm" title="Batalkan"><i class="fas fa-ban"></i></button>
                            </form>
                        <?php else : ?>
                            <form action="<?= site_url('invoiceOut/hapus/' . $invoice['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Hapus permanen invoice yang sudah dibatalkan ini?');">
                                <?= csrf_field() ?><button class="btn btn-danger btn-sm" title="Hapus"><i class="fas fa-trash"></i></button>
                            </form>
                        <?php endif ?>
                    </td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</div>
<script>
$(function(){ $('#invoiceOutTable').DataTable({ pageLength: 10, stateSave: true, stateDuration: -1, order: [[2, 'desc']] }); });
</script>
<?= $this->endSection('isi') ?>
