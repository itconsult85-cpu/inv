<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Input Produk Keluar
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
<div class="row">
    <div class="col-lg-3">
        <div class="form-group">
            <label for="tglfaktur">Tanggal</label>
            <input type="date" name="tglfaktur" id="tglfaktur" class="form-control" value="<?= date('Y-m-d') ?>">
        </div>
    </div>
    <div class="col-lg-3">
        <div class="form-group">
            <label for="nofaktur">No Surat Jalan</label>
            <input type="text" name="nofaktur" id="nofaktur" class="form-control">
        </div>
    </div>
    <div class="col-lg-3">
        <div class="form-group">
            <label for="nopo">No. PO</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" name="nopo" id="nopo" readonly>
                <div class="input-group-append">
                    <button class="btn btn-outline-primary" type="button" id="tombolCariPo">
                        <i class="fa fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="form-group">
            <label for="namapelanggan">Nama Pelanggan</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" name="namapelanggan" id="namapelanggan" readonly>
                <input type="hidden" class="form-control" name="idpelanggan" id="idpelanggan" readonly>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-2">
        <div class="form-group">
            <label for="kodebarang">Kode Produk</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" name="kodebarang" id="kodebarang" readonly>
                <input type="hidden" id="iddetail">
                <input type="hidden" id="idmaterial">
                <input type="hidden" id="idbarang">
                <input type="hidden" id="idgudang">
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
            <label for="stok">Stok</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" name="stok" id="stok" readonly>
            </div>
        </div>
    </div>
    <div class="col-lg-2">
        <div class="form-group">
            <label for="jml">Qty Kirim</label>
            <div class="input-group mb-3">
                <input type="number" class="form-control" name="jml" id="jml" value="1">
                <input type="hidden" class="form-control" name="detberat" id="detberat" readonly>
            </div>
        </div>
    </div>
    <div class="col-lg-2">
        <div class="form-group">
            <label for="gudang">Lokasi Gudang</label>
            <div class="input-group mb-3">
                <select name="gudang" id="gudang" class="form-control" disabled>
                    <option selected value="">-- Pilih --</option>
                    <?php foreach ($datagudang as $gdg) : ?>
                        <option value="<?= $gdg['gdgid'] ?>" id="gudang"><?= $gdg['gdgnama'] ?></option>
                    <?php endforeach ?>
                </select>
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
            </div>
        </div>
    </div>
</div>
<div class="row">
    <input type="hidden" class="form-control" name="detqty" id="detqty" readonly>
    <input type="hidden" class="form-control" name="kirim" id="kirim" readonly>
    <div class="col-lg-12 tampilDataTemp">

    </div>

</div>
<div class="row">
    <div class="col-lg-12 tampilDataTempKeluar">

    </div>
</div>
<div class="row justify-content-end">
    <button type="button" class="btn btn-sm btn-success" id="tombolSelesaiTransaksi">
        <i class="fa fa-arrow-circle-up"></i> Kirim Produk
    </button>
</div>
<div class="viewmodal" style="display: none;"></div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const shippingData = document.querySelectorAll("#shippingData tr");
        shippingData.forEach(row => {
            const kodebrg = row.getAttribute("data-kodebrg");
            const akanDikirim = parseInt(row.querySelector(".akanDikirim").textContent.replace(/\./g, '').replace(',', ''), 10);

            const productRow = document.querySelector(`#productData tr[data-id="${kodebrg}"]`);
            if (productRow) {
                const belumTerkirimCell = productRow.querySelector(".belumTerkirim");
                const kirimInput = productRow.querySelector(".kirim");

                let belumTerkirim = parseInt(belumTerkirimCell.textContent.replace(/\./g, '').replace(',', ''), 10);
                belumTerkirim -= akanDikirim;

                belumTerkirimCell.textContent = belumTerkirim.toLocaleString('de-DE') + ' Pcs';
                kirimInput.value = belumTerkirim;
            }
        });
    });
