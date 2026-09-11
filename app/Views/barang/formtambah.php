<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Form Tambah Data Produk
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>

<button type="button" class="btn btn-warning" onclick="location.href=('/barang/index')">
    <i class="fa fa-undo"></i> Kembali
</button>

<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<?= form_open_multipart('barang/simpandata', ['id' => 'formProduk']) ?>
<?= session()->getFlashdata('error') ?>
<?= session()->getFlashdata('sukses') ?>

<div class="card mb-3">
    <div class="card-header py-2"><strong>Informasi Dasar</strong></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="kodebarang">Kode Produk</label>
                    <input type="text" class="form-control" id="kodebarang" placeholder="Input Kode Produk" name="kodebarang" autofocus>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="namabarang">Nama Produk</label>
                    <input type="text" class="form-control" id="namabarang" placeholder="Input Nama Produk" name="namabarang" autofocus>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="idpel">Pelanggan</label>
                    <select name="idpel" id="idpel" class="tre-combobox-source d-none">
                        <option selected value="">-- Pilih Pelanggan --</option>
                        <?php foreach ($datapelanggan as $pel) : ?>
                            <option value="<?= $pel['pelid'] ?>"><?= $pel['pelnama'] ?></option>
                        <?php endforeach ?>
                    </select>
                    <div class="tre-combobox" data-target="idpel">
                        <input type="text" class="form-control tre-combobox-input" placeholder="-- Pilih Pelanggan --" autocomplete="off">
                        <div class="tre-combobox-menu"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="kategori">Kategori</label>
                    <select name="kategori" id="kategori" class="tre-combobox-source d-none">
                        <option selected value="">-- Pilih Kategori --</option>
                        <?php foreach ($datakategori as $kat) : ?>
                            <option value="<?= $kat['katid'] ?>"><?= $kat['katnama'] ?></option>
                        <?php endforeach ?>
                    </select>
                    <div class="tre-combobox" data-target="kategori">
                        <input type="text" class="form-control tre-combobox-input" placeholder="-- Pilih Kategori --" autocomplete="off">
                        <div class="tre-combobox-menu"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="satuan">Satuan</label>
                    <select name="satuan" id="satuan" class="tre-combobox-source d-none">
                        <option selected value="">-- Pilih Satuan --</option>
                        <?php foreach ($datasatuan as $sat) : ?>
                            <option value="<?= $sat['satid'] ?>"><?= $sat['satnama'] ?></option>
                        <?php endforeach ?>
                    </select>
                    <div class="tre-combobox" data-target="satuan">
                        <input type="text" class="form-control tre-combobox-input" placeholder="-- Pilih Satuan --" autocomplete="off">
                        <div class="tre-combobox-menu"></div>
                    </div>
                    <input type="hidden" name="satuanberat" id="satuanberat">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group pt-md-4 mt-md-2">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="tanpaBerat" name="tanpa_berat" value="1">
                        <label class="custom-control-label font-weight-bold" for="tanpaBerat">Produk jasa / tanpa berat</label>
                    </div>
                    <small class="form-text text-muted">Pakai untuk jasa jahit/konsinyasi yang belum punya data berat material atau berat produk jadi.</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="sumberMaterial">Sumber Material Produksi</label>
                    <select name="sumber_material" id="sumberMaterial" class="form-control">
                        <option value="tre" <?= old('sumber_material', 'tre') === 'tre' ? 'selected' : '' ?>>Material TRE</option>
                        <option value="vendor" <?= old('sumber_material') === 'vendor' ? 'selected' : '' ?>>Material dari Customer</option>
                        <option value="beli_jadi" <?= old('sumber_material') === 'beli_jadi' ? 'selected' : '' ?>>Beli Barang Jadi (Full dari Vendor)</option>
                    </select>
                    <small class="form-text text-muted">Pilih customer kalau material tidak masuk stok TRE dan hanya dicatat sebagai referensi produk. Pilih "Beli Barang Jadi" kalau produk ini nggak pernah diproduksi di TRE sama sekali -- selalu dibeli barang jadi dari vendor lewat PO Out, jadi nggak butuh material apapun.</small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header py-2"><strong>Harga &amp; Stok</strong></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="harga">Harga Produk</label>
                    <input type="number" class="form-control" id="harga" placeholder="Input Harga Produk" name="harga" autofocus>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="minstok">Minimal Stok Produk</label>
                    <input type="number" class="form-control" id="minstok" placeholder="Input Minimal Stok Produk" name="minstok" autofocus>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header py-2"><strong>Material &amp; Berat</strong></div>
    <div class="card-body">
        <div class="form-group">
            <label for="materialUtama">Material Inti / Default</label>
            <select name="material_utama" id="materialUtama" class="form-control select2" data-placeholder="-- Pilih material inti --" style="width: 100%;">
                <option value="">-- Pilih material inti --</option>
                <?php foreach ($datamaterial as $mat) : ?>
                    <option value="<?= $mat['matid'] ?>" data-matsatid="<?= $mat['matsatid'] ?>" <?= (string) old('material_utama') === (string) $mat['matid'] ? 'selected' : '' ?>><?= esc($mat['matnama']) ?></option>
                <?php endforeach ?>
            </select>
            <small class="form-text text-muted">Material ini menjadi pilihan default saat penerimaan hasil produksi. Jika stoknya habis, material alternatif dapat dipilih.</small>
        </div>
        <div class="form-group">
            <label for="materialAlternatif">Material Alternatif</label>
            <select name="material_alternatif[]" id="materialAlternatif" class="form-control select2" multiple="multiple" data-placeholder="-- Pilih material alternatif --" style="width: 100%;">
                <?php foreach ($datamaterial as $mat) : ?>
                    <option value="<?= $mat['matid'] ?>" data-matsatid="<?= $mat['matsatid'] ?>" <?= in_array((int) $mat['matid'], array_map('intval', (array) old('material_alternatif')), true) ? 'selected' : '' ?>><?= esc($mat['matnama']) ?></option>
                <?php endforeach ?>
            </select>
            <small class="form-text text-muted">Alternatif memakai berat per pcs masing-masing. Material inti dan alternatif tidak dikurangi bersamaan; pilih salah satu saat produksi.</small>
            <div id="materialHelp" class="form-text text-muted">Material inti wajib untuk produk barang biasa. Untuk produk jasa/tanpa berat, material boleh hanya sebagai referensi atau dikosongkan.</div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Berat Material Terpakai <small class="text-muted">(input dalam gram)</small></label>
                    <div id="materialBeratContainer" class="berat-material-container text-muted">
                        Pilih material terlebih dahulu.
                    </div>
                    <small class="form-text text-muted">Isi berat tiap material yang dibutuhkan untuk membuat 1 pcs produk ini (referensi, bisa beda dari berat produk jadi kalau ada susut/kepotong).</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="totalMaterialTerpakai">Total Material Terpakai <small class="text-muted">(referensi, Kg)</small></label>
                    <input type="number" step="0.0001" min="0" class="form-control" id="totalMaterialTerpakai" placeholder="0.0000" readonly>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="wise">Wise <small class="text-muted">(% material yang kebuang/susut pas produksi)</small></label>
                    <input type="number" step="0.01" min="0" max="100" class="form-control" id="wise" name="wise" placeholder="mis. 12.5">
                    <small class="form-text text-muted">Persentase dari Total Material Terpakai yang kebuang/susut jadi waste pas produksi. Dipakai buat nyaranin Berat 1 Pcs Produk Jadi di bawah, dan buat laporan Material Terbuang. Opsional, kosongin kalau tidak tahu.</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="beratProdukJadi">Berat 1 Pcs Produk Jadi <small class="text-muted">(input dalam gram)</small></label>
                    <input type="number" step="0.0001" min="0.0001" class="form-control" id="beratProdukJadi" name="berat_produk_jadi" placeholder="Berat produk jadi dalam gram" required>
                    <small class="form-text text-muted">Berat aktual produk setelah jadi. Otomatis disarankan dari Total Material Terpakai x (1 - Wise%), tapi boleh ditimpa manual kalau perlu. Ini yang dipakai buat hitung berat pengiriman.</small>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="mb-1">Hasil Kalkulasi</label>
            <div class="berat-material-container">
                <div class="row">
                    <div class="col-md-3"><strong>PCS/KG:</strong> <span id="hasilPcsPerKg">-</span></div>
                    <div class="col-md-3"><strong>Estimasi Waste/Pcs:</strong> <span id="hasilWastePcs">-</span></div>
                    <div class="col-md-3"><strong>Harga Material Terakhir/KG:</strong> <span id="hasilHargaMaterialKg">-</span></div>
                    <div class="col-md-3"><strong>Harga Material/PCS:</strong> <span id="hasilHargaMaterialPcs">-</span></div>
                </div>
                <small class="form-text text-muted">Dihitung otomatis dari Total Material Terpakai, Wise, dan harga pembelian material terakhir -- bukan angka tersimpan, selalu ter-update.</small>
            </div>
        </div>
    </div>
