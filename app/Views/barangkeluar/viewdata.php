<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Pengiriman
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>
<div class="d-flex flex-wrap" style="gap:.5rem;">
    <button type="button" class="btn btn-success" onclick="location.href='/permintaanPengiriman/langsung'">
        <i class="fa fa-plus-circle"></i> Input Pengiriman
    </button>
    <button type="button" class="btn btn-primary" onclick="location.href='/permintaanPengiriman/input'">
        <i class="fa fa-clipboard-list"></i> Buat Permintaan
    </button>
</div>
<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<script src="<?= base_url() ?>/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

<style>
.pengiriman-tabs .nav-link {
    font-weight: 700;
}

.pgr-toolbar {
    align-items: flex-start;
    display: flex;
    justify-content: flex-end;
    margin-bottom: 1rem;
    position: relative;
    z-index: 30;
}

.pgr-filter-anchor {
    align-items: flex-end;
    display: flex;
    flex-direction: column;
    position: relative;
    width: min(100%, 32rem);
}

.pgr-search-shell {
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

.pgr-search-shell>i {
    color: #6b7280;
    font-size: 1.1rem;
}

.pgr-search-input {
    background: transparent;
    border: 0;
    box-shadow: none;
    color: #4b5563;
    flex: 1 1 auto;
    font-size: .95rem;
    min-width: 0;
    outline: 0;
}

.pgr-search-input:focus {
    box-shadow: none;
    outline: 0;
}

.pgr-filter-toggle {
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

.pgr-filter-toggle:hover,
.pgr-filter-toggle:focus {
    background: #126f7f;
    color: #fff;
    outline: 0;
}

.pgr-filter-panel {
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

.pgr-filter-panel.is-visible {
    display: block;
}

.pgr-filter-panel.is-open {
    opacity: 1;
    pointer-events: auto;
    transform: translateY(0) scale(1);
}

.pgr-filter-header {
    align-items: center;
    display: flex;
    justify-content: space-between;
    padding: 1.2rem 1.35rem .9rem;
}

.pgr-filter-title {
    color: #111827;
    font-size: 1.2rem;
    font-weight: 800;
    margin: 0;
}

.pgr-filter-close {
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

.pgr-filter-section {
    border-top: 1px solid #edf1f5;
    padding: 1.05rem 1.35rem;
}

.pgr-filter-label {
    color: #718096;
    font-size: .9rem;
    font-weight: 800;
    margin-bottom: .75rem;
}

.pgr-filter-chip-row,
.pgr-filter-field-row,
.pgr-filter-action-row {
    display: flex;
    flex-wrap: wrap;
    gap: .75rem;
}

.pgr-filter-chip {
    background: #fff;
    border: 1px solid #e8edf4;
    border-radius: 999px;
    color: #111827;
    font-weight: 600;
    min-height: 2.45rem;
    padding: .45rem .9rem;
}

.pgr-filter-chip.is-active,
.pgr-filter-chip:hover {
    background: #eef6e8;
    border-color: #d9e9cf;
}

.pgr-filter-date,
.pgr-filter-select {
    background: #fff;
    border: 1px solid #e5eaf1;
    border-radius: 999px;
    color: #111827;
    min-height: 2.55rem;
    padding: .45rem .85rem;
}

.pgr-filter-date {
    min-width: 11rem;
}

.pgr-filter-select {
    min-width: 11rem;
}

.pgr-filter-apply,
.pgr-filter-reset {
    border: 0;
    border-radius: 999px;
    font-weight: 800;
    min-height: 2.55rem;
    padding: .45rem 1.2rem;
}

.pgr-filter-apply {
    background: #16869a;
    color: #fff;
}

.pgr-filter-reset {
    background: #eef2f7;
    color: #4b5563;
}

@media (max-width: 768px) {
    .pgr-toolbar {
        justify-content: stretch;
    }

    .pgr-filter-anchor,
    .pgr-search-shell,
    .pgr-filter-panel {
        max-width: none;
        width: 100%;
    }

    .pgr-filter-panel {
        left: 0;
        right: auto;
    }

    .pgr-filter-date,
    .pgr-filter-select,
    .pgr-filter-apply,
    .pgr-filter-reset {
        width: 100%;
    }
}
</style>

<ul class="nav nav-tabs pengiriman-tabs" id="tabPengirimanUtama" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="tab-daftar-link" data-toggle="tab" href="#tab-daftar" role="tab">
            List Pengiriman
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="tab-permintaan-link" data-toggle="tab" href="#tab-permintaan" role="tab">
            List Permintaan
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="tab-riwayat-link" data-toggle="tab" href="#tab-riwayat" role="tab">
            Cetak Surat Jalan
        </a>
    </li>
</ul>

<div class="tab-content border border-top-0 p-3" id="tabPengirimanUtamaContent">
    <div class="tab-pane fade show active" id="tab-daftar" role="tabpanel">
        <div class="pgr-toolbar">
            <div class="pgr-filter-anchor">
                <div class="pgr-search-shell">
                    <i class="fas fa-search"></i>
                    <input type="search" id="daftarSearchInput" class="pgr-search-input"
                        placeholder="Search anything..." aria-label="Search anything">
                    <button type="button" class="pgr-filter-toggle" id="daftarFilterToggle" title="Buka filter"
                        aria-controls="daftarFilterPanel" aria-expanded="false">
                        <i class="fas fa-sliders-h"></i>
                    </button>
                </div>

                <section class="pgr-filter-panel" id="daftarFilterPanel" aria-hidden="true">
                    <div class="pgr-filter-header">
                        <h3 class="pgr-filter-title">Filter</h3>
                        <button type="button" class="pgr-filter-close" id="daftarFilterClose" title="Tutup filter">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="pgr-filter-section">
                        <div class="pgr-filter-label">Filter by Date Range</div>
                        <div class="pgr-filter-chip-row">
                            <button type="button" class="pgr-filter-chip" data-range="30">Last 30 Days</button>
                            <button type="button" class="pgr-filter-chip" data-range="180">Last 6 Months</button>
                        </div>
                    </div>

                    <div class="pgr-filter-section">
                        <div class="pgr-filter-label">Custom Date Range</div>
                        <div class="pgr-filter-field-row">
                            <input type="date" name="tglawal" id="daftarTglawal" class="pgr-filter-date"
                                aria-label="Start date">
                            <input type="date" name="tglakhir" id="daftarTglakhir" class="pgr-filter-date"
                                aria-label="End date">
                        </div>
                    </div>

                    <div class="pgr-filter-section">
                        <div class="pgr-filter-label">Show Data</div>
                        <div class="pgr-filter-field-row">
                            <select id="daftarPageLength" class="pgr-filter-select" aria-label="Show data">
                                <option value="10">10 entries</option>
                                <option value="25">25 entries</option>
                                <option value="50" selected>50 entries</option>
                                <option value="100">100 entries</option>
                            </select>
                        </div>
                    </div>

                    <div class="pgr-filter-section">
                        <div class="pgr-filter-action-row">
                            <button type="button" class="pgr-filter-apply" id="daftarTombolTampil">Tampilkan</button>
                            <button type="button" class="pgr-filter-reset" id="daftarFilterReset">Reset</button>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <table id="dataPengirimanGabungan" class="table table-bordered table-striped dataTable dtr-inline collapsed"
            style="width:100%">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>No Surat Jalan</th>
                    <th>No. PO</th>
                    <th>Total</th>
                    <th>Rencana</th>
                    <th>Terkirim</th>
                    <th>Belum</th>
                    <th>User</th>
                    <th>Keterangan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>
    </div>

    <div class="tab-pane fade" id="tab-permintaan" role="tabpanel">
        <div class="pgr-toolbar">
            <div class="pgr-filter-anchor">
                <div class="pgr-search-shell">
                    <i class="fas fa-search"></i>
                    <input type="search" id="permintaanSearchInput" class="pgr-search-input"
                        placeholder="Search anything..." aria-label="Search anything">
                    <button type="button" class="pgr-filter-toggle" id="permintaanFilterToggle" title="Buka filter"
                        aria-controls="permintaanFilterPanel" aria-expanded="false">
                        <i class="fas fa-sliders-h"></i>
                    </button>
                </div>

                <section class="pgr-filter-panel" id="permintaanFilterPanel" aria-hidden="true">
                    <div class="pgr-filter-header">
                        <h3 class="pgr-filter-title">Filter</h3>
                        <button type="button" class="pgr-filter-close" id="permintaanFilterClose" title="Tutup filter">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="pgr-filter-section">
                        <div class="pgr-filter-label">Show Data</div>
                        <div class="pgr-filter-field-row">
                            <select id="permintaanPageLength" class="pgr-filter-select" aria-label="Show data">
                                <option value="10">10 entries</option>
                                <option value="25">25 entries</option>
                                <option value="50" selected>50 entries</option>
                                <option value="100">100 entries</option>
                            </select>
                        </div>
                    </div>

                    <div class="pgr-filter-section">
                        <div class="pgr-filter-action-row">
                            <button type="button" class="pgr-filter-reset" id="permintaanFilterReset">Reset</button>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <table id="dataPermintaanPengiriman" class="table table-bordered table-striped dataTable dtr-inline collapsed"
            style="width:100%">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>User</th>
                    <th>Total Produk (Pcs)</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>
    </div>

    <div class="tab-pane fade" id="tab-riwayat" role="tabpanel">
        <div class="pgr-toolbar">
            <div class="pgr-filter-anchor">
                <div class="pgr-search-shell">
                    <i class="fas fa-search"></i>
                    <input type="search" id="riwayatSearchInput" class="pgr-search-input"
                        placeholder="Search anything..." aria-label="Search anything">
                    <button type="button" class="pgr-filter-toggle" id="riwayatFilterToggle" title="Buka filter"
                        aria-controls="riwayatFilterPanel" aria-expanded="false">
                        <i class="fas fa-sliders-h"></i>
                    </button>
                </div>

                <section class="pgr-filter-panel" id="riwayatFilterPanel" aria-hidden="true">
                    <div class="pgr-filter-header">
                        <h3 class="pgr-filter-title">Filter</h3>
                        <button type="button" class="pgr-filter-close" id="riwayatFilterClose" title="Tutup filter">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="pgr-filter-section">
                        <div class="pgr-filter-label">Filter by Date Range</div>
                        <div class="pgr-filter-chip-row">
                            <button type="button" class="pgr-filter-chip" data-range="30">Last 30 Days</button>
                            <button type="button" class="pgr-filter-chip" data-range="180">Last 6 Months</button>
                        </div>
                    </div>

                    <div class="pgr-filter-section">
                        <div class="pgr-filter-label">Custom Date Range</div>
                        <div class="pgr-filter-field-row">
                            <input type="date" name="tglawal" id="tglawal" class="pgr-filter-date"
                                aria-label="Start date">
                            <input type="date" name="tglakhir" id="tglakhir" class="pgr-filter-date"
                                aria-label="End date">
                        </div>
                    </div>

                    <div class="pgr-filter-section">
                        <div class="pgr-filter-label">Show Data</div>
                        <div class="pgr-filter-field-row">
                            <select id="riwayatPageLength" class="pgr-filter-select" aria-label="Show data">
                                <option value="10">10 entries</option>
                                <option value="25">25 entries</option>
                                <option value="50" selected>50 entries</option>
                                <option value="100">100 entries</option>
                            </select>
                        </div>
                    </div>

                    <div class="pgr-filter-section">
                        <div class="pgr-filter-action-row">
                            <button type="button" class="pgr-filter-apply" id="tombolTampil">Tampilkan</button>
                            <button type="button" class="pgr-filter-reset" id="riwayatFilterReset">Reset</button>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <table id="databarangkeluar" class="table table-bordered table-striped dataTable dtr-inline collapsed"
            style="width:100%">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th>No Surat Jalan</th>
                    <th>No. PO</th>
                    <th>Tanggal</th>
                    <th>Jenis</th>
                    <th>Pelanggan</th>
                    <th>Terkirim (Pcs)</th>
                    <th>Gudang Asal</th>
                    <th style="width: 10%;">#</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<script>
let csrfToken = '<?= csrf_token() ?>';
let csrfHash = '<?= csrf_hash() ?>';
let tableDaftar;
let tablePermintaan;
let tableRiwayat;

$(document).ready(function() {
    tableDaftar = $('#dataPengirimanGabungan').DataTable({
        searching: true,
        searchDelay: 500,
        stateSave: true,
        stateDuration: -1,
        responsive: true,
        processing: true,
        serverSide: true,
        pageLength: 10,
        dom: "<'row'<'col-sm-12'tr>><'row align-items-center mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        order: [
            [1, 'desc']
        ],
        ajax: {
            url: '<?= site_url('barangkeluar/listDataPengiriman') ?>',
            type: 'POST',
            data: function(data) {
                data.tglawal = $('#daftarTglawal').val();
                data.tglakhir = $('#daftarTglakhir').val();
                data[csrfToken] = csrfHash;
            }
        },
        columns: [{
                data: 'nomor',
                orderable: false,
                className: 'text-center'
            },
            {
                data: 'tanggal',
                className: 'text-center'
            },
            {
                data: 'status_badge',
                orderable: false,
                className: 'text-center'
            },
            {
                data: 'dokumen'
            },
            {
                data: 'po'
            },
            {
                data: 'total_produk',
                className: 'text-right'
            },
            {
                data: 'rencana',
                className: 'text-right'
            },
            {
                data: 'terkirim',
                className: 'text-right'
            },
            {
                data: 'belum',
                className: 'text-right'
            },
            {
                data: 'usernama'
            },
            {
                data: 'keterangan'
            },
            {
                data: 'aksi',
                orderable: false,
                className: 'text-center'
            }
        ]
    });

    tablePermintaan = $('#dataPermintaanPengiriman').DataTable({
        searching: true,
        searchDelay: 500,
        stateSave: true,
        stateDuration: -1,
        processing: true,
        serverSide: true,
        responsive: true,
        pageLength: 10,
        dom: "<'row'<'col-sm-12'tr>><'row align-items-center mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        order: [
            [1, 'desc']
        ],
        ajax: {
            url: '<?= site_url('permintaanPengiriman/listData') ?>',
            type: 'POST',
            data: function(data) {
                data[csrfToken] = csrfHash;
            }
        },
        columns: [{
                data: 'nomor',
                orderable: false,
                className: 'text-center'
            },
            {
                data: 'tanggal',
                className: 'text-center'
            },
            {
                data: 'usernama'
            },
            {
                data: 'total_produk',
                className: 'text-right'
            },
            {
                data: 'status',
                className: 'text-center'
            },
            {
                data: 'aksi',
                orderable: false,
                className: 'text-center'
            }
        ]
    });

    tableRiwayat = $('#databarangkeluar').DataTable({
        searching: true,
        searchDelay: 500,
        stateSave: true,
        stateDuration: -1,
        responsive: true,
        processing: true,
        serverSide: true,
        pageLength: 10,
        dom: "<'row'<'col-sm-12'tr>><'row align-items-center mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        order: [
            [3, 'desc']
        ],
        ajax: {
            url: '<?= site_url('barangkeluar/listData') ?>',
            type: 'POST',
            data: function(data) {
                data.tglawal = $('#tglawal').val();
                data.tglakhir = $('#tglakhir').val();
                data[csrfToken] = csrfHash;
            }
        },
        columns: [{
                data: 'nomor',
                orderable: false,
                className: 'text-center'
            },
            {
                data: 'faktur'
            },
            {
                data: 'detpo'
            },
            {
                data: 'tglfaktur',
                className: 'text-center'
            },
            {
                data: 'jenis_badge',
                orderable: false,
                className: 'text-center'
            },
            {
                data: 'pelnama',
                className: 'text-center'
            },
            {
                data: 'qtykeluar',
                className: 'text-right'
            },
            {
                data: 'gdgnama',
                className: 'text-right'
            },
            {
                data: 'aksi',
                orderable: false,
                className: 'text-center'
            }
        ]
    });

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
            panel.find('.pgr-filter-chip').on('click', function() {
                const days = Number($(this).data('range')) || 30;
                const endDate = new Date();
                const startDate = new Date();
                startDate.setDate(endDate.getDate() - days);
                panel.find('.pgr-filter-chip').removeClass('is-active');
                $(this).addClass('is-active');
                $('#' + opts.tglawalId).val(formatDateInput(startDate));
                $('#' + opts.tglakhirId).val(formatDateInput(endDate));
            });
        }

        return {
            toggleFilterPanel
        };
    }

    const daftarFilter = setupFilterPanel({
        panelId: 'daftarFilterPanel',
        toggleId: 'daftarFilterToggle',
        closeId: 'daftarFilterClose',
        searchInputId: 'daftarSearchInput',
        pageLengthId: 'daftarPageLength',
        tglawalId: 'daftarTglawal',
        tglakhirId: 'daftarTglakhir',
        table: tableDaftar
    });

    const permintaanFilter = setupFilterPanel({
        panelId: 'permintaanFilterPanel',
        toggleId: 'permintaanFilterToggle',
        closeId: 'permintaanFilterClose',
        searchInputId: 'permintaanSearchInput',
        pageLengthId: 'permintaanPageLength',
        table: tablePermintaan
    });

    const riwayatFilter = setupFilterPanel({
        panelId: 'riwayatFilterPanel',
        toggleId: 'riwayatFilterToggle',
        closeId: 'riwayatFilterClose',
        searchInputId: 'riwayatSearchInput',
        pageLengthId: 'riwayatPageLength',
        tglawalId: 'tglawal',
        tglakhirId: 'tglakhir',
        table: tableRiwayat
    });

    $('#tombolTampil').on('click', function() {
        tableRiwayat.ajax.reload();
        riwayatFilter.toggleFilterPanel(false);
    });

    $('#daftarTombolTampil').on('click', function() {
        tableDaftar.ajax.reload();
        daftarFilter.toggleFilterPanel(false);
    });

    $('#daftarFilterReset').on('click', function() {
        $('#daftarTglawal').val('');
        $('#daftarTglakhir').val('');
        $('#daftarFilterPanel .pgr-filter-chip').removeClass('is-active');
        $('#daftarPageLength').val('50');
        $('#daftarSearchInput').val('');
        tableDaftar.search('').page.len(50);
        tableDaftar.ajax.reload();
    });

    $('#permintaanFilterReset').on('click', function() {
        $('#permintaanPageLength').val('50');
        $('#permintaanSearchInput').val('');
        tablePermintaan.search('').page.len(50);
        tablePermintaan.ajax.reload();
    });

    $('#riwayatFilterReset').on('click', function() {
        $('#tglawal').val('');
        $('#tglakhir').val('');
        $('#riwayatFilterPanel .pgr-filter-chip').removeClass('is-active');
        $('#riwayatPageLength').val('50');
        $('#riwayatSearchInput').val('');
        tableRiwayat.search('').page.len(50);
        tableRiwayat.ajax.reload();
    });

    $('a[data-toggle="tab"]').on('shown.bs.tab', function(event) {
        const target = $(event.target).attr('href');
        if (target === '#tab-daftar') {
            tableDaftar.columns.adjust().responsive.recalc();
        } else if (target === '#tab-permintaan') {
            tablePermintaan.columns.adjust().responsive.recalc();
        } else if (target === '#tab-riwayat') {
            tableRiwayat.columns.adjust().responsive.recalc();
        }
        window.location.hash = target.replace('#tab-', '');
    });

    const initialHash = window.location.hash.replace('#', '');
    if (initialHash) {
        $('#tab-' + initialHash + '-link').tab('show');
    }
});

