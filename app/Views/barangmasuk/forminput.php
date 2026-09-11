<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Input Produk Masuk
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>

<button type="button" class="btn btn-warning" onclick="location.href=('/barangmasuk/data')">
    <i class="fa fa-undo"></i> Kembali
</button>

<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<style>
    #nofaktur+.select2-container .select2-selection--multiple {
        height: calc(2.25rem + 2px) !important;
        min-height: calc(2.25rem + 2px) !important;
        padding: 0.375rem 0.75rem !important;
        overflow: hidden;
    }

    #nofaktur+.select2-container .select2-selection__rendered {
        display: flex !important;
        align-items: center;
        height: 100%;
        flex-wrap: nowrap;
        overflow-x: auto;
    }

    #nofaktur+.select2-container .select2-search--inline {
        display: flex;
        align-items: center;
    }

    #nofaktur+.select2-container .select2-search__field {
        height: auto !important;
        margin: 0 !important;
        padding: 0 !important;
        border: 0 !important;
    }

    #nofaktur+.select2-container .select2-selection__choice {
        color: #000 !important;
        background-color: #f1f1f1 !important;
        border: 1px solid #ced4da !important;
        margin-top: 0 !important;
    }

    #nofaktur+.select2-container .select2-selection__choice__remove {
        color: #000 !important;
    }

    #nofaktur+.select2-container.select2-container--focus .select2-selection--multiple {
        border-color: #80bdff !important;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
</style>

<div class="row">
    <div class="col-lg-3">
        <div class="form-group">
            <label for="sumberProduk">Sumber Produk</label>
            <select id="sumberProduk" class="form-control">
                <option value="beli">Beli dari Supplier</option>
                <option value="adjustment">Adjustment Stok</option>
                <option value="produksi">Produksi dari Material</option>
                <option value="produksi_pelanggan">Produksi Material Pelanggan</option>
            </select>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="form-group">
            <label for="tglfaktur">Tanggal Transaksi</label>
            <input type="date" name="tglfaktur" id="tglfaktur" class="form-control" value="<?= date('Y-m-d') ?>">
        </div>
    </div>
    <div class="col-lg-3 beli-only supplier-only">
        <div class="form-group no-po-field">
            <label for="nofaktur">No PO</label>
            <select id="nofaktur" name="nofaktur" class="form-control select2" multiple="multiple" data-placeholder="-- Pilih PO Keluar --" style="width: 100%;">
                <?php foreach ($datapokeluar as $pok) : ?>
                    <option value="<?= esc($pok['no_po']) ?>" data-id="<?= $pok['id'] ?>"><?= esc($pok['no_po'] . ' - ' . $pok['supplier_nama']) ?></option>
                <?php endforeach ?>
            </select>
            <input type="hidden" name="po_keluar_id" id="po_keluar_id">
            <input type="hidden" name="faktur_internal" id="faktur_internal">
            <small class="text-muted">Wajib untuk sumber Beli dari Supplier. Kalau bukan dari PO, pilih sumber produk lain.</small>
        </div>
    </div>

    <div class="col-lg-3 beli-only supplier-only">
        <div class="form-group">
            <label for="namasupplier">Cari Supplier</label>
            <div class="input-group mb-3 tre-inline-combobox" id="supplierCombobox">
                <input type="text" class="form-control" placeholder="Nama Supplier" name="namasupplier" id="namasupplier">
                <input type="hidden" name="idsupplier" id="idsupplier">
                <div class="input-group-append">
                    <?php if (\App\Libraries\AccessControl::can('master.supplier.create')) :  ?>
                        <button class="btn btn-outline-success" type="button" id="tombolTambahSupplier" title="Tambah Supplier">
                            <i class="fa fa-plus-square"></i>
                        </button>
                    <?php endif ?>
                </div>
                <div class="tre-inline-combobox-menu" id="supplierComboboxMenu"></div>
            </div>
        </div>
    </div>

    <div class="col-lg-3">
        <div class="form-group">
            <label for="gudang">Gudang Tujuan</label>
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

<div class="row beli-only supplier-only" id="cardItemPoKeluar" style="display:none;">
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
                            <th>Kode Produk</th>
                            <th>Nama Produk</th>
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