</div>

<div class="form-group">
    <button type="submit" class="btn btn-success">Simpan</button>&nbsp;
    <button type="reset" class="btn btn-warning">Reset</button>
</div>
<?= form_close() ?>

<script>
    var materialBeratContainer = document.getElementById('materialBeratContainer');
    var totalBeratElement = document.getElementById('totalMaterialTerpakai');
    var beratProdukJadiElement = document.getElementById('beratProdukJadi');
    var tanpaBeratElement = document.getElementById('tanpaBerat');
    var sumberMaterialElement = document.getElementById('sumberMaterial');
    var materialUtamaElement = document.getElementById('materialUtama');
    var materialAlternatifElement = document.getElementById('materialAlternatif');

    var GRAM_KE_KG = 1000;
    var beratProdukJadiManual = false;

    function hitungTotalBerat() {
        var totalGram = 0;
        materialBeratContainer.querySelectorAll('.berat-material').forEach(function(input) {
            totalGram += parseFloat(input.value) || 0;
        });
        totalBeratElement.value = (totalGram / GRAM_KE_KG).toFixed(4);
        hitungKalkulasiWise();
    }

    function tampilkanBeratMaterial() {
        if (tanpaBeratElement.checked || sumberMaterialElement.value === 'vendor' || sumberMaterialElement.value === 'beli_jadi') {
            var pesan = sumberMaterialElement.value === 'vendor'
                ? 'Material dari customer: detail berat material tidak wajib dan tidak dihitung sebagai kebutuhan material TRE.'
                : sumberMaterialElement.value === 'beli_jadi'
                ? 'Beli barang jadi dari vendor: material tidak dipakai, jadi tidak perlu diisi.'
                : 'Produk jasa/tanpa berat: detail berat material tidak wajib diisi.';
            materialBeratContainer.innerHTML = '<span class="text-muted">' + pesan + '</span>';
            totalBeratElement.value = '0.0000';
            return;
        }

        var selected = [];
        if (materialUtamaElement.value) {
            selected.push(materialUtamaElement.options[materialUtamaElement.selectedIndex]);
        }
        Array.from(materialAlternatifElement.selectedOptions).forEach(function(opt) {
            if (!selected.some(function(item) { return item.value === opt.value; })) {
                selected.push(opt);
            }
        });
        materialBeratContainer.innerHTML = '';

        if (selected.length === 0) {
            materialBeratContainer.innerHTML = '<span class="text-muted">Pilih material terlebih dahulu.</span>';
            totalBeratElement.value = '';
            return;
        }

        selected.forEach(function(opt) {
            var wrapper = document.createElement('div');
            wrapper.className = 'form-group mb-2';

            var label = document.createElement('label');
            label.textContent = opt.textContent;

            var input = document.createElement('input');
            input.type = 'number';
            input.step = '0.0001';
            input.min = '0.0001';
            input.required = true;
            input.className = 'form-control berat-material';
            input.name = 'berat_material[' + opt.value + ']';
            input.placeholder = 'Berat dalam gram';
            input.addEventListener('input', hitungTotalBerat);

            wrapper.appendChild(label);
            wrapper.appendChild(input);
            materialBeratContainer.appendChild(wrapper);
        });

        hitungTotalBerat();
    }

    function toggleTanpaBerat() {
        var isVendorMaterial = sumberMaterialElement.value === 'vendor';
        if (isVendorMaterial) {
            tanpaBeratElement.checked = true;
        }
        var isTanpaBerat = tanpaBeratElement.checked || isVendorMaterial;
        beratProdukJadiElement.required = !isTanpaBerat;
        beratProdukJadiElement.disabled = isTanpaBerat;
        beratProdukJadiElement.value = isTanpaBerat ? '0' : beratProdukJadiElement.value;
        materialUtamaElement.required = !isTanpaBerat && sumberMaterialElement.value !== 'beli_jadi';
        document.getElementById('materialHelp').textContent = isVendorMaterial
            ? 'Material tetap boleh dipilih sebagai referensi, tapi stok/kebutuhan material TRE tidak akan ikut dihitung.'
            : sumberMaterialElement.value === 'beli_jadi'
            ? 'Produk dibeli jadi dari vendor: material tidak perlu diisi sama sekali.'
            : isTanpaBerat
            ? 'Material boleh dipilih sebagai referensi, tapi tidak wajib dan tidak perlu berat.'
            : 'Material inti wajib dipilih. Material inti dan alternatif dipakai sebagai pilihan satu-per-satu saat produksi.';
        tampilkanBeratMaterial();
    }

    var wiseElement = document.getElementById('wise');
    var hasilPcsPerKgElement = document.getElementById('hasilPcsPerKg');
    var hasilWastePcsElement = document.getElementById('hasilWastePcs');
    var hasilHargaMaterialKgElement = document.getElementById('hasilHargaMaterialKg');
    var hasilHargaMaterialPcsElement = document.getElementById('hasilHargaMaterialPcs');
    var hargaMaterialTerakhirKg = null;

    function formatRupiahKalkulasi(value) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(value));
    }

    // Total Material Terpakai (kg, sudah termasuk bagian yang bakal kebuang)
    // + Wise% -> Berat Produk Jadi disaranin otomatis (boleh ditimpa manual),
    // dan sisanya (Total Material Terpakai x Wise%) itu estimasi waste-nya.
    function hitungKalkulasiWise() {
        var totalMaterialKg = parseFloat(totalBeratElement.value) || 0;
        var wisePersen = parseFloat(wiseElement.value) || 0;

        if (totalMaterialKg <= 0) {
            hasilPcsPerKgElement.textContent = '-';
            hasilWastePcsElement.textContent = '-';
            hasilHargaMaterialPcsElement.textContent = '-';
            return;
        }

        hasilPcsPerKgElement.textContent = (1 / totalMaterialKg).toLocaleString('id-ID', { maximumFractionDigits: 4 });

        var wasteKg = totalMaterialKg * (wisePersen / 100);
        var beratProdukJadiSaranKg = Math.max(totalMaterialKg - wasteKg, 0);
        hasilWastePcsElement.textContent = (wasteKg * GRAM_KE_KG).toLocaleString('id-ID', { maximumFractionDigits: 4 }) + ' gram';

        if (!beratProdukJadiManual && !beratProdukJadiElement.disabled) {
            beratProdukJadiElement.value = (beratProdukJadiSaranKg * GRAM_KE_KG).toFixed(4);
        }

        hasilHargaMaterialPcsElement.textContent = hargaMaterialTerakhirKg !== null
            ? formatRupiahKalkulasi(totalMaterialKg * hargaMaterialTerakhirKg)
            : '-';
    }

    function ambilHargaMaterialTerakhir() {
        var selectedOption = materialUtamaElement.options[materialUtamaElement.selectedIndex];
        if (!selectedOption || !selectedOption.value) {
            hargaMaterialTerakhirKg = null;
            hasilHargaMaterialKgElement.textContent = '-';
            hitungKalkulasiWise();
            return;
        }

        $.getJSON('/barang/hargaMaterialTerakhir', { matid: selectedOption.value }, function(response) {
            hargaMaterialTerakhirKg = response.harga !== null && response.harga !== undefined ? Number(response.harga) : null;
            hasilHargaMaterialKgElement.textContent = hargaMaterialTerakhirKg !== null
                ? formatRupiahKalkulasi(hargaMaterialTerakhirKg)
                : 'Belum ada histori pembelian';
            hitungKalkulasiWise();
        }).fail(function() {
            hargaMaterialTerakhirKg = null;
            hasilHargaMaterialKgElement.textContent = '-';
            hitungKalkulasiWise();
        });
    }

    $('#materialUtama, #materialAlternatif').on('change', function() {
        var coreId = materialUtamaElement.value;
        Array.from(materialAlternatifElement.options).forEach(function(option) {
            option.disabled = coreId !== '' && option.value === coreId;
        });
        if (coreId) {
            $('#materialAlternatif').trigger('change.select2');
            document.getElementById('satuanberat').value = materialUtamaElement.options[materialUtamaElement.selectedIndex].getAttribute('data-matsatid');
        }
        tampilkanBeratMaterial();
        ambilHargaMaterialTerakhir();
    });

    beratProdukJadiElement.addEventListener('input', function() {
        // Field beneran dikosongin (bukan cuma lagi ketik angka baru) --
        // balikin ke mode saran otomatis lagi, jangan nyangkut manual
        // selamanya cuma gara-gara sempet dihapus.
        var kosong = beratProdukJadiElement.value.trim() === '';
        beratProdukJadiManual = !kosong;
        if (kosong) {
            hitungKalkulasiWise();
        }
    });
    wiseElement.addEventListener('input', hitungKalkulasiWise);

    tanpaBeratElement.addEventListener('change', toggleTanpaBerat);
    sumberMaterialElement.addEventListener('change', toggleTanpaBerat);

    // Input berat per material ditampilkan & diisi dalam gram (lebih mudah
    // buat angka kecil), tapi yang beneran dikirim ke server harus dalam Kg
    // (satuan yang dipakai tabel berat/berat_material) -- jadi dikonversi
    // pas submit, bukan pas diketik, biar tampilannya tetap gram terus.
    document.getElementById('formProduk').addEventListener('submit', function() {
        syncTreComboboxValues();
        if (tanpaBeratElement.checked || sumberMaterialElement.value === 'vendor' || sumberMaterialElement.value === 'beli_jadi') {
            beratProdukJadiElement.disabled = false;
            beratProdukJadiElement.value = '0';
            return;
        }

        materialBeratContainer.querySelectorAll('.berat-material').forEach(function(input) {
            var gram = parseFloat(input.value) || 0;
            input.value = (gram / GRAM_KE_KG).toFixed(6);
        });
        var gramProdukJadi = parseFloat(beratProdukJadiElement.value) || 0;
        beratProdukJadiElement.value = (gramProdukJadi / GRAM_KE_KG).toFixed(6);
    });

    toggleTanpaBerat();

    // Keterangan panjang di bawah tiap field disembunyiin default biar form
    // nggak kelihatan penuh -- diganti link "Info" kecil yang bisa
    // di-klik buat buka-tutup. Otomatis, nggak perlu sentuh HTML tiap field.
    document.querySelectorAll('.form-text.text-muted').forEach(function(el) {
        var toggle = document.createElement('a');
        toggle.href = '#';
        toggle.className = 'form-text-toggle';
        toggle.setAttribute('aria-expanded', 'false');
        toggle.innerHTML = '<i class="fas fa-info-circle"></i> Info <i class="fas fa-chevron-down"></i>';
        el.classList.add('d-none');
        el.parentNode.insertBefore(toggle, el);
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            var tersembunyi = el.classList.toggle('d-none');
            toggle.setAttribute('aria-expanded', tersembunyi ? 'false' : 'true');
        });
    });
