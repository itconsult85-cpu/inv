<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Managemen Data Material
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>
<button type="button" class="btn btn-primary" onclick="location.href=('/material/tambah')">
    <i class="fa fa-plus-circle"></i> Tambah Data Material
</button>
<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<style>
    .mat-toolbar { align-items: flex-start; display: flex; justify-content: flex-end; margin-bottom: 1rem; position: relative; z-index: 30; }
    .mat-filter-anchor { align-items: flex-end; display: flex; flex-direction: column; position: relative; width: min(100%, 32rem); }
    .mat-search-shell { align-items: center; background: #fff; border: 1px solid #eef1f7; border-radius: 999px; box-shadow: 0 3px 10px rgba(15, 23, 42, .025); display: flex; gap: .65rem; min-height: 3.4rem; padding: .35rem .45rem .35rem 1.25rem; width: 100%; }
    .mat-search-shell > i { color: #6b7280; font-size: 1.1rem; }
    .mat-search-input { background: transparent; border: 0; box-shadow: none; color: #4b5563; flex: 1 1 auto; font-size: .95rem; min-width: 0; outline: 0; }
    .mat-search-input:focus { box-shadow: none; outline: 0; }
    .mat-filter-toggle { align-items: center; background: #16869a; border: 0; border-radius: 999px; color: #fff; display: inline-flex; flex: 0 0 2.65rem; height: 2.65rem; justify-content: center; width: 2.65rem; }
    .mat-filter-toggle:hover, .mat-filter-toggle:focus { background: #126f7f; color: #fff; outline: 0; }
    .mat-filter-panel { background: #fff; border: 1px solid #edf1f5; border-radius: 22px; box-shadow: 0 18px 42px rgba(15, 23, 42, .1); display: none; max-width: min(46rem, calc(100vw - 4rem)); opacity: 0; overflow: hidden; pointer-events: none; position: absolute; right: 0; top: calc(100% + .75rem); transform: translateY(-.45rem) scale(.985); transform-origin: top right; transition: opacity .18s ease, transform .18s ease; width: 26rem; }
    .mat-filter-panel.is-visible { display: block; }
    .mat-filter-panel.is-open { opacity: 1; pointer-events: auto; transform: translateY(0) scale(1); }
    .mat-filter-header { align-items: center; display: flex; justify-content: space-between; padding: 1.2rem 1.35rem .9rem; }
    .mat-filter-title { color: #111827; font-size: 1.2rem; font-weight: 800; margin: 0; }
    .mat-filter-close { align-items: center; background: #fff; border: 0; border-radius: 999px; box-shadow: 0 8px 22px rgba(15, 23, 42, .12); color: #111827; display: inline-flex; height: 2.4rem; justify-content: center; width: 2.4rem; }
    .mat-filter-section { border-top: 1px solid #edf1f5; padding: 1.05rem 1.35rem; }
    .mat-filter-label { color: #718096; font-size: .9rem; font-weight: 800; margin-bottom: .75rem; }
    .mat-filter-field-row, .mat-filter-action-row { display: flex; flex-wrap: wrap; gap: .75rem; }
    .mat-filter-select { background: #fff; border: 1px solid #e5eaf1; border-radius: 999px; color: #111827; min-height: 2.55rem; min-width: 11rem; padding: .45rem .85rem; }
    .mat-filter-reset { background: #eef2f7; border: 0; border-radius: 999px; color: #4b5563; font-weight: 800; min-height: 2.55rem; padding: .45rem 1.2rem; }
    @media (max-width: 768px) {
        .mat-toolbar { justify-content: stretch; }
        .mat-filter-anchor, .mat-search-shell, .mat-filter-panel { max-width: none; width: 100%; }
        .mat-filter-panel { left: 0; right: auto; }
        .mat-filter-select, .mat-filter-reset { width: 100%; }
    }
</style>
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<script src="<?= base_url() ?>/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

<div class="mat-toolbar">
    <div class="mat-filter-anchor">
        <div class="mat-search-shell">
            <i class="fas fa-search"></i>
            <input type="search" id="matSearchInput" class="mat-search-input" placeholder="Search anything..." aria-label="Search anything">
            <button type="button" class="mat-filter-toggle" id="matFilterToggle" title="Buka filter" aria-controls="matFilterPanel" aria-expanded="false">
                <i class="fas fa-sliders-h"></i>
            </button>
        </div>

        <section class="mat-filter-panel" id="matFilterPanel" aria-hidden="true">
            <div class="mat-filter-header">
                <h3 class="mat-filter-title">Filter</h3>
                <button type="button" class="mat-filter-close" id="matFilterClose" title="Tutup filter">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="mat-filter-section">
                <div class="mat-filter-label">Show Data</div>
                <div class="mat-filter-field-row">
                    <select id="matPageLength" class="mat-filter-select" aria-label="Show data">
                        <option value="10">10 entries</option>
                        <option value="25">25 entries</option>
                        <option value="50" selected>50 entries</option>
                        <option value="100">100 entries</option>
                    </select>
                </div>
            </div>

            <div class="mat-filter-section">
                <div class="mat-filter-action-row">
                    <button type="button" class="mat-filter-reset" id="matFilterReset">Reset</button>
                </div>
            </div>
        </section>
    </div>
</div>
<table class="table table-bordered table-striped" id="datamaterial" style="width: 100%;">
    <thead>
        <tr>
            <th style="width: 5%;">No</th>
            <th>Kode Material</th>
            <th>Nama Material</th>
            <th>Kategori</th>
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
        table = $('#datamaterial').DataTable({
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
            ajax: '<?= site_url('material/listData') ?>',
            order: [
                [1, 'asc']
            ],
            columns: [{
                    data: 'nomor',
                    orderable: false,
                    className: 'text-center'
                },
                {
                    data: 'matkode',
                    className: 'text-center'
                },
                {
                    data: 'matnama',
                    className: 'text-center'
                },
                {
                    data: 'katnama',
                    className: 'text-center'
                },
                {
                    data: 'aksi',
                    className: 'text-center',
                    orderable: false
                },
            ]
        });
        $('#matSearchInput').val(table.search());

        const matFilterPanel = $('#matFilterPanel');
        const matFilterToggle = $('#matFilterToggle');
        let matPanelTimer = null;
        let matSearchTimer = null;

        function matToggleFilterPanel(forceOpen = null) {
            const willOpen = forceOpen === null ? !matFilterPanel.hasClass('is-open') : forceOpen;
            window.clearTimeout(matPanelTimer);

            if (willOpen) {
                matFilterPanel.addClass('is-visible');
                window.requestAnimationFrame(function() {
                    matFilterPanel.addClass('is-open');
                });
            } else {
                matFilterPanel.removeClass('is-open');
                matPanelTimer = window.setTimeout(function() {
                    matFilterPanel.removeClass('is-visible');
                }, 180);
            }

            matFilterPanel.attr('aria-hidden', willOpen ? 'false' : 'true');
            matFilterToggle.attr('aria-expanded', willOpen ? 'true' : 'false');
        }

        matFilterToggle.on('click', function() {
            matToggleFilterPanel();
        });

        $('#matFilterClose').on('click', function() {
            matToggleFilterPanel(false);
        });

        $('#matSearchInput').on('input', function() {
            const keyword = this.value;
            window.clearTimeout(matSearchTimer);
            matSearchTimer = window.setTimeout(function() {
                table.search(keyword).draw();
            }, 350);
        });

        $('#matPageLength').on('change', function() {
            table.page.len(Number(this.value)).draw();
        });

        $('#matFilterReset').on('click', function() {
            $('#matPageLength').val('50');
            $('#matSearchInput').val('');
            table.search('').page.len(50);
            table.ajax.reload();
        });
    });

    function edit(kode) {
        $.getJSON('/material/pemakaian', { hash: kode })
            .done(function(response) {
                const dipakai = response.pemakaian && response.pemakaian.length > 0;

                if (!dipakai) {
                    window.location.href = ('/material/edit/') + kode;
                    return;
                }

                const daftar = response.pemakaian
                    .map(item => `<li><strong>${item.label}</strong> (${item.jumlah} data)</li>`)
                    .join('');
                Swal.fire({
                    title: 'Material Sedang Digunakan',
                    html: `<p>Material ini sedang dipakai di data lain. Mengganti namanya aman -- data yang memakainya otomatis ikut menampilkan nama baru.</p><ul style="text-align:left; margin:12px auto 0; width:fit-content;">${daftar}</ul>`,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Lanjut Edit',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = ('/material/edit/') + kode;
                    }
                });
            })
            .fail(function() {
                Swal.fire('Kesalahan', 'Informasi pemakaian material gagal dimuat.', 'error');
            });
    }

    function escapeHtmlMaterial(value) {
        return String(value ?? '').replace(/[&<>"']/g, function(match) {
            return ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            })[match];
        });
    }

    function labelSupplier(hash) {
        Swal.fire({
            title: 'Memuat data...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            type: 'post',
            url: '<?= site_url('material/labelSupplier') ?>',
            data: {
                [csrfToken]: csrfHash,
                hash: hash
            },
            dataType: 'json',
            success: function(response) {
                if (response.error) {
                    Swal.fire('Gagal', response.error, 'error');
                    return;
                }

                const suppliers = response.suppliers || [];
                const opsiSupplier = suppliers.map(s => `<option value="${s.id}">${escapeHtmlMaterial(s.nama)}</option>`).join('');

                const renderDaftar = (labels) => {
                    if (!labels.length) {
                        return '<div class="text-muted">Belum ada Label Nama untuk material ini.</div>';
                    }
                    return '<table class="table table-sm table-bordered mb-0"><thead><tr><th>Supplier</th><th>Label Nama</th><th></th></tr></thead><tbody>' +
                        labels.map(l => `
                            <tr>
                                <td>${escapeHtmlMaterial(l.supnama)}</td>
                                <td>${escapeHtmlMaterial(l.label_nama)}</td>
                                <td class="text-center"><button type="button" class="btn btn-sm btn-danger" data-id="${l.id}" onclick="hapusLabelSupplier(${l.id}, '${hash}')"><i class="fa fa-trash-alt"></i></button></td>
                            </tr>
                        `).join('') + '</tbody></table>';
                };

                Swal.fire({
                    title: 'Label Nama per Supplier',
                    html: `
                        <div class="text-left">
                            <div class="mb-2"><strong>Material:</strong> ${escapeHtmlMaterial(response.material_nama)}</div>
                            <div id="labelSupplierDaftar" class="mb-3">${renderDaftar(response.labels || [])}</div>
                            <hr>
                            <div class="form-group mb-2">
                                <label class="mb-1">Supplier</label>
                                <select id="labelSupplierSupplierId" class="form-control">
                                    <option value="">-- Pilih Supplier --</option>
                                    ${opsiSupplier}
                                </select>
                            </div>
                            <div class="form-group mb-2">
                                <label class="mb-1">Label Nama (buat supplier ini)</label>
                                <input type="text" id="labelSupplierNama" class="form-control" maxlength="150" placeholder="Nama yang tercantum di PO Keluar untuk supplier ini">
                            </div>
                            <button type="button" class="btn btn-success btn-sm" id="btnSimpanLabelSupplier"><i class="fa fa-save"></i> Simpan</button>
                        </div>
                    `,
                    width: 620,
                    showConfirmButton: false,
                    showCancelButton: true,
                    cancelButtonText: 'Tutup',
                    didOpen: () => {
                        $('#btnSimpanLabelSupplier').on('click', function() {
                            const supplierId = $('#labelSupplierSupplierId').val();
                            const labelNama = $('#labelSupplierNama').val().trim();

                            if (!supplierId) {
                                Swal.fire('Gagal', 'Pilih supplier terlebih dahulu.', 'error');
                                return;
                            }
                            if (!labelNama) {
                                Swal.fire('Gagal', 'Label Nama tidak boleh kosong.', 'error');
                                return;
                            }

                            Swal.showLoading();
                            $.ajax({
                                type: 'post',
                                url: '<?= site_url('material/simpanLabelSupplier') ?>',
                                data: {
                                    [csrfToken]: csrfHash,
                                    hash: hash,
                                    supplier_id: supplierId,
                                    label_nama: labelNama
                                },
                                dataType: 'json',
                                success: function(saveResponse) {
                                    if (saveResponse.error) {
                                        Swal.fire('Gagal', saveResponse.error, 'error').then(() => labelSupplier(hash));
                                        return;
                                    }
                                    Swal.fire('Berhasil', saveResponse.sukses, 'success').then(() => labelSupplier(hash));
                                },
                                error: function(xhr, ajaxOptions, thrownError) {
                                    Swal.fire('Gagal', xhr.status + ' ' + thrownError, 'error').then(() => labelSupplier(hash));
                                }
                            });
                        });
                    }
                });
            },
            error: function(xhr, ajaxOptions, thrownError) {
                Swal.fire('Gagal', xhr.status + '\n' + thrownError, 'error');
            }
        });
    }

    function hapusLabelSupplier(id, hash) {
        Swal.fire({
            title: 'Hapus Label Nama?',
            text: 'Label Nama untuk supplier ini akan dihapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.post('/material/hapusLabelSupplier', {
                [csrfToken]: csrfHash,
                id: id
            }, function(response) {
                if (response.error) {
                    Swal.fire('Gagal', response.error, 'error');
                    return;
                }
                Swal.fire('Berhasil', response.sukses, 'success').then(() => labelSupplier(hash));
            }, 'json');
        });
    }

    function hapus(kode, nama) {
        Swal.fire({
            title: 'Hapus Material',
            html: `Yakin data material dengan nama <strong>${nama}</strong> di hapus ?`,
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
                    url: '<?= site_url('material/hapus') ?>',
                    data: {
                        [csrfToken]: csrfHash,
                        kode: kode,
                        nama: nama,
                    },
                    dataType: "json",
                    success: function(response) {
                        console.log(response);
                        if (response.sukses) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Hapus data',
                                html: response.sukses
                            }).then(() => {
                                window.location.reload();
                            });
                        } else if (response.error) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                html: response.error
                            });
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Kesalahan',
                            html: `Data Material <b>${nama}</b> tidak bisa dihapus karena masih terkait dengan data di tabel lain`
                            // alert(xhr.status + '\n' + thrownError)
                        });
                    }
                });
            }
        });
    }
</script>
<?= $this->endSection('isi') ?>
