<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Edit Permintaan Transfer
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>

<button type="button" class="btn btn-warning" onclick="location.href=('/permintaanBarangKirim/datakirim')">
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

    #permintaanDisplay {
        display: flex;
        align-items: center;
    }

    #permintaanInput {
        width: auto;
        margin-right: 8px;
        display: none;
        /* Ensure it is hidden by default */
    }

    #permintaanDisplay button {
        margin-left: 8px;
    }
</style>
<table class="table table-striped table-sm">
    <tr>
        <input type="hidden" id="permintaan" value="<?= $permintaan ?>">
        <input type="hidden" id="idpermintaan" value="<?= $idpermintaan ?>">
        <td style="width: 20%;">No. Surat Jalan</td>
        <td style="width: 2%;">:</td>
        <td style="width: auto;" id="permintaanDisplay">
            <span id="permintaanText"><?= $permintaan ?></span>
            <?php if (\App\Libraries\AccessControl::can('produk.transfer.edit')) :  ?>
                <input type="text" id="permintaanInput" class="form-control" value="<?= $permintaan ?>" style="display: none;">
                <button type="button" id="editPermintaanBtn" class="btn btn-sm btn-primary ml-2">Edit</button>
                <button type="button" id="savePermintaanBtn" class="btn btn-sm btn-success ml-2" style="display: none;">Simpan</button>
                <button type="button" id="cancelPermintaanBtn" class="btn btn-sm btn-danger ml-2" style="display: none;">Batal</button>
            <?php endif ?>
        </td>
        <td rowspan="7" style="width: 50%; font-weight:bold; color:blue; font-size:20pt; text-align:center; vertical-align:middle;" id="lbTotalQty">
            <!-- <td rowspan="3" style="width: 50%; font-weight:bold; color:blue; font-size:20pt; text-align:center; vertical-align:middle;" id="lbTotalHarga"> -->

        </td>
    </tr>
    <tr>
        <td>Tanggal Permintaan</td>
        <td>:</td>
        <td><?= $tanggal ?>
            <input type="hidden" id="tglpermintaan" value="<?= $tanggal ?>">
        </td>
    </tr>
    <tr>
        <td>User</td>
        <td>:</td>
        <td><?= $namauser ?>
            <input type="hidden" id="iduser" value="<?= $iduser ?>">
        </td>
    </tr>
    <tr>
        <td>Jenis Pengiriman</td>
        <td>:</td>
        <td><?= esc($jenisPengiriman) ?></td>
    </tr>
    <tr>
        <td>PIC Pengirim</td>
        <td>:</td>
        <td><?= esc($picPengirim) ?></td>
    </tr>
    <tr>
        <td>Nominal</td>
        <td>:</td>
        <td>Rp <?= number_format((float) $nominal, 2, ',', '.') ?></td>
    </tr>
</table>

