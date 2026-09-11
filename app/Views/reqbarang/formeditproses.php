<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Edit Antar Gudang
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
</style>
<table class="table table-striped table-sm">
    <tr>
        <input type="hidden" id="nofaktur" value="<?= $nofaktur ?>">
        <td>Tanggal</td>
        <td>:</td>
        <td><?= $tanggal ?></td>
        <input type="hidden" id="tanggal" value="<?= $tanggal ?>">
        <td rowspan="7" style="width: 50%; font-weight:bold; color:blue; font-size:20pt; text-align:center; vertical-align:middle;" id="lbTotalQty">
            Total : <?= number_format($totalQtyKirim, 0, ',', '.') ?> Pcs
        </td>
    </tr>
    <tr>
        <td>No Surat Jalan</td>
        <td>:</td>
        <td><?= $permintaan ?></td>
        <input type="hidden" id="permintaan" value="<?= $permintaan ?>">
    </tr>
    <tr>
        <td>User</td>
        <td>:</td>
        <td><?= $namauser ?></td>
        <input type="hidden" id="iduser" value="<?= $iduser ?>">
    </tr>
    <tr>
        <td>Gudang Keluar</td>
        <td>:</td>
        <td><?= $namagudang ?></td>
    </tr>
    <tr>
        <td>Jenis Pengiriman</td>
        <td>:</td>
        <td>
            <span id="jenisPengirimanText"><?= $jenisPengiriman !== '' ? esc($jenisPengiriman) : '-' ?></span>
            <input type="text" id="jenisPengirimanInput" class="form-control" style="display:none; max-width:260px;" maxlength="100" value="<?= esc($jenisPengiriman) ?>" placeholder="Contoh: Mobil Box">
        </td>
    </tr>
    <tr>
        <td>PIC Pengirim</td>
        <td>:</td>
        <td>
            <span id="picPengirimText"><?= $picPengirim !== '' ? esc($picPengirim) : '-' ?></span>
            <input type="text" id="picPengirimInput" class="form-control" style="display:none; max-width:260px;" maxlength="100" value="<?= esc($picPengirim) ?>" placeholder="Nama PIC yang mengirim">
        </td>
    </tr>
    <tr>
        <td>Nominal</td>
        <td>:</td>
        <td>
            <span id="nominalText">Rp <?= number_format((float) $nominal, 2, ',', '.') ?></span>
            <input type="number" id="nominalInput" class="form-control" style="display:none; max-width:260px;" min="0" step="0.01" value="<?= esc((string) $nominal) ?>">
        </td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td>
            <button type="button" class="btn btn-sm btn-primary" id="editHeaderBtn">Edit Info Pengiriman</button>
            <button type="button" class="btn btn-sm btn-success" id="saveHeaderBtn" style="display:none;">Simpan</button>
            <button type="button" class="btn btn-sm btn-danger" id="cancelHeaderBtn" style="display:none;">Batal</button>
        </td>
    </tr>
</table>


<p class="text-muted mb-2">Tambah item baru langsung ke pengiriman ini -- boleh item apapun (nggak harus udah ada di permintaan aslinya), langsung motong stok gudang asal begitu disimpan.</p>
<div class="row mt-2">
    <div class="col-lg-2">
        <div class="form-group">
            <label for="jenisItemProses">Jenis Item</label>
            <select id="jenisItemProses" class="form-control">
                <option value="produk">Produk</option>
                <option value="material">Material</option>
            </select>
        </div>
    </div>
    <div class="col-lg-2">
        <div class="form-group">
            <label for="kodebarang" id="labelKodeItemProses">Kode Produk</label>
            <div class="input-group mb-3 tre-inline-combobox tre-inline-combobox-solo" id="itemComboboxProses">
                <input type="text" class="form-control" id="kodebarang" autocomplete="off">
                <input type="hidden" id="kodebarang_pilih">
                <input type="hidden" id="idmaterial">
                <input type="hidden" id="iddetail">
                <div class="tre-inline-combobox-menu" id="itemComboboxProsesMenu"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-2">
        <div class="form-group">
            <label for="namabarang">Nama Item</label>
            <input type="text" class="form-control" id="namabarang" readonly>
            <input type="hidden" id="idbarang">
        </div>
    </div>
    <div class="col-lg-2">
        <div class="form-group">
            <label for="idgudang">Gudang Asal</label>
            <select id="idgudang" class="form-control">
                <option value="">-- Pilih --</option>
                <?php foreach (($datagudang ?? []) as $gdg) : ?>
                    <option value="<?= $gdg['gdgid'] ?>"><?= esc($gdg['gdgnama']) ?></option>
                <?php endforeach ?>
            </select>
        </div>
    </div>
    <div class="col-lg-1">
        <div class="form-group">
            <label for="stok">Stok</label>
            <input type="number" class="form-control" id="stok" readonly>
            <input type="hidden" id="berat">
            <input type="hidden" id="detkurang">
            <input type="hidden" id="detqty">
        </div>
    </div>
    <div class="col-lg-1">
        <div class="form-group">
            <label for="jml">Qty</label>
            <input type="number" class="form-control" id="jml" value="1">
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
        <?= view('reqbarang/datadetailkirim', ['tampildata' => $detailPengiriman]) ?>
    </div>
