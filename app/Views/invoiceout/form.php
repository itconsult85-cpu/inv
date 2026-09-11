<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>Generate Invoice Out<?= $this->endSection('judul') ?>
<?= $this->section('subjudul') ?><a href="<?= site_url('invoiceOut/data') ?>" class="btn btn-warning"><i class="fas fa-undo"></i> Kembali</a><?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<?php if (session('error')) : ?><div class="alert alert-danger"><?= session('error') ?></div><?php endif ?>

<div class="form-group">
    <label>Pilih PO yang memiliki qty terkirim dan belum ditagihkan</label>
    <?php
    $selectedPoLabel = '';
    foreach ($candidates as $candidate) {
        if ($selectedPo === $candidate['nopo']) {
            $selectedPoLabel = $candidate['nopo'] . ' | ' . date('d-m-Y', strtotime($candidate['tglpo'])) . ' | ' . $candidate['pelnama'];
            break;
        }
    }
    ?>
    <div class="tre-inline-combobox tre-inline-combobox-solo" id="pilihPoCombobox">
        <input type="hidden" id="pilihPo" value="<?= $selectedPo ? esc(sha1($selectedPo)) : '' ?>">
        <input type="text" id="pilihPoText" class="form-control" value="<?= esc($selectedPoLabel) ?>" placeholder="-- Pilih PO --">
        <div class="tre-inline-combobox-menu" id="pilihPoComboboxMenu"></div>
    </div>
</div>

<?php if ($selectedPo && !empty($shipments)) : ?>
    <div class="card">
        <div class="card-header"><strong>List Surat Jalan berdasarkan PO yang dipilih</strong></div>
        <div class="card-body">
            <p class="text-muted">pilih satu atau beberapa surat jalan yang mau digabung jadi 1 invoice. Surat jalan yang qty-nya sudah full ditagihkan tidak muncul lagi di daftar ini.</p>
            <form method="get" action="<?= site_url('invoiceOut/create/' . sha1($selectedPo)) ?>">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th style="width: 5%;"></th>
                            <th>No. Surat Jalan</th>
                            <th>Tanggal Kirim</th>
                            <th>Produk & Qty Belum Ditagihkan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($shipments as $shipment) : ?>
                            <?php $isMigrasiShipment = strpos((string) $shipment['faktur'], 'MIGRASI-') === 0; ?>
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" name="faktur[]" value="<?= esc($shipment['faktur']) ?>"
                                        <?= in_array($shipment['faktur'], $selectedFakturs, true) ? 'checked' : '' ?>>
                                </td>
                                <td>
                                    <?= $isMigrasiShipment ? '<span class="badge badge-warning">Migrasi sebelum sistem</span>' : esc($shipment['faktur']) ?>
                                </td>
                                <td><?= $shipment['tglfaktur'] ? date('d-m-Y', strtotime($shipment['tglfaktur'])) : '-' ?></td>
                                <td>
                                    <?php foreach ($shipment['lines'] as $line) : ?>
                                        <?= esc($line['product_code']) ?> &mdash; <?= number_format($line['qty_invoice'], 0, ',', '.') ?> <?= esc($line['unit']) ?><br>
                                    <?php endforeach ?>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
                <button type="submit" class="btn btn-primary"><i class="fas fa-eye"></i> Tampilkan Item Invoice</button>
            </form>
        </div>
    </div>
<?php endif ?>

