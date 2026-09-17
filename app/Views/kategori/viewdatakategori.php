<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Managemen Data Kategori
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>

<?= form_button('', '<i class="fa fa-plus-circle"></i> Tambah Data Kategori', [
    'class' => 'btn btn-primary',
    'onclick' => "location.href=('" . site_url('kategori/formtambah') . "')"
]) ?>

<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<style>
    .kat-toolbar { align-items: flex-start; display: flex; justify-content: flex-end; margin-bottom: 1rem; position: relative; z-index: 30; }
    .kat-filter-anchor { align-items: flex-end; display: flex; flex-direction: column; position: relative; width: min(100%, 32rem); }
    .kat-search-shell { align-items: center; background: #fff; border: 1px solid #eef1f7; border-radius: 999px; box-shadow: 0 3px 10px rgba(15, 23, 42, .025); display: flex; gap: .65rem; min-height: 3.4rem; padding: .35rem .45rem .35rem 1.25rem; width: 100%; }
    .kat-search-shell > i { color: #6b7280; font-size: 1.1rem; }
    .kat-search-input { background: transparent; border: 0; box-shadow: none; color: #4b5563; flex: 1 1 auto; font-size: .95rem; min-width: 0; outline: 0; }
    .kat-search-input:focus { box-shadow: none; outline: 0; }
    .kat-filter-toggle { align-items: center; background: #16869a; border: 0; border-radius: 999px; color: #fff; display: inline-flex; flex: 0 0 2.65rem; height: 2.65rem; justify-content: center; width: 2.65rem; }
    .kat-filter-toggle:hover, .kat-filter-toggle:focus { background: #126f7f; color: #fff; outline: 0; }
    .kat-filter-panel { background: #fff; border: 1px solid #edf1f5; border-radius: 22px; box-shadow: 0 18px 42px rgba(15, 23, 42, .1); display: none; max-width: min(46rem, calc(100vw - 4rem)); opacity: 0; overflow: hidden; pointer-events: none; position: absolute; right: 0; top: calc(100% + .75rem); transform: translateY(-.45rem) scale(.985); transform-origin: top right; transition: opacity .18s ease, transform .18s ease; width: 26rem; }
    .kat-filter-panel.is-visible { display: block; }
    .kat-filter-panel.is-open { opacity: 1; pointer-events: auto; transform: translateY(0) scale(1); }
    .kat-filter-header { align-items: center; display: flex; justify-content: space-between; padding: 1.2rem 1.35rem .9rem; }
    .kat-filter-title { color: #111827; font-size: 1.2rem; font-weight: 800; margin: 0; }
    .kat-filter-close { align-items: center; background: #fff; border: 0; border-radius: 999px; box-shadow: 0 8px 22px rgba(15, 23, 42, .12); color: #111827; display: inline-flex; height: 2.4rem; justify-content: center; width: 2.4rem; }
    .kat-filter-section { border-top: 1px solid #edf1f5; padding: 1.05rem 1.35rem; }
    .kat-filter-label { color: #718096; font-size: .9rem; font-weight: 800; margin-bottom: .75rem; }
    .kat-filter-field-row, .kat-filter-action-row { display: flex; flex-wrap: wrap; gap: .75rem; }
    .kat-filter-select { background: #fff; border: 1px solid #e5eaf1; border-radius: 999px; color: #111827; min-height: 2.55rem; min-width: 11rem; padding: .45rem .85rem; }
    .kat-filter-reset { background: #eef2f7; border: 0; border-radius: 999px; color: #4b5563; font-weight: 800; min-height: 2.55rem; padding: .45rem 1.2rem; }
    @media (max-width: 768px) {
        .kat-toolbar { justify-content: stretch; }
        .kat-filter-anchor, .kat-search-shell, .kat-filter-panel { max-width: none; width: 100%; }
        .kat-filter-panel { left: 0; right: auto; }
        .kat-filter-select, .kat-filter-reset { width: 100%; }
    }
</style>
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<script src="<?= base_url() ?>/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

<div class="kat-toolbar">
    <div class="kat-filter-anchor">
        <div class="kat-search-shell">
            <i class="fas fa-search"></i>
            <input type="search" id="katSearchInput" class="kat-search-input" placeholder="Search anything..." aria-label="Search anything">
            <button type="button" class="kat-filter-toggle" id="katFilterToggle" title="Buka filter" aria-controls="katFilterPanel" aria-expanded="false">
                <i class="fas fa-sliders-h"></i>
            </button>
        </div>

        <section class="kat-filter-panel" id="katFilterPanel" aria-hidden="true">
            <div class="kat-filter-header">
                <h3 class="kat-filter-title">Filter</h3>
                <button type="button" class="kat-filter-close" id="katFilterClose" title="Tutup filter">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="kat-filter-section">
                <div class="kat-filter-label">Show Data</div>
                <div class="kat-filter-field-row">
                    <select id="katPageLength" class="kat-filter-select" aria-label="Show data">
                        <option value="10">10 entries</option>
                        <option value="25">25 entries</option>
                        <option value="50" selected>50 entries</option>
                        <option value="100">100 entries</option>
                    </select>
                </div>
            </div>

            <div class="kat-filter-section">
                <div class="kat-filter-action-row">
                    <button type="button" class="kat-filter-reset" id="katFilterReset">Reset</button>
                </div>
            </div>
        </section>
    </div>
</div>
<table class="table table-bordered table-striped" id="datakategori" style="width: 100%;">
    <thead>
        <tr>
            <th style="width: 5%;">No</th>
            <th>Nama Kategori</th>
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
        table = $('#datakategori').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            searching: true,
            searchDelay: 500,
            stateSave: true,
            stateDuration: -1,
            pageLength: 10,
            dom: "<'row'<'col-sm-12'tr>><'row align-items-center mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            ajax: '<?= site_url('kategori/listData') ?>',
            order: [
                [1, 'asc']
            ],
            columns: [{
                    data: 'nomor',
                    orderable: false
                },
                {
                    data: 'katnama'
                },
                {
                    data: 'aksi',
                    className: 'text-center',
                    orderable: false
                },
            ]
        });
        $('#katSearchInput').val(table.search());

        const katFilterPanel = $('#katFilterPanel');
        const katFilterToggle = $('#katFilterToggle');
        let katPanelTimer = null;
        let katSearchTimer = null;

        function katToggleFilterPanel(forceOpen = null) {
            const willOpen = forceOpen === null ? !katFilterPanel.hasClass('is-open') : forceOpen;
            window.clearTimeout(katPanelTimer);

            if (willOpen) {
                katFilterPanel.addClass('is-visible');
                window.requestAnimationFrame(function() {
                    katFilterPanel.addClass('is-open');
                });
            } else {
                katFilterPanel.removeClass('is-open');
                katPanelTimer = window.setTimeout(function() {
                    katFilterPanel.removeClass('is-visible');
                }, 180);
            }

            katFilterPanel.attr('aria-hidden', willOpen ? 'false' : 'true');
            katFilterToggle.attr('aria-expanded', willOpen ? 'true' : 'false');
        }

        katFilterToggle.on('click', function() {
            katToggleFilterPanel();
        });

        $('#katFilterClose').on('click', function() {
            katToggleFilterPanel(false);
        });

        $('#katSearchInput').on('input', function() {
            const keyword = this.value;
            window.clearTimeout(katSearchTimer);
            katSearchTimer = window.setTimeout(function() {
                table.search(keyword).draw();
            }, 350);
        });

        $('#katPageLength').on('change', function() {
            table.page.len(Number(this.value)).draw();
        });

        $('#katFilterReset').on('click', function() {
            $('#katPageLength').val('50');
            $('#katSearchInput').val('');
            table.search('').page.len(50);
            table.ajax.reload();
        });
    });

    function escapeHtml(value) {
        return $('<div>').text(value ?? '').html();
    }

    function htmlPemakaian(pemakaian) {
        if (!pemakaian || pemakaian.length === 0) {
            return '<p class="mb-0">Kategori ini belum digunakan pada data lain.</p>';
        }

        let html = '<p class="text-left mb-2">Kategori ini digunakan pada:</p><ul class="text-left">';
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
            url: '<?= site_url('kategori/pemakaian') ?>',
            data: { kode: kode },
            dataType: 'json'
        });
    }

    function editKategori(button) {
        const kode = button.dataset.kode;
        const hash = button.dataset.hash;
        const nama = button.dataset.nama;

        ambilPemakaian(kode).done(function(response) {
            const dipakai = response.pemakaian && response.pemakaian.length > 0;

            showBootstrapModal({
                title: dipakai ? 'Kategori Sedang Digunakan' : 'Informasi Pemakaian Kategori',
                html: `<p>Kategori <strong>${escapeHtml(nama)}</strong>${dipakai ? ' sedang dipakai di data lain. Mengganti namanya aman -- data yang memakainya otomatis ikut menampilkan nama baru.' : ''}</p>${htmlPemakaian(response.pemakaian)}`,
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Lanjut Edit',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location = '/kategori/formedit/' + hash;
                }
            });
        }).fail(function() {
            showBootstrapModal('Kesalahan', 'Informasi pemakaian kategori gagal dimuat', 'error');
        });
    }

    function hapusKategori(button) {
        const kode = button.dataset.kode;
        const nama = button.dataset.nama;

        ambilPemakaian(kode).done(function(response) {
            if (response.digunakan) {
                showBootstrapModal({
                    title: 'Kategori Tidak Bisa Dihapus',
                    html: `<p>Kategori <strong>${escapeHtml(nama)}</strong> masih digunakan.</p>${htmlPemakaian(response.pemakaian)}`,
                    icon: 'error'
                });
                return;
            }

            konfirmasiHapus(kode, nama);
        }).fail(function() {
            showBootstrapModal('Kesalahan', 'Informasi pemakaian kategori gagal dimuat', 'error');
        });
    }

    function konfirmasiHapus(kode, nama) {
        showBootstrapModal({
            title: 'Hapus Kategori',
            html: `Yakin data kategori dengan nama <strong>${escapeHtml(nama)}</strong> dihapus?`,
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
                    url: '<?= site_url('kategori/hapus') ?>',
                    data: {
                        [csrfToken]: csrfHash,
                        kode: kode,
                        nama: nama,
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.sukses) {
                            showBootstrapModal('Berhasil', response.sukses, 'success').then(() => {
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
                        showBootstrapModal({
                            icon: 'error',
                            title: 'Kesalahan',
                            html: `Data Kategori <b>${nama}</b> tidak bisa dihapus karena masih terkait dengan data di tabel lain`
                            // alert(xhr.status + '\n' + thrownError)
                        });
                    }
                });
            }
        })
    }
</script>

<?= $this->endSection('isi') ?>
