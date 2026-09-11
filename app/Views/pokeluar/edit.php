<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Edit PO Keluar
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>
<a href="<?= site_url('poKeluar/data') ?>" class="btn btn-warning">
    <i class="fa fa-undo"></i> Kembali
</a>
<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<?php if (session('error')) : ?>
    <div class="alert alert-danger"><?= session('error') ?></div>
<?php endif ?>

<?php
$canFullEdit = (bool) ($canFullEdit ?? false);
$referenceUsages = $referenceUsages ?? [];
$jenisPo = strtolower((string) old('jenis_po', $po['jenis_po'] ?? 'material'));
$jenisPoLabels = ['produk' => 'PO Produk', 'jasa' => 'PO Jasa', 'material' => 'PO Material'];
$selectedSupplier = (string) old('idsup', $po['idsup'] ?? '');
$selectedPoAsal = (string) old('po_asal', $po['po_asal'] ?? '');
$selectedPoMasuk = (string) old('po_masuk_terkait', $po['po_masuk_terkait'] ?? '');
?>

<style>
    .po-edit-table th,
    .po-edit-table td {
        vertical-align: middle;
    }

    .po-edit-table select,
    .po-edit-table input {
        min-width: 120px;
    }

    .po-edit-item-col {
        width: 34%;
    }

    .po-edit-item-select {
        min-width: 0 !important;
        padding-right: 2rem;
    }

    .po-edit-actions .btn {
        align-items: center;
        display: inline-flex;
        height: 38px;
        justify-content: center;
        width: 38px;
    }
</style>

<?php if ($canFullEdit) : ?>
    <div class="alert alert-success">
        <i class="fa fa-check-circle"></i> PO Keluar ini belum dipakai transaksi lain, jadi No. PO, supplier, jenis, dan item masih bisa diubah.
    </div>
<?php else : ?>
    <div class="alert alert-info">
        <i class="fa fa-info-circle"></i> PO Keluar ini sudah dipakai transaksi lain, jadi No. PO, Supplier, dan Item PO dikunci. Yang bisa diubah cuma keterangan dan info cetak.
        <?php if ($referenceUsages) : ?>
            <div class="mt-2 small">
                Dipakai di:
                <?php foreach ($referenceUsages as $label => $count) : ?>
                    <span class="badge badge-light"><?= esc($label) ?>: <?= (int) $count ?></span>
                <?php endforeach ?>
            </div>
        <?php endif ?>
    </div>
<?php endif ?>

<?= form_open('/poKeluar/update/' . $po['id']) ?>

<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>No. PO Keluar</label>
            <?php if ($canFullEdit) : ?>
                <input type="text" name="no_po" class="form-control" value="<?= esc(old('no_po', $po['no_po'])) ?>" required>
            <?php else : ?>
                <input type="text" class="form-control" value="<?= esc($po['no_po']) ?>" disabled>
            <?php endif ?>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Tanggal PO</label>
            <?php if ($canFullEdit) : ?>
                <input type="date" name="tgl_po" class="form-control" value="<?= esc(old('tgl_po', $po['tgl_po'])) ?>" required>
            <?php else : ?>
                <input type="text" class="form-control" value="<?= date('d-m-Y', strtotime($po['tgl_po'])) ?>" disabled>
            <?php endif ?>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Supplier/Vendor</label>
            <?php if ($canFullEdit) : ?>
                <select name="idsup" id="idsupEdit" class="form-control" required>
                    <option value="">-- Pilih Supplier --</option>
                    <?php foreach (($suppliers ?? []) as $supplier) : ?>
                        <option value="<?= esc($supplier['supid']) ?>" <?= (string) $selectedSupplier === (string) $supplier['supid'] ? 'selected' : '' ?>><?= esc($supplier['supnama']) ?></option>
                    <?php endforeach ?>
                </select>
            <?php else : ?>
                <input type="text" class="form-control" value="<?= esc($po['supplier_nama']) ?>" disabled>
            <?php endif ?>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Jenis PO</label>
            <?php if ($canFullEdit) : ?>
                <select name="jenis_po" id="jenisPo" class="form-control" required>
                    <?php foreach ($jenisPoLabels as $value => $label) : ?>
                        <option value="<?= esc($value) ?>" <?= $jenisPo === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach ?>
                </select>
            <?php else : ?>
                <input type="text" class="form-control" value="<?= esc($jenisPoLabels[$jenisPo] ?? 'PO Material') ?>" disabled>
            <?php endif ?>
        </div>
    </div>
