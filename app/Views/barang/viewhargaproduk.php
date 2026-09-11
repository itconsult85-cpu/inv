<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Harga Produk
<?= $this->endSection('judul') ?>

<?= $this->section('isi') ?>
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<script src="<?= base_url() ?>/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>

<table class="table table-bordered table-striped" id="tabelHargaProduk">
    <thead>
        <tr>
            <th style="width: 5%;">No</th>
            <th>Kode Produk</th>
            <th>Nama Produk</th>
            <th>Kategori</th>
            <th>Satuan</th>
            <th>Harga</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<script>
    let csrfToken = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';

    $(document).ready(function() {
        $('#tabelHargaProduk').DataTable({
            autoWidth: false,
            processing: true,
            serverSide: true,
            stateSave: true,
            stateDuration: -1,
            ajax: {
                url: '<?= site_url('barang/listDataHarga') ?>',
                type: 'POST',
                data: function(d) {
                    d[csrfToken] = csrfHash;
                }
            },
            pageLength: 10,
            order: [
                [2, 'asc']
            ],
            columns: [
                { data: 'nomor', orderable: false, className: 'text-center' },
                { data: 'brgkode', className: 'text-center' },
                { data: 'brgnama' },
                { data: 'katnama', orderable: false, className: 'text-center' },
                { data: 'satnama', orderable: false, className: 'text-center' },
                { data: 'harga', orderable: false, className: 'text-right' },
            ],
        });
    });
</script>
<?= $this->endSection('isi') ?>