<input type="hidden" id="iddetail">
<div class="row mt-4">
    <div class="col-lg-3">
        <div class="form-group">
            <label for="kodebarang">Kode Produk</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" name="kodebarang" id="kodebarang" readonly>
                <div class="input-group-append">
                    <button class="btn btn-outline-primary" type="button" id="tombolCariBarang">
                        <i class="fa fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="form-group">
            <label for="namabarang">Nama Produk</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" name="namabarang" id="namabarang" readonly>
            </div>
        </div>
    </div>
    <div class="col-lg-2">
        <div class="form-group">
            <label for="stok">Stok (Pcs)</label>
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
        $('#kodebarang').val('');
        $('#stok').val('');
        $('#namabarang').val('');
        $('#iddetail').val('');
        $('#jml').val('1');
        $('#kodebarang').focus();
    }

    function ambilDataBarang() {
        let kodebarang = $('#kodebarang').val();
        let harga = $('#harga').val();
        if (kodebarang.length == 0) {
            showBootstrapModal('Error', 'Kode Produk harus di inputkan', 'error');
            kosong();
        } else {
            $.ajax({
                type: "post",
                url: '<?= site_url('permintaanBarang/ambilDataBarang') ?>',
                data: {
                    [csrfToken]: csrfHash,
                    kodebarang: kodebarang,
                    harga: harga,
                },
                dataType: "json",
                success: function(response) {
                    if (response.error) {
                        showBootstrapModal('Error', response.error, 'error');
                        kosong();
                    }

                    if (response.sukses) {
                        let data = response.sukses;

                        $('#stok').val(data.stok);
                        $('#namabarang').val(data.namabarang);
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
        let permintaan = $('#permintaan').val();
        let idpermintaan = $('#idpermintaan').val();
        $.ajax({
            type: "post",
            url: '<?= site_url('permintaanBarang/tampilDataDetail') ?>',
            data: {
                [csrfToken]: csrfHash,
                permintaan: permintaan,
                idpermintaan: idpermintaan,
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

    function ambilTotalQty() {
        let idpermintaan = $('#idpermintaan').val();
        let permintaan = $('#permintaan').val();
        $.ajax({
            type: "post",
            url: '<?= site_url('permintaanBarang/ambilTotalQty') ?>',
            data: {
                [csrfToken]: csrfHash,
                idpermintaan: idpermintaan,
                permintaan: permintaan,
            },
            dataType: "json",
            success: function(response) {
                $('#lbTotalQty').html(response.totalqty);
            },
            error: function(xhr, ajaxOptions, thrownError) {
                showBootstrapModal('Error', xhr.status + '\n' + thrownError, 'error')
            }
        });
    }

    function simpanItem() {
        let permintaan = $('#permintaan').val();
        let tglpermintaan = $('#tglpermintaan').val();
        let iduser = $('#iduser').val();
        let kodebarang = $('#kodebarang').val();
        let namabarang = $('#namabarang').val();
        let jml = $('#jml').val();

        if (kodebarang.length == 0) {
            showBootstrapModal('Error', 'Kode Produk harus di inputkan', 'error');
            kosong();
        } else {
            $.ajax({
                type: "post",
                url: '<?= site_url('permintaanBarang/simpanItemDetail') ?>',
                data: {
                    [csrfToken]: csrfHash,
                    permintaan: permintaan,
                    tglpermintaan: tglpermintaan,
                    iduser: iduser,
                    kodebarang: kodebarang,
                    namabarang: namabarang,
                    jml: jml,
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
                        ambilTotalQty();
                        kosong();
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    showBootstrapModal('Error', xhr.status + '\n' + thrownError, 'error')
                }
            });
        }
    }

    $(document).ready(function() {
        ambilTotalQty();
        tampilDataDetail();

        $('#tombolSimpanItem').click(function(e) {
            e.preventDefault();
            ambilTotalQty();
            simpanItem();
        });

        $('#tombolCariBarang').click(function(e) {
            e.preventDefault();
            $.ajax({
                url: '<?= site_url('permintaanBarang/modalCariBarang') ?>',
                dataType: "json",
                success: function(response) {
                    if (response.data) {
                        $('.viewmodal').html(response.data).show();
                        $('#modalcaribarang').modal('show');
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    showBootstrapModal('Error', xhr.status + '\n' + thrownError, 'error')
                }
            });
        });

        $(document).on('click', '#editPermintaanBtn', function() {
            $('#permintaanText').hide();
            $('#permintaanInput').show();
            $('#editPermintaanBtn').hide();
            $('#savePermintaanBtn').show();
            $('#cancelPermintaanBtn').show();
        });

        $(document).on('click', '#cancelPermintaanBtn', function() {
            $('#permintaanInput').hide();
            $('#permintaanText').show();
            $('#permintaanInput').val($('#permintaan').val());
            $('#editPermintaanBtn').show();
            $('#savePermintaanBtn').hide();
            $('#cancelPermintaanBtn').hide();
        });

        $(document).on('click', '#savePermintaanBtn', function(e) {
            e.preventDefault();
            let newPermintaan = $('#permintaanInput').val();
            let originalPermintaan = $('#permintaan').val();
            $.ajax({
                type: "post",
                url: '<?= site_url('permintaanBarang/updatePermintaan') ?>',
                data: {
                    [csrfToken]: csrfHash,
                    newPermintaan: newPermintaan,
                    originalPermintaan: originalPermintaan,
                },
                dataType: "json",
                success: function(response) {
                    if (response.error) {
                        showBootstrapModal('Error', response.error, 'error');
                    } else {
                        showBootstrapModal('Berhasil', response.sukses, 'success').then(() => {});
                        $('#permintaan').val(newPermintaan);
                        $('#permintaanText').text(newPermintaan);
                        $('#permintaanInput').hide();
                        $('#permintaanText').show();
                        $('#editPermintaanBtn').show();
                        $('#savePermintaanBtn').hide();
                        $('#cancelPermintaanBtn').hide();
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    showBootstrapModal('Error', xhr.status + '\n' + thrownError, 'error');
                }
            });
        });

        $('#tombolEditItem').click(function(e) {
            e.preventDefault();
            $.ajax({
                type: "post",
                url: '<?= site_url('permintaanBarang/editItem') ?>',
                data: {
                    [csrfToken]: csrfHash,
                    iddetail: $('#iddetail').val(),
                    permintaan: $('#permintaan').val(),
                    iduser: $('#iduser').val(),
                    tglpermintaan: $('#tglpermintaan').val(),
                    kodebarang: $('#kodebarang').val(),
                    jml: $('#jml').val(),
                },
                dataType: "json",
                success: function(response) {
                    if (response.error) {
                        showBootstrapModal('Error', response.error, 'error');
                        tampilDataDetail();
                        ambilTotalQty();
                        kosong();
                        $('#kodebarang').prop('readonly', false);
                        $('#tombolCariBarang').prop('disabled', false);
                        $('#tombolSimpanItem').fadeIn();
                        $('#tombolEditItem').fadeOut();
                        $('#tombolBatal').fadeOut();
                    }
                    if (response.sukses) {
                        showBootstrapModal({
                            'icon': 'success',
                            'title': 'Berhasil',
                            'text': response.sukses
                        });
                        tampilDataDetail();
                        ambilTotalQty();
                        kosong();
                        $('#kodebarang').prop('readonly', false);
                        $('#tombolCariBarang').prop('disabled', false);
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
