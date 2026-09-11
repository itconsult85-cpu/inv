<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Ubah PO
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>

<button type="button" class="btn btn-warning" onclick="location.href=('/po/data')">
    <i class="fa fa-undo"></i> Kembali
</button>
<?php if (\App\Libraries\AccessControl::can('order.po_masuk.close_item')) : ?>
    <button type="button" class="btn btn-secondary" id="tombolClosePo">
        <i class="fa fa-box-open"></i> Tutup PO
    </button>
<?php endif ?>

<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<?php
    $poLocked = (bool) ($poLocked ?? false);
    $poLockReasons = $poLockReasons ?? [];
    $poEditDisabled = $poLocked ? 'disabled' : '';
?>
<style>
    .po-edit-shell {
        max-width: 1440px;
        width: calc(100% - 24px);
        margin: 0 auto;
    }

    .po-edit-intro {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.4rem;
    }

    .po-edit-eyebrow {
        color: #7b8798;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .18em;
        text-transform: uppercase;
        margin-bottom: .45rem;
    }

    .po-edit-title-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: .65rem;
    }

    .po-edit-title {
        color: #111827;
        font-size: 2rem;
        font-weight: 800;
        line-height: 1.15;
        margin: 0;
    }

    .po-id-pill,
    .po-lock-pill,
    .po-readonly-pill {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 700;
        line-height: 1;
        padding: .48rem .75rem;
        white-space: nowrap;
    }

    .po-id-pill {
        background: #f3f4f6;
        color: #374151;
    }

    .po-lock-pill,
    .po-readonly-pill {
        background: #fff8db;
        border: 1px solid #f4dc80;
        color: #8a6200;
    }

    .po-soft-alert {
        background: #fff7d6;
        border: 1px solid #f4d56b;
        border-radius: 8px;
        color: #2a2f3a;
        font-size: .9rem;
        margin-bottom: 1.2rem;
        padding: .85rem 1rem;
    }

    .po-soft-alert.is-info {
        background: #eef9fc;
        border-color: #ccebf2;
        color: #18606e;
    }

    .po-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        overflow: hidden;
        margin-bottom: 1.4rem;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #fff;
    }

    .po-summary-card {
        min-height: 110px;
        padding: 1.25rem 1.35rem;
        border-right: 1px solid #e5e7eb;
    }

    .po-summary-card:last-child {
        border-right: 0;
    }

    .po-summary-label {
        display: flex;
        align-items: center;
        gap: .45rem;
        margin-bottom: .8rem;
        color: #6b7280;
        font-size: .75rem;
        font-weight: 800;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .po-summary-value {
        color: #111827;
        font-size: 1rem;
        font-weight: 800;
        line-height: 1.45;
    }

    .po-total-value {
        color: #111827 !important;
        font-size: 1.15rem !important;
        text-align: left !important;
    }

    .po-summary-subtext {
        color: #8a95a7;
        font-size: .78rem;
        margin-top: .2rem;
    }

    .po-section-card {
        overflow: hidden;
        margin-bottom: 1.35rem;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #fff;
    }

    .po-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .po-section-title {
        color: #111827;
        font-size: 1rem;
        font-weight: 800;
        margin: 0;
    }

    .po-section-subtitle {
        color: #7b8798;
        font-size: .8rem;
        margin-top: .15rem;
    }

    .po-section-body {
        padding: 1.25rem;
    }

    .po-form-grid {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 1rem;
    }

    .po-field {
        grid-column: span 3;
        min-width: 0;
    }

    .po-field.is-small,
    .po-field.is-actions {
        grid-column: span 2;
    }

    .po-field.is-actions {
        align-self: end;
    }

    .po-field label {
        color: #111827;
        font-size: .9rem;
        font-weight: 800;
        margin-bottom: .55rem;
    }

    .po-field .form-control {
        min-height: 46px;
        border-color: #d7e2ee;
        border-radius: 8px;
    }

    .po-action-row {
        display: flex;
        align-items: center;
        gap: .5rem;
        min-height: 46px;
    }

    .po-action-row .btn {
        min-height: 46px;
        border-radius: 8px;
        font-weight: 800;
    }

    .po-table-wrap {
        overflow-x: auto;
    }

    .po-table-wrap .table {
        margin-bottom: 0;
    }

    .po-table-wrap .table thead th {
        background: #f6f8fa;
        color: #243042;
        font-size: .84rem;
        font-weight: 800;
        border-color: #e1e8ef;
        vertical-align: middle;
    }

    .po-table-wrap .table td {
        border-color: #e8eef5;
        vertical-align: middle;
    }

    table#datadetail tbody tr:hover {
        cursor: pointer;
        background-color: #f7fbfd;
        color: inherit;
    }

    #nopoDisplay,
    #pelangganDisplay,
    #tanggalDisplay {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: .5rem;
    }

    #pelangganSelect {
        display: none;
        max-width: 360px;
    }

    #nopoInput {
        width: auto;
        margin-right: 8px;
        display: none;
    }

    #nopoDisplay button {
        margin-left: 8px;
    }

    @media (max-width: 992px) {
        .po-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .po-summary-card:nth-child(2) {
            border-right: 0;
        }

        .po-summary-card:nth-child(-n+2) {
            border-bottom: 1px solid #e5e7eb;
        }

        .po-field,
        .po-field.is-small,
        .po-field.is-actions {
            grid-column: span 6;
        }
    }

    @media (max-width: 576px) {
        .po-edit-intro {
            display: block;
        }

        .po-summary-grid {
            grid-template-columns: 1fr;
        }

        .po-summary-card {
            border-right: 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .po-summary-card:last-child {
            border-bottom: 0;
        }

        .po-field,
        .po-field.is-small,
        .po-field.is-actions {
            grid-column: span 12;
        }
    }
</style>
<div class="po-edit-shell">
    <input type="hidden" id="nopo" value="<?= $nopo ?>">
    <input type="hidden" id="nopoOriginalSha1" value="<?= sha1($nopo) ?>">
    <input type="hidden" id="idpelanggan" value="<?= esc($idpelanggan) ?>">
    <input type="hidden" id="tglfaktur" value="<?= $tanggal ?>">


    <?php if ($poLocked) : ?>
        <div class="po-soft-alert">
            <span class="po-lock-pill mr-2"><i class="fa fa-lock"></i> Terkunci</span> <strong>PO sudah dipakai transaksi lanjutan.</strong> Semua data PO dikunci dan tidak bisa diedit.
            <?php if ($poLockReasons) : ?>
                <span class="ml-1">Terkait: <?= esc(implode(', ', $poLockReasons)) ?></span>
            <?php endif ?>
        </div>
    <?php else : ?>
        <div class="po-soft-alert is-info">
            PO masih bisa diedit selama belum dipakai di transaksi lanjutan. Progress awal dari input PO tidak mengunci edit.
        </div>
    <?php endif ?>

    <div class="po-summary-grid">
        <div class="po-summary-card">
            <div class="po-summary-label">No. PO</div>
            <div class="po-summary-value" id="nopoDisplay">
                <span id="nopoText"><?= esc($nopo) ?></span>
                <?php if (!$poLocked && \App\Libraries\AccessControl::can('order.po_masuk.change_number')) :  ?>
                    <input type="text" id="nopoInput" class="form-control" value="<?= esc($nopo) ?>" style="display: none;">
                    <button type="button" id="editNopoBtn" class="btn btn-sm btn-primary ml-2">Edit</button>
                    <button type="button" id="saveNopoBtn" class="btn btn-sm btn-success ml-2" style="display: none;">Simpan</button>
                    <button type="button" id="cancelNopoBtn" class="btn btn-sm btn-danger ml-2" style="display: none;">Batal</button>
                <?php endif ?>
            </div>
        </div>
        <div class="po-summary-card">
            <div class="po-summary-label">Tanggal</div>
            <div class="po-summary-value" id="tanggalDisplay">
                <span id="tanggalText"><?= esc($tanggal) ?></span>
                <?php if (\App\Libraries\AccessControl::can('order.po_masuk.edit_detail')) : ?>
                    <input type="text" id="tanggalInputTampil" class="form-control" style="width:140px; display:none;" placeholder="dd-MM-YYYY" inputmode="numeric">
                    <button type="button" id="editTanggalBtn" class="btn btn-sm btn-primary ml-2">Edit</button>
                    <button type="button" id="saveTanggalBtn" class="btn btn-sm btn-success ml-2" style="display: none;">Simpan</button>
                    <button type="button" id="cancelTanggalBtn" class="btn btn-sm btn-danger ml-2" style="display: none;">Batal</button>
                <?php endif ?>
            </div>
        </div>
        <div class="po-summary-card">
            <div class="po-summary-label">Pelanggan</div>
            <div class="po-summary-value" id="pelangganDisplay">
                <span id="pelangganText"><?= esc($namapelanggan) ?></span>
                <?php if (!$poLocked && \App\Libraries\AccessControl::can('order.po_masuk.edit_detail')) : ?>
                    <select id="pelangganSelect" class="form-control form-control-sm">
                        <?php foreach (($datapelanggan ?? []) as $pelanggan) : ?>
                            <option value="<?= esc($pelanggan['pelid']) ?>" <?= (string) $pelanggan['pelid'] === (string) $idpelanggan ? 'selected' : '' ?>><?= esc($pelanggan['pelnama']) ?></option>
                        <?php endforeach ?>
                    </select>
                    <button type="button" id="editPelangganBtn" class="btn btn-sm btn-primary">Edit</button>
                    <button type="button" id="savePelangganBtn" class="btn btn-sm btn-success" style="display: none;">Simpan</button>
                    <button type="button" id="cancelPelangganBtn" class="btn btn-sm btn-danger" style="display: none;">Batal</button>
                <?php endif ?>
            </div>
        </div>
        <div class="po-summary-card">
            <div class="po-summary-label">Total Qty</div>
            <div class="po-summary-value po-total-value" id="lbTotalBerat"></div>
            <div class="po-summary-subtext">Total item PO</div>
        </div>
    </div>

    <input type="hidden" id="iddetail">
    <div class="po-section-card">
        <div class="po-section-header">
            <div>
                <h3 class="po-section-title">Tambah Barang</h3>
                <div class="po-section-subtitle"><?= $poLocked ? 'Form dinonaktifkan karena PO sudah dipakai transaksi lanjutan.' : 'Masukkan detail item baru untuk ditambahkan ke PO ini.' ?></div>
            </div>
            <?php if ($poLocked) : ?>
                <span class="po-readonly-pill"><i class="fa fa-lock"></i> Terkunci</span>
            <?php endif ?>
        </div>
        <div class="po-section-body">
            <div class="po-form-grid">
                <div class="po-field">
                    <label for="kodebarang">Kode Produk</label>
                    <div class="tre-inline-combobox tre-inline-combobox-solo" id="produkCombobox">
                        <input type="text" class="form-control" name="kodebarang" id="kodebarang" autocomplete="off" <?= $poEditDisabled ?>>
                        <input type="hidden" id="kodebarang_pilih">
                        <div class="tre-inline-combobox-menu" id="produkComboboxMenu"></div>
                    </div>
                </div>
                <div class="po-field">
                    <label for="namabarang">Nama Produk</label>
                    <input type="text" class="form-control" name="namabarang" id="namabarang" readonly>
                </div>
                <div class="po-field">
                    <label for="berat">Berat Satuan (KG)</label>
                    <input type="number" class="form-control" name="berat" id="berat" readonly>
                </div>
                <div class="po-field">
                    <label for="stok">Stok (Pcs)</label>
                    <input type="number" class="form-control" name="stok" id="stok" readonly>
                </div>
                <div class="po-field is-small">
                    <label for="harga">Harga</label>
                    <input type="number" class="form-control" name="harga" id="harga" readonly>
                </div>
                <div class="po-field is-small">
                    <label for="jml">Qty</label>
                    <input type="number" class="form-control" name="jml" id="jml" value="1" <?= $poEditDisabled ?>>
                </div>
                <div class="po-field">
                    <label for="terkirim_awal">QTY terkirim</label>
                    <input type="number" min="0" class="form-control" name="terkirim_awal" id="terkirim_awal" value="0" <?= $poEditDisabled ?>>
                </div>
                <div class="po-field">
                    <label for="invoice_awal">Nilai sudah ditagihkan</label>
                    <input type="number" min="0" class="form-control" name="invoice_awal" id="invoice_awal" value="0" <?= $poEditDisabled ?>>
                </div>
                <div class="po-field is-actions">
                    <label>&nbsp;</label>
                    <div class="po-action-row">
                        <button type="button" class="btn btn-success" title="Simpan Item" id="tombolSimpanItem" <?= $poEditDisabled ?>>
                            <i class="fa fa-save"></i> Tambah Item
                        </button>
                        <button type="button" style="display: none;" class="btn btn-primary" title="Edit Item" id="tombolEditItem" <?= $poEditDisabled ?>>
                            <i class="fa fa-edit"></i> Simpan
                        </button>
                        <button type="button" style="display:none;" class="btn btn-default" title="Batalkan" id="tombolBatal">
                            <i class="fa fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="po-section-card">
        <div class="po-section-header">
            <div>
                <h3 class="po-section-title">Daftar Item PO</h3>
                <div class="po-section-subtitle">Item PO dan progress qty yang sudah tercatat.</div>
            </div>
            <?php if ($poLocked) : ?>
                <span class="po-readonly-pill"><i class="fa fa-lock"></i> Read-only</span>
            <?php endif ?>
        </div>
        <div class="po-section-body po-table-wrap tampilDataDetail"></div>
    </div>
</div>
<div class="viewmodal" style="display: none;"></div>
<script>
    let csrfToken = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';
    const produkOptions = <?= json_encode(array_map(static function ($row) {
                                $kode = (string) $row['brgkode'];
                                $nama = (string) $row['brgnama'];
                                return [
                                    'id' => $kode,
                                    'text' => $kode . ' - ' . $nama,
                                    'value' => $kode,
                                ];
                            }, $databarang ?? []), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    let produkCombobox = null;
    const poEditLocked = <?= $poLocked ? 'true' : 'false' ?>;

    function guardPoEditLocked() {
        if (poEditLocked) {
            Swal.fire('PO Terkunci', 'PO sudah dipakai transaksi lanjutan sehingga tidak bisa diedit.', 'warning');
            return true;
        }
        return false;
    }

    function normalizeComboboxValue(value) {
        return String(value || '').trim().toLowerCase();
    }

    // Tanggal PO sengaja nggak ngikut guardPoEditLocked() -- boleh diedit
    // baik sebelum maupun sesudah PO terkunci transaksi lanjutan.
    function formatTanggalTampil(value) {
        const tanggal = String(value || '').trim();
        const isoMatch = tanggal.match(/^(\d{4})-(\d{2})-(\d{2})$/);
        if (isoMatch) {
            return `${isoMatch[3]}-${isoMatch[2]}-${isoMatch[1]}`;
        }
        return tanggal;
    }

    function formatTanggalServer(value) {
        const tanggal = String(value || '').trim().replace(/\//g, '-');
        const tampilMatch = tanggal.match(/^(\d{2})-(\d{2})-(\d{4})$/);
        if (tampilMatch) {
            return `${tampilMatch[3]}-${tampilMatch[2]}-${tampilMatch[1]}`;
        }
        return tanggal;
    }

    function syncProdukCombobox() {
        if (produkCombobox && typeof produkCombobox.sync === 'function') {
            produkCombobox.sync();
        }

        const keyword = normalizeComboboxValue($('#kodebarang').val());
        const match = produkOptions.find(function(option) {
            return normalizeComboboxValue(option.text) === keyword || normalizeComboboxValue(option.value) === keyword;
        });

        if (match) {
            $('#kodebarang').val(match.value || match.text);
            $('#kodebarang_pilih').val(match.id);
        }
    }

    function handlePoEditAjaxError(xhr, thrownError, targetSelector = null) {
        const responseText = xhr && xhr.responseText ? xhr.responseText : '';
        const isHtmlResponse = /^\s*</.test(responseText);
        let message = xhr && xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : '';

        if (!message && isHtmlResponse) {
            message = 'Server mengirim halaman HTML, bukan data JSON. Coba refresh halaman atau login ulang kalau sesi habis.';
        }

        if (!message && xhr && xhr.status === 0) {
            message = 'Koneksi ke server terputus atau request dibatalkan. Coba refresh halaman.';
        }

        if (!message) {
            message = thrownError || 'Request gagal diproses.';
        }

        console.error('PO edit AJAX error', {
            status: xhr ? xhr.status : null,
            thrownError: thrownError,
            response: responseText.substring(0, 500)
        });

        if (targetSelector) {
            $(targetSelector).html('<div class="alert alert-warning mb-0">' + message + '</div>');
            return;
        }

        Swal.fire('Request Gagal', message, 'warning');
    }

    function kosong() {
        $('#kodebarang').val('');
        $('#kodebarang_pilih').val('');
        $('#stok').val('');
        $('#berat').val('');
        $('#harga').val('');
        $('#namabarang').val('');
        $('#iddetail').val('');
        $('#jml').val('1');
        $('#terkirim_awal').val('0');
        $('#invoice_awal').val('0');
        $('#kodebarang').focus();
    }

    function ambilDataBarang() {
        syncProdukCombobox();
        let kodebarang = $('#kodebarang').val();
        let harga = $('#harga').val();
        if (kodebarang.length == 0) {
            Swal.fire('Error', 'Kode Barang harus di inputkan', 'error');
            kosong();
        } else {
            $.ajax({
                type: "post",
                url: '<?= site_url('po/ambilDataBarang') ?>',
                data: {
                    [csrfToken]: csrfHash,
                    kodebarang: kodebarang,
                    harga: harga,
                },
                dataType: "json",
                success: function(response) {
                    if (response.error) {
                        Swal.fire('Error', response.error, 'error');
                        kosong();
                    }

                    if (response.sukses) {
                        let data = response.sukses;

                        $('#stok').val(data.stok);
                        $('#harga').val(data.harga);
                        $('#namabarang').val(data.namabarang);
                        $('#berat').val(data.berat);
                        $('#jml').focus();
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    handlePoEditAjaxError(xhr, thrownError);
                }
            });
        }
    }

    function tampilDataDetail() {
        let nopo = $('#nopo').val();
        $.ajax({
            type: "post",
            url: '<?= site_url('po/tampilDataDetail') ?>',
            data: {
                [csrfToken]: csrfHash,
                nopo: nopo
            },
            dataType: "json",
            beforeSend: function() {
                $('.tampilDataDetail').html("<i class='fa fa-spin fa-spinner'></i>");
            },
            success: function(response) {
                if (response.data) {
                    $('.tampilDataDetail').html(response.data);
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                handlePoEditAjaxError(xhr, thrownError, '.tampilDataDetail');
            }
        });
    }

    function ambilTotalBerat() {
        let nopo = $('#nopo').val();
        $.ajax({
            type: "post",
            url: '<?= site_url('po/ambilTotalBerat') ?>',
            data: {
                [csrfToken]: csrfHash,
                nopo: nopo
            },
            dataType: "json",
            success: function(response) {
                $('#lbTotalBerat').html(response.totalberat);
            },
            error: function(xhr, ajaxOptions, thrownError) {
                handlePoEditAjaxError(xhr, thrownError);
            }
        });
    }

    function ambilTotalHarga() {
        let nopo = $('#nopo').val();
        $.ajax({
            type: "post",
            url: '<?= site_url('po/ambilTotalHarga') ?>',
            data: {
                [csrfToken]: csrfHash,
                nopo: nopo
            },
            dataType: "json",
            success: function(response) {
                $('#lbTotalHarga').html(response.totalharga);
            },
            error: function(xhr, ajaxOptions, thrownError) {
                handlePoEditAjaxError(xhr, thrownError);
            }
        });
    }

    function simpanItem() {
        if (guardPoEditLocked()) {
            return;
        }
        syncProdukCombobox();
        let nopo = $('#nopo').val();
        let tglfaktur = $('#tglfaktur').val();
        let idpelanggan = $('#idpelanggan').val();
        let kodebarang = $('#kodebarang').val();
        let namabarang = $('#namabarang').val();
        let berat = $('#berat').val();
        let jml = $('#jml').val();
        let harga = $('#harga').val();
        let terkirimAwal = $('#terkirim_awal').val();
        let invoiceAwal = $('#invoice_awal').val();

        if (kodebarang.length == 0) {
            Swal.fire('Error', 'Kode Barang harus di inputkan', 'error');
            kosong();
        } else if (parseFloat(terkirimAwal || 0) > parseFloat(jml || 0)) {
            Swal.fire('Error', 'Qty terkirim tidak boleh lebih besar dari Qty PO item.', 'error');
        } else if (parseFloat(invoiceAwal || 0) > (parseFloat(terkirimAwal || 0) * parseFloat(harga || 0))) {
            Swal.fire('Error', 'Nilai sudah ditagihkan tidak boleh lebih besar dari QTY terkirim dikali harga satuan.', 'error');
        } else {
            $.ajax({
                type: "post",
                url: '<?= site_url('po/simpanItemDetail') ?>',
                data: {
                    [csrfToken]: csrfHash,
                    nopo: nopo,
                    tglfaktur: tglfaktur,
                    idpelanggan: idpelanggan,
                    kodebarang: kodebarang,
                    namabarang: namabarang,
                    berat: berat,
                    harga: harga,
                    jml: jml,
                    terkirim_awal: terkirimAwal,
                    invoice_awal: invoiceAwal
                },
                dataType: "json",
                success: function(response) {
                    if (response.error) {
                        Swal.fire('Error', response.error, 'error');
                        kosong();
                    }
                    if (response.sukses) {
                        Swal.fire('Berhasil', response.sukses, 'success');
                        tampilDataDetail();
                        ambilTotalBerat();
                        kosong();
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    handlePoEditAjaxError(xhr, thrownError);
                }
            });
        }
    }

    $(document).ready(function() {
        produkCombobox = window.treInitInlineCombobox({
            box: '#produkCombobox',
            input: '#kodebarang',
            hidden: '#kodebarang_pilih',
            menu: '#produkComboboxMenu',
            options: produkOptions,
            onSelect: function() {
                ambilDataBarang();
            }
        });

        $('#kodebarang').on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                syncProdukCombobox();
                ambilDataBarang();
            }
        });

        $('#kodebarang').on('blur', function() {
            syncProdukCombobox();
            if ($('#kodebarang_pilih').val()) {
                ambilDataBarang();
            }
        });

        ambilTotalBerat();
        tampilDataDetail();

        $('#tombolSimpanItem').click(function(e) {
            e.preventDefault();
            simpanItem();
        });


        $(document).on('click', '#editNopoBtn', function() {
            $('#nopoText').hide();
            $('#nopoInput').show();
            $('#editNopoBtn').hide();
            $('#saveNopoBtn').show();
            $('#cancelNopoBtn').show();
        });

        $(document).on('click', '#cancelNopoBtn', function() {
            $('#nopoInput').hide();
            $('#nopoText').show();
            $('#nopoInput').val($('#nopo').val());
            $('#editNopoBtn').show();
            $('#saveNopoBtn').hide();
            $('#cancelNopoBtn').hide();
        });

        $(document).on('click', '#editTanggalBtn', function() {
            $('#tanggalInputTampil').val(formatTanggalTampil($('#tglfaktur').val())).show();
            $('#tanggalText').hide();
            $('#editTanggalBtn').hide();
            $('#saveTanggalBtn').show();
            $('#cancelTanggalBtn').show();
        });

        $(document).on('click', '#cancelTanggalBtn', function() {
            $('#tanggalInputTampil').hide();
            $('#tanggalText').show();
            $('#editTanggalBtn').show();
            $('#saveTanggalBtn').hide();
            $('#cancelTanggalBtn').hide();
        });

        $(document).on('click', '#saveTanggalBtn', function(e) {
            e.preventDefault();
            const tanggalServer = formatTanggalServer($('#tanggalInputTampil').val());
            if (!/^\d{4}-\d{2}-\d{2}$/.test(tanggalServer)) {
                Swal.fire('Error', 'Format tanggal harus dd-MM-YYYY', 'error');
                return;
            }
            $.ajax({
                type: "post",
                url: '<?= site_url('po/updateTanggal') ?>',
                data: {
                    [csrfToken]: csrfHash,
                    nopoSha1: $('#nopoOriginalSha1').val(),
                    tanggal: tanggalServer
                },
                dataType: "json",
                success: function(response) {
                    if (response.error) {
                        Swal.fire('Error', response.error, 'error');
                        return;
                    }
                    $('#tglfaktur').val(tanggalServer);
                    $('#tanggalText').text(tanggalServer).show();
                    $('#tanggalInputTampil').hide();
                    $('#editTanggalBtn').show();
                    $('#saveTanggalBtn').hide();
                    $('#cancelTanggalBtn').hide();
                    Swal.fire('Berhasil', response.sukses, 'success');
                    tampilDataDetail();
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    handlePoEditAjaxError(xhr, thrownError);
                }
            });
        });

        $(document).on('click', '#editPelangganBtn', function() {
            if (guardPoEditLocked()) {
                return;
            }
            $('#pelangganText').hide();
            $('#pelangganSelect').show();
            $('#editPelangganBtn').hide();
            $('#savePelangganBtn').show();
            $('#cancelPelangganBtn').show();
        });

        $(document).on('click', '#cancelPelangganBtn', function() {
            $('#pelangganSelect').hide().val($('#idpelanggan').val());
            $('#pelangganText').show();
            $('#editPelangganBtn').show();
            $('#savePelangganBtn').hide();
            $('#cancelPelangganBtn').hide();
        });

        $(document).on('click', '#savePelangganBtn', function(e) {
            e.preventDefault();
            if (guardPoEditLocked()) {
                return;
            }

            const idpelanggan = $('#pelangganSelect').val();
            $.ajax({
                type: "post",
                url: '<?= site_url('po/updatePelanggan') ?>',
                data: {
                    [csrfToken]: csrfHash,
                    nopoSha1: $('#nopoOriginalSha1').val(),
                    idpelanggan: idpelanggan
                },
                dataType: "json",
                success: function(response) {
                    if (response.error) {
                        Swal.fire('Error', response.error, 'error');
                        return;
                    }

                    $('#idpelanggan').val(idpelanggan);
                    $('#pelangganText').text(response.namaPelanggan || $('#pelangganSelect option:selected').text()).show();
                    $('#pelangganSelect').hide();
                    $('#editPelangganBtn').show();
                    $('#savePelangganBtn').hide();
                    $('#cancelPelangganBtn').hide();
                    Swal.fire('Berhasil', response.sukses, 'success');
                    tampilDataDetail();
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    handlePoEditAjaxError(xhr, thrownError);
                }
            });
        });

        $(document).on('click', '#saveNopoBtn', function(e) {
            e.preventDefault();
            let newNopo = $('#nopoInput').val();
            let originalNopo = $('#nopo').val();
            let originalNopoSha1 = $('#nopoOriginalSha1').val();
            $.ajax({
                type: "post",
                url: '<?= site_url('po/updateNopo') ?>',
                data: {
                    [csrfToken]: csrfHash,
                    newNopo: newNopo,
                    originalNopo: originalNopo,
                    originalNopoSha1: originalNopoSha1
                },
                dataType: "json",
                success: function(response) {
                    if (response.error) {
                        Swal.fire('Error', response.error, 'error');
                    } else {
                        Swal.fire('Berhasil', response.sukses, 'success').then(() => {
                            // Update originalNopoSha1 with the new value
                            $('#originalNopoSha1').val(response.newNopoSha1);
                            const nextUrl = '/po/edit/' + response.newNopoSha1;
                            window.location.href = (typeof window.treApplyManualPreview === 'function') ? window.treApplyManualPreview(nextUrl) : nextUrl;
                        });
                        $('#nopo').val(newNopo);
                        $('#nopoOriginalSha1').val(response.newNopoSha1);
                        $('#nopoText').text(newNopo);
                        $('#nopoInput').hide();
                        $('#nopoText').show();
                        $('#editNopoBtn').show();
                        $('#saveNopoBtn').hide();
                        $('#cancelNopoBtn').hide();
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    handlePoEditAjaxError(xhr, thrownError);
                }
            });
        });

        $('#tombolEditItem').click(function(e) {
            e.preventDefault();
            syncProdukCombobox();
            let editQty = $('#jml').val();
            let editHarga = $('#harga').val();
            let editTerkirimAwal = $('#terkirim_awal').val();
            let editInvoiceAwal = $('#invoice_awal').val();

            if (parseFloat(editTerkirimAwal || 0) > parseFloat(editQty || 0)) {
                Swal.fire('Error', 'Qty terkirim tidak boleh lebih besar dari Qty PO item.', 'error');
                return;
            }
            if (parseFloat(editInvoiceAwal || 0) > (parseFloat(editTerkirimAwal || 0) * parseFloat(editHarga || 0))) {
                Swal.fire('Error', 'Nilai sudah ditagihkan tidak boleh lebih besar dari QTY terkirim dikali harga satuan.', 'error');
                return;
            }

            $.ajax({
                type: "post",
                url: '<?= site_url('po/editItem') ?>',
                data: {
                    [csrfToken]: csrfHash,
                    iddetail: $('#iddetail').val(),
                    idpelanggan: $('#idpelanggan').val(),
                    tglfaktur: $('#tglfaktur').val(),
                    kodebarang: $('#kodebarang').val(),
                    jml: editQty,
                    harga: editHarga,
                    terkirim_awal: editTerkirimAwal,
                    invoice_awal: editInvoiceAwal
                },
                dataType: "json",
                success: function(response) {
                    if (response.error) {
                        Swal.fire('Error', response.error, 'error');
                        tampilDataDetail();
                        ambilTotalBerat();
                        kosong();
                        $('#tombolSimpanItem').fadeIn();
                        $('#tombolEditItem').fadeOut();
                        $('#tombolBatal').fadeOut();
                    }
                    if (response.sukses) {
                        Swal.fire({
                            'icon': 'success',
                            'title': 'Berhasil',
                            'text': response.sukses
                        });
                        tampilDataDetail();
                        ambilTotalBerat();
                        kosong();
                        $('#tombolSimpanItem').fadeIn();
                        $('#tombolEditItem').fadeOut();
                        $('#tombolBatal').fadeOut();
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    handlePoEditAjaxError(xhr, thrownError);
                }
            });
        });

        // Pakai fungsi buatBarisAlokasi/bacaAlokasi yang didefinisikan di
        // po/datadetail.php (di-load via AJAX ke .tampilDataDetail, jadi
        // fungsinya udah global di halaman ini duluan sebelum tombol Close
        // PO ini bisa diklik).
        function buatBarisAlokasiPo(itemId, daftarPo, qtyAwal) {
            buatBarisAlokasi('#alokasiContainer' + itemId, '#alokasiTotal' + itemId, daftarPo, qtyAwal);
        }

        function bacaAlokasiPo(itemId) {
            return bacaAlokasi('#alokasiContainer' + itemId);
        }

        $(document).on('click', '#tombolClosePo', function() {
            const nopo = $('#nopo').val();
            const idpelanggan = $('#idpelanggan').val();

            const items = [];
            $('#datadetail tbody .btn-close-item').each(function() {
                items.push({
                    id: $(this).data('id'),
                    kode: $(this).data('kode'),
                    qtySisa: parseFloat($(this).data('qty-sisa')) || 0
                });
            });

            if (items.length === 0) {
                Swal.fire('Tidak ada yang perlu ditutup', 'Tidak ada sisa qty yang perlu ditutup di PO ini.', 'info');
                return;
            }

            // Daftar PO tujuan tergantung kode produk masing-masing item
            // (Metode 1 cuma nampilin PO yang udah punya item itu), jadi
            // di-fetch per-item paralel dulu sebelum modalnya dibuka.
            const requests = items.map(item => $.post('/po/daftarPoTujuanClose', {
                [csrfToken]: csrfHash,
                idpelanggan: idpelanggan,
                no_po_kecuali: nopo,
                kode_produk: item.kode
            }, null, 'json'));

            $.when.apply($, requests).then(function(...responses) {
                const hasil = items.length === 1 ? [responses] : responses;
                items.forEach((item, idx) => {
                    item.daftarPo = (hasil[idx][0] || {}).data || [];
                });

                const itemsHtml = items.map(item => `
                    <div class="border rounded p-2 mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong>${$('<div>').text(item.kode).html()}</strong>
                            <span>Sisa: <b>${item.qtySisa.toLocaleString('id-ID')} pcs</b></span>
                        </div>
                        ${item.daftarPo.length === 0 ? '<div class="alert alert-info py-1 mb-2" style="font-size:0.85em;">Belum ada PO lain milik pelanggan ini yang punya item ini -- cuma bisa "Sesuaikan".</div>' : ''}
                        <div id="alokasiContainer${item.id}"></div>
                        <button type="button" class="btn btn-sm btn-outline-primary tambah-alokasi-po" data-item-id="${item.id}"><i class="fa fa-plus"></i> Tambah Baris</button>
                        <div class="mt-1" style="font-size:0.9em;">Total: <b id="alokasiTotal${item.id}">0</b> / ${item.qtySisa.toLocaleString('id-ID')} pcs</div>
                    </div>
                `).join('');

                Swal.fire({
                    title: 'Close PO',
                    width: 650,
                    html: `
                        <div class="text-left" style="max-height:60vh; overflow-y:auto;">
                            <p class="mb-2">Semua item di bawah masih ada sisa qty yang belum dikirim. Isi rincian penutupan buat item yang mau ditutup (boleh dipecah/campur Pindah &amp; Sesuaikan, boleh cuma sebagian). Item yang barisnya dikosongin (klik &times; sampai habis) akan DILEWATI, tetap outstanding seperti biasa.</p>
                            ${itemsHtml}
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Tutup PO Ini',
                    cancelButtonText: 'Batal',
                    focusConfirm: false,
                    didOpen: () => {
                        items.forEach(item => {
                            buatBarisAlokasiPo(item.id, item.daftarPo, item.qtySisa);
                        });
                        Swal.getHtmlContainer().querySelectorAll('.tambah-alokasi-po').forEach(function(btn) {
                            btn.addEventListener('click', function() {
                                const itemId = $(this).data('item-id');
                                const item = items.find(i => i.id === itemId);
                                buatBarisAlokasiPo(itemId, item.daftarPo);
                            });
                        });
                    },
                    preConfirm: () => {
                        const payload = {};
                        for (const item of items) {
                            const alokasi = bacaAlokasiPo(item.id);
                            if (!alokasi) {
                                Swal.showValidationMessage('Item ' + item.kode + ': pastikan tiap baris punya qty > 0, dan PO tujuan dipilih kalau tipenya Pindah');
                                return false;
                            }
                            if (alokasi.length === 0) {
                                // Baris dikosongin semua -- item ini dilewati.
                                continue;
                            }
                            const total = alokasi.reduce((sum, a) => sum + a.qty, 0);
                            if (total - item.qtySisa > 0.0001) {
                                Swal.showValidationMessage('Item ' + item.kode + ': total dialokasikan (' + total.toLocaleString('id-ID') + ') tidak boleh lebih dari sisa ' + item.qtySisa.toLocaleString('id-ID'));
                                return false;
                            }
                            payload[item.id] = {
                                alokasi: alokasi
                            };
                        }
                        if (Object.keys(payload).length === 0) {
                            Swal.showValidationMessage('Isi rincian penutupan minimal untuk 1 item');
                            return false;
                        }
                        return payload;
                    }
                }).then(result => {
                    if (!result.isConfirmed) return;

                    $.post('/po/closePo', {
                        [csrfToken]: csrfHash,
                        nopo: nopo,
                        items: result.value
                    }, function(closeResponse) {
                        if (closeResponse.error) {
                            Swal.fire('Gagal', closeResponse.error, 'error');
                            return;
                        }
                        Swal.fire('Berhasil', closeResponse.sukses, 'success');
                        tampilDataDetail();
                        ambilTotalBerat();
                    }, 'json').fail(function(xhr) {
                        Swal.fire('Gagal', xhr.responseJSON?.error || 'PO gagal ditutup.', 'error');
                    });
                });
            }).fail(function(xhr) {
                Swal.fire('Gagal', xhr.responseJSON?.error || 'Gagal memuat daftar PO tujuan.', 'error');
            });
        });
    });
</script>
<?= $this->endSection('isi') ?>
