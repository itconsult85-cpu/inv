<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Edit Produk Keluar
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>

<button type="button" class="btn btn-warning" onclick="location.href=('/barangkeluar/data')">
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
        <input type="hidden" id="nopo" value="<?= $nopo ?>">
        <td style="width: 20%;">No Surat Jalan</td>
        <td style="width: 2%;">:</td>
        <td style="width: 28%;">
            <div class="input-group input-group-sm">
                <input type="text" class="form-control" id="nofakturBaru" value="<?= $nofaktur ?>">
                <div class="input-group-append">
                    <button type="button" class="btn btn-outline-primary" id="tombolUbahNoSuratJalan" title="Simpan No Surat Jalan">
                        <i class="fa fa-save"></i>
                    </button>
                </div>
            </div>
        </td>
        <td rowspan="4" style="width: 50%; font-weight:bold; color:blue; font-size:20pt; text-align:center; vertical-align:middle;" id="lbTotalBerat">

        </td>
    </tr>
    <tr>
        <td>Tanggal</td>
        <td>:</td>
        <td><?= $tanggal ?></td>
        <input type="hidden" id="tanggal" value="<?= $tanggal ?>">
    </tr>
    <tr>
        <td>Pelanggan</td>
        <td>:</td>
        <td><?= $namapelanggan ?></td>
        <input type="hidden" id="idpelanggan" value="<?= $idpelanggan ?>">
    </tr>
    <tr>
        <td>Gudang Keluar</td>
        <td>:</td>
        <td><?= $namagudang ?></td>
    </tr>
    <!-- <tr>
        <td>Total Qty</td>
        <td>:</td>
        <td><?= $detqty ?></td>
    </tr>
    <tr>
        <td>Belum Terkirim</td>
        <td>:</td>
        <td></td>
    </tr> -->
</table>


<div class="row mt-4">
    <div class="col-lg-2">
        <div class="form-group">
            <label for="datapo">Pilih Produk</label>
            <div class="input-group mb-3">
                <select name="datapo" id="datapo" class="form-control">
                    <option selected value="">-- Pilih --</option>
                    <?php foreach ($datapo as $po) : ?>
                        <?php if ($po['detkurang'] != 0) : ?>
                            <option value="<?= $po['idbarang'] ?>" data-detpo="<?= esc($po['detnopo'], 'attr') ?>" data-detkodebrg="<?= $po['detkodebrg'] ?>" data-idbarang="<?= $po['idbarang'] ?>" data-namabrg="<?= $po['namabarang'] ?>" data-idgudang="<?= $po['gudang'] ?>" data-detberat="<?= $po['detberat'] ?>" data-detqty="<?= $po['detqty'] ?>" data-detkurang="<?= $po['detkurang'] ?>" data-stok="<?= $po['stok'] ?>" data-material="<?= $po['material'] ?>">
                                <?= $po['detnopo'] ?> - <?= $po['detkodebrg'] ?>
                            </option>
                        <?php endif ?>
                    <?php endforeach ?>
                </select>
            </div>
        </div>
    </div>
    <div class="col-lg-2">
        <div class="form-group">
            <label for="namabarang">Nama Produk</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" name="namabarang" id="namabarang" readonly>
                <input type="hidden" id="kodebarang">
                <input type="hidden" id="iddetail">
                <input type="hidden" id="idbarang">
                <input type="hidden" id="idgudang">
                <input type="hidden" id="idmaterial">
            </div>
        </div>
    </div>
    <div class="col-lg-2">
        <div class="form-group">
            <label for="detkurang">Belum Terkirim</label>
            <div class="input-group mb-3">
                <input type="number" class="form-control" name="detkurang" id="detkurang" readonly>
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
                <input type="hidden" class="form-control" name="berat" id="berat" readonly>
                <input type="hidden" class="form-control" name="detqty" id="detqty" readonly>
                <input type="hidden" class="form-control" name="stok" id="stok" readonly>
                <input type="hidden" class="form-control" name="kirim" id="kirim" readonly>
                <input type="hidden" class="form-control" name="detjmlLama" id="detjmlLama" readonly>
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
<!-- <div class="row mt-4">
    <div class="col-lg-12">
        <div id="debug-info">
        </div>
    </div>
