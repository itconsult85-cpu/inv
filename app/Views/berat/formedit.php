<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Form Edit Data Berat/Ukuran Bersih Barang
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>

<button type="button" class="btn btn-warning" onclick="location.href=('/berat/index')">
    <i class="fa fa-undo"></i> Kembali
</button>

<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<?= form_open('berat/updatedata') ?>
<?= session()->getFlashdata('error') ?>
<?= session()->getFlashdata('sukses') ?>

<div class="form-group row">
    <label for="kodeprd" class="col-sm-4 col-form-label">Kode Produk</label>
    <div class="col-sm-4">
        <input type="text" class="form-control" id="kodeprd" name="kodeprd" readonly value="<?= $kodeprd ?>">
    </div>
</div>

<div class="form-group row">
    <label class="col-sm-4 col-form-label">Berat per Material</label>
    <div class="col-sm-4">
        <?php foreach ($materialProduk as $material) : ?>
            <div class="form-group mb-2">
                <label>
                    <?= esc($material['matnama']) ?>
                    (<?= esc($material['matkode']) ?>)
                </label>
                <input
                    type="number"
                    step="0.0001"
                    min="0.0001"
                    required
                    class="form-control berat-material"
                    name="berat_material[<?= (int) $material['matid'] ?>]"
                    value="<?= esc($material['berat_material']) ?>"
                >
            </div>
        <?php endforeach ?>
    </div>
</div>

<div class="form-group row">
    <label for="berat" class="col-sm-4 col-form-label">Berat/Ukuran</label>
    <div class="col-sm-4">
        <input type="number" step="0.0001" min="0" class="form-control" id="berat" name="berat" value="<?= esc($berat) ?>" readonly>
        <small class="form-text text-muted">Total otomatis dari seluruh berat material.</small>
    </div>
</div>

<div class="form-group row">
    <label for="satuan" class="col-sm-4 col-form-label">Satuan</label>
    <div class="col-sm-4">
        <?php
        // Cari data material berdasarkan kode barang
        $satuan = $datasatuan[array_search($satuan, array_column($datasatuan, 'satid'))];
        $satnama = $satuan['satnama'];
        $satid = $satuan['satid'];
        ?>
        <!-- <select class="form-control" id="satuan" name="satuan">
            <?php foreach ($datasatuan as $satuan) : ?>
                <option value="<?= $satuan['satid'] ?>" <?= $satuan['satid'] == $satnama ? 'selected' : '' ?>>
                    <?= $satuan['satnama'] ?>
                </option>
            <?php endforeach; ?>
        </select> -->
        <input type="text" class="form-control" readonly value="<?= $satnama ?>">
        <input type="hidden" class="form-control" id="satuan" name="satuan" readonly value="<?= $satid ?>">
    </div>
</div>


<div class="form-group row">
    <label class="col-sm-4 col-form-label"></label>
    <div class="col-sm-4">
        <input type="submit" value="Simpan" class="btn btn-success">
    </div>
</div>
<?= form_close() ?>
<script>
    function hitungTotalBerat() {
        var total = 0;
        document.querySelectorAll('.berat-material').forEach(function(input) {
            total += parseFloat(input.value) || 0;
        });
        document.getElementById('berat').value = total.toFixed(4);
    }

    document.querySelectorAll('.berat-material').forEach(function(input) {
        input.addEventListener('input', hitungTotalBerat);
    });
    hitungTotalBerat();
</script>
<?= $this->endSection('isi') ?>
