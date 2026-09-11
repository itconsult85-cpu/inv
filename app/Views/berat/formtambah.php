<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Form Tambah Data Berat/Ukuran Bersih Barang
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>

<button type="button" class="btn btn-warning" onclick="location.href=('/berat/index')">
    <i class="fa fa-undo"></i> Kembali
</button>

<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<?= form_open_multipart('berat/simpandata') ?>
<?= session()->getFlashdata('error') ?>
<?= session()->getFlashdata('sukses') ?>

<div class="form-group row">
    <label for="kodeprd" class="col-sm-4 col-form-label">Pilih Produk</label>
    <div class="col-sm-4">
        <select name="kodeprd" id="kodeprd" class="form-control">
            <option selected value="">-- Pilih --</option>
            <?php foreach ($databarang as $prd) : ?>
                <?php
                $isDisabled = false;

                foreach ($existingBerat as $dataBerat) {
                    if ($dataBerat['kodeprd'] === $prd['brgkode']) {
                        $isDisabled = true;
                        break;
                    }
                }

                ?>

                <?php if (!$isDisabled) : ?>
                    <option value="<?= esc($prd['brgkode']) ?>">
                        <?= esc($prd['brgkode']) ?>
                    </option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>

    </div>
</div>

<div class="form-group row">
    <label class="col-sm-4 col-form-label">Berat per Material</label>
    <div class="col-sm-4">
        <div id="materialContainer" class="text-muted">
            Pilih produk terlebih dahulu.
        </div>
    </div>
</div>

<div class="form-group row">
    <label for="berat" class="col-sm-4 col-form-label">Berat/Ukuran</label>
    <div class="col-sm-4">
        <input type="number" step="0.0001" min="0" class="form-control" id="berat" name="berat" readonly>
        <small class="form-text text-muted">Total otomatis dari seluruh berat material.</small>
    </div>
</div>

<div class="form-group row">
    <label for="satuan" class="col-sm-4 col-form-label">Satuan Berat/Ukuran</label>
    <div class="col-sm-4">
        <select name="satuan" id="satuan" class="form-control">
            <option selected value="">-- Pilih Satuan --</option>
            <?php foreach ($datasatuan as $sat) : ?>
                <option value="<?= $sat['satid'] ?>"><?= $sat['satnama'] ?></option>
            <?php endforeach ?>
        </select>
    </div>
</div>

<div class=" form-group row">
    <label class="col-sm-4 col-form-label"></label>
    <div class="col-sm-4">
        <button type="submit" class="btn btn-success">Simpan</button>&nbsp;
        <button type="reset" class="btn btn-warning">Reset</button>
    </div>
</div>

<script>
    var selectElement = document.getElementById('kodeprd');
    var materialContainer = document.getElementById('materialContainer');
    var totalBeratElement = document.getElementById('berat');
    var materialProduk = <?= json_encode($materialProduk, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    function hitungTotalBerat() {
        var total = 0;
        materialContainer.querySelectorAll('.berat-material').forEach(function(input) {
            total += parseFloat(input.value) || 0;
        });
        totalBeratElement.value = total.toFixed(4);
    }

    function tampilkanMaterial(kodeProduk) {
        var materials = materialProduk[kodeProduk] || [];
        materialContainer.innerHTML = '';
        totalBeratElement.value = '0.0000';

        if (materials.length === 0) {
            materialContainer.innerHTML = '<span class="text-danger">Produk belum memiliki relasi material.</span>';
            return;
        }

        materials.forEach(function(material) {
            var wrapper = document.createElement('div');
            wrapper.className = 'form-group mb-2';

            var label = document.createElement('label');
            label.textContent = material.matnama + ' (' + material.matkode + ')';

            var input = document.createElement('input');
            input.type = 'number';
            input.step = '0.0001';
            input.min = '0.0001';
            input.required = true;
            input.className = 'form-control berat-material';
            input.name = 'berat_material[' + material.matid + ']';
            input.placeholder = 'Berat material';
            input.addEventListener('input', hitungTotalBerat);

            wrapper.appendChild(label);
            wrapper.appendChild(input);
            materialContainer.appendChild(wrapper);
        });
    }

    selectElement.addEventListener('change', function() {
        tampilkanMaterial(selectElement.value);
    });
</script>
<?= form_close() ?>
<?= $this->endSection('isi') ?>