<?php if ($invoiceData) : ?>
    <div class="alert alert-warning" id="alertHargaKosong" style="display: none;"><strong>Harga produk belum lengkap.</strong> Isi manual kolom Harga pada baris yang masih Rp 0.</div>
    <div class="alert alert-info"><i class="fas fa-info-circle"></i> Harga otomatis diambil dari master produk. Jika ada harga khusus, ubah langsung pada kolom Harga sebelum invoice disimpan.</div>
    <?= form_open('/invoiceOut/save') ?>
        <input type="hidden" name="po_no" value="<?= esc($invoiceData['po']['nopo']) ?>">
        <?php foreach ($selectedFakturs as $faktur) : ?>
            <input type="hidden" name="faktur[]" value="<?= esc($faktur) ?>">
        <?php endforeach ?>
        <div class="alert alert-info">Sumber yang ditagihkan: <strong><?= esc(implode(', ', array_map(static fn($faktur) => strpos((string) $faktur, 'MIGRASI-') === 0 ? 'Migrasi sebelum sistem' : $faktur, $selectedFakturs))) ?></strong></div>
        <div class="row">
            <div class="col-md-4 form-group"><label>No. Invoice</label><input name="invoice_no" class="form-control" maxlength="100" value="<?= old('invoice_no') ?>" required></div>
            <div class="col-md-4 form-group"><label>Tanggal Invoice</label><input type="date" name="invoice_date" class="form-control" value="<?= old('invoice_date', date('Y-m-d')) ?>" required></div>
            <div class="col-md-4 form-group"><label>No. PO</label><input class="form-control" value="<?= esc($invoiceData['po']['nopo']) ?>" readonly></div>
        </div>
        <div class="row">
            <div class="col-md-6 form-group"><label>Pelanggan</label><input class="form-control" value="<?= esc($invoiceData['po']['pelnama']) ?>" readonly></div>
            <div class="col-md-6 form-group"><label>Tanggal PO</label><input class="form-control" value="<?= date('d-m-Y', strtotime($invoiceData['po']['tglpo'])) ?>" readonly></div>
        </div>
        <div class="row">
            <div class="col-md-6 form-group"><label>Nama Penandatangan</label><input name="signer_name" class="form-control" maxlength="100" value="<?= old('signer_name') ?>" placeholder="Nama yang menandatangani invoice" required></div>
            <div class="col-md-6 form-group"><label>Jabatan Penandatangan</label><input name="signer_position" class="form-control" maxlength="100" value="<?= old('signer_position') ?>" placeholder="Contoh: Direktur" required></div>
        </div>
        <div class="card">
            <div class="card-header"><strong>Pengaturan Invoice</strong></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label>PPN</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <input type="checkbox" name="ppn_enabled" id="ppnEnabled" value="1" <?= old('ppn_enabled', '1') ? 'checked' : '' ?>>
                                </div>
                            </div>
                            <input type="number" name="ppn_percent" id="ppnPercent" class="form-control" min="0" max="100" step="0.01" value="<?= old('ppn_percent', 11) ?>">
                            <div class="input-group-append"><span class="input-group-text">%</span></div>
                        </div>
                        <small class="text-muted">Centang kalau invoice memakai PPN.</small>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>PPh 23</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <input type="checkbox" name="pph_enabled" id="pphEnabled" value="1" <?= old('pph_enabled', '1') ? 'checked' : '' ?>>
                                </div>
                            </div>
                            <input type="number" name="pph_percent" id="pphPercent" class="form-control" min="0" max="100" step="0.01" value="<?= old('pph_percent', 2) ?>">
                            <div class="input-group-append"><span class="input-group-text">%</span></div>
                        </div>
                        <small class="text-muted">Centang kalau invoice memakai PPh 23.</small>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>DP</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <input type="checkbox" name="dp_enabled" id="dpEnabled" value="1" <?= old('dp_enabled', '1') ? 'checked' : '' ?>>
                                </div>
                            </div>
                            <input type="number" name="dp_percent" id="dpPercent" class="form-control" min="0" max="100" step="0.01" value="<?= old('dp_percent', 50) ?>">
                            <div class="input-group-append"><span class="input-group-text">%</span></div>
                        </div>
                        <small class="text-muted">Centang kalau invoice memakai DP.</small>
                    </div>
                    <div class="col-md-3 form-group mb-0">
                        <label>Pemilik Rekening</label>
                        <input name="bank_owner" class="form-control" maxlength="150" value="<?= old('bank_owner', 'TRISENTOSA RAYA ESOLUSI') ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>Nama Bank</label>
                        <input name="bank_name" class="form-control" maxlength="150" value="<?= old('bank_name', 'Maybank Kantor Cabang Bukit Indah') ?>">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>No Rekening</label>
                        <input name="bank_account" class="form-control" maxlength="100" value="<?= old('bank_account', '2-784-001326') ?>">
                    </div>
                    <div class="col-md-4 form-group mb-0">
                        <label>NPWP</label>
                        <input name="bank_npwp" class="form-control" maxlength="100" value="<?= old('bank_npwp', '076.249.385.6-014.000') ?>">
                    </div>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered" id="tabelItemInvoice">
                <thead><tr><th>No</th><th>No. Surat Jalan</th><th>Kode Produk</th><th>Nama Produk</th><th>Qty Belum Ditagihkan</th><th>UoM</th><th style="width: 15%;">Harga</th><th>Amount</th></tr></thead>
                <tbody>
                    <?php foreach ($invoiceData['lines'] as $i => $line) : $rowKey = $line['source_no'] . '||' . $line['product_code']; ?>
                        <?php $isMigrasiLine = strpos((string) $line['source_no'], 'MIGRASI-') === 0; ?>
                        <tr>
                            <td><?= $i + 1 ?></td><td><?= $isMigrasiLine ? '<span class="badge badge-warning">Migrasi sebelum sistem</span>' : esc($line['source_no']) ?></td><td><?= esc($line['product_code']) ?></td><td><?= esc($line['product_name']) ?></td>
                            <td class="text-right" data-qty="<?= esc($line['qty_invoice']) ?>"><?= number_format($line['qty_invoice'], 0, ',', '.') ?></td><td><?= esc($line['unit']) ?></td>
                            <td class="text-right">
                                <input type="number" name="harga[<?= esc($rowKey) ?>]" class="form-control form-control-sm input-harga text-right"
                                    value="<?= esc($line['unit_price']) ?>" min="0" step="1" required>
                            </td>
                            <td class="text-right cell-amount">Rp <?= number_format($line['amount'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
                <tfoot>
                    <tr><th colspan="7" class="text-right">Subtotal</th><th class="text-right" id="cellSubtotal">Rp 0</th></tr>
                    <tr id="rowPpn"><th colspan="7" class="text-right">PPN <span id="cellPpnLabel">11%</span></th><th class="text-right" id="cellPpn">Rp 0</th></tr>
                    <tr id="rowPph"><th colspan="7" class="text-right">PPh 23 (<span id="cellPphLabel">2%</span>)</th><th class="text-right" id="cellPph">Rp 0</th></tr>
                    <tr id="rowDp"><th colspan="7" class="text-right">DP <span id="cellDpLabel">50%</span></th><th class="text-right" id="cellDp">Rp 0</th></tr>
                    <tr><th colspan="7" class="text-right">Grand Total</th><th class="text-right" id="cellGrandTotal">Rp 0</th></tr>
                </tfoot>
            </table>
        </div>
        <button type="submit" class="btn btn-success" id="btnSimpanInvoice"><i class="fas fa-save"></i> Simpan Invoice</button>
    <?= form_close() ?>
<?php elseif ($selectedPo && empty($shipments)) : ?>
    <div class="alert alert-warning">PO ini belum memiliki qty terkirim yang dapat ditagihkan.</div>
<?php elseif ($selectedPo && $selectedFakturs) : ?>
    <div class="alert alert-warning">Surat jalan yang dipilih tidak memiliki qty tersisa untuk ditagihkan.</div>
<?php endif ?>

<script>
$(function() {
    window.treInitInlineCombobox({
        box: '#pilihPoCombobox',
        input: '#pilihPoText',
        hidden: '#pilihPo',
        menu: '#pilihPoComboboxMenu',
        options: <?= json_encode(array_map(static function ($candidate) {
            return [
                'id' => sha1($candidate['nopo']),
                'text' => $candidate['nopo'] . ' | ' . date('d-m-Y', strtotime($candidate['tglpo'])) . ' | ' . $candidate['pelnama'],
                'value' => $candidate['nopo'],
            ];
        }, $candidates), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
        onSelect: function(option) {
            if (option && option.id) {
                const nextUrl = '<?= site_url('invoiceOut/create') ?>/' + option.id;
                window.location.href = (typeof window.treApplyManualPreview === 'function') ? window.treApplyManualPreview(nextUrl) : nextUrl;
            }
        }
    });
});

function formatRupiah(angka) {
    return 'Rp ' + Number(angka).toLocaleString('id-ID', { maximumFractionDigits: 0 });
}

function hitungUlangInvoice() {
    let subtotal = 0;
    let adaHargaKosong = false;

    $('#tabelItemInvoice tbody tr').each(function() {
        const $row = $(this);
        const qty = parseFloat($row.find('td[data-qty]').data('qty')) || 0;
        const harga = parseFloat($row.find('.input-harga').val()) || 0;
        const amount = qty * harga;

        $row.find('.cell-amount').text(formatRupiah(amount));
        subtotal += amount;

        if (harga <= 0) {
            adaHargaKosong = true;
        }
    });

    const ppnEnabled = $('#ppnEnabled').is(':checked');
    const ppnPercent = parseFloat($('#ppnPercent').val()) || 0;
    const ppn = ppnEnabled ? subtotal * (ppnPercent / 100) : 0;

    const pphEnabled = $('#pphEnabled').is(':checked');
    const pphPercent = parseFloat($('#pphPercent').val()) || 0;
    const pph = pphEnabled ? subtotal * (pphPercent / 100) : 0;

    const dpEnabled = $('#dpEnabled').is(':checked');
    const dpPercent = parseFloat($('#dpPercent').val()) || 0;
    const dpAmount = dpEnabled ? (subtotal + ppn) * (dpPercent / 100) : 0;
    const grandTotal = Math.max((subtotal + ppn) - pph - dpAmount, 0);

    $('#cellSubtotal').text(formatRupiah(subtotal));
    $('#cellPpnLabel').text((ppnPercent || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 }) + '%');
    $('#cellPpn').text(formatRupiah(ppn));
    $('#rowPpn').toggle(ppnEnabled);
    $('#cellPphLabel').text((pphPercent || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 }) + '%');
    $('#cellPph').text(formatRupiah(pph));
    $('#rowPph').toggle(pphEnabled);
    $('#cellDpLabel').text((dpPercent || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 }) + '%');
    $('#cellDp').text(formatRupiah(dpAmount));
    $('#rowDp').toggle(dpEnabled);
    $('#cellGrandTotal').text(formatRupiah(grandTotal));

    $('#alertHargaKosong').toggle(adaHargaKosong);
    $('#btnSimpanInvoice').prop('disabled', adaHargaKosong);
}

$(document).on('input', '.input-harga, #ppnPercent, #pphPercent, #dpPercent', hitungUlangInvoice);
$(document).on('change', '#ppnEnabled, #pphEnabled, #dpEnabled', hitungUlangInvoice);
hitungUlangInvoice();
</script>
<?= $this->endSection('isi') ?>
