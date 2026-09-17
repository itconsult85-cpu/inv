<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Pengiriman ke Pelanggan
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>
<button type="button" class="btn btn-success" onclick="location.href='/permintaanPengiriman/langsung'">
    <i class="fa fa-plus-circle"></i> Input Pengiriman
</button>
<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<script src="<?= base_url() ?>/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>

<table id="dataPermintaanPengiriman" class="table table-bordered table-striped table-hover" style="width:100%">
    <thead>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>User</th>
            <th>Total Produk (Pcs)</th>
            <th>Keterangan</th>
            <th>Aksi</th>
        </tr>
    </thead>
</table>

<script>
    const csrfToken = '<?= csrf_token() ?>';
    const csrfHash = '<?= csrf_hash() ?>';
    let tablePengiriman;

    $(function() {
        tablePengiriman = $('#dataPermintaanPengiriman').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            stateDuration: -1,
            pageLength: 10,
            order: [[1, 'desc']],
            ajax: {
                url: '<?= site_url('permintaanPengiriman/listData') ?>',
                type: 'POST',
                data: function(data) {
                    data[csrfToken] = csrfHash;
                }
            },
            columns: [
                {data: 'nomor', orderable: false, className: 'text-center'},
                {data: 'tanggal', className: 'text-center'},
                {data: 'usernama'},
                {data: 'total_produk', className: 'text-right'},
                {data: 'keterangan'},
                {data: 'aksi', orderable: false, className: 'text-center'}
            ]
        });
    });

    function proses(id) {
        location.href = '/permintaanPengiriman/proses/' + id;
    }

    function cetak(id) {
        location.href = '/permintaanPengiriman/pilih-cetak/' + id;
    }

    function hapusPengiriman(id) {
        showBootstrapModal({
            title: 'Hapus Permintaan?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus'
        }).then(function(result) {
            if (!result.isConfirmed) return;

            $.post('/permintaanPengiriman/hapus', {
                [csrfToken]: csrfHash,
                id: id
            }, function(response) {
                showBootstrapModal('Berhasil', response.sukses, 'success');
                tablePengiriman.ajax.reload();
            }, 'json');
        });
    }
</script>
<?= $this->endSection('isi') ?>