<div class="row produk-picker-row">
    <div class="col-lg-2">
        <div class="form-group">
            <label for="kodebarang">Kode Produk</label>
            <div class="input-group mb-3 tre-inline-combobox tre-inline-combobox-solo" id="produkCombobox">
                <input type="text" class="form-control" name="kodebarang" id="kodebarang" disabled autocomplete="off">
                <input type="hidden" name="kodebarang_pilih" id="kodebarang_pilih">
                <input type="hidden" name="idmaterial" id="idmaterial">
                <input type="hidden" name="idbarang" id="idbarang">
                <input type="hidden" name="gdgid" id="gdgid">
                <input type="hidden" name="idgudang" id="idgudang">
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
    <div class="col-lg-2 beli-only">
        <div class="form-group">
            <label for="jml">Qty</label>
            <div class="input-group mb-3">
                <input type="number" class="form-control" name="jml" id="jml" value="1">
            </div>
        </div>
    </div>
    <div class="col-lg-2 beli-only">
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

<div class="row beli-only">
    <div class="col-lg-12 tampilDataTemp">

    </div>
</div>
<div class="row justify-content-end beli-only">
    <button type="button" class="btn btn-sm btn-success" id="tombolSelesaiTransaksi">
        <i class="fa fa-save"></i> Selesai Transaksi
    </button>
</div>