function proses(id) {
    location.href = '/permintaanPengiriman/proses/' + id;
}

function cetak(id) {
    location.href = '/permintaanPengiriman/pilih-cetak/' + id;
}

function hapusPengiriman(id) {
    showBootstrapModal({
        title: 'Hapus Permintaan?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus'
    }).then(function(result) {
        if (!result.isConfirmed) return;

        $.post('/permintaanPengiriman/hapus', {
            [csrfToken]: csrfHash,
            id: id
        }, function(response) {
            showBootstrapModal('Berhasil', response.sukses, 'success');
            tablePermintaan.ajax.reload();
            tableDaftar.ajax.reload();
        }, 'json');
    });
}

function escapeHtmlPengiriman(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function editDokumenPengiriman(noDoHash) {
    showBootstrapModal({
        title: 'Memuat dokumen...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    $.ajax({
        type: 'post',
        url: '<?= site_url('barangkeluar/dokumen-pengiriman') ?>',
        data: {
            [csrfToken]: csrfHash,
            no_do_hash: noDoHash
        },
        dataType: 'json',
        success: function(response) {
            if (response.error) {
                showBootstrapModal('Gagal', response.error, 'error');
                return;
            }

            const daftarItem = response.data || [];
            if (daftarItem.length === 0) {
                showBootstrapModal('Gagal', 'Data dokumen pengiriman tidak ditemukan.', 'error');
                return;
            }

            const adaBanyakItem = daftarItem.length > 1;

            const formatQty = (qty) => Number(qty || 0).toLocaleString('id-ID');
            const labelItemDasar = (item) =>
                `${item.no_po} - ${item.kode_produk} - Qty ${formatQty(item.qty)}`;
            const labelItemText = (item) => labelItemDasar(item) + (item.no_btb ? ` (${item.no_btb})` : '');
            const labelItem = (item) => {
                const dasar =
                    `<span style="font-weight:normal;">${escapeHtmlPengiriman(labelItemDasar(item))}</span>`;
                return item.no_btb ?
                    `${dasar} <strong>(${escapeHtmlPengiriman(item.no_btb)})</strong>` : dasar;
            };

            const daftarCheckbox = daftarItem.map(item => `
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input btb-item-checkbox" id="btbItem${item.id}" value="${item.id}" checked>
                        <label class="form-check-label" for="btbItem${item.id}">${labelItem(item)}</label>
                    </div>
                `).join('');

            const bagianItem = adaBanyakItem ? `
                    <div class="form-group mb-2">
                        <label class="mb-1">Pilih Item (bisa lebih dari 1)</label>
                        <div class="form-check mb-1" style="border-bottom:1px solid #eee;padding-bottom:4px;">
                            <input type="checkbox" class="form-check-input" id="btbPilihSemua" checked>
                            <label class="form-check-label" for="btbPilihSemua"><strong>Pilih Semua</strong> (${daftarItem.length} item)</label>
                        </div>
                        <div style="max-height:180px; overflow-y:auto;">
                            ${daftarCheckbox}
                        </div>
                    </div>
                ` : `
                    <div class="form-group mb-2">
                        <label class="mb-1">Item</label>
                        <input type="text" class="form-control" value="${escapeHtmlPengiriman(labelItemText(daftarItem[0]))}" readonly>
                        <input type="checkbox" class="btb-item-checkbox" value="${daftarItem[0].id}" checked style="display:none;">
                    </div>
                `;

            showBootstrapModal({
                title: 'Dokumen BTB',
                html: `
                        <div class="text-left">
                            <div class="form-group mb-2">
                                <label class="mb-1">No Surat Jalan</label>
                                <input type="text" class="form-control" value="${escapeHtmlPengiriman(daftarItem[0].no_do)}" readonly>
                            </div>
                            ${bagianItem}

                            <div id="btbViewArea">
                                <div class="mb-2"><strong>No BTB:</strong> <span id="btbViewNoBtb">-</span></div>
                                <div class="mb-3"><strong>File:</strong> <span id="btbViewFile">-</span></div>
                                <div class="d-flex" style="gap:8px;">
                                    <button type="button" class="btn btn-primary btn-sm" id="btnEditBtb"><i class="fa fa-edit"></i> Edit</button>
                                    <button type="button" class="btn btn-danger btn-sm" id="btnHapusBtb"><i class="fa fa-trash-alt"></i> Hapus</button>
                                </div>
                            </div>

                            <div id="btbEditArea" style="display:none;">
                                <div class="form-group mb-2">
                                    <label for="noBtbPengiriman" class="mb-1">No BTB</label>
                                    <input type="text" id="noBtbPengiriman" class="form-control" maxlength="100" placeholder="Isi No BTB">
                                </div>
                                <div class="form-group mb-2">
                                    <label for="fileBtbPengiriman" class="mb-1">Upload File BTB</label>
                                    <input type="file" id="fileBtbPengiriman" class="form-control" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png">
                                    <small class="text-muted">Format PDF/JPG/PNG, maksimal 5 MB. Upload baru akan mengganti file lama.</small>
                                </div>
                                <div class="d-flex" style="gap:8px;">
                                    <button type="button" class="btn btn-success btn-sm" id="btnSimpanBtb"><i class="fa fa-save"></i> Simpan</button>
                                    <button type="button" class="btn btn-secondary btn-sm" id="btnBatalBtb">Batal</button>
                                </div>
                            </div>
                        </div>
                    `,
                width: 620,
                showConfirmButton: false,
                showCancelButton: true,
                cancelButtonText: 'Tutup',
                didOpen: () => {
                    const idTerpilih = () => $('.btb-item-checkbox:checked').map(function() {
                        return parseInt(this.value, 10);
                    }).get();

                    const tampilkanView = function() {
                        $('#btbEditArea').hide();
                        $('#btbViewArea').show();

                        const ids = idTerpilih();
                        if (ids.length === 0) {
                            $('#btbViewNoBtb').text('-');
                            $('#btbViewFile').html(
                                '<span class="text-muted">Pilih minimal 1 item.</span>');
                            $('#btnEditBtb').prop('disabled', true);
                            $('#btnHapusBtb').prop('disabled', true);
                            return;
                        }
                        $('#btnEditBtb').prop('disabled', false);

                        const dipilih = daftarItem.filter(item => ids.includes(item.id));
                        const semuaSama = dipilih.every(item => item.no_btb === dipilih[0]
                            .no_btb && item.btb_file_url === dipilih[0].btb_file_url);

                        if (!semuaSama) {
                            $('#btbViewNoBtb').text('-');
                            $('#btbViewFile').html(
                                '<span class="text-muted">Item-item terpilih punya No BTB / file berbeda-beda. Isi baru untuk menerapkan ke semua item terpilih.</span>'
                                );
                            $('#btnHapusBtb').prop('disabled', false);
                            return;
                        }

                        const item = dipilih[0];
                        $('#btbViewNoBtb').text(item.no_btb || '-');
                        $('#btbViewFile').html(item.btb_file_url ?
                            `<a href="${escapeHtmlPengiriman(item.btb_file_url)}" target="_blank">${escapeHtmlPengiriman(item.btb_original_name || 'Lihat file BTB')}</a>` :
                            '<span class="text-muted">Belum ada file BTB.</span>');
                        $('#btnHapusBtb').prop('disabled', !item.no_btb && !item
                            .btb_file_url);
                    };

                    tampilkanView();

                    if (adaBanyakItem) {
                        $('#btbPilihSemua').on('change', function() {
                            $('.btb-item-checkbox').prop('checked', this.checked);
                            tampilkanView();
                        });

                        $('.btb-item-checkbox').on('change', function() {
                            const totalChecked = $('.btb-item-checkbox:checked').length;
                            $('#btbPilihSemua').prop('checked', totalChecked ===
                                daftarItem.length);
                            tampilkanView();
                        });
                    }

                    $('#btnEditBtb').on('click', function() {
                        const ids = idTerpilih();
                        const dipilih = daftarItem.filter(item => ids.includes(item
                        .id));
                        const semuaSama = dipilih.length > 0 && dipilih.every(item =>
                            item.no_btb === dipilih[0].no_btb);
                        $('#noBtbPengiriman').val(semuaSama ? (dipilih[0].no_btb ||
                            '') : '');
                        document.getElementById('fileBtbPengiriman').value = '';
                        $('#btbViewArea').hide();
                        $('#btbEditArea').show();
                    });

                    $('#btnBatalBtb').on('click', function() {
                        $('#btbEditArea').hide();
                        $('#btbViewArea').show();
                    });

                    $('#btnSimpanBtb').on('click', function() {
                        const ids = idTerpilih();
                        if (ids.length === 0) {
                            showBootstrapModal('Gagal', 'Pilih minimal 1 item.',
                                'error');
                            return;
                        }

                        const formData = new FormData();
                        const fileInput = document.getElementById('fileBtbPengiriman');

                        formData.append(csrfToken, csrfHash);
                        formData.append('no_do_hash', noDoHash);
                        ids.forEach(id => formData.append('ids[]', id));
                        formData.append('no_btb', document.getElementById(
                            'noBtbPengiriman').value.trim());

                        if (fileInput.files.length > 0) {
                            formData.append('btb_file', fileInput.files[0]);
                        }

                        Swal.showLoading();
                        $.ajax({
                            type: 'post',
                            url: '<?= site_url('barangkeluar/simpan-dokumen-pengiriman') ?>',
                            data: formData,
                            dataType: 'json',
                            processData: false,
                            contentType: false,
                            success: function(saveResponse) {
                                if (saveResponse.error) {
                                    showBootstrapModal('Gagal', saveResponse
                                        .error, 'error').then(() =>
                                        editDokumenPengiriman(noDoHash));
                                    return;
                                }

                                if (tableDaftar) tableDaftar.ajax.reload(
                                    null, false);
                                if (tableRiwayat) tableRiwayat.ajax.reload(
                                    null, false);

                                showBootstrapModal('Berhasil', saveResponse
                                    .sukses, 'success').then(() =>
                                    editDokumenPengiriman(noDoHash));
                            },
                            error: function(xhr, ajaxOptions, thrownError) {
                                showBootstrapModal('Gagal', xhr.status +
                                    ' ' + thrownError, 'error').then(
                                () => editDokumenPengiriman(noDoHash));
                            }
                        });
                    });

                    $('#btnHapusBtb').on('click', function() {
                        const ids = idTerpilih();
                        if (ids.length === 0) {
                            showBootstrapModal('Gagal', 'Pilih minimal 1 item.',
                                'error');
                            return;
                        }

                        showBootstrapModal({
                            title: 'Hapus Dokumen BTB?',
                            text: 'No BTB & file BTB untuk ' + ids.length +
                                ' item terpilih akan dihapus.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Ya, hapus',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (!result.isConfirmed) return;

                            const dataHapus = {
                                [csrfToken]: csrfHash,
                                no_do_hash: noDoHash,
                                ids: ids
                            };

                            $.post('/barangkeluar/hapus-dokumen-pengiriman',
                                dataHapus,
                                function(hapusResponse) {
                                    if (hapusResponse.error) {
                                        showBootstrapModal('Gagal',
                                            hapusResponse.error, 'error'
                                            );
                                        return;
                                    }

                                    if (tableDaftar) tableDaftar.ajax
                                        .reload(null, false);
                                    if (tableRiwayat) tableRiwayat.ajax
                                        .reload(null, false);

                                    showBootstrapModal('Berhasil',
                                            hapusResponse.sukses, 'success')
                                        .then(() => editDokumenPengiriman(
                                            noDoHash));
                                }, 'json');
                        });
                    });
                }
            });
        },
        error: function(xhr, ajaxOptions, thrownError) {
            showBootstrapModal('Gagal', xhr.status + '\n' + thrownError, 'error');
        }
    });
}

function lihatDo(faktur) {
    const url = "<?= site_url('barangkeluar/cetak-do') ?>/" + encodeURIComponent(faktur) + "?preview=1";
    window.open(url, '_blank', 'noopener');
}

function cetakDo(faktur) {
    const escapeHtml = (value) => String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    showBootstrapModal({
        title: 'Memuat data DO...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    $.ajax({
        type: "get",
        url: "<?= site_url('barangkeluar/detail-do') ?>/" + faktur,
        dataType: "json",
        success: function(response) {
            if (response.error) {
                showBootstrapModal('Gagal', response.error, 'error');
                return;
            }

            const detailRows = (response.details || []).map((item, index) => `
                    <div class="mb-2 p-2 border rounded">
                        <div class="mb-1">
                            <strong>${index + 1}. ${escapeHtml(item.kode)}</strong><br>
                            <small>${escapeHtml(item.nama)} - Qty ${escapeHtml(item.qty)}</small>
                        </div>
                        <textarea class="form-control note-do-produk" data-id="${escapeHtml(item.id)}" rows="2" placeholder="Note untuk produk ini, boleh kosong"></textarea>
                    </div>
                `).join('');

            showBootstrapModal({
                title: 'Print Delivery Order',
                html: `
                        <div class="text-left">
                            <label for="formatDo" class="mb-1">Format Cetak</label>
                            <select id="formatDo" class="form-control" style="margin: 0 0 12px 0; width: 100%;">
                                <option value="lengkap">Format Lengkap</option>
                                <option value="ringkas">Format Form Kosong (isi data saja)</option>
                            </select>

                            <label for="noKendaraanDo" class="mb-1">No Kendaraan</label>
                            <input type="text" id="noKendaraanDo" class="form-control" placeholder="Contoh: B 9739 OH" style="margin: 0 0 12px 0; width: 100%;">

                            <label for="penerimaDo" class="mb-1">Yang Menerima Barang</label>
                            <input type="text" id="penerimaDo" class="form-control" placeholder="Nama penerima (opsional)" style="margin: 0 0 12px 0; width: 100%;">

                            <div id="pengirimDoGroup">
                                <label for="pengirimDo" class="mb-1">Pengirim Barang</label>
                                <input type="text" id="pengirimDo" class="form-control" placeholder="Contoh: Eka" style="margin: 0 0 12px 0; width: 100%;">
                            </div>

                            <div id="notesDoGroup">
                                <label class="mb-1">Note per produk <small>(boleh kosong)</small></label>
                                <div style="max-height: 260px; overflow-y: auto;">
                                    ${detailRows || '<div class="text-muted">Tidak ada produk.</div>'}
                                </div>
                            </div>
                        </div>
                    `,
                icon: 'question',
                width: 650,
                didOpen: () => {
                    const formatSelect = document.getElementById('formatDo');
                    const pengirimGroup = document.getElementById('pengirimDoGroup');
                    const notesGroup = document.getElementById('notesDoGroup');
                    const toggleOptionalFields = () => {
                        const ringkas = formatSelect.value === 'ringkas';
                        pengirimGroup.style.display = 'block';
                        notesGroup.style.display = ringkas ? 'none' : 'block';
                    };
                    formatSelect.addEventListener('change', toggleOptionalFields);
                    toggleOptionalFields();
                },
                showCancelButton: true,
                confirmButtonText: 'Print',
                cancelButtonText: 'Batal',
                preConfirm: () => {
                    const notes = {};
                    document.querySelectorAll('.note-do-produk').forEach((textarea) => {
                        const note = textarea.value.trim();
                        if (note !== '') {
                            notes[textarea.dataset.id] = note;
                        }
                    });

                    return {
                        format: document.getElementById('formatDo').value,
                        kendaraan: document.getElementById('noKendaraanDo').value.trim(),
                        penerimaBarang: document.getElementById('penerimaDo').value.trim(),
                        pengirim: document.getElementById('pengirimDo').value.trim(),
                        notes: notes
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const params = new URLSearchParams({
                        format: result.value.format,
                        kendaraan: result.value.kendaraan,
                        penerimaBarang: result.value.penerimaBarang,
                        pengirimBarang: result.value.pengirim,
                        pengirim: result.value.pengirim,
                        notes: JSON.stringify(result.value.notes)
                    });

                    let windowCetak = window.open('<?= site_url('barangkeluar/cetak-do') ?>/' +
                        encodeURIComponent(faktur) + '?' + params.toString(),
                        "Cetak DO",
                        "width=900,height=700");
                    windowCetak.focus();
                }
            });
        },
        error: function(xhr, ajaxOptions, thrownError) {
            showBootstrapModal('Gagal', xhr.status + '\n' + thrownError, 'error');
        }
    });
}

function hapusPengirimanLangsung(id) {
    showBootstrapModal({
        title: 'Hapus Pengiriman?',
        text: "Stok yang tadi terkirim akan dikembalikan, dan surat jalannya ikut terhapus. Yakin dihapus?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Hapus !'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "post",
                url: '<?= site_url('barangkeluar/hapusPengirimanLangsung') ?>',
                data: {
                    [csrfToken]: csrfHash,
                    id: id
                },
                dataType: "json",
                success: function(response) {
                    if (response.error) {
                        showBootstrapModal('Gagal', response.error, 'error');
                        return;
                    }
                    showBootstrapModal('Berhasil', response.sukses, 'success');
                    tableDaftar.ajax.reload();
                    tableRiwayat.ajax.reload();
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    showBootstrapModal('Error', xhr.status + '\n' + thrownError, 'error');
                }
            });
        }
    });
}

function hapusSuratJalan(hash) {
    showBootstrapModal({
        title: 'Hapus Surat Jalan ini?',
        text: "Cuma surat jalan ini yang dihapus & stoknya dikembalikan -- surat jalan lain dalam sesi input yang sama (kalau ada) tidak ikut terhapus. Yakin dihapus?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Hapus !'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "post",
                url: '<?= site_url('barangkeluar/hapusSuratJalanLangsung') ?>',
                data: {
                    [csrfToken]: csrfHash,
                    hash: hash
                },
                dataType: "json",
                success: function(response) {
                    if (response.error) {
                        showBootstrapModal('Gagal', response.error, 'error');
                        return;
                    }
                    showBootstrapModal('Berhasil', response.sukses, 'success');
                    tableDaftar.ajax.reload();
                    tableRiwayat.ajax.reload();
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    showBootstrapModal('Error', xhr.status + '\n' + thrownError, 'error');
                }
            });
        }
    });
}

function edit(faktur) {
    window.location.href = ('/barangkeluar/edit/') + faktur;
}

function lanjutkanPengiriman(hash) {
    window.location.href = '/permintaanPengiriman/langsung/' + hash;
}
</script>

<?= $this->endSection('isi') ?>