<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Form Tambah Gudang
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>

<?= form_button('', '<i class="fa fa-undo"></i> Kembali', [
    'class' => 'btn btn-warning',
    'onclick' => "location.href=('" . site_url('gudang/index') . "')"
]) ?>

<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>

<?= form_open('gudang/simpandata') ?>
<div class="form-group">
    <label for="namagudang">Nama Gudang</label>
    <?= form_input('namagudang', '', [
        'class' => 'form-control',
        'id' => 'namagudang',
        'autofocus' => true,
        'placeholder' => 'Isikan Nama Gudang'
    ]) ?>

    <?= session()->getFlashdata('errorNamaGudang') ?>
</div>

<div class="form-group">
    <?= form_submit('', 'Simpan', [
        'class' => 'btn btn-success'
    ]) ?>
</div>
<?= form_close() ?>

<?= $this->endSection('isi') ?>