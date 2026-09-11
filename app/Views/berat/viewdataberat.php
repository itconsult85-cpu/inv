<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Managemen Data Berat/Ukuran
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>
<button type="button" class="btn btn-primary" onclick="location.href=('/berat/tambah')">
    <i class="fa fa-plus-circle"></i> Tambah Data Berat/Ukuran Bersih
</button>
<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<script src="<?= base_url() ?>/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

<table class="table table-bordered table-striped" id="databerat">
    <thead>
        <tr>
            <th style="width: 5%;">No</th>
            <th>Kode Produk</th>
            <th>Material</th>
            <th>Berat/Ukuran</th>
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
        $('#databerat').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            stateSave: true,
            stateDuration: -1,
            ajax: '<?= site_url('berat/listData') ?>',
            order: [
                [1, 'asc']
            ],
            columns: [{
                    data: 'nomor',
                    orderable: false,
                    className: 'text-center'
                },
                {
                    data: 'kodeprd',
                    className: 'text-center'
                },
                {
                    data: 'matnama',
                    className: 'text-center'
                },
                {
                    data: 'berat_satuan',
                    name: 'berat.berat',
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

    function edit(kode) {
        window.location.href = ('/berat/edit/') + kode;
    }

    function hapus(kode) {
        Swal.fire({
            title: 'Hapus Data Berat',
            html: `Yakin data Beratdengan nama <strong>${kode}</strong> di hapus ?`,
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
                    url: '<?= site_url('berat/hapus') ?>',
                    data: {
                        [csrfToken]: csrfHash,
                        kode: kode
                    },
                    dataType: "json",
                    success: function(response) {
                        console.log(response);
                        if (response.sukses) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Hapus data',
                                text: response.sukses
                            }).then(() => {
                                window.location.reload();
                            });
                        } else if (response.error) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                html: response.error
                            });
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            html: `Data Berat tidak bisa dihapus karena masih terkait dengan data di tabel lain`
                            // alert(xhr.status + '\n' + thrownError)
                        });
                    }
                });
            }
        });
    }
</script>
<?= $this->endSection('isi') ?>