</script>

<style>
    .form-text-toggle {
        align-items: center;
        color: #6c757d;
        display: inline-flex;
        font-size: .8rem;
        gap: 4px;
        margin-top: .25rem;
        text-decoration: none;
    }

    .form-text-toggle:hover,
    .form-text-toggle:focus {
        color: #495057;
        text-decoration: none;
    }

    .form-text-toggle .fa-chevron-down {
        font-size: .65rem;
        transition: transform .15s ease;
    }

    .form-text-toggle[aria-expanded="true"] .fa-chevron-down {
        transform: rotate(180deg);
    }

    .tre-combobox {
        position: relative;
    }

    .tre-combobox-menu {
        background: #fff;
        border: 1px solid #80bdff;
        border-radius: 0 0 0.25rem 0.25rem;
        box-shadow: 0 0.35rem 0.75rem rgba(15, 23, 42, .12);
        display: none;
        left: 0;
        max-height: 15rem;
        overflow-y: auto;
        position: absolute;
        right: 0;
        top: calc(100% - 1px);
        z-index: 1050;
    }

    .tre-combobox.is-open .tre-combobox-input {
        border-color: #80bdff;
        border-bottom-left-radius: 0;
        border-bottom-right-radius: 0;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .tre-combobox.is-open .tre-combobox-menu {
        display: block;
    }

    .tre-combobox-option {
        cursor: pointer;
        padding: .55rem .85rem;
    }

    .tre-combobox-option:hover,
    .tre-combobox-option.is-active {
        background: #0d6efd;
        color: #fff;
    }

    .tre-combobox-empty {
        color: #6c757d;
        padding: .55rem .85rem;
    }

    #materialUtama + .select2-container .select2-selection--multiple {
        height: calc(2.25rem + 2px) !important;
        min-height: calc(2.25rem + 2px) !important;
        padding: 0.375rem 0.75rem !important;
        background-color: #fff !important;
        border: 1px solid #ced4da !important;
        border-radius: 0.25rem !important;
        box-sizing: border-box;
        overflow: hidden;
    }

    #materialUtama + .select2-container .select2-selection__rendered {
        display: flex !important;
        align-items: center;
        height: 100%;
        padding: 0 !important;
        margin: 0 !important;
        overflow-x: auto;
    }

    #materialUtama + .select2-container .select2-search--inline {
        display: flex;
        align-items: center;
    }

    #materialUtama + .select2-container .select2-search__field {
        height: auto !important;
        margin: 0 !important;
        padding: 0 !important;
        border: 0 !important;
    }

    #materialUtama + .select2-container .select2-selection__choice {
        color: #000 !important;
        background-color: #f1f1f1 !important;
        border: 1px solid #ced4da !important;
        margin-top: 0 !important;
    }

    #materialUtama + .select2-container .select2-selection__choice__remove {
        color: #000 !important;
    }

    #materialUtama + .select2-container.select2-container--focus
    .select2-selection--multiple {
        border-color: #80bdff !important;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .berat-material-container {
        border: 1px dashed #ced4da;
        border-radius: 0.25rem;
        padding: 0.6rem 0.75rem;
        background-color: #f8f9fa;
    }

    .berat-material-container .form-group:last-child {
        margin-bottom: 0;
    }
