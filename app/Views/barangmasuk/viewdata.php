<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Data Produk Masuk
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>

<button type="button" class="btn btn-primary" onclick="location.href=('/barangmasuk/input')">
    <i class="fa fa-plus-circle"></i> Input Transaksi Produk Masuk
</button>

<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<style>
    .dpm-toolbar {
        align-items: flex-start;
        display: flex;
        justify-content: flex-end;
        margin-bottom: 1rem;
        position: relative;
        z-index: 30;
    }

    .dpm-filter-anchor {
        align-items: flex-end;
        display: flex;
        flex-direction: column;
        position: relative;
        width: min(100%, 32rem);
    }

    .dpm-search-shell {
        align-items: center;
        background: #fff;
        border: 1px solid #eef1f7;
        border-radius: 999px;
        box-shadow: 0 3px 10px rgba(15, 23, 42, .025);
        display: flex;
        gap: .65rem;
        min-height: 3.4rem;
        padding: .35rem .45rem .35rem 1.25rem;
        width: 100%;
    }

    .dpm-search-shell > i {
        color: #6b7280;
        font-size: 1.1rem;
    }

    .dpm-search-input {
        background: transparent;
        border: 0;
        box-shadow: none;
        color: #4b5563;
        flex: 1 1 auto;
        font-size: .95rem;
        min-width: 0;
        outline: 0;
    }

    .dpm-search-input:focus {
        box-shadow: none;
        outline: 0;
    }

    .dpm-filter-toggle {
        align-items: center;
        background: #16869a;
        border: 0;
        border-radius: 999px;
        color: #fff;
        display: inline-flex;
        flex: 0 0 2.65rem;
        height: 2.65rem;
        justify-content: center;
        width: 2.65rem;
    }

    .dpm-filter-toggle:hover,
    .dpm-filter-toggle:focus {
        background: #126f7f;
        color: #fff;
        outline: 0;
    }

    .dpm-filter-panel {
        background: #fff;
        border: 1px solid #edf1f5;
        border-radius: 22px;
        box-shadow: 0 18px 42px rgba(15, 23, 42, .1);
        display: none;
        max-width: min(46rem, calc(100vw - 4rem));
        opacity: 0;
        overflow: hidden;
        pointer-events: none;
        position: absolute;
        right: 0;
        top: calc(100% + .75rem);
        transform: translateY(-.45rem) scale(.985);
        transform-origin: top right;
        transition: opacity .18s ease, transform .18s ease;
        width: 34rem;
    }

    .dpm-filter-panel.is-visible {
        display: block;
    }

    .dpm-filter-panel.is-open {
        opacity: 1;
        pointer-events: auto;
        transform: translateY(0) scale(1);
    }

    .dpm-filter-header {
        align-items: center;
        display: flex;
        justify-content: space-between;
        padding: 1.2rem 1.35rem .9rem;
    }

    .dpm-filter-title {
        color: #111827;
        font-size: 1.2rem;
        font-weight: 800;
        margin: 0;
    }

    .dpm-filter-close {
        align-items: center;
        background: #fff;
        border: 0;
        border-radius: 999px;
        box-shadow: 0 8px 22px rgba(15, 23, 42, .12);
        color: #111827;
        display: inline-flex;
        height: 2.4rem;
        justify-content: center;
        width: 2.4rem;
    }

    .dpm-filter-section {
        border-top: 1px solid #edf1f5;
        padding: 1.05rem 1.35rem;
    }

    .dpm-filter-label {
        color: #718096;
        font-size: .9rem;
        font-weight: 800;
        margin-bottom: .75rem;
    }

    .dpm-filter-chip-row,
    .dpm-filter-field-row,
    .dpm-filter-action-row {
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
    }

    .dpm-filter-chip {
        background: #fff;
        border: 1px solid #e8edf4;
        border-radius: 999px;
        color: #111827;
        font-weight: 600;
        min-height: 2.45rem;
        padding: .45rem .9rem;
    }

    .dpm-filter-chip.is-active,
    .dpm-filter-chip:hover {
        background: #eef6e8;
        border-color: #d9e9cf;
    }

    .dpm-filter-date,
    .dpm-filter-select {
        background: #fff;
        border: 1px solid #e5eaf1;
        border-radius: 999px;
        color: #111827;
        min-height: 2.55rem;
        padding: .45rem .85rem;
    }

    .dpm-filter-date {
        min-width: 11rem;
    }

    .dpm-filter-select {
        min-width: 11rem;
    }

    .dpm-filter-apply,
    .dpm-filter-reset {
        border: 0;
        border-radius: 999px;
        font-weight: 800;
        min-height: 2.55rem;
        padding: .45rem 1.2rem;
    }

    .dpm-filter-apply {
        background: #16869a;
        color: #fff;
    }

    .dpm-filter-reset {
        background: #eef2f7;
        color: #4b5563;
    }

    @media (max-width: 768px) {
        .dpm-toolbar {
            justify-content: stretch;
        }

        .dpm-filter-anchor,
        .dpm-search-shell,
        .dpm-filter-panel {
            max-width: none;
            width: 100%;
        }

        .dpm-filter-panel {
            left: 0;
            right: auto;
        }

        .dpm-filter-date,
        .dpm-filter-select,
        .dpm-filter-apply,
        .dpm-filter-reset {
            width: 100%;
        }
    }
