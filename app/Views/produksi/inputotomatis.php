<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Input Produksi
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>

<button type="button" class="btn btn-warning" onclick="location.href=('/produksi/data')">
    <i class="fa fa-undo"></i> Kembali
</button>

<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>

<div class="card">
    <div class="card-header py-2">
        <strong>Catat Produksi</strong>
        <small class="text-muted d-block">
            Isi produk &amp; qty yang diproduksi saja. Material yang kepakai dihitung
            &amp; dicatat otomatis dari data Kebutuhan Material (BOM) -- nggak perlu
            input material satu-satu.
        </small>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-lg-3">
                <div class="form-group">
                    <label for="tgl_produksi">Tanggal Produksi</label>
                    <input type="date" class="form-control" id="tgl_produksi" value="<?= date('Y-m-d') ?>">
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <label for="gudang">Gudang</label>
                    <select id="gudang" class="form-control">
                        <option selected value="">-- Pilih --</option>
                        <?php foreach ($datagudang as $gdg) : ?>
                            <option value="<?= $gdg['gdgid'] ?>"><?= $gdg['gdgnama'] ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="form-group">
                    <label for="materialProduksi">Material yang Digunakan</label>
                    <select id="materialProduksi" class="form-control select2" disabled style="width: 100%;">
                        <option value="">-- Pilih produk terlebih dahulu --</option>
                    </select>
                    <small id="materialProduksiHelp" class="form-text text-muted">Material inti/default akan dipilih otomatis. Jika stok material inti habis, pilih material alternatif yang sudah ditentukan pada Produk.</small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3">
                <div class="form-group">
                    <label for="kodebarang">Kode Produk</label>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="kodebarang" disabled>
                        <input type="hidden" id="idbarang">
                        <input type="hidden" id="idgudang">
                        <input type="hidden" id="gdgid">
                        <div class="input-group-append">
                            <button class="btn btn-outline-primary" type="button" id="tombolCariBarang" disabled>
                                <i class="fa fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <label for="namabarang">Nama Produk</label>
                    <input type="text" class="form-control" id="namabarang" readonly>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group">
                    <label for="stok1">Stok Saat Ini</label>
                    <input type="number" class="form-control" id="stok1" readonly>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group">
                    <label for="qty_produk">Qty Diproduksi</label>
                    <input type="number" class="form-control" id="qty_produk" min="0.0001" step="0.0001" value="1">
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer text-right">
        <button type="button" class="btn btn-success" id="tombolSimpanProduksi">
            <i class="fa fa-save"></i> Simpan Produksi
        </button>
    </div>
</div>

<div class="viewmodal" style="display: none;"></div>

