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
<?= form_open('material/updatedata', '', [
    'idmaterial' => $id
]) ?>
<?= session()->getFlashdata('error') ?>
<?= session()->getFlashdata('sukses') ?>
<div class="form-group row">
    <label for="kodematerial" class="col-sm-4 col-form-label">Kode Material</label>
    <div class="col-sm-4">
        <input type="text" class="form-control" id="kodematerial" name="kodematerial" value="<?= $kodematerial ?>">
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
    <label for="kategori" class="col-sm-4 col-form-label">Kategori</label>
    <div class="col-sm-4">
        <select name="kategori" id="kategori" class="form-control">
            <?php foreach ($datakategori as $kat) : ?>
                <?php if ($kat['katid'] == $kategori) : ?>
                    <option selected value="<?= $kat['katid'] ?>"><?= $kat['katnama'] ?></option>
                <?php else : ?>
                    <option value="<?= $kat['katid'] ?>"><?= $kat['katnama'] ?></option>
                <?php endif; ?>
            <?php endforeach ?>
        </select>
    </div>
</div>

<div class="form-group row">
    <label for="satuan" class="col-sm-4 col-form-label">Satuan Material</label>
    <div class="col-sm-4">
        <select name="satuan" id="satuan" class="form-control">
            <?php foreach ($datasatuan as $sat) : ?>
                <?php if ($sat['satid'] == $satuan) : ?>
                    <option selected value="<?= $sat['satid'] ?>"><?= $sat['satnama'] ?></option>
                <?php else : ?>
                    <option value="<?= $sat['satid'] ?>"><?= $sat['satnama'] ?></option>
                <?php endif; ?>
            <?php endforeach ?>
        </select>
    </div>
</div>

<div class="form-group row">
    <label for="minstok" class="col-sm-4 col-form-label">Minimal Stok Material</label>
    <div class="col-sm-4">
        <input type="number" class="form-control" id="minstok" name="minstok" value="<?= $minstok ?>" placeholder="Isikan angka 0 jika belum ada stok">
    </div>
</div>

<div class="form-group row">
    <label for="stok" class="col-sm-4 col-form-label">Stok</label>
    <div class="col-sm-4">
        <input type="number" class="form-control" id="stok" name="stok" value="<?= $stok ?>" placeholder="Isikan angka 0 jika belum ada stok" readonly>
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