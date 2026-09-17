<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Input Material Keluar
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>

<button type="button" class="btn btn-warning" onclick="location.href=('/materialkeluar/data')">
    <i class="fa fa-undo"></i> Kembali
</button>

<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>

<div class="row">
    <div class="col-lg-3">
        <div class="form-group">
            <label for="tglfaktur">Tanggal</label>
            <input type="date" name="tglfaktur" id="tglfaktur" class="form-control" value="<?= date('Y-m-d') ?>">
        </div>
    </div>
    <div class="col-lg-3">
        <div class="form-group">
            <label for="nofaktur">No. Transaksi</label>
            <input type="text" name="nofaktur" id="nofaktur" class="form-control" onchange="cekNoTransaksi()">
        </div>
    </div>
    <div class="col-lg-3">
        <div class="form-group">
            <label for="namasupplier">Cari Supplier</label>
            <div class="input-group mb-3 tre-inline-combobox" id="supplierCombobox">
                <input type="text" class="form-control" placeholder="Nama Supplier" name="namasupplier" id="namasupplier">
                <input type="hidden" name="idsupplier" id="idsupplier">
                <div class="input-group-append">
                    <button class="btn btn-outline-success" type="button" id="tombolTambahSupplier" title="Tambah Supplier">
                        <i class="fa fa-plus-square"></i>
                    </button>
                </div>
                <div class="tre-inline-combobox-menu" id="supplierComboboxMenu"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="form-group">
            <label for="gudang">Lokasi Gudang Asal</label>
            <div class="input-group mb-3">
                <select name="gudang" id="gudang" class="form-control">
                    <option selected value="">-- Pilih --</option>
                    <?php foreach ($datagudang as $gdg) : ?>
                        <option value="<?= $gdg['gdgid'] ?>" id="gudang"><?= $gdg['gdgnama'] ?></option>
                    <?php endforeach ?>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-2">
        <div class="form-group">
            <label for="kodematerial">Kode Material</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" name="kodematerial" id="kodematerial" disabled>
                <input type="hidden" name="materialid" id="materialid">
                <input type="hidden" name="idmat" id="idmat">
                <input type="hidden" name="materialkatid" id="materialkatid">
                <input type="hidden" name="materialsatid" id="materialsatid">
                <input type="hidden" name="gdgid" id="gdgid">
                <input type="hidden" name="idgudang" id="idgudang">
                <div class="input-group-append">
                    <button class="btn btn-outline-primary" type="button" id="tombolCariMaterial" disabled>
                        <i class="fa fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-2">
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
            <label for="keterangan">Keterangan</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" name="keterangan" id="keterangan" placeholder="Silahkan Isi Keterangan">
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
    const supplierOptions = <?= json_encode(array_map(static function ($row) {
                                return [
                                    'id' => (string) $row['supid'],
                                    'text' => $row['supnama'],
                                ];
                            }, $datasupplier ?? []), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    let supplierCombobox = null;

    function syncSupplierCombobox() {
        if (supplierCombobox && typeof supplierCombobox.sync === 'function') {
            supplierCombobox.sync();
        }
    }

    document.getElementById('gudang').addEventListener('change', function() {
        var selectedGudangId = this.value;
        document.getElementById('idgudang').value = selectedGudangId;

        var materialid = document.getElementById('materialid').value;

        var selectedOption = this.options[this.selectedIndex];
        var gdgid = selectedOption.value;
        document.getElementById("gdgid").value = gdgid;

        var tombolCariMaterial = document.getElementById('tombolCariMaterial');
        if (selectedGudangId !== "") {
            tombolCariMaterial.removeAttribute('disabled');
        } else {
            tombolCariMaterial.setAttribute('disabled', 'disabled');
        }

        if (materialid.length == 0) {
            return;
        }

        $.ajax({
            type: "post",
            url: '<?= site_url('materialkeluar/ambilDataMaterial') ?>',
            data: {
                [csrfToken]: csrfHash,
                materialid: materialid,
                idgudang: selectedGudangId
            },
            dataType: "json",
            success: function(response) {
                if (response.error) {
                    showBootstrapModal('Error', response.error, 'error');
                }

                if (response.sukses) {
                    let data = response.sukses;

                    $('#namamaterial').val(data.namamaterial);
                    $('#kodematerial').val(data.kodematerial);
                    $('#stok').val(data.stok);
                    $('#materialid').val(data.materialid);
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(xhr.status + '\n' + thrownError);
            }
        });
    });

    function kosong() {
        $('#kodematerial').val('');
        $('#materialid').val('');
        $('#idmat').val('');
        $('#stok').val('');
        $('#namamaterial').val('');
        $('#jml').val('1');
        $('#kodematerial').focus();

    }

    function cekNoTransaksi() {
        const nofaktur = $('#nofaktur').val().trim();

        if (nofaktur.length === 0) {
            return;
        }

        $.ajax({
            type: 'post',
            url: '<?= site_url('materialkeluar/cekNoDo') ?>',
            data: {
                [csrfToken]: csrfHash,
                nofaktur: nofaktur
            },
            dataType: 'json',
            success: function(response) {
                if (response.terpakai) {
                    showBootstrapModal('No. Transaksi sudah digunakan', response.pesan, 'warning')
                        .then(() => {
                            $('#nofaktur').val('').focus();
                        });
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(xhr.status + '\n' + thrownError);
            }
        });
    }

    function simpanItem() {
        syncSupplierCombobox();
        let nofaktur = $('#nofaktur').val();
        let tglfaktur = $('#tglfaktur').val();
        let idmat = $('#idmat').val();
        let materialid = $('#materialid').val();
        let kodematerial = $('#kodematerial').val();
        let namamaterial = $('#namamaterial').val();
        let materialkatid = $('#materialkatid').val();
        let materialsatid = $('#materialsatid').val();
        let idsupplier = $('#idsupplier').val();
        let stok = $('#stok').val();
        let satuan = $('#satuan').val();
        let jml = $('#jml').val();

        if (nofaktur.length == 0) {
            showBootstrapModal('Error', 'No Faktur harus di inputkan', 'error');
            kosong();
        } else if (kodematerial.length == 0) {
            showBootstrapModal('Error', 'Kode Material harus di inputkan', 'error');
            kosong();
        } else {
            $.ajax({
                type: "post",
                url: '<?= site_url('materialkeluar/simpanItem') ?>',
                data: {
                    [csrfToken]: csrfHash,
                    nofaktur: nofaktur,
                    idmat: idmat,
                    materialid: materialid,
                    kodematerial: kodematerial,
                    tglfaktur: tglfaktur,
                    materialkatid: materialkatid,
                    materialsatid: materialsatid,
                    idsupplier: idsupplier,
                    namamaterial: namamaterial,
                    stok: stok,
                    satuan: satuan,
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
        let idgudang = $('#idgudang').val();
        let materialid = $('#materialid').val();
        let idmat = $('#idmat').val();
        if (kodematerial.length == 0) {
            showBootstrapModal('Error', 'Kode Material harus di inputkan', 'error');
            kosong();
        } else {
            $.ajax({
                type: "post",
                url: '<?= site_url('materialkeluar/ambilDataMaterial') ?>',
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
                    alert(xhr.status + '\n' + thrownError)
                }
            });
        }
    }

    function tampilDataTemp() {
        let faktur = $('#nofaktur').val();
        let idsupplier = $('#idsupplier').val();
        $.ajax({
            type: "post",
            url: '<?= site_url('materialkeluar/tampilDataTemp') ?>',
            data: {
                [csrfToken]: csrfHash,
                nofaktur: faktur,
                idsupplier: idsupplier
            },
            dataType: "json",
            beforeSend: function() {
                $('.tampilDataTemp').html("<i class='fa fa-spin fa-spinner'></i>");
            },
            success: function(response) {
                if (response.data) {
                    $('.tampilDataTemp').html(response.data);
                    $('#idsupplier').val(idsupplier);
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(xhr.status + '\n' + thrownError)
            }
        });
    }

    $(document).ready(function() {
        supplierCombobox = window.treInitInlineCombobox({
            box: '#supplierCombobox',
            input: '#namasupplier',
            hidden: '#idsupplier',
            menu: '#supplierComboboxMenu',
            options: supplierOptions
        });
        $(document).off('tre:supplierAdded.supplierCombobox').on('tre:supplierAdded.supplierCombobox', function(event, supplier) {
            if (supplierCombobox && typeof supplierCombobox.addOption === 'function') {
                supplierCombobox.addOption(supplier, true);
            }
        });

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

        $('#tombolCariMaterial').click(function(e) {
            e.preventDefault();
            $.ajax({
                url: '<?= site_url('materialkeluar/modalCariMaterial') ?>',
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
            syncSupplierCombobox();
            let nofaktur = $('#nofaktur').val();
            let idsupplier = $('#idsupplier').val();
            let idgudang = $('#idgudang').val();
            let tglfaktur = $('#tglfaktur').val();
            let idmat = $('#idmat').val();
            let keterangan = $('#keterangan').val();
            // let jml = $('#jml').val();

            if (nofaktur.length == 0) {
                showBootstrapModal({
                    title: 'Pesan',
                    icon: 'warning',
                    text: 'Maaf No. Transaksi tidak boleh kosong'
                })
            } else if (idsupplier.length == 0) {
                showBootstrapModal({
                    title: 'Pesan',
                    icon: 'warning',
                    text: 'Maaf data supplier tidak boleh kosong'
                })
            } else if (idgudang.length == 0) {
                showBootstrapModal({
                    title: 'Pesan',
                    icon: 'warning',
                    text: 'Maaf Gudang belum terpilih'
                })
            } else if (keterangan.length == 0) {
                showBootstrapModal({
                    title: 'Pesan',
                    icon: 'warning',
                    text: 'Maaf Kolom Keterangan belum terisi'
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
                            url: '<?= site_url('materialkeluar/selesaiTransaksi') ?>',
                            data: {
                                [csrfToken]: csrfHash,
                                nofaktur: nofaktur,
                                tglfaktur: tglfaktur,
                                idsupplier: idsupplier,
                                idgudang: idgudang,
                                idmat: idmat,
                                keterangan: keterangan,
                                // jml: $('#jml').val(),
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