<script>
    let csrfToken = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';
    let materialProdukData = [];

    function kosong() {
        $('#kodebarang').val('');
        $('#namabarang').val('');
        $('#stok1').val('');
        $('#idbarang').val('');
        $('#qty_produk').val('1');
        materialProdukData = [];
        $('#materialProduksi').prop('disabled', true).html('<option value="">-- Pilih produk terlebih dahulu --</option>').trigger('change');
        $('#materialProduksiHelp').text('Material inti/default akan dipilih otomatis. Jika stok material inti habis, pilih material alternatif yang sudah ditentukan pada Produk.');
    }

    function formatMaterialOption(material) {
        let stok = material.stok === null || material.stok === undefined ? 'stok -' : 'stok ' + Number(material.stok).toLocaleString('id-ID');
        let berat = material.berat_per_pcs === null || material.berat_per_pcs === undefined
            ? 'berat belum diisi'
            : 'berat/pcs ' + Number(material.berat_per_pcs * 1000).toLocaleString('id-ID', { maximumFractionDigits: 4 }) + ' gram';
        let label = (material.default ? 'Default - ' : 'Alternatif - ') + material.namamaterial + ' (' + stok + ', ' + berat + ')';
        if (!material.satuan_sesuai) {
            label += ' [satuan belum sesuai]';
        }
        return label;
    }

    function tampilkanMaterialProduk(materials, defaultId, tanpaBerat) {
        materialProdukData = materials || [];
        let select = $('#materialProduksi');
        select.empty();
        if (tanpaBerat) {
            select.append('<option value="">Tidak ada pemakaian material (jasa/tanpa berat)</option>');
            select.prop('disabled', true).trigger('change');
            $('#materialProduksiHelp').text('Produk ini ditandai jasa/tanpa berat, sehingga penerimaan tidak mengurangi stok material.');
            return;
        }
        materialProdukData.forEach(function(material) {
            select.append(new Option(formatMaterialOption(material), material.materialid, Number(material.materialid) === Number(defaultId), Number(material.materialid) === Number(defaultId)));
        });
        select.prop('disabled', materialProdukData.length === 0).trigger('change');
        $('#materialProduksiHelp').text(materialProdukData.length
            ? 'Material inti/default dipilih otomatis. Jika stoknya habis atau tidak cukup, pilih material alternatif yang stoknya tersedia.'
            : 'Produk belum memiliki material inti/alternatif.');
    }

    function ambilMaterialProduk() {
        let kodebarang = $('#kodebarang').val();
        let gudang = $('#gudang').val();
        if (!kodebarang || !gudang) {
            return;
        }
        $.ajax({
            type: 'post',
            url: '<?= site_url('produksi/materialProduk') ?>',
            data: { [csrfToken]: csrfHash, kodebarang: kodebarang, gudang: gudang },
            dataType: 'json',
            success: function(response) {
                if (response.error) {
                    $('#materialProduksi').prop('disabled', true).html('<option value="">Material belum tersedia</option>').trigger('change');
                    showBootstrapModal('Material Produk', response.error, 'warning');
                    return;
                }
                let data = response.sukses;
                tampilkanMaterialProduk(data.materials, data.default_material_id, data.tanpa_berat);
            },
            error: function() {
                $('#materialProduksi').prop('disabled', true).html('<option value="">Material belum tersedia</option>').trigger('change');
                showBootstrapModal('Material Produk', 'Data material produk tidak dapat dimuat.', 'error');
            }
        });
    }

    function materialIdTerpilih() {
        return $('#materialProduksi').val() || '';
    }

    function ambilDataBarang() {
        let kodebarang = $('#kodebarang').val();
        let idgudang = $('#idgudang').val();
        if (kodebarang.length == 0) {
            return;
        }
        $.ajax({
            type: "post",
            url: '<?= site_url('produksi/ambilDataBarangProduksi') ?>',
            data: {
                [csrfToken]: csrfHash,
                kodebarang: kodebarang,
                idgudang: idgudang
            },
            dataType: "json",
            success: function(response) {
                if (response.error) {
                    showBootstrapModal('Error', response.error, 'error');
                    kosong();
                    return;
                }
                let data = response.sukses;
                $('#namabarang').val(data.namabarang);
                $('#stok1').val(data.stok);
                ambilMaterialProduk();
                $('#qty_produk').focus();
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(xhr.status + '\n' + thrownError);
            }
        });
    }

    function simpanProduksi() {
        let tglProduksi = $('#tgl_produksi').val();
        let gudang = $('#gudang').val();
        let kodebarang = $('#kodebarang').val();
        let namabarang = $('#namabarang').val();
        let qtyProduk = $('#qty_produk').val();
        let materialid = materialIdTerpilih();

        if (!tglProduksi) {
            showBootstrapModal('Pesan', 'Tanggal Produksi belum diisi.', 'warning');
            return;
        }
        if (!gudang) {
            showBootstrapModal('Pesan', 'Gudang belum dipilih.', 'warning');
            return;
        }
        if (!kodebarang) {
            showBootstrapModal('Pesan', 'Produk yang diproduksi belum dipilih.', 'warning');
            return;
        }
        if (!qtyProduk || Number(qtyProduk) <= 0) {
            showBootstrapModal('Pesan', 'Qty Diproduksi harus lebih dari 0.', 'warning');
            return;
        }
        if ($('#materialProduksi').prop('disabled') === false && !materialid) {
            showBootstrapModal('Pesan', 'Material yang digunakan belum dipilih.', 'warning');
            return;
        }

        showBootstrapModal({
            title: 'Simpan Produksi',
            text: "Material yang kepakai akan dihitung otomatis dari Kebutuhan Material, stok material berkurang dan stok produk bertambah. Yakin disimpan?",
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
            $.ajax({
                type: "post",
                url: '<?= site_url('produksi/simpanOtomatis') ?>',
                data: {
                    [csrfToken]: csrfHash,
                    tgl_produksi: tglProduksi,
                    gudang: gudang,
                    kodebarang: kodebarang,
                    namabarang: namabarang,
                    qty_produk: qtyProduk,
                    materialid: materialid
                },
                dataType: "json",
                beforeSend: function() {
                    $('#tombolSimpanProduksi').prop('disabled', true);
                },
                success: function(response) {
                    if (response.error) {
                        showBootstrapModal('Gagal', response.error, 'error');
                        return;
                    }
                    let pesan = response.sukses;
                    if (response.peringatan && response.peringatan.length) {
                        pesan += '<br><br><small class="text-warning">' + response.peringatan.join('<br>') + '</small>';
                    }
                    showBootstrapModal({
                        title: 'Berhasil',
                        icon: response.peringatan && response.peringatan.length ? 'warning' : 'success',
                        html: pesan
                    }).then(() => {
                        window.location.href = '/produksi/data';
                    });
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert(xhr.status + '\n' + thrownError);
                },
                complete: function() {
                    $('#tombolSimpanProduksi').prop('disabled', false);
                }
            });
        });
    }

    $(document).ready(function() {
        $('#gudang').on('change', function() {
            let selectedGudangId = this.value;
            $('#idgudang').val(selectedGudangId);
            $('#gdgid').val(selectedGudangId);

            if (selectedGudangId !== "") {
                $('#tombolCariBarang').removeAttr('disabled');
            } else {
                $('#tombolCariBarang').attr('disabled', 'disabled');
            }

            kosong();
        });

        $('#tombolCariBarang').click(function(e) {
            e.preventDefault();
            $.ajax({
                url: '<?= site_url('barangmasuk/modalCariBarang') ?>',
                dataType: "json",
                success: function(response) {
                    if (response.data) {
                        $('.viewmodal').html(response.data).show();
                        $('#modalcaribarang').modal('show');
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert(xhr.status + '\n' + thrownError);
                }
            });
        });

        $('#tombolSimpanProduksi').click(function(e) {
            e.preventDefault();
            simpanProduksi();
        });
    });
</script>

<?= $this->endSection('isi') ?>
