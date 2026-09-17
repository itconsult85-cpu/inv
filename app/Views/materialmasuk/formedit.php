<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Edit Material Masuk
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>

<button type="button" class="btn btn-warning" onclick="location.href=('/materialmasuk/data')">
    <i class="fa fa-undo"></i> Kembali
</button>

<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<style>
    table#datadetail tbody tr:hover {
        cursor: pointer;
        background-color: red;
        color: #fff;
    }
</style>
<table class="table table-striped table-sm">
    <tr>
        <input type="hidden" id="nofaktur" value="<?= $nofaktur ?>">
        <td style="width: 20%;">No. Invoice</td>
        <td style="width: 2%;">:</td>
        <td style="width: 28%;">
            <div class="input-group input-group-sm">
                <input type="text" class="form-control" id="no_invoice" value="<?= esc($no_invoice) ?>" placeholder="Belum ada invoice">
                <div class="input-group-append">
                    <button type="button" class="btn btn-success" id="tombolSimpanInvoice" title="Simpan No. Invoice">
                        <i class="fa fa-save"></i>
                    </button>
                </div>
            </div>
        </td>
        <td rowspan="5" style="width: 50%; font-weight:bold; color:blue; font-size:20pt; text-align:center; vertical-align:middle;" id="lbTotalBerat">

        </td>
    </tr>
    <tr>
        <td>No Surat Jalan</td>
        <td>:</td>
        <td><?= esc($no_do) ?></td>
    </tr>
    <tr>
        <td>Tanggal</td>
        <td>:</td>
        <td><?= $tanggal ?></td>
        <input type="hidden" id="tglfaktur" value="<?= $tanggal ?>">
    </tr>
    <tr>
        <td>Supplier</td>
        <td>:</td>
        <td><?= $namasupplier ?></td>
        <input type="hidden" id="idsupplier" value="<?= $idsupplier ?>">
    </tr>
    <tr>
        <td>Gudang</td>
        <td>:</td>
        <td><?= $namagudang ?></td>
        <input type="hidden" id="idgudang" value="<?= $idgudang ?>">
    </tr>
</table>

<div class="row mt-4">
    <div class="col-lg-2">
        <div class="form-group">
            <label for="kodematerial">Kode Material</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" name="kodematerial" id="kodematerial" disabled>
                <input type="hidden" id="iddetail">
                <input type="hidden" id="materialid">
                <input type="hidden" id="idmat">
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
    <div class="col-lg-3">
        <div class="form-group">
            <label for="stok">Stok (KG)</label>
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
                <button type="button" style="display: none;" class="btn btn-primary" title="Edit Item" id="tombolEditItem">
                    <i class="fa fa-edit"></i>
                </button>&nbsp;
                <button type="button" style="display:none;" class="btn btn-default" title="Batalkan" id="tombolBatal">
                    <i class="fa fa-sync-alt"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12 tampilDataDetail">

    </div>
