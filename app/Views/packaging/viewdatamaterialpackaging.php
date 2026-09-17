<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Managemen Data Material
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>
<button type="button" class="btn btn-primary" onclick="location.href=('/packaging/tambah')">
    <i class="fa fa-plus-circle"></i> Tambah Data Material
</button>
<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<!-- DataTables -->
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<!-- DataTables  & Plugins -->
<script src="<?= base_url() ?>/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

<table class="table table-bordered table-striped" id="datamaterial">
    <thead>
        <tr>
            <th style="width: 5%;">No</th>
            <th>Kode Material</th>
            <th>Nama Material</th>
            <th>Stok</th>
            <th style="width: 15%;">#</th>
        </tr>
    </thead>
    <tbody>

    </tbody>
</table>

<script>
    let csrfToken = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';

    $(document).ready(function() {
        $('#datamaterial').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            stateSave: true,
            stateDuration: -1,
            ajax: '<?= site_url('packaging/listData') ?>',
            order: [],
            columns: [{
                    data: 'nomor',
                    orderable: false,
                    className: 'text-center'
                },
                {
                    data: 'matpkode',
                    className: 'text-center'
                },
                {
                    data: 'matpnama',
                    className: 'text-center'
                },
            
                {
                    data: 'matpstok',
                    className: 'text-right'
                },
                {
                    data: 'aksi',
                    className: 'text-center',
                    orderable: false
                },
            ]
        });
    });

    function edit(id) {
        window.location.href = ('/packaging/edit/') + id;
    }

    function hapus(kode, nama) {
        showBootstrapModal({
            title: 'Hapus Material',
            html: `Yakin data material dengan kode <strong>${nama}</strong> di hapus ?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus !'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "post",
                    url: '<?= site_url('packaging/hapus') ?>',
                    data: {
                        [csrfToken]: csrfHash,
                        kode: kode
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.sukses) {
                            showBootstrapModal('Berhasil', response.sukses, 'success').then(() => {
                                window.location.reload();
                            });
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        alert(xhr.status + '\n' + thrownError)
                    }
                });
            }
        })
    }
</script>
<?= $this->endSection('isi') ?>