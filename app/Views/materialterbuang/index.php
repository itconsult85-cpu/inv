<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Material Terbuang
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>
Estimasi waste material berdasarkan outstanding, Berat Material Terpakai, dan Wise per produk
<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<script src="<?= base_url() ?>/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

<style>
    .forecast-card { border-left: 4px solid; min-height: 88px; }
    .forecast-card .value { font-size: 1.65rem; font-weight: 700; line-height: 1; }
    .forecast-card .label { color: #6c757d; margin-top: .5rem; }
    .forecast-card.total { border-color: #dc3545; }
    .forecast-card.belum { border-color: #ffc107; }
    .forecast-card.vendor { border-color: #6f42c1; }
    #peringatanData, #vendorSupplyData, #fullBeliJadiData { max-height: 210px; overflow-y: auto; }
    #tabelWaste th, #tabelWaste td { vertical-align: middle; }
    .angka { text-align: right; white-space: nowrap; }
    .collapsible-alert .alert-toggle { color: inherit; text-decoration: none; }
    .collapsible-alert .alert-toggle:focus { box-shadow: none; }
    .collapsible-alert .alert-toggle-icon { transition: transform .2s ease; }
    .collapsible-alert .alert-toggle[aria-expanded="true"] .alert-toggle-icon { transform: rotate(180deg); }
</style>

<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col-md-4 mb-2">
                <label for="filterPelangganInput">Pelanggan</label>
                <div class="input-group tre-inline-combobox tre-inline-combobox-solo" id="pelangganCombobox">
                    <input type="text" id="filterPelangganInput" class="form-control" placeholder="-- Semua Pelanggan --" autocomplete="off">
                    <input type="hidden" id="filterPelanggan">
                    <div class="tre-inline-combobox-menu" id="pelangganComboboxMenu"></div>
                </div>
            </div>
            <div class="col-md-4 mb-2">
                <label for="filterProdukInput">Produk</label>
                <div class="input-group tre-inline-combobox tre-inline-combobox-solo" id="produkCombobox">
                    <input type="text" id="filterProdukInput" class="form-control" placeholder="-- Semua Produk --" autocomplete="off">
                    <input type="hidden" id="filterProduk">
                    <div class="tre-inline-combobox-menu" id="produkComboboxMenu"></div>
                </div>
            </div>
            <div class="col-md-4 mb-2">
                <label for="filterMaterialInput">Material</label>
                <div class="input-group tre-inline-combobox tre-inline-combobox-solo" id="materialCombobox">
                    <input type="text" id="filterMaterialInput" class="form-control" placeholder="-- Semua Material --" autocomplete="off">
                    <input type="hidden" id="filterMaterial">
                    <div class="tre-inline-combobox-menu" id="materialComboboxMenu"></div>
                </div>
            </div>
        </div>
        <div class="d-flex flex-wrap justify-content-between align-items-center mt-2">
            <small class="text-muted" id="waktuHitung">Belum dihitung</small>
            <div>
                <button type="button" id="btnReset" class="btn btn-warning mr-1">
                    <i class="fas fa-sync-alt"></i> Reset
                </button>
                <button type="button" id="btnTampilkan" class="btn btn-primary">
                    <i class="fas fa-calculator"></i> Hitung Waste
                </button>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-6 col-lg-3 mb-3">
                <div class="card forecast-card total mb-0">
                    <div class="card-body">
                        <div class="value text-danger" id="ringkasanTotalWaste">0</div>
                        <div class="label">Total Estimasi Waste (Kg)</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3 mb-3">
                <div class="card forecast-card belum mb-0">
                    <div class="card-body">
                        <div class="value text-warning" id="ringkasanBelum">0</div>
                        <div class="label">Peringatan Data</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3 mb-3">
                <div class="card forecast-card vendor mb-0">
                    <div class="card-body">
                        <div class="value" id="ringkasanVendor">0</div>
                        <div class="label">Material Customer</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3 mb-3">
                <div class="card forecast-card vendor mb-0">
                    <div class="card-body">
                        <div class="value" id="ringkasanFullBeliJadi">0</div>
                        <div class="label">Beli Jadi dari Vendor</div>
                    </div>
                </div>
            </div>
        </div>

        <div id="peringatanWrapper" class="alert alert-warning collapsible-alert d-none">
            <button type="button" class="btn btn-link alert-toggle d-flex justify-content-between align-items-center w-100 p-0 text-left" data-toggle="collapse" data-target="#peringatanCollapse" aria-expanded="false" aria-controls="peringatanCollapse">
                <span><i class="fas fa-exclamation-triangle"></i> Data yang perlu dilengkapi <span id="peringatanCount"></span></span>
                <i class="fas fa-chevron-down alert-toggle-icon ml-2"></i>
            </button>
            <div id="peringatanCollapse" class="collapse mt-2">
                <ul id="peringatanData" class="mb-0 pl-3"></ul>
            </div>
        </div>

        <div id="vendorSupplyWrapper" class="alert alert-info collapsible-alert d-none">
            <button type="button" class="btn btn-link alert-toggle d-flex justify-content-between align-items-center w-100 p-0 text-left" data-toggle="collapse" data-target="#vendorSupplyCollapse" aria-expanded="false" aria-controls="vendorSupplyCollapse">
                <span><i class="fas fa-info-circle"></i> Produk dengan material dari customer <span id="vendorSupplyCount"></span></span>
                <i class="fas fa-chevron-down alert-toggle-icon ml-2"></i>
            </button>
            <div id="vendorSupplyCollapse" class="collapse mt-2">
                <ul id="vendorSupplyData" class="mb-0 pl-3"></ul>
            </div>
        </div>

        <div id="fullBeliJadiWrapper" class="alert alert-secondary collapsible-alert d-none">
            <button type="button" class="btn btn-link alert-toggle d-flex justify-content-between align-items-center w-100 p-0 text-left" data-toggle="collapse" data-target="#fullBeliJadiCollapse" aria-expanded="false" aria-controls="fullBeliJadiCollapse">
                <span><i class="fas fa-truck-loading"></i> Produk beli jadi dari vendor (tidak butuh material) <span id="fullBeliJadiCount"></span></span>
                <i class="fas fa-chevron-down alert-toggle-icon ml-2"></i>
            </button>
            <div id="fullBeliJadiCollapse" class="collapse mt-2">
                <ul id="fullBeliJadiData" class="mb-0 pl-3"></ul>
            </div>
        </div>

        <div class="table-responsive">
            <table id="tabelWaste" class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Material</th>
                        <th>Nama Material</th>
                        <th>Satuan</th>
                        <th>Kebutuhan Material</th>
                        <th>Estimasi Waste</th>
                        <th>% Waste</th>
                        <th>Detail</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetail" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="judulDetail">Detail Waste Material</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="alasanDetail" class="alert alert-warning d-none"></div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th>Produk</th>
                                <th>Nama Produk</th>
                                <th>Pelanggan</th>
                                <th>Perlu Diproduksi</th>
                                <th>Wise</th>
                                <th>Berat Material/Produk</th>
                                <th>Kebutuhan</th>
                                <th>Estimasi Waste</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody id="isiDetail"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let dataWaste = [];
    let tabelWaste;
    const pelangganOptions = <?= json_encode(array_map(static function ($row) {
                                return [
                                    'id' => (string) $row['pelid'],
                                    'text' => (string) $row['pelnama'],
                                ];
                            }, $pelanggans ?? []), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const produkOptions = <?= json_encode(array_map(static function ($row) {
                            $kode = (string) $row['brgkode'];
                            $sumberMaterialProduk = $row['sumber_material'] ?? 'tre';
                            $sumber = $sumberMaterialProduk === 'vendor'
                                ? ' [Material Customer]'
                                : ($sumberMaterialProduk === 'beli_jadi' ? ' [Beli Barang Jadi]' : '');
                            return [
                                'id' => $kode,
                                'text' => $kode . ' - ' . (string) $row['brgnama'] . $sumber,
                                'value' => $kode,
                            ];
                        }, $produks ?? []), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const materialOptions = <?= json_encode(array_map(static function ($row) {
                                $kode = (string) $row['matkode'];
                                return [
                                    'id' => (string) $row['matid'],
                                    'text' => $kode . ' - ' . (string) $row['matnama'],
                                    'value' => $kode,
                                ];
                            }, $materials ?? []), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    let pelangganCombobox = null;
    let produkCombobox = null;
    let materialCombobox = null;

    function normalizeComboboxValue(value) {
        return String(value || '').trim().toLowerCase();
    }

    function syncFilterCombobox(instance, inputSelector, hiddenSelector, options) {
        if (instance && typeof instance.sync === 'function') {
            instance.sync();
        }

        const $input = $(inputSelector);
        const $hidden = $(hiddenSelector);
        const typed = $input.val();
        if (normalizeComboboxValue(typed) === '') {
            $hidden.val('');
            return;
        }

        const match = options.find(function(option) {
            return normalizeComboboxValue(option.id) === normalizeComboboxValue(typed) ||
                normalizeComboboxValue(option.text) === normalizeComboboxValue(typed) ||
                normalizeComboboxValue(option.value) === normalizeComboboxValue(typed);
        });

        if (match) {
            $hidden.val(match.id);
            $input.val(match.text);
        } else {
            $hidden.val('');
        }
    }

    function syncFilterComboboxes() {
        syncFilterCombobox(pelangganCombobox, '#filterPelangganInput', '#filterPelanggan', pelangganOptions);
        syncFilterCombobox(produkCombobox, '#filterProdukInput', '#filterProduk', produkOptions);
        syncFilterCombobox(materialCombobox, '#filterMaterialInput', '#filterMaterial', materialOptions);
    }

    function escapeHtml(value) {
        return $('<div>').text(value == null ? '' : value).html();
    }

    function formatAngka(value) {
        if (value === null || value === undefined || value === '') return '-';
        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 4
        }).format(Number(value));
    }

    function parameterFilter() {
        syncFilterComboboxes();
        return {
            pelanggan: $('#filterPelanggan').val(),
            produk: $('#filterProduk').val(),
            material: $('#filterMaterial').val()
        };
    }

    function tampilkanRingkasan(summary) {
        $('#ringkasanTotalWaste').text(formatAngka(summary.total_waste || 0));
        $('#ringkasanBelum').text(summary.peringatan || 0);
        $('#ringkasanVendor').text(summary.vendor_supply || 0);
        $('#ringkasanFullBeliJadi').text(summary.full_beli_jadi || 0);
    }

    function resetCollapse(selector) {
        $(selector).collapse('hide');
    }

    function tampilkanPeringatan(warnings) {
        const wrapper = $('#peringatanWrapper');
        const list = $('#peringatanData').empty();
        const total = warnings ? warnings.length : 0;
        $('#peringatanCount').text(total > 0 ? '(' + total + ')' : '');
        resetCollapse('#peringatanCollapse');
        if (total === 0) {
            wrapper.addClass('d-none');
            return;
        }
        warnings.forEach(pesan => list.append('<li>' + escapeHtml(pesan) + '</li>'));
        wrapper.removeClass('d-none');
    }

    function tampilkanVendorSupply(rows) {
        const wrapper = $('#vendorSupplyWrapper');
        const list = $('#vendorSupplyData').empty();
        const total = rows ? rows.length : 0;
        $('#vendorSupplyCount').text(total > 0 ? '(' + total + ')' : '');
        resetCollapse('#vendorSupplyCollapse');
        if (total === 0) {
            wrapper.addClass('d-none');
            return;
        }
        rows.forEach(row => {
            list.append(
                '<li><strong>' + escapeHtml(row.kode_produk) + '</strong> - ' +
                escapeHtml(row.nama_produk) + ' (' + escapeHtml(row.pelanggan) + '), perlu produksi ' +
                formatAngka(row.perlu_produksi) + ' Pcs. ' + escapeHtml(row.keterangan) + '</li>'
            );
        });
        wrapper.removeClass('d-none');
    }

    function tampilkanFullBeliJadi(rows) {
        const wrapper = $('#fullBeliJadiWrapper');
        const list = $('#fullBeliJadiData').empty();
        const total = rows ? rows.length : 0;
        $('#fullBeliJadiCount').text(total > 0 ? '(' + total + ')' : '');
        resetCollapse('#fullBeliJadiCollapse');
        if (total === 0) {
            wrapper.addClass('d-none');
            return;
        }
        rows.forEach(row => {
            list.append(
                '<li><strong>' + escapeHtml(row.kode_produk) + '</strong> - ' +
                escapeHtml(row.nama_produk) + ' (' + escapeHtml(row.pelanggan) + '). ' +
                escapeHtml(row.keterangan) + '</li>'
            );
        });
        wrapper.removeClass('d-none');
    }

    function renderTabel(rows) {
        if ($.fn.DataTable.isDataTable('#tabelWaste')) {
            $('#tabelWaste').DataTable().destroy();
        }

        const body = $('#tabelWaste tbody').empty();
        rows.forEach((row, index) => {
            const unit = escapeHtml(row.satuan);
            const adaMasalah = (row.alasan_belum_lengkap || []).length > 0;
            body.append(`
                <tr>
                    <td class="text-center">${index + 1}</td>
                    <td>${escapeHtml(row.kode_material)}</td>
                    <td>${escapeHtml(row.nama_material)}</td>
                    <td class="text-center">${unit}</td>
                    <td class="angka">${formatAngka(row.kebutuhan)}</td>
                    <td class="angka text-danger font-weight-bold">${formatAngka(row.waste)}</td>
                    <td class="angka">${formatAngka(row.persen_waste)}%</td>
                    <td class="text-center">
                        ${adaMasalah ? '<span class="badge badge-warning mr-1" title="Ada data yang belum lengkap">!</span>' : ''}
                        <button type="button" class="btn btn-info btn-sm btn-detail" data-index="${index}" title="Lihat asal perhitungan">
                            <i class="fas fa-info-circle"></i> Detail
                        </button>
                    </td>
                </tr>
            `);
        });

        tabelWaste = $('#tabelWaste').DataTable({
            responsive: true,
            autoWidth: false,
            stateSave: true,
            stateDuration: -1,
            pageLength: 10,
            order: [[5, 'desc']],
            language: {
                emptyTable: 'Tidak ada estimasi waste untuk filter yang dipilih.'
            },
            columnDefs: [
                { orderable: false, targets: [0, 7] }
            ]
        });
    }

    function muatData() {
        const tombol = $('#btnTampilkan');
        tombol.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menghitung...');

        $.ajax({
            url: '<?= site_url('materialterbuang/data') ?>',
            method: 'GET',
            data: parameterFilter(),
            dataType: 'json'
        }).done(response => {
            dataWaste = response.data || [];
            tampilkanRingkasan(response.summary || {});
            tampilkanPeringatan(response.warnings || []);
            tampilkanVendorSupply(response.vendor_supply || []);
            tampilkanFullBeliJadi(response.full_beli_jadi || []);
            renderTabel(dataWaste);
            $('#waktuHitung').text('Terakhir dihitung: ' + response.generated_at);
        }).fail(xhr => {
            const pesan = xhr.responseJSON && xhr.responseJSON.message
                ? xhr.responseJSON.message
                : 'Perhitungan waste material gagal dimuat.';
            Swal.fire('Gagal', pesan, 'error');
        }).always(() => {
            tombol.prop('disabled', false).html('<i class="fas fa-calculator"></i> Hitung Waste');
        });
    }

    $(document).on('click', '.btn-detail', function() {
        const row = dataWaste[Number($(this).data('index'))];
        if (!row) return;

        $('#judulDetail').text('Detail Waste ' + row.kode_material + ' - ' + row.nama_material);
        const alasan = row.alasan_belum_lengkap || [];
        if (alasan.length) {
            $('#alasanDetail').removeClass('d-none').html(
                '<strong>Data perlu diperbaiki:</strong><ul class="mb-0">' +
                alasan.map(item => '<li>' + escapeHtml(item) + '</li>').join('') +
                '</ul>'
            );
        } else {
            $('#alasanDetail').addClass('d-none').empty();
        }

        const body = $('#isiDetail').empty();
        (row.details || []).forEach(detail => {
            body.append(`
                <tr>
                    <td>${escapeHtml(detail.kode_produk)}</td>
                    <td>${escapeHtml(detail.nama_produk)}</td>
                    <td>${escapeHtml(detail.pelanggan)}</td>
                    <td class="angka">${formatAngka(detail.perlu_produksi)} Pcs</td>
                    <td class="angka">${detail.wise_persen === null ? '-' : formatAngka(detail.wise_persen) + '%'}</td>
                    <td class="angka">${formatAngka(detail.berat_per_produk)} ${escapeHtml(detail.satuan)}</td>
                    <td class="angka">${detail.kebutuhan === null ? '-' : formatAngka(detail.kebutuhan) + ' ' + escapeHtml(detail.satuan)}</td>
                    <td class="angka">${detail.waste === null ? '-' : formatAngka(detail.waste) + ' ' + escapeHtml(detail.satuan)}</td>
                    <td>${escapeHtml(detail.keterangan || '-')}</td>
                </tr>
            `);
        });
        $('#modalDetail').modal('show');
    });

    $('#btnTampilkan').on('click', muatData);
    $('#btnReset').on('click', function() {
        $('#filterPelanggan, #filterProduk, #filterMaterial').val('');
        $('#filterPelangganInput, #filterProdukInput, #filterMaterialInput').val('');
        muatData();
    });

    $(document).ready(function() {
        pelangganCombobox = window.treInitInlineCombobox({
            box: '#pelangganCombobox',
            input: '#filterPelangganInput',
            hidden: '#filterPelanggan',
            menu: '#pelangganComboboxMenu',
            options: pelangganOptions
        });
        produkCombobox = window.treInitInlineCombobox({
            box: '#produkCombobox',
            input: '#filterProdukInput',
            hidden: '#filterProduk',
            menu: '#produkComboboxMenu',
            options: produkOptions
        });
        materialCombobox = window.treInitInlineCombobox({
            box: '#materialCombobox',
            input: '#filterMaterialInput',
            hidden: '#filterMaterial',
            menu: '#materialComboboxMenu',
            options: materialOptions
        });
        muatData();
    });
</script>
<?= $this->endSection('isi') ?>
