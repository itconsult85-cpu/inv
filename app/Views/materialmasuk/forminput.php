<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
<?= !empty($penerimaanNg) ? 'Penerimaan Material Pengganti NG' : 'Input Material Masuk' ?>
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>

<button type="button" class="btn btn-warning" onclick="location.href=('/materialmasuk/data')">
    <i class="fa fa-undo"></i> Kembali
</button>

<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>

<div class="row">
    <?php if (!empty($penerimaanNg)) : ?><div class="col-12"><div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> Mode Penerimaan dari NG: pilih PO berstatus NG yang akan menerima material pengganti.</div></div><?php endif ?>
    <div class="col-lg-2">
        <div class="form-group">
            <label for="tglfaktur">Tanggal</label>
            <input type="date" name="tglfaktur" id="tglfaktur" class="form-control" value="<?= date('Y-m-d') ?>">
        </div>
    </div>
    <div class="col-lg-2" id="wrapperInvoice">
        <div class="form-group">
            <label for="nofaktur">No. Invoice <small class="text-muted">(opsional)</small></label>
            <input type="text" name="nofaktur" id="nofaktur" class="form-control" onchange="cariDataMaterialMasuk()">
            <input type="hidden" name="faktur_internal" id="faktur_internal">
        </div>
    </div>
    <div class="col-lg-3" id="wrapperNoDo">
        <div class="form-group">
            <label for="no_do">No Surat Jalan <small class="text-muted" id="noDoHelp">(wajib)</small></label>
            <input type="text" name="no_do" id="no_do" class="form-control" onchange="cekNoDo()">
        </div>
    </div>
    <div class="col-lg-2">
        <div class="form-group">
            <label for="sumber_material">Sumber Material</label>
            <select name="sumber" id="sumber_material" class="form-control">
                <option value="beli" <?= !empty($penerimaanNg) ? '' : 'selected' ?>>Beli dari Supplier</option>
                <?php if (!empty($penerimaanNg)) : ?><option value="retur_ng" selected>Penerimaan Pengganti NG</option><?php endif ?>
                <option value="adjustment">Adjustment Stok</option>
                <option value="konsinyasi">Konsinyasi dari Pelanggan</option>
            </select>
        </div>
    </div>

    <div class="col-lg-3" id="wrapperSupplier">
        <div class="form-group">
            <label for="namasupplier">Cari Supplier</label>
            <div class="input-group mb-3 tre-inline-combobox" id="supplierCombobox">
                <input type="text" class="form-control" placeholder="Nama Supplier" name="namasupplier" id="namasupplier">
                <input type="hidden" name="idsupplier" id="idsupplier">
                <div class="input-group-append">
                    <?php if (\App\Libraries\AccessControl::can('master.supplier.create')) : ?>
                        <button class="btn btn-outline-success" type="button" id="tombolTambahSupplier" title="Tambah Supplier">
                            <i class="fa fa-plus-square"></i>
                        </button>
                    <?php endif ?>
                </div>
                <div class="tre-inline-combobox-menu" id="supplierComboboxMenu"></div>
            </div>
        </div>
    </div>

    <div class="col-lg-3" id="wrapperPelanggan" style="display:none;">
        <div class="form-group">
            <label for="pelanggan_input">Pelanggan (Sumber Konsinyasi)</label>
            <div class="input-group tre-inline-combobox tre-inline-combobox-solo" id="pelangganCombobox">
                <input type="text" id="pelanggan_input" class="form-control" placeholder="-- Pilih Pelanggan --" autocomplete="off">
                <input type="hidden" name="idpelanggan" id="idpelanggan">
                <div class="tre-inline-combobox-menu" id="pelangganComboboxMenu"></div>
            </div>
            <small class="text-muted">Material dititipkan pelanggan ini buat diproses, bukan dibeli.</small>
        </div>
    </div>

    <div class="col-lg-2">
        <div class="form-group">
            <label for="gudang">Lokasi Gudang</label>
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

    <div class="col-lg-3" id="wrapperPoKeluar">
        <div class="form-group">
            <label for="po_keluar_input">Pilih PO Keluar <small class="text-muted">(opsional)</small></label>
            <div class="input-group tre-inline-combobox tre-inline-combobox-solo" id="poKeluarCombobox">
                <input type="text" id="po_keluar_input" class="form-control" placeholder="-- Bukan dari PO Keluar --" autocomplete="off">
                <input type="hidden" name="po_keluar_id" id="po_keluar_id">
                <div class="tre-inline-combobox-menu" id="poKeluarComboboxMenu"></div>
            </div>
        </div>
    </div>

