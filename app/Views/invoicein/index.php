<?= $this->extend('main/layout') ?>
<?= $this->section('judul') ?>Invoice In<?= $this->endSection('judul') ?>
<?= $this->section('subjudul') ?>
<a href="<?= site_url('invoiceHub') ?>" class="btn btn-warning"><i class="fas fa-undo"></i> Kembali</a>
<a href="<?= site_url('invoiceIn/create') ?>" class="btn btn-primary">
    <i class="fas fa-file-import"></i> Catat Invoice In
</a>
<?= $this->endSection('subjudul') ?>
<?= $this->section('isi') ?>
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<script src="<?= base_url() ?>/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>

<?php if (session('message')) : ?>
    <div class="alert alert-success"><?= esc(session('message')) ?></div>
<?php endif ?>
<?php if (session('error')) : ?>
    <div class="alert alert-danger"><?= esc(session('error')) ?></div>
<?php endif ?>

<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover" id="invoiceInTable">
        <thead>
            <tr>
                <th>No</th>
                <th>No. Invoice Supplier</th>
                <th>Tanggal</th>
                <th>Supplier</th>
                <th>Sumber</th>
                <th>Grand Total</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($invoices as $invoice) : ?>
                <tr>
                    <td class="row-number"></td>
                    <td><?= esc($invoice['invoice_no']) ?></td>
                    <td data-order="<?= esc($invoice['invoice_date']) ?>">
                        <?= date('d-m-Y', strtotime($invoice['invoice_date'])) ?>
                    </td>
                    <td><?= esc($invoice['supplier_name']) ?></td>
                    <?php $sourceNo = (string) ($invoice['source_no'] ?? ''); $sourceParts = explode('||', $sourceNo, 2); $sourceLabel = ($invoice['source_type'] ?? '') === 'po_keluar' ? 'PO Keluar' : ucfirst((string) ($invoice['source_type'] ?? '')) . ' Masuk'; ?>
                    <td><?= esc($sourceLabel) ?> - <?= esc($sourceParts[0]) ?></td>
                    <td class="text-right">Rp <?= number_format($invoice['grand_total'], 0, ',', '.') ?></td>
                    <?php
                        $badgeStatus = [
                            'AKTIF' => 'success',
                            'Lunas' => 'success',
                            'DIBATALKAN' => 'secondary',
                        ][$invoice['status']] ?? 'secondary';
                    ?>
                    <td><span class="badge badge-<?= $badgeStatus ?>"><?= esc($invoice['status']) ?></span></td>
                    <td class="text-nowrap">
                        <a class="btn btn-info btn-sm" href="<?= site_url('invoiceIn/detail/' . $invoice['id']) ?>"><i class="fas fa-eye"></i></a>
                        <?php if ($invoice['status'] !== 'DIBATALKAN') : ?>
                            <form method="post" action="<?= site_url('invoiceIn/cancel/' . $invoice['id']) ?>" class="d-inline" data-bootstrap-confirm="Batalkan pencatatan invoice ini?">
                                <?= csrf_field() ?>
                                <button class="btn btn-danger btn-sm" title="Batalkan"><i class="fas fa-ban"></i></button>
                            </form>
                        <?php else : ?>
                            <form method="post" action="<?= site_url('invoiceIn/hapus/' . $invoice['id']) ?>" class="d-inline" data-bootstrap-confirm="Hapus invoice ini secara permanen? Data yang sudah dihapus tidak bisa dikembalikan.">
                                <?= csrf_field() ?>
                                <button class="btn btn-danger btn-sm" title="Hapus"><i class="fas fa-trash-alt"></i></button>
                            </form>
                        <?php endif ?>
                    </td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</div>
<script>
    $(function() {
        $('#invoiceInTable').DataTable({
            pageLength: 10,
            stateSave: false,
            order: [[2, 'desc']],
            drawCallback: function() {
                const api = this.api();
                const pageStart = api.page.info().start;
                api.rows({ page: 'current' }).nodes().each(function(rowNode, pageRowIndex) {
                    $(rowNode).find('.row-number').text(pageStart + pageRowIndex + 1);
                });
            }
        });
    });
</script>
<?= $this->endSection('isi') ?>