</style>
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<script src="<?= base_url() ?>/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

<ul class="nav nav-tabs" id="tabSumberProdukMasuk" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="tab-stok-link" data-toggle="tab" href="#tab-stok" role="tab">
            <i class="fa fa-box"></i> Stok Saat Ini
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="tab-supplier-link" data-toggle="tab" href="#tab-supplier" role="tab">
            <i class="fa fa-truck-loading"></i> Supplier / PO Out
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="tab-adjustment-link" data-toggle="tab" href="#tab-adjustment" role="tab">
            <i class="fa fa-sliders-h"></i> Adjustment
        </a>
    </li>
    <?php if ($bisaLihatProduksi) : ?>
        <li class="nav-item">
            <a class="nav-link" id="tab-produksi-link" data-toggle="tab" href="#tab-produksi" role="tab">
                <i class="fa fa-industry"></i> Dari Produksi
            </a>
        </li>
    <?php endif ?>
</ul>

<div class="tab-content border border-top-0 p-3 mb-3">
    <div class="tab-pane fade show active" id="tab-stok" role="tabpanel">
        <div class="dpm-toolbar">
            <div class="dpm-filter-anchor">
                <div class="dpm-search-shell">
                    <i class="fas fa-search"></i>
                    <input type="search" id="stokSearchInput" class="dpm-search-input" placeholder="Search anything..." aria-label="Search anything">
                    <button type="button" class="dpm-filter-toggle" id="stokFilterToggle" title="Buka filter" aria-controls="stokFilterPanel" aria-expanded="false">
                        <i class="fas fa-sliders-h"></i>
                    </button>
                </div>

                <section class="dpm-filter-panel" id="stokFilterPanel" aria-hidden="true">
                    <div class="dpm-filter-header">
                        <h3 class="dpm-filter-title">Filter</h3>
                        <button type="button" class="dpm-filter-close" id="stokFilterClose" title="Tutup filter">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <?= form_open('stok/cetakLaporan', ['target' => '_blank', 'id' => 'formCetakStok']) ?>
                    <?= csrf_field() ?>
                    <div class="dpm-filter-section">
                        <div class="dpm-filter-label">Filter Kategori, Material &amp; Pelanggan</div>
                        <div class="dpm-filter-field-row">
                            <select name="kategori" id="kategoriStok" class="dpm-filter-select" aria-label="Kategori">
                                <option value="">Pilih Kategori</option>
                                <?php foreach ($kategoris as $kategori) : ?>
                                    <option value="<?= $kategori['katid'] ?>"><?= $kategori['katnama'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select name="material" id="materialStok" class="dpm-filter-select" aria-label="Material">
                                <option value="">Pilih Material</option>
                                <?php foreach ($materials as $material) : ?>
                                    <option value="<?= $material['matid'] ?>"><?= $material['matnama'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select name="pelanggan" id="pelangganStok" class="dpm-filter-select" aria-label="Pelanggan">
                                <option value="">Pilih Pelanggan</option>
                                <?php foreach ($pelanggans as $pelanggan) : ?>
                                    <option value="<?= $pelanggan['pelid'] ?>"><?= $pelanggan['pelnama'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="dpm-filter-section">
                        <div class="dpm-filter-label">Show Data</div>
                        <div class="dpm-filter-field-row">
                            <select id="stokPageLength" class="dpm-filter-select" aria-label="Show data">
                                <option value="10">10 entries</option>
                                <option value="25">25 entries</option>
                                <option value="50" selected>50 entries</option>
                                <option value="100">100 entries</option>
                            </select>
                        </div>
                    </div>

                    <div class="dpm-filter-section">
                        <div class="dpm-filter-action-row">
                            <button type="submit" name="btnCetak" class="dpm-filter-apply"><i class="fa fa-print"></i> Cetak Laporan</button>
                            <button type="button" class="dpm-filter-reset" id="stokFilterReset">Reset</button>
                        </div>
                    </div>
                    <?= form_close() ?>
                </section>
            </div>
        </div>
        <table class="table table-bordered table-striped" id="datastok">
            <thead>
                <tr>
                    <th style="width: 5%; vertical-align: middle;">No</th>
                    <th style="vertical-align: middle;">Kode Barang</th>
                    <th style="vertical-align: middle;">Stok Cikarang (Pcs)</th>
                    <th style="vertical-align: middle;">Stok Cirebon (Pcs)</th>
                    <th style="vertical-align: middle;">Total Stok (Pcs)</th>
                    <th style="vertical-align: middle;">Total Sisa PO</th>
                    <th style="vertical-align: middle;">QTY Terkirim (Pcs)</th>
                    <th style="vertical-align: middle;">Kekurangan Produksi (Pcs)</th>
                    <th style="vertical-align: middle;">Kelebihan Produksi (Pcs)</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="tab-pane fade" id="tab-supplier" role="tabpanel">
        <div class="dpm-toolbar">
            <div class="dpm-filter-anchor">
                <div class="dpm-search-shell">
                    <i class="fas fa-search"></i>
                    <input type="search" id="supplierSearchInput" class="dpm-search-input" placeholder="Search anything..." aria-label="Search anything">
                    <button type="button" class="dpm-filter-toggle" id="supplierFilterToggle" title="Buka filter" aria-controls="supplierFilterPanel" aria-expanded="false">
                        <i class="fas fa-sliders-h"></i>
                    </button>
                </div>

                <section class="dpm-filter-panel" id="supplierFilterPanel" aria-hidden="true">
                    <div class="dpm-filter-header">
                        <h3 class="dpm-filter-title">Filter</h3>
                        <button type="button" class="dpm-filter-close" id="supplierFilterClose" title="Tutup filter">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="dpm-filter-section">
                        <div class="dpm-filter-label">Filter by Date Range</div>
                        <div class="dpm-filter-chip-row">
                            <button type="button" class="dpm-filter-chip" data-range="30">Last 30 Days</button>
                            <button type="button" class="dpm-filter-chip" data-range="180">Last 6 Months</button>
                        </div>
                    </div>

                    <div class="dpm-filter-section">
                        <div class="dpm-filter-label">Custom Date Range</div>
                        <div class="dpm-filter-field-row">
                            <input type="date" name="tglawal" id="tglawal" class="dpm-filter-date" aria-label="Start date">
                            <input type="date" name="tglakhir" id="tglakhir" class="dpm-filter-date" aria-label="End date">
                        </div>
                    </div>

                    <div class="dpm-filter-section">
                        <div class="dpm-filter-label">Show Data</div>
                        <div class="dpm-filter-field-row">
                            <select id="supplierPageLength" class="dpm-filter-select" aria-label="Show data">
                                <option value="10">10 entries</option>
                                <option value="25">25 entries</option>
                                <option value="50" selected>50 entries</option>
                                <option value="100">100 entries</option>
                            </select>
                        </div>
                    </div>

                    <div class="dpm-filter-section">
                        <div class="dpm-filter-action-row">
                            <button type="button" class="dpm-filter-apply" id="tombolTampil">Tampilkan</button>
                            <button type="button" class="dpm-filter-reset" id="supplierFilterReset">Reset</button>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <table id="databarangmasuk" class="table table-bordered table-striped table-hover dataTable dtr-inline collapsed">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th>No PO</th>
                    <th>Tanggal</th>
                    <th>Supplier</th>
                    <th>QTY (Pcs)</th>
                    <th>Total Berat (KG)</th>
                    <th>Gudang Tujuan</th>
                    <th style="width: 10%;">#</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="tab-pane fade" id="tab-adjustment" role="tabpanel">
        <div class="dpm-toolbar">
            <div class="dpm-filter-anchor">
                <div class="dpm-search-shell">
                    <i class="fas fa-search"></i>
                    <input type="search" id="adjustmentSearchInput" class="dpm-search-input" placeholder="Search anything..." aria-label="Search anything">
                    <button type="button" class="dpm-filter-toggle" id="adjustmentFilterToggle" title="Buka filter" aria-controls="adjustmentFilterPanel" aria-expanded="false">
                        <i class="fas fa-sliders-h"></i>
                    </button>
                </div>

                <section class="dpm-filter-panel" id="adjustmentFilterPanel" aria-hidden="true">
                    <div class="dpm-filter-header">
                        <h3 class="dpm-filter-title">Filter</h3>
                        <button type="button" class="dpm-filter-close" id="adjustmentFilterClose" title="Tutup filter">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="dpm-filter-section">
                        <div class="dpm-filter-label">Filter by Date Range</div>
                        <div class="dpm-filter-chip-row">
                            <button type="button" class="dpm-filter-chip" data-range="30">Last 30 Days</button>
                            <button type="button" class="dpm-filter-chip" data-range="180">Last 6 Months</button>
                        </div>
                    </div>

                    <div class="dpm-filter-section">
                        <div class="dpm-filter-label">Custom Date Range</div>
                        <div class="dpm-filter-field-row">
                            <input type="date" name="tglawalAdjustment" id="tglawalAdjustment" class="dpm-filter-date" aria-label="Start date">
                            <input type="date" name="tglakhirAdjustment" id="tglakhirAdjustment" class="dpm-filter-date" aria-label="End date">
                        </div>
                    </div>

                    <div class="dpm-filter-section">
                        <div class="dpm-filter-label">Show Data</div>
                        <div class="dpm-filter-field-row">
                            <select id="adjustmentPageLength" class="dpm-filter-select" aria-label="Show data">
                                <option value="10">10 entries</option>
                                <option value="25">25 entries</option>
                                <option value="50" selected>50 entries</option>
                                <option value="100">100 entries</option>
                            </select>
                        </div>
                    </div>

                    <div class="dpm-filter-section">
                        <div class="dpm-filter-action-row">
                            <button type="button" class="dpm-filter-apply" id="tombolTampilAdjustment">Tampilkan</button>
                            <button type="button" class="dpm-filter-reset" id="adjustmentFilterReset">Reset</button>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <table id="dataadjustment" class="table table-bordered table-striped table-hover dataTable dtr-inline collapsed" style="width: 100%;">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th>No Adjustment</th>
                    <th>Tanggal</th>
                    <th>QTY (Pcs)</th>
                    <th>Total Berat (KG)</th>
                    <th>Gudang Tujuan</th>
                    <th style="width: 10%;">#</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <?php if ($bisaLihatProduksi) : ?>
    <div class="tab-pane fade" id="tab-produksi" role="tabpanel">
        <div class="dpm-toolbar">
            <div class="dpm-filter-anchor">
                <div class="dpm-search-shell">
                    <i class="fas fa-search"></i>
                    <input type="search" id="produksiSearchInput" class="dpm-search-input" placeholder="Search anything..." aria-label="Search anything">
                    <button type="button" class="dpm-filter-toggle" id="produksiFilterToggle" title="Buka filter" aria-controls="produksiFilterPanel" aria-expanded="false">
                        <i class="fas fa-sliders-h"></i>
                    </button>
                </div>

                <section class="dpm-filter-panel" id="produksiFilterPanel" aria-hidden="true">
                    <div class="dpm-filter-header">
                        <h3 class="dpm-filter-title">Filter</h3>
                        <button type="button" class="dpm-filter-close" id="produksiFilterClose" title="Tutup filter">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="dpm-filter-section">
                        <div class="dpm-filter-label">Filter by Date Range</div>
                        <div class="dpm-filter-chip-row">
                            <button type="button" class="dpm-filter-chip" data-range="30">Last 30 Days</button>
                            <button type="button" class="dpm-filter-chip" data-range="180">Last 6 Months</button>
                        </div>
                    </div>

                    <div class="dpm-filter-section">
                        <div class="dpm-filter-label">Custom Date Range</div>
                        <div class="dpm-filter-field-row">
                            <input type="date" name="tglawalProduksi" id="tglawalProduksi" class="dpm-filter-date" aria-label="Start date">
                            <input type="date" name="tglakhirProduksi" id="tglakhirProduksi" class="dpm-filter-date" aria-label="End date">
                        </div>
                    </div>

                    <div class="dpm-filter-section">
                        <div class="dpm-filter-action-row">
                            <button type="button" class="dpm-filter-apply" id="tombolTampilProduksi">Tampilkan</button>
                            <button type="button" class="dpm-filter-reset" id="produksiFilterReset">Reset</button>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <div class="table-responsive">
            <table id="dataproduksi" class="table table-bordered table-striped table-hover dataTable" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th>Tanggal</th>
                        <th>Kode Produk</th>
                        <th>Nama Produk</th>
                        <th>Qty Diproduksi</th>
                        <th>Gudang</th>
                        <th>Keterangan</th>
                        <th style="width: 10%;">#</th>
                        <th>User Input</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    <?php endif ?>
</div>

<script>
    let csrfToken = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';
    var table;
    var tableAdjustment;
    var tableProduksi;
    var tableStok;

    // Setiap POST AJAX dapat meregenerasi token CSRF. Sinkronkan token
    // terbaru ke request berikutnya dan ke form cetak stok.
    $(document).ajaxComplete(function(event, xhr) {
        const responseToken = xhr.getResponseHeader('X-CSRF-TOKEN');
        if (responseToken) {
            csrfHash = responseToken;
            $('#formCetakStok input[name="' + csrfToken + '"]').val(csrfHash);
        }
    });

    function formatNumber(number) {
        return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    $(document).ready(function() {
        tableStok = $('#datastok').DataTable({
            lengthChange: true,
            autoWidth: false,
            responsive: true,
            searching: true,
            searchDelay: 500,
            stateSave: true,
            stateDuration: -1,
            processing: true,
            serverSide: true,
            dom: "<'row'<'col-sm-12'tr>><'row align-items-center mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            ajax: {
                url: '<?= site_url('stok/data') ?>',
                type: 'POST',
                data: function(d) {
                    d.kategori = $('#kategoriStok').val();
                    d.material = $('#materialStok').val();
                    d.pelanggan = $('#pelangganStok').val();
                    d[csrfToken] = csrfHash;
                }
            },
            pageLength: 10,
            order: [
                [1, 'asc']
            ],
            columns: [{
                    data: 'nomor',
                    orderable: false,
                    className: 'text-center'
                },
                {
                    data: 'kodebarang',
                    className: 'text-center'
                },
                {
                    data: 'totmascik',
                    className: 'text-right',
                    render: function(data) {
                        return formatNumber(data);
                    }
                },
                {
                    data: 'totmascir',
                    className: 'text-right',
                    render: function(data) {
                        return formatNumber(data);
                    }
                },
                {
                    data: 'totalstok',
                    className: 'text-right',
                    render: function(data) {
                        return formatNumber(data);
                    }
                },
                {
                    data: 'kekurangan',
                    className: 'text-right',
                    render: function(data) {
                        return formatNumber(data);
                    }
                },
                {
                    data: 'kirim',
                    className: 'text-right',
                    render: function(data) {
                        return formatNumber(data);
                    }
                },
                {
                    data: 'kekuranganproduksi',
                    className: 'text-right',
                    render: function(data) {
                        return formatNumber(data);
                    }
                },
                {
                    data: 'kelebihanproduksi',
                    className: 'text-right',
                    render: function(data) {
                        return formatNumber(data);
                    }
                },
            ],
        });

        $('#kategoriStok').on('change', function() {
            tableStok.ajax.reload();
        });
        $('#materialStok').on('change', function() {
            tableStok.ajax.reload();
        });
        $('#pelangganStok').on('change', function() {
            tableStok.ajax.reload();
        });

        table = $('#databarangmasuk').DataTable({
            lengthChange: true,
            autoWidth: false,
            responsive: true,
            searching: true,
            searchDelay: 500,
            stateSave: true,
            stateDuration: -1,
            processing: true,
            serverSide: true,
            dom: "<'row'<'col-sm-12'tr>><'row align-items-center mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            ajax: {
                url: '<?= site_url('barangmasuk/listData') ?>',
                type: 'POST',
                data: function(d) {
                    d.tglawal = $('#tglawal').val();
                    d.tglakhir = $('#tglakhir').val();
                    d.sumber = 'supplier';
                    d[csrfToken] = csrfHash;
                }
            },
            pageLength: 10,
            order: [
                [2, 'desc']
            ],
            columns: [{
                    data: 'nomor',
                    orderable: false,
                    className: 'text-center'
                },
                {
                    data: 'faktur'
                },
                {
                    data: 'tglfaktur',
                    className: 'text-center'
                },
                {
                    data: 'supnama',
                    className: 'text-center'
                },
                {
                    data: 'qtymasuk',
                    className: 'text-right'
                },
                {
                    data: 'totalberatbarang',
                    className: 'text-right'
                },
                {
                    data: 'gdgnama',
                    className: 'text-center'
                },
                {
                    data: 'aksi',
                    orderable: false,
                    className: 'text-center'
                },
            ]
        });

        $('#tombolTampil').on('click', function() {
            table.ajax.reload();
        });

        tableAdjustment = $('#dataadjustment').DataTable({
            lengthChange: true,
            autoWidth: false,
            responsive: true,
            searching: true,
            searchDelay: 500,
            stateSave: true,
            stateDuration: -1,
            processing: true,
            serverSide: true,
            dom: "<'row'<'col-sm-12'tr>><'row align-items-center mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            ajax: {
                url: '<?= site_url('barangmasuk/listData') ?>',
                type: 'POST',
                data: function(d) {
                    d.tglawal = $('#tglawalAdjustment').val();
                    d.tglakhir = $('#tglakhirAdjustment').val();
                    d.sumber = 'adjustment';
                    d[csrfToken] = csrfHash;
                }
            },
            pageLength: 10,
            order: [
                [2, 'desc']
            ],
            columns: [{
                    data: 'nomor',
                    orderable: false,
                    className: 'text-center'
                },
                {
                    data: 'faktur'
                },
                {
                    data: 'tglfaktur',
                    className: 'text-center'
                },
                {
                    data: 'qtymasuk',
                    className: 'text-right'
                },
                {
                    data: 'totalberatbarang',
                    className: 'text-right'
                },
                {
                    data: 'gdgnama',
                    className: 'text-center'
                },
                {
                    data: 'aksi',
                    orderable: false,
                    className: 'text-center'
                },
            ]
        });

        $('#tombolTampilAdjustment').on('click', function() {
            tableAdjustment.ajax.reload();
        });

<?php if ($bisaLihatProduksi) : ?>
        tableProduksi = $('#dataproduksi').DataTable({
            searching: true,
            searchDelay: 500,
            stateSave: true,
            stateDuration: -1,
            autoWidth: false,
            responsive: true,
            processing: true,
            serverSide: false,
            ordering: false,
            paging: false,
            dom: "<'row'<'col-sm-12'tr>><'row mt-3'<'col-sm-12'i>>",
            ajax: {
                url: '<?= site_url('produksi/listData') ?>',
                type: 'POST',
                data: function(d) {
                    d.tglawal = $('#tglawalProduksi').val();
                    d.tglakhir = $('#tglakhirProduksi').val();
                    d[csrfToken] = csrfHash;
                    // Endpoint memakai library Hermawan\DataTables yang
                    // mengharuskan parameter draw, columns, dan search.
                    // Array kosong akan dihilangkan oleh jQuery saat
                    // diserialisasi, sehingga backend menerima columns=null
                    // dan gagal saat melakukan foreach pada parameter itu.
                    d.draw = 1;
                    d.length = -1;
                    d.start = 0;
                    d.columns = [
                        'nomor', 'tgl_produksi', 'kode_produk',
                        'nama_produk', 'qty_produk', 'gdgnama',
                        'keterangan', 'aksi', 'user_input'
                    ].map(function(column) {
                        return {
                            data: column,
                            name: column,
                            searchable: 'false',
                            orderable: 'false',
                            search: {
                                value: '',
                                regex: 'false'
                            }
                        };
                    });
                    d.search = {
                        value: '',
                        regex: false
                    };
                }
            },
            drawCallback: function(settings) {
                // Pakai settings->api langsung (bukan variabel `tableProduksi`
                // di luar) karena drawCallback pertama nembak SEBELUM baris
                // `tableProduksi = $(...).DataTable(...)` selesai assign.
                window.treRenderMergedGroupRows(new $.fn.dataTable.Api(settings), {
                    groupBy: function(row) {
                        return row.no_produksi;
                    },
                    // Tanggal sengaja tidak di-merge agar tiap baris produksi
                    // tetap jelas terbaca walaupun masih satu batch.
                    columns: [5, 6]
                });
            },
            columns: [{
                    data: 'nomor',
                    orderable: false,
                    className: 'text-center'
                },
                {
                    data: 'tgl_produksi',
                    className: 'text-center'
                },
                {
                    data: 'kode_produk',
                    className: 'text-center'
                },
                {
                    data: 'nama_produk'
                },
                {
                    data: 'qty_produk',
                    className: 'text-right'
                },
                {
                    data: 'gdgnama',
                    className: 'text-center'
                },
                {
                    data: 'keterangan',
                    className: 'text-center'
                },
                {
                    data: 'aksi',
                    orderable: false,
                    className: 'text-center'
                },
                {
                    data: 'user_input',
                    className: 'text-center'
                },
            ]
        });

        $('#tombolTampilProduksi').on('click', function() {
            tableProduksi.ajax.reload();
        });
<?php endif ?>

        function setupFilterPanel(opts) {
            const panel = $('#' + opts.panelId);
            const toggle = $('#' + opts.toggleId);
            const closeBtn = $('#' + opts.closeId);
            let visibilityTimer = null;

            function toggleFilterPanel(forceOpen = null) {
                const willOpen = forceOpen === null ? !panel.hasClass('is-open') : forceOpen;
                window.clearTimeout(visibilityTimer);

                if (willOpen) {
                    panel.addClass('is-visible');
                    window.requestAnimationFrame(function() {
                        panel.addClass('is-open');
                    });
                } else {
                    panel.removeClass('is-open');
                    visibilityTimer = window.setTimeout(function() {
                        panel.removeClass('is-visible');
                    }, 180);
                }

                panel.attr('aria-hidden', willOpen ? 'false' : 'true');
                toggle.attr('aria-expanded', willOpen ? 'true' : 'false');
            }

            toggle.on('click', function() {
                toggleFilterPanel();
            });

            closeBtn.on('click', function() {
                toggleFilterPanel(false);
            });

            if (opts.searchInputId && opts.table) {
                $('#' + opts.searchInputId).val(opts.table.search());
                let searchTimer = null;
                $('#' + opts.searchInputId).on('input', function() {
                    const keyword = this.value;
                    window.clearTimeout(searchTimer);
                    searchTimer = window.setTimeout(function() {
                        opts.table.search(keyword).draw();
                    }, 350);
                });
            }

            if (opts.pageLengthId && opts.table) {
                $('#' + opts.pageLengthId).on('change', function() {
                    opts.table.page.len(Number(this.value)).draw();
                });
            }

            if (opts.tglawalId && opts.tglakhirId) {
                const formatDateInput = (date) => date.toISOString().slice(0, 10);
                panel.find('.dpm-filter-chip').on('click', function() {
                    const days = Number($(this).data('range')) || 30;
                    const endDate = new Date();
                    const startDate = new Date();
                    startDate.setDate(endDate.getDate() - days);
                    panel.find('.dpm-filter-chip').removeClass('is-active');
                    $(this).addClass('is-active');
                    $('#' + opts.tglawalId).val(formatDateInput(startDate));
                    $('#' + opts.tglakhirId).val(formatDateInput(endDate));
                });
            }

            return { toggleFilterPanel };
        }

        const stokFilter = setupFilterPanel({
            panelId: 'stokFilterPanel',
            toggleId: 'stokFilterToggle',
            closeId: 'stokFilterClose',
            searchInputId: 'stokSearchInput',
            pageLengthId: 'stokPageLength',
            table: tableStok
        });

        const supplierFilter = setupFilterPanel({
            panelId: 'supplierFilterPanel',
            toggleId: 'supplierFilterToggle',
            closeId: 'supplierFilterClose',
            searchInputId: 'supplierSearchInput',
            pageLengthId: 'supplierPageLength',
            tglawalId: 'tglawal',
            tglakhirId: 'tglakhir',
            table: table
        });

        const adjustmentFilter = setupFilterPanel({
            panelId: 'adjustmentFilterPanel',
            toggleId: 'adjustmentFilterToggle',
            closeId: 'adjustmentFilterClose',
            searchInputId: 'adjustmentSearchInput',
            pageLengthId: 'adjustmentPageLength',
            tglawalId: 'tglawalAdjustment',
            tglakhirId: 'tglakhirAdjustment',
            table: tableAdjustment
        });

<?php if ($bisaLihatProduksi) : ?>
        const produksiFilter = setupFilterPanel({
            panelId: 'produksiFilterPanel',
            toggleId: 'produksiFilterToggle',
            closeId: 'produksiFilterClose',
            searchInputId: 'produksiSearchInput',
            tglawalId: 'tglawalProduksi',
            tglakhirId: 'tglakhirProduksi',
            table: tableProduksi
        });
<?php endif ?>

        $('#tombolTampil').on('click', function() {
            supplierFilter.toggleFilterPanel(false);
        });
        $('#tombolTampilAdjustment').on('click', function() {
            adjustmentFilter.toggleFilterPanel(false);
        });
        <?php if ($bisaLihatProduksi) : ?>
        $('#tombolTampilProduksi').on('click', function() {
            produksiFilter.toggleFilterPanel(false);
        });
        <?php endif ?>

        $('#stokFilterReset').on('click', function() {
            $('#materialStok').val('');
            $('#pelangganStok').val('');
            $('#stokPageLength').val('50');
            $('#stokSearchInput').val('');
            tableStok.search('').page.len(50);
            tableStok.ajax.reload();
        });

        $('#supplierFilterReset').on('click', function() {
            $('#tglawal').val('');
            $('#tglakhir').val('');
            $('#supplierFilterPanel .dpm-filter-chip').removeClass('is-active');
            $('#supplierPageLength').val('50');
            $('#supplierSearchInput').val('');
            table.search('').page.len(50);
            table.ajax.reload();
        });

        $('#adjustmentFilterReset').on('click', function() {
            $('#tglawalAdjustment').val('');
            $('#tglakhirAdjustment').val('');
            $('#adjustmentFilterPanel .dpm-filter-chip').removeClass('is-active');
            $('#adjustmentPageLength').val('50');
            $('#adjustmentSearchInput').val('');
            tableAdjustment.search('').page.len(50);
            tableAdjustment.ajax.reload();
        });

        <?php if ($bisaLihatProduksi) : ?>
        $('#produksiFilterReset').on('click', function() {
            $('#tglawalProduksi').val('');
            $('#tglakhirProduksi').val('');
            $('#produksiFilterPanel .dpm-filter-chip').removeClass('is-active');
            $('#produksiSearchInput').val('');
            tableProduksi.search('');
            tableProduksi.ajax.reload();
        });
        <?php endif ?>

        // DataTables perlu di-recalculate lebarnya begitu tab yang
        // nampilinnya baru pertama kali ditampilkan, karena pas di-init dia
        // masih tersembunyi (display:none) dan lebar tabelnya belum
        // kehitung benar.
        <?php if ($bisaLihatProduksi) : ?>
        $('#tab-produksi-link').on('shown.bs.tab', function() {
            tableProduksi.columns.adjust();
        });
        <?php endif ?>
        $('#tab-supplier-link').on('shown.bs.tab', function() {
            table.columns.adjust().responsive.recalc();
        });
        $('#tab-adjustment-link').on('shown.bs.tab', function() {
            tableAdjustment.columns.adjust().responsive.recalc();
        });

        <?php if ($bisaLihatProduksi) : ?>
        if (window.location.hash === '#tab-produksi') {
            $('#tab-produksi-link').tab('show');
        }
        <?php endif ?>
    });

    function cetak(faktur) {
        let windowCetak = window.open('/barangmasuk/cetakfaktur/' + faktur,
            "Cetak Faktur Barang Masuk",
            "width=200,height=400");
        windowCetak.focus();
    }

    function hapus(faktur) {
        Swal.fire({
            title: 'Hapus Transaksi',
            text: "Yakin Hapus Transaksi ?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus !'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "post",
                    url: '<?= site_url('barangmasuk/hapusTransaksi') ?>',
                    data: {
                        [csrfToken]: csrfHash,
                        faktur: faktur
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.sukses) {
                            Swal.fire('Berhasil', response.sukses, 'success').then(() => {
                                window.location.reload();
                            });
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        alert(xhr.status + '\n' + thrownError)
                    }
                });
            }
        })
    }

    function edit(faktur) {
        window.location.href = ('/barangmasuk/edit/') + faktur;
    }

    function editProduksi(hashProduksi) {
        window.location.href = ('/produksi/edit/') + hashProduksi;
    }

    function hapusProduksi(produksiProdukId) {
        Swal.fire({
            title: 'Hapus Data Produksi',
            text: "Stok material yang tadi dipakai akan dikembalikan, dan stok produk hasil produksi ini akan dikurangi lagi. Yakin hapus?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus !'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "post",
                    url: '<?= site_url('produksi/hapusTransaksi') ?>',
                    data: {
                        [csrfToken]: csrfHash,
                        id: produksiProdukId
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.sukses) {
                            Swal.fire('Berhasil', response.sukses, 'success');
                            tableProduksi.ajax.reload();
                        } else if (response.error) {
                            Swal.fire('Gagal', response.error, 'error');
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        alert(xhr.status + '\n' + thrownError)
                    }
                });
            }
        })
    }
</script>

<?= $this->endSection('isi') ?>
