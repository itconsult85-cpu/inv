<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Utility System
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>
Backup Database
<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<?= session()->getFlashdata('pesan') ?>
<button type="button" class="btn btn-primary" onclick="location.href=('/utility/doBackup')">
    Click to Backup Database
</button>

<form method="POST" enctype="multipart/form-data" action="/utility/doRestore">
    <?= csrf_field() ?>
    <input type="file" name="restore_file">
    <button type="submit" name="restore">Restore Database</button>
</form>
<?= $this->endSection('isi') ?>