<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Input PO Keluar
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

<style>
    .po-keluar-helper {
        color: #6b7280;
        display: none;
        line-height: 1.6;
        margin-top: .35rem;
        padding: 0;
    }

    .po-helper-toggle {
        align-items: center;
        background: transparent;
        border: 0;
        color: #16869a;
        display: inline-flex;
        font-size: .86rem;
        font-weight: 600;
        gap: .35rem;
        margin-top: .45rem;
        opacity: .72;
        padding: 0;
    }

    .po-helper-toggle:hover,
    .po-helper-toggle:focus {
        opacity: 1;
    }

    .po-helper-toggle:focus {
        outline: 0;
        text-decoration: underline;
    }

    .po-helper-toggle i {
        font-size: .76rem;
        transition: transform .18s ease;
    }

    .po-helper-toggle.is-open i {
        transform: rotate(180deg);
    }

    .po-helper-spacer {
        height: 1.62rem;
        margin-top: .45rem;
    }
    .po-keluar-link-row .tre-inline-combobox {
        margin-bottom: 0 !important;
    }

    .po-keluar-link-row select.form-control {
        margin-bottom: 0;
    }

    .po-keluar-direct-check.custom-control {
        min-height: calc(2.25rem + 2px);
        padding-left: 1.5rem;
        padding-top: .35rem;
    }

    .po-keluar-direct-check .custom-control-label {
        font-weight: 700;
        line-height: 1.45;
        margin-bottom: 0;
    }
</style>

<?php
$oldSupplierId = (string) old('idsup', '');
$oldSupplierName = '';
foreach (($suppliers ?? []) as $supplier) {
    if ((string) $supplier['supid'] === $oldSupplierId) {
        $oldSupplierName = (string) $supplier['supnama'];
        break;
    }
}

$oldPoAsal = (string) old('po_asal', '');
$oldPoAsalLabel = '';
foreach (($poAktifList ?? []) as $poItem) {
    if ((string) $poItem['no_po'] === $oldPoAsal) {
        $oldPoAsalLabel = (string) $poItem['no_po'] . ' - ' . (string) $poItem['supplier_nama'];
        break;
    }
}

$oldPoMasuk = (string) old('po_masuk_terkait', '');
$oldPoMasukLabel = '';
foreach (($poMasukAktifList ?? []) as $poMasukItem) {
    if ((string) $poMasukItem['nopo'] === $oldPoMasuk) {
        $oldPoMasukLabel = (string) $poMasukItem['nopo'] . ' - ' . (string) ($poMasukItem['pelnama'] ?? '-');
        break;
    }
}
$oldJenisPo = strtolower((string) old('jenis_po', 'material'));
$labelJenisPo = [
    'produk' => 'PO Produk',
    'jasa' => 'PO Jasa',
    'material' => 'PO Material',
    'habis_pakai' => 'PO Barang Habis Pakai',
][$oldJenisPo] ?? 'PO Material';
?>

