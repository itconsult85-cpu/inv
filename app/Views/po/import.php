<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Import PO dari PDF
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>
<button type="button" class="btn btn-warning" onclick="location.href=('/po/data')">
    <i class="fa fa-undo"></i> Kembali
</button>
<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<style>
    .po-import-table .tre-inline-combobox .form-control-sm {
        border-radius: 12px !important;
        min-height: calc(1.8125rem + 2px);
    }

    .po-import-table .tre-inline-combobox-menu {
        font-size: .875rem;
        max-height: 14rem;
    }

    .po-import-table-scroll {
        width: 100%;
        overflow-x: auto;
        overflow-y: visible;
        -webkit-overflow-scrolling: touch;
        padding-bottom: .5rem;
    }

    .po-import-table {
        min-width: 1180px;
        margin-bottom: 1rem;
    }

    .po-import-table.po-import-migrasi-active {
        min-width: 1560px;
    }

    .po-import-table th,
    .po-import-table td {
        vertical-align: middle;
    }

    .po-import-table th:nth-child(1),
    .po-import-table td:nth-child(1) {
        width: 64px;
        min-width: 64px;
    }

    .po-import-table th:nth-child(2),
    .po-import-table td:nth-child(2) {
        min-width: 260px;
    }

    .po-import-table th:nth-child(3),
    .po-import-table td:nth-child(3) {
        min-width: 360px;
    }

    .po-import-table th:nth-child(4),
    .po-import-table td:nth-child(4),
    .po-import-table th:nth-child(5),
    .po-import-table td:nth-child(5),
    .po-import-table th:nth-child(6),
    .po-import-table td:nth-child(6),
    .po-import-table th:nth-child(7),
    .po-import-table td:nth-child(7),
    .po-import-table th:nth-child(8),
    .po-import-table td:nth-child(8) {
        min-width: 180px;
    }

    .po-migrasi-column {
        display: none;
    }

    .po-import-migrasi-active .po-migrasi-column {
        display: table-cell;
    }
</style>

<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert alert-danger">
        <i class="fa fa-exclamation-triangle"></i>
        <?= esc(session()->getFlashdata('error')) ?>
    </div>
<?php endif ?>

<?php if (($mode ?? 'upload') === 'upload') : ?>
    <div class="card">
        <div class="card-body">
            <h5 class="mb-3">Upload File PO</h5>
            <p class="text-muted">
                Upload file PDF PO dari pelanggan/vendor, lalu sistem akan mencoba membaca No PO, tanggal, pelanggan, dan item produk.
                Setelah itu data tetap bisa dicek dan dikoreksi sebelum disimpan.
            </p>

            <form action="<?= site_url('po/import-preview') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="file_po">File PO PDF</label>
                    <input type="file" name="file_po" id="file_po" class="form-control" accept="application/pdf,.pdf" required>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-file-upload"></i> Baca File PO
                </button>
            </form>
        </div>
    </div>
