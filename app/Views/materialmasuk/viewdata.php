<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Data Material Masuk
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>

<button type="button" class="btn btn-primary" onclick="location.href=('/materialmasuk/input')">
    <i class="fa fa-plus-circle"></i> Input Transaksi Material Masuk
</button>
<button type="button" class="btn btn-danger ml-2" onclick="location.href=('/materialmasuk/input?penerimaan_ng=1')">
    <i class="fa fa-exchange-alt"></i> Penerimaan dari NG
</button>

<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<?php if ($message = session()->getFlashdata('success')) : ?>
    <div class="alert alert-success"><?= esc($message) ?></div>
<?php endif ?>
<?php if ($message = session()->getFlashdata('error')) : ?>
    <div class="alert alert-danger"><?= esc($message) ?></div>
<?php endif ?>
<style>
    .mm-toolbar {
        align-items: flex-start;
        display: flex;
        justify-content: flex-end;
        margin-bottom: 1rem;
        position: relative;
        z-index: 30;
    }

    .mm-filter-anchor {
        align-items: flex-end;
        display: flex;
        flex-direction: column;
        position: relative;
        width: min(100%, 32rem);
    }

    .mm-search-shell {
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

    .mm-search-shell > i {
        color: #6b7280;
        font-size: 1.1rem;
    }

    .mm-search-input {
        background: transparent;
        border: 0;
        box-shadow: none;
        color: #4b5563;
        flex: 1 1 auto;
        font-size: .95rem;
        min-width: 0;
        outline: 0;
    }

    .mm-search-input:focus {
        box-shadow: none;
        outline: 0;
    }

    .mm-filter-toggle {
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

    .mm-filter-toggle:hover,
    .mm-filter-toggle:focus {
        background: #126f7f;
        color: #fff;
        outline: 0;
    }

    .mm-filter-panel {
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

    .mm-filter-panel.is-visible {
        display: block;
    }

    .mm-filter-panel.is-open {
        opacity: 1;
        pointer-events: auto;
        transform: translateY(0) scale(1);
    }

    .mm-filter-header {
        align-items: center;
        display: flex;
        justify-content: space-between;
        padding: 1.2rem 1.35rem .9rem;
    }

    .mm-filter-title {
        color: #111827;
        font-size: 1.2rem;
        font-weight: 800;
        margin: 0;
    }

    .mm-filter-close {
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

    .mm-filter-section {
        border-top: 1px solid #edf1f5;
        padding: 1.05rem 1.35rem;
    }

    .mm-filter-label {
        color: #718096;
        font-size: .9rem;
        font-weight: 800;
        margin-bottom: .75rem;
    }

    .mm-filter-chip-row,
    .mm-filter-field-row,
    .mm-filter-action-row {
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
    }

    .mm-filter-chip {
        background: #fff;
        border: 1px solid #e8edf4;
        border-radius: 999px;
        color: #111827;
        font-weight: 600;
        min-height: 2.45rem;
        padding: .45rem .9rem;
    }

    .mm-filter-chip.is-active,
    .mm-filter-chip:hover {
        background: #eef6e8;
        border-color: #d9e9cf;
    }

    .mm-filter-date,
    .mm-filter-select {
        background: #fff;
        border: 1px solid #e5eaf1;
        border-radius: 999px;
        color: #111827;
        min-height: 2.55rem;
        padding: .45rem .85rem;
    }

    .mm-filter-date {
        min-width: 11rem;
    }

    .mm-filter-select {
        min-width: 11rem;
    }

    .mm-filter-apply,
    .mm-filter-reset {
        border: 0;
        border-radius: 999px;
        font-weight: 800;
        min-height: 2.55rem;
        padding: .45rem 1.2rem;
    }

    .mm-filter-apply {
        background: #16869a;
        color: #fff;
    }

    .mm-filter-reset {
        background: #eef2f7;
        color: #4b5563;
    }

    @media (max-width: 768px) {
        .mm-toolbar {
            justify-content: stretch;
        }

        .mm-filter-anchor,
        .mm-search-shell,
        .mm-filter-panel {
            max-width: none;
            width: 100%;
        }

        .mm-filter-panel {
            left: 0;
            right: auto;
        }

        .mm-filter-date,
        .mm-filter-select,
        .mm-filter-apply,
        .mm-filter-reset {
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

<div class="mm-toolbar">
    <div class="mm-filter-anchor">
        <div class="mm-search-shell">
            <i class="fas fa-search"></i>
            <input type="search" id="mmSearchInput" class="mm-search-input" placeholder="Search anything..." aria-label="Search anything">
            <button type="button" class="mm-filter-toggle" id="mmFilterToggle" title="Buka filter" aria-controls="mmFilterPanel" aria-expanded="false">
                <i class="fas fa-sliders-h"></i>
            </button>
        </div>

        <section class="mm-filter-panel" id="mmFilterPanel" aria-hidden="true">
            <div class="mm-filter-header">
                <h3 class="mm-filter-title">Filter</h3>
                <button type="button" class="mm-filter-close" id="mmFilterClose" title="Tutup filter">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="mm-filter-section">
                <div class="mm-filter-label">Filter by Date Range</div>
                <div class="mm-filter-chip-row">
                    <button type="button" class="mm-filter-chip" data-range="30">Last 30 Days</button>
                    <button type="button" class="mm-filter-chip" data-range="180">Last 6 Months</button>
                </div>
            </div>

            <div class="mm-filter-section">
                <div class="mm-filter-label">Custom Date Range</div>
                <div class="mm-filter-field-row">
                    <input type="date" name="tglawal" id="tglawal" class="mm-filter-date" aria-label="Start date">
                    <input type="date" name="tglakhir" id="tglakhir" class="mm-filter-date" aria-label="End date">
                </div>
            </div>

            <div class="mm-filter-section">
                <div class="mm-filter-label">Show Data</div>
                <div class="mm-filter-field-row">
                    <select id="mmPageLength" class="mm-filter-select" aria-label="Show data">
                        <option value="10">10 entries</option>
                        <option value="25">25 entries</option>
                        <option value="50" selected>50 entries</option>
                        <option value="100">100 entries</option>
                    </select>
                </div>
            </div>

            <div class="mm-filter-section">
                <div class="mm-filter-action-row">
                    <button type="button" class="mm-filter-apply" id="tombolTampil">Tampilkan</button>
                    <button type="button" class="mm-filter-reset" id="mmFilterReset">Reset</button>
                </div>
            </div>
        </section>
    </div>
</div>
<table id="datamaterialmasuk" class="table table-bordered table-striped table-hover dataTable dtr-inline collapsed" style="width: 100%;">
    <thead>
        <tr>
            <th style="width: 5%;">No</th>
            <th>No. Invoice</th>
            <th>No Surat Jalan</th>
            <th>Tanggal</th>
            <th>Supplier/Sumber</th>
            <th>Total Berat/Ukuran</th>
            <th>Gudang</th>
            <th style="width: 10%;">#</th>
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
        table = $('#datamaterialmasuk').DataTable({
            searching: true, // Aktifkan opsi pencarian
            searchDelay: 500,
            stateSave: true,
            stateDuration: -1,
            responsive: true,
            processing: true,
            serverSide: true,
            dom: "<'row'<'col-sm-12'tr>><'row align-items-center mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            ajax: {
                url: '<?= site_url('materialmasuk/listData') ?>',
                type: 'POST',
                data: function(d) {
                    d.tglawal = $('#tglawal').val();
                    d.tglakhir = $('#tglakhir').val();
                    d[csrfToken] = csrfHash;
                }
            },
            pageLength: 10,
            order: [
                [3, 'asc']
            ],
            columns: [{
                    data: 'nomor',
                    orderable: false,
                    className: 'text-center'
                },
                {
                    data: 'no_invoice'
                },
                {
                    data: 'no_do',
                    defaultContent: '-',
                    className: 'text-center'
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
                    data: 'totalberatmaterial',
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
        $('#mmSearchInput').val(table.search());

        const filterPanel = $('#mmFilterPanel');
        const filterToggle = $('#mmFilterToggle');
        const filterClose = $('#mmFilterClose');
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

        $('#mmSearchInput').on('input', function() {
            const keyword = this.value;
            window.clearTimeout(searchTimer);
            searchTimer = window.setTimeout(function() {
                table.search(keyword).draw();
            }, 350);
        });

        $('.mm-filter-chip').on('click', function() {
            const days = Number($(this).data('range')) || 30;
            const endDate = new Date();
            const startDate = new Date();
            startDate.setDate(endDate.getDate() - days);
            $('.mm-filter-chip').removeClass('is-active');
            $(this).addClass('is-active');
            $('#tglawal').val(formatDateInput(startDate));
            $('#tglakhir').val(formatDateInput(endDate));
        });

        $('#mmPageLength').on('change', function() {
            table.page.len(Number(this.value)).draw();
        });

        $('#tombolTampil').on('click', function() {
            table.ajax.reload();
            toggleFilterPanel(false);
        });

        $('#mmFilterReset').on('click', function() {
            $('#tglawal').val('');
            $('#tglakhir').val('');
            $('.mm-filter-chip').removeClass('is-active');
            $('#mmPageLength').val('50');
            $('#mmSearchInput').val('');
            table.search('').page.len(50);
            table.ajax.reload();
        });
    });

    function cetak(faktur) {
        let windowCetak = window.open('/materialmasuk/cetakfaktur/' + faktur,
            "Cetak Invoice Material Masuk",
            "width=200,height=400");
        windowCetak.focus();
    }

    function hapus(faktur) {
        showBootstrapModal({
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
                    url: '<?= site_url('materialmasuk/hapusTransaksi') ?>',
                    data: {
                        [csrfToken]: csrfHash,
                        faktur: faktur
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.sukses) {
                            showBootstrapModal('Berhasil', response.sukses, 'success');
                            table.ajax.reload();
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        showBootstrapModal('Error', xhr.status + '\n' + thrownError, 'error')
                    }
                });
            }
        })
    }

    function edit(faktur) {
        window.location.href = ('/materialmasuk/edit/') + faktur;
    }

    function returMaterial(faktur) {
        window.location.href = ('/materialmasuk/retur/') + faktur;
    }
</script>

<?= $this->endSection('isi') ?>