</div>
<div class="viewmodal" style="display: none;"></div>
<script>
    let csrfToken = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';

    function kosong() {
        $('#kodematerial').val('');
        $('#materialid').val('');
        $('#idmat').val('');
        $('#stok').val('');
        $('#namamaterial').val('');
        $('#jml').val('1');
        $('#kodematerial').focus();
    }

    function ambilDataMaterial() {
        let kodematerial = $('#kodematerial').val();
        let idgudang = $('#idgudang').val();
        let materialid = $('#materialid').val();
        let idmat = $('#idmat').val();
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
                    idgudang: idgudang,
                    materialid: materialid,
                    idmat: idmat,
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
                        $('#materialid').val(data.materialid);
                        $('#idmat').val(data.idmat);
                        $('#jml').focus();
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    showBootstrapModal('Error', xhr.status + '\n' + thrownError, 'error')
                }
            });
        }
    }

    function tampilDataDetail() {
        let faktur = $('#nofaktur').val();
        $.ajax({
            type: "post",
            url: '<?= site_url('materialmasuk/tampilDataDetail') ?>',
            data: {
                [csrfToken]: csrfHash,
                nofaktur: faktur
            },
            dataType: "json",
            beforeSend: function() {
                $('.tampilDataDetail').html("<i class='fa fa-spin fa-spinner'></i>");
            },
            success: function(response) {
                if (response.data) {
                    $('.tampilDataDetail').html(response.data);
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                showBootstrapModal('Error', xhr.status + '\n' + thrownError, 'error')
            }
        });
    }

    function ambilTotalBerat() {
        let nofaktur = $('#nofaktur').val();
        $.ajax({
            type: "post",
            url: '<?= site_url('materialmasuk/ambilTotalBerat') ?>',
            data: {
                [csrfToken]: csrfHash,
                nofaktur: nofaktur
            },
            dataType: "json",
            success: function(response) {
                $('#lbTotalBerat').html(response.totalberat);
            },
            error: function(xhr, ajaxOptions, thrownError) {
                showBootstrapModal('Error', xhr.status + '\n' + thrownError, 'error')
            }
        });
    }

    function simpanItem() {
        let nofaktur = $('#nofaktur').val();
        let tglfaktur = $('#tglfaktur').val();
        let idsupplier = $('#idsupplier').val();
        let materialid = $('#materialid').val();
        let idmat = $('#idmat').val();
        let idgudang = $('#idgudang').val();
        let kodematerial = $('#kodematerial').val();
        let namamaterial = $('#namamaterial').val();
        let stok = $('#stok').val();
        let jml = $('#jml').val();

        if (kodematerial.length == 0) {
            showBootstrapModal('Error', 'Kode Material harus di inputkan', 'error');
            kosong();
        } else {
            $.ajax({
                type: "post",
                url: '<?= site_url('materialmasuk/simpanItemDetail') ?>',
                data: {
                    [csrfToken]: csrfHash,
                    nofaktur: nofaktur,
                    tglfaktur: tglfaktur,
                    idsupplier: idsupplier,
                    materialid: materialid,
                    idmat: idmat,
                    kodematerial: kodematerial,
                    namamaterial: namamaterial,
                    idgudang: idgudang,
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
                        tampilDataDetail();
                        ambilTotalBerat();
                        kosong();
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    showBootstrapModal('Error', xhr.status + '\n' + thrownError, 'error')
                }
            });
        }
    }

    function simpanInvoice() {
        $.ajax({
            type: "post",
            url: '<?= site_url('materialmasuk/updateInvoice') ?>',
            data: {
                [csrfToken]: csrfHash,
                faktur: $('#nofaktur').val(),
                nofaktur: $('#no_invoice').val().trim()
            },
            dataType: "json",
            success: function(response) {
                if (response.error) {
                    showBootstrapModal('Error', response.error, 'error');
                } else if (response.sukses) {
                    showBootstrapModal('Berhasil', response.sukses, 'success');
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                showBootstrapModal('Error', xhr.status + '\n' + thrownError, 'error')
            }
        });
    }

    $(document).ready(function() {
        ambilTotalBerat();
        tampilDataDetail();

        $('#kodematerial').on('change', function() {
            let materialid = $(this).find('option:selected').data('materialid');
            $('#materialid').val(materialid);
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

        $('#tombolSimpanInvoice').click(function(e) {
            e.preventDefault();
            simpanInvoice();
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
                    showBootstrapModal('Error', xhr.status + '\n' + thrownError, 'error')
                }
            });
        });

        $('#tombolEditItem').click(function(e) {
            e.preventDefault();
            $.ajax({
                type: "post",
                url: '<?= site_url('materialmasuk/editItem') ?>',
                data: {
                    [csrfToken]: csrfHash,
                    iddetail: $('#iddetail').val(),
                    materialid: $('#materialid').val(),
                    idmat: $('#idmat').val(),
                    jml: $('#jml').val()
                },
                dataType: "json",
                success: function(response) {
                    if (response.sukses) {
                        showBootstrapModal({
                            'icon': 'success',
                            'title': 'Berhasil',
                            'text': response.sukses
                        });
                        tampilDataDetail();
                        ambilTotalBerat();
                        kosong();
                        $('#kodematerial').prop('readonly', false);
                        $('#tombolCariMaterial').prop('disabled', false);
                        $('#tombolSimpanItem').fadeIn();
                        $('#tombolEditItem').fadeOut();
                        $('#tombolBatal').fadeOut();
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    showBootstrapModal('Error', xhr.status + '\n' + thrownError, 'error')
                }
            });
        });
    });
</script>
<?= $this->endSection('isi') ?>