<?php else : ?>
    <?php
    $selectedPelangganId = (string) old('idpelanggan', $matchedPelanggan ?? '');
    $selectedPelangganNama = '';
    foreach (($pelanggan ?? []) as $p) {
        if ((string) $p['pelid'] === $selectedPelangganId) {
            $selectedPelangganNama = (string) $p['pelnama'];
            break;
        }
    }

    $produkLabelByKode = [];
    foreach (($produk ?? []) as $row) {
        $produkLabelByKode[(string) $row['brgkode']] = (string) $row['brgnama'];
    }

    $tglpoServer = old('tglpo', $parsed['tglpo'] ?? date('Y-m-d'));
    $tglpoTimestamp = strtotime((string) $tglpoServer);
    $tglpoTampil = $tglpoTimestamp ? date('d-m-Y', $tglpoTimestamp) : (string) $tglpoServer;
    ?>
    <form action="<?= site_url('po/import-simpan') ?>" method="post">
        <?= csrf_field() ?>

        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i>
            Cek ulang hasil baca PDF di bawah ini. Produk di sistem dicocokkan dari nama produk; kalau belum cocok otomatis, pilih produk secara manual dulu sebelum disimpan.
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <strong>Data Header PO</strong>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="nopo">No PO</label>
                            <input type="text" name="nopo" id="nopo" class="form-control" value="<?= esc(old('nopo', $parsed['nopo'] ?? '')) ?>" required>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="tglpo_display">Tanggal PO</label>
                            <input type="text" id="tglpo_display" class="form-control" value="<?= esc($tglpoTampil) ?>" placeholder="dd-MM-YYYY" inputmode="numeric" required>
                            <input type="hidden" name="tglpo" id="tglpo" value="<?= esc($tglpoServer) ?>">
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="pelangganInput">Pelanggan</label>
                            <div class="input-group mb-3 tre-inline-combobox tre-inline-combobox-solo" id="pelangganCombobox">
                                <input type="text" id="pelangganInput" class="form-control" value="<?= esc($selectedPelangganNama) ?>" placeholder="-- Pilih Pelanggan --" autocomplete="off" required>
                                <input type="hidden" name="idpelanggan" id="idpelanggan" value="<?= esc($selectedPelangganId) ?>">
                                <div class="tre-inline-combobox-menu" id="pelangganComboboxMenu"></div>
                            </div>
                            <?php if (!empty($parsed['pelanggan_hint'])) : ?>
                                <small class="text-muted">Terbaca dari PDF: <?= esc($parsed['pelanggan_hint']) ?></small>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
                <div class="custom-control custom-checkbox mt-2">
                    <input type="checkbox" class="custom-control-input" id="poMigrasi" name="po_migrasi" value="1" <?= old('po_migrasi') ? 'checked' : '' ?>>
                    <label class="custom-control-label font-weight-bold" for="poMigrasi">PO sudah berjalan / migrasi</label>
                    <small class="form-text text-muted">Centang kalau PO ini sudah berjalan sebelum dicatat di sistem, lalu isi qty yang sudah terkirim sebelumnya.</small>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <strong>Detail Item PO</strong>
            </div>
            <div class="card-body">
                <div class="table-responsive po-import-table-scroll">
                    <table class="table table-bordered table-sm po-import-table" id="poImportTable">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th>Deskripsi dari PDF</th>
                            <th style="width: 28%;">Produk di Sistem</th>
                            <th style="width: 12%;">Qty</th>
                            <th style="width: 12%;" class="po-migrasi-column">QTY terkirim</th>
                            <th style="width: 12%;" class="po-migrasi-column">Nilai sudah ditagihkan</th>
                            <th style="width: 12%;">UoM</th>
                            <th style="width: 14%;">Harga Satuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)) : ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    Item belum terbaca otomatis. Silakan kembali dan input PO secara manual.
                                </td>
                            </tr>
                        <?php endif ?>
                        <?php foreach (($items ?? []) as $i => $item) : ?>
                            <tr>
                                <td class="text-center"><?= $i + 1 ?></td>
                                <td>
                                    <?= esc($item['description']) ?>
                                    <?php if (!empty($item['product_code_hint'])) : ?>
                                        <br><small class="text-muted">Hint dari PDF: <?= esc($item['product_code_hint']) ?></small>
                                    <?php endif ?>
                                </td>
                                <td>
                                    <?php
                                    $selectedKode = (string) old('kodebarang.' . $i, $item['matched_brgkode'] ?? '');
                                    $selectedProdukLabel = $produkLabelByKode[$selectedKode] ?? '';
                                    ?>
                                    <div class="input-group tre-inline-combobox tre-inline-combobox-solo po-import-product-combobox" id="produkCombobox<?= $i ?>">
                                        <input type="text" class="form-control form-control-sm po-import-product-input" id="produkInput<?= $i ?>" value="<?= esc($selectedProdukLabel) ?>" placeholder="-- Pilih Produk --" autocomplete="off" required>
                                        <input type="hidden" name="kodebarang[]" id="kodebarang<?= $i ?>" value="<?= esc($selectedKode) ?>">
                                        <div class="tre-inline-combobox-menu" id="produkComboboxMenu<?= $i ?>"></div>
                                    </div>
                                </td>
                                <td>
                                    <input type="number" min="1" step="1" name="qty[]" class="form-control form-control-sm text-right" value="<?= esc((int) ($item['qty'] ?? 0)) ?>" required>
                                </td>
                                <td class="po-migrasi-column">
                                    <input type="number" min="0" step="1" name="terkirim_awal[]" class="form-control form-control-sm text-right po-import-terkirim-awal" value="<?= esc(old('terkirim_awal.' . $i, 0)) ?>">
                                </td>
                                <td class="po-migrasi-column">
                                    <input type="number" min="0" step="1" name="invoice_awal[]" class="form-control form-control-sm text-right po-import-invoice-awal" value="<?= esc(old('invoice_awal.' . $i, 0)) ?>">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm" value="<?= esc($item['uom'] ?? 'Pcs') ?>" readonly>
                                </td>
                                <td>
                                    <input type="number" min="0" step="0.01" name="harga[]" class="form-control form-control-sm text-right" value="<?= esc((float) ($item['matched_harga'] ?? 0)) ?>">
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                    </table>
                </div>

                <button type="submit" class="btn btn-success" <?= empty($items) ? 'disabled' : '' ?>>
                    <i class="fa fa-save"></i> Simpan PO
                </button>
                <a href="<?= site_url('po/import') ?>" class="btn btn-secondary">Upload Ulang</a>
            </div>
        </div>
    </form>
    <script>
        const pelangganOptions = <?= json_encode(array_map(static function ($row) {
                                    return [
                                        'id' => (string) $row['pelid'],
                                        'text' => (string) $row['pelnama'],
                                    ];
                                }, $pelanggan ?? []), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
        const produkOptions = <?= json_encode(array_map(static function ($row) {
                                $kode = (string) $row['brgkode'];
                                $nama = (string) $row['brgnama'];
                                return [
                                    'id' => $kode,
                                    'text' => $nama,
                                    'value' => $nama,
                                    'code' => $kode,
                                ];
                            }, $produk ?? []), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;

        function normalizeImportCombobox(value) {
            return String(value || '').trim().toLowerCase();
        }

        function formatImportTanggalTampil(value) {
            const tanggal = String(value || '').trim();
            const isoMatch = tanggal.match(/^(\d{4})-(\d{2})-(\d{2})$/);

            if (isoMatch) {
                return `${isoMatch[3]}-${isoMatch[2]}-${isoMatch[1]}`;
            }

            return tanggal;
        }

        function formatImportTanggalServer(value) {
            const tanggal = String(value || '').trim().replace(/\//g, '-');
            const tampilMatch = tanggal.match(/^(\d{2})-(\d{2})-(\d{4})$/);

            if (tampilMatch) {
                return `${tampilMatch[3]}-${tampilMatch[2]}-${tampilMatch[1]}`;
            }

            return tanggal;
        }

        function syncImportTanggalPo() {
            const tanggalServer = formatImportTanggalServer($('#tglpo_display').val());
            $('#tglpo').val(tanggalServer);
            $('#tglpo_display').val(formatImportTanggalTampil(tanggalServer));
        }

        function syncImportMigrasiColumn() {
            const aktif = $('#poMigrasi').is(':checked');
            $('#poImportTable').toggleClass('po-import-migrasi-active', aktif);

            if (!aktif) {
                $('.po-import-terkirim-awal').val('0');
                $('.po-import-invoice-awal').val('0');
            }
        }

        function matchImportOption(options, value) {
            const keyword = normalizeImportCombobox(value);
            return options.find(function(option) {
                return normalizeImportCombobox(option.text) === keyword || normalizeImportCombobox(option.value) === keyword;
            });
        }

        $(function() {
            const pelangganCombobox = window.treInitInlineCombobox({
                box: '#pelangganCombobox',
                input: '#pelangganInput',
                hidden: '#idpelanggan',
                menu: '#pelangganComboboxMenu',
                options: pelangganOptions
            });
            $('#tglpo_display').on('change blur', syncImportTanggalPo);
            $('#poMigrasi').on('change', syncImportMigrasiColumn);
            syncImportMigrasiColumn();

            $('.po-import-product-combobox').each(function() {
                const $box = $(this);
                const $input = $box.find('.po-import-product-input');
                const $hidden = $box.find('input[type="hidden"]');
                const $menu = $box.find('.tre-inline-combobox-menu');
                const combobox = window.treInitInlineCombobox({
                    box: '#' + $box.attr('id'),
                    input: '#' + $input.attr('id'),
                    hidden: '#' + $hidden.attr('id'),
                    menu: '#' + $menu.attr('id'),
                    options: produkOptions,
                    onSelect: function(option) {
                        $input.val(option.text);
                    }
                });

                $input.on('blur', function() {
                    window.setTimeout(function() {
                        if (combobox && typeof combobox.sync === 'function') {
                            combobox.sync();
                        }

                        const match = matchImportOption(produkOptions, $input.val());
                        if (match) {
                            $hidden.val(match.id);
                            $input.val(match.text);
                        }
                    }, 140);
                });
            });

            $('form[action="<?= site_url('po/import-simpan') ?>"]').on('submit', function(event) {
                syncImportTanggalPo();

                if (pelangganCombobox && typeof pelangganCombobox.sync === 'function') {
                    pelangganCombobox.sync();
                }

                const pelangganMatch = matchImportOption(pelangganOptions, $('#pelangganInput').val());
                if (pelangganMatch) {
                    $('#idpelanggan').val(pelangganMatch.id);
                    $('#pelangganInput').val(pelangganMatch.text);
                }

                let hasEmptyProduct = false;
                let hasInvalidMigrasiQty = false;
                let hasInvalidMigrasiInvoiceQty = false;
                $('.po-import-product-combobox').each(function() {
                    const $box = $(this);
                    const $input = $box.find('.po-import-product-input');
                    const $hidden = $box.find('input[type="hidden"]');
                    const match = matchImportOption(produkOptions, $input.val());
                    if (match) {
                        $hidden.val(match.id);
                        $input.val(match.text);
                    }
                    if (!$hidden.val()) {
                        hasEmptyProduct = true;
                    }
                });

                $('.po-import-terkirim-awal').each(function() {
                    const $row = $(this).closest('tr');
                    const qty = parseFloat($row.find('input[name="qty[]"]').val() || 0);
                    const terkirimAwal = parseFloat($(this).val() || 0);
                    const invoiceAwal = parseFloat($row.find('.po-import-invoice-awal').val() || 0);
                    const harga = parseFloat($row.find('input[name="harga[]"]').val() || 0);
                    if (terkirimAwal < 0 || terkirimAwal > qty) {
                        hasInvalidMigrasiQty = true;
                    }
                    if (invoiceAwal < 0 || invoiceAwal > (terkirimAwal * harga)) {
                        hasInvalidMigrasiInvoiceQty = true;
                    }
                });

                if (!$('#idpelanggan').val()) {
                    event.preventDefault();
                    Swal.fire('Pesan', 'Pelanggan harus dipilih dari daftar.', 'warning');
                    $('#pelangganInput').focus();
                    return;
                }

                if (hasEmptyProduct) {
                    event.preventDefault();
                    Swal.fire('Pesan', 'Semua produk harus dipilih dari daftar produk di sistem.', 'warning');
                    return;
                }

                if (hasInvalidMigrasiQty) {
                    event.preventDefault();
                    Swal.fire('Pesan', 'Qty terkirim sebelum sistem tidak boleh lebih besar dari Qty PO.', 'warning');
                    return;
                }

                if (hasInvalidMigrasiInvoiceQty) {
                    event.preventDefault();
                    Swal.fire('Pesan', 'Nilai sudah ditagihkan tidak boleh lebih besar dari QTY terkirim dikali harga satuan.', 'warning');
                }
            });
        });
    </script>
<?php endif ?>

<?= $this->endSection('isi') ?>
