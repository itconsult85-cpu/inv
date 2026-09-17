<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Managemen Data Gudang
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>

<?= form_button('', '<i class="fa fa-plus-circle"></i> Tambah Data Gudang', [
    'class' => 'btn btn-primary',
    'onclick' => "location.href=('" . site_url('gudang/formtambah') . "')"
]) ?>

<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<script src="<?= base_url() ?>/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

<table class="table table-bordered table-striped" id="datagudang">
    <thead>
        <tr>
            <th style="width: 5%;">No</th>
            <th>Nama Gudang</th>
            <th style="width: 10%;">#</th>
        </tr>
    </thead>
    <tbody>

    </tbody>
</table>
<script>
    // var pusher = new Pusher('8f027ac11961f0fa1906', {
    //     cluster: 'ap1'
    // });

    // var channel = pusher.subscribe('my-channel');
    // channel.bind('my-event', function(data) {
    //     table.ajax.reload(null, false);
    // });
</script>
<script>
    let csrfToken = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';

    $(document).ready(function() {
        $('#datagudang').DataTable({
            lengthChange: true,
            autoWidth: false,
            responsive: true,
            processing: true,
            serverSide: true,
            stateSave: true,
            stateDuration: -1,
            ajax: '<?= site_url('gudang/listData') ?>',
            order: [
                [1, 'asc']
            ],
            columns: [{
                    data: 'nomor',
                    orderable: false
                },
                {
                    data: 'gdgnama'
                },
                {
                    data: 'aksi',
                    className: 'text-center',
                    orderable: false
                },
            ]
        });
    });

    function edit(kode) {
        window.location = ('/gudang/formedit/') + kode;
    }

    function hapus(id, nama) {
        showBootstrapModal({
            title: 'Hapus Gudang',
            html: `Yakin data gudang dengan nama <strong>${nama}</strong> di hapus ?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus !',
            cancelButtonText: 'Tidak'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "post",
                    url: '<?= site_url('gudang/hapus') ?>',
                    data: {
                        [csrfToken]: csrfHash,
                        id: id,
                        nama: nama,
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.sukses) {
                            showBootstrapModal({
                                icon: 'success',
                                title: 'Hapus data',
                                text: response.sukses
                            }).then(() => {
                                window.location.reload();
                            });
                        } else if (response.error) {
                            showBootstrapModal({
                                icon: 'error',
                                title: 'Gagal',
                                html: response.error
                            });
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        showBootstrapModal({
                            icon: 'error',
                            title: 'Gagal',
                            html: `Data Gudang <b>${nama}</b> tidak bisa dihapus karena masih terkait dengan data di tabel lain`
                            // alert(xhr.status + '\n' + thrownError)
                        });
                    }
                });
            }
        })
    }
</script>

<?= $this->endSection('isi') ?>