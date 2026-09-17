<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Managemen Data Produk
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>
<button type="button" class="btn btn-primary" onclick="location.href=('/barang/tambah')">
    <i class="fa fa-plus-circle"></i> Tambah Data Produk
</button>
<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<style>
    .brg-toolbar { align-items: flex-start; display: flex; justify-content: flex-end; margin-bottom: 1rem; position: relative; z-index: 30; }
    .brg-filter-anchor { align-items: flex-end; display: flex; flex-direction: column; position: relative; width: min(100%, 32rem); }
    .brg-search-shell { align-items: center; background: #fff; border: 1px solid #eef1f7; border-radius: 999px; box-shadow: 0 3px 10px rgba(15, 23, 42, .025); display: flex; gap: .65rem; min-height: 3.4rem; padding: .35rem .45rem .35rem 1.25rem; width: 100%; }
    .brg-search-shell > i { color: #6b7280; font-size: 1.1rem; }
    .brg-search-input { background: transparent; border: 0; box-shadow: none; color: #4b5563; flex: 1 1 auto; font-size: .95rem; min-width: 0; outline: 0; }
    .brg-search-input:focus { box-shadow: none; outline: 0; }
    .brg-filter-toggle { align-items: center; background: #16869a; border: 0; border-radius: 999px; color: #fff; display: inline-flex; flex: 0 0 2.65rem; height: 2.65rem; justify-content: center; width: 2.65rem; }
    .brg-filter-toggle:hover, .brg-filter-toggle:focus { background: #126f7f; color: #fff; outline: 0; }
    .brg-filter-panel { background: #fff; border: 1px solid #edf1f5; border-radius: 22px; box-shadow: 0 18px 42px rgba(15, 23, 42, .1); display: none; max-width: min(46rem, calc(100vw - 4rem)); opacity: 0; overflow: hidden; pointer-events: none; position: absolute; right: 0; top: calc(100% + .75rem); transform: translateY(-.45rem) scale(.985); transform-origin: top right; transition: opacity .18s ease, transform .18s ease; width: 34rem; }
    .brg-filter-panel.is-visible { display: block; }
    .brg-filter-panel.is-open { opacity: 1; pointer-events: auto; transform: translateY(0) scale(1); }
    .brg-filter-header { align-items: center; display: flex; justify-content: space-between; padding: 1.2rem 1.35rem .9rem; }
    .brg-filter-title { color: #111827; font-size: 1.2rem; font-weight: 800; margin: 0; }
    .brg-filter-close { align-items: center; background: #fff; border: 0; border-radius: 999px; box-shadow: 0 8px 22px rgba(15, 23, 42, .12); color: #111827; display: inline-flex; height: 2.4rem; justify-content: center; width: 2.4rem; }
    .brg-filter-section { border-top: 1px solid #edf1f5; padding: 1.05rem 1.35rem; }
    .brg-filter-label { color: #718096; font-size: .9rem; font-weight: 800; margin-bottom: .75rem; }
    .brg-filter-field-row, .brg-filter-action-row { display: flex; flex-wrap: wrap; gap: .75rem; }
    .brg-filter-select { background: #fff; border: 1px solid #e5eaf1; border-radius: 999px; color: #111827; min-height: 2.55rem; min-width: 11rem; padding: .45rem .85rem; }
    .brg-filter-reset { background: #eef2f7; border: 0; border-radius: 999px; color: #4b5563; font-weight: 800; min-height: 2.55rem; padding: .45rem 1.2rem; }
    @media (max-width: 768px) {
        .brg-toolbar { justify-content: stretch; }
        .brg-filter-anchor, .brg-search-shell, .brg-filter-panel { max-width: none; width: 100%; }
        .brg-filter-panel { left: 0; right: auto; }
        .brg-filter-select, .brg-filter-reset { width: 100%; }
    }
</style>
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<script src="<?= base_url() ?>/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

<div class="brg-toolbar">
    <div class="brg-filter-anchor">
        <div class="brg-search-shell">
            <i class="fas fa-search"></i>
            <input type="search" id="brgSearchInput" class="brg-search-input" placeholder="Search anything..." aria-label="Search anything">
            <button type="button" class="brg-filter-toggle" id="brgFilterToggle" title="Buka filter" aria-controls="brgFilterPanel" aria-expanded="false">
                <i class="fas fa-sliders-h"></i>
            </button>
        </div>

        <section class="brg-filter-panel" id="brgFilterPanel" aria-hidden="true">
            <div class="brg-filter-header">
                <h3 class="brg-filter-title">Filter</h3>
                <button type="button" class="brg-filter-close" id="brgFilterClose" title="Tutup filter">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="brg-filter-section">
                <div class="brg-filter-label">Filter Kategori &amp; Material</div>
                <div class="brg-filter-field-row">
                    <select id="filter_kategori" class="brg-filter-select" aria-label="Kategori">
                        <option value="">-- Semua Kategori --</option>
                        <?php foreach ($datakategori as $kat) : ?>
                            <option value="<?= $kat['katid'] ?>"><?= esc($kat['katnama']) ?></option>
                        <?php endforeach ?>
                    </select>
                    <select id="filter_material" class="brg-filter-select" aria-label="Material">
                        <option value="">-- Semua Material --</option>
                        <?php foreach ($datamaterial as $mat) : ?>
                            <option value="<?= $mat['matid'] ?>"><?= esc($mat['matnama']) ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
            </div>

            <div class="brg-filter-section">
                <div class="brg-filter-label">Show Data</div>
                <div class="brg-filter-field-row">
                    <select id="brgPageLength" class="brg-filter-select" aria-label="Show data">
                        <option value="10">10 entries</option>
                        <option value="25">25 entries</option>
                        <option value="50" selected>50 entries</option>
                        <option value="100">100 entries</option>
                    </select>
                </div>
            </div>

            <div class="brg-filter-section">
                <div class="brg-filter-action-row">
                    <button type="button" class="brg-filter-reset" id="brgFilterReset">Reset</button>
                </div>
            </div>
        </section>
    </div>
</div>
<table class="table table-bordered table-striped" id="databarang">
    <thead>
        <tr>
            <th style="width: 5;">No</th>
            <th>Kode Produk</th>
            <th>Nama Produk</th>
            <th>Material &amp; Detail Berat</th>
            <th>Kategori</th>
            <th>Satuan</th>
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
        <?php $flashSukses = session()->getFlashdata('message') ?: session()->getFlashdata('sukses'); ?>
        <?php if ($flashSukses) : ?>
            showBootstrapModal({
                title: 'Berhasil',
                html: <?= json_encode(is_array($flashSukses) ? implode('<br>', array_map('esc', $flashSukses)) : strip_tags((string) $flashSukses), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
                icon: 'success',
                timer: 1800,
                showConfirmButton: false
            });
        <?php endif ?>
        <?php $flashError = session()->getFlashdata('error'); ?>
        <?php if ($flashError) : ?>
            showBootstrapModal({
                title: 'Gagal',
                html: <?= json_encode(is_array($flashError) ? implode('<br>', array_map('esc', $flashError)) : strip_tags((string) $flashError), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
                icon: 'error'
            });
        <?php endif ?>

        table = $('#databarang').DataTable({
            lengthChange: true,
            autoWidth: false,
            responsive: true,
            processing: true,
            serverSide: true,
            searching: true,
            searchDelay: 500,
            stateSave: true,
            stateDuration: -1,
            dom: "<'row'<'col-sm-12'tr>><'row align-items-center mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            ajax: {
                url: '<?= site_url('barang/listData') ?>',
                type: 'POST',
                data: function(d) {
                    d.filter_kategori = $('#filter_kategori').val();
                    d.filter_material = $('#filter_material').val();
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
                    data: 'brgkode',
                    className: 'text-center'
                },
                {
                    data: 'brgnama',
                    className: 'text-center'
                },
                {
                    data: 'matnama_label',
                    orderable: false,
                    className: 'text-center'
                },
                {
                    data: 'katnama',
                    orderable: false,
                    className: 'text-center'
                },
                {
                    data: 'satnama',
                    orderable: false,
                    className: 'text-center'
                },
                {
                    data: 'aksi',
                    className: 'text-center',
                    orderable: false
                },
            ],
            initComplete: function() {
                // Balikin isi search bar biar sesuai state yang kesimpen
                // (misal abis dari halaman Edit terus balik lagi ke sini).
                $('#brgSearchInput').val(this.api().search());
            },
        });
        $('#filter_kategori, #filter_material').on('change', function() {
            table.ajax.reload();
        });

        const brgFilterPanel = $('#brgFilterPanel');
        const brgFilterToggle = $('#brgFilterToggle');
        let brgPanelTimer = null;
        let brgSearchTimer = null;

        function brgToggleFilterPanel(forceOpen = null) {
            const willOpen = forceOpen === null ? !brgFilterPanel.hasClass('is-open') : forceOpen;
            window.clearTimeout(brgPanelTimer);

            if (willOpen) {
                brgFilterPanel.addClass('is-visible');
                window.requestAnimationFrame(function() {
                    brgFilterPanel.addClass('is-open');
                });
            } else {
                brgFilterPanel.removeClass('is-open');
                brgPanelTimer = window.setTimeout(function() {
                    brgFilterPanel.removeClass('is-visible');
                }, 180);
            }

            brgFilterPanel.attr('aria-hidden', willOpen ? 'false' : 'true');
            brgFilterToggle.attr('aria-expanded', willOpen ? 'true' : 'false');
        }

        brgFilterToggle.on('click', function() {
            brgToggleFilterPanel();
        });

        $('#brgFilterClose').on('click', function() {
            brgToggleFilterPanel(false);
        });

        $('#brgSearchInput').on('input', function() {
            const keyword = this.value;
            window.clearTimeout(brgSearchTimer);
            brgSearchTimer = window.setTimeout(function() {
                table.search(keyword).draw();
            }, 350);
        });

        $('#brgPageLength').on('change', function() {
            table.page.len(Number(this.value)).draw();
        });

        $('#brgFilterReset').on('click', function() {
            $('#filter_kategori').val('');
            $('#filter_material').val('');
            $('#brgPageLength').val('50');
            $('#brgSearchInput').val('');
            table.search('').page.len(50);
            table.ajax.reload();
        });
    });

    function edit(kode) {
        window.location.href = ('/barang/edit/') + kode;
    }

    function riwayat(kode) {
        window.location.href = ('/barang/riwayat/') + kode;
    }

    function hapus(kode) {
        showBootstrapModal({
            title: 'Hapus Produk',
            html: `Yakin data Produk dengan nama <strong>${kode}</strong> di hapus ?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus !',
            cancelButtonText: 'Tidak'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "post",
                    url: '<?= site_url('barang/hapus') ?>',
                    data: {
                        [csrfToken]: csrfHash,
                        kode: kode
                    },
                    dataType: "json",
                    success: function(response) {
                        console.log(response);
                        if (response.sukses) {
                            showBootstrapModal({
                                icon: 'success',
                                title: 'Hapus data',
                                html: response.sukses
                            }).then(() => {
                                window.location.reload();
                            });
                        } else if (response.error) {
                            showBootstrapModal({
                                icon: 'error',
                                title: 'Gagal',
                                html: response.error
                            });
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        showBootstrapModal({
                            icon: 'error',
                            title: 'Kesalahan',
                            html: `Data Produk <b>${kode}</b> tidak bisa dihapus karena masih terkait dengan data di tabel lain`
                            // showBootstrapModal('Error', xhr.status + '\n' + thrownError, 'error')
                        });
                    }
                });
            }
        });
    }
</script>
<?= $this->endSection('isi') ?>
