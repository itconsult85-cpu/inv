<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Input PO
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>

<button type="button" class="btn btn-warning" onclick="location.href=('/po/data')">
    <i class="fa fa-undo"></i> Kembali
</button>

<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>

<style>
    .po-migrasi-draft-column {
        display: none;
    }

    .po-migrasi-draft-active .po-migrasi-draft-column {
        display: table-cell;
    }

    .po-entry-section {
        border-top: 1px solid #e8edf4;
        padding: 1.25rem 1.25rem 1.1rem;
    }

    .po-entry-section:first-of-type {
        border-top: 0;
        padding-top: .25rem;
    }

    .po-section-heading {
        color: #111827;
        font-size: 1rem;
        font-weight: 800;
        margin-bottom: 1rem;
    }

    .po-section-muted {
        color: #6b7280;
        font-size: .86rem;
        margin-top: .2rem;
    }

    .po-migrasi-box {
        align-items: flex-start;
        background: transparent;
        border: 0;
        display: flex;
        padding: .35rem 0 0;
    }

    .po-migrasi-box-compact {
        min-height: 0;
        padding: .35rem 0 0;
    }

    .po-status-info-toggle {
        align-items: center;
        background: transparent;
        border: 0;
        color: #16869a;
        display: inline-flex;
        font-size: .82rem;
        font-weight: 600;
        gap: .35rem;
        margin-top: .45rem;
        opacity: .72;
        padding: 0;
    }

    .po-status-info-toggle:hover,
    .po-status-info-toggle:focus {
        opacity: 1;
    }

    .po-status-info-toggle:focus {
        outline: 0;
        text-decoration: underline;
    }

    .po-status-info-toggle i {
        font-size: .72rem;
        transition: transform .18s ease;
    }

    .po-status-info-toggle.is-open i {
        transform: rotate(180deg);
    }

    .po-status-info {
        display: none;
        font-size: .82rem;
        line-height: 1.45;
        margin-top: .35rem;
    }

    .po-item-entry-grid .form-group {
        margin-bottom: .9rem;
    }

    .po-migrasi-field-break {
        flex-basis: 100%;
        height: 0;
        padding: 0;
    }

    .po-item-actions .input-group {
        align-items: center;
        gap: .45rem;
    }

    .po-item-actions .btn {
        align-items: center;
        border-radius: 12px !important;
        display: inline-flex;
        height: 42px;
        justify-content: center;
        margin-right: 0 !important;
        width: 42px;
    }

    .po-draft-section {
        padding-bottom: 1.25rem;
    }

    .po-draft-footer {
        display: flex;
        justify-content: flex-end;
        margin-top: .9rem;
    }

    .po-draft-table-wrap {
        overflow-x: auto;
        width: 100%;
    }

    .po-draft-table {
        margin-bottom: 0;
        min-width: 1100px;
    }

    .po-draft-table th,
    .po-draft-table td {
        vertical-align: middle;
    }

    .po-draft-table h2 {
        font-size: 1.75rem;
        margin: .35rem 0;
    }

    @media (max-width: 768px) {
        .po-entry-section {
            padding-left: .9rem;
            padding-right: .9rem;
        }

        .po-draft-footer .btn {
            width: 100%;
        }
    }
