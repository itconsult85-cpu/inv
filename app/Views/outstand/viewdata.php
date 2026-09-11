<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Data Outstanding
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>

<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<script src="<?= base_url() ?>/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<style>
    .hide-row {
        display: none;
    }

    .outstanding-toolbar {
        align-items: center;
        display: flex;
        justify-content: flex-end;
        margin-bottom: 1rem;
        position: relative;
        z-index: 30;
    }

    .outstanding-filter-anchor {
        align-items: flex-end;
        display: flex;
        flex-direction: column;
        position: relative;
        width: min(100%, 32rem);
    }

    .outstanding-search-shell {
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

    .outstanding-search-shell > i {
        color: #6b7280;
        font-size: 1.1rem;
    }

    .outstanding-search-input {
        background: transparent;
        border: 0;
        box-shadow: none;
        color: #4b5563;
        flex: 1 1 auto;
        font-size: .95rem;
        min-width: 0;
        outline: 0;
    }

    .outstanding-search-input:focus {
        box-shadow: none;
        outline: 0;
    }

    .outstanding-filter-toggle {
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

    .outstanding-filter-toggle:hover,
    .outstanding-filter-toggle:focus {
        background: #126f7f;
        color: #fff;
        outline: 0;
    }

    .outstanding-filter-panel {
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
        width: 42rem;
    }

    .outstanding-filter-panel.is-visible {
        display: block;
    }

    .outstanding-filter-panel.is-open {
        opacity: 1;
        pointer-events: auto;
        transform: translateY(0) scale(1);
    }

    .outstanding-filter-header {
        align-items: center;
        display: flex;
        justify-content: space-between;
        padding: 1.2rem 1.35rem .9rem;
    }

    .outstanding-filter-title {
        color: #111827;
        font-size: 1.2rem;
        font-weight: 800;
        margin: 0;
    }

    .outstanding-filter-close {
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

    .outstanding-filter-section {
        border-top: 1px solid #edf1f5;
        padding: 1.05rem 1.35rem;
    }

    .outstanding-filter-label {
        color: #718096;
        font-size: .9rem;
        font-weight: 800;
        margin-bottom: .75rem;
    }

    .outstanding-filter-chip-row,
    .outstanding-filter-field-row,
    .outstanding-filter-action-row {
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
    }

    .outstanding-filter-chip {
        background: #fff;
        border: 1px solid #e8edf4;
        border-radius: 999px;
        color: #111827;
        font-weight: 600;
        min-height: 2.45rem;
        padding: .45rem .9rem;
    }

    .outstanding-filter-chip.is-active,
    .outstanding-filter-chip:hover {
        background: #eef6e8;
        border-color: #d9e9cf;
    }

    .outstanding-filter-date,
    .outstanding-filter-select {
        background: #fff;
        border: 1px solid #e5eaf1;
        border-radius: 999px;
        color: #111827;
        min-height: 2.55rem;
        padding: .45rem .85rem;
    }

    .outstanding-filter-date {
        min-width: 11rem;
    }

    .outstanding-filter-select {
        min-width: 9rem;
    }

    .outstanding-filter-apply,
    .outstanding-filter-reset {
        border: 0;
        border-radius: 999px;
        font-weight: 800;
        min-height: 2.55rem;
        padding: .45rem 1.2rem;
    }

    .outstanding-filter-apply {
        background: #16869a;
        color: #fff;
    }

    .outstanding-filter-reset {
        background: #eef2f7;
        color: #4b5563;
    }

    @media (max-width: 768px) {
        .outstanding-toolbar {
            justify-content: stretch;
        }

        .outstanding-filter-anchor,
        .outstanding-search-shell,
        .outstanding-filter-panel {
            max-width: none;
            width: 100%;
        }

        .outstanding-filter-panel {
            left: 0;
            right: auto;
        }

        .outstanding-filter-date,
        .outstanding-filter-select,
        .outstanding-filter-apply,
        .outstanding-filter-reset {
            width: 100%;
        }
    }
</style>
<div class="outstanding-toolbar">
    <button type="button" id="outstandingBtnCetak" class="btn btn-primary btn-sm" style="margin-right:.75rem; height:2.2rem;" data-toggle="modal" data-target="#modalCetakOutstanding">
        <i class="fas fa-print"></i> Cetak
    </button>
    <div class="outstanding-filter-anchor">
        <div class="outstanding-search-shell">
            <i class="fas fa-search"></i>
            <input type="search" id="outstandingSearchInput" class="outstanding-search-input" placeholder="Search anything..." aria-label="Search anything">
            <button type="button" class="outstanding-filter-toggle" id="outstandingFilterToggle" title="Buka filter" aria-controls="outstandingFilterPanel" aria-expanded="false">
                <i class="fas fa-sliders-h"></i>
            </button>
        </div>

        <section class="outstanding-filter-panel" id="outstandingFilterPanel" aria-hidden="true">
            <div class="outstanding-filter-header">
                <h3 class="outstanding-filter-title">Filter</h3>
                <button type="button" class="outstanding-filter-close" id="outstandingFilterClose" title="Tutup filter">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="outstanding-filter-section">
                <div class="outstanding-filter-label">Filter by Date Range</div>
                <div class="outstanding-filter-chip-row">
                    <button type="button" class="outstanding-filter-chip" data-range="30">Last 30 Days</button>
                    <button type="button" class="outstanding-filter-chip" data-range="180">Last 6 Months</button>
                </div>
            </div>

            <div class="outstanding-filter-section">
                <div class="outstanding-filter-label">Custom Date Range</div>
                <div class="outstanding-filter-field-row">
                    <input type="date" name="tglawal" id="tglawal" class="outstanding-filter-date" aria-label="Start date">
                    <input type="date" name="tglakhir" id="tglakhir" class="outstanding-filter-date" aria-label="End date">
                </div>
            </div>

            <div class="outstanding-filter-section">
                <div class="outstanding-filter-label">Filter Data</div>
                <div class="outstanding-filter-field-row">
                    <select id="filter_kekurangan" class="outstanding-filter-select" aria-label="Filter data">
                        <option value="1">Belum Selesai</option>
                        <option value="0">Sudah Selesai</option>
                    </select>
                    <select name="pelanggan" id="pelanggan" class="outstanding-filter-select" aria-label="Pelanggan">
                        <option value="">Semua Pelanggan</option>
                        <?php foreach ($pelanggans as $pelanggan) : ?>
                            <option value="<?= $pelanggan['pelid'] ?>"><?= $pelanggan['pelnama'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="outstanding-filter-section">
                <div class="outstanding-filter-label">Show Data</div>
                <div class="outstanding-filter-field-row">
                    <select id="outstandingPageLength" class="outstanding-filter-select" aria-label="Show data">
                        <option value="10" selected>10 entries</option>
                        <option value="25">25 entries</option>
                        <option value="50">50 entries</option>
                        <option value="100">100 entries</option>
                    </select>
                </div>
            </div>

            <div class="outstanding-filter-section">
                <div class="outstanding-filter-action-row">
                    <button type="button" class="outstanding-filter-apply" id="outstandingFilterApply">Tampilkan</button>
                    <button type="button" class="outstanding-filter-reset" id="outstandingFilterReset">Reset</button>
                </div>
            </div>
        </section>
    </div>
</div>

<div class="modal fade" id="modalCetakOutstanding" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cetak Data Outstanding</h5>

                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="form-group">
                    <label for="pelangganCetak">Pelanggan</label>

                    <select id="pelangganCetak" class="form-control">
                        <option value="">Semua Pelanggan</option>
                        <?php foreach ($pelanggans as $pelanggan) : ?>
                            <option value="<?= $pelanggan['pelid'] ?>"><?= $pelanggan['pelnama'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    Batal
                </button>

                <button type="button" class="btn btn-primary" onclick="cetakOutstanding()">
                    <i class="fas fa-print"></i> Cetak
                </button>
            </div>
        </div>
    </div>
</div>

<table id="dataoutstanding" class="table table-bordered table-striped dataTable dtr-inline collapsed">
    <thead>
        <tr>
            <th class="text-center" style="width: 5;" data-orderable="false">No</th>
            <th class="text-center">No. PO</th>
            <th class="text-center">Tgl. PO</th>
            <th class="text-center">Kode Produk</th>
            <th class="text-center">QTY</th>
            <th class="text-center">Terkirim</th>
            <!-- <th class="text-center">Tgl. Dikirim</th> -->
            <th class="text-center" style="width: 160px; min-width: 160px;">Belum Terkirim</th>
            <?php if (\App\Libraries\AccessControl::can('order.outstanding.view_price')) :  ?>
                <th class="text-center" style="width: 160px; min-width: 160px;">Nilai Tagihan Terkirim</th>
                <th class="text-center" style="width: 160px; min-width: 160px;">Sisa Nilai Tagihan</th>
            <?php endif ?>
        </tr>
    </thead>
    <tbody>

    </tbody>
</table>
<script>
    // var pusher = new Pusher('8f027ac11961f0fa1906', {
    //     cluster: 'ap1'
    // });

    // var channel = pusher.subscribe('my-channel');
    // channel.bind('my-event', function(data) {
    //     table.ajax.reload(null, false);
    // });
</script>
<script>
    let csrfToken = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';
    var table;

    $(document).ready(function() {
        table = $('#dataoutstanding').DataTable({
            lengthChange: true,
            autoWidth: false,
            responsive: true,
            searching: true, // Aktifkan opsi pencarian
            searchDelay: 500, // Tunda pencarian selama 500 milidetik
            stateSave: true,
            stateDuration: -1,
            processing: true,
            serverSide: true,
            pageLength: 10,
            dom: "<'row'<'col-sm-12'tr>><'row align-items-center mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            ajax: {
                url: '<?= site_url('outstand/listData') ?>',
                type: 'POST',
                data: function(d) {
                    d.tglawal = $('#tglawal').val();
                    d.tglakhir = $('#tglakhir').val();
                    d.filter_kekurangan = $('#filter_kekurangan').val();
                    d.pelanggan = $('#pelanggan').val();
                    d[csrfToken] = csrfHash;
                }
            },
            order: [
                [2, 'desc']
            ],
            columns: [{
                    data: 'nomor',
                    orderable: false,
                    className: 'text-center'
                },
                {
                    data: 'nopo',
                    className: 'text-left'
                },
                {
                    data: 'tgl',
                    className: 'text-center'
                },
                {
                    data: 'kodebrg',
                    className: 'text-center'
                },
                {
                    data: 'qty',
                    className: 'text-center'
                },
                {
                    data: 'terkirim',
                    orderable: false,
                    className: 'text-center'
                },
                // {
                //     data: 'tglkirim',
                //     orderable: false,
                //     className: 'text-right'
                // },
                {
                    data: 'kekurangan',
                    orderable: false,
                    className: 'text-center',
                    width: '160px'
                },
                <?php if (\App\Libraries\AccessControl::can('order.outstanding.view_price')) :  ?> {
                        data: 'outkirim',
                        orderable: false,
                        className: 'text-right',
                        width: '160px'
                    },
                    {
                        data: 'outharga',
                        orderable: false,
                        className: 'text-right',
                        width: '160px'
                    },
                <?php endif ?>
            ],
            // drawCallback: function(settings) {
            //     let api = this.api();
            //     let rows = api.rows({
            //         search: 'applied'
            //     }).nodes();

            //     $(rows).removeClass('hide-row');

            //     // Sembunyikan baris dengan kekurangan 0 jika opsi dipilih
            //     let filterValue = $('#filter_kekurangan').val();
            //     if (filterValue === '0') {
            //         $(rows).each(function() {
            //             let kekurangan = api.row(this).data().kekurangan;
            //             if (kekurangan === 0) {
            //                 $(this).addClass('hide-row');
            //             }
            //         });
            //     }
            //     // Tampilkan hanya baris dengan kekurangan bukan 0 jika opsi dipilih
            //     else if (filterValue === '1') {
            //         $(rows).each(function() {
            //             let kekurangan = api.row(this).data().kekurangan;
            //             if (kekurangan === 0) {
            //                 $(this).addClass('hide-row');
            //             }
            //         });
            //     }
            // }
        });
        $('#outstandingSearchInput').val(table.search());

        const filterPanel = $('#outstandingFilterPanel');
        const filterToggle = $('#outstandingFilterToggle');
        const filterClose = $('#outstandingFilterClose');
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

        $('#outstandingSearchInput').on('input', function() {
            const keyword = this.value;
            window.clearTimeout(searchTimer);
            searchTimer = window.setTimeout(function() {
                table.search(keyword).draw();
            }, 350);
        });

        $('.outstanding-filter-chip').on('click', function() {
            const days = Number($(this).data('range')) || 30;
            const endDate = new Date();
            const startDate = new Date();
            startDate.setDate(endDate.getDate() - days);
            $('.outstanding-filter-chip').removeClass('is-active');
            $(this).addClass('is-active');
            $('#tglawal').val(formatDateInput(startDate));
            $('#tglakhir').val(formatDateInput(endDate));
        });

        $('#outstandingPageLength').on('change', function() {
            table.page.len(Number(this.value)).draw();
        });

        $('#outstandingFilterApply').on('click', function() {
            table.ajax.reload();
            toggleFilterPanel(false);
        });

        $('#outstandingFilterReset').on('click', function() {
            $('#tglawal').val('');
            $('#tglakhir').val('');
            $('#filter_kekurangan').val('1');
            $('#pelanggan').val('');
            $('#outstandingSearchInput').val('');
            $('#outstandingPageLength').val('10');
            $('.outstanding-filter-chip').removeClass('is-active');
            table.search('').page.len(10).draw();
            table.ajax.reload();
        });

        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                toggleFilterPanel(false);
            }
        });

    });

    function cetakOutstanding() {
        const params = new URLSearchParams();
        params.set('filter_kekurangan', $('#filter_kekurangan').val() || '1');
        if ($('#pelangganCetak').val()) {
            params.set('pelanggan', $('#pelangganCetak').val());
        }
        window.open('<?= site_url('outstand/cetak') ?>?' + params.toString(), '_blank');
        $('#modalCetakOutstanding').modal('hide');
    }
</script>

<?= $this->endSection('isi') ?>