</style>
<script>
    function initTreComboboxes() {
        $('.tre-combobox').each(function() {
            const $box = $(this);
            const $select = $('#' + $box.data('target'));
            const $input = $box.find('.tre-combobox-input');
            const $menu = $box.find('.tre-combobox-menu');

            function options() {
                return $select.find('option').map(function() {
                    return {
                        value: this.value,
                        text: $(this).text()
                    };
                }).get().filter(function(option) {
                    return option.value !== '';
                });
            }

            function render(query) {
                const normalized = (query || '').toLowerCase();
                const filtered = options().filter(function(option) {
                    return option.text.toLowerCase().includes(normalized);
                });

                $menu.empty();
                if (filtered.length === 0) {
                    $menu.append('<div class="tre-combobox-empty">Tidak ada data yang cocok.</div>');
                    return;
                }

                filtered.forEach(function(option, index) {
                    $('<div class="tre-combobox-option"></div>')
                        .toggleClass('is-active', index === 0)
                        .text(option.text)
                        .attr('data-value', option.value)
                        .appendTo($menu);
                });
            }

            function setSelected(value, text) {
                $select.val(value).trigger('change');
                $input.val(text);
                $box.removeClass('is-open');
            }

            const selectedText = $select.find('option:selected').val() ? $select.find('option:selected').text() : '';
            $input.val(selectedText);

            $input.on('focus input', function() {
                render(this.value);
                $box.addClass('is-open');
            });

            $input.on('keydown', function(event) {
                const $active = $menu.find('.tre-combobox-option.is-active');
                if (event.key === 'Enter' && $active.length) {
                    event.preventDefault();
                    setSelected($active.data('value'), $active.text());
                }
            });

            $menu.on('mousedown', '.tre-combobox-option', function(event) {
                event.preventDefault();
                setSelected($(this).data('value'), $(this).text());
            });

            $input.on('blur', function() {
                window.setTimeout(function() {
                    const typed = $input.val().trim().toLowerCase();
                    const match = options().find(function(option) {
                        return option.text.toLowerCase() === typed;
                    });
                    if (match) {
                        setSelected(match.value, match.text);
                    } else if (!typed) {
                        $select.val('');
                    }
                    $box.removeClass('is-open');
                }, 120);
            });
        });
    }

    function syncTreComboboxValues() {
        $('.tre-combobox').each(function() {
            const $box = $(this);
            const $select = $('#' + $box.data('target'));
            const typed = $box.find('.tre-combobox-input').val().trim().toLowerCase();
            let matchedValue = '';

            $select.find('option').each(function() {
                if (this.value !== '' && $(this).text().trim().toLowerCase() === typed) {
                    matchedValue = this.value;
                    return false;
                }
            });

            $select.val(matchedValue);
        });
    }

    $(document).ready(initTreComboboxes);
</script>
<?= $this->endSection('isi') ?>
