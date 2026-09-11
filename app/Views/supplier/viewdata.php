<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Managemen Data Supplier
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>

<button type="button" class="btn btn-primary" id="tombolTambahSupplier">
    <i class="fa fa-plus-circle"></i> Input Data Supplier
</button>

<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<style>
    .sup-toolbar { align-items: flex-start; display: flex; justify-content: flex-end; margin-bottom: 1rem; position: relative; z-index: 30; }
    .sup-filter-anchor { align-items: flex-end; display: flex; flex-direction: column; position: relative; width: min(100%, 32rem); }
    .sup-search-shell { align-items: center; background: #fff; border: 1px solid #eef1f7; border-radius: 999px; box-shadow: 0 3px 10px rgba(15, 23, 42, .025); display: flex; gap: .65rem; min-height: 3.4rem; padding: .35rem .45rem .35rem 1.25rem; width: 100%; }
    .sup-search-shell > i { color: #6b7280; font-size: 1.1rem; }
    .sup-search-input { background: transparent; border: 0; box-shadow: none; color: #4b5563; flex: 1 1 auto; font-size: .95rem; min-width: 0; outline: 0; }
    .sup-search-input:focus { box-shadow: none; outline: 0; }
    .sup-filter-toggle { align-items: center; background: #16869a; border: 0; border-radius: 999px; color: #fff; display: inline-flex; flex: 0 0 2.65rem; height: 2.65rem; justify-content: center; width: 2.65rem; }
    .sup-filter-toggle:hover, .sup-filter-toggle:focus { background: #126f7f; color: #fff; outline: 0; }
    .sup-filter-panel { background: #fff; border: 1px solid #edf1f5; border-radius: 22px; box-shadow: 0 18px 42px rgba(15, 23, 42, .1); display: none; max-width: min(46rem, calc(100vw - 4rem)); opacity: 0; overflow: hidden; pointer-events: none; position: absolute; right: 0; top: calc(100% + .75rem); transform: translateY(-.45rem) scale(.985); transform-origin: top right; transition: opacity .18s ease, transform .18s ease; width: 26rem; }
    .sup-filter-panel.is-visible { display: block; }
    .sup-filter-panel.is-open { opacity: 1; pointer-events: auto; transform: translateY(0) scale(1); }
    .sup-filter-header { align-items: center; display: flex; justify-content: space-between; padding: 1.2rem 1.35rem .9rem; }
    .sup-filter-title { color: #111827; font-size: 1.2rem; font-weight: 800; margin: 0; }
    .sup-filter-close { align-items: center; background: #fff; border: 0; border-radius: 999px; box-shadow: 0 8px 22px rgba(15, 23, 42, .12); color: #111827; display: inline-flex; height: 2.4rem; justify-content: center; width: 2.4rem; }
    .sup-filter-section { border-top: 1px solid #edf1f5; padding: 1.05rem 1.35rem; }
    .sup-filter-label { color: #718096; font-size: .9rem; font-weight: 800; margin-bottom: .75rem; }
    .sup-filter-field-row, .sup-filter-action-row { display: flex; flex-wrap: wrap; gap: .75rem; }
    .sup-filter-select { background: #fff; border: 1px solid #e5eaf1; border-radius: 999px; color: #111827; min-height: 2.55rem; min-width: 11rem; padding: .45rem .85rem; }
    .sup-filter-reset { background: #eef2f7; border: 0; border-radius: 999px; color: #4b5563; font-weight: 800; min-height: 2.55rem; padding: .45rem 1.2rem; }
    @media (max-width: 768px) {
        .sup-toolbar { justify-content: stretch; }
        .sup-filter-anchor, .sup-search-shell, .sup-filter-panel { max-width: none; width: 100%; }
        .sup-filter-panel { left: 0; right: auto; }
        .sup-filter-select, .sup-filter-reset { width: 100%; }
    }
</style>
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<script src="<?= base_url() ?>/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

<div class="sup-toolbar">
    <div class="sup-filter-anchor">
        <div class="sup-search-shell">
            <i class="fas fa-search"></i>
            <input type="search" id="supSearchInput" class="sup-search-input" placeholder="Search anything..." aria-label="Search anything">
            <button type="button" class="sup-filter-toggle" id="supFilterToggle" title="Buka filter" aria-controls="supFilterPanel" aria-expanded="false">
                <i class="fas fa-sliders-h"></i>
            </button>
        </div>

        <section class="sup-filter-panel" id="supFilterPanel" aria-hidden="true">
            <div class="sup-filter-header">
                <h3 class="sup-filter-title">Filter</h3>
                <button type="button" class="sup-filter-close" id="supFilterClose" title="Tutup filter">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="sup-filter-section">
                <div class="sup-filter-label">Show Data</div>
                <div class="sup-filter-field-row">
                    <select id="supPageLength" class="sup-filter-select" aria-label="Show data">
                        <option value="10">10 entries</option>
                        <option value="25">25 entries</option>
                        <option value="50" selected>50 entries</option>
                        <option value="100">100 entries</option>
                    </select>
                </div>
            </div>

            <div class="sup-filter-section">
                <div class="sup-filter-action-row">
                    <button type="button" class="sup-filter-reset" id="supFilterReset">Reset</button>
                </div>
            </div>
        </section>
    </div>
</div>
<table id="datasupplier" class="table table-bordered table-striped" style="width: 100%;">
    <thead>
        <tr>
            <th style="width: 5%;">No</th>
            <th>Supplier</th>
            <th>Nama PIC</th>
            <th>Email</th>
            <th>No Telp / HP</th>
            <th>Alamat</th>
            <th style="width: 20%;">Aksi</th>
        </tr>
    </thead>
    <tbody>

    </tbody>
</table>

<!-- Modal Edit Supplier -->
<div class="modal fade" id="modalEditSupplier" tabindex="-1" aria-labelledby="modalEditSupplierLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditSupplierLabel">Edit Supplier</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formEditSupplier">
                    <input type="hidden" name="id_supplier" id="editIdSupplier">
                    <div class="form-group">
                        <label for="editNamaSupplier">Nama Supplier</label>
                        <input type="text" class="form-control" name="editNamaSupplier" id="editNamaSupplier">
                        <span class="invalid-feedback errorEditNamaSupplier" id="errorEditNamaSupplier"></span>
                    </div>
                    <div class="form-group">
                        <label for="editNamaPic">Nama PIC</label>
                        <input type="text" class="form-control" name="editNamaPic" id="editNamaPic">
                        <span class="invalid-feedback errorEditNamaPic" id="errorEditNamaPic"></span>
                    </div>
                    <div class="form-group">
                        <label for="editEmail">Email</label>
                        <input type="email" class="form-control" name="editEmail" id="editEmail">
                        <span class="invalid-feedback errorEditEmail" id="errorEditEmail"></span>
                    </div>
                    <div class="form-group">
                        <label for="editTelp">No Telp / Handphone</label>
                        <input type="text" class="form-control" name="editTelp" id="editTelp">
                        <span class="invalid-feedback errorEditTelp" id="errorEditTelp"></span>
                    </div>
                    <div class="form-group">
                        <label for="editAlamat">Alamat</label>
                        <textarea class="form-control" name="editAlamat" id="editAlamat"></textarea>
                        <span class="invalid-feedback errorEditAlamat" id="errorEditAlamat"></span>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="update()">Update</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<div class="viewmodal" style="display:none;"></div>
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
    let table;

    $(document).ready(function() {
        table = $('#datasupplier').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            searching: true,
            searchDelay: 500,
            stateSave: true,
            stateDuration: -1,
            pageLength: 10,
            dom: "<'row'<'col-sm-12'tr>><'row align-items-center mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            ajax: '<?= site_url('supplier/listDataView') ?>',
            order: [
                [1, 'asc']
            ],
            columns: [{
                    data: 'nomor',
                    orderable: false,
                    className: 'text-center'
                },
                {
                    data: 'supnama',
                    className: 'text-center'
                },
                {
                    data: 'suppic',
                    className: 'text-center'
                },
                {
                    data: 'supemail',
                    className: 'text-center',
                    render: function(data) {
                        return data || '-';
                    }
                },
                {
                    data: 'suptelp',
                    className: 'text-center'
                },
                {
                    data: 'alamat',
                    orderable: false,
                    className: 'text-center'
                },
                {
                    data: 'aksi',
                    className: 'text-center',
                    orderable: false
                },
            ]
        });
        $('#supSearchInput').val(table.search());
        $('#tombolTambahSupplier').click(function(e) {
            e.preventDefault();
            $.ajax({
                url: '<?= site_url('supplier/formtambah') ?>',
                dataType: "json",
                success: function(response) {
                    if (response.data) {
                        $('.viewmodal').html(response.data).show();
                        $('#modaltambahsupplier').modal('show');
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert(xhr.status + '\n' + thrownError)
                }
            });
        });

        const supFilterPanel = $('#supFilterPanel');
        const supFilterToggle = $('#supFilterToggle');
        let supPanelTimer = null;
        let supSearchTimer = null;

        function supToggleFilterPanel(forceOpen = null) {
            const willOpen = forceOpen === null ? !supFilterPanel.hasClass('is-open') : forceOpen;
            window.clearTimeout(supPanelTimer);

            if (willOpen) {
                supFilterPanel.addClass('is-visible');
                window.requestAnimationFrame(function() {
                    supFilterPanel.addClass('is-open');
                });
            } else {
                supFilterPanel.removeClass('is-open');
                supPanelTimer = window.setTimeout(function() {
                    supFilterPanel.removeClass('is-visible');
                }, 180);
            }

            supFilterPanel.attr('aria-hidden', willOpen ? 'false' : 'true');
            supFilterToggle.attr('aria-expanded', willOpen ? 'true' : 'false');
        }

        supFilterToggle.on('click', function() {
            supToggleFilterPanel();
        });

        $('#supFilterClose').on('click', function() {
            supToggleFilterPanel(false);
        });

        $('#supSearchInput').on('input', function() {
            const keyword = this.value;
            window.clearTimeout(supSearchTimer);
            supSearchTimer = window.setTimeout(function() {
                table.search(keyword).draw();
            }, 350);
        });

        $('#supPageLength').on('change', function() {
            table.page.len(Number(this.value)).draw();
        });

        $('#supFilterReset').on('click', function() {
            $('#supPageLength').val('50');
            $('#supSearchInput').val('');
            table.search('').page.len(50);
            table.ajax.reload();
        });
    });

    function pilih(id, nama) {
        $('#namasupplier').val(nama);
        $('#idsupplier').val(id);

        $('#modaldatasupplier').modal('hide');
    }

    function editData(id, nama, pic, email, telp, alamat) {
        $.getJSON('/supplier/pemakaian', { id: id }).done(function(response) {
            if (response.pemakaian && response.pemakaian.length > 0) {
                $('#editNamaSupplier').prop('readonly', true);
            } else {
                $('#editNamaSupplier').prop('readonly', false);
            }

            bukaEditSupplier(id, nama, pic, email, telp, alamat);
        }).fail(function() {
            Swal.fire('Kesalahan', 'Informasi pemakaian supplier gagal dimuat.', 'error');
        });
    }

    function bukaEditSupplier(id, nama, pic, email, telp, alamat) {
        $('#editIdSupplier').val(id);
        $('#editNamaSupplier').val(nama);
        $('#editNamaPic').val(pic);
        $('#editEmail').val(email);
        $('#editTelp').val(telp);
        $('#editAlamat').val(alamat);

        // Menampilkan modal edit
        $('#modalEditSupplier').modal('show');
        $('#modaldatasupplier').modal('hide');
    }

    function update() {
        var idSupplier = $('#editIdSupplier').val();
        var namaSupplier = $('#editNamaSupplier').val();
        var namaPic = $('#editNamaPic').val();
        var email = $('#editEmail').val();
        var telp = $('#editTelp').val();
        var alamat = $('#editAlamat').val();

        $.ajax({
            url: '<?= site_url('supplier/update') ?>',
            type: 'POST',
            data: {
                [csrfToken]: csrfHash,
                id_supplier: idSupplier,
                editNamaSupplier: namaSupplier,
                editNamaPic: namaPic,
                editEmail: email,
                editTelp: telp,
                editAlamat: alamat
            },
            dataType: 'json',
            success: function(response) {
                if (response.error) {
                    let err = response.error;

                    if (err.errNamaSupplier) {
                        $('#editNamaSupplier').addClass('is-invalid');
                        $('.errorEditNamaSupplier').html(err.errNamaSupplier);
                    }
                    if (err.errNamaPic) {
                        $('#editNamaPic').addClass('is-invalid');
                        $('.errorEditNamaPic').html(err.errNamaPic);
                    }
                    if (err.errEmail) {
                        $('#editEmail').addClass('is-invalid');
                        $('.errorEditEmail').html(err.errEmail);
                    }
                    if (err.errTelp) {
                        $('#editTelp').addClass('is-invalid');
                        $('.errorEditTelp').html(err.errTelp);
                    }
                    if (err.errAlamat) {
                        $('#editAlamat').addClass('is-invalid');
                        $('.errorEditAlamat').html(err.errAlamat);
                    }
                } else if (response.sukses) {
                    // Menampilkan pesan sukses dengan Swal.fire
                    Swal.fire({
                        icon: 'success',
                        title: 'Update Data',
                        text: response.sukses
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('#modalEditSupplier').modal('hide');
                            $('#datasupplier').DataTable().ajax.reload();
                            $('#modaldatasupplier').modal('show');
                        }
                    });
                }
            }
        });
    }

    function hapus(id, nama) {
        Swal.fire({
            title: 'Hapus Supplier?',
            html: `Yakin menghapus Data Supplier dengan nama <strong>${nama}</strong>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "post",
                    url: '<?= site_url('supplier/hapus') ?>',
                    data: {
                        [csrfToken]: csrfHash,
                        id: id
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.sukses) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Hapus data',
                                text: response.sukses
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $('#datasupplier').DataTable().ajax.reload();
                                    $('#modaldatasupplier').modal('show');
                                }
                            });
                        } else if (response.error) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                html: response.error
                            });
                            listDataSupplier();
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        alert(xhr.status + '\n' + thrownError);
                    }
                });
            }
        });
    }
</script>
<?= $this->endSection('isi') ?>
