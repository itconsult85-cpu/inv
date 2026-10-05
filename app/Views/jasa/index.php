<?= $this->extend('main/layout') ?>
<?= $this->section('judul') ?>Master Data Jasa<?= $this->endSection('judul') ?>
<?= $this->section('subjudul') ?>
<button type="button" class="btn btn-primary" id="btnTambahJasa"><i class="fa fa-plus-circle"></i> Tambah Data Jasa</button>
<button type="button" class="btn btn-info" id="btnDaftarJasa"><i class="fa fa-list"></i> Daftar / Kelola Jasa</button>
<?= $this->endSection('subjudul') ?>
<?= $this->section('isi') ?>
<div class="card card-outline card-info">
    <div class="card-body">
        <h5><i class="fa fa-handshake text-info"></i> Data Jasa</h5>
        <p class="text-muted mb-0">Tambahkan jasa/vendor beserta harga modal per pcs. Data ini dapat dipakai saat membuat PO Jasa dan untuk reporting margin.</p>
    </div>
</div>
<div class="viewmodal" style="display:none"></div>
<?= view('jasa/modaldata') ?>
<script>
$(function () {
    $('#btnDaftarJasa').on('click', function () { $('#modaldatajasa').modal('show'); });
    $('#btnTambahJasa').on('click', function () {
        $.getJSON('<?= site_url('jasa/formtambah') ?>', function (response) {
            if (response.data) { $('.viewmodal').html(response.data).show(); $('#modaltambahjasa').modal('show'); }
        });
    });
});
</script>
<?= $this->endSection('isi') ?>