</div> -->

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
        $('#kirim').val('');
        $('#idbarang').val('');
        $('#idgudang').val('');
        $('#iddetail').val('');
        $('#datapo').val('');
        $('#stok').val('');
        $('#berat').val('');
        $('#detkurang').val('');
        $('#detqty').val('');
        $('#namabarang').val('');
        $('#jml').val('1');
        $('#kodebarang').focus();
    }

    function ambilDataPo() {
        let nopo = $('#nopo').val();
        if (nopo.length == 0) {
            showBootstrapModal('Error', 'No Po harus di inputkan', 'error');
            kosong();
        } else {
            $.ajax({
                type: "post",
                url: '<?= site_url('barangkeluar/ambilDataPo') ?>',
                data: {
                    [csrfToken]: csrfHash,
                    nopo: nopo
                },
                dataType: "json",
                success: function(response) {
                    if (response.error) {
                        showBootstrapModal('Error', response.error, 'error');
                        kosong();
                    }

                    if (response.sukses) {
                        let data = response.sukses;
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
        let nopo = $('#nopo').val();
        $.ajax({
            type: "post",
            url: '<?= site_url('barangkeluar/tampilDataDetail') ?>',
            data: {
                [csrfToken]: csrfHash,
                nofaktur: faktur,
                nopo: nopo,
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

    function tampilDataTempKeluar() {
        let faktur = $('#nofaktur').val();
        $.ajax({
            type: "post",
            url: '<?= site_url('barangkeluar/tampilDataTempKeluar') ?>',
            data: {
                [csrfToken]: csrfHash,
                nofaktur: faktur,
            },
            dataType: "json",
            beforeSend: function() {
                $('.tampilDataTempKeluar').html("<i class='fa fa-spin fa-spinner'></i>");
            },
            success: function(response) {
                if (response.data) {
                    $('.tampilDataTempKeluar').html(response.data);

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
            url: '<?= site_url('barangkeluar/ambilTotalBerat') ?>',
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
        let nopo = $('#nopo').val();
        let nofaktur = $('#nofaktur').val();
        let kodebarang = $('#kodebarang').val();
        let idbarang = $('#idbarang').val();
        let idgudang = $('#idgudang').val();
        let idmaterial = $('#idmaterial').val();
        let namabarang = $('#namabarang').val();
        let berat = $('#berat').val();
        let idpelanggan = $('#idpelanggan').val();
        let detkurang = $('#detkurang').val();
        let detqty = $('#detqty').val();
        let detjml = $('#detjmlLama').val();
        let stok = $('#stok').val();
        let jml = $('#jml').val();
        let tanggal = $('#tanggal').val();

        console.log("Tanggal: ", tanggal);

        if (kodebarang.length == 0) {
            showBootstrapModal('Error', 'Kode Produk harus di inputkan', 'error');
            kosong();
        } else {
            $.ajax({
                type: "post",
                url: '<?= site_url('barangkeluar/simpanItemDetail') ?>',
                data: {
                    [csrfToken]: csrfHash,
                    nopo: nopo,
                    nofaktur: nofaktur,
                    kodebarang: kodebarang,
                    idbarang: idbarang,
                    idgudang: idgudang,
                    idpelanggan: idpelanggan,
                    idmaterial: idmaterial,
                    namabarang: namabarang,
                    berat: berat,
                    detkurang: detkurang,
                    detqty: detqty,
                    detjml: detjml,
                    stok: stok,
                    jml: jml,
                    tanggal: tanggal,
                },
                dataType: "json",
                success: function(response) {
                    if (response.error) {
                        showBootstrapModal('Error', response.error, 'error');
                        kosong();
                    }
                    if (response.error1) {
                        showBootstrapModal('Error', response.error1, 'error');
                    }
                    if (response.error2) {
                        showBootstrapModal('Error', response.error2, 'error');
                    }
                    if (response.error3) {
                        showBootstrapModal('Error', response.error3, 'error');
                    }
                    if (response.sukses) {
                        showBootstrapModal({
                            title: 'Berhasil',
                            text: response.sukses,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.reload();
                            }
                        });
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    showBootstrapModal('Error', xhr.status + '\n' + thrownError, 'error')
                }
            });
        }
    }

    $(document).ready(function() {
        ambilTotalBerat();
        tampilDataDetail();

        $('#datapo').on('change', function() {
            var selectedOption = $(this).find(':selected');
            var detkodebrg = selectedOption.data('detkodebrg');
            var detpo = selectedOption.data('detpo');
            var idbarang = selectedOption.data('idbarang');
            var idgudang = selectedOption.data('idgudang');
            var idmaterial = selectedOption.data('material');
            var namabrg = selectedOption.data('namabrg');
            var detberat = selectedOption.data('detberat');
            var detkurang = selectedOption.data('detkurang');
            var stok = selectedOption.data('stok');
            var detqty = selectedOption.data('detqty');
            $('#nopo').val(detpo);
            $('#kodebarang').val(detkodebrg);
            $('#idbarang').val(idbarang);
            $('#idgudang').val(idgudang);
            $('#idmaterial').val(idmaterial);
            $('#namabarang').val(namabrg);
            $('#berat').val(detberat);
            $('#detkurang').val(detkurang);
            $('#stok').val(stok);
            $('#detqty').val(detqty);
        });

        $('#tombolSimpanItem').click(function(e) {
            e.preventDefault();
            simpanItem();
        });

        $('#tombolCariPo').click(function(e) {
            e.preventDefault();
            $.ajax({
                url: '<?= site_url('barangkeluar/modalData') ?>',
                dataType: "json",
                success: function(response) {
                    if (response.data) {
                        $('.viewmodal').html(response.data).show();
                        $('#modaldatapo').modal('show');
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    showBootstrapModal('Error', xhr.status + '\n' + thrownError, 'error')
                }
            });
        });

        $('#tombolUbahNoSuratJalan').click(function(e) {
            e.preventDefault();
            let fakturLama = $('#nofaktur').val();
            let fakturBaru = $('#nofakturBaru').val().trim();

            if (fakturBaru === '') {
                showBootstrapModal('Error', 'No Surat Jalan tidak boleh kosong', 'error');
                return;
            }
            if (fakturBaru === fakturLama) {
                return;
            }

            showBootstrapModal({
                title: 'Ubah No Surat Jalan?',
                text: 'Dari "' + fakturLama + '" menjadi "' + fakturBaru + '"',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, ubah',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }
                $.ajax({
                    type: "post",
                    url: '<?= site_url('barangkeluar/ubah-no-surat-jalan') ?>',
                    data: {
                        [csrfToken]: csrfHash,
                        nofaktur_lama: fakturLama,
                        nofaktur_baru: fakturBaru,
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.error) {
                            showBootstrapModal('Error', response.error, 'error');
                        } else if (response.sukses) {
                            showBootstrapModal({
                                title: 'Berhasil',
                                text: response.sukses,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                // URL edit ini pakai hash dari No Surat Jalan LAMA,
                                // jadi setelah berubah nama, kembali ke daftar aja.
                                window.location.href = '/barangkeluar/data';
                            });
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        showBootstrapModal('Error', xhr.status + '\n' + thrownError, 'error');
                    }
                });
            });
        });

        $('#tombolEditItem').click(function(e) {
            e.preventDefault();
            $.ajax({
                type: "post",
                url: '<?= site_url('barangkeluar/editItem') ?>',
                data: {
                    [csrfToken]: csrfHash,
                    iddetail: $('#iddetail').val(),
                    nopo: $('#nopo').val(),
                    kodebarang: $('#kodebarang').val(),
                    detkurang: $('#detkurang').val(),
                    stok: $('#stok').val(),
                    detqty: $('#detqty').val(),
                    detjml: $('#detjmlLama').val(),
                    jml: $('#jml').val(),
                    tanggal: $('#tanggal').val(),
                },
                dataType: "json",
                success: function(response) {
                    // hasildebug
                    // $('#debug-info').html('<pre>' + JSON.stringify(response.data.debug, null, 2) + '</pre>');

                    if (response.error || response.error1 || response.error2 || response.error3) {
                        let errorMessage = response.error || response.error1 || response.error2 || response.error3;
                        showBootstrapModal('Error', errorMessage, 'error');
                    } else if (response.sukses) {
                        showBootstrapModal({
                            title: 'Berhasil',
                            text: response.sukses,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                tampilDataDetail();
                                ambilTotalBerat();
                                $('#kodebarang').prop('readonly', false);
                                $('#tombolCariBarang').prop('disabled', false);
                                $('#tombolSimpanItem').fadeIn();
                                $('#tombolEditItem').fadeOut();
                                $('#tombolBatal').fadeOut();
                                $('#datapo').prop('disabled', false);
                                window.location.reload();
                            }
                        });
                    } else {
                        // Example of displaying debug info in a pre-defined element
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    showBootstrapModal('Error', xhr.status + '\n' + thrownError, 'error');
                }
            });
        });
    });
</script>
<?= $this->endSection('isi') ?>