</script>
<script>
    let csrfToken = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';

    document.getElementById('gudang').addEventListener('change', function() {
        var selectedGudangId = this.value;
        document.getElementById('idgudang').value = selectedGudangId;

        var kodebarang = document.getElementById('kodebarang').value;

        if (kodebarang.length == 0) {
            return;
        }

        // Panggil AJAX untuk mengambil data barang
        $.ajax({
            type: "post",
            url: '<?= site_url('barangkeluar/ambilDataBarang') ?>',
            data: {
                [csrfToken]: csrfHash,
                kodebarang: kodebarang,
                idgudang: selectedGudangId,
                nofaktur: $('#nofaktur').val(),
                nopo: $('#nopo').val()
            },
            dataType: "json",
            success: function(response) {
                if (response.error) {
                    showBootstrapModal('Error', response.error, 'error');
                }

                if (response.sukses) {
                    let data = response.sukses;

                    $('#namabarang').val(data.namabarang);
                    $('#berat').val(data.berat);
                    $('#stok').val(data.stok);
                    $('#idbarang').val(data.idbarang); // Isi input hidden idbarang

                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(xhr.status + '\n' + thrownError);
            }
        });
    });

    function kosong() {
        // $('#nofaktur').val('');
        // $('#nopo').val('');
        $('#kirim').val('');
        $('#stok').val('');
        $('#detqty').val('');
        $('#kodebarang').val('');
        $('#namabarang').val('');
        $('#idmaterial').val('');
        // $('#namapelanggan').val('');
        // $('#idpelanggan').val('');
        $('#nopo').focus();
        $('#jml').val('1');
        $('#tombolSimpanItem').fadeIn();
    }

    function simpanItem() {
        let nofaktur = $('#nofaktur').val();
        let tglfaktur = $('#tglfaktur').val();
        let nopo = $('#nopo').val();
        let kodebarang = $('#kodebarang').val();
        let idpelanggan = $('#idpelanggan').val();
        let idbarang = $('#idbarang').val();
        let idmaterial = $('#idmaterial').val();
        let idgudang = $('#idgudang').val();
        let namabarang = $('#namabarang').val();
        let detberat = $('#detberat').val();
        let detqty = $('#detqty').val();
        let stok = $('#stok').val();
        let kirim = $('#kirim').val();
        let jml = $('#jml').val();

        if (nopo.length == 0) {
            showBootstrapModal('Error', 'No Surat Jalan dan No. PO tidak boleh kosong', 'error');
            kosong();
        } else {
            $.ajax({
                type: "post",
                url: '<?= site_url('barangkeluar/simpanItem') ?>',
                data: {
                    [csrfToken]: csrfHash,
                    nofaktur: nofaktur,
                    tglfaktur: tglfaktur,
                    nopo: nopo,
                    idpelanggan: idpelanggan,
                    kodebarang: kodebarang,
                    idbarang: idbarang,
                    idmaterial: idmaterial,
                    idgudang: idgudang,
                    namabarang: namabarang,
                    detberat: detberat,
                    detqty: detqty,
                    stok: stok,
                    kirim: kirim,
                    jml: jml,
                },
                dataType: "json",
                success: function(response) {
                    if (response.error) {
                        showBootstrapModal('Error', response.error, 'error');
                        // kosong();
                    }
                    if (response.error1) {
                        showBootstrapModal('Error', response.error1, 'error');
                        // kosong();
                    }
                    if (response.error2) {
                        showBootstrapModal('Error', response.error2, 'error');
                        // kosong();
                    }
                    if (response.error3) {
                        showBootstrapModal('Error', response.error3, 'error');
                        // kosong();
                    }
                    if (response.error4) {
                        showBootstrapModal('Error', response.error4, 'error');
                        // kosong();
                    }
                    if (response.sukses) {
                        showBootstrapModal('Berhasil', response.sukses, 'success');
                        if (response.stokSisa !== undefined) {
                            $('#stok').val(response.stokSisa);
                        }
                        tampilDataTemp();
                        tampilDataTempKeluar();
                        kosong();
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert(xhr.status + '\n' + thrownError)
                }
            });
        }
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
                    alert(xhr.status + '\n' + thrownError)
                }
            });
        }
    }

    function ambilDataBarang() {
        let kodebarang = $('#kodebarang').val();
        let idgudang = $('#idgudang').val();
        let idmaterial = $('#idmaterial').val();

        if (kodebarang.length == 0) {
            showBootstrapModal('Error', 'Kode Barang harus di inputkan', 'error');
            kosong();
        } else {
            $.ajax({
                type: "post",
                url: '<?= site_url('barangkeluar/ambilDataBarang') ?>',
                data: {
                    [csrfToken]: csrfHash,
                    kodebarang: kodebarang,
                    idgudang: idgudang,
                    idmaterial: idmaterial,
                    nofaktur: $('#nofaktur').val(),
                    nopo: $('#nopo').val(),
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
                        $('#stok').val(data.stok);
                        $('#idmaterial').val(data.idmaterial);
                        $('#jml').focus();
                        $('#idbarang').val(data.idbarang);
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert(xhr.status + '\n' + thrownError)
                }
            });
        }
    }

    function tampilDataTemp() {
        let nopo = $('#nopo').val();
        $.ajax({
            type: "post",
            url: '<?= site_url('barangkeluar/tampilDataTemp') ?>',
            data: {
                [csrfToken]: csrfHash,
                nopo: nopo
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
                alert(xhr.status + '\n' + thrownError)
            }
        });
    }

    $(document).ready(function() {
        tampilDataTemp();
        tampilDataTempKeluar();

        // $('#tglfaktur').change(function(e) {
        //     buatNofaktur();
        // });
        $('#tombolReload').click(function(e) {
            e.preventDefault();
            tampilDataTemp();
            tampilDataTempKeluar();
            kosong();
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
                    alert(xhr.status + '\n' + thrownError)
                }
            });
        });

        $('#nopo').keydown(function(e) {
            if (e.keyCode == 13) {
                e.preventDefault();
                ambilDataPo();
            }
        });

        $('#tombolSimpanItem').click(function(e) {
            e.preventDefault();
            simpanItem();
        });

        $('#tombolCariBarang').click(function(e) {
            e.preventDefault();
            $.ajax({
                url: '<?= site_url('barangkeluar/modalCariBarang') ?>',
                dataType: "json",
                success: function(response) {
                    if (response.data) {
                        $('.viewmodal').html(response.data).show();
                        $('#modalcaribarang').modal('show');
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
            let tglfaktur = $('#tglfaktur').val();
            let nopo = $('#nopo').val();
            let detqty = $('#detqty').val();
            let idpelanggan = $('#idpelanggan').val();
            let jml = $('#jml').val();
            let totalberatbarang = $('#totalberatbarang').val();
            let idgudang = $('#idgudang').val();
            let idmaterial = $('#idmaterial').val();

            if (nofaktur.length == 0) {
                showBootstrapModal({
                    title: 'Pesan',
                    icon: 'warning',
                    text: 'Maaf No Surat Jalan tidak boleh kosong'
                })
            } else if (nopo.length == 0) {
                showBootstrapModal({
                    title: 'Pesan',
                    icon: 'warning',
                    text: 'Maaf No. PO tidak boleh kosong'
                })
            } else if (idgudang.length == 0) {
                showBootstrapModal({
                    title: 'Pesan',
                    icon: 'warning',
                    text: 'Maaf Gudang Belum Terpilih'
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
                            url: '<?= site_url('barangkeluar/selesaiTransaksi') ?>',
                            data: {
                                [csrfToken]: csrfHash,
                                nofaktur: nofaktur,
                                tglfaktur: tglfaktur,
                                detqty: detqty,
                                nopo: nopo,
                                idpelanggan: idpelanggan,
                                jml: jml,
                                totalberatbarang: totalberatbarang,
                                idgudang: idgudang,
                                idmaterial: idmaterial,
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
        });
    });
</script>

<?= $this->endSection('isi') ?>
