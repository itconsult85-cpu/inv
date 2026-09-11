<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Antar Gudang
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>

<div class="d-flex justify-content-between align-items-center">
    <button type="button"
        class="btn btn-primary"
        onclick="location.href='/permintaanBarang/input'">
        <i class="fa fa-plus-circle"></i>
        Input Transfer
    </button>

    <button type="button"
        class="btn btn-info"
        data-toggle="modal"
        data-target="#modalCetakPeriode">
        <i class="fa fa-print"></i>
        Print
    </button>
</div>

<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>

<style>
    .card-header > .card-title {
        float: none !important;
        display: block !important;
        width: 100% !important;
        margin: 0 !important;
    }

    .card-header > .card-title > .d-flex {
        display: flex !important;
        justify-content: space-between !important;
        width: 100% !important;
    }

    .badge {
        font-size: 0.85rem;
    }

    .ag-toolbar {
        align-items: flex-start;
        display: flex;
        justify-content: flex-end;
        margin-bottom: 1rem;
        position: relative;
        z-index: 30;
    }

    .ag-filter-anchor {
        align-items: flex-end;
        display: flex;
        flex-direction: column;
        position: relative;
        width: min(100%, 32rem);
    }

    .ag-search-shell {
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

    .ag-search-shell > i {
        color: #6b7280;
        font-size: 1.1rem;
    }

    .ag-search-input {
        background: transparent;
        border: 0;
        box-shadow: none;
        color: #4b5563;
        flex: 1 1 auto;
        font-size: .95rem;
        min-width: 0;
        outline: 0;
    }

    .ag-search-input:focus {
        box-shadow: none;
        outline: 0;
    }

    .ag-filter-toggle {
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

    .ag-filter-toggle:hover,
    .ag-filter-toggle:focus {
        background: #126f7f;
        color: #fff;
        outline: 0;
    }

    .ag-filter-panel {
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

    .ag-filter-panel.is-visible {
        display: block;
    }

    .ag-filter-panel.is-open {
        opacity: 1;
        pointer-events: auto;
        transform: translateY(0) scale(1);
    }

    .ag-filter-header {
        align-items: center;
        display: flex;
        justify-content: space-between;
        padding: 1.2rem 1.35rem .9rem;
    }

    .ag-filter-title {
        color: #111827;
        font-size: 1.2rem;
        font-weight: 800;
        margin: 0;
    }

    .ag-filter-close {
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

    .ag-filter-section {
        border-top: 1px solid #edf1f5;
        padding: 1.05rem 1.35rem;
    }

    .ag-filter-label {
        color: #718096;
        font-size: .9rem;
        font-weight: 800;
        margin-bottom: .75rem;
    }

    .ag-filter-field-row,
    .ag-filter-action-row {
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
    }

    .ag-filter-select {
        background: #fff;
        border: 1px solid #e5eaf1;
        border-radius: 999px;
        color: #111827;
        min-height: 2.55rem;
        min-width: 11rem;
        padding: .45rem .85rem;
    }

    .ag-filter-reset {
        background: #eef2f7;
        border: 0;
        border-radius: 999px;
        color: #4b5563;
        font-weight: 800;
        min-height: 2.55rem;
        padding: .45rem 1.2rem;
    }

    @media (max-width: 768px) {
        .ag-toolbar {
            justify-content: stretch;
        }

        .ag-filter-anchor,
        .ag-search-shell,
        .ag-filter-panel {
            max-width: none;
            width: 100%;
        }

        .ag-filter-panel {
            left: 0;
            right: auto;
        }

        .ag-filter-select,
        .ag-filter-reset {
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

<div class="ag-toolbar">
    <div class="ag-filter-anchor">
        <div class="ag-search-shell">
            <i class="fas fa-search"></i>
            <input type="search" id="kirimSearchInput" class="ag-search-input" placeholder="Search anything..." aria-label="Search anything">
            <button type="button" class="ag-filter-toggle" id="kirimFilterToggle" title="Buka filter" aria-controls="kirimFilterPanel" aria-expanded="false">
                <i class="fas fa-sliders-h"></i>
            </button>
        </div>

        <section class="ag-filter-panel" id="kirimFilterPanel" aria-hidden="true">
            <div class="ag-filter-header">
                <h3 class="ag-filter-title">Filter</h3>
                <button type="button" class="ag-filter-close" id="kirimFilterClose" title="Tutup filter">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="ag-filter-section">
                <div class="ag-filter-label">Show Data</div>
                <div class="ag-filter-field-row">
                    <select id="kirimPageLength" class="ag-filter-select" aria-label="Show data">
                        <option value="10">10 entries</option>
                        <option value="25">25 entries</option>
                        <option value="50" selected>50 entries</option>
                        <option value="100">100 entries</option>
                    </select>
                </div>
            </div>

            <div class="ag-filter-section">
                <div class="ag-filter-action-row">
                    <button type="button" class="ag-filter-reset" id="kirimFilterReset">Reset</button>
                </div>
            </div>
        </section>
    </div>
</div>
<table id="datareqkirim" class="table table-bordered table-striped table-hover dataTable dtr-inline collapsed" style="width: 100%;">
    <thead>
        <tr>
            <th style="width: 5%;">No</th>
            <th>No Surat Jalan</th>
            <th>Tanggal</th>
            <th>User</th>
            <th>Total Produk (Pcs)</th>
            <th>Jenis Pengiriman</th>
            <th>PIC Pengirim</th>
            <th>Nominal</th>
            <th>Gudang Keluar</th>
            <th style="width: 15%;">Aksi</th>
        </tr>
    </thead>
    <tbody>

    </tbody>
</table>

<div class="modal fade" id="modalCetakPeriode" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Print Permintaan Transfer</h5>

                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="form-group">
                    <label for="tanggalPrintAwal">Tanggal Awal</label>
                    <input type="date"
                        id="tanggalPrintAwal"
                        class="form-control">
                </div>

                <div class="form-group">
                    <label for="tanggalPrintAkhir">Tanggal Akhir</label>
                    <input type="date"
                        id="tanggalPrintAkhir"
                        class="form-control">
                </div>

                <div class="form-group">
                    <label for="gudangPrint">Asal Gudang</label>

                    <select id="gudangPrint" class="form-control">
                        <option value="">-- Pilih Asal Gudang --</option>

                        <?php foreach ($dataGudang as $gudang) : ?>
                            <option value="<?= esc($gudang['gdgid']) ?>">
                                <?= esc($gudang['gdgnama']) ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                </div>

                <small class="text-muted">
                    Hanya permintaan yang seluruh barangnya sudah terkirim
                    yang akan dicetak.
                </small>
            </div>

            <div class="modal-footer">
                <button type="button"
                    class="btn btn-secondary"
                    data-dismiss="modal">
                    Batal
                </button>

                <button type="button"
                    class="btn btn-info"
                    onclick="cetakPeriode()">
                    <i class="fa fa-print"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let csrfToken = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';
    var table;

    $(document).ready(function() {
        table = $('#datareqkirim').DataTable({
            searching: true,
            searchDelay: 500,
            stateSave: true,
            stateDuration: -1,
            responsive: true,
            processing: true,
            serverSide: true,
            dom: "<'row'<'col-sm-12'tr>><'row align-items-center mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            ajax: {
                url: '<?= site_url('permintaanBarangKirim/listDataKirim') ?>',
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
                    data: 'detpermintaan'
                },
                {
                    data: 'tglfaktur',
                    className: 'text-center'
                },
                {
                    data: 'usernama',
                    className: 'text-center'
                },
                {
                    data: 'qtykirim',
                    className: 'text-right'
                },
                {
                    data: 'jenispengiriman',
                    className: 'text-center',
                    defaultContent: '-'
                },
                {
                    data: 'picpengirim',
                    className: 'text-center',
                    defaultContent: '-'
                },
                {
                    data: 'nominal',
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
                },
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

            return { toggleFilterPanel };
        }

        setupFilterPanel({
            panelId: 'kirimFilterPanel',
            toggleId: 'kirimFilterToggle',
            closeId: 'kirimFilterClose',
            searchInputId: 'kirimSearchInput',
            pageLengthId: 'kirimPageLength',
            table: table
        });

        $('#kirimFilterReset').on('click', function() {
            $('#kirimPageLength').val('50');
            $('#kirimSearchInput').val('');
            table.search('').page.len(50);
            table.ajax.reload();
        });
    });

    function cetakPeriode() {
        const tanggalAwal = $('#tanggalPrintAwal').val();
        const tanggalAkhir = $('#tanggalPrintAkhir').val();
        const gudang = $('#gudangPrint').val();

        if (!tanggalAwal || !tanggalAkhir || !gudang) {
            Swal.fire(
                'Data belum lengkap',
                'Tanggal awal, tanggal akhir, dan asal gudang wajib dipilih.',
                'warning'
            );

            return;
        }

        if (tanggalAwal > tanggalAkhir) {
            Swal.fire(
                'Tanggal tidak valid',
                'Tanggal awal tidak boleh melebihi tanggal akhir.',
                'warning'
            );

            return;
        }

        const parameter = new URLSearchParams({
            tglawal: tanggalAwal,
            tglakhir: tanggalAkhir,
            gudang: gudang
        });

        window.open(
            '/permintaanBarang/cetakPeriode?' + parameter.toString(),
            '_blank'
        );
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
                    url: '<?= site_url('permintaanBarang/hapusTransaksiProses') ?>',
                    data: {
                        [csrfToken]: csrfHash,
                        faktur: faktur,
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
                        alert(xhr.status + '\n' + thrownError);
                    }
                });
            }
        });
    }

    function edit(id) {
        window.location.href = ('/permintaanBarangKirim/editproses/') + id;
    }
</script>
<?= $this->endSection('isi') ?>
