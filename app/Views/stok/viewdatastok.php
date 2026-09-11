<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Data Stok
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>

<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<script src="<?= base_url() ?>/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<?= form_open('stok/cetakLaporan', ['target' => '_blank']) ?>
<div class="row">
    <div class="col">
        <label>Filter Data</label>
    </div>
    <div class="col">
        <select name="kategori" id="kategori" class="form-control">
            <option value="">Pilih Kategori</option>
            <?php foreach ($kategoris as $kategori) : ?>
                <option value="<?= $kategori['katid'] ?>"><?= $kategori['katnama'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col">
        <select name="material" id="material" class="form-control">
            <option value="">Pilih Material</option>
            <?php foreach ($materials as $material) : ?>
                <option value="<?= $material['matid'] ?>"><?= $material['matnama'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col">
        <select name="pelanggan" id="pelanggan" class="form-control">
            <option value="">Pilih Pelanggan</option>
            <?php foreach ($pelanggans as $pelanggan) : ?>
                <option value="<?= $pelanggan['pelid'] ?>"><?= $pelanggan['pelnama'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col">
        <button type="submit" name="btnCetak" class="btn btn-block btn-success">
            <i class="fa fa-print"></i> Cetak Laporan
        </button>
    </div>
</div>
<?= form_close() ?>
<br>
<table class="table table-bordered table-striped" id="datastok">
    <thead>
        <tr>
            <th style="width: 5%; vertical-align: middle;">No</th>
            <th style="vertical-align: middle;">Kode Barang</th>
            <th style="vertical-align: middle;">Stok Cikarang (Pcs)</th>
            <th style="vertical-align: middle;">Stok Cirebon (Pcs)</th>
            <th style="vertical-align: middle;">Total Stok (Pcs)</th>
            <th style="vertical-align: middle;">Total Sisa PO</th>
            <th style="vertical-align: middle;">QTY Terkirim (Pcs)</th>
            <th style="vertical-align: middle;">Kekurangan Produksi (Pcs)</th>
            <th style="vertical-align: middle;">Kelebihan Produksi (Pcs)</th>
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
    var table;

    $(document).ready(function() {
        table = $('#datastok').DataTable({
            lengthChange: true,
            autoWidth: false,
            responsive: true,
            searching: true, // Aktifkan opsi pencarian
            searchDelay: 500, // Tunda pencarian selama 500 milidetik
            stateSave: true,
            stateDuration: -1,
            processing: true,
            serverSide: true,
            ajax: {
                url: '<?= site_url('stok/data') ?>',
                type: 'POST',
                data: function(d) {
                    d.kategori = $('#kategori').val();
                    d.material = $('#material').val();
                    d.pelanggan = $('#pelanggan').val();
                    d[csrfToken] = csrfHash;
                }
            },
            pageLength: 10,
            order: [
                [1, 'asc']
            ],
            columns: [{
                    data: 'nomor',
                    orderable: false,
                    className: 'text-center'
                },
                {
                    data: 'kodebarang',
                    className: 'text-center'
                },
                {
                    data: 'totmascik',
                    className: 'text-right',
                    render: function(data) {
                        return formatNumber(data);
                    }
                },
                {
                    data: 'totmascir',
                    className: 'text-right',
                    render: function(data) {
                        return formatNumber(data);
                    }
                },
                {
                    data: 'totalstok',
                    className: 'text-right',
                    render: function(data) {
                        return formatNumber(data);
                    }
                },
                {
                    data: 'kekurangan',
                    className: 'text-right',
                    render: function(data) {
                        return formatNumber(data);
                    }
                },
                {
                    data: 'kirim',
                    className: 'text-right',
                    render: function(data) {
                        return formatNumber(data);
                    }
                },
                {
                    data: 'kekuranganproduksi',
                    className: 'text-right',
                    render: function(data) {
                        return formatNumber(data);
                    }
                },
                {
                    data: 'kelebihanproduksi',
                    className: 'text-right',
                    render: function(data) {
                        return formatNumber(data);
                    }
                },
            ],
        });
        $('#kategori').on('change', function() {
            table.ajax.reload();
        });
        $('#material').on('change', function() {
            table.ajax.reload();
        });
        $('#pelanggan').on('change', function() {
            table.ajax.reload();
        });
        $('#btnFilter').on('click', function() {
            table.ajax.reload();
        });

        function formatNumber(number) {
            return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

    });
</script>
<?= $this->endSection('isi') ?>
