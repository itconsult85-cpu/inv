<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Form Edit Data Material
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>

<button type="button" class="btn btn-warning" onclick="location.href=('/material/index')">
    <i class="fa fa-undo"></i> Kembali
</button>

<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<?= form_open('packaging/updatedata', '', [
    'idmaterial' => $id
]) ?>
<?= session()->getFlashdata('error') ?>
<?= session()->getFlashdata('sukses') ?>
<div class="form-group row">
    <label for="kodematerial" class="col-sm-4 col-form-label">Kode Material</label>
    <div class="col-sm-4">
        <input type="text" class="form-control" id="kodematerial" name="kodematerial" readonly value="<?= $kodematerial ?>">
        <input type="hidden" class="form-control" id="idmaterial" name="idmaterial" readonly value="<?= $id ?>">
    </div>
</div>

<div class="form-group row">
    <label for="namamaterial" class="col-sm-4 col-form-label">Nama Material</label>
    <div class="col-sm-4">
        <input type="text" class="form-control" id="namamaterial" name="namamaterial" value="<?= $namamaterial ?>" autofocus>
    </div>
</div>


<div class="form-group row">
    <label for="stok" class="col-sm-4 col-form-label">Stok</label>
    <div class="col-sm-4">
        <input type="number" class="form-control" id="stok" name="stok" value="<?= $stok ?>" placeholder="Isikan angka 0 jika belum ada stok">
    </div>
</div>

<div class="form-group row">
    <label for="gambar" class="col-sm-4 col-form-label"></label>
    <div class="col-sm-4">
        <input type="submit" value="Simpan" class="btn btn-success">
    </div>
</div>
<?= form_close() ?>
<?= $this->endSection('isi') ?>