<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Data PO Masuk
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>

<?php if (\App\Libraries\AccessControl::can('order.po_masuk.input_manual')) : ?>
    <button type="button" class="btn btn-primary" onclick="location.href=('/po/input')">
        <i class="fa fa-plus-circle"></i> Input PO
    </button>
<?php endif ?>
<?php if (\App\Libraries\AccessControl::can('order.po_masuk.import')) : ?>
    <button type="button" class="btn btn-info ml-2" onclick="location.href=('/po/import')">
        <i class="fa fa-file-upload"></i> Import PO PDF
    </button>
<?php endif ?>

<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<style>
    .po-action-buttons {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-wrap: nowrap;
        gap: .35rem;
        white-space: nowrap;
    }

    .po-action-buttons .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        margin: 0;
    }

    .po-toolbar {
        align-items: flex-start;
        display: flex;
        justify-content: flex-end;
        margin-bottom: 1rem;
        position: relative;
        z-index: 30;
    }

    .po-filter-anchor {
        align-items: flex-end;
        display: flex;
        flex-direction: column;
        position: relative;
        width: min(100%, 32rem);
    }

    .po-search-shell {
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

    .po-search-shell > i {
        color: #6b7280;
        font-size: 1.1rem;
    }

    .po-search-input {
        background: transparent;
        border: 0;
        box-shadow: none;
        color: #4b5563;
        flex: 1 1 auto;
        font-size: .95rem;
        min-width: 0;
        outline: 0;
    }

    .po-search-input:focus {
        box-shadow: none;
        outline: 0;
    }

    .po-filter-toggle {
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

    .po-filter-toggle:hover,
    .po-filter-toggle:focus {
        background: #126f7f;
        color: #fff;
        outline: 0;
    }

    .po-filter-panel {
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

    .po-filter-panel.is-visible {
        display: block;
    }

    .po-filter-panel.is-open {
        opacity: 1;
        pointer-events: auto;
        transform: translateY(0) scale(1);
    }

    .po-filter-header {
        align-items: center;
        display: flex;
        justify-content: space-between;
        padding: 1.2rem 1.35rem .9rem;
    }

    .po-filter-title {
        color: #111827;
        font-size: 1.2rem;
        font-weight: 800;
        margin: 0;
    }

    .po-filter-close {
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

    .po-filter-section {
        border-top: 1px solid #edf1f5;
        padding: 1.05rem 1.35rem;
    }

    .po-filter-label {
        color: #718096;
        font-size: .9rem;
        font-weight: 800;
        margin-bottom: .75rem;
    }

    .po-filter-chip-row,
    .po-filter-field-row,
    .po-filter-action-row {
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
    }

    .po-filter-chip {
        background: #fff;
        border: 1px solid #e8edf4;
        border-radius: 999px;
        color: #111827;
        font-weight: 600;
        min-height: 2.45rem;
        padding: .45rem .9rem;
    }

    .po-filter-chip.is-active,
    .po-filter-chip:hover {
        background: #eef6e8;
        border-color: #d9e9cf;
    }

    .po-filter-date,
    .po-filter-select {
        background: #fff;
        border: 1px solid #e5eaf1;
        border-radius: 999px;
        color: #111827;
        min-height: 2.55rem;
        padding: .45rem .85rem;
    }

    .po-filter-date {
        min-width: 11rem;
    }

    .po-filter-select {
        min-width: 11rem;
    }

    /* Tabel PO memiliki banyak kolom. Jangan dipaksa mengecil menjadi
       satu huruf per baris pada layar sempit; gunakan scroll horizontal. */
    #datapo {
        width: 100% !important;
        min-width: 980px !important;
        max-width: none !important;
        table-layout: fixed;
    }

    #datapo th,
    #datapo td {
        vertical-align: middle;
        white-space: nowrap;
    }

    #datapo thead th {
        text-align: center !important;
        vertical-align: middle;
        white-space: nowrap;
    }

    #datapo th:nth-child(1),
    #datapo td:nth-child(1) { width: 55px; }
    #datapo th:nth-child(2),
    #datapo td:nth-child(2) { width: 125px; }
    #datapo th:nth-child(3),
    #datapo td:nth-child(3) { width: 115px; }
    #datapo th:nth-child(4),
    #datapo td:nth-child(4) { width: 235px; white-space: normal; overflow-wrap: anywhere; }
    #datapo th:nth-child(5),
    #datapo td:nth-child(5) { width: 145px; }
    #datapo th:nth-child(6),
    #datapo td:nth-child(6) { width: 145px; }
    #datapo th:nth-child(7),
    #datapo td:nth-child(7) { width: 175px; white-space: normal; }
    #datapo th:nth-child(8),
    #datapo td:nth-child(8) { width: 145px; }

    #datapo tbody td:nth-child(4) {
        text-align: left;
    }

    #datapo tbody td:nth-child(5) {
        text-align: center;
    }

    .po-filter-apply,
    .po-filter-reset {
        border: 0;
        border-radius: 999px;
        font-weight: 800;
        min-height: 2.55rem;
        padding: .45rem 1.2rem;
    }

    .po-filter-apply {
        background: #16869a;
        color: #fff;
    }

    .po-filter-reset {
        background: #eef2f7;
        color: #4b5563;
    }

    @media (max-width: 768px) {
        .po-toolbar {
            justify-content: stretch;
        }

        .po-filter-anchor,
        .po-search-shell,
        .po-filter-panel {
            max-width: none;
            width: 100%;
        }

        .po-filter-panel {
            left: 0;
            right: auto;
        }

        .po-filter-date,
        .po-filter-select,
        .po-filter-apply,
        .po-filter-reset {
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

<div class="po-toolbar">
    <div class="po-filter-anchor">
        <div class="po-search-shell">
            <i class="fas fa-search"></i>
            <input type="search" id="poSearchInput" class="po-search-input" placeholder="Search anything..." aria-label="Search anything">
            <button type="button" class="po-filter-toggle" id="poFilterToggle" title="Buka filter" aria-controls="poFilterPanel" aria-expanded="false">
                <i class="fas fa-sliders-h"></i>
            </button>
        </div>

        <section class="po-filter-panel" id="poFilterPanel" aria-hidden="true">
            <div class="po-filter-header">
                <h3 class="po-filter-title">Filter</h3>
                <button type="button" class="po-filter-close" id="poFilterClose" title="Tutup filter">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="po-filter-section">
                <div class="po-filter-label">Filter by Date Range</div>
                <div class="po-filter-chip-row">
                    <button type="button" class="po-filter-chip" data-range="30">Last 30 Days</button>
                    <button type="button" class="po-filter-chip" data-range="180">Last 6 Months</button>
                </div>
            </div>

            <div class="po-filter-section">
                <div class="po-filter-label">Custom Date Range</div>
                <div class="po-filter-field-row">
                    <input type="date" name="tglawal" id="tglawal" class="po-filter-date" aria-label="Start date">
                    <input type="date" name="tglakhir" id="tglakhir" class="po-filter-date" aria-label="End date">
                </div>
            </div>

            <div class="po-filter-section">
                <div class="po-filter-label">Show Data</div>
                <div class="po-filter-field-row">
                    <select id="poPageLength" class="po-filter-select" aria-label="Show data">
                        <option value="10">10 entries</option>
                        <option value="25">25 entries</option>
                        <option value="50" selected>50 entries</option>
                        <option value="100">100 entries</option>
                    </select>
                    <select name="pelanggan" id="pelanggan" class="po-filter-select" aria-label="Pelanggan">
                        <option value="">Semua Pelanggan</option>
                        <?php foreach ($pelanggans as $pelanggan) : ?>
                            <option value="<?= $pelanggan['pelid'] ?>"><?= $pelanggan['pelnama'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="progress" id="progressFilter" class="po-filter-select" aria-label="Progress">
                        <option value="">Semua Progress</option>
                        <option value="material_belum">Dipesan, Belum Datang</option>
                        <option value="material_sebagian">Diterima Sebagian</option>
                        <option value="belum_kirim">Belum Dikirim</option>
                        <option value="kirim_sebagian">Dikirim Sebagian</option>
                        <option value="belum_tagih">Belum Ditagih</option>
                        <option value="belum_lunas">Belum Lunas</option>
                        <option value="lunas">Lunas</option>
                    </select>
                </div>
            </div>

            <div class="po-filter-section">
                <div class="po-filter-action-row">
                    <button type="button" class="po-filter-apply" id="tombolTampil">Tampilkan</button>
                    <button type="button" class="po-filter-reset" id="poFilterReset">Reset</button>
                </div>
            </div>
        </section>
    </div>
</div>
<table id="datapo" class="table table-bordered table-striped table-hover dataTable dtr-inline collapsed" style="width: 100%;">
    <thead>
        <tr>
            <th style="width: 5%;">No</th>
            <th>No PO</th>
            <th>Tanggal</th>
            <th>Pelanggan</th>
            <th style="width: 130px; min-width: 130px;">Total Barang (Pcs)</th>
            <?php if (\App\Libraries\AccessControl::can('order.po_masuk.view_price')) :  ?>
                <th style="width: 130px; min-width: 130px;">Harga</th>
            <?php endif ?>
            <th>Progress</th>
            <th style="width: 145px; min-width: 145px;">Aksi</th>
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
        table = $('#datapo').DataTable({
            searching: true, // Aktifkan opsi pencarian
            searchDelay: 500,
            stateSave: true,
            stateDuration: -1,
            autoWidth: false,
            responsive: false,
            scrollX: true,
            processing: true,
            serverSide: true,
            dom: "<'row'<'col-sm-12'tr>><'row align-items-center mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            ajax: {
                url: '<?= site_url('po/listData') ?>',
                type: 'POST',
                data: function(d) {
                    d.tglawal = $('#tglawal').val();
                    d.tglakhir = $('#tglakhir').val();
                    d.pelanggan = $('#pelanggan').val();
                    d.progress = $('#progressFilter').val();
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
                    data: 'nopo'
                },
                {
                    data: 'tglpo',
                    className: 'text-center'
                },
                {
                    data: 'pelnama',
                    className: 'text-left'
                },
                {
                    data: 'qty',
                    className: 'text-center',
                    width: '130px'
                },
                <?php if (\App\Libraries\AccessControl::can('order.po_masuk.view_price')) :  ?> {
                        data: 'hargapo',
                        className: 'text-right',
                        width: '130px'
                    },
                <?php endif ?> {
                    data: 'progress',
                    orderable: false,
                    className: 'text-center'
                },
                {
                    data: 'aksi',
                    orderable: false,
                    className: 'text-center',
                    width: '145px'
                },
            ]
        });
        $('#poSearchInput').val(table.search());

        const filterPanel = $('#poFilterPanel');
        const filterToggle = $('#poFilterToggle');
        const filterClose = $('#poFilterClose');
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

        $('#poSearchInput').on('input', function() {
            const keyword = this.value;
            window.clearTimeout(searchTimer);
            searchTimer = window.setTimeout(function() {
                table.search(keyword).draw();
            }, 350);
        });

        $('.po-filter-chip').on('click', function() {
            const days = Number($(this).data('range')) || 30;
            const endDate = new Date();
            const startDate = new Date();
            startDate.setDate(endDate.getDate() - days);
            $('.po-filter-chip').removeClass('is-active');
            $(this).addClass('is-active');
            $('#tglawal').val(formatDateInput(startDate));
            $('#tglakhir').val(formatDateInput(endDate));
        });

        $('#poPageLength').on('change', function() {
            table.page.len(Number(this.value)).draw();
        });

        $('#pelanggan, #progressFilter').on('change', function() {
            table.ajax.reload();
        });

        $('#tombolTampil').on('click', function() {
            table.ajax.reload();
            toggleFilterPanel(false);
        });

        $('#poFilterReset').on('click', function() {
            $('#tglawal').val('');
            $('#tglakhir').val('');
            $('.po-filter-chip').removeClass('is-active');
            $('#poPageLength').val('50');
            table.page.len(50);
            $('#pelanggan').val('');
            $('#progressFilter').val('');
            table.ajax.reload();
        });
    });

    function hapus(nopo) {
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
                    url: '<?= site_url('po/hapusTransaksi') ?>',
                    data: {
                        [csrfToken]: csrfHash,
                        nopo: nopo
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.sukses) {
                            Swal.fire('Berhasil', response.sukses, 'success');
                            table.ajax.reload();
                        } else if (response.error) {
                            Swal.fire('Gagal', response.error, 'error');
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        Swal.fire('Gagal', thrownError || 'Request gagal diproses.', 'error');
                    }
                });
            }
        });
    }

    function edit(nopo) {
        window.location.href = ('/po/edit/') + nopo;
    }
</script>

<?= $this->endSection('isi') ?>
