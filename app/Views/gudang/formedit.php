<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Form Edit Gudang
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>

<?= form_button('', '<i class="fa fa-undo"></i> Kembali', [
    'class' => 'btn btn-warning',
    'onclick' => "location.href=('" . site_url('gudang/index') . "')"
]) ?>

<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>

<?= form_open('gudang/updatedata', '', [
    'idgudang' => $id
]) ?>
<div class="form-group">
    <label for="namagudang">Nama Gudang</label>
    <?= form_input('namagudang', $nama, [
        'class' => 'form-control',
        'id' => 'namagudang',
        'autofocus' => true,
    ]) ?>

    <?= session()->getFlashdata('errorNamaGudang') ?>
</div>

<div class="form-group">
    <?= form_submit('', 'Update', [
        'class' => 'btn btn-success'
    ]) ?>
</div>
<?= form_close() ?>

<?= $this->endSection('isi') ?>