</div>

<script>
    function terapkanSumberMaterial() {
        var sumber = $('#sumber_material').val();
        var beli = sumber === 'beli' || sumber === 'retur_ng';
        var konsinyasi = sumber === 'konsinyasi';
        var adjustment = sumber === 'adjustment';

        $('#wrapperInvoice').toggle(beli);
        $('#wrapperNoDo').toggle(!adjustment);
        $('#wrapperSupplier').toggle(beli);
        $('#wrapperPelanggan').toggle(konsinyasi);
        $('#wrapperPoKeluar').toggle(beli);
        $('#noDoHelp').text(!adjustment ? '(wajib)' : '');

        if (!beli) {
            $('#nofaktur').val('');
            $('#faktur_internal').val('');
            $('#idsupplier').val('');
            $('#namasupplier').val('');
            $('#po_keluar_id').val('');
            $('#po_keluar_input').val('');
            muatItemPoKeluar('');
        }
        if (adjustment) {
            $('#no_do').val('');
        }
        if (!konsinyasi) {
            $('#idpelanggan').val('');
            $('#pelanggan_input').val('');
        }
    }

    $('#sumber_material').on('change', terapkanSumberMaterial);
</script>

<div class="row" id="cardItemPoKeluar" style="display:none;">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header py-2">
                <strong>Item PO Keluar <span id="labelPoKeluarTerpilih"></span></strong>
                <small class="text-muted d-block">Klik salah satu baris untuk mengisi item, lalu isi Qty yang datang (boleh sebagian dari sisa).</small>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0" id="tabelItemPoKeluar">
                    <thead>
                        <tr>
                            <th>Kode Material</th>
                            <th>Nama Material</th>
                            <th class="text-right">Qty Pesan</th>
                            <th class="text-right">Sudah Masuk</th>
                            <th class="text-right">Sisa</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-3">
        <div class="form-group">
            <label for="kodematerial">Kode Material</label>
            <div class="input-group mb-3 tre-inline-combobox tre-inline-combobox-solo" id="materialCombobox">
                <input type="text" class="form-control" name="kodematerial" id="kodematerial" autocomplete="off">
                <input type="hidden" name="materialid" id="materialid">
                <input type="hidden" name="idmat" id="idmat">
                <input type="hidden" name="materialkatid" id="materialkatid">
                <input type="hidden" name="materialsatid" id="materialsatid">
                <input type="hidden" name="gdgid" id="gdgid">
                <input type="hidden" name="idgudang" id="idgudang">
                <div class="tre-inline-combobox-menu" id="materialComboboxMenu"></div>
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
    let materialSaveInProgress = false;
    const supplierOptions = <?= json_encode(array_map(static function ($row) {
                                return [
                                    'id' => (string) $row['supid'],
                                    'text' => $row['supnama'],
                                ];
                            }, $datasupplier ?? []), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const pelangganOptions = <?= json_encode(array_map(static function ($row) {
                                return [
                                    'id' => (string) $row['pelid'],
                                    'text' => $row['pelnama'],
                                ];
                            }, $datapelanggan ?? []), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const poKeluarOptions = <?= json_encode(array_map(static function ($row) {
                                $label = (string) $row['no_po'] . ' - ' . (string) $row['supplier_nama'];
                                return [
                                    'id' => (string) $row['id'],
                                    'text' => $label,
                                    'value' => $label,
                                ];
                            }, $datapokeluar ?? []), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const materialOptions = <?= json_encode(array_map(static function ($row) {
                                $kode = (string) $row['matkode'];
                                $nama = (string) $row['matnama'];
                                return [
                                    'id' => (string) $row['matid'],
                                    'text' => $kode . ' - ' . $nama,
                                    'value' => $kode,
                                ];
                            }, $datamaterial ?? []), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    let supplierCombobox = null;
    let pelangganCombobox = null;
    let poKeluarCombobox = null;
    let materialCombobox = null;

    function syncSupplierCombobox() {
        if (supplierCombobox && typeof supplierCombobox.sync === 'function') {
            supplierCombobox.sync();
        }
    }

    function syncPelangganCombobox() {
        if (pelangganCombobox && typeof pelangganCombobox.sync === 'function') {
            pelangganCombobox.sync();
        }
    }

    function syncPoKeluarCombobox() {
        if (poKeluarCombobox && typeof poKeluarCombobox.sync === 'function') {
            poKeluarCombobox.sync();
        }
    }

    function syncMaterialCombobox() {
        if (materialCombobox && typeof materialCombobox.sync === 'function') {
            materialCombobox.sync();
        }
    }

    document.getElementById('gudang').addEventListener('change', function() {
        var selectedGudangId = this.value;
        document.getElementById('idgudang').value = selectedGudangId;

        var materialid = document.getElementById('materialid').value;

        var selectedOption = this.options[this.selectedIndex];
        var gdgid = selectedOption.value;
        document.getElementById("gdgid").value = gdgid;

        if (materialid.length == 0) {
            return;
        }

        $.ajax({
            type: "post",
            url: '<?= site_url('materialmasuk/ambilDataMaterial') ?>',
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
                    $('#idmat').val(data.idmat);
                    $('#materialkatid').val(data.materialkatid);
                    $('#materialsatid').val(data.materialsatid);
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                showBootstrapModal('Error', xhr.status + '\n' + thrownError, 'error');
            }
        });
    });

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function(match) {
            return ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            })[match];
        });
    }

    function muatItemPoKeluar(poKeluarId) {
        if (!poKeluarId) {
            $('#cardItemPoKeluar').hide();
            $('#tabelItemPoKeluar tbody').empty();
            $('#labelPoKeluarTerpilih').text('');
            return;
        }
        $.ajax({
            type: "post",
            url: '<?= site_url('materialmasuk/itemPoKeluar') ?>',
            data: {
                [csrfToken]: csrfHash,
                po_keluar_id: poKeluarId
            },
            dataType: "json",
            success: function(response) {
                if (response.error) {
                    showBootstrapModal('Error', response.error, 'error');
                    return;
                }
                let data = response.sukses;
                $('#namasupplier').val(data.namasupplier);
                $('#idsupplier').val(data.idsupplier);
                $('#labelPoKeluarTerpilih').text('- ' + data.namasupplier);

                let rows = data.items.map(function(item) {
                    return '<tr class="item-po-keluar-row" style="cursor:pointer" ' +
                        'data-matid="' + escapeHtml(item.kode_item) + '" ' +
                        'data-kode="' + escapeHtml(item.matkode) + '" ' +
                        'data-nama="' + escapeHtml(item.matnama) + '" ' +
                        'data-katid="' + escapeHtml(item.matkatid) + '" ' +
                        'data-satid="' + escapeHtml(item.matsatid) + '" ' +
                        'data-sisa="' + escapeHtml(item.sisa) + '">' +
                        '<td>' + escapeHtml(item.matkode) + '</td>' +
                        '<td>' + escapeHtml(item.matnama) + '</td>' +
                        '<td class="text-right">' + Number(item.qty_pesan).toLocaleString('id-ID') + '</td>' +
                        '<td class="text-right">' + Number(item.qty_masuk).toLocaleString('id-ID') + '</td>' +
                        '<td class="text-right">' + Number(item.sisa).toLocaleString('id-ID') + '</td>' +
                        '</tr>';
                });
                $('#tabelItemPoKeluar tbody').html(rows.join(''));
                $('#cardItemPoKeluar').show();
            },
            error: function(xhr, ajaxOptions, thrownError) {
                showBootstrapModal('Error', xhr.status + '\n' + thrownError, 'error');
            }
        });
    }

    $(document).on('click', '.item-po-keluar-row', function() {
        let idgudang = $('#idgudang').val();
        if (!idgudang) {
            showBootstrapModal('Pesan', 'Pilih Lokasi Gudang terlebih dahulu.', 'warning');
            return;
        }
        $('#materialid').val($(this).data('matid'));
        $('#kodematerial').val($(this).data('kode'));
        $('#materialkatid').val($(this).data('katid'));
        $('#materialsatid').val($(this).data('satid'));
        ambilDataMaterial();
        $('#jml').attr('placeholder', 'Sisa: ' + $(this).data('sisa')).focus();
    });

    $('#po_keluar_input').on('input', function() {
        if ($(this).val().trim() === '') {
            $('#po_keluar_id').val('');
            muatItemPoKeluar('');
        }
    });

    function kosong() {
        console.log("Fungsi kosong dipanggil");
        $('#kodematerial').val('');
        $('#stok').val('');
        $('#materialid').val('');
        $('#namamaterial').val('');
        $('#materialkatid').val('');
        $('#materialsatid').val('');
        $('#idmat').val('');
        $('#jml').val('1');

    }

    function buatNomorMaterialMasuk() {
        const now = new Date();
        const pad = value => String(value).padStart(2, '0');
        return 'MM-' +
            now.getFullYear() +
            pad(now.getMonth() + 1) +
            pad(now.getDate()) + '-' +
            pad(now.getHours()) +
            pad(now.getMinutes()) +
            pad(now.getSeconds()) + '-' +
            Math.floor(Math.random() * 900 + 100);
    }

    function pastikanNomorMaterialMasuk() {
        if (!$('#faktur_internal').val()) {
            $('#faktur_internal').val(buatNomorMaterialMasuk());
        }
        return $('#faktur_internal').val().trim();
    }

    function cariDataMaterialMasuk() {
        const nofaktur = $('#nofaktur').val().trim();

        if (nofaktur.length === 0) {
            return;
        }

        $.ajax({
            type: 'post',
            url: '<?= site_url('materialmasuk/cekNoInvoice') ?>',
            data: {
                [csrfToken]: csrfHash,
                nofaktur: nofaktur
            },
            dataType: 'json',
            success: function(response) {
                if (response.terpakai) {
                    showBootstrapModal('No. Invoice sudah digunakan', response.pesan, 'warning')
                        .then(() => {
                            $('#nofaktur').val('').focus();
                        });
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                showBootstrapModal('Error', xhr.status + '\n' + thrownError, 'error');
            }
        });
    }

    function cekNoDo() {
        const noDo = $('#no_do').val().trim();
        if (noDo.length === 0) {
            return;
        }

        $.ajax({
            type: 'post',
            url: '<?= site_url('materialmasuk/cekNoDo') ?>',
            data: {
                [csrfToken]: csrfHash,
                no_do: noDo
            },
            dataType: 'json',
            success: function(response) {
                if (response.terpakai) {
                    showBootstrapModal('No Surat Jalan sudah digunakan', response.pesan, 'warning')
                        .then(() => $('#no_do').val('').focus());
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                showBootstrapModal('Error', xhr.status + '\n' + thrownError, 'error');
            }
        });
    }

    function simpanItem() {
        syncSupplierCombobox();
        syncPelangganCombobox();
        syncMaterialCombobox();
        let fakturInternal = pastikanNomorMaterialMasuk();
        let nofaktur = $('#nofaktur').val().trim();
        let idmat = $('#idmat').val();
        let kodematerial = $('#kodematerial').val();
        let namamaterial = $('#namamaterial').val();
        let materialkatid = $('#materialkatid').val();
        let materialsatid = $('#materialsatid').val();
        let idsupplier = $('#idsupplier').val();
        let materialid = $('#materialid').val();
        let gudang = $('#gudang').val();
        let gdgid = $('#gdgid').val();
        let stok = $('#stok').val();
        let tglfaktur = $('#tglfaktur').val();
        let jml = $('#jml').val();
        let poKeluarId = $('#po_keluar_id').val();

        if (kodematerial.length == 0) {
            showBootstrapModal('Error', 'Kode material harus diinputkan', 'error');
            kosong();
        } else if (gudang.length == 0) {
            showBootstrapModal('Error', 'Lokasi gudang harus dipilih terlebih dahulu', 'error');
        } else if (materialid.length == 0) {
            showBootstrapModal('Error', 'Pilih material dari dropdown terlebih dahulu', 'error');
        } else {
            $.ajax({
                type: "post",
                url: '<?= site_url('materialmasuk/simpanItem') ?>',
                data: {
                    [csrfToken]: csrfHash,
                    nofaktur: nofaktur,
                    faktur_internal: fakturInternal,
                    tglfaktur: tglfaktur,
                    idmat: idmat,
                    kodematerial: kodematerial,
                    namamaterial: namamaterial,
                    materialkatid: materialkatid,
                    materialsatid: materialsatid,
                    idsupplier: idsupplier,
                    sumber: $('#sumber_material').val(),
                    idpelanggan: $('#idpelanggan').val(),
                    materialid: materialid,
                    gudang: gudang,
                    gdgid: gdgid,
                    materialid: materialid,
                    stok: stok,
                    jml: jml,
                    po_keluar_id: poKeluarId
                },
                dataType: "json",
                success: function(response) {
                    if (response.error) {
                        showBootstrapModal('Error', response.error, 'error');
                    } else if (response.error2) {
                        showBootstrapModal('Error', response.error2, 'error');
                        kosong();
                    } else if (response.sukses) {
                        if (response.faktur_internal) {
                            $('#faktur_internal').val(response.faktur_internal);
                        }
                        showBootstrapModal({
                            title: 'Berhasil',
                            icon: 'success',
                            text: response.sukses
                        }).then((result) => {
                            if (result.isConfirmed) {
                                tampilDataTemp();
                                kosong();
                            }
                        })
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    showBootstrapModal('Error', xhr.status + '\n' + thrownError, 'error');
                }
            });
        }
    }

    function ambilDataMaterial() {
        syncMaterialCombobox();
        let kodematerial = $('#kodematerial').val();
        let idgudang = $('#idgudang').val();
        let materialid = $('#materialid').val();
        let idmat = $('#idmat').val();
        if (kodematerial.length == 0) {
            showBootstrapModal('Error', 'Kode Material harus di inputkan', 'error');
            kosong();
        } else if (idgudang.length == 0) {
            showBootstrapModal('Error', 'Pilih Lokasi Gudang terlebih dahulu.', 'error');
        } else if (materialid.length == 0) {
            showBootstrapModal('Error', 'Pilih material dari dropdown terlebih dahulu.', 'error');
        } else {
            $.ajax({
                type: "post",
                url: '<?= site_url('materialmasuk/ambilDataMaterial') ?>',
                data: {
                    [csrfToken]: csrfHash,
                    kodematerial: kodematerial,
                    idmat: idmat,
                    idgudang: idgudang,
                    materialid: materialid,
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
                        $('#kodematerial').val(data.kodematerial);
                        $('#stok').val(data.stok);
                        $('#materialid').val(data.materialid);
                        $('#idmat').val(data.idmat);
                        $('#materialkatid').val(data.materialkatid);
                        $('#materialsatid').val(data.materialsatid);
                        $('#jml').focus();
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    showBootstrapModal('Error', xhr.status + '\n' + thrownError, 'error')
                }
            });
        }
    }

    function tampilDataTemp() {
        let faktur = $('#faktur_internal').val().trim();
        let idsupplier = $('#idsupplier').val();
        let materialid = $('#materialid').val();
        let idmat = $('#idmat').val();

        $.ajax({
            type: "post",
            url: '<?= site_url('materialmasuk/tampilDataTemp') ?>',
            data: {
                [csrfToken]: csrfHash,
                nofaktur: faktur,
                faktur_internal: $('#faktur_internal').val(),
                idsupplier: idsupplier,
                materialid: materialid,
                idmat: idmat,
            },
            dataType: "json",
            beforeSend: function() {
                $('.tampilDataTemp').html("<i class='fa fa-spin fa-spinner'></i>");
            },
            success: function(response) {
                if (response.data) {
                    $('.tampilDataTemp').html(response.data);
                    $('#idsupplier').val(idsupplier);
                    $('#materialid').val(materialid);
                    $('#idmat').val(idmat);
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                showBootstrapModal('Error', xhr.status + '\n' + thrownError, 'error')
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
        pelangganCombobox = window.treInitInlineCombobox({
            box: '#pelangganCombobox',
            input: '#pelanggan_input',
            hidden: '#idpelanggan',
            menu: '#pelangganComboboxMenu',
            options: pelangganOptions
        });
        poKeluarCombobox = window.treInitInlineCombobox({
            box: '#poKeluarCombobox',
            input: '#po_keluar_input',
            hidden: '#po_keluar_id',
            menu: '#poKeluarComboboxMenu',
            options: poKeluarOptions,
            onSelect: function(option) {
                muatItemPoKeluar(option.id);
            }
        });
        materialCombobox = window.treInitInlineCombobox({
            box: '#materialCombobox',
            input: '#kodematerial',
            hidden: '#materialid',
            menu: '#materialComboboxMenu',
            options: materialOptions,
            onSelect: function() {
                ambilDataMaterial();
            }
        });
        terapkanSumberMaterial();
        <?php if (!empty($penerimaanNg) && !empty($poNgTerpilih)) : ?>
        $('#sumber_material').prop('disabled', true);
        $('#po_keluar_id').val('<?= (int) $poNgTerpilih['id'] ?>');
        $('#po_keluar_input').val('<?= esc($poNgTerpilih['no_po'], 'js') ?> - <?= esc($poNgTerpilih['supplier_nama'], 'js') ?>');
        muatItemPoKeluar('<?= (int) $poNgTerpilih['id'] ?>');
        <?php endif ?>

        $(document).off('tre:supplierAdded.supplierCombobox').on('tre:supplierAdded.supplierCombobox', function(event, supplier) {
            if (supplierCombobox && typeof supplierCombobox.addOption === 'function') {
                supplierCombobox.addOption(supplier, true);
            }
        });

        tampilDataTemp();
        $('#nofaktur').focus();

        $('#tombolReload').click(function(e) {
            e.preventDefault();
            tampilDataTemp();
            kosong();
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
                    showBootstrapModal('Error', xhr.status + '\n' + thrownError, 'error')
                }
            });
        });

        $('#tombolCariSupplier').click(function(e) {
            e.preventDefault();
            $.ajax({
                url: "<?= site_url('supplier/modalData') ?>?exclude_internal=1",
                dataType: "json",
                success: function(response) {
                    if (response.data) {
                        $('.viewmodal').html(response.data).show();
                        $('#modaldatasupplier').modal('show');
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    showBootstrapModal('Error', xhr.status + '\n' + thrownError, 'error')
                }
            });
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

        $('#tombolSelesaiTransaksi').click(function(e) {
            e.preventDefault();
            if (materialSaveInProgress) {
                return;
            }
            syncSupplierCombobox();
            syncPelangganCombobox();
            syncPoKeluarCombobox();
            // Jangan panggil terapkanSumberMaterial() di sini -- fungsi itu
            // ngosongin #faktur_internal buat sumber selain "beli" (termasuk
            // "adjustment"), jadi kalau dipanggil pas mau selesai transaksi,
            // nomor transaksinya ke-generate ulang jadi baru & beda sama yang
            // udah dipakai buat nyimpen item-itemnya (item jadi "ilang").
            let fakturInternal = pastikanNomorMaterialMasuk();
            let nofaktur = $('#nofaktur').val().trim();
            let noDo = $('#no_do').val();
            let tglfaktur = $('#tglfaktur').val();
            let idsupplier = $('#idsupplier').val();
            let idgudang = $('#idgudang').val();
            let idmat = $('#idmat').val();
            let poKeluarId = $('#po_keluar_id').val();
            let sumber = $('#sumber_material').val();
            let idpelanggan = $('#idpelanggan').val();

            if (sumber !== 'adjustment' && noDo.length == 0) {
                showBootstrapModal({
                    title: 'Pesan',
                    icon: 'warning',
                    text: 'Maaf No Surat Jalan tidak boleh kosong'
                })
            } else if (sumber === 'konsinyasi' && idpelanggan.length == 0) {
                showBootstrapModal({
                    title: 'Pesan',
                    icon: 'warning',
                    text: 'Maaf data pelanggan (sumber konsinyasi) tidak boleh kosong'
                })
            } else if ((sumber === 'beli' || sumber === 'retur_ng') && idsupplier.length == 0) {
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
                        materialSaveInProgress = true;
                        $('#tombolSelesaiTransaksi').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');
                        $.ajax({
                            type: "post",
                            url: "<?= site_url('materialmasuk/selesaiTransaksi') ?>",
                            data: {
                                [csrfToken]: csrfHash,
                                nofaktur: nofaktur,
                                faktur_internal: fakturInternal,
                                no_do: noDo,
                                tglfaktur: tglfaktur,
                                idsupplier: idsupplier,
                                idgudang: idgudang,
                                idmat: idmat,
                                po_keluar_id: poKeluarId,
                                sumber: sumber,
                                idpelanggan: idpelanggan,
                            },
                            dataType: "json",
                            success: function(response) {
                                if (response.error) {
                                    showBootstrapModal({
                                        title: 'Error',
                                        icon: 'error',
                                        text: response.error
                                    });
                                    materialSaveInProgress = false;
                                    $('#tombolSelesaiTransaksi').prop('disabled', false).html('<i class="fa fa-save"></i> Selesai Transaksi');
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
                                showBootstrapModal({
                                    title: 'Error',
                                    icon: 'error',
                                    text: (xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON.message)) || (xhr.status + '\n' + thrownError)
                                });
                                materialSaveInProgress = false;
                                $('#tombolSelesaiTransaksi').prop('disabled', false).html('<i class="fa fa-save"></i> Selesai Transaksi');
                            }
                        });
                    }
                })
            }
        });
    });
</script>

<?= $this->endSection('isi') ?>