</style>
<section class="po-entry-section">
    <div class="po-section-heading">Data Header PO</div>
    <div class="row po-header-grid align-items-start">
        <div class="col-lg-2">
            <div class="form-group">
                <label for="tglpo">Tanggal PO</label>
                <input type="text" id="tglpo_display" class="form-control" value="<?= date('d-m-Y') ?>" placeholder="dd-MM-YYYY" inputmode="numeric">
                <input type="hidden" name="tglpo" id="tglpo" value="<?= date('Y-m-d') ?>">
            </div>
        </div>
        <div class="col-lg-3">
            <div class="form-group">
                <label for="nopo">No. PO</label>
                <input type="text" name="nopo" id="nopo" class="form-control">
            </div>
        </div>
        <div class="col-lg-4">
            <div class="form-group">
                <label for="namapelanggan">Pelanggan</label>
                <div class="input-group mb-3 tre-inline-combobox" id="pelangganCombobox">
                    <input type="text" class="form-control" placeholder="Nama Pelanggan" name="namapelanggan" id="namapelanggan" autocomplete="off">
                    <input type="hidden" name="idpelanggan" id="idpelanggan">
                    <div class="input-group-append">
                        <?php if (\App\Libraries\AccessControl::can('master.pelanggan.create')) :  ?>
                            <button class="btn btn-outline-success" type="button" id="tombolTambahPelanggan" title="Tambah Pelanggan">
                                <i class="fa fa-plus-square"></i>
                            </button>
                        <?php endif ?>
                    </div>
                    <div class="tre-inline-combobox-menu" id="pelangganComboboxMenu"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="form-group">
                <label>Status PO</label>
                <div class="po-migrasi-box po-migrasi-box-compact">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="poMigrasi" value="1">
                        <label class="custom-control-label font-weight-bold" for="poMigrasi">PO sudah berjalan / migrasi</label>
                        <button type="button" class="po-status-info-toggle" id="poStatusInfoToggle" aria-controls="poStatusInfo" aria-expanded="false">
                            <span>More information</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="po-section-muted po-status-info" id="poStatusInfo" aria-hidden="true">Centang kalau PO ini sudah berjalan sebelum dicatat di sistem.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="po-entry-section">
    <div class="po-section-heading">Detail Item Produk</div>
    <div class="row po-item-entry-grid">
    <div class="col-lg-2">
        <div class="form-group">
            <label for="kodebarang">Kode Produk</label>
            <div class="input-group mb-3 tre-inline-combobox tre-inline-combobox-solo" id="produkCombobox">
                <input type="text" class="form-control" name="kodebarang" id="kodebarang" autocomplete="off">
                <input type="hidden" name="kodebarang_pilih" id="kodebarang_pilih">
                <input type="hidden" class="form-control" name="idbarang" id="idbarang">
                <input type="hidden" class="form-control" name="idgudang" id="idgudang">
                <div class="tre-inline-combobox-menu" id="produkComboboxMenu"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-2">
        <div class="form-group">
            <label for="namabarang">Nama Produk</label>
            <div class="mb-3">
                <input type="text" class="form-control" name="namabarang" id="namabarang" readonly>
            </div>
        </div>
    </div>
    <div class="col-lg-2">
        <div class="form-group">
            <label for="berat">Berat Satuan</label>
            <div class="mb-3">
                <input type="number" class="form-control" name="berat" id="berat" readonly>
            </div>
        </div>
    </div>
    <div class="col-lg-2">
        <div class="form-group">
            <label for="stok">Stok</label>
            <div class="mb-3">
                <input type="number" class="form-control" name="stok" id="stok" readonly>
                <input type="hidden" class="form-control" name="harga" id="harga" readonly>
            </div>
        </div>
    </div>
    <div class="col-lg-2">
        <div class="form-group">
            <label for="jml">Qty</label>
            <div class="mb-3">
                <input type="number" class="form-control" name="jml" id="jml" value="1">
            </div>
        </div>
    </div>
    <div class="w-100 po-migrasi-field po-migrasi-field-break" style="display:none;"></div>
    <div class="col-lg-2 po-migrasi-field" style="display:none;">
        <div class="form-group">
            <label for="terkirim_awal">QTY terkirim</label>
            <div class="mb-3">
                <input type="number" min="0" class="form-control" name="terkirim_awal" id="terkirim_awal" value="0">
            </div>
        </div>
    </div>
    <div class="col-lg-2 po-migrasi-field" style="display:none;">
        <div class="form-group">
            <label for="invoice_awal">Nilai sudah ditagihkan</label>
            <div class="mb-3">
                <input type="number" min="0" class="form-control" name="invoice_awal" id="invoice_awal" value="0">
            </div>
        </div>
    </div>
    <div class="col-lg-2 po-item-actions">
        <div class="form-group">
            <label>&nbsp;</label>
            <div class="input-group mb-3">
                <button type="button" class="btn btn-success" title="Simpan Item" id="tombolSimpanItem">
                    <i class="fa fa-save"></i>
                </button>
                <button type="button" class="btn btn-sm btn-warning" title="Reload Data" id="tombolReload">
                    <i class="fa fa-sync-alt"></i>
                </button>
            </div>
        </div>
    </div>
    </div>
