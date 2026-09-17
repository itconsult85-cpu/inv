<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Managemen Data Satuan
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>
<?= form_button('', '<i class="fa fa-plus-circle"></i> Tambah Data Satuan', [
    'class' => 'btn btn-primary',
    'onclick' => "location.href=('" . site_url('satuan/formtambah') . "')"
]) ?>

<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<style>
    .sat-toolbar { align-items: flex-start; display: flex; justify-content: flex-end; margin-bottom: 1rem; position: relative; z-index: 30; }
    .sat-filter-anchor { align-items: flex-end; display: flex; flex-direction: column; position: relative; width: min(100%, 32rem); }
    .sat-search-shell { align-items: center; background: #fff; border: 1px solid #eef1f7; border-radius: 999px; box-shadow: 0 3px 10px rgba(15, 23, 42, .025); display: flex; gap: .65rem; min-height: 3.4rem; padding: .35rem .45rem .35rem 1.25rem; width: 100%; }
    .sat-search-shell > i { color: #6b7280; font-size: 1.1rem; }
    .sat-search-input { background: transparent; border: 0; box-shadow: none; color: #4b5563; flex: 1 1 auto; font-size: .95rem; min-width: 0; outline: 0; }
    .sat-search-input:focus { box-shadow: none; outline: 0; }
    .sat-filter-toggle { align-items: center; background: #16869a; border: 0; border-radius: 999px; color: #fff; display: inline-flex; flex: 0 0 2.65rem; height: 2.65rem; justify-content: center; width: 2.65rem; }
    .sat-filter-toggle:hover, .sat-filter-toggle:focus { background: #126f7f; color: #fff; outline: 0; }
    .sat-filter-panel { background: #fff; border: 1px solid #edf1f5; border-radius: 22px; box-shadow: 0 18px 42px rgba(15, 23, 42, .1); display: none; max-width: min(46rem, calc(100vw - 4rem)); opacity: 0; overflow: hidden; pointer-events: none; position: absolute; right: 0; top: calc(100% + .75rem); transform: translateY(-.45rem) scale(.985); transform-origin: top right; transition: opacity .18s ease, transform .18s ease; width: 26rem; }
    .sat-filter-panel.is-visible { display: block; }
    .sat-filter-panel.is-open { opacity: 1; pointer-events: auto; transform: translateY(0) scale(1); }
    .sat-filter-header { align-items: center; display: flex; justify-content: space-between; padding: 1.2rem 1.35rem .9rem; }
    .sat-filter-title { color: #111827; font-size: 1.2rem; font-weight: 800; margin: 0; }
    .sat-filter-close { align-items: center; background: #fff; border: 0; border-radius: 999px; box-shadow: 0 8px 22px rgba(15, 23, 42, .12); color: #111827; display: inline-flex; height: 2.4rem; justify-content: center; width: 2.4rem; }
    .sat-filter-section { border-top: 1px solid #edf1f5; padding: 1.05rem 1.35rem; }
    .sat-filter-label { color: #718096; font-size: .9rem; font-weight: 800; margin-bottom: .75rem; }
    .sat-filter-field-row, .sat-filter-action-row { display: flex; flex-wrap: wrap; gap: .75rem; }
    .sat-filter-select { background: #fff; border: 1px solid #e5eaf1; border-radius: 999px; color: #111827; min-height: 2.55rem; min-width: 11rem; padding: .45rem .85rem; }
    .sat-filter-reset { background: #eef2f7; border: 0; border-radius: 999px; color: #4b5563; font-weight: 800; min-height: 2.55rem; padding: .45rem 1.2rem; }
    @media (max-width: 768px) {
        .sat-toolbar { justify-content: stretch; }
        .sat-filter-anchor, .sat-search-shell, .sat-filter-panel { max-width: none; width: 100%; }
        .sat-filter-panel { left: 0; right: auto; }
        .sat-filter-select, .sat-filter-reset { width: 100%; }
    }
</style>
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<script src="<?= base_url() ?>/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

<div class="sat-toolbar">
    <div class="sat-filter-anchor">
        <div class="sat-search-shell">
            <i class="fas fa-search"></i>
            <input type="search" id="satSearchInput" class="sat-search-input" placeholder="Search anything..." aria-label="Search anything">
            <button type="button" class="sat-filter-toggle" id="satFilterToggle" title="Buka filter" aria-controls="satFilterPanel" aria-expanded="false">
                <i class="fas fa-sliders-h"></i>
            </button>
        </div>

        <section class="sat-filter-panel" id="satFilterPanel" aria-hidden="true">
            <div class="sat-filter-header">
                <h3 class="sat-filter-title">Filter</h3>
                <button type="button" class="sat-filter-close" id="satFilterClose" title="Tutup filter">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="sat-filter-section">
                <div class="sat-filter-label">Show Data</div>
                <div class="sat-filter-field-row">
                    <select id="satPageLength" class="sat-filter-select" aria-label="Show data">
                        <option value="10">10 entries</option>
                        <option value="25">25 entries</option>
                        <option value="50" selected>50 entries</option>
                        <option value="100">100 entries</option>
                    </select>
                </div>
            </div>

            <div class="sat-filter-section">
                <div class="sat-filter-action-row">
                    <button type="button" class="sat-filter-reset" id="satFilterReset">Reset</button>
                </div>
            </div>
        </section>
    </div>
</div>
<table class="table table-bordered table-striped" id="datasatuan">
    <thead>
        <tr>
            <th style="width: 5%;">No</th>
            <th>Nama Satuan</th>
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
        table = $('#datasatuan').DataTable({
            lengthChange: true,
            autoWidth: false,
            responsive: true,
            processing: true,
            serverSide: true,
            searching: true,
            searchDelay: 500,
            stateSave: true,
            stateDuration: -1,
            pageLength: 10,
            dom: "<'row'<'col-sm-12'tr>><'row align-items-center mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            ajax: '<?= site_url('satuan/listData') ?>',
            order: [
                [1, 'asc']
            ],
            columns: [{
                    data: 'nomor',
                    orderable: false
                },
                {
                    data: 'satnama'
                },
                {
                    data: 'aksi',
                    className: 'text-center',
                    orderable: false
                },
            ]
        });
        $('#satSearchInput').val(table.search());

        const satFilterPanel = $('#satFilterPanel');
        const satFilterToggle = $('#satFilterToggle');
        let satPanelTimer = null;
        let satSearchTimer = null;

        function satToggleFilterPanel(forceOpen = null) {
            const willOpen = forceOpen === null ? !satFilterPanel.hasClass('is-open') : forceOpen;
            window.clearTimeout(satPanelTimer);

            if (willOpen) {
                satFilterPanel.addClass('is-visible');
                window.requestAnimationFrame(function() {
                    satFilterPanel.addClass('is-open');
                });
            } else {
                satFilterPanel.removeClass('is-open');
                satPanelTimer = window.setTimeout(function() {
                    satFilterPanel.removeClass('is-visible');
                }, 180);
            }

            satFilterPanel.attr('aria-hidden', willOpen ? 'false' : 'true');
            satFilterToggle.attr('aria-expanded', willOpen ? 'true' : 'false');
        }

        satFilterToggle.on('click', function() {
            satToggleFilterPanel();
        });

        $('#satFilterClose').on('click', function() {
            satToggleFilterPanel(false);
        });

        $('#satSearchInput').on('input', function() {
            const keyword = this.value;
            window.clearTimeout(satSearchTimer);
            satSearchTimer = window.setTimeout(function() {
                table.search(keyword).draw();
            }, 350);
        });

        $('#satPageLength').on('change', function() {
            table.page.len(Number(this.value)).draw();
        });

        $('#satFilterReset').on('click', function() {
            $('#satPageLength').val('50');
            $('#satSearchInput').val('');
            table.search('').page.len(50);
            table.ajax.reload();
        });
    });

    function escapeHtml(value) {
        return $('<div>').text(value ?? '').html();
    }

    function htmlPemakaian(pemakaian) {
        if (!pemakaian || pemakaian.length === 0) {
            return '<p class="mb-0">Satuan ini belum digunakan pada data lain.</p>';
        }

        let html = '<p class="text-left mb-2">Satuan ini digunakan pada:</p><ul class="text-left">';
        pemakaian.forEach(function(item) {
            html += `<li><strong>${escapeHtml(item.label)}</strong>: ${item.jumlah} data`;
            if (item.contoh && item.contoh.length > 0) {
                html += `<br><small>${item.contoh.map(escapeHtml).join(', ')}</small>`;
            }
            html += '</li>';
        });
        return html + '</ul>';
    }

    function ambilPemakaian(kode) {
        return $.ajax({
            type: 'get',
            url: '<?= site_url('satuan/pemakaian') ?>',
            data: { kode: kode },
            dataType: 'json'
        });
    }

    function editSatuan(button) {
        const kode = button.dataset.kode;
        const hash = button.dataset.hash;
        const nama = button.dataset.nama;

        ambilPemakaian(kode).done(function(response) {
            const dipakai = response.pemakaian && response.pemakaian.length > 0;

            showBootstrapModal({
                title: dipakai ? 'Satuan Sedang Digunakan' : 'Informasi Pemakaian Satuan',
                html: `<p>Satuan <strong>${escapeHtml(nama)}</strong>${dipakai ? ' sedang dipakai di data lain. Mengganti namanya aman -- data yang memakainya otomatis ikut menampilkan nama baru.' : ''}</p>${htmlPemakaian(response.pemakaian)}`,
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Lanjut Edit',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location = '/satuan/formedit/' + hash;
                }
            });
        }).fail(function() {
            showBootstrapModal('Kesalahan', 'Informasi pemakaian satuan gagal dimuat', 'error');
        });
    }

    function hapusSatuan(button) {
        const kode = button.dataset.kode;
        const nama = button.dataset.nama;

        ambilPemakaian(kode).done(function(response) {
            if (response.digunakan) {
                showBootstrapModal({
                    title: 'Satuan Tidak Bisa Dihapus',
                    html: `<p>Satuan <strong>${escapeHtml(nama)}</strong> masih digunakan.</p>${htmlPemakaian(response.pemakaian)}`,
                    icon: 'error'
                });
                return;
            }

            konfirmasiHapus(kode, nama);
        }).fail(function() {
            showBootstrapModal('Kesalahan', 'Informasi pemakaian satuan gagal dimuat', 'error');
        });
    }

    function konfirmasiHapus(kode, nama) {
        showBootstrapModal({
            title: 'Hapus Satuan',
            html: `Yakin data satuan dengan nama <strong>${escapeHtml(nama)}</strong> dihapus?`,
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
                    url: '<?= site_url('satuan/hapus') ?>',
                    data: {
                        [csrfToken]: csrfHash,
                        kode: kode,
                        nama: nama,
                    },
                    dataType: "json",
                    success: function(response) {
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
                                html: `${response.error}${htmlPemakaian(response.pemakaian)}`
                            });
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        showBootstrapModal('Error', xhr.status + '\n' + thrownError, 'error');
                        // showBootstrapModal({
                        //     icon: 'error',
                        //     title: 'Kesalahan',
                        //     html: `Data Satuan <b>${nama}</b> tidak bisa dihapus karena masih terkait dengan data di tabel lain`,
                        // });
                    }
                });
            }
        });
    }
</script>

<?= $this->endSection('isi') ?>
