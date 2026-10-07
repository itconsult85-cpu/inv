<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Form Edit Data Produk
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>

<button type="button" class="btn btn-warning" onclick="location.href=('/barang/index')">
    <i class="fa fa-undo"></i> Kembali
</button>

<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<?= form_open('barang/updatedata', ['id' => 'formProduk']) ?>
<?php
$flashError = session()->getFlashdata('error');
$flashSukses = session()->getFlashdata('sukses');
if (is_array($flashError)) {
    $flashError = '<div class="alert alert-danger"><ul class="mb-0"><li>' . implode('</li><li>', array_map('esc', $flashError)) . '</li></ul></div>';
}
if (is_array($flashSukses)) {
    $flashSukses = '<div class="alert alert-success"><ul class="mb-0"><li>' . implode('</li><li>', array_map('esc', $flashSukses)) . '</li></ul></div>';
}
?>
<?= $flashError ?>
<?= $flashSukses ?>
<?php
$kodeDapatDiubah = (bool) ($kodeDapatDiubah ?? false);
$pemakaianKodeProduk = $pemakaianKodeProduk ?? [];
$labelPemakaianKode = implode(', ', array_map(static function ($row) {
    return ($row['label'] ?? '-') . ' (' . ($row['jumlah'] ?? 0) . ')';
}, $pemakaianKodeProduk));
?>

