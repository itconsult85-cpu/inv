<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Managemen Data Pelanggan
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>
<button type="button" class="btn btn-primary" id="btntambahpelanggan">
    <i class="fa fa-plus-circle"></i> Tambah Data Pelanggan
</button>
<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<style>
    .pel-toolbar { align-items: flex-start; display: flex; justify-content: flex-end; margin-bottom: 1rem; position: relative; z-index: 30; }
    .pel-filter-anchor { align-items: flex-end; display: flex; flex-direction: column; position: relative; width: min(100%, 32rem); }
    .pel-search-shell { align-items: center; background: #fff; border: 1px solid #eef1f7; border-radius: 999px; box-shadow: 0 3px 10px rgba(15, 23, 42, .025); display: flex; gap: .65rem; min-height: 3.4rem; padding: .35rem .45rem .35rem 1.25rem; width: 100%; }
    .pel-search-shell > i { color: #6b7280; font-size: 1.1rem; }
    .pel-search-input { background: transparent; border: 0; box-shadow: none; color: #4b5563; flex: 1 1 auto; font-size: .95rem; min-width: 0; outline: 0; }
    .pel-search-input:focus { box-shadow: none; outline: 0; }
    .pel-filter-toggle { align-items: center; background: #16869a; border: 0; border-radius: 999px; color: #fff; display: inline-flex; flex: 0 0 2.65rem; height: 2.65rem; justify-content: center; width: 2.65rem; }
    .pel-filter-toggle:hover, .pel-filter-toggle:focus { background: #126f7f; color: #fff; outline: 0; }
    .pel-filter-panel { background: #fff; border: 1px solid #edf1f5; border-radius: 22px; box-shadow: 0 18px 42px rgba(15, 23, 42, .1); display: none; max-width: min(46rem, calc(100vw - 4rem)); opacity: 0; overflow: hidden; pointer-events: none; position: absolute; right: 0; top: calc(100% + .75rem); transform: translateY(-.45rem) scale(.985); transform-origin: top right; transition: opacity .18s ease, transform .18s ease; width: 26rem; }
    .pel-filter-panel.is-visible { display: block; }
    .pel-filter-panel.is-open { opacity: 1; pointer-events: auto; transform: translateY(0) scale(1); }
    .pel-filter-header { align-items: center; display: flex; justify-content: space-between; padding: 1.2rem 1.35rem .9rem; }
    .pel-filter-title { color: #111827; font-size: 1.2rem; font-weight: 800; margin: 0; }
    .pel-filter-close { align-items: center; background: #fff; border: 0; border-radius: 999px; box-shadow: 0 8px 22px rgba(15, 23, 42, .12); color: #111827; display: inline-flex; height: 2.4rem; justify-content: center; width: 2.4rem; }
    .pel-filter-section { border-top: 1px solid #edf1f5; padding: 1.05rem 1.35rem; }
    .pel-filter-label { color: #718096; font-size: .9rem; font-weight: 800; margin-bottom: .75rem; }
    .pel-filter-field-row, .pel-filter-action-row { display: flex; flex-wrap: wrap; gap: .75rem; }
    .pel-filter-select { background: #fff; border: 1px solid #e5eaf1; border-radius: 999px; color: #111827; min-height: 2.55rem; min-width: 11rem; padding: .45rem .85rem; }
    .pel-filter-reset { background: #eef2f7; border: 0; border-radius: 999px; color: #4b5563; font-weight: 800; min-height: 2.55rem; padding: .45rem 1.2rem; }
    @media (max-width: 768px) {
        .pel-toolbar { justify-content: stretch; }
        .pel-filter-anchor, .pel-search-shell, .pel-filter-panel { max-width: none; width: 100%; }
        .pel-filter-panel { left: 0; right: auto; }
        .pel-filter-select, .pel-filter-reset { width: 100%; }
    }
</style>
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<script src="<?= base_url() ?>/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

<div class="pel-toolbar">
    <div class="pel-filter-anchor">
        <div class="pel-search-shell">
            <i class="fas fa-search"></i>
            <input type="search" id="pelSearchInput" class="pel-search-input" placeholder="Search anything..." aria-label="Search anything">
            <button type="button" class="pel-filter-toggle" id="pelFilterToggle" title="Buka filter" aria-controls="pelFilterPanel" aria-expanded="false">
                <i class="fas fa-sliders-h"></i>
            </button>
        </div>

        <section class="pel-filter-panel" id="pelFilterPanel" aria-hidden="true">
            <div class="pel-filter-header">
                <h3 class="pel-filter-title">Filter</h3>
                <button type="button" class="pel-filter-close" id="pelFilterClose" title="Tutup filter">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="pel-filter-section">
                <div class="pel-filter-label">Show Data</div>
                <div class="pel-filter-field-row">
                    <select id="pelPageLength" class="pel-filter-select" aria-label="Show data">
                        <option value="10">10 entries</option>
                        <option value="25">25 entries</option>
                        <option value="50" selected>50 entries</option>
                        <option value="100">100 entries</option>
                    </select>
                </div>
            </div>

            <div class="pel-filter-section">
                <div class="pel-filter-action-row">
                    <button type="button" class="pel-filter-reset" id="pelFilterReset">Reset</button>
                </div>
            </div>
        </section>
    </div>
</div>
<table id="datapelanggan" class="table table-bordered table-striped" style="width: 100%;">
    <thead>
        <tr>
            <th style="width: 5%;">No</th>
            <th>Nama Pelanggan</th>
            <th>PIC</th>
            <th>Email</th>
            <th>Alamat</th>
            <th>No Telp / Handphone</th>
            <th>Fax</th>
            <th>To</th>
            <th>Gudang</th>
            <th style="width: 20%;">Aksi</th>
        </tr>
    </thead>
    <tbody>

    </tbody>
</table>
<!-- Modal Edit Pelanggan -->
<div class="modal fade" id="modalEditPelanggan" tabindex="-1" aria-labelledby="modalEditPelangganLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditPelangganLabel">Edit Pelanggan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formEditPelanggan">
                    <input type="hidden" name="id_pelanggan" id="editIdPelanggan">
                    <div class="form-group">
                        <label for="editNamaPelanggan">Nama Pelanggan</label>
                        <input type="text" class="form-control" name="editNamaPelanggan" id="editNamaPelanggan">
                        <span class="invalid-feedback erroreditNamaPelanggan" id="erroreditNamaPelanggan"></span>
                    </div>
                    <div class="form-group">
                        <label for="editNamaPic">Nama PIC</label>
                        <input type="text" class="form-control" name="editNamaPic" id="editNamaPic">
                        <span class="invalid-feedback errorEditNamaPic"></span>
                    </div>
                    <div class="form-group">
                        <label for="editEmail">Email</label>
                        <input type="email" class="form-control" name="editEmail" id="editEmail">
                        <span class="invalid-feedback errorEditEmail"></span>
                    </div>
                    <div class="form-group">
                        <label for="editAlamat">Alamat</label>
                        <textarea class="form-control" name="editAlamat" id="editAlamat" rows="3"></textarea>
                        <span class="invalid-feedback errorEditAlamat"></span>
                    </div>
                    <div class="form-group">
                        <label for="editTelp">No Telp / Handphone</label>
                        <input type="text" class="form-control" name="editTelp" id="editTelp">
                        <span class="invalid-feedback errorEditTelp" id="errorEditTelp"></span>
                    </div>
                    <div class="form-group">
                        <label for="editFax">Fax <small class="text-muted">(opsional, kosongkan kalau tidak ada)</small></label>
                        <input type="text" class="form-control" name="editFax" id="editFax">
                        <span class="invalid-feedback errorEditFax"></span>
                    </div>
                    <div class="form-group">
                        <label for="editTo">To / Bagian Penerima <small class="text-muted">(opsional, kosongkan kalau tidak ada)</small></label>
                        <input type="text" class="form-control" name="editTo" id="editTo" placeholder="Contoh: Bag. Keuangan">
                        <span class="invalid-feedback errorEditTo"></span>
                    </div>
                    <div class="form-group">
                        <label for="editGudang">Gudang</label>

                        <select class="form-control" name="editGudang" id="editGudang">
                            <option value="">-- Pilih Gudang --</option>

                            <?php foreach ($gudang as $item): ?>
                                <option value="<?= esc($item['gdgid']) ?>">
                                    <?= esc($item['gdgnama']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <span class="invalid-feedback errorEditGudang"></span>
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
        // listDataPelanggan();
        table = $('#datapelanggan').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            searching: true,
            searchDelay: 500,
            stateSave: true,
            stateDuration: -1,
            pageLength: 10,
            dom: "<'row'<'col-sm-12'tr>><'row align-items-center mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            ajax: '<?= site_url('pelanggan/listDataView') ?>',
            order: [
                [1, 'asc']
            ],
            columns: [{
                    data: 'nomor',
                    orderable: false,
                    className: 'text-center'
                },
                {
                    data: 'pelnama',
                    className: 'text-center'
                },
                {
                    data: 'pelpic',
                    className: 'text-center'
                },
                {
                    data: 'pelemail',
                    className: 'text-center'
                },
                {
                    data: 'pelalamat',
                    className: 'text-left'
                },
                {
                    data: 'peltelp',
                    className: 'text-center'
                },
                {
                    data: 'pelfax',
                    className: 'text-center'
                },
                {
                    data: 'pelto',
                    className: 'text-center'
                },
                {
                    data: 'gdgnama',
                    className: 'text-center'
                },
                {
                    data: 'aksi',
                    className: 'text-center',
                    orderable: false
                },
            ]
        });
        $('#pelSearchInput').val(table.search());
        $('#btntambahpelanggan').click(function(e) {
            e.preventDefault();
            $.ajax({
                url: '<?= site_url('pelanggan/formtambah') ?>',
                dataType: "json",
                success: function(response) {
                    if (response.data) {
                        $('.viewmodal').html(response.data).show();
                        $('#modaltambahpelanggan').modal('show');
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert(xhr.status + '\n' + thrownError)
                }
            });
        });

        const pelFilterPanel = $('#pelFilterPanel');
        const pelFilterToggle = $('#pelFilterToggle');
        let pelPanelTimer = null;
        let pelSearchTimer = null;

        function pelToggleFilterPanel(forceOpen = null) {
            const willOpen = forceOpen === null ? !pelFilterPanel.hasClass('is-open') : forceOpen;
            window.clearTimeout(pelPanelTimer);

            if (willOpen) {
                pelFilterPanel.addClass('is-visible');
                window.requestAnimationFrame(function() {
                    pelFilterPanel.addClass('is-open');
                });
            } else {
                pelFilterPanel.removeClass('is-open');
                pelPanelTimer = window.setTimeout(function() {
                    pelFilterPanel.removeClass('is-visible');
                }, 180);
            }

            pelFilterPanel.attr('aria-hidden', willOpen ? 'false' : 'true');
            pelFilterToggle.attr('aria-expanded', willOpen ? 'true' : 'false');
        }

        pelFilterToggle.on('click', function() {
            pelToggleFilterPanel();
        });

        $('#pelFilterClose').on('click', function() {
            pelToggleFilterPanel(false);
        });

        $('#pelSearchInput').on('input', function() {
            const keyword = this.value;
            window.clearTimeout(pelSearchTimer);
            pelSearchTimer = window.setTimeout(function() {
                table.search(keyword).draw();
            }, 350);
        });

        $('#pelPageLength').on('change', function() {
            table.page.len(Number(this.value)).draw();
        });

        $('#pelFilterReset').on('click', function() {
            $('#pelPageLength').val('50');
            $('#pelSearchInput').val('');
            table.search('').page.len(50);
            table.ajax.reload();
        });
    });

    function editData(id, nama, pic, email, alamat, telp, gdgid, fax, to) {
        $.getJSON('/pelanggan/pemakaian', { id: id }).done(function(response) {
            const dipakai = response.pemakaian && response.pemakaian.length > 0;

            if (!dipakai) {
                bukaEditPelanggan(id, nama, pic, email, alamat, telp, gdgid, fax, to);
                return;
            }

            const daftar = response.pemakaian
                .map(item => `<li><strong>${item.label}</strong> (${item.jumlah} data)</li>`)
                .join('');
            showBootstrapModal({
                title: 'Pelanggan Sedang Digunakan',
                html: `<p>Pelanggan ini sedang dipakai di data lain. Datanya masih aman diedit.</p><ul style="text-align:left; margin:12px auto 0; width:fit-content;">${daftar}</ul>`,
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Lanjut Edit',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    bukaEditPelanggan(id, nama, pic, email, alamat, telp, gdgid, fax, to);
                }
            });
        }).fail(function() {
            showBootstrapModal('Kesalahan', 'Informasi pemakaian pelanggan gagal dimuat.', 'error');
        });
    }

    function bukaEditPelanggan(id, nama, pic, email, alamat, telp, gdgid, fax, to) {
        $('#editIdPelanggan').val(id);
        $('#editNamaPelanggan').val(nama);
        $('#editNamaPic').val(pic === '-' ? '' : pic);
        $('#editEmail').val(email === '-' ? '' : email);
        $('#editAlamat').val(alamat === '-' ? '' : alamat);
        $('#editTelp').val(telp);
        $('#editFax').val(fax === '-' ? '' : fax);
        $('#editTo').val(to === '-' ? '' : to);
        $('#editGudang').val(gdgid || '');

        $('#editNamaPelanggan').removeClass('is-invalid');
        $('#editNamaPic').removeClass('is-invalid');
        $('#editEmail').removeClass('is-invalid');
        $('#editAlamat').removeClass('is-invalid');
        $('#editTelp').removeClass('is-invalid');
        $('#editFax').removeClass('is-invalid');
        $('#editTo').removeClass('is-invalid');
        $('#editGudang').removeClass('is-invalid');

        $('.erroreditNamaPelanggan').html('');
        $('.errorEditNamaPic').html('');
        $('.errorEditEmail').html('');
        $('.errorEditAlamat').html('');
        $('.errorEditTelp').html('');
        $('.errorEditFax').html('');
        $('.errorEditTo').html('');
        $('.errorEditGudang').html('');

        $('#modalEditPelanggan').modal('show');
    }

    function update() {
        var idPelanggan = $('#editIdPelanggan').val();
        var namaPelanggan = $('#editNamaPelanggan').val();
        var namaPic = $('#editNamaPic').val();
        var email = $('#editEmail').val();
        var alamat = $('#editAlamat').val();
        var telp = $('#editTelp').val();
        var fax = $('#editFax').val();
        var to = $('#editTo').val();
        var gudang = $('#editGudang').val();

        $('#editNamaPelanggan').removeClass('is-invalid');
        $('#editNamaPic').removeClass('is-invalid');
        $('#editEmail').removeClass('is-invalid');
        $('#editAlamat').removeClass('is-invalid');
        $('#editTelp').removeClass('is-invalid');
        $('#editFax').removeClass('is-invalid');
        $('#editTo').removeClass('is-invalid');
        $('#editGudang').removeClass('is-invalid');

        $('.erroreditNamaPelanggan').html('');
        $('.errorEditNamaPic').html('');
        $('.errorEditEmail').html('');
        $('.errorEditAlamat').html('');
        $('.errorEditTelp').html('');
        $('.errorEditFax').html('');
        $('.errorEditTo').html('');
        $('.errorEditGudang').html('');

        $.ajax({
            url: '<?= site_url('pelanggan/update') ?>',
            type: 'POST',
            data: {
                [csrfToken]: csrfHash,
                id_pelanggan: idPelanggan,
                editNamaPelanggan: namaPelanggan,
                editNamaPic: namaPic,
                editEmail: email,
                editAlamat: alamat,
                editTelp: telp,
                editFax: fax,
                editTo: to,
                editGudang: gudang,
            },
            dataType: 'json',
            success: function(response) {
                if (response.error) {
                    let err = response.error;

                    if (err.errNamaPelanggan) {
                        $('#editNamaPelanggan').addClass('is-invalid');
                        $('.erroreditNamaPelanggan').html(err.errNamaPelanggan);
                    }

                    if (err.errTelp) {
                        $('#editTelp').addClass('is-invalid');
                        $('.errorEditTelp').html(err.errTelp);
                    }

                    if (err.errNamaPic) {
                        $('#editNamaPic').addClass('is-invalid');
                        $('.errorEditNamaPic').html(err.errNamaPic);
                    }

                    if (err.errEmail) {
                        $('#editEmail').addClass('is-invalid');
                        $('.errorEditEmail').html(err.errEmail);
                    }

                    if (err.errAlamat) {
                        $('#editAlamat').addClass('is-invalid');
                        $('.errorEditAlamat').html(err.errAlamat);
                    }

                    if (err.errFax) {
                        $('#editFax').addClass('is-invalid');
                        $('.errorEditFax').html(err.errFax);
                    }

                    if (err.errTo) {
                        $('#editTo').addClass('is-invalid');
                        $('.errorEditTo').html(err.errTo);
                    }

                    if (err.errGudang) {
                        $('#editGudang').addClass('is-invalid');
                        $('.errorEditGudang').html(err.errGudang);
                    }
                } else if (response.sukses) {
                    showBootstrapModal({
                        icon: 'success',
                        title: 'Update Data',
                        text: response.sukses
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('#modalEditPelanggan').modal('hide');
                            $('#datapelanggan').DataTable().ajax.reload();
                        }
                    });
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(
                    'Status: ' + xhr.status +
                    '\nError: ' + thrownError +
                    '\n\n' + xhr.responseText
                );
            }
        });
    }

    function hapus(id, nama) {
        showBootstrapModal({
            title: 'Hapus Pelanggan ?',
            html: `Yakin menghapus Data Pelanggan dengan nama <strong>${nama}</strong> ?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus !'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "post",
                    url: '<?= site_url('pelanggan/hapus') ?>',
                    data: {
                        [csrfToken]: csrfHash,
                        id: id
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.sukses) {
                            showBootstrapModal({
                                icon: 'success',
                                title: 'Hapus data',
                                text: response.sukses
                            }).then(() => {
                                $('#datapelanggan').DataTable().ajax.reload();
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
                        alert(xhr.status + '\n' + thrownError)
                    }
                });
            }
        })
    }
</script>
<?= $this->endSection('isi') ?>
