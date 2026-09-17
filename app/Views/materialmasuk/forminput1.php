<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Input Material Masuk
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>

<button type="button" class="btn btn-warning" onclick="location.href=('/materialmasuk/data')">
    <i class="fa fa-undo"></i> Kembali
</button>

<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>

<div class="row">
    <div class="col-lg-4">
        <div class="form-group">
            <label for="tglfaktur">Tanggal</label>
            <input type="date" name="tglfaktur" id="tglfaktur" class="form-control" value="<?= date('Y-m-d') ?>">
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-group">
            <label for="nofaktur">No. Invoice</label>
            <input type="text" name="nofaktur" id="nofaktur" class="form-control">
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-group">
            <label for="namasupplier">Cari Supplier</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" placeholder="Nama Supplier" name="namasupplier" id="namasupplier" readonly>
                <input type="hidden" name="idsupplier" id="idsupplier">
                <div class="input-group-append">
                    <button class="btn btn-outline-primary" type="button" id="tombolCariSupplier" title="Cari Supplier">
                        <i class="fa fa-search"></i>
                    </button>
                    <?php if (\App\Libraries\AccessControl::can('master.supplier.create')) : ?>
                        <button class="btn btn-outline-success" type="button" id="tombolTambahSupplier" title="Tambah Supplier">
                            <i class="fa fa-plus-square"></i>
                        </button>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-3">
        <div class="form-group">
            <label for="kodematerial">Kode Material</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" name="kodematerial" id="kodematerial">
                <input type="hidden" name="idmaterial" id="idmaterial">
                <div class="input-group-append">
                    <button class="btn btn-outline-primary" type="button" id="tombolCariMaterial">
                        <i class="fa fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="form-group">
            <label for="namamaterial">Nama Material</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" name="namamaterial" id="namamaterial" readonly>
            </div>
        </div>
    </div>
    <div class="col-lg-2">
        <div class="form-group">
            <label for="stok">Stok</label>
            <div class="input-group mb-3">
                <input type="number" class="form-control" name="stok" id="stok" readonly>
            </div>
        </div>
    </div>
    <div class="col-lg-2">
        <div class="form-group">
            <label for="jml">Qty</label>
            <div class="input-group mb-3">
                <input type="number" class="form-control" name="jml" id="jml" value="1">
            </div>
        </div>
    </div>
    <div class="col-lg-2">
        <div class="form-group">
            <label>#</label>
            <div class="input-group mb-3">
                <button type="button" class="btn btn-success" title="Simpan Item" id="tombolSimpanItem">
                    <i class="fa fa-save"></i>
                </button>&nbsp;
                <button type="button" class="btn btn-sm btn-warning" title="Reload Data" id="tombolReload">
                    <i class="fa fa-sync-alt"></i>
                </button>&nbsp;
                <!-- <button type="button" class="btn btn-info" title="Selesai Transaksi" id="tombolSelesaiTransaksi">
                    Selesai Transaksi
                </button> -->
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12 tampilDataTemp">

    </div>
</div>
<div class="row justify-content-end">
    <button type="button" class="btn btn-sm btn-success" id="tombolSelesaiTransaksi">
        <i class="fa fa-save"></i> Selesai Transaksi
    </button>
</div>
<div class="viewmodal" style="display: none;"></div>