<div class="card mb-3">
    <div class="card-header py-2"><strong>Informasi Dasar</strong></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="kodebarang">Kode Produk</label>
                    <input type="text" class="form-control" id="kodebarang" name="kodebarang" value="<?= esc($kodebarang) ?>" <?= $kodeDapatDiubah ? '' : 'readonly' ?>>
                    <?php if ($kodeDapatDiubah) : ?>
                        <small class="text-muted">Bisa diubah karena belum dipakai transaksi.</small>
                    <?php else : ?>
                        <small class="text-muted">Tidak bisa diubah, sudah dipakai di: <?= esc($labelPemakaianKode ?: 'transaksi lain') ?>.</small>
                    <?php endif ?>
                    <input type="hidden" class="form-control" id="old_kodebarang" name="old_kodebarang" value="<?= esc($kodebarangToken ?? '') ?>">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="namabarang">Nama Produk</label>
                    <input type="text" class="form-control" id="namabarang" placeholder="Input Nama Produk" name="namabarang" value="<?= $namabarang ?>" autofocus>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="idpel">Pelanggan</label>
                    <select name="idpel" id="idpel" class="tre-combobox-source d-none">
                        <?php foreach ($datapelanggan as $pel) : ?>
                            <?php if ($pel['pelid'] == $idpel) : ?>
                                <option selected value="<?= $pel['pelid'] ?>"><?= $pel['pelnama'] ?></option>
                            <?php else : ?>
                                <option value="<?= $pel['pelid'] ?>"><?= $pel['pelnama'] ?></option>
                            <?php endif; ?>
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
                        <?php foreach ($datakategori as $kat) : ?>
                            <?php if ($kat['katid'] == $kategori) : ?>
                                <option selected value="<?= $kat['katid'] ?>"><?= $kat['katnama'] ?></option>
                            <?php else : ?>
                                <option value="<?= $kat['katid'] ?>"><?= $kat['katnama'] ?></option>
                            <?php endif; ?>
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
                    <label for="satuan">Satuan Produk</label>
                    <select name="satuan" id="satuan" class="tre-combobox-source d-none">
                        <?php foreach ($datasatuan as $sat) : ?>
                            <?php if ($sat['satid'] == $satuan) : ?>
                                <option selected value="<?= $sat['satid'] ?>"><?= $sat['satnama'] ?></option>
                            <?php else : ?>
                                <option value="<?= $sat['satid'] ?>"><?= $sat['satnama'] ?></option>
                            <?php endif; ?>
                        <?php endforeach ?>
                    </select>
                    <div class="tre-combobox" data-target="satuan">
                        <input type="text" class="form-control tre-combobox-input" placeholder="-- Pilih Satuan --" autocomplete="off">
                        <div class="tre-combobox-menu"></div>
                    </div>
                    <input type="hidden" name="satuanberat" id="satuanberat" value="<?= $satuanberat ?>">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group pt-md-4 mt-md-2">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="tanpaBerat" name="tanpa_berat" value="1" <?= !empty($tanpaBerat) ? 'checked' : '' ?>>
                        <label class="custom-control-label font-weight-bold" for="tanpaBerat">Produk jasa / tanpa berat</label>
                    </div>
                    <small class="form-text text-muted">Pakai untuk jasa jahit/konsinyasi yang belum punya data berat material atau berat produk jadi.</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="sumberMaterial">Sumber Material Produksi</label>
                    <select name="sumber_material" id="sumberMaterial" class="form-control">
                        <option value="tre" <?= ($sumberMaterial ?? 'tre') === 'tre' ? 'selected' : '' ?>>Material TRE</option>
                        <option value="vendor" <?= ($sumberMaterial ?? 'tre') === 'vendor' ? 'selected' : '' ?>>Material dari Customer</option>
                        <option value="beli_jadi" <?= ($sumberMaterial ?? 'tre') === 'beli_jadi' ? 'selected' : '' ?>>Beli Barang Jadi (Full dari Vendor)</option>
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
                    <input type="number" class="form-control" id="harga" placeholder="Input Harga Produk" name="harga" value="<?= $harga ?>" autofocus>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="minstok">Minimal Stok Produk</label>
                    <input type="number" class="form-control" id="minstok" name="minstok" value="<?= $minstok ?>" autofocus>
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
                    <option value="<?= $mat['matid'] ?>" data-matsatid="<?= $mat['matsatid'] ?>" <?= (int) ($materialUtama ?? 0) === (int) $mat['matid'] ? 'selected' : '' ?>><?= esc($mat['matnama']) ?></option>
                <?php endforeach ?>
            </select>
            <small class="form-text text-muted">Material ini menjadi pilihan default saat penerimaan hasil produksi. Jika stoknya habis, material alternatif dapat dipilih.</small>
        </div>
        <div class="form-group">
            <label for="materialAlternatif">Material Alternatif</label>
            <select name="material_alternatif[]" id="materialAlternatif" class="form-control select2" multiple="multiple" data-placeholder="-- Pilih material alternatif --" style="width: 100%;">
                <?php foreach ($datamaterial as $mat) : ?>
                    <option value="<?= $mat['matid'] ?>" data-matsatid="<?= $mat['matsatid'] ?>" <?= in_array((int) $mat['matid'], $materialAlternatif ?? [], true) ? 'selected' : '' ?>><?= esc($mat['matnama']) ?></option>
                <?php endforeach ?>
            </select>
            <small class="form-text text-muted">Alternatif memakai berat per pcs masing-masing. Material inti dan alternatif tidak dikurangi bersamaan; pilih salah satu saat produksi.</small>
            <div id="materialHelp" class="form-text text-muted">Material inti wajib dipilih untuk produk biasa. Untuk produk jasa/tanpa berat, material boleh hanya sebagai referensi atau dikosongkan.</div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Berat Material Terpakai <small class="text-muted">(input dalam gram)</small></label>
                    <small class="d-block text-muted mb-2">Setiap material memiliki berat pemakaian, Wise, dan berat produk jadi masing-masing.</small>
                    <div id="materialBeratContainer" class="berat-material-container text-muted">
                        Pilih material terlebih dahulu.
                    </div>
            <small class="form-text text-muted">Isi berat tiap material yang dibutuhkan untuk membuat 1 pcs produk ini. Jika Berat Produk Jadi dan Wise diisi, berat material utama akan dihitung otomatis dan tetap bisa disesuaikan manual.</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="totalMaterialTerpakai">Total Semua Material <small class="text-muted">(referensi global, Kg)</small></label>
                    <input type="number" step="0.0001" min="0" class="form-control" id="totalMaterialTerpakai" placeholder="0.0000" readonly>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="wise">Wise Material Utama <small class="text-muted">(referensi global, % susut)</small></label>
                    <input type="number" step="0.01" min="0" max="100" class="form-control" id="wise" name="wise" placeholder="otomatis dari kalibrasi" value="<?= $wise !== null ? esc($wise) : '' ?>" readonly>
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
            <label class="mb-1">Hasil Kalkulasi per Material</label>
            <div id="hasilKalkulasiPerMaterial" class="berat-material-container">
                <span class="text-muted">Pilih material untuk melihat kalkulasi masing-masing material.</span>
            </div>
            <small class="form-text text-muted">Setiap kartu dihitung dari berat material, Wise, berat produk jadi, dan harga pembelian terakhir material tersebut. Tidak lagi menggunakan total global.</small>
        </div>
    </div>
</div>