</div>

<?php if ($canFullEdit) : ?>
    <div class="row align-items-start">
        <div class="col-md-3">
            <div class="form-group">
                <label>Jenis Transaksi</label>
                <select name="jenis_transaksi" id="jenisTransaksi" class="form-control" required>
                    <option value="Beli" <?= old('jenis_transaksi', $po['jenis_transaksi'] ?? 'Beli') === 'Beli' ? 'selected' : '' ?>>Beli</option>
                    <option value="Titip Proses" <?= old('jenis_transaksi', $po['jenis_transaksi'] ?? '') === 'Titip Proses' ? 'selected' : '' ?>>Titip Proses</option>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>PO Asal <small class="text-muted">(opsional)</small></label>
                <select name="po_asal" class="form-control">
                    <option value="">-- Tidak ada --</option>
                    <?php foreach (($poAktifList ?? []) as $poItem) : ?>
                        <option value="<?= esc($poItem['no_po']) ?>" <?= $selectedPoAsal === (string) $poItem['no_po'] ? 'selected' : '' ?>><?= esc($poItem['no_po'] . ' - ' . $poItem['supplier_nama']) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>PO Masuk Terkait <small class="text-muted">(opsional)</small></label>
                <select name="po_masuk_terkait" class="form-control">
                    <option value="">-- Tidak ada --</option>
                    <?php foreach (($poMasukAktifList ?? []) as $poMasuk) : ?>
                        <option value="<?= esc($poMasuk['nopo']) ?>" <?= $selectedPoMasuk === (string) $poMasuk['nopo'] ? 'selected' : '' ?>><?= esc($poMasuk['nopo'] . ' - ' . ($poMasuk['pelnama'] ?? '-')) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <label class="d-block">&nbsp;</label>
            <div class="custom-control custom-checkbox mt-2">
                <input type="checkbox" name="kirim_langsung" id="kirimLangsung" class="custom-control-input" value="1" <?= old('kirim_langsung', $po['kirim_langsung'] ?? 0) ? 'checked' : '' ?>>
                <label class="custom-control-label" for="kirimLangsung">Kirim langsung ke pihak lain</label>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label>Keterangan <small class="text-muted">(opsional)</small></label>
                <textarea name="keterangan" class="form-control" rows="3"><?= esc(old('keterangan', $po['keterangan'])) ?></textarea>
            </div>
        </div>
        <div class="col-md-3" id="sumberMaterialProduksiGroup">
            <div class="form-group">
                <label>Material Produksi</label>
                <select name="sumber_material_produksi" id="sumberMaterialProduksi" class="form-control">
                    <option value="tre" <?= old('sumber_material_produksi', $po['sumber_material_produksi'] ?? 'tre') === 'tre' ? 'selected' : '' ?>>Material TRE</option>
                    <option value="vendor" <?= old('sumber_material_produksi', $po['sumber_material_produksi'] ?? 'tre') === 'vendor' ? 'selected' : '' ?>>Material dari Customer</option>
                </select>
            </div>
        </div>
    </div>
<?php elseif (strtolower((string) ($po['jenis_po'] ?? 'material')) === 'produk') : ?>
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label>Material Produksi</label>
                <select name="sumber_material_produksi" class="form-control">
                    <option value="tre" <?= old('sumber_material_produksi', $po['sumber_material_produksi'] ?? 'tre') === 'tre' ? 'selected' : '' ?>>Material TRE</option>
                    <option value="vendor" <?= old('sumber_material_produksi', $po['sumber_material_produksi'] ?? 'tre') === 'vendor' ? 'selected' : '' ?>>Material dari Customer</option>
                </select>
            </div>
        </div>
    </div>
<?php endif ?>