<?= form_open('/poKeluar/simpan', ['id' => 'formPoKeluar']) ?>
<div class="card mb-3">
    <div class="card-header">
        <strong>Data PO Keluar</strong>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label>No. PO Keluar</label>
                    <input type="text" name="no_po" class="form-control" value="<?= old('no_po') ?>" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Tanggal PO</label>
                    <input type="date" name="tgl_po" class="form-control" value="<?= old('tgl_po', date('Y-m-d')) ?>" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="supplierInput">Supplier/Vendor</label>
                    <div class="input-group mb-3 tre-inline-combobox tre-inline-combobox-solo" id="supplierCombobox">
                        <input type="text" id="supplierInput" class="form-control" value="<?= esc($oldSupplierName) ?>" placeholder="-- Pilih Supplier --" autocomplete="off" required>
                        <input type="hidden" name="idsup" id="idsup" value="<?= esc($oldSupplierId) ?>">
                        <div class="tre-inline-combobox-menu" id="supplierComboboxMenu"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Jenis PO</label>
                    <select name="jenis_po" id="jenisPo" class="form-control" required>
                        <option value="produk" <?= $oldJenisPo === 'produk' ? 'selected' : '' ?>>PO Produk</option>
                        <option value="jasa" <?= $oldJenisPo === 'jasa' ? 'selected' : '' ?>>PO Jasa</option>
                        <option value="material" <?= $oldJenisPo === 'material' ? 'selected' : '' ?>>PO Material</option>
                        <option value="habis_pakai" <?= $oldJenisPo === 'habis_pakai' ? 'selected' : '' ?>>PO Barang Habis Pakai</option>
                    </select>
                    <small class="text-muted po-keluar-helper">Menentukan isi detail item di bawah: produk jadi, jasa/vendor, atau material.</small>
                </div>
            </div>
        </div>

        <div class="row align-items-start po-keluar-link-row">
            <div class="col-md-3">
                <div class="form-group">
                    <label>Jenis Transaksi</label>
                    <select name="jenis_transaksi" id="jenisTransaksi" class="form-control" required>
                        <option value="Beli" <?= old('jenis_transaksi', 'Beli') == 'Beli' ? 'selected' : '' ?>>Beli</option>
                        <option value="Titip Proses" <?= old('jenis_transaksi') == 'Titip Proses' ? 'selected' : '' ?>>Titip Proses</option>
                    </select>
                    <small class="text-muted po-keluar-helper">"Titip Proses" untuk barang milik TRE yang cuma dititip diolah/dibentuk vendor, bukan pembelian material baru.</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="poAsalInput">PO Asal <small class="text-muted">(opsional)</small></label>
                    <div class="input-group tre-inline-combobox tre-inline-combobox-solo" id="poAsalCombobox">
                        <input type="text" id="poAsalInput" class="form-control" value="<?= esc($oldPoAsalLabel) ?>" placeholder="-- Tidak ada --" autocomplete="off">
                        <input type="hidden" name="po_asal" id="po_asal" value="<?= esc($oldPoAsal) ?>">
                        <div class="tre-inline-combobox-menu" id="poAsalComboboxMenu"></div>
                    </div>
                    <small class="text-muted po-keluar-helper">Isi kalau PO ini lanjutan dari PO Keluar lain, mis. bahan dari Vendor A dikirim ke Vendor B ini untuk diproses.</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="poMasukInput">PO Masuk Terkait <small class="text-muted">(opsional)</small></label>
                    <div class="input-group tre-inline-combobox tre-inline-combobox-solo" id="poMasukCombobox">
                        <input type="text" id="poMasukInput" class="form-control" value="<?= esc($oldPoMasukLabel) ?>" placeholder="-- Tidak ada --" autocomplete="off">
                        <input type="hidden" name="po_masuk_terkait" id="po_masuk_terkait" value="<?= esc($oldPoMasuk) ?>">
                        <div class="tre-inline-combobox-menu" id="poMasukComboboxMenu"></div>
                    </div>
                    <small class="text-muted po-keluar-helper">Isi kalau pembelian material ini memang untuk memenuhi PO Masuk (pesanan pelanggan) tertentu.</small>
                </div>
            </div>
            <div class="col-md-3">
                <label class="d-block">&nbsp;</label>
                <div class="po-keluar-direct-check custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" name="kirim_langsung" id="kirimLangsung" value="1" <?= old('kirim_langsung') ? 'checked' : '' ?>>
                    <label class="custom-control-label" for="kirimLangsung">Kirim langsung ke pihak lain</label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Keterangan <small class="text-muted">(opsional)</small></label>
                    <textarea name="keterangan" class="form-control" rows="3" placeholder="Opsional"><?= esc(old('keterangan')) ?></textarea>
                </div>
            </div>
            <div class="col-md-3" id="sumberMaterialProduksiGroup">
                <div class="form-group">
                    <label>Material Produksi</label>
                    <select name="sumber_material_produksi" id="sumberMaterialProduksi" class="form-control">
                        <option value="tre" <?= old('sumber_material_produksi', 'tre') === 'tre' ? 'selected' : '' ?>>Material TRE</option>
                        <option value="vendor" <?= old('sumber_material_produksi') === 'vendor' ? 'selected' : '' ?>>Material dari Customer</option>
                    </select>
                    <small class="text-muted po-keluar-helper">Untuk PO Produk. Pilih customer kalau material disediakan customer dan tidak masuk stok material TRE.</small>
                </div>
            </div>
        </div>

    </div>