<script>
    let csrfToken = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';

    function kosong() {
        // $('#nofaktur').val('');
        $('#kodematerial').val('');
        $('#idmaterial').val('');
        $('#stok').val('');
        $('#namamaterial').val('');
        $('#jml').val('1');
        $('#kodematerial').focus();

    }

    function simpanItem() {
        let nofaktur = $('#nofaktur').val();
        let tglfaktur = $('#tglfaktur').val();
        let idsupplier = $('#idsupplier').val();
        let idmaterial = $('#idmaterial').val();
        let kodematerial = $('#kodematerial').val();
        let namamaterial = $('#namamaterial').val();
        let stok = $('#stok').val();
        let jml = $('#jml').val();

        if (nofaktur.length == 0) {
            showBootstrapModal('Error', 'No. Invoice harus diinputkan', 'error');
            kosong();
        } else if (kodematerial.length == 0) {
            showBootstrapModal('Error', 'Kode Material harus di inputkan', 'error');
            kosong();
        } else {
            $.ajax({
                type: "post",
                url: '<?= site_url('materialmasuk/simpanItem') ?>',
                data: {
                    [csrfToken]: csrfHash,
                    nofaktur: nofaktur,
                    tglfaktur: tglfaktur,
                    idsupplier: idsupplier,
                    idmaterial: idmaterial,
                    kodematerial: kodematerial,
                    namamaterial: namamaterial,
                    stok: stok,
                    jml: jml
                },
                dataType: "json",
                success: function(response) {
                    if (response.error) {
                        showBootstrapModal('Error', response.error, 'error');
                        kosong();
                    }
                    if (response.sukses) {
                        showBootstrapModal('Berhasil', response.sukses, 'success');
                        tampilDataTemp();
                        kosong();
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert(xhr.status + '\n' + thrownError)
                }
            });
        }
    }

    function ambilDataMaterial() {
        let kodematerial = $('#kodematerial').val();
        let idmaterial = $('#idmaterial').val();
        if (kodematerial.length == 0) {
            showBootstrapModal('Error', 'Kode Material harus di inputkan', 'error');
            kosong();
        } else {
            $.ajax({
                type: "post",
                url: '<?= site_url('materialmasuk/ambilDataMaterial') ?>',
                data: {
                    [csrfToken]: csrfHash,
                    kodematerial: kodematerial,
                    idmaterial: idmaterial
                },
                dataType: "json",
                success: function(response) {
                    if (response.error) {
                        showBootstrapModal('Error', response.error, 'error');
                        kosong();
                    }

                    if (response.sukses) {
                        let data = response.sukses;

                        $('#namamaterial').val(data.namamaterial);
                        $('#stok').val(data.stok);
                        $('#idmaterial').val(data.idmaterial);
                        $('#kodematerial').val(kodematerial);
                        $('#jml').focus();
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert(xhr.status + '\n' + thrownError)
                }
            });
        }
    }

    function tampilDataTemp() {
        let faktur = $('#nofaktur').val();
        $.ajax({
            type: "post",
            url: '<?= site_url('materialmasuk/tampilDataTemp') ?>',
            data: {
                [csrfToken]: csrfHash,
                nofaktur: faktur
            },
            dataType: "json",
            beforeSend: function() {
                $('.tampilDataTemp').html("<i class='fa fa-spin fa-spinner'></i>");
            },
            success: function(response) {
                if (response.data) {
                    $('.tampilDataTemp').html(response.data);
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(xhr.status + '\n' + thrownError)
            }
        });
    }

    $(document).ready(function() {
        tampilDataTemp();
        $('#tombolReload').click(function(e) {
            e.preventDefault();
            tampilDataTemp();
        });

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

        $('#tombolCariSupplier').click(function(e) {
            e.preventDefault();
            $.ajax({
                url: '<?= site_url('supplier/modalData') ?>',
                dataType: "json",
                success: function(response) {
                    if (response.data) {
                        $('.viewmodal').html(response.data).show();
                        $('#modaldatasupplier').modal('show');
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert(xhr.status + '\n' + thrownError)
                }
            });
        });

        $('#kodematerial').on('change', function() {
            let idmaterial = $(this).find('option:selected').data('idmaterial');
            $('#idmaterial').val(idmaterial);
        });

        $('#kodematerial').keydown(function(e) {
            if (e.keyCode == 13) {
                e.preventDefault();
                ambilDataMaterial();
            }
        });

        $('#tombolSimpanItem').click(function(e) {
            e.preventDefault();
            simpanItem();
        });

        $('#tombolCariMaterial').click(function(e) {
            e.preventDefault();
            $.ajax({
                url: '<?= site_url('materialmasuk/modalCariMaterial') ?>',
                dataType: "json",
                success: function(response) {
                    if (response.data) {
                        $('.viewmodal').html(response.data).show();
                        $('#modalcarimaterial').modal('show');
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert(xhr.status + '\n' + thrownError)
                }
            });
        });

        $('#tombolSelesaiTransaksi').click(function(e) {
            e.preventDefault();
            let nofaktur = $('#nofaktur').val();
            let idsupplier = $('#idsupplier').val();
            let jml = $('#jml').val();

            if (nofaktur.length == 0) {
                showBootstrapModal({
                    title: 'Pesan',
                    icon: 'warning',
                    text: 'Maaf No. Invoice tidak boleh kosong'
                })
            } else if (idsupplier.length == 0) {
                showBootstrapModal({
                    title: 'Pesan',
                    icon: 'warning',
                    text: 'Maaf data supplier tidak boleh kosong'
                })
            } else {
                showBootstrapModal({
                    title: 'Selesai Transaksi',
                    text: "Yakin transaksi ini di simpan ?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Simpan Data Transaksi !',
                    cancelButtonText: 'Tidak'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "post",
                            url: '<?= site_url('materialmasuk/selesaiTransaksi') ?>',
                            data: {
                                [csrfToken]: csrfHash,
                                nofaktur: nofaktur,
                                tglfaktur: $('#tglfaktur').val(),
                                jml: $('#jml').val(),
                                idsupplier: $('#idsupplier').val()
                            },
                            dataType: "json",
                            success: function(response) {
                                if (response.error) {
                                    showBootstrapModal({
                                        title: 'Error',
                                        icon: 'error',
                                        text: response.error
                                    });
                                }

                                if (response.sukses) {
                                    showBootstrapModal({
                                        title: 'Berhasil',
                                        icon: 'success',
                                        text: response.sukses
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            window.location.reload();
                                        }
                                    })
                                }
                            },
                            error: function(xhr, ajaxOptions, thrownError) {
                                alert(xhr.status + '\n' + thrownError)
                            }
                        });
                    }
                })
            }
        })
    });
</script>

<?= $this->endSection('isi') ?>