<?php if (!$canFullEdit) : ?>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label>Keterangan <small class="text-muted">(opsional)</small></label>
                <textarea name="keterangan" class="form-control" rows="3"><?= esc(old('keterangan', $po['keterangan'])) ?></textarea>
            </div>
        </div>
    </div>
<?php endif ?>

<?php if ($canFullEdit) : ?>
    <div class="card mb-3">
        <div class="card-header">
            <strong>Detail Item PO Keluar</strong>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label id="printSpecLabelTitle">Label Detail</label>
                        <input type="text" name="print_spec_label" id="printSpecLabelHeader" class="form-control" value="<?= esc(old('print_spec_label', $po['print_spec_label'] ?? 'Lebar Sliting')) ?>" placeholder="mis. Lebar Sliting">
                        <small class="form-text text-muted" id="printSpecLabelHelp">Dipakai sebagai label detail item pada print PO.</small>
                    </div>
                </div>
                <div class="col-md-3" id="materialColumnToggleGroup">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div class="form-check">
                            <input type="checkbox" name="material_column_enabled" id="materialColumnEnabled" class="form-check-input" value="1" <?= old('material_column_enabled', $po['material_column_enabled'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="materialColumnEnabled">Tampilkan kolom Material di print</label>
                        </div>
                        <small class="form-text text-muted">Kalau tidak dicentang, kolom MATERIAL tidak muncul di hasil print PO Produk.</small>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-sm po-edit-table" id="poEditItemsTable">
                    <thead>
                        <tr>
                            <th style="width:4%">No</th>
                            <th class="po-edit-item-col">Item</th>
                            <th style="width:18%">Info Print</th>
                            <th style="width:12%" id="editQtyHeader">Qty</th>
                            <th style="width:12%">Harga</th>
                            <th style="width:12%">Subtotal</th>
                            <th style="width:5%">#</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-right">Total</th>
                            <th id="editTotalQty" class="text-right">0</th>
                            <th></th>
                            <th id="editTotalNominal" class="text-right">Rp 0</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="text-right mt-2">
                <button type="button" class="btn btn-sm btn-info" id="addPoItem"><i class="fa fa-plus-circle"></i> Tambah Item</button>
            </div>
        </div>
    </div>
<?php endif ?>

<?php if (!$canFullEdit) : ?>
    <div class="card mb-3">
        <div class="card-header">
            <strong>Detail Item PO Keluar</strong>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label id="printSpecLabelTitle">Label Detail</label>
                        <input type="text" name="print_spec_label" id="printSpecLabelHeader" class="form-control" value="<?= esc(old('print_spec_label', $po['print_spec_label'] ?? 'Lebar Sliting')) ?>" placeholder="mis. Lebar Sliting">
                        <small class="form-text text-muted" id="printSpecLabelHelp">Dipakai sebagai label detail item pada print PO.</small>
                    </div>
                </div>
                <?php if (strtolower((string) ($po['jenis_po'] ?? '')) === 'produk') : ?>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div class="form-check">
                            <input type="checkbox" name="material_column_enabled" id="materialColumnEnabled" class="form-check-input" value="1" <?= old('material_column_enabled', $po['material_column_enabled'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="materialColumnEnabled">Tampilkan kolom Material di print</label>
                        </div>
                        <small class="form-text text-muted">Kalau tidak dicentang, kolom MATERIAL tidak muncul di hasil print PO Produk.</small>
                    </div>
                </div>
                <?php endif ?>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-sm po-edit-table">
                    <thead><tr><th style="width:4%">No</th><th class="po-edit-item-col">Item</th><th style="width:260px">Info Print</th></tr></thead>
                    <tbody>
                        <?php foreach (($details ?? []) as $i => $detail) : ?>
                            <tr>
                                <td class="text-center"><?= $i + 1 ?></td>
                                <td><?= esc($detail['nama_item']) ?></td>
                                <td><input type="text" name="detail_print_spec[<?= (int) $detail['id'] ?>]" class="form-control form-control-sm" value="<?= esc(old('detail_print_spec.' . $detail['id'], $detail['print_spec'] ?? '')) ?>" placeholder="Ukuran/Size/Material untuk print"></td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif ?>
<div class="card">
    <div class="card-header">
        <strong>Pengaturan Cetak PO</strong>
    </div>
    <div class="card-body">
        <div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>TOP <small class="text-muted">(opsional)</small></label>
            <input type="text" name="top" class="form-control" value="<?= esc(old('top', $po['top'])) ?>" placeholder="mis. 30hr setelah Invoice">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>System Payment <small class="text-muted">(opsional)</small></label>
            <input type="text" name="system_payment" class="form-control" value="<?= esc(old('system_payment', $po['system_payment'])) ?>" placeholder="mis. Transfer">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Shipping To <small class="text-muted">(opsional)</small></label>
            <input type="text" name="shipping_to" class="form-control" value="<?= esc(old('shipping_to', $po['shipping_to'])) ?>" placeholder="mis. Gudang CCIP">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Quot Number <small class="text-muted">(opsional)</small></label>
            <input type="text" name="quot_number" class="form-control" value="<?= esc(old('quot_number', $po['quot_number'])) ?>">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Approved By <small class="text-muted">(opsional)</small></label>
            <input type="text" name="approved_by" class="form-control" value="<?= esc(old('approved_by', $po['approved_by'])) ?>" placeholder="Nama yang menyetujui PO ini">
        </div>
    </div>
</div>

        <div class="row">

            <div class="col-md-3">
                <div class="form-group">
                    <label>Discount <small class="text-muted">(opsional)</small></label>
                    <div class="input-group">
                        <div class="input-group-prepend"><div class="input-group-text"><input type="checkbox" name="discount_enabled" id="discountEnabled" value="1" <?= old('discount_enabled', $po['discount_enabled'] ?? 0) ? 'checked' : '' ?>></div></div>
                        <input type="number" name="discount_amount" id="discountAmount" class="form-control" min="0" step="0.01" value="<?= esc(old('discount_amount', $po['discount_amount'] ?? 0)) ?>">
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>PPN 11%</label>
                    <div class="custom-control custom-checkbox mt-2">
                        <input type="checkbox" name="ppn_included" id="ppnIncluded" class="custom-control-input" value="1" <?= old('ppn_included', $po['ppn_included'] ?? 0) ? 'checked' : '' ?>>
                        <label class="custom-control-label" for="ppnIncluded">Harga sudah termasuk PPN</label>
                    </div>
                    <small class="form-text text-muted">Kalau tidak dicentang, PPN ditambahkan saat print.</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>PPH 23</label>
                    <div class="custom-control custom-checkbox mt-2">
                        <input type="checkbox" name="pph23_enabled" id="pph23Enabled" class="custom-control-input" value="1" <?= old('pph23_enabled', $po['pph23_enabled'] ?? 0) ? 'checked' : '' ?>>
                        <label class="custom-control-label" for="pph23Enabled">Potong PPH 23 2%</label>
                    </div>
                    <small class="form-text text-muted">Nominal PPH 23 dihitung otomatis 2% dari nilai PO setelah discount.</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Notes Cetak <small class="text-muted">(opsional)</small></label>
                    <textarea name="print_notes" class="form-control" rows="3" placeholder="Isi notes yang tampil di print PO"><?= esc(old('print_notes', $po['print_notes'] ?? '')) ?></textarea>
                </div>
            </div>
        </div>

    </div>
</div>

<button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Simpan Perubahan</button>

<?= form_close() ?>

<script>
    const canFullEdit = <?= $canFullEdit ? 'true' : 'false' ?>;
    const itemOptions = <?= json_encode($items ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const initialDetails = <?= json_encode(array_map(static function ($row) {
                            return [
                                'tipe_item' => (string) $row['tipe_item'],
                                'kode_item' => (string) $row['kode_item'],
                                'print_spec' => (string) ($row['print_spec'] ?? ''),
                                'qty_pesan' => (float) $row['qty_pesan'],
                                'harga' => (float) $row['harga'],
                            ];
                        }, $details ?? []), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;

    $(function() {
        function formatNumber(value) {
            return new Intl.NumberFormat('id-ID').format(Math.round(Number(value) || 0));
        }

        function formatRupiah(value) {
            return 'Rp ' + formatNumber(value);
        }

        function syncPrintOptionInputs() {
            $('#discountAmount').prop('disabled', !$('#discountEnabled').is(':checked'));
        }

        function getPrintSpecMeta(jenis) {
            return {
                produk: {
                    title: 'Label Material',
                    defaultLabel: 'Material',
                    help: 'Dipakai sebagai label kolom material pada detail print PO Produk.'
                },
                jasa: {
                    title: 'Label Ukuran',
                    defaultLabel: 'Lebar Sliting',
                    help: 'Dipakai sebagai subjudul kolom UKURAN pada detail print PO Jasa.'
                },
                material: {
                    title: 'Label Size',
                    defaultLabel: 'Size',
                    help: 'Dipakai sebagai label kolom size pada detail print PO Material.'
                }
            }[jenis] || {
                title: 'Label Detail',
                defaultLabel: 'Ukuran',
                help: 'Dipakai sebagai label detail item pada print PO.'
            };
        }

        function updatePrintSpecLabelByJenis(jenis) {
            const meta = getPrintSpecMeta(jenis);
            const knownDefaults = ['Material', 'Lebar Sliting', 'Size', 'Ukuran'];
            const currentLabel = $('#printSpecLabelHeader').val();
            $('#printSpecLabelTitle').text(meta.title);
            $('#printSpecLabelHelp').text(meta.help);
            $('#printSpecLabelHeader').attr('placeholder', 'mis. ' + meta.defaultLabel);
            if (!currentLabel || knownDefaults.includes(currentLabel)) {
                $('#printSpecLabelHeader').val(meta.defaultLabel);
            }
        }

        function getQtyLabel(jenis) {
            return jenis === 'material' ? 'Kg' : 'Qty';
        }

        function updateQtyHeaderByJenis(jenis) {
            $('#editQtyHeader').text(getQtyLabel(jenis));
        }

        function isTitipProses() {
            return $('#jenisTransaksi').val() === 'Titip Proses';
        }

        function getHeaderJenisPo() {
            return $('#jenisPo').val() || '<?= esc($jenisPo) ?>';
        }

        function getDetailItemType() {
            return isTitipProses() ? 'material' : getHeaderJenisPo();
        }

        function syncHeaderByJenisPo() {
            const jenis = getHeaderJenisPo();
            const detailJenis = getDetailItemType();
            $('#sumberMaterialProduksiGroup').toggle(jenis === 'produk');
            if (jenis !== 'produk') {
                $('#sumberMaterialProduksi').val('tre');
            }
            $('#materialColumnToggleGroup').toggle(jenis === 'produk');
            updatePrintSpecLabelByJenis(jenis);
            updateQtyHeaderByJenis(detailJenis);
            $('#poEditItemsTable tbody tr').each(function() {
                populateItemSelect($(this));
            });
            recalcTotals();
        }

        function resetRowsForJenisPo() {
            $('#poEditItemsTable tbody').empty();
            addRow({ tipe_item: getDetailItemType(), qty_pesan: 1, harga: 0 });
            syncHeaderByJenisPo();
        }

        function itemText(item) {
            const kode = item.label_kode || item.kode_item;
            const idsupTerpilih = $('#idsupEdit').val();
            const nama = (item.tipe_item === 'material' && item.label_per_supplier && item.label_per_supplier[idsupTerpilih])
                ? item.label_per_supplier[idsupTerpilih]
                : item.nama_item;
            return kode + ' - ' + nama + ' (' + item.satuan + ')';
        }

        function populateItemSelect(row) {
            const jenis = getDetailItemType();
            const select = row.find('.po-edit-item-select');
            const current = select.val() || row.data('selectedKey') || '';
            select.empty().append('<option value="">-- Pilih Item --</option>');
            itemOptions.filter(item => item.tipe_item === jenis).forEach(function(item) {
                const key = item.tipe_item + '|' + item.kode_item;
                select.append($('<option></option>').attr('value', key).text(itemText(item)));
            });
            if (current) {
                select.val(current);
            }
            syncRowItem(row);
        }

        function findItem(key) {
            return itemOptions.find(item => (item.tipe_item + '|' + item.kode_item) === key) || null;
        }

        function syncRowItem(row) {
            const item = findItem(row.find('.po-edit-item-select').val());
            row.find('.tipe-item-input').val(item ? item.tipe_item : '');
            row.find('.kode-item-input').val(item ? item.kode_item : '');
            row.data('selectedKey', item ? item.tipe_item + '|' + item.kode_item : '');
        }

        function addRow(detail = {}) {
            const key = detail.tipe_item && detail.kode_item ? detail.tipe_item + '|' + detail.kode_item : '';
            const row = $(`
                <tr>
                    <td class="text-center row-number"></td>
                    <td>
                        <select class="form-control form-control-sm po-edit-item-select" required></select>
                        <input type="hidden" name="tipe_item[]" class="tipe-item-input">
                        <input type="hidden" name="kode_item[]" class="kode-item-input">
                    </td>
                    <td><input type="text" name="print_spec[]" class="form-control form-control-sm" value="${String(detail.print_spec || '').replace(/"/g, '&quot;')}" placeholder="Info print"></td>
                    <td><input type="number" name="qty_pesan[]" class="form-control form-control-sm qty-input text-right" min="0.0001" step="0.0001" value="${detail.qty_pesan || 1}" required></td>
                    <td><input type="number" name="harga[]" class="form-control form-control-sm harga-input text-right" min="0" step="0.01" value="${detail.harga || 0}" required></td>
                    <td class="subtotal-cell text-right">Rp 0</td>
                    <td class="po-edit-actions text-center"><button type="button" class="btn btn-sm btn-danger remove-row"><i class="fa fa-trash-alt"></i></button></td>
                </tr>`);
            row.data('selectedKey', key);
            $('#poEditItemsTable tbody').append(row);
            populateItemSelect(row);
            renumberRows();
            recalcTotals();
        }

        function renumberRows() {
            $('#poEditItemsTable tbody tr').each(function(index) {
                $(this).find('.row-number').text(index + 1);
            });
        }

        function recalcTotals() {
            let totalQty = 0;
            let totalNominal = 0;
            $('#poEditItemsTable tbody tr').each(function() {
                const row = $(this);
                const qty = Number(row.find('.qty-input').val()) || 0;
                const harga = Number(row.find('.harga-input').val()) || 0;
                const subtotal = qty * harga;
                totalQty += qty;
                totalNominal += subtotal;
                row.find('.subtotal-cell').text(formatRupiah(subtotal));
            });
            $('#editTotalQty').text(formatNumber(totalQty));
            $('#editTotalNominal').text(formatRupiah(totalNominal));
        }

        syncPrintOptionInputs();
        updatePrintSpecLabelByJenis($('#jenisPo').val() || '<?= esc($jenisPo) ?>');
        $('#discountEnabled').on('change', syncPrintOptionInputs);

        if (canFullEdit) {
            if (initialDetails.length) {
                initialDetails.forEach(addRow);
            } else {
                addRow();
            }
            $('#addPoItem').on('click', function() { addRow({ tipe_item: getDetailItemType(), qty_pesan: 1, harga: 0 }); });
            $('#jenisPo').on('change', resetRowsForJenisPo);
            $('#jenisTransaksi').on('change', resetRowsForJenisPo);
            $('#idsupEdit').on('change', syncHeaderByJenisPo);
            $('#poEditItemsTable').on('change', '.po-edit-item-select', function() {
                syncRowItem($(this).closest('tr'));
            });
            $('#poEditItemsTable').on('input', '.qty-input, .harga-input', recalcTotals);
            $('#poEditItemsTable').on('click', '.remove-row', function() {
                if ($('#poEditItemsTable tbody tr').length <= 1) {
                    return;
                }
                $(this).closest('tr').remove();
                renumberRows();
                recalcTotals();
            });
            syncHeaderByJenisPo();
        }
    });
</script>
<?= $this->endSection('isi') ?>