</div>

<div class="viewmodal" style="display: none;"></div>

<script>
    let csrfToken = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';

    const produkOptionsProses = <?= json_encode(array_map(static function ($row) {
                                $kode = (string) $row['brgkode'];
                                $nama = (string) $row['brgnama'];
                                return [
                                    'id' => $kode,
                                    'text' => $kode . ' - ' . $nama,
                                    'value' => $kode,
                                ];
                            }, $databarang ?? []), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const materialOptionsProses = <?= json_encode(array_map(static function ($row) {
                                $kode = (string) $row['matkode'];
                                $nama = (string) $row['matnama'];
                                return [
                                    'id' => (string) $row['matid'],
                                    'text' => $kode . ' - ' . $nama,
                                    'value' => $kode,
                                ];
                            }, $datamaterial ?? []), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const itemOptionsProses = [];
    let itemComboboxProses = null;

    function refreshItemOptionsProses() {
        const source = $('#jenisItemProses').val() === 'material' ? materialOptionsProses : produkOptionsProses;
        itemOptionsProses.length = 0;
        source.forEach(function(option) {
            itemOptionsProses.push(option);
        });
    }

    function normalizeComboboxValueProses(value) {
        return String(value || '').trim().toLowerCase();
    }

    function syncItemComboboxProses() {
        if (itemComboboxProses && typeof itemComboboxProses.sync === 'function') {
            itemComboboxProses.sync();
        }

        const keyword = normalizeComboboxValueProses($('#kodebarang').val());
        const match = itemOptionsProses.find(function(option) {
            return normalizeComboboxValueProses(option.text) === keyword || normalizeComboboxValueProses(option.value) === keyword;
        });

        if (match) {
            $('#kodebarang').val(match.value || match.text);
            $('#kodebarang_pilih').val(match.id);
        }
    }

    function ambilDataItemProses() {
        syncItemComboboxProses();
        const kodebarang = $('#kodebarang').val();
        const idgudang = $('#idgudang').val();
        if (!kodebarang) {
            return;
        }
        if (!idgudang) {
            Swal.fire('Error', 'Pilih Gudang Asal dulu', 'error');
            return;
        }

        $.ajax({
            type: "post",
            url: $('#jenisItemProses').val() === 'material'
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
                    return;
                }
                if (response.sukses) {
                    const data = response.sukses;
                    $('#berat').val(data.berat);
                    $('#stok').val(data.stok);
                    $('#namabarang').val(data.namabarang);
                    $('#idmaterial').val(data.idmaterial || '');
                    $('#jml').focus();
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(xhr.status + '\n' + thrownError);
            }
        });
    }

    $(document).on('click', '#editHeaderBtn', function() {
        $('#jenisPengirimanText, #picPengirimText, #nominalText').hide();
        $('#jenisPengirimanInput, #picPengirimInput, #nominalInput').show();
        $('#editHeaderBtn').hide();
        $('#saveHeaderBtn, #cancelHeaderBtn').show();
    });

    $(document).on('click', '#cancelHeaderBtn', function() {
        $('#jenisPengirimanInput, #picPengirimInput, #nominalInput').hide();
        $('#jenisPengirimanText, #picPengirimText, #nominalText').show();
        $('#saveHeaderBtn, #cancelHeaderBtn').hide();
        $('#editHeaderBtn').show();
    });

    $(document).on('click', '#saveHeaderBtn', function(e) {
        e.preventDefault();
        $.ajax({
            type: "post",
            url: '<?= site_url('permintaanBarangKirim/updateHeader') ?>',
            data: {
                [csrfToken]: csrfHash,
                faktur: $('#nofaktur').val(),
                jenispengiriman: $('#jenisPengirimanInput').val(),
                picpengirim: $('#picPengirimInput').val(),
                nominal: $('#nominalInput').val(),
            },
            dataType: "json",
            success: function(response) {
                if (response.error) {
                    Swal.fire('Error', response.error, 'error');
                    return;
                }
                Swal.fire('Berhasil', response.sukses, 'success').then(() => {
                    window.location.reload();
                });
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(xhr.status + '\n' + thrownError);
            }
        });
    });

    function kosong() {
        $('#kodebarang').val('');
        $('#kodebarang_pilih').val('');
        $('#idbarang').val('');
        $('#idmaterial').val('');
        $('#idgudang').val('');
        $('#iddetail').val('');
        $('#stok').val('');
        $('#berat').val('');
        $('#detkurang').val('');
        $('#detqty').val('');
        $('#namabarang').val('');
        $('#jml').val('1');
        $('#jenisItemProses').prop('disabled', false);
        $('#idgudang').prop('disabled', false);
        $('#kodebarang').focus();
    }

    function tampilDataDetail() {
        let faktur = $('#nofaktur').val();
        let permintaan = $('#permintaan').val();
        $.ajax({
            type: "post",
            url: '<?= site_url('permintaanBarangKirim/tampilDataDetailProses') ?>',
            data: {
                [csrfToken]: csrfHash,
                nofaktur: faktur,
                permintaan: permintaan,
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

    function tampilDataTempKeluar() {
        let faktur = $('#nofaktur').val();
        $.ajax({
            type: "post",
            url: '<?= site_url('permintaanBarangKirim/tampilDataTempKeluar') ?>',
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

    function ambilTotalQty() {
        let nofaktur = $('#nofaktur').val();
        $.ajax({
            type: "post",
            url: '<?= site_url('permintaanBarangKirim/ambilTotalQtyProses') ?>',
            data: {
                [csrfToken]: csrfHash,
                nofaktur: nofaktur
            },
            dataType: "json",
            success: function(response) {
                $('#lbTotalQty').html(response.totalqty);
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(xhr.status + '\n' + thrownError)
            }
        });
    }

    function simpanItem() {
        syncItemComboboxProses();
        let permintaan = $('#permintaan').val();
        let nofaktur = $('#nofaktur').val();
        let kodebarang = $('#kodebarang').val();
        let idgudang = $('#idgudang').val();
        let idmaterial = $('#idmaterial').val();
        let namabarang = $('#namabarang').val();
        let jenisItem = $('#jenisItemProses').val();
        let iduser = $('#iduser').val();
        let berat = $('#berat').val();
        let jml = $('#jml').val();
        let tanggal = $('#tanggal').val();

        if (kodebarang.length == 0) {
            Swal.fire('Error', 'Kode item harus dipilih', 'error');
            return;
        }
        if (!idgudang) {
            Swal.fire('Error', 'Gudang Asal wajib dipilih', 'error');
            return;
        }

        $.ajax({
            type: "post",
            url: '<?= site_url('permintaanBarangKirim/simpanItemDetailProses') ?>',
            data: {
                [csrfToken]: csrfHash,
                permintaan: permintaan,
                nofaktur: nofaktur,
                kodebarang: kodebarang,
                idgudang: idgudang,
                idmaterial: idmaterial,
                namabarang: namabarang,
                jenis_item: jenisItem,
                berat: berat,
                iduser: iduser,
                jml: jml,
                tanggal: tanggal,
            },
            dataType: "json",
            success: function(response) {
                if (response.error) {
                    Swal.fire('Error', response.error, 'error');
                    return;
                }
                if (response.sukses) {
                    Swal.fire({
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
                alert(xhr.status + '\n' + thrownError)
            }
        });
    }

    $(document).ready(function() {
        refreshItemOptionsProses();
        itemComboboxProses = window.treInitInlineCombobox({
            box: '#itemComboboxProses',
            input: '#kodebarang',
            hidden: '#kodebarang_pilih',
            menu: '#itemComboboxProsesMenu',
            options: itemOptionsProses,
            onSelect: function() {
                ambilDataItemProses();
            }
        });

        $('#kodebarang').on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                syncItemComboboxProses();
                ambilDataItemProses();
            }
        });

        $('#jenisItemProses').on('change', function() {
            const material = $(this).val() === 'material';
            $('#labelKodeItemProses').text(material ? 'Kode Material' : 'Kode Produk');
            refreshItemOptionsProses();
            kosong();
        });

        $('#idgudang').on('change', function() {
            if ($('#kodebarang_pilih').val()) {
                ambilDataItemProses();
            }
        });

        $('#tombolSimpanItem').click(function(e) {
            e.preventDefault();
            simpanItem();
        });

        $('#tombolEditItem').click(function(e) {
            e.preventDefault();
            $.ajax({
                type: "post",
                url: '<?= site_url('permintaanBarangKirim/editItemProses') ?>',
                data: {
                    [csrfToken]: csrfHash,
                    iddetail: $('#iddetail').val(),
                    permintaan: $('#permintaan').val(),
                    kodebarang: $('#kodebarang').val(),
                    detkurang: $('#detkurang').val(),
                    stok: $('#stok').val(),
                    detqty: $('#detqty').val(),
                    detjml: $('#detjml').val(),
                    jml: $('#jml').val(),
                    tanggal: $('#tanggal').val(),
                },
                dataType: "json",
                success: function(response) {
                    // hasildebug
                    // $('#debug-info').html('<pre>' + JSON.stringify(response.data.debug, null, 2) + '</pre>');

                    if (response.error || response.error1 || response.error2 || response.error3) {
                        let errorMessage = response.error || response.error1 || response.error2 || response.error3;
                        Swal.fire('Error', errorMessage, 'error');
                    } else if (response.sukses) {
                        Swal.fire({
                            title: 'Berhasil',
                            text: response.sukses,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                tampilDataDetail();
                                ambilTotalQty();
                                $('#kodebarang').prop('readonly', false);
                                $('#jenisItemProses').prop('disabled', false);
                                $('#idgudang').prop('disabled', false);
                                $('#tombolSimpanItem').fadeIn();
                                $('#tombolEditItem').fadeOut();
                                $('#tombolBatal').fadeOut();
                                window.location.reload();
                            }
                        });
                    } else {
                        // Example of displaying debug info in a pre-defined element
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert(xhr.status + '\n' + thrownError);
                }
            });
        });
    });
</script>
<?= $this->endSection('isi') ?>