</div>
<div class="card mb-3">
    <div class="card-header">
        <strong>Detail Item PO Keluar</strong>
    </div>
    <div class="card-body">
        <div class="row align-items-end">
            <div class="col-md-2">
                <div class="form-group">
                    <label>Tipe Item</label>
                    <input type="text" id="tipeItemLabel" class="form-control" value="<?= esc($labelJenisPo) ?>" readonly>
                    <input type="hidden" id="tipeItem" value="<?= esc($oldJenisPo) ?>">
                    <div class="po-helper-spacer" aria-hidden="true"></div>
                </div>
            </div>
            <div class="col-md-3" id="printSpecLabelGroup">
                <div class="form-group">
                    <label id="printSpecLabelTitle">Label Detail</label>
                    <input type="text" name="print_spec_label" id="printSpecLabelHeader" class="form-control" value="<?= old('print_spec_label', 'Lebar Sliting') ?>" placeholder="mis. Lebar Sliting">
                    <small class="form-text text-muted po-keluar-helper" id="printSpecLabelHelp">Dipakai sebagai label detail item pada print PO.</small>
                </div>
            </div>
            <div class="col-md-3" id="materialColumnToggleGroup">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <div class="form-check">
                        <input type="checkbox" name="material_column_enabled" id="materialColumnEnabled" class="form-check-input" value="1" <?= old('material_column_enabled', '1') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="materialColumnEnabled">Tampilkan kolom Material di print</label>
                    </div>
                    <small class="form-text text-muted po-keluar-helper">Kalau tidak dicentang, kolom MATERIAL tidak akan muncul sama sekali di hasil print PO Produk.</small>
                </div>
            </div>
        </div>
        <div class="row align-items-end">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="pilihItemInput">Item</label>
                    <div class="input-group tre-inline-combobox tre-inline-combobox-solo" id="itemCombobox">
                        <input type="text" id="pilihItemInput" class="form-control" placeholder="-- Pilih Item --" autocomplete="off">
                        <input type="hidden" id="pilihItem">
                        <div class="tre-inline-combobox-menu" id="itemComboboxMenu"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label id="labelPrintSpecItem">Info Print</label>
                    <input type="text" id="printSpecItem" class="form-control" placeholder="Opsional">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label id="labelQtyItem">Qty</label>
                    <input type="number" id="qtyItem" class="form-control" min="0.0001" step="0.0001" value="1">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Harga</label>
                    <input type="number" id="hargaItem" class="form-control" min="0" step="0.01" value="0">
                </div>
            </div>
            <div class="col-md-1">
                <div class="form-group">
                    <button type="button" id="tambahItem" class="btn btn-info btn-block">
                        <i class="fa fa-plus-circle"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered" id="tableDetailPoKeluar">
                <thead>
                    <tr>
                        <th style="width:5%">No</th>
                        <th>Tipe</th>
                        <th>Kode</th>
                        <th>Nama Item</th>
                        <th>Info Print</th>
                        <th>Satuan</th>
                        <th>Jumlah</th>
                        <th>Harga</th>
                        <th>Subtotal</th>
                        <th style="width:5%">#</th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                    <tr>
                        <th colspan="6" class="text-right">Total QTY</th>
                        <th id="totalQty" class="text-right">0</th>
                        <th></th>
                        <th id="totalNominal" class="text-right">Rp 0</th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <strong>Pengaturan Cetak PO</strong>
        <small class="text-muted ml-2" id="printFormatHint"></small>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label>Shipping To <small class="text-muted">(opsional)</small></label>
                    <input type="text" name="shipping_to" class="form-control" value="<?= old('shipping_to') ?>" placeholder="mis. Gudang CCIP">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>TOP <small class="text-muted">(opsional)</small></label>
                    <input type="text" name="top" class="form-control" value="<?= old('top') ?>" placeholder="mis. 30hr setelah Invoice">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>System Payment <small class="text-muted">(opsional)</small></label>
                    <input type="text" name="system_payment" class="form-control" value="<?= old('system_payment') ?>" placeholder="mis. Transfer">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Quot Number <small class="text-muted">(opsional)</small></label>
                    <input type="text" name="quot_number" class="form-control" value="<?= old('quot_number') ?>">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Approved By <small class="text-muted">(opsional)</small></label>
                    <input type="text" name="approved_by" class="form-control" value="<?= old('approved_by') ?>" placeholder="Nama yang menyetujui PO ini">
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label>Discount <small class="text-muted">(opsional)</small></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text">
                                <input type="checkbox" name="discount_enabled" id="discountEnabled" value="1" <?= old('discount_enabled') ? 'checked' : '' ?>>
                            </div>
                        </div>
                        <input type="number" name="discount_amount" id="discountAmount" class="form-control" min="0" step="0.01" value="<?= old('discount_amount', 0) ?>" placeholder="Nominal discount">
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>PPN 11%</label>
                    <div class="custom-control custom-checkbox mt-2">
                        <input type="checkbox" name="ppn_included" id="ppnIncluded" class="custom-control-input" value="1" <?= old('ppn_included') ? 'checked' : '' ?>>
                        <label class="custom-control-label" for="ppnIncluded">Harga sudah termasuk PPN</label>
                    </div>
                    <small class="form-text text-muted">Kalau tidak dicentang, PPN ditambahkan saat print.</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>PPH 23</label>
                    <div class="custom-control custom-checkbox mt-2">
                        <input type="checkbox" name="pph23_enabled" id="pph23Enabled" class="custom-control-input" value="1" <?= old('pph23_enabled') ? 'checked' : '' ?>>
                        <label class="custom-control-label" for="pph23Enabled">Potong PPH 23 2%</label>
                    </div>
                    <small class="form-text text-muted">Nominal PPH 23 dihitung otomatis 2% dari nilai PO setelah discount.</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-0">
                    <label>Notes Cetak <small class="text-muted">(opsional)</small></label>
                    <textarea name="print_notes" id="printNotes" class="form-control" rows="4" placeholder="Isi notes yang tampil di print PO"><?= esc(old('print_notes')) ?></textarea>
                </div>
            </div>
        </div>
    </div>
