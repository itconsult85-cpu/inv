<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Edit Produk Masuk
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>

<button type="button" class="btn btn-warning" onclick="location.href=('/barangmasuk/data')">
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
        <td style="width: 20%;">No PO</td>
        <td style="width: 2%;">:</td>
        <td style="width: 28%;"><?= $nofaktur ?></td>
        <td rowspan="4" style="width: 50%; font-weight:bold; color:blue; font-size:20pt; text-align:center; vertical-align:middle;" id="lbTotalBerat">
        </td>
    </tr>
    <tr>
        <td>Tanggal</td>
        <td>:</td>
        <td><?= $tanggal ?></td>
        <input type="hidden" value="<?= $tanggal ?>" id="tglfaktur">
    </tr>
    <tr>
        <td>Supplier</td>
        <td>:</td>
        <td><?= $namasupplier ?></td>
        <input type="hidden" value="<?= $idsupplier ?>" id="idsupplier">
    </tr>
    <tr>
        <td>Gudang</td>
        <td>:</td>
        <td><?= $gudangnama ?></td>
        <input type="hidden" value="<?= $gdgid ?>" id="gdgid">
    </tr>
</table>

<div class="row mt-4">
    <div class="col-lg-2">
        <div class="form-group">
            <label for="kodebarang">Kode Produk</label>
            <div class="input-group mb-3 tre-inline-combobox tre-inline-combobox-solo" id="produkCombobox">
                <input type="text" class="form-control" name="kodebarang" id="kodebarang" autocomplete="off">
                <input type="hidden" name="kodebarang_pilih" id="kodebarang_pilih">
                <input type="hidden" name="idmaterial" id="idmaterial">
                <input type="hidden" name="idgudang" id="idgudang">
                <input type="hidden" name="iddetail" id="iddetail">
                <input type="hidden" name="idbarang" id="idbarang" class="idbarang">
                <div class="tre-inline-combobox-menu" id="produkComboboxMenu"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-2">
        <div class="form-group">
            <label for="namabarang">Nama Produk</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" name="namabarang" id="namabarang" readonly>
            </div>
        </div>
    </div>
    <div class="col-lg-2">
        <div class="form-group">
            <label for="berat">Berat/Ukuran</label>
            <div class="input-group mb-3">
                <input type="number" class="form-control" name="berat" id="berat" readonly>
            </div>
        </div>
    </div>
    <div class="col-lg-2">
        <div class="form-group">
            <label for="stok1">Stok</label>
            <div class="input-group mb-3">
                <input type="number" class="form-control" name="stok1" id="stok1" readonly>
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
                </button>
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
    const produkOptions = <?= json_encode(array_map(static function ($row) {
                                $kode = (string) $row['brgkode'];
                                $nama = (string) $row['brgnama'];
                                return [
                                    'id' => $kode,
                                    'text' => $kode . ' - ' . $nama,
                                    'value' => $kode,
                                ];
                            }, $databarang ?? []), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    let produkCombobox = null;

    function syncProdukCombobox() {
        if (produkCombobox && typeof produkCombobox.sync === 'function') {
            produkCombobox.sync();
        }
    }

    function kosong() {
        $('#kodebarang').val('');
        $('#kodebarang_pilih').val('');
        $('#stok1').val('');
        $('#berat').val('');
        $('#namabarang').val('');
        $('#jml').val('1');
        $('#kodebarang').focus();

    }

    function ambilDataBarang() {
        syncProdukCombobox();
        let kodebarang = $('#kodebarang').val();
        let idgudang = $('#idgudang').val();
        let idbarang = $('#idbarang').val();
        let stok = $('#stok1').val();
        if (kodebarang.length == 0) {
            showBootstrapModal('Error', 'Kode Barang harus di inputkan', 'error');
            kosong();
        } else {
            $.ajax({
                type: "post",
                url: '<?= site_url('barangmasuk/ambilDataBarang') ?>',
                data: {
                    [csrfToken]: csrfHash,
                    kodebarang: kodebarang,
                    idgudang: idgudang,
                    idbarang: idbarang,
                    stok: stok,
                },
                dataType: "json",
                success: function(response) {
                    if (response.error) {
                        showBootstrapModal('Error', response.error, 'error');
                        kosong();
                    }

                    if (response.sukses) {
                        let data = response.sukses;

                        $('#namabarang').val(data.namabarang);
                        $('#berat').val(data.berat);
                        $('#stok1').val(data.stok);
                        $('#idmaterial').val(data.idmaterial);
                        $('#jml').focus();
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert(xhr.status + '\n' + thrownError)
                }
            });
        }
    }

    function tampilDataDetail() {
        let faktur = $('#nofaktur').val();
        let iddetail = $('#iddetail').val();
        let idbarang = $('#idbarang').val();
        $.ajax({
            type: "post",
            url: '<?= site_url('barangmasuk/tampilDataDetail') ?>',
            data: {
                [csrfToken]: csrfHash,
                nofaktur: faktur,
                iddetail: iddetail,
                idbarang: idbarang,
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
                alert(xhr.status + '\n' + thrownError)
            }
        });
    }

    function ambilTotalBerat() {
        let nofaktur = $('#nofaktur').val();
        $.ajax({
            type: "post",
            url: '<?= site_url('barangmasuk/ambilTotalBerat') ?>',
            data: {
                [csrfToken]: csrfHash,
                nofaktur: nofaktur
            },
            dataType: "json",
            success: function(response) {
                $('#lbTotalBerat').html(response.totalberat);
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(xhr.status + '\n' + thrownError)
            }
        });
    }

    function simpanItem() {
        syncProdukCombobox();
        let nofaktur = $('#nofaktur').val();
        let tglfaktur = $('#tglfaktur').val();
        let kodebarang = $('#kodebarang').val();
        let idgudang = $('#idgudang').val();
        let gdgid = $('#gdgid').val();
        let namabarang = $('#namabarang').val();
        let idmaterial = $('#idmaterial').val();
        let idsupplier = $('#idsupplier').val();
        let idbarang = $('#idbarang').val();
        let berat = $('#berat').val();
        let stok = $('#stok1').val();
        let jml = $('#jml').val();

        if (kodebarang.length == 0) {
            showBootstrapModal('Error', 'Kode Barang harus di inputkan', 'error');
            kosong();
        } else {
            $.ajax({
                type: "post",
                url: '<?= site_url('barangmasuk/simpanItemDetail') ?>',
                data: {
                    [csrfToken]: csrfHash,
                    nofaktur: nofaktur,
                    tglfaktur: tglfaktur,
                    kodebarang: kodebarang,
                    idbarang: idbarang,
                    idgudang: idgudang,
                    idsupplier: idsupplier,
                    gdgid: gdgid,
                    namabarang: namabarang,
                    idmaterial: idmaterial,
                    idsupplier: idsupplier,
                    berat: berat,
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
                        $('#kodebarang').prop('disabled', false);
                        $('#tombolSimpanItem').fadeIn();
                        $('#tombolEditItem').fadeOut();
                        $('#tombolBatal').fadeOut();

                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert(xhr.status + '\n' + thrownError)
                }
            });
        }
    }

    $(document).ready(function() {
        ambilTotalBerat();
        tampilDataDetail();

        $('#tombolReload').click(function(e) {
            e.preventDefault();
            kosong();
            tampilDataDetail();
        });

        $('#tombolSimpanItem').click(function(e) {
            e.preventDefault();
            simpanItem();
            tampilDataDetail();
        });

        produkCombobox = window.treInitInlineCombobox({
            box: '#produkCombobox',
            input: '#kodebarang',
            hidden: '#kodebarang_pilih',
            menu: '#produkComboboxMenu',
            options: produkOptions,
            onSelect: function() {
                ambilDataBarang();
            }
        });

        $('#kodebarang').keydown(function(e) {
            if (e.keyCode == 13) {
                e.preventDefault();
                syncProdukCombobox();
                ambilDataBarang();
            }
        });

        $('#tombolEditItem').click(function(e) {
            e.preventDefault();

            var gdgid = $('#gdgid').val();
            var stok = $('#stok1').val();
            var jml = $('#jml').val();
            var namabarang = $('#namabarang').val();
            if (kodebarang.length == 0) {
                showBootstrapModal('Error', 'Kode Barang harus di inputkan', 'error');
                kosong();
            } else {
                $.ajax({
                    type: "post",
                    url: '<?= site_url('barangmasuk/editItem') ?>',
                    data: {
                        [csrfToken]: csrfHash,
                        iddetail: $('#iddetail').val(),
                        idbarang: $('#idbarang').val(),
                        idgudang: $('#idgudang').val(),
                        kodebarang: $('#kodebarang').val(),
                        jml: $('#jml').val()
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.error) {
                            showBootstrapModal('Error', response.error, 'error');
                            kosong();
                        }
                        if (response.sukses) {
                            showBootstrapModal({
                                'icon': 'success',
                                'title': 'Berhasil',
                                'text': response.sukses
                            });
                            tampilDataDetail();
                            ambilTotalBerat();
                            kosong();
                            $('#kodebarang').prop('disabled', false);
                            $('#tombolSimpanItem').fadeIn();
                            $('#tombolEditItem').fadeOut();
                            $('#tombolBatal').fadeOut();
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        alert(xhr.status + '\n' + thrownError)
                    }
                });
            }
        });
    });
</script>
<?= $this->endSection('isi') ?>