</section>

<section class="po-entry-section po-draft-section">
    <div class="po-section-heading">Draft Item PO</div>
    <div class="tampilDataTemp po-draft-table-wrap"></div>
    <div class="po-draft-footer">
        <button type="button" class="btn btn-sm btn-success" id="tombolSelesaiTransaksi">
            <i class="fa fa-save"></i> Selesai Transaksi
        </button>
    </div>
</section>
<div class="viewmodal" style="display: none;"></div>

<script>
    let csrfToken = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';
    const pelangganOptions = <?= json_encode(array_map(static function ($row) {
                                    return [
                                        'id' => (string) $row['pelid'],
                                        'text' => $row['pelnama'],
                                    ];
                                }, $datapelanggan ?? []), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const produkOptions = <?= json_encode(array_map(static function ($row) {
                                $kode = (string) $row['brgkode'];
                                $nama = (string) $row['brgnama'];
                                return [
                                    'id' => $kode,
                                    'text' => $kode . ' - ' . $nama,
                                    'value' => $kode,
                                ];
                            }, $databarang ?? []), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    let pelangganCombobox = null;
    let produkCombobox = null;

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

    function syncTanggalPo() {
        const tanggalServer = formatTanggalServer($('#tglpo_display').val());
        $('#tglpo').val(tanggalServer);
        $('#tglpo_display').val(formatTanggalTampil(tanggalServer));
    }

    function normalizeComboboxValue(value) {
        return String(value || '').trim().toLowerCase();
    }

    function syncPelangganCombobox() {
        if (pelangganCombobox && typeof pelangganCombobox.sync === 'function') {
            pelangganCombobox.sync();
        }
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

    function renderEmptyDraft() {
        const emptyTable = `
            <table class="table table-sm table-hover table-bordered po-draft-table" style="width:100%">
                <thead>
                    <tr>
                        <th colspan="11" style="text-align: right;">
                            <h2 style="font-weight:bold">Total : 0 Pcs</h2>
                            <input type="hidden" id="qty" value="0">
                            <input type="hidden" id="hargapo" value="0">
                        </th>
                    </tr>
                    <tr>
                        <th style="text-align: center;">No</th>
                        <th style="text-align: center;">Kode Produk</th>
                        <th style="text-align: center;">Nama Produk</th>
                        <th style="text-align: center;">Berat Satuan</th>
                        <th style="text-align: center;">Jumlah</th>
                        <th class="po-migrasi-draft-column" style="text-align: center;">QTY terkirim</th>
                        <th class="po-migrasi-draft-column" style="text-align: center;">Nilai sudah ditagihkan</th>
                        <th class="po-migrasi-draft-column" style="text-align: center;">Sisa Awal</th>
                        <th style="text-align: center;">Subtotal (KG)</th>
                        <th style="text-align: center;">Harga</th>
                        <th style="text-align: center;">#</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="11" class="text-center text-muted">Belum ada item PO.</td>
                    </tr>
                </tbody>
            </table>`;

        $('.tampilDataTemp').html(emptyTable);
        $('#qty').val(0);
        $('#hargapo').val(0);
        syncPoMigrasiField();
    }

    function handlePoAjaxError(xhr, thrownError, targetSelector = null) {
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

        console.error('PO AJAX error', {
            status: xhr ? xhr.status : null,
            thrownError: thrownError,
            response: responseText.substring(0, 500)
        });

        if (targetSelector) {
            $(targetSelector).html('<div class="alert alert-warning mb-0">' + message + '</div>');
            return;
        }

        showBootstrapModal('Request Gagal', message, 'warning');
    }

    function kosong() {
        $('#kodebarang').val('');
        $('#kodebarang_pilih').val('');
        $('#berat').val('');
        $('#namabarang').val('');
        $('#idbarang').val('');
        $('#idgudang').val('');
        $('#stok').val('');
        $('#harga').val('');
        $('#jml').val('1');
        $('#terkirim_awal').val('0');
        $('#invoice_awal').val('0');
        $('#kodebarang').focus();

    }

    function initPoStatusInfoToggle() {
        const toggle = $('#poStatusInfoToggle');
        const info = $('#poStatusInfo');

        info.hide().attr('aria-hidden', 'true');
        toggle.off('click.poStatusInfo').on('click.poStatusInfo', function() {
            const isOpen = toggle.hasClass('is-open');
            toggle.toggleClass('is-open', !isOpen);
            toggle.attr('aria-expanded', isOpen ? 'false' : 'true');
            toggle.find('span').text(isOpen ? 'More information' : 'Hide information');
            info.attr('aria-hidden', isOpen ? 'true' : 'false').stop(true, true).slideToggle(160);
        });
    }
    function syncPoMigrasiField() {
        const aktif = $('#poMigrasi').is(':checked');
        $('.po-migrasi-field').toggle(aktif);
        $('.po-draft-table').toggleClass('po-migrasi-draft-active', aktif);
        if (!aktif) {
            $('#terkirim_awal').val('0');
            $('#invoice_awal').val('0');
        }
    }

    function simpanItem() {
        syncTanggalPo();
        syncPelangganCombobox();
        syncProdukCombobox();
        let nopo = $('#nopo').val();
        let tglpo = $('#tglpo').val();
        let kodebarang = $('#kodebarang').val();
        let idpelanggan = $('#idpelanggan').val();
        let namabarang = $('#namabarang').val();
        let berat = $('#berat').val();
        let jml = $('#jml').val();
        let harga = $('#harga').val();
        let terkirimAwal = $('#poMigrasi').is(':checked') ? $('#terkirim_awal').val() : 0;
        let invoiceAwal = $('#poMigrasi').is(':checked') ? $('#invoice_awal').val() : 0;

        if (kodebarang.length == 0) {
            showBootstrapModal('Error', 'Kode Barang harus di inputkan', 'error');
            kosong();
        } else if (parseFloat(terkirimAwal || 0) > parseFloat(jml || 0)) {
            showBootstrapModal('Error', 'Qty terkirim sebelum sistem tidak boleh lebih besar dari Qty PO item.', 'error');
        } else if (parseFloat(invoiceAwal || 0) > (parseFloat(terkirimAwal || 0) * parseFloat(harga || 0))) {
            showBootstrapModal('Error', 'Nilai sudah ditagihkan tidak boleh lebih besar dari QTY terkirim dikali harga satuan.', 'error');
        } else {
            $.ajax({
                type: "post",
                url: '<?= site_url('po/simpanItem') ?>',
                data: {
                    [csrfToken]: csrfHash,
                    nopo: nopo,
                    tglpo: tglpo,
                    kodebarang: kodebarang,
                    namabarang: namabarang,
                    idpelanggan: idpelanggan,
                    berat: berat,
                    jml: jml,
                    harga: harga,
                    terkirim_awal: terkirimAwal,
                    invoice_awal: invoiceAwal
                },
                dataType: "json",
                success: function(response) {
                    if (response.error1) {
                        showBootstrapModal('Error', response.error1, 'error');
                        kosong();
                    }
                    if (response.error2) {
                        showBootstrapModal('Error', response.error2, 'error');
                        kosong();
                    }
                    if (response.sukses) {
                        showBootstrapModal('Berhasil', response.sukses, 'success');
                        // Beri kesempatan handler global menyimpan token CSRF
                        // terbaru sebelum request pemuatan ulang draft dijalankan.
                        setTimeout(tampilDataTemp, 0);
                        kosong();
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    handlePoAjaxError(xhr, thrownError);
                }
            });
        }
    }

    function ambilDataBarang() {
        syncProdukCombobox();
        let kodebarang = $('#kodebarang').val();
        let harga = $('#harga').val();
        if (kodebarang.length == 0) {
            showBootstrapModal('Error', 'Kode Barang harus di inputkan', 'error');
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
                        showBootstrapModal('Error', response.error, 'error');
                        kosong();
                    }

                    if (response.sukses) {
                        let data = response.sukses;

                        $('#stok').val(data.stok);
                        $('#namabarang').val(data.namabarang);
                        $('#berat').val(data.berat);
                        $('#harga').val(data.harga);
                        $('#jml').focus();
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    handlePoAjaxError(xhr, thrownError);
                }
            });
        }
    }

    function tampilDataTemp(callback) {
        let nopo = $('#nopo').val().trim();

        if (!nopo) {
            renderEmptyDraft();
            if (typeof callback === 'function') {
                callback(0);
            }
            return;
        }

        $.ajax({
            type: "post",
            url: '<?= site_url('po/tampilDataTemp') ?>',
            data: {
                [csrfToken]: csrfHash,
                nopo: nopo
            },
            dataType: "json",
            beforeSend: function() {
                $('.tampilDataTemp').html("<i class='fa fa-spin fa-spinner'></i>");
            },
            success: function(response) {
                if (response.data) {
                    $('.tampilDataTemp').html(response.data);
                    syncPoMigrasiField();
                }
                if (typeof callback === 'function') {
                    callback(response.jumlah || 0);
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                handlePoAjaxError(xhr, thrownError, '.tampilDataTemp');
            }
        });
    }

    let nopoDicek = '';

    function cekDraftLama() {
        let nopo = $('#nopo').val().trim();
        if (nopo === '' || nopo === nopoDicek) {
            return;
        }
        nopoDicek = nopo;
        tampilDataTemp(function(jumlah) {
            if (jumlah > 0) {
                showBootstrapModal({
                    title: 'Draft Lama Ditemukan',
                    text: 'No. PO "' + nopo + '" masih punya ' + jumlah + ' item draft yang belum selesai disimpan. Lanjutkan draft ini atau mulai baru (draft lama dihapus)?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Lanjutkan Draft',
                    cancelButtonText: 'Mulai Baru',
                    reverseButtons: true
                }).then(function(result) {
                    if (!result.isConfirmed) {
                        $.ajax({
                            type: "post",
                            url: '<?= site_url('po/hapusDraftPo') ?>',
                            data: {
                                [csrfToken]: csrfHash,
                                nopo: nopo
                            },
                            dataType: "json",
                            success: function() {
                                tampilDataTemp();
                            },
                            error: function(xhr, ajaxOptions, thrownError) {
                                handlePoAjaxError(xhr, thrownError, '.tampilDataTemp');
                            }
                        });
                    }
                });
            }
        });
    }

    $(document).ready(function() {
        pelangganCombobox = window.treInitInlineCombobox({
            box: '#pelangganCombobox',
            input: '#namapelanggan',
            hidden: '#idpelanggan',
            menu: '#pelangganComboboxMenu',
            options: pelangganOptions
        });
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
        $(document).off('tre:pelangganAdded.poCombobox').on('tre:pelangganAdded.poCombobox', function(event, pelanggan) {
            if (pelangganCombobox && typeof pelangganCombobox.addOption === 'function') {
                pelangganCombobox.addOption(pelanggan, true);
            }
        });
        tampilDataTemp();

        $('#nopo').on('blur', cekDraftLama);
        $('#tglpo_display').on('change blur', syncTanggalPo);
        $('#poMigrasi').on('change', syncPoMigrasiField);
        syncPoMigrasiField();
        initPoStatusInfoToggle();
        $('#tombolReload').click(function(e) {
            e.preventDefault();
            tampilDataTemp();
            kosong();
        });

        $('#tombolTambahPelanggan').click(function(e) {
            e.preventDefault();
            $.ajax({
                url: '<?= site_url('pelanggan/formtambah') ?>',
                dataType: "json",
                success: function(response) {
                    if (response.data) {
                        $('.viewmodal').html(response.data).show();
                        $('#modaltambahpelanggan').modal('show');
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    handlePoAjaxError(xhr, thrownError);
                }
            });
        });

        $('#kodebarang').keydown(function(e) {
            if (e.keyCode == 13) {
                if (e.isDefaultPrevented()) {
                    return;
                }
                e.preventDefault();
                syncProdukCombobox();
                ambilDataBarang();
            }
        });

        $('#tombolSimpanItem').click(function(e) {
            e.preventDefault();
            simpanItem();
        });

        $('#tombolSelesaiTransaksi').click(function(e) {
            e.preventDefault();
            syncTanggalPo();
            syncPelangganCombobox();
            syncProdukCombobox();
            let nopo = $('#nopo').val();
            let idpelanggan = $('#idpelanggan').val();
            let jml = $('#jml').val();
            let kodebarang = $('#kodebarang').val();
            let namabarang = $('#namabarang').val();
            let idbarang = $('#idbarang').val();
            let idgudang = $('#idgudang').val();
            let qty = $('#qty').val();
            let hargapo = $('#hargapo').val();

            if (nopo.length == 0) {
                showBootstrapModal({
                    title: 'Pesan',
                    icon: 'warning',
                    text: 'Maaf No PO tidak boleh kosong'
                })
            } else if (idpelanggan.length == 0) {
                showBootstrapModal({
                    title: 'Pesan',
                    icon: 'warning',
                    text: 'Maaf data pelanggan tidak boleh kosong'
                })
            } else {
                showBootstrapModal({
                    title: 'Selesai Transaksi',
                    text: "Yakin transaksi ini di simpan ?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Simpan Data Transaksi !',
                    cancelButtonText: 'Tidak'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "post",
                            url: '<?= site_url('po/selesaiTransaksi') ?>',
                            data: {
                                [csrfToken]: csrfHash,
                                nopo: nopo,
                                tglpo: $('#tglpo').val(),
                                idpelanggan: $('#idpelanggan').val(),
                                kodebarang: $('#kodebarang').val(),
                                namabarang: $('#namabarang').val(),
                                idbarang: $('#idbarang').val(),
                                idgudang: $('#idgudang').val(),
                                qty: $('#qty').val(),
                                hargapo: $('#hargapo').val(),
                                po_migrasi: $('#poMigrasi').is(':checked') ? 1 : 0,
                            },
                            dataType: "json",
                            success: function(response) {
                                if (response.error) {
                                    showBootstrapModal({
                                        title: 'Error',
                                        icon: 'error',
                                        text: response.error
                                    });
                                }

                                if (response.sukses) {
                                    showBootstrapModal({
                                        title: 'Berhasil',
                                        icon: 'success',
                                        text: response.sukses
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            window.location.reload();
                                        }
                                    })
                                }
                            },
                            error: function(xhr, ajaxOptions, thrownError) {
                                handlePoAjaxError(xhr, thrownError);
                            }
                        });
                    }
                })
            }
        });
    });
</script>

<?= $this->endSection('isi') ?>