</div>

<button type="submit" class="btn btn-success">
    <i class="fa fa-save"></i> Simpan PO Keluar
</button>
<?= form_close() ?>

<script>
    const items = <?= json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const supplierOptions = <?= json_encode(array_map(static function ($row) {
                                return [
                                    'id' => (string) $row['supid'],
                                    'text' => (string) $row['supnama'],
                                ];
                            }, $suppliers ?? []), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const poAsalOptions = <?= json_encode(array_map(static function ($row) {
                            $noPo = (string) $row['no_po'];
                            return [
                                'id' => $noPo,
                                'text' => $noPo . ' - ' . (string) $row['supplier_nama'],
                            ];
                        }, $poAktifList ?? []), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const poMasukOptions = <?= json_encode(array_map(static function ($row) {
                            $noPo = (string) $row['nopo'];
                            return [
                                'id' => $noPo,
                                'text' => $noPo . ' - ' . (string) ($row['pelnama'] ?? '-'),
                            ];
                        }, $poMasukAktifList ?? []), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const allItemOptions = items.map(function(item) {
        const kodeLabel = item.label_kode || item.kode_item;
        return {
            id: String(item.kode_item),
            text: String(kodeLabel) + ' - ' + String(item.nama_item) + ' (' + String(item.satuan) + ')',
            tipe_item: item.tipe_item,
            kode_label: String(kodeLabel),
            harga_default: Number(item.harga_default || 0)
        };
    });
    const itemOptions = [];
    let supplierCombobox = null;
    let poAsalCombobox = null;
    let poMasukCombobox = null;
    let itemCombobox = null;
    let selectedRows = [];

    function formatNumber(value) {
        return new Intl.NumberFormat('id-ID').format(Math.round(value));
    }

    function formatRupiah(value) {
        return 'Rp ' + formatNumber(value);
    }

    function getQtyLabel(tipe) {
        return tipe === 'material' ? 'Kg' : 'Qty';
    }

    function getJenisPoLabel(tipe) {
        return {
            produk: 'PO Produk',
            jasa: 'PO Jasa',
            material: 'PO Material',
            habis_pakai: 'PO Barang Habis Pakai'
        }[tipe] || 'PO Material';
    }

    function getPrintSpecMeta(tipe) {
        return {
            produk: {
                title: 'Label Material',
                defaultLabel: 'Material',
                help: 'Dipakai sebagai label kolom material pada detail print PO Produk.',
                itemLabel: 'Material / Info Print',
                placeholder: 'mis. PET 0,22mm / Plastik PE Ori'
            },
            jasa: {
                title: 'Label Ukuran',
                defaultLabel: 'Lebar Sliting',
                help: 'Dipakai sebagai subjudul kolom UKURAN pada detail print PO Jasa.',
                itemLabel: null,
                placeholder: 'mis. 38mm'
            },
            material: {
                title: 'Label Size',
                defaultLabel: 'Size',
                help: 'Dipakai sebagai label kolom size pada detail print PO Material.',
                itemLabel: 'Size / Info Print',
                placeholder: 'mis. diameter/tebal/ukuran material'
            },
            habis_pakai: {
                title: 'Label Detail',
                defaultLabel: 'Keterangan',
                help: 'Informasi tambahan barang habis pakai pada detail PO vendor.',
                itemLabel: 'Keterangan / Info Print',
                placeholder: 'Opsional'
            }
        }[tipe] || {
            title: 'Label Detail',
            defaultLabel: 'Ukuran',
            help: 'Dipakai sebagai label detail item pada print PO.',
            itemLabel: 'Info Print',
            placeholder: 'Opsional'
        };
    }

    function getPrintSpecLabel(tipe) {
        const meta = getPrintSpecMeta(tipe);
        return meta.itemLabel || (($('#printSpecLabelHeader').val() || meta.defaultLabel) + ' / Info Print');
    }

    function isTitipProses() {
        return $('#jenisTransaksi').val() === 'Titip Proses';
    }

    function getDetailItemType() {
        return isTitipProses() ? 'material' : ($('#jenisPo').val() || 'material');
    }

    function getDetailItemTypeLabel(tipe) {
        return isTitipProses() && tipe === 'material' ? 'Material Diproses' : getJenisPoLabel(tipe);
    }

    function updateQtyLabel() {
        const tipe = $('#tipeItem').val();
        $('#labelQtyItem').text(getQtyLabel(tipe));
        $('#tipeItemLabel').val(getDetailItemTypeLabel(tipe));
        $('#labelPrintSpecItem').text(getPrintSpecLabel(tipe));
        $('#printSpecItem').attr('placeholder', getPrintSpecPlaceholder(tipe));
    }

    function getPrintSpecPlaceholder(tipe) {
        return getPrintSpecMeta(tipe).placeholder;
    }

    function updatePrintSettingsByJenisPo() {
        const tipe = $('#jenisPo').val();
        const meta = getPrintSpecMeta(tipe);
        const knownDefaults = ['Material', 'Lebar Sliting', 'Size', 'Ukuran'];
        const currentLabel = $('#printSpecLabelHeader').val();

        $('#printFormatHint').text({
            produk: 'Format cetak: kolom MATERIAL, DESCRIPTION, Unit, Qty, harga.',
            jasa: 'Format cetak: kolom UKURAN, DESCRIPTION, Quantity, UoM, harga.',
            material: 'Format cetak: kolom SIZE, DESCRIPTION, QTY, UoM, harga.',
            habis_pakai: 'Format cetak: barang habis pakai, DESCRIPTION, QTY, UoM, harga.'
        }[tipe] || '');

        $('#printSpecLabelTitle').text(meta.title);
        $('#printSpecLabelHelp').text(meta.help);
        $('#printSpecLabelHeader').attr('placeholder', 'mis. ' + meta.defaultLabel);
        if (!currentLabel || knownDefaults.includes(currentLabel)) {
            $('#printSpecLabelHeader').val(meta.defaultLabel);
        }
        $('#printNotes').attr('placeholder', {
            produk: 'Isi notes yang tampil di print PO Produk',
            jasa: 'Isi notes yang tampil di print PO Jasa',
            material: 'Isi notes yang tampil di print PO Material',
            habis_pakai: 'Isi notes yang tampil di print PO Barang Habis Pakai'
        }[tipe] || 'Isi notes yang tampil di print PO');

        updateQtyLabel();
    }

    function normalizeComboboxValue(value) {
        return String(value || '').trim().toLowerCase();
    }

    function findComboboxOption(options, value) {
        const keyword = normalizeComboboxValue(value);
        if (keyword === '') {
            return null;
        }

        return options.find(function(option) {
            return normalizeComboboxValue(option.id) === keyword ||
                normalizeComboboxValue(option.text) === keyword ||
                normalizeComboboxValue(option.kode_label) === keyword;
        }) || null;
    }

    function syncComboboxValue(instance, inputSelector, hiddenSelector, options, allowEmpty) {
        if (instance && typeof instance.sync === 'function') {
            instance.sync();
        }

        const $input = $(inputSelector);
        const $hidden = $(hiddenSelector);
        const typed = $input.val();
        if (allowEmpty && normalizeComboboxValue(typed) === '') {
            $hidden.val('');
            return true;
        }

        const match = findComboboxOption(options, typed);
        if (match) {
            $hidden.val(match.id);
            $input.val(match.text);
            return true;
        }

        return false;
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function(match) {
            return ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            })[match];
        });
    }

    function refreshItemOptions() {
        const tipe = getDetailItemType();
        const jenisPo = $('#jenisPo').val() || 'material';
        $('#tipeItem').val(tipe);
        updateQtyLabel();
        const isProduk = jenisPo === 'produk';
        $('#sumberMaterialProduksiGroup').toggle(isProduk);
        $('#sumberMaterialProduksi').prop('disabled', !isProduk);
        $('#materialColumnToggleGroup').toggle(isProduk);
        updatePrintSettingsByJenisPo();
        itemOptions.length = 0;
        allItemOptions
            .filter(item => item.tipe_item === tipe)
            .forEach(item => itemOptions.push(item));
        $('#pilihItemInput').val('');
        $('#pilihItem').val('');
    }

    function renderRows() {
        let totalQty = 0;
        let totalNominal = 0;
        const rows = selectedRows.map((row, index) => {
            totalQty += row.qty;
            totalNominal += row.subtotal;
            return `
                <tr>
                    <td class="text-center">${index + 1}</td>
                    <td>${escapeHtml(row.tipe)}</td>
                    <td>${escapeHtml(row.kodeLabel)}</td>
                    <td>${escapeHtml(row.nama)}</td>
                    <td>${escapeHtml(row.printSpec || '-')}</td>
                    <td>${escapeHtml(row.satuan)}</td>
                    <td class="text-right">${formatNumber(row.qty)}</td>
                    <td class="text-right">${formatRupiah(row.harga)}</td>
                    <td class="text-right">${formatRupiah(row.subtotal)}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger" onclick="hapusItem(${index})">
                            <i class="fa fa-trash-alt"></i>
                        </button>
                        <input type="hidden" name="tipe_item[]" value="${escapeHtml(row.tipe)}">
                        <input type="hidden" name="kode_item[]" value="${escapeHtml(row.kode)}">
                        <input type="hidden" name="print_spec[]" value="${escapeHtml(row.printSpec || '')}">
                        <input type="hidden" name="qty_pesan[]" value="${escapeHtml(row.qty)}">
                        <input type="hidden" name="harga[]" value="${escapeHtml(row.harga)}">
                    </td>
                </tr>`;
        });

        $('#tableDetailPoKeluar tbody').html(rows.join(''));
        $('#totalQty').text(formatNumber(totalQty));
        $('#totalNominal').text(formatRupiah(totalNominal));
    }

    function hapusItem(index) {
        selectedRows.splice(index, 1);
        renderRows();
    }

    function initPoHelperToggles() {
        $('.po-keluar-helper').each(function(index) {
            const helper = $(this);
            if (helper.data('po-helper-ready')) {
                return;
            }

            const helperId = helper.attr('id') || 'poKeluarHelperInfo' + index;
            helper.attr('id', helperId).attr('aria-hidden', 'true');

            const toggle = $('<button type="button" class="po-helper-toggle" aria-expanded="false"></button>');
            toggle.attr('aria-controls', helperId);
            toggle.append('<span>More information</span>', '<i class="fas fa-chevron-down"></i>');
            helper.before(toggle);
            helper.data('po-helper-ready', true).hide();

            toggle.on('click', function() {
                const isOpen = toggle.hasClass('is-open');
                toggle.toggleClass('is-open', !isOpen);
                toggle.attr('aria-expanded', isOpen ? 'false' : 'true');
                toggle.find('span').text(isOpen ? 'More information' : 'Hide information');
                helper.attr('aria-hidden', isOpen ? 'true' : 'false').stop(true, true).slideToggle(160);
            });
        });
    }
    function syncPrintOptionInputs() {
        $('#discountAmount').prop('disabled', !$('#discountEnabled').is(':checked'));
    }

    $(document).ready(function() {
        refreshItemOptions();
        syncPrintOptionInputs();
        initPoHelperToggles();

        supplierCombobox = window.treInitInlineCombobox({
            box: '#supplierCombobox',
            input: '#supplierInput',
            hidden: '#idsup',
            menu: '#supplierComboboxMenu',
            options: supplierOptions
        });
        poAsalCombobox = window.treInitInlineCombobox({
            box: '#poAsalCombobox',
            input: '#poAsalInput',
            hidden: '#po_asal',
            menu: '#poAsalComboboxMenu',
            options: poAsalOptions
        });
        poMasukCombobox = window.treInitInlineCombobox({
            box: '#poMasukCombobox',
            input: '#poMasukInput',
            hidden: '#po_masuk_terkait',
            menu: '#poMasukComboboxMenu',
            options: poMasukOptions
        });
        itemCombobox = window.treInitInlineCombobox({
            box: '#itemCombobox',
            input: '#pilihItemInput',
            hidden: '#pilihItem',
            menu: '#itemComboboxMenu',
            options: itemOptions
        });

        $('#jenisPo').on('change', function() {
            selectedRows = [];
            renderRows();
            refreshItemOptions();
            updatePrintSettingsByJenisPo();
        });

        $('#jenisTransaksi').on('change', function() {
            selectedRows = [];
            renderRows();
            refreshItemOptions();
        });

        $('#printSpecLabelHeader').on('input', updateQtyLabel);
        $('#discountEnabled').on('change', syncPrintOptionInputs);

        $('#tambahItem').on('click', function() {
            const tipe = $('#tipeItem').val();
            syncComboboxValue(itemCombobox, '#pilihItemInput', '#pilihItem', itemOptions, false);
            const kode = $('#pilihItem').val();
            const printSpec = $('#printSpecItem').val();
            const qty = parseFloat($('#qtyItem').val()) || 0;
            let harga = parseFloat($('#hargaItem').val()) || 0;
            const item = items.find(row => row.tipe_item === tipe && row.kode_item == kode);

            if (!item) {
                showBootstrapModal('Pesan', 'Pilih item terlebih dahulu.', 'warning');
                return;
            }
            if (harga <= 0 && Number(item.harga_default || 0) > 0) {
                harga = Number(item.harga_default || 0);
                $('#hargaItem').val(harga);
            }
            if (qty <= 0) {
                showBootstrapModal('Pesan', getQtyLabel(tipe) + ' harus lebih dari 0.', 'warning');
                return;
            }
            if (harga < 0) {
                showBootstrapModal('Pesan', 'Harga tidak boleh negatif.', 'warning');
                return;
            }

            const idsupTerpilih = $('#idsup').val();
            const namaTerpilih = (tipe === 'material' && item.label_per_supplier && item.label_per_supplier[idsupTerpilih])
                ? item.label_per_supplier[idsupTerpilih]
                : item.nama_item;

            selectedRows.push({
                tipe: tipe,
                kode: item.kode_item,
                kodeLabel: item.label_kode || item.kode_item,
                nama: namaTerpilih,
                printSpec: printSpec,
                satuan: item.satuan,
                qty: qty,
                harga: harga,
                subtotal: qty * harga
            });
            renderRows();
            $('#pilihItemInput').val('');
            $('#pilihItem').val('');
            $('#printSpecItem').val('');
            $('#pilihItemInput').focus();
        });

        $('#formPoKeluar').on('submit', function(e) {
            const supplierValid = syncComboboxValue(supplierCombobox, '#supplierInput', '#idsup', supplierOptions, false);
            const poAsalValid = syncComboboxValue(poAsalCombobox, '#poAsalInput', '#po_asal', poAsalOptions, true);
            const poMasukValid = syncComboboxValue(poMasukCombobox, '#poMasukInput', '#po_masuk_terkait', poMasukOptions, true);

            if (!supplierValid || !$('#idsup').val()) {
                e.preventDefault();
                showBootstrapModal('Pesan', 'Supplier/Vendor harus dipilih dari daftar.', 'warning');
                $('#supplierInput').focus();
                return;
            }
            if (!poAsalValid) {
                e.preventDefault();
                showBootstrapModal('Pesan', 'PO Asal harus dipilih dari daftar atau dikosongkan.', 'warning');
                $('#poAsalInput').focus();
                return;
            }
            if (!poMasukValid) {
                e.preventDefault();
                showBootstrapModal('Pesan', 'PO Masuk Terkait harus dipilih dari daftar atau dikosongkan.', 'warning');
                $('#poMasukInput').focus();
                return;
            }
            if (selectedRows.length === 0) {
                e.preventDefault();
                showBootstrapModal('Pesan', 'Minimal harus ada 1 item PO Keluar.', 'warning');
            }
        });
    });
</script>
<?= $this->endSection('isi') ?>
