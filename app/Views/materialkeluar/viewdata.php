<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Pemakaian Material
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>
<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<style>
    .mk-toolbar {
        align-items: flex-start;
        display: flex;
        justify-content: flex-end;
        margin-bottom: 1rem;
        position: relative;
        z-index: 30;
    }

    .mk-filter-anchor {
        align-items: flex-end;
        display: flex;
        flex-direction: column;
        position: relative;
        width: min(100%, 32rem);
    }

    .mk-search-shell {
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

    .mk-search-shell > i {
        color: #6b7280;
        font-size: 1.1rem;
    }

    .mk-search-input {
        background: transparent;
        border: 0;
        box-shadow: none;
        color: #4b5563;
        flex: 1 1 auto;
        font-size: .95rem;
        min-width: 0;
        outline: 0;
    }

    .mk-search-input:focus {
        box-shadow: none;
        outline: 0;
    }

    .mk-filter-toggle {
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

    .mk-filter-toggle:hover,
    .mk-filter-toggle:focus {
        background: #126f7f;
        color: #fff;
        outline: 0;
    }

    .mk-filter-panel {
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

    .mk-filter-panel.is-visible {
        display: block;
    }

    .mk-filter-panel.is-open {
        opacity: 1;
        pointer-events: auto;
        transform: translateY(0) scale(1);
    }

    .mk-filter-header {
        align-items: center;
        display: flex;
        justify-content: space-between;
        padding: 1.2rem 1.35rem .9rem;
    }

    .mk-filter-title {
        color: #111827;
        font-size: 1.2rem;
        font-weight: 800;
        margin: 0;
    }

    .mk-filter-close {
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

    .mk-filter-section {
        border-top: 1px solid #edf1f5;
        padding: 1.05rem 1.35rem;
    }

    .mk-filter-label {
        color: #718096;
        font-size: .9rem;
        font-weight: 800;
        margin-bottom: .75rem;
    }

    .mk-filter-chip-row,
    .mk-filter-field-row,
    .mk-filter-action-row {
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
    }

    .mk-filter-chip {
        background: #fff;
        border: 1px solid #e8edf4;
        border-radius: 999px;
        color: #111827;
        font-weight: 600;
        min-height: 2.45rem;
        padding: .45rem .9rem;
    }

    .mk-filter-chip.is-active,
    .mk-filter-chip:hover {
        background: #eef6e8;
        border-color: #d9e9cf;
    }

    .mk-filter-date,
    .mk-filter-select {
        background: #fff;
        border: 1px solid #e5eaf1;
        border-radius: 999px;
        color: #111827;
        min-height: 2.55rem;
        padding: .45rem .85rem;
    }

    .mk-filter-date {
        min-width: 11rem;
    }

    .mk-filter-select {
        min-width: 11rem;
    }

    .mk-filter-apply,
    .mk-filter-reset {
        border: 0;
        border-radius: 999px;
        font-weight: 800;
        min-height: 2.55rem;
        padding: .45rem 1.2rem;
    }

    .mk-filter-apply {
        background: #16869a;
        color: #fff;
    }

    .mk-filter-reset {
        background: #eef2f7;
        color: #4b5563;
    }

    @media (max-width: 768px) {
        .mk-toolbar {
            justify-content: stretch;
        }

        .mk-filter-anchor,
        .mk-search-shell,
        .mk-filter-panel {
            max-width: none;
            width: 100%;
        }

        .mk-filter-panel {
            left: 0;
            right: auto;
        }

        .mk-filter-date,
        .mk-filter-select,
        .mk-filter-apply,
        .mk-filter-reset {
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

<div class="alert alert-info">
    <i class="fa fa-info-circle"></i> Halaman ini nampilin material yang kepakai buat Produksi, otomatis kecatat begitu ada transaksi "Selesai Produksi" -- nggak perlu input manual di sini.
</div>

<div class="mk-toolbar">
    <div class="mk-filter-anchor">
        <div class="mk-search-shell">
            <i class="fas fa-search"></i>
            <input type="search" id="mkSearchInput" class="mk-search-input" placeholder="Search anything..." aria-label="Search anything">
            <button type="button" class="mk-filter-toggle" id="mkFilterToggle" title="Buka filter" aria-controls="mkFilterPanel" aria-expanded="false">
                <i class="fas fa-sliders-h"></i>
            </button>
        </div>

        <section class="mk-filter-panel" id="mkFilterPanel" aria-hidden="true">
            <div class="mk-filter-header">
                <h3 class="mk-filter-title">Filter</h3>
                <button type="button" class="mk-filter-close" id="mkFilterClose" title="Tutup filter">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="mk-filter-section">
                <div class="mk-filter-label">Filter by Date Range</div>
                <div class="mk-filter-chip-row">
                    <button type="button" class="mk-filter-chip" data-range="30">Last 30 Days</button>
                    <button type="button" class="mk-filter-chip" data-range="180">Last 6 Months</button>
                </div>
            </div>

            <div class="mk-filter-section">
                <div class="mk-filter-label">Custom Date Range</div>
                <div class="mk-filter-field-row">
                    <input type="date" name="tglawal" id="tglawal" class="mk-filter-date" aria-label="Start date">
                    <input type="date" name="tglakhir" id="tglakhir" class="mk-filter-date" aria-label="End date">
                </div>
            </div>

            <div class="mk-filter-section">
                <div class="mk-filter-label">Show Data</div>
                <div class="mk-filter-field-row">
                    <select id="mkPageLength" class="mk-filter-select" aria-label="Show data">
                        <option value="10">10 entries</option>
                        <option value="25">25 entries</option>
                        <option value="50" selected>50 entries</option>
                        <option value="100">100 entries</option>
                    </select>
                </div>
            </div>

            <div class="mk-filter-section">
                <div class="mk-filter-action-row">
                    <button type="button" class="mk-filter-apply" id="tombolTampil">Tampilkan</button>
                    <button type="button" class="mk-filter-reset" id="mkFilterReset">Reset</button>
                </div>
            </div>
        </section>
    </div>
</div>
<table id="datamaterialkeluar" class="table table-bordered table-striped table-hover dataTable dtr-inline collapsed" style="width: 100%;">
    <thead>
        <tr>
            <th style="width: 5%;">No</th>
            <th>No. Produksi</th>
            <th>Tanggal</th>
            <th>Produk yang Dibuat</th>
            <th>Kode Material</th>
            <th>Nama Material</th>
            <th>Qty Terpakai</th>
            <th>Satuan</th>
            <th>Gudang</th>
        </tr>
    </thead>
    <tbody>

    </tbody>
</table>
<script>
    let csrfToken = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';
    var table;

    $(document).ready(function() {
        table = $('#datamaterialkeluar').DataTable({
            searching: true,
            searchDelay: 500,
            stateSave: true,
            stateDuration: -1,
            responsive: true,
            processing: true,
            serverSide: true,
            dom: "<'row'<'col-sm-12'tr>><'row align-items-center mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            ajax: {
                url: '<?= site_url('materialkeluar/listData') ?>',
                type: 'POST',
                data: function(d) {
                    d.tglawal = $('#tglawal').val();
                    d.tglakhir = $('#tglakhir').val();
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
                    data: 'no_produksi'
                },
                {
                    data: 'tgl_produksi',
                    className: 'text-center'
                },
                {
                    data: 'nama_produk',
                    className: 'text-center'
                },
                {
                    data: 'kode_material',
                    className: 'text-center'
                },
                {
                    data: 'nama_material'
                },
                {
                    data: 'qty_material',
                    className: 'text-right'
                },
                {
                    data: 'satuan',
                    className: 'text-center'
                },
                {
                    data: 'gdgnama',
                    className: 'text-center'
                },
            ]
        });
        $('#mkSearchInput').val(table.search());

        const filterPanel = $('#mkFilterPanel');
        const filterToggle = $('#mkFilterToggle');
        const filterClose = $('#mkFilterClose');
        let searchTimer = null;
        let filterPanelTimer = null;

        function toggleFilterPanel(forceOpen = null) {
            const willOpen = forceOpen === null ? !filterPanel.hasClass('is-open') : forceOpen;
            window.clearTimeout(filterPanelTimer);

            if (willOpen) {
                filterPanel.addClass('is-visible');
                window.requestAnimationFrame(function() {
                    filterPanel.addClass('is-open');
                });
            } else {
                filterPanel.removeClass('is-open');
                filterPanelTimer = window.setTimeout(function() {
                    filterPanel.removeClass('is-visible');
                }, 180);
            }

            filterPanel.attr('aria-hidden', willOpen ? 'false' : 'true');
            filterToggle.attr('aria-expanded', willOpen ? 'true' : 'false');
        }

        function formatDateInput(date) {
            return date.toISOString().slice(0, 10);
        }

        filterToggle.on('click', function() {
            toggleFilterPanel();
        });

        filterClose.on('click', function() {
            toggleFilterPanel(false);
        });

        $('#mkSearchInput').on('input', function() {
            const keyword = this.value;
            window.clearTimeout(searchTimer);
            searchTimer = window.setTimeout(function() {
                table.search(keyword).draw();
            }, 350);
        });

        $('.mk-filter-chip').on('click', function() {
            const days = Number($(this).data('range')) || 30;
            const endDate = new Date();
            const startDate = new Date();
            startDate.setDate(endDate.getDate() - days);
            $('.mk-filter-chip').removeClass('is-active');
            $(this).addClass('is-active');
            $('#tglawal').val(formatDateInput(startDate));
            $('#tglakhir').val(formatDateInput(endDate));
        });

        $('#mkPageLength').on('change', function() {
            table.page.len(Number(this.value)).draw();
        });

        $('#tombolTampil').on('click', function() {
            table.ajax.reload();
            toggleFilterPanel(false);
        });

        $('#mkFilterReset').on('click', function() {
            $('#tglawal').val('');
            $('#tglakhir').val('');
            $('.mk-filter-chip').removeClass('is-active');
            $('#mkPageLength').val('50');
            $('#mkSearchInput').val('');
            table.search('').page.len(50);
            table.ajax.reload();
        });
    });
</script>

<?= $this->endSection('isi') ?>
