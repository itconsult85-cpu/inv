<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>Pembayaran Invoice Out<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>
<a href="<?= site_url('invoiceOut/data') ?>" class="btn btn-warning"><i class="fas fa-undo"></i> Kembali</a>
<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<?php if (session('error')) : ?><div class="alert alert-danger"><?= session('error') ?></div><?php endif ?>

<div class="card">
    <div class="card-header"><strong>Pilih Pelanggan</strong></div>
    <div class="card-body">
        <form method="get" action="<?= site_url('invoiceOut/pembayaran') ?>">
            <div class="row align-items-end">
                <div class="col-md-8 form-group">
                    <label>Pelanggan dengan invoice belum lunas</label>
                    <select name="customer_id" class="form-control select2bs4" required>
                        <option value="">-- Pilih Pelanggan --</option>
                        <?php foreach ($customers as $customer) : ?>
                            <option value="<?= esc($customer['customer_id']) ?>" <?= (string) $selectedCustomerId === (string) $customer['customer_id'] ? 'selected' : '' ?>>
                                <?= esc($customer['customer_name']) ?> | <?= (int) $customer['total_invoice'] ?> invoice | Sisa Rp <?= number_format((float) $customer['total_sisa'], 0, ',', '.') ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                </div>
                <div class="col-md-4 form-group">
                    <button class="btn btn-primary btn-block"><i class="fas fa-search"></i> Tampilkan Invoice</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if ($selectedCustomerId !== null && $selectedCustomerId !== '') : ?>
    <?php if (empty($invoices)) : ?>
        <div class="alert alert-info">Tidak ada invoice belum lunas untuk pelanggan ini.</div>
    <?php else : ?>
        <?= form_open('invoiceOut/simpanPembayaran', ['id' => 'formPembayaran']) ?>
            <input type="hidden" name="customer_id" value="<?= esc($selectedCustomerId) ?>">
            <div class="card">
                <div class="card-header"><strong>Data Pembayaran</strong></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>No. Pembayaran / Bukti</label>
                            <input name="payment_no" class="form-control" maxlength="100" value="<?= old('payment_no') ?>" placeholder="Kosongkan untuk auto">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Tanggal Bayar</label>
                            <input type="date" name="payment_date" class="form-control" value="<?= old('payment_date', date('Y-m-d')) ?>" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Nominal Pembayaran</label>
                            <input type="number" name="amount" id="amount" class="form-control text-right" min="0" step="1" value="<?= old('amount', 0) ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Keterangan</label>
                        <textarea name="note" class="form-control" rows="2" placeholder="Catatan pembayaran, rekening, atau info bukti transfer"><?= old('note') ?></textarea>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Invoice yang Dibayar</strong>
                    <button type="button" class="btn btn-sm btn-outline-primary ml-auto" id="btnAutoAlokasi">
                        <i class="fas fa-calculator"></i> Auto Alokasi dari Nominal
                    </button>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-sm" id="tablePembayaran">
                        <thead>
                            <tr>
                                <th style="width: 4%;"></th>
                                <th>No. Invoice</th>
                                <th>Tanggal</th>
                                <th>No. PO</th>
                                <th>Grand Total</th>
                                <th>Sudah Dibayar</th>
                                <th>Sisa</th>
                                <th style="width: 16%;">Alokasi Bayar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invoices as $invoice) : ?>
                                <?php $remaining = (float) $invoice['remaining_total']; ?>
                                <tr data-remaining="<?= esc($remaining) ?>">
                                    <td class="text-center">
                                        <input type="checkbox" class="check-invoice">
                                    </td>
                                    <td><?= esc($invoice['invoice_no']) ?></td>
                                    <td><?= date('d-m-Y', strtotime($invoice['invoice_date'])) ?></td>
                                    <td><?= esc($invoice['po_no']) ?></td>
                                    <td class="text-right">Rp <?= number_format((float) $invoice['grand_total'], 0, ',', '.') ?></td>
                                    <td class="text-right">Rp <?= number_format((float) ($invoice['paid_total'] ?? 0), 0, ',', '.') ?></td>
                                    <td class="text-right">Rp <?= number_format($remaining, 0, ',', '.') ?></td>
                                    <td>
                                        <input
                                            type="number"
                                            name="alokasi[<?= esc($invoice['id']) ?>]"
                                            class="form-control form-control-sm text-right input-alokasi"
                                            min="0"
                                            step="1"
                                            max="<?= esc($remaining) ?>"
                                            value="0"
                                            disabled>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="7" class="text-right">Total Alokasi</th>
                                <th class="text-right" id="totalAlokasi">Rp 0</th>
                            </tr>
                            <tr>
                                <th colspan="7" class="text-right">Selisih Nominal - Alokasi</th>
                                <th class="text-right" id="selisihAlokasi">Rp 0</th>
                            </tr>
                        </tfoot>
                    </table>
                    <button class="btn btn-success" id="btnSimpanPembayaran"><i class="fas fa-save"></i> Simpan Pembayaran</button>
                </div>
            </div>
        <?= form_close() ?>
    <?php endif ?>
<?php endif ?>

<script>
function rupiah(value) {
    return 'Rp ' + Math.round(Number(value || 0)).toLocaleString('id-ID');
}

function syncRowState($row, checked) {
    const remaining = parseFloat($row.data('remaining')) || 0;
    const $input = $row.find('.input-alokasi');
    $input.prop('disabled', !checked);
    if (checked && (parseFloat($input.val()) || 0) <= 0) {
        $input.val(Math.round(remaining));
    }
    if (!checked) {
        $input.val(0);
    }
}

function hitungTotalAlokasi() {
    let total = 0;
    $('#tablePembayaran .input-alokasi:enabled').each(function() {
        total += parseFloat(this.value) || 0;
    });

    const amount = parseFloat($('#amount').val()) || 0;
    const diff = amount - total;
    $('#totalAlokasi').text(rupiah(total));
    $('#selisihAlokasi').text(rupiah(diff));
    $('#btnSimpanPembayaran').prop('disabled', total <= 0 || Math.abs(diff) > 0.01);
}

$('.check-invoice').on('change', function() {
    syncRowState($(this).closest('tr'), this.checked);
    hitungTotalAlokasi();
});

$(document).on('input', '#amount, .input-alokasi', hitungTotalAlokasi);

$('#btnAutoAlokasi').on('click', function() {
    let sisaNominal = parseFloat($('#amount').val()) || 0;

    $('#tablePembayaran tbody tr').each(function() {
        const $row = $(this);
        const remaining = parseFloat($row.data('remaining')) || 0;
        const alokasi = Math.min(remaining, Math.max(sisaNominal, 0));
        const checked = alokasi > 0;

        $row.find('.check-invoice').prop('checked', checked);
        $row.find('.input-alokasi').prop('disabled', !checked).val(Math.round(alokasi));
        sisaNominal -= alokasi;
    });

    hitungTotalAlokasi();
});

$('#formPembayaran').on('submit', function(event) {
    hitungTotalAlokasi();
    if ($('#btnSimpanPembayaran').prop('disabled')) {
        event.preventDefault();
        Swal.fire('Cek Pembayaran', 'Total alokasi harus sama dengan nominal pembayaran.', 'warning');
    }
});

hitungTotalAlokasi();
</script>
<?= $this->endSection('isi') ?>
