<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Management User
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>
<?php
$dynamicRbac = \App\Libraries\AccessControl::isDynamicEnabled();
$canTambahUser = $dynamicRbac ? \App\Libraries\AccessControl::can('utility.users.create') : in_array(session()->idlevel, [4, 5]);
$canHakAkses = $dynamicRbac ? \App\Libraries\AccessControl::can('utility.access.view') : in_array((int) session()->idlevel, [1, 4, 5], true);
$canTambahLevel = $dynamicRbac ? \App\Libraries\AccessControl::can('utility.access.manage_level') : in_array((int) session()->idlevel, [1, 4, 5], true);
?>
<?php if ($canTambahUser) : ?>
    <button type="button" class="btn btn-primary btntambah">
        <i class="fa fa-plus-circle"></i> Tambah User Baru
    </button>
<?php endif ?>
<?php if ($canHakAkses) : ?>
    <a href="<?= site_url('users/akses') ?>" class="btn btn-info">
        <i class="fa fa-user-shield"></i> Hak Akses User
    </a>
<?php endif ?>
<?php if ($canTambahLevel) : ?>
    <button type="button" class="btn btn-secondary btntambahlevel">
        <i class="fa fa-layer-group"></i> Tambah Role
    </button>
<?php endif ?>
<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<script src="<?= base_url() ?>/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

<table class="table table-sm table-bordered" id="datauser" style="width: 100%;">
    <thead>
        <tr>
            <th>No</th>
            <th>ID User</th>
            <th>Nama User</th>
            <th>Role</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>
<div class="viewmodal" style="display:none;"></div>
<script>
    let csrfToken = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';

    dataUser = $('#datauser').DataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        stateSave: true,
        stateDuration: -1,
        ajax: '<?= site_url('users/listdata') ?>',
        order: [],
        columns: [{
                data: 'nomor',
                orderable: false,
                width: 10
            },
            {
                data: 'userid'
            },
            {
                data: 'usernama'
            },
            {
                data: 'levelnama'
            },
            {
                data: 'status',
                orderable: false,
                width: 25
            },
            {
                data: 'aksi',
                orderable: false,
                className: 'text-center',
                width: 20
            },
        ]
    });

    $('.btntambah').click(function(e) {
        e.preventDefault();
        $.ajax({
            url: '<?= site_url('users/formtambah') ?>',
            success: function(response) {
                $('.viewmodal').html(response).show();
                $('#modaltambah').on('shown.bs.modal', function(event) {
                    $('#iduser').focus();
                });
                $('#modaltambah').modal('show');
            }
        });
    });

    $('.btntambahlevel').click(function(e) {
        e.preventDefault();
        $.ajax({
            url: '<?= site_url('users/formtambahlevel') ?>',
            success: function(response) {
                $('.viewmodal').html(response).show();
                $('#modaltambahlevel').on('shown.bs.modal', function(event) {
                    $('#levelnama').focus();
                });
                $('#modaltambahlevel').modal('show');
            }
        });
    });

    function view(iduser, userid) {
        $.ajax({
            type: "post",
            url: '<?= site_url('users/formedit') ?>',
            data: {
                [csrfToken]: csrfHash,
                iduser: iduser,
                userid: userid,
            },
            success: function(response) {
                $('.viewmodal').html(response).show();
                $('#modaledit').on('shown.bs.modal', function(event) {
                    $('#namalengkap').focus();
                });
                $('#modaledit').modal('show');
            }
        });
    }
</script>

<?= $this->endSection('isi') ?>