<div class="form-group">
    <button type="submit" class="btn btn-success">Simpan</button>
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
    var beratMaterialAwal = <?= json_encode($beratMaterial, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var beratProdukJadiMaterialAwal = <?= json_encode($beratProdukJadiMaterial ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var wiseMaterialAwal = <?= json_encode($wiseMaterial ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var GRAM_KE_KG = 1000;
    // Berat Produk Jadi sudah tersimpan sebelumnya (dari input manual lama)
    // -- jangan ditimpa otomatis pas halaman baru dibuka, biarin apa adanya
    // sampai user sendiri yang ubah Total Material Terpakai/Wise.
    var beratProdukJadiManual = <?= $beratProdukJadi !== null ? 'true' : 'false' ?>;

    <?php if ($beratProdukJadi !== null) : ?>
        beratProdukJadiElement.value = <?= json_encode((float) $beratProdukJadi * 1000) ?>;
    <?php endif ?>

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
            input.dataset.materialId = opt.value;
            input.name = 'berat_material[' + opt.value + ']';
            input.placeholder = 'Berat dalam gram';
            if (beratMaterialAwal[opt.value] !== undefined) {
                // Tersimpan di database dalam Kg, ditampilkan dalam gram.
                input.value = (parseFloat(beratMaterialAwal[opt.value]) * GRAM_KE_KG).toString();
            }
            input.addEventListener('input', hitungTotalBerat);

            label.className = 'font-weight-bold mb-1';
            wrapper.appendChild(label);
            var materialLabel = document.createElement('small');
            materialLabel.className = 'd-block text-muted';
            materialLabel.textContent = 'Berat material terpakai (gram)';
            wrapper.appendChild(materialLabel);
            wrapper.appendChild(input);
            var detailRow = document.createElement('div');
            detailRow.className = 'row mt-1';
            var wiseAwal = wiseMaterialAwal[opt.value] !== undefined && wiseMaterialAwal[opt.value] !== null
                ? wiseMaterialAwal[opt.value] : '';
            var beratProdukAwal = beratProdukJadiMaterialAwal[opt.value] !== undefined && beratProdukJadiMaterialAwal[opt.value] !== null
                ? (parseFloat(beratProdukJadiMaterialAwal[opt.value]) * GRAM_KE_KG).toString() : '';
            detailRow.innerHTML = '<div class="col-md-6"><label class="small text-muted mb-1">Wise master (%)</label><input type="number" step="0.001" min="0" max="100" class="form-control wise-material" name="wise_material[' + opt.value + ']" value="' + wiseAwal + '" readonly></div>'
                + '<div class="col-md-6"><label class="small text-muted mb-1">Berat produk jadi (gram)</label><input type="number" step="0.001" min="0.001" required class="form-control berat-produk-jadi-material" name="berat_produk_jadi_material[' + opt.value + ']" placeholder="Contoh: 9.820" value="' + beratProdukAwal + '"></div>'
                + '<div class="col-12 mt-2"><div class="border rounded bg-light p-2"><strong class="small d-block mb-2">Kalibrasi aktual material ini (opsional)</strong><div class="row"><div class="col-md-4"><label class="small mb-1">Material masuk (Kg)</label><input type="number" step="0.001" min="0" class="form-control kalibrasi-material" name="kalibrasi_material_kg[' + opt.value + ']" value=""></div><div class="col-md-4"><label class="small mb-1">Qty jadi (pcs)</label><input type="number" step="1" min="1" class="form-control kalibrasi-qty" name="kalibrasi_qty_produk[' + opt.value + ']" value=""></div><div class="col-md-4"><label class="small mb-1">Scrap (Kg)</label><input type="number" step="0.001" min="0" class="form-control kalibrasi-scrap" name="kalibrasi_scrap_kg[' + opt.value + ']" placeholder="38.736"></div><div class="col-md-6 mt-2"><label class="small mb-1">Material/pcs (gram)</label><input type="text" class="form-control kalibrasi-hasil-material" value="-" disabled></div><div class="col-md-6 mt-2"><label class="small mb-1">Wise aktual (%)</label><input type="text" class="form-control kalibrasi-hasil-wise" value="-" disabled></div></div></div></div>';
            detailRow.querySelectorAll('.kalibrasi-material, .kalibrasi-qty, .kalibrasi-scrap').forEach(function(input) { input.addEventListener('input', function() { var box = detailRow.querySelector('.kalibrasi-hasil-material'); var wiseBox = detailRow.querySelector('.kalibrasi-hasil-wise'); var kg = parseFloat(detailRow.querySelector('.kalibrasi-material').value) || 0; var qty = parseFloat(detailRow.querySelector('.kalibrasi-qty').value) || 0; var scrap = parseFloat(detailRow.querySelector('.kalibrasi-scrap').value) || 0; box.value = kg > 0 && qty > 0 ? (kg * 1000 / qty).toFixed(6) : '-'; wiseBox.value = kg > 0 && scrap >= 0 && scrap <= kg ? (scrap / kg * 100).toFixed(6) : '-'; if (opt.value === materialUtamaElement.value && wiseBox.value !== '-') { document.getElementById('wise').value = wiseBox.value; } }); });
            detailRow.querySelector('.wise-material').addEventListener('input', function() {
                hitungKalkulasiWise();
                if (opt.value === materialUtamaElement.value) {
                    document.getElementById('wise').value = this.value;
                    beratProdukJadiManual = false;
                    hitungBeratMaterialDariProduk();
                    hitungKalkulasiWise('wise');
                }
            });
            detailRow.querySelector('.berat-produk-jadi-material').addEventListener('input', function() {
                hitungKalkulasiWise();
                if (opt.value === materialUtamaElement.value) {
                    document.getElementById('beratProdukJadi').value = this.value;
                    beratProdukJadiManual = true;
                    hitungBeratMaterialDariProduk();
                    hitungKalkulasiWise('finished');
                }
            });
            wrapper.appendChild(detailRow);
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
        if (isTanpaBerat) {
            beratProdukJadiElement.value = '0';
        } else if (<?= $beratProdukJadi !== null ? 'false' : 'true' ?>) {
            beratProdukJadiElement.value = '';
        }
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
    var hasilKalkulasiPerMaterialElement = document.getElementById('hasilKalkulasiPerMaterial');
    var hargaMaterialTerakhirPerMaterial = {};

    function hitungKalibrasiLapangan() {
        var inputKg = parseFloat(document.querySelector('[name="kalibrasi_material_kg"]').value) || 0;
        var qty = parseFloat(document.querySelector('[name="kalibrasi_qty_produk"]').value) || 0;
        var scrapKg = parseFloat(document.querySelector('[name="kalibrasi_scrap_kg"]').value) || 0;
        document.getElementById('kalibrasiMaterialPcsHasil').value = inputKg > 0 && qty > 0
            ? (inputKg * 1000 / qty).toFixed(6) : '-';
        document.getElementById('kalibrasiWiseHasil').value = inputKg > 0 && scrapKg >= 0 && scrapKg <= inputKg
            ? (scrapKg / inputKg * 100).toFixed(6) : '-';
    }
    document.querySelectorAll('[name="kalibrasi_material_kg"], [name="kalibrasi_qty_produk"], [name="kalibrasi_scrap_kg"]')
        .forEach(function(input) { input.addEventListener('input', hitungKalibrasiLapangan); });

    function formatRupiahKalkulasi(value) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(value));
    }

    function escapeKalkulasi(value) {
        return $('<div>').text(value == null ? '' : value).html();
    }

    function hitungKalkulasiWise() {
        var rows = Array.from(materialBeratContainer.querySelectorAll('.berat-material'));
        if (!rows.length) {
            hasilKalkulasiPerMaterialElement.innerHTML = '<span class="text-muted">Pilih material untuk melihat kalkulasi masing-masing material.</span>';
            return;
        }
        hasilKalkulasiPerMaterialElement.innerHTML = rows.map(function(input) {
            var wrapper = input.closest('.form-group');
            var materialId = input.dataset.materialId;
            var materialGram = parseFloat(input.value) || 0;
            var produkInput = wrapper.querySelector('.berat-produk-jadi-material');
            var wiseInput = wrapper.querySelector('.wise-material');
            var produkGram = parseFloat(produkInput && produkInput.value) || 0;
            var wise = parseFloat(wiseInput && wiseInput.value);
            var wasteGram = produkGram > 0 ? Math.max(materialGram - produkGram, 0) : 0;
            var wiseHitung = materialGram > 0 && produkGram > 0 ? wasteGram / materialGram * 100 : wise;
            var pcsPerKg = produkGram > 0 ? GRAM_KE_KG / produkGram : null;
            var harga = hargaMaterialTerakhirPerMaterial[materialId];
            var hargaPcs = harga != null && materialGram > 0 ? materialGram / GRAM_KE_KG * harga : null;
            var label = wrapper.querySelector('label').textContent;
            return '<div class="border rounded p-2 mb-2 bg-light"><strong>' + escapeKalkulasi(label) + '</strong>' +
                '<div class="row mt-1"><div class="col-md-3"><small>Total Material Terpakai</small><br><strong>' + (materialGram > 0 ? (materialGram / GRAM_KE_KG).toLocaleString('id-ID', {maximumFractionDigits: 4}) + ' Kg' : '-') + '</strong></div>' +
                '<div class="col-md-3"><small>Wise</small><br><strong>' + (wiseHitung != null && !isNaN(wiseHitung) ? wiseHitung.toLocaleString('id-ID', {maximumFractionDigits: 4}) + '%' : '-') + '</strong></div>' +
                '<div class="col-md-3"><small>Berat 1 Pcs Produk Jadi</small><br><strong>' + (produkGram > 0 ? produkGram.toLocaleString('id-ID', {maximumFractionDigits: 4}) + ' gram' : '-') + '</strong></div>' +
                '<div class="col-md-3"><small>PCS/KG</small><br><strong>' + (pcsPerKg ? pcsPerKg.toLocaleString('id-ID', {maximumFractionDigits: 4}) : '-') + '</strong></div>' +
                '<div class="col-md-3 mt-2"><small>Estimasi Waste/Pcs</small><br><strong>' + (produkGram > 0 ? wasteGram.toLocaleString('id-ID', {maximumFractionDigits: 4}) + ' gram' : '-') + '</strong></div>' +
                '<div class="col-md-3 mt-2"><small>Harga Material Terakhir/KG</small><br><strong>' + (harga != null ? formatRupiahKalkulasi(harga) : 'Belum ada histori pembelian') + '</strong></div>' +
                '<div class="col-md-3 mt-2"><small>Harga Material/PCS</small><br><strong>' + (hargaPcs != null ? formatRupiahKalkulasi(hargaPcs) : '-') + '</strong></div></div></div>';
        }).join('');
    }

    // Wise adalah persentase waste dari material masuk, sehingga berat material
    // dapat dihitung balik dari berat produk jadi dan Wise.
    function hitungBeratMaterialDariProduk() {
        if (tanpaBeratElement.checked || sumberMaterialElement.value === 'vendor' || sumberMaterialElement.value === 'beli_jadi') {
            return;
        }
        var beratProdukGram = parseFloat(beratProdukJadiElement.value) || 0;
        var wisePersen = parseFloat(wiseElement.value) || 0;
        var penyebut = 1 - (wisePersen / 100);
        if (beratProdukGram <= 0 || wisePersen < 0 || wisePersen >= 100 || penyebut <= 0) {
            return;
        }
        var materialUtama = materialUtamaElement.value;
        if (!materialUtama) {
            return;
        }
        var inputMaterialUtama = Array.from(materialBeratContainer.querySelectorAll('.berat-material'))
            .find(function(input) { return input.dataset.materialId === materialUtama; });
        if (inputMaterialUtama) {
            inputMaterialUtama.value = (beratProdukGram / penyebut).toFixed(4);
            hitungTotalBerat();
        }
    }

    function ambilHargaMaterialTerakhir() {
        var ids = Array.from(materialBeratContainer.querySelectorAll('.berat-material')).map(function(input) { return input.dataset.materialId; });
        ids.forEach(function(matid) {
            $.getJSON('/barang/hargaMaterialTerakhir', { matid: matid }, function(response) {
                hargaMaterialTerakhirPerMaterial[matid] = response.harga !== null && response.harga !== undefined ? Number(response.harga) : null;
                hitungKalkulasiWise();
            }).fail(function() {
                hargaMaterialTerakhirPerMaterial[matid] = null;
                hitungKalkulasiWise();
            });
        });
        hitungKalkulasiWise();
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
        hitungBeratMaterialDariProduk();
        hitungKalkulasiWise('finished');
    });
    wiseElement.addEventListener('input', function() {
        beratProdukJadiManual = false;
        hitungBeratMaterialDariProduk();
        hitungKalkulasiWise('wise');
    });

    tanpaBeratElement.addEventListener('change', toggleTanpaBerat);
    sumberMaterialElement.addEventListener('change', toggleTanpaBerat);

    $(document).ready(function() {
        toggleTanpaBerat();
        ambilHargaMaterialTerakhir();
    });

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
        materialBeratContainer.querySelectorAll('.berat-produk-jadi-material').forEach(function(input) {
            var gram = parseFloat(input.value) || 0;
            input.value = (gram / GRAM_KE_KG).toFixed(6);
        });
        var gramProdukJadi = parseFloat(beratProdukJadiElement.value) || 0;
        beratProdukJadiElement.value = (gramProdukJadi / GRAM_KE_KG).toFixed(6);
    });

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
