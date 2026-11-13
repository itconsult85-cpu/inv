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
                <div class="col-md-5 form-group">
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
                <div class="col-md-2 form-group">
                    <label for="tanggalAwalPembayaran">Tanggal awal</label>
                    <input type="date" id="tanggalAwalPembayaran" name="tanggal_awal" class="form-control" value="<?= esc($tanggalAwal ?? '') ?>">
                </div>
                <div class="col-md-2 form-group">
                    <label for="tanggalAkhirPembayaran">Tanggal akhir</label>
                    <input type="date" id="tanggalAkhirPembayaran" name="tanggal_akhir" class="form-control" value="<?= esc($tanggalAkhir ?? '') ?>">
                </div>
                <div class="col-md-3 form-group">
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
                            <input type="text" name="amount_display" id="amountDisplay" class="form-control text-right" inputmode="decimal" autocomplete="off" value="<?= esc(old('amount', '0')) ?>" required>
                            <input type="hidden" name="amount" id="amount" value="<?= esc(old('amount', '0')) ?>">
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
                                <th style="width: 4%;" class="text-center">
                                    <input type="checkbox" id="selectAllInvoice" title="Pilih semua invoice">
                                </th>
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
                                        <input type="checkbox" class="check-invoice invoice-checkbox">
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
                                            step="0.01"
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
    const number = Math.round(Number(value || 0));
    return 'Rp ' + number.toLocaleString('id-ID');
}

function syncRowState($row, checked) {
    const remaining = parseFloat($row.data('remaining')) || 0;
    const $input = $row.find('.input-alokasi');
    $input.prop('disabled', !checked);
    if (checked && (parseFloat($input.val()) || 0) <= 0) {
        $input.val(Number(remaining.toFixed(2)));
    }
    if (!checked) {
        $input.val('0');
    }
}

function hitungTotalAlokasi() {
    let total = 0;
    $('#tablePembayaran .input-alokasi:enabled').each(function() {
        total += parseFloat(this.value) || 0;
    });

    // Jumlahkan nilai asli lebih dahulu, lalu bulatkan total satu kali.
    // Pembulatan per invoice dapat menimbulkan selisih beberapa rupiah.
    total = Math.round(total);
    const amount = Math.round(parseFloat($('#amount').val()) || 0);
    const diff = amount - total;
    $('#totalAlokasi').text(rupiah(total));
    $('#selisihAlokasi').text(rupiah(diff));
    $('#btnSimpanPembayaran').prop('disabled', total <= 0 || diff !== 0);
}

$('.check-invoice').on('change', function() {
    syncRowState($(this).closest('tr'), this.checked);
    syncSelectAllInvoice();
    hitungTotalAlokasi();
});

function syncSelectAllInvoice() {
    const $checkboxes = $('.invoice-checkbox');
    const total = $checkboxes.length;
    const checked = $checkboxes.filter(':checked').length;
    $('#selectAllInvoice').prop('checked', total > 0 && checked === total);
    $('#selectAllInvoice').prop('indeterminate', checked > 0 && checked < total);
}

$('#selectAllInvoice').on('change', function() {
    const checked = this.checked;
    $('.invoice-checkbox').each(function() {
        $(this).prop('checked', checked);
        syncRowState($(this).closest('tr'), checked);
    });
    syncSelectAllInvoice();
    hitungTotalAlokasi();
});

$(document).on('input', '#amount, .input-alokasi', hitungTotalAlokasi);

function parseNominalIndonesia(value) {
    value = String(value || '').replace(/[^0-9,.-]/g, '').trim();
    if (value.indexOf(',') !== -1) {
        value = value.replace(/\./g, '').replace(',', '.');
    } else {
        // Titik pada input tampilan adalah pemisah ribuan, bukan desimal.
        value = value.replace(/\./g, '');
    }
    return parseFloat(value) || 0;
}

function formatNominalIndonesia(value) {
    const number = Math.round(parseNominalIndonesia(value));
    return number.toLocaleString('id-ID');
}

$('#amountDisplay').on('input', function() {
    const cursor = this.selectionStart || 0;
    const digitsBeforeCursor = (this.value.slice(0, cursor).match(/\d/g) || []).length;
    const digits = this.value.replace(/\D/g, '');
    const number = digits ? parseInt(digits, 10) : 0;
    const formatted = digits ? number.toLocaleString('id-ID') : '';

    $('#amount').val(number);
    this.value = formatted;

    // Pertahankan posisi kursor berdasarkan jumlah digit yang sudah diketik.
    let newCursor = 0;
    let digitCount = 0;
    while (newCursor < formatted.length && digitCount < digitsBeforeCursor) {
        if (/\d/.test(formatted.charAt(newCursor))) {
            digitCount++;
        }
        newCursor++;
    }
    this.setSelectionRange(newCursor, newCursor);
    hitungTotalAlokasi();
});

$('#amountDisplay').on('blur', function() {
    const value = parseNominalIndonesia(this.value);
    $('#amount').val(Math.round(value));
    this.value = value ? formatNominalIndonesia(value) : '0';
    hitungTotalAlokasi();
});

$('#btnAutoAlokasi').on('click', function() {
    let sisaNominal = parseFloat($('#amount').val()) || 0;

    $('#tablePembayaran tbody tr').each(function() {
        const $row = $(this);
        const remaining = parseFloat($row.data('remaining')) || 0;
        const alokasi = Math.min(remaining, Math.max(sisaNominal, 0));
        const checked = alokasi > 0;

        $row.find('.check-invoice').prop('checked', checked);
        $row.find('.input-alokasi').prop('disabled', !checked).val(Number(alokasi.toFixed(2)));
        sisaNominal -= alokasi;
    });

    hitungTotalAlokasi();
});

$('#formPembayaran').on('submit', function(event) {
    const amount = Math.round(parseNominalIndonesia($('#amountDisplay').val()));
    $('#amount').val(amount);
    hitungTotalAlokasi();
    if ($('#btnSimpanPembayaran').prop('disabled')) {
        event.preventDefault();
        showBootstrapModal('Cek Pembayaran', 'Total alokasi harus sama dengan nominal pembayaran.', 'warning');
    }
});

hitungTotalAlokasi();
syncSelectAllInvoice();
$('#amountDisplay').val(formatNominalIndonesia($('#amount').val()));
</script>
<?= $this->endSection('isi') ?>
