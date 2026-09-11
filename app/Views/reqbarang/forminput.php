<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Input Antar Gudang
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>

<button type="button" class="btn btn-warning" onclick="location.href=('/permintaanBarangKirim/datakirim')">
    <i class="fa fa-undo"></i> Kembali
</button>

<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<div class="row">
    <div class="col-lg-3">
        <div class="form-group">
            <label for="tglpermintaan">Tanggal Permintaan Transfer</label>
            <input type="date" name="tglpermintaan" id="tglpermintaan" class="form-control" value="<?= date('Y-m-d') ?>">
        </div>
    </div>
    <div class="col-lg-3">
        <div class="form-group">
            <label for="permintaan">No. Surat Jalan</label>
            <input type="text" name="permintaan" id="permintaan" class="form-control">
        </div>
    </div>
    <div class="col-lg-3">
        <div class="form-group">
            <label for="gudang">Gudang Asal</label>
            <select name="gudang" id="gudang" class="form-control">
                <option value="">-- Pilih --</option>
                <?php foreach (($datagudang ?? []) as $gdg) : ?>
                    <option value="<?= $gdg['gdgid'] ?>"><?= esc($gdg['gdgnama']) ?></option>
                <?php endforeach ?>
            </select>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="form-group">
            <label>User</label>
            <input type="text" class="form-control" value="<?= esc($currentUser['usernama']) ?> (<?= esc($currentUser['userid']) ?>)" readonly>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-2">
        <div class="form-group">
            <label for="jenis_item">Jenis Item</label>
            <select id="jenis_item" class="form-control">
                <option value="produk">Produk</option>
                <option value="material">Material</option>
            </select>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="form-group">
            <label for="kodebarang" id="labelKodeItem">Kode Produk</label>
            <div class="input-group mb-3 tre-inline-combobox tre-inline-combobox-solo" id="itemCombobox">
                <input type="text" class="form-control" name="kodebarang" id="kodebarang" autocomplete="off">
                <input type="hidden" name="kodebarang_pilih" id="kodebarang_pilih">
                <input type="hidden" class="form-control" name="idbarang" id="idbarang">
                <input type="hidden" name="idmaterial" id="idmaterial">
                <input type="hidden" id="satuan_item" value="Pcs">
                <div class="tre-inline-combobox-menu" id="itemComboboxMenu"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="form-group">
            <label for="namabarang" id="labelNamaItem">Nama Produk</label>
            <div class="mb-3">
                <input type="text" class="form-control" name="namabarang" id="namabarang" readonly>
            </div>
        </div>
    </div>
    <div class="col-lg-2">
        <div class="form-group">
            <label for="stok">Stok</label>
            <div class="mb-3">
                <input type="number" class="form-control" name="stok" id="stok" readonly>
                <input type="hidden" class="form-control" name="harga" id="harga" readonly>
            </div>
        </div>
    </div>
    <div class="col-lg-1">
        <div class="form-group">
            <label for="jml">Qty</label>
            <div class="mb-3">
                <input type="number" class="form-control" name="jml" id="jml" value="1">
                <input type="hidden" class="form-control" name="berat" id="berat" readonly>
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
    const produkOptions = <?= json_encode(array_map(static function ($row) {
                                $kode = (string) $row['brgkode'];
                                $nama = (string) $row['brgnama'];
                                return [
                                    'id' => $kode,
                                    'text' => $kode . ' - ' . $nama,
                                    'value' => $kode,
                                ];
                            }, $databarang ?? []), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const materialOptions = <?= json_encode(array_map(static function ($row) {
                                $kode = (string) $row['matkode'];
                                $nama = (string) $row['matnama'];
                                return [
                                    'id' => (string) $row['matid'],
                                    'text' => $kode . ' - ' . $nama,
                                    'value' => $kode,
                                ];
                            }, $datamaterial ?? []), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const itemOptions = [];
    let itemCombobox = null;

    function refreshItemOptions() {
        const source = $('#jenis_item').val() === 'material' ? materialOptions : produkOptions;
        itemOptions.length = 0;
        source.forEach(function(option) {
            itemOptions.push(option);
        });
    }

    function normalizeComboboxValue(value) {
        return String(value || '').trim().toLowerCase();
    }

    function syncItemCombobox() {
        if (itemCombobox && typeof itemCombobox.sync === 'function') {
            itemCombobox.sync();
        }

        const keyword = normalizeComboboxValue($('#kodebarang').val());
        const match = itemOptions.find(function(option) {
            return normalizeComboboxValue(option.text) === keyword || normalizeComboboxValue(option.value) === keyword;
        });

        if (match) {
            $('#kodebarang').val(match.value || match.text);
            $('#kodebarang_pilih').val(match.id);
        }
    }

    function kosong() {
        $('#kodebarang').val('');
        $('#kodebarang_pilih').val('');
        $('#namabarang').val('');
        $('#idbarang').val('');
        $('#idmaterial').val('');
        $('#stok').val('');
        $('#jml').val('1');
        $('#kodebarang').focus();

    }

    function simpanItem() {
        let tglpermintaan = $('#tglpermintaan').val();
        let permintaan = $('#permintaan').val();
        let kodebarang = $('#kodebarang').val();
        let namabarang = $('#namabarang').val();
        let jml = $('#jml').val();
        let berat = $('#berat').val();
        let jenisItem = $('#jenis_item').val();
        let idmaterial = $('#idmaterial').val();
        let idgudang = $('#gudang').val();

        if (kodebarang.length == 0) {
            Swal.fire('Error', 'Kode item harus dipilih', 'error');
            kosong();
        } else if (!idgudang) {
            Swal.fire('Error', 'Gudang Asal harus dipilih', 'error');
        } else {
            $.ajax({
                type: "post",
                url: '<?= site_url('permintaanBarang/simpanItem') ?>',
                data: {
                    [csrfToken]: csrfHash,
                    tglpermintaan: tglpermintaan,
                    permintaan: permintaan,
                    kodebarang: kodebarang,
                    namabarang: namabarang,
                    jml: jml,
                    berat: berat,
                    jenis_item: jenisItem,
                    idmaterial: idmaterial,
                    idgudang: idgudang,
                },
                dataType: "json",
                success: function(response) {
                    if (response.error1) {
                        Swal.fire('Error', response.error1, 'error');
                        kosong();
                    }
                    if (response.error2) {
                        Swal.fire('Error', response.error2, 'error');
                        kosong();
                    }
                    if (response.sukses) {
                        Swal.fire('Berhasil', response.sukses, 'success');
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

    function ambilDataBarang() {
        syncItemCombobox();
        let kodebarang = $('#kodebarang').val();
        let idgudang = $('#gudang').val();
        if (kodebarang.length == 0) {
            Swal.fire('Error', 'Kode item harus dipilih', 'error');
            kosong();
        } else if (!idgudang) {
            Swal.fire('Error', 'Pilih Gudang Asal dulu', 'error');
        } else {
            $.ajax({
                type: "post",
                url: $('#jenis_item').val() === 'material'
                    ? "/permintaanBarang/ambilDataMaterial"
                    : "/permintaanBarang/ambilDataBarang",
                data: {
                    [csrfToken]: csrfHash,
                    kodebarang: kodebarang,
                    idgudang: idgudang,
                },
                dataType: "json",
                success: function(response) {
                    if (response.error) {
                        Swal.fire('Error', response.error, 'error');
                        kosong();
                    }

                    if (response.sukses) {
                        let data = response.sukses;

                        $('#berat').val(data.berat);
                        $('#stok').val(data.stok);
                        $('#namabarang').val(data.namabarang);
                        $('#idmaterial').val(data.idmaterial || '');
                        $('#satuan_item').val(data.satuan || ($('#jenis_item').val() === 'material' ? 'Kg' : 'Pcs'));
                        $('#jml').focus();
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert(xhr.status + '\n' + thrownError);
                }
            });
        }
    }

    function tampilDataTemp() {
        let permintaan = $('#permintaan').val();
        $.ajax({
            type: "post",
            url: '<?= site_url('permintaanBarang/tampilDataTemp') ?>',
            data: {
                [csrfToken]: csrfHash,
                permintaan: permintaan
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
        refreshItemOptions();
        itemCombobox = window.treInitInlineCombobox({
            box: '#itemCombobox',
            input: '#kodebarang',
            hidden: '#kodebarang_pilih',
            menu: '#itemComboboxMenu',
            options: itemOptions,
            onSelect: function() {
                ambilDataBarang();
            }
        });
        tampilDataTemp();

        $('#tombolReload').click(function(e) {
            e.preventDefault();
            tampilDataTemp();
            kosong();
        });

        $('#kodebarang').keydown(function(e) {
            if (e.keyCode == 13) {
                if (e.isDefaultPrevented()) {
                    return;
                }
                e.preventDefault();
                syncItemCombobox();
                ambilDataBarang();
            }
        });

        $('#gudang').change(function() {
            if ($('#kodebarang_pilih').val()) {
                ambilDataBarang();
            }
        });

        $('#tombolSimpanItem').click(function(e) {
            e.preventDefault();
            syncItemCombobox();
            simpanItem();
        });

        $('#jenis_item').change(function() {
            const material = $(this).val() === 'material';
            $('#labelKodeItem').text(material ? 'Kode Material' : 'Kode Produk');
            $('#labelNamaItem').text(material ? 'Nama Material' : 'Nama Produk');
            $('#satuan_item').val(material ? 'Kg' : 'Pcs');
            refreshItemOptions();
            kosong();
        });

        $('#tombolSelesaiTransaksi').click(function(e) {
            e.preventDefault();
            let permintaan = $('#permintaan').val();

            if (permintaan.length == 0) {
                Swal.fire({
                    title: 'Pesan',
                    icon: 'warning',
                    text: 'Maaf No Surat Jalan tidak boleh kosong'
                })
            } else {
                $.ajax({
                    type: "post",
                    url: '<?= site_url('permintaanBarang/selesaiTransaksi') ?>',
                    data: {
                        [csrfToken]: csrfHash,
                        permintaan: permintaan,
                        tglpermintaan: $('#tglpermintaan').val(),
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.error) {
                            Swal.fire({
                                title: 'Error',
                                icon: 'error',
                                text: response.error
                            });
                            return;
                        }

                        if (response.sukses) {
                            Swal.fire({
                                title: 'Berhasil',
                                icon: 'success',
                                text: response.sukses
                            }).then(() => {
                                window.location.reload();
                            });
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