<div class="produksi-only" style="display:none;">
    <input type="hidden" id="noProduksiBatch" value="">
    <div class="row">
        <div class="col-lg-2">
            <div class="form-group">
                <label for="kodebarangProd">Kode Produk</label>
                <div class="input-group mb-3 tre-inline-combobox tre-inline-combobox-solo" id="produkComboboxProd">
                    <input type="text" class="form-control" id="kodebarangProd" autocomplete="off">
                    <input type="hidden" id="kodebarangProdPilih">
                    <input type="hidden" id="idbarangProd">
                    <div class="tre-inline-combobox-menu" id="produkComboboxProdMenu"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-2">
            <div class="form-group">
                <label for="namabarangProd">Nama Produk</label>
                <input type="text" class="form-control" id="namabarangProd" readonly>
            </div>
        </div>
        <div class="col-lg-2">
            <div class="form-group">
                <label for="stokProd">Stok</label>
                <input type="number" class="form-control" id="stokProd" readonly>
            </div>
        </div>
        <div class="col-lg-2">
            <div class="form-group">
                <label for="qty_produk">Qty Diproduksi</label>
                <input type="number" class="form-control" id="qty_produk" min="0.0001" step="0.0001" value="1">
            </div>
        </div>
        <div class="col-lg-2">
            <div class="form-group">
                <label>#</label>
                <div class="input-group mb-3">
                    <button type="button" class="btn btn-success" title="Tambah ke Daftar" id="tombolSimpanProduksi">
                        <i class="fa fa-plus-circle"></i>
                    </button>&nbsp;
                    <button type="button" class="btn btn-sm btn-warning" title="Reset Form" id="tombolResetProduksi">
                        <i class="fa fa-sync-alt"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="form-group">
                <label for="materialProduksi">Material yang Digunakan</label>
                <select id="materialProduksi" name="materialid" class="form-control select2" disabled style="width: 100%;">
                    <option value="">-- Pilih produk terlebih dahulu --</option>
                </select>
                <small id="materialProduksiHelp" class="form-text text-muted">
                    Material inti/default akan dipilih otomatis. Pilih material alternatif jika material tersebut yang digunakan.
                </small>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header py-2">
            <strong>Daftar Produk yang Diproduksi</strong>
        </div>
        <div class="card-body">
            <div class="table-responsive">
            <table class="table table-sm table-hover table-bordered mb-0" id="tabelDaftarProduk">
                <thead>
                    <tr>
                        <th style="width: 5%; text-align: center;">No</th>
                        <th style="text-align: center;">Kode Produk</th>
                        <th style="text-align: center;">Nama Produk</th>
                        <th style="text-align: center;">Material yang Digunakan</th>
                        <th style="width: 12%; text-align: center;">Qty Diproduksi</th>
                        <th style="width: 8%; text-align: center;">#</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="baris-daftar-produk-kosong">
                        <td colspan="6" class="text-center text-muted">Belum ada produk ditambahkan</td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>
    </div>

    <div class="row justify-content-end">
        <button type="button" class="btn btn-sm btn-success" id="tombolSelesaiProduksi">
            <i class="fa fa-save"></i> Selesai Transaksi
        </button>
    </div>
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
    const produkOptions = <?= json_encode(array_map(static function ($row) {
                                $kode = (string) $row['brgkode'];
                                $nama = (string) $row['brgnama'];
                                return [
                                    'id' => $kode,
                                    'text' => $kode . ' - ' . $nama,
                                    'value' => $kode,
                                ];
                            }, $databarang ?? []), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    let supplierCombobox = null;
    let produkCombobox = null;
    let produkComboboxProd = null;
    let daftarProduk = [];
    let materialProdukData = [];
    let materialProduksiAktif = null;

    function syncSupplierCombobox() {
        if (supplierCombobox && typeof supplierCombobox.sync === 'function') {
            supplierCombobox.sync();
        }
    }

    function syncProdukCombobox() {
        if (produkCombobox && typeof produkCombobox.sync === 'function') {
            produkCombobox.sync();
        }
    }

    function syncProdukComboboxProd() {
        if (produkComboboxProd && typeof produkComboboxProd.sync === 'function') {
            produkComboboxProd.sync();
        }
    }

    function resetMaterialProduksi() {
        materialProdukData = [];
        materialProduksiAktif = null;
        $('#materialProduksi')
            .prop('disabled', true)
            .prop('required', false)
            .html('<option value="">-- Pilih produk terlebih dahulu --</option>')
            .trigger('change');
        $('#materialProduksiHelp').text('Material inti/default akan dipilih otomatis. Pilih material alternatif jika material tersebut yang digunakan.');
    }

    function labelMaterialProduksi(material) {
        const status = material.default ? 'Default' : 'Alternatif';
        const stok = material.stok === null || material.stok === undefined
            ? '-'
            : Number(material.stok).toLocaleString('id-ID');
        const berat = material.berat_per_pcs === null || material.berat_per_pcs === undefined
            ? 'belum diisi'
            : Number(material.berat_per_pcs * 1000).toLocaleString('id-ID', { maximumFractionDigits: 4 }) + ' gram/pcs';
        return status + ' - ' + material.namamaterial + ' | stok: ' + stok + ' | ' + berat;
    }

    function tampilkanMaterialProduksi(data) {
        const $select = $('#materialProduksi');
        materialProdukData = data.materials || [];
        $select.empty();

        if (data.tanpa_berat) {
            $select.append(new Option('Tidak ada pemakaian material', ''))
                .prop('disabled', true)
                .prop('required', false)
                .trigger('change');
            $('#materialProduksiHelp').text('Produk tanpa berat/jasa tidak mengurangi stok material.');
            return;
        }

        materialProduksiAktif = data.default_material_id || null;
        materialProdukData.forEach(function(material) {
            const selected = Number(material.materialid) === Number(materialProduksiAktif);
            $select.append(new Option(labelMaterialProduksi(material), material.materialid, selected, selected));
        });

        $select
            .prop('disabled', materialProdukData.length === 0)
            .prop('required', materialProdukData.length > 0)
            .trigger('change');
        $('#materialProduksiHelp').text(materialProdukData.length
            ? 'Material inti/default dipilih otomatis. Ganti ke alternatif jika material alternatif yang digunakan.'
            : 'Produk belum memiliki material inti/alternatif.');
    }

    function ambilMaterialProduksi(kodebarang) {
        const gudang = $('#idgudang').val() || $('#gudang').val();
        if (!kodebarang || !gudang) {
            resetMaterialProduksi();
            return;
        }

        $.ajax({
            type: 'post',
            url: '<?= site_url('produksi/materialProduk') ?>',
            data: {
                [csrfToken]: csrfHash,
                kodebarang: kodebarang,
                gudang: gudang
            },
            dataType: 'json',
            success: function(response) {
                if (response.error) {
                    resetMaterialProduksi();
                    Swal.fire('Material Produk', response.error, 'warning');
                    return;
                }
                tampilkanMaterialProduksi(response.sukses || { materials: [], tanpa_berat: true });
            },
            error: function() {
                resetMaterialProduksi();
                Swal.fire('Material Produk', 'Data material produk tidak dapat dimuat.', 'error');
            }
        });
    }

    document.getElementById('gudang').addEventListener('change', function() {
        var selectedGudangId = this.value;
        document.getElementById('idgudang').value = selectedGudangId;

        var kodebarang = document.getElementById('kodebarang').value;

        var selectedOption = this.options[this.selectedIndex];
        var gdgid = selectedOption.value;
        document.getElementById("gdgid").value = gdgid;

        var kodebarangInput = document.getElementById('kodebarang');
        if (selectedGudangId !== "") {
            kodebarangInput.removeAttribute('disabled');
        } else {
            kodebarangInput.setAttribute('disabled', 'disabled');
            $('#kodebarang').val('');
            $('#kodebarang_pilih').val('');
            $('#namabarang').val('');
            $('#berat').val('');
            $('#stok1').val('');
            $('#idmaterial').val('');
            $('#idbarang').val('');
        }

        if (kodebarang.length == 0) {
            return;
        }

        // Panggil AJAX untuk mengambil data barang
        $.ajax({
            type: "post",
            url: '<?= site_url('barangmasuk/ambilDataBarang') ?>',
            data: {
                [csrfToken]: csrfHash,
                kodebarang: kodebarang,
                idgudang: selectedGudangId
            },
            dataType: "json",
            success: function(response) {
                if (response.error) {
                    Swal.fire('Error', response.error, 'error');
                }

                if (response.sukses) {
                    let data = response.sukses;

                    $('#namabarang').val(data.namabarang);
                    $('#berat').val(data.berat);
                    $('#stok1').val(data.stok);
                    $('#idbarang').val(data.idbarang);

                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(xhr.status + '\n' + thrownError);
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
            url: '<?= site_url('barangmasuk/itemPoKeluar') ?>',
            data: {
                [csrfToken]: csrfHash,
                po_keluar_id: poKeluarId
            },
            dataType: "json",
            success: function(response) {
                if (response.error) {
                    Swal.fire('Error', response.error, 'error');
                    return;
                }
                let data = response.sukses;
                $('#namasupplier').val(data.namasupplier);
                $('#idsupplier').val(data.idsupplier);
                $('#labelPoKeluarTerpilih').text('- ' + data.namasupplier);

                let rows = data.items.map(function(item) {
                    return '<tr class="item-po-keluar-row" style="cursor:pointer" ' +
                        'data-kode="' + escapeHtml(item.kodebarang) + '" ' +
                        'data-sisa="' + escapeHtml(item.sisa) + '">' +
                        '<td>' + escapeHtml(item.kodebarang) + '</td>' +
                        '<td>' + escapeHtml(item.brgnama) + '</td>' +
                        '<td class="text-right">' + Number(item.qty_pesan).toLocaleString('id-ID') + '</td>' +
                        '<td class="text-right">' + Number(item.qty_masuk).toLocaleString('id-ID') + '</td>' +
                        '<td class="text-right">' + Number(item.sisa).toLocaleString('id-ID') + '</td>' +
                        '</tr>';
                });
                $('#tabelItemPoKeluar tbody').html(rows.join(''));
                $('#cardItemPoKeluar').show();
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(xhr.status + '\n' + thrownError);
            }
        });
    }

    $(document).on('click', '.item-po-keluar-row', function() {
        let idgudang = $('#idgudang').val();
        if (!idgudang) {
            Swal.fire('Pesan', 'Pilih Gudang Tujuan terlebih dahulu.', 'warning');
            return;
        }
        $('#kodebarang').val($(this).data('kode'));
        $('#jml').attr('max', $(this).data('sisa')).val($(this).data('sisa'));
        ambilDataBarang();
    });

    function noPoDipilih() {
        const dipilih = $('#nofaktur').val();
        if (Array.isArray(dipilih)) {
            return dipilih.length ? String(dipilih[0]).trim() : '';
        }
        return dipilih ? String(dipilih).trim() : '';
    }

    function nofakturTerpilih() {
        if ($('#po_keluar_id').val()) {
            return $('#faktur_internal').val().trim();
        }

        const noPo = noPoDipilih();
        return noPo !== '' ? noPo : $('#faktur_internal').val().trim();
    }

    function buatNomorProdukMasuk() {
        const now = new Date();
        const pad = value => String(value).padStart(2, '0');
        return 'BM-' +
            now.getFullYear() +
            pad(now.getMonth() + 1) +
            pad(now.getDate()) + '-' +
            pad(now.getHours()) +
            pad(now.getMinutes()) +
            pad(now.getSeconds()) + '-' +
            Math.floor(Math.random() * 900 + 100);
    }

    function pastikanNomorTransaksi() {
        const noPo = noPoDipilih();
        const poKeluarId = $('#po_keluar_id').val();

        if (poKeluarId) {
            if (!$('#faktur_internal').val()) {
                $('#faktur_internal').val(buatNomorProdukMasuk());
            }
            return $('#faktur_internal').val().trim();
        }

        if (noPo) {
            return noPo;
        }

        if (!$('#faktur_internal').val()) {
            $('#faktur_internal').val(buatNomorProdukMasuk());
        }
        return $('#faktur_internal').val().trim();
    }

    function kosong() {
        // $('#nofaktur').val('');
        $('#kodebarang').val('');
        $('#berat').val('');
        $('#stok1').val('');
        $('#namabarang').val('');
        // $('#idsupplier').val('');
        // $('#gudang').val('');
        $('#idmaterial').val('');
        $('#idbarang').val('');
        $('#jml').val('1');
        $('#kodebarang').focus();

    }

    function simpanItem() {
        syncSupplierCombobox();
        syncProdukCombobox();
        let nofaktur = pastikanNomorTransaksi();
        let kodebarang = $('#kodebarang').val();
        let namabarang = $('#namabarang').val();
        let idsupplier = $('#idsupplier').val();
        let idbarang = $('#idbarang').val();
        let gudang = $('#gudang').val();
        let gdgid = $('#gdgid').val();
        let idmaterial = $('#idmaterial').val();
        let berat = $('#berat').val();
        let stok = $('#stok1').val();
        let tglfaktur = $('#tglfaktur').val();
        let jml = $('#jml').val();
        let poKeluarId = $('#po_keluar_id').val();
        let batasQtyPo = $("#jml").attr('max');
        let sumberProduk = $("#sumberProduk").val();

        if (kodebarang.length == 0) {
            Swal.fire('Error', 'Kode Barang harus diinputkan', 'error');
            kosong();
        } else if (sumberProduk === 'beli' && !poKeluarId) {
            Swal.fire('Error', 'No PO wajib dipilih untuk sumber Beli dari Supplier', 'error');
        } else if (poKeluarId && batasQtyPo && Number(jml) > Number(batasQtyPo)) {
            Swal.fire('Error', 'Qty masuk tidak boleh lebih besar dari sisa PO Keluar', 'error');
        } else {
            $.ajax({
                type: "post",
                url: '<?= site_url('barangmasuk/simpanItem') ?>',
                data: {
                    [csrfToken]: csrfHash,
                    nofaktur: nofaktur,
                    faktur_internal: $('#faktur_internal').val(),
                    po_keluar_id: poKeluarId,
                    sumber_produk: sumberProduk,
                    tglfaktur: tglfaktur,
                    kodebarang: kodebarang,
                    namabarang: namabarang,
                    idsupplier: idsupplier,
                    idbarang: idbarang,
                    gudang: gudang,
                    gdgid: gdgid,
                    idmaterial: idmaterial,
                    berat: berat,
                    stok: stok,
                    jml: jml
                },
                dataType: "json",
                success: function(response) {
                    if (response.error1) {
                        Swal.fire('Error', response.error1, 'error');
                    } else if (response.error2) {
                        Swal.fire('Error', response.error2, 'error');
                        kosong();
                    } else if (response.sukses) {
                        if (response.nofaktur && !noPoDipilih()) {
                            $('#faktur_internal').val(response.nofaktur);
                        }
                        Swal.fire({
                            title: 'Berhasil',
                            icon: 'success',
                            text: response.sukses
                        }).then((result) => {
                            if (result.isConfirmed) {
                                tampilDataTemp();
                                kosong();
                                $('#kodebarang').focus();
                            }
                        })
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert(xhr.status + '\n' + thrownError);
                }
            });
        }
    }

    function ambilDataBarang() {
        syncProdukCombobox();
        let kodebarang = $('#kodebarang').val();
        let idgudang = $('#idgudang').val();
        let idbarang = $('#idbarang').val();
        if (kodebarang.length == 0) {
            Swal.fire('Error', 'Kode Barang harus di inputkan', 'error');
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
                },
                dataType: "json",
                success: function(response) {
                    if (response.error) {
                        Swal.fire('Error', response.error, 'error');
                        kosong();
                    }

                    if (response.sukses) {
                        let data = response.sukses;

                        $('#namabarang').val(data.namabarang);
                        $('#berat').val(data.berat);
                        $('#stok1').val(data.stok);
                        $('#idmaterial').val(data.idmaterial);
                        $('#idbarang').val(data.idbarang);
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
        let faktur = nofakturTerpilih();
        let idsupplier = $('#idsupplier').val();
        let idmaterial = $('#idmaterial').val();

        $.ajax({
            type: "post",
            url: '<?= site_url('barangmasuk/tampilDataTemp') ?>',
            data: {
                [csrfToken]: csrfHash,
                nofaktur: faktur,
                faktur_internal: $('#faktur_internal').val(),
                po_keluar_id: $('#po_keluar_id').val(),
                idsupplier: idsupplier,
                idmaterial: idmaterial
            },
            dataType: "json",
            beforeSend: function() {
                $('.tampilDataTemp').html("<i class='fa fa-spin fa-spinner'></i>");
            },
            success: function(response) {
                if (response.data) {
                    $('.tampilDataTemp').html(response.data);
                    $('#idsupplier').val(idsupplier);
                    $('#idmaterial').val(idmaterial);
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(xhr.status + '\n' + thrownError)
            }
        });
    }

    function kosongProdukInput() {
        $('#kodebarangProd').val('');
        $('#kodebarangProdPilih').val('');
        $('#namabarangProd').val('');
        $('#stokProd').val('');
        $('#idbarangProd').val('');
        $('#qty_produk').val('1');
        resetMaterialProduksi();
    }

    function ambilDataBarangProd() {
        syncProdukComboboxProd();
        let kodebarang = $('#kodebarangProd').val();
        let idgudang = $('#idgudang').val();
        if (!kodebarang) {
            resetMaterialProduksi();
            return;
        }
        $.ajax({
            type: "post",
            url: '<?= site_url('produksi/ambilDataBarangProduksi') ?>',
            data: {
                [csrfToken]: csrfHash,
                kodebarang: kodebarang,
                idgudang: idgudang,
                idbarang: $('#idbarangProd').val()
            },
            dataType: "json",
            success: function(response) {
                if (response.error) {
                    Swal.fire('Error', response.error, 'error');
                    return;
                }
                let data = response.sukses;
                $('#namabarangProd').val(data.namabarang);
                $('#stokProd').val(data.stok);
                $('#idbarangProd').val(data.idbarang);
                ambilMaterialProduksi(kodebarang);
                $('#qty_produk').focus();
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(xhr.status + '\n' + thrownError);
            }
        });
    }

    function renderDaftarProduk() {
        if (daftarProduk.length === 0) {
            $('#tabelDaftarProduk tbody').html('<tr class="baris-daftar-produk-kosong"><td colspan="6" class="text-center text-muted">Belum ada produk ditambahkan</td></tr>');
            return;
        }
        let rows = daftarProduk.map(function(item, i) {
            return '<tr>' +
                '<td class="text-center">' + (i + 1) + '</td>' +
                '<td>' + escapeHtml(item.kodebarang) + '</td>' +
                '<td>' + escapeHtml(item.namabarang) + '</td>' +
                '<td>' + escapeHtml(item.materialNama || 'Tanpa pemakaian material') + '</td>' +
                '<td class="text-right">' + Number(item.qtyProduk).toLocaleString('id-ID') + '</td>' +
                '<td class="text-center">' +
                '<button type="button" class="btn btn-sm btn-danger tombol-hapus-daftar-produk" data-index="' + i + '"><i class="fa fa-trash-alt"></i></button>' +
                '</td>' +
                '</tr>';
        });
        $('#tabelDaftarProduk tbody').html(rows.join(''));
    }

    // Belum nyimpen apa-apa ke server -- cuma nambah/gabung ke daftar lokal
    // di browser. Baru beneran disimpan (stok berkurang/bertambah) pas user
    // klik "Selesai Transaksi".
    function tambahKeDaftarProduk() {
        syncProdukComboboxProd();
        let kodebarang = $('#kodebarangProd').val();
        let namabarang = $('#namabarangProd').val();
        let qtyProduk = Number($('#qty_produk').val());
        let materialid = $('#materialProduksi').prop('disabled') ? '' : ($('#materialProduksi').val() || '');
        let materialNama = $('#materialProduksi').prop('disabled')
            ? 'Tanpa pemakaian material'
            : ($('#materialProduksi option:selected').text() || '');

        if (!kodebarang) {
            Swal.fire('Pesan', 'Produk yang diproduksi belum dipilih.', 'warning');
            return;
        }
        if (!qtyProduk || qtyProduk <= 0) {
            Swal.fire('Pesan', 'Qty Diproduksi harus lebih dari 0.', 'warning');
            return;
        }
        if (!$('#materialProduksi').prop('disabled') && !materialid) {
            Swal.fire('Pesan', 'Material yang digunakan belum dipilih.', 'warning');
            return;
        }

        const idxAda = daftarProduk.findIndex(it => it.kodebarang === kodebarang && String(it.materialid || '') === String(materialid));
        if (idxAda >= 0) {
            daftarProduk[idxAda].qtyProduk += qtyProduk;
        } else {
            daftarProduk.push({
                kodebarang: kodebarang,
                namabarang: namabarang,
                qtyProduk: qtyProduk,
                materialid: materialid,
                materialNama: materialNama
            });
        }
        renderDaftarProduk();
        kosongProdukInput();
        $('#kodebarangProd').focus();
    }

    function hapusDariDaftarProduk(index) {
        daftarProduk.splice(index, 1);
        renderDaftarProduk();
    }

    function simpanProduksiBerurutan(items, index, tglProduksi, gudang) {
        if (index >= items.length) {
            Swal.fire({
                title: 'Berhasil',
                icon: 'success',
                text: items.length + ' produk berhasil disimpan. Stok material berkurang, stok produk bertambah.'
            }).then(() => {
                window.location.href = '/barangmasuk/data#tab-produksi';
            });
            return;
        }

        const item = items[index];
        $.ajax({
            type: "post",
            url: '<?= site_url('produksi/simpanOtomatis') ?>',
            data: {
                [csrfToken]: csrfHash,
                no_produksi: $('#noProduksiBatch').val(),
                tgl_produksi: tglProduksi,
                gudang: gudang,
                kodebarang: item.kodebarang,
                namabarang: item.namabarang,
                qty_produk: item.qtyProduk,
                materialid: item.materialid || ''
            },
            dataType: "json",
            success: function(response) {
                if (response.error) {
                    Swal.fire('Gagal', item.kodebarang + ': ' + response.error, 'error');
                    return;
                }
                $('#noProduksiBatch').val(response.no_produksi);
                simpanProduksiBerurutan(items, index + 1, tglProduksi, gudang);
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(xhr.status + '\n' + thrownError);
            }
        });
    }

    function terapkanSumberProduk() {
        let sumber = $('#sumberProduk').val();
        if (sumber === 'produksi') {
            $('.beli-only').hide();
            $('.produk-picker-row').hide();
            $('.produksi-only').show();
        } else {
            $('.beli-only').show();
            $('.produk-picker-row').show();
            $('.produksi-only').hide();
            $('.supplier-only').toggle(sumber === 'beli');
            if (sumber === 'adjustment' || sumber === 'produksi_pelanggan') {
                $('#nofaktur').val(null).trigger('change');
                $('#po_keluar_id').val('');
                $('#namasupplier').val('');
                $('#idsupplier').val('');
                $('#jml').removeAttr('max').val('1');
                muatItemPoKeluar('');
            }
        }
    }

    $(document).ready(function() {
        supplierCombobox = window.treInitInlineCombobox({
            box: '#supplierCombobox',
            input: '#namasupplier',
            hidden: '#idsupplier',
            menu: '#supplierComboboxMenu',
            options: supplierOptions
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
        produkComboboxProd = window.treInitInlineCombobox({
            box: '#produkComboboxProd',
            input: '#kodebarangProd',
            hidden: '#kodebarangProdPilih',
            menu: '#produkComboboxProdMenu',
            options: produkOptions,
            onSelect: function() {
                ambilDataBarangProd();
            }
        });
        $(document).off('tre:supplierAdded.supplierCombobox').on('tre:supplierAdded.supplierCombobox', function(event, supplier) {
            if (supplierCombobox && typeof supplierCombobox.addOption === 'function') {
                supplierCombobox.addOption(supplier, true);
            }
        });

        kosongProdukInput();
        renderDaftarProduk();
        terapkanSumberProduk();

        $('#sumberProduk').on('change', function() {
            terapkanSumberProduk();
            $('#kodebarang').val('');
            $('#namabarang').val('');
            $('#berat').val('');
            $('#stok1').val('');
        });

        $('#kodebarangProd').keydown(function(e) {
            if (e.keyCode == 13) {
                e.preventDefault();
                ambilDataBarangProd();
            }
        });

        $('#gudang').on('change', function() {
            if ($('#sumberProduk').val() === 'produksi' && $('#kodebarangProd').val()) {
                ambilDataBarangProd();
            }
        });

        $('#tombolSimpanProduksi').click(function(e) {
            e.preventDefault();
            tambahKeDaftarProduk();
        });

        $('#tombolResetProduksi').click(function(e) {
            e.preventDefault();
            kosongProdukInput();
        });

        $(document).on('click', '.tombol-hapus-daftar-produk', function(e) {
            e.preventDefault();
            hapusDariDaftarProduk(Number($(this).data('index')));
        });

        $('#tombolSelesaiProduksi').click(function(e) {
            e.preventDefault();

            let gudang = $('#gudang').val();
            let tglProduksi = $('#tglfaktur').val();

            if (!gudang) {
                Swal.fire('Pesan', 'Gudang Tujuan belum dipilih.', 'warning');
                return;
            }
            if (daftarProduk.length === 0) {
                Swal.fire('Pesan', 'Belum ada produk di daftar. Isi form produk lalu klik tombol tambah dulu.', 'warning');
                return;
            }

            Swal.fire({
                title: 'Selesai Transaksi',
                text: "Stok material akan berkurang dan stok produk akan bertambah untuk " + daftarProduk.length + " produk. Yakin disimpan?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Simpan!',
                cancelButtonText: 'Tidak'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }
                simpanProduksiBerurutan(daftarProduk.slice(), 0, tglProduksi, gudang);
            });
        });

        $('#nofaktur').on('select2:select', function(e) {
            $(this).val([e.params.data.id]).trigger('change');

            const poKeluarId = e.params.data.element ? $(e.params.data.element).data('id') : '';
            if (poKeluarId) {
                $('#po_keluar_id').val(poKeluarId);
                pastikanNomorTransaksi();
            } else {
                $('#po_keluar_id').val('');
                $('#faktur_internal').val('');
            }
            muatItemPoKeluar(poKeluarId || '');
        });

        $('#nofaktur').on('select2:unselect', function() {
            $('#po_keluar_id').val('');
            $('#faktur_internal').val('');
            $('#jml').removeAttr('max').val('1');
            muatItemPoKeluar('');
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

        $('#kodebarang').keydown(function(e) {
            if (e.keyCode == 13) {
                e.preventDefault();
                syncProdukCombobox();
                ambilDataBarang();
            }
        });

        $('#tombolSimpanItem').click(function(e) {
            e.preventDefault();
            simpanItem();
        });

        $('#tombolSelesaiTransaksi').click(function(e) {
            e.preventDefault();
            syncSupplierCombobox();
            let nofaktur = pastikanNomorTransaksi();
            let tglfaktur = $('#tglfaktur').val();
            let idsupplier = $('#idsupplier').val();
            let gudang = $('#gudang').val();
            let idmaterial = $('#idmaterial').val();
            let jml = $('#jml').val();
            let totalberatbarang = $('#totalberatbarang').val();
            let poKeluarId = $("#po_keluar_id").val();
            let sumberProduk = $("#sumberProduk").val();
            if (sumberProduk === 'beli' && !poKeluarId) {
                Swal.fire({
                    title: 'Pesan',
                    icon: 'warning',
                    text: 'Maaf No PO wajib dipilih untuk sumber Beli dari Supplier'
                })
            } else if (sumberProduk === 'beli' && idsupplier.length == 0) {
                Swal.fire({
                    title: 'Pesan',
                    icon: 'warning',
                    text: 'Maaf data supplier tidak boleh kosong'
                })
            } else if (gudang.length == 0) {
                Swal.fire({
                    title: 'Pesan',
                    icon: 'warning',
                    text: 'Maaf Gudang belum dipilih'
                })
            } else {
                Swal.fire({
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
                            url: '<?= site_url('barangmasuk/selesaiTransaksi') ?>',
                            data: {
                                [csrfToken]: csrfHash,
                                nofaktur: nofaktur,
                                faktur_internal: $('#faktur_internal').val(),
                                tglfaktur: tglfaktur,
                                idsupplier: idsupplier,
                                gudang: gudang,
                                idmaterial: idmaterial,
                                totalberatbarang: totalberatbarang,
                                po_keluar_id: poKeluarId,
                                sumber_produk: sumberProduk,
                            },
                            dataType: "json",
                            success: function(response) {
                                if (response.error) {
                                    Swal.fire({
                                        title: 'Error',
                                        icon: 'error',
                                        text: response.error
                                    });
                                }

                                if (response.sukses) {
                                    Swal.fire({
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
