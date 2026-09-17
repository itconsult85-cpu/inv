<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Data Raw Produk
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

<?= form_open('ngdata/cetak-raw-produk-periode', ['target' => '_blank']) ?>
<div class="row">
    <div class="col">
        <label>Filter Data</label>
    </div>
    <div class="col">
        <input type="date" name="tglawal" id="tglawal" class="form-control" required>
    </div>
    <div class="col">
        <input type="date" name="tglakhir" id="tglakhir" class="form-control" required>
    </div>
    <?php if (\App\Libraries\AccessControl::can('material.raw_produk.print')) :  ?>
        <div class="col">
            <select name="supplier" id="supplier" class="form-control">
                <option value="">Pilih Pelanggan</option>
                <?php foreach ($suppliers as $supplier) : ?>
                    <option value="<?= $supplier['supid'] ?>"><?= $supplier['supnama'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    <?php endif ?>
    <div class="col">
        <button type="button" class="btn btn-block btn-primary" id="tombolTampil">
            Tampilkan
        </button>
    </div>
    <?php if (\App\Libraries\AccessControl::can('material.raw_produk.print')) :  ?>
        <div class="col">
            <button type="submit" name="btnCetak" class="btn btn-block btn-success">
                <i class="fa fa-print"></i> Cetak Laporan
            </button>
        </div>
    <?php endif ?>
</div>
<?= form_close() ?>
<br>
<table id="ngdata" class="table table-bordered table-striped table-hover dataTable dtr-inline collapsed" style="width: 100%;">
    <thead>
        <tr>
            <th class="text-center" style="width: 5;" data-orderable="false">No</th>
            <th class="text-center">Vendor</th>
            <th class="text-center">Material</th>
            <th class="text-center">Berat Keluar (KG)</th>
            <th class="text-center">Berat Masuk (KG)</th>
            <th class="text-center">Sisa Material (KG)</th>
            <th class="text-center" data-orderable="false">Aksi</th>
        </tr>
    </thead>
    <tbody>

    </tbody>
</table>
<div class="viewmodal" style="display: none;"></div>
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
        table = $('#ngdata').DataTable({
            searching: true, // Aktifkan opsi pencarian
            searchDelay: 500,
            stateSave: true,
            stateDuration: -1,
            responsive: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: '<?= site_url('ngdata/listData') ?>',
                type: 'POST',
                data: function(d) {
                    d.tglawal = $('#tglawal').val();
                    d.tglakhir = $('#tglakhir').val();
                    d.supplier = $('#supplier').val();
                    d[csrfToken] = csrfHash;
                }
            },
            order: [
                [1, 'asc']
            ],
            columns: [{
                    data: 'nomor',
                    orderable: false
                },
                {
                    data: 'supnama'
                },
                {
                    data: 'matnama'
                },
                {
                    data: 'total_beratmatkeluar',
                    orderable: false,
                    className: 'text-right'
                },
                {
                    data: 'total_beratmatmasuk',
                    orderable: false,
                    className: 'text-right'
                },
                {
                    data: 'total_beratng',
                    orderable: false,
                    className: 'text-right'
                },
                {
                    data: 'aksi',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
            ]
        });

        window.detailNg = function(idsup, matjenis) {
            $.ajax({
                type: 'post',
                url: '<?= site_url('ngdata/detail') ?>',
                data: {
                    [csrfToken]: csrfHash,
                    idsup: idsup,
                    matjenis: matjenis
                },
                dataType: 'json',
                success: function(response) {
                    if (response.error) {
                        showBootstrapModal('Gagal', response.error, 'error');
                        return;
                    }
                    $('.viewmodal').html(response.data).show();
                },
                error: function(xhr) {
                    showBootstrapModal('Gagal', xhr.status + ' - Detail NG tidak dapat dimuat.', 'error');
                }
            });
        };

        $('#tombolTampil').on('click', function() {
            table.ajax.reload();
        });
        <?php if (\App\Libraries\AccessControl::can('material.raw_produk.print')) :  ?>
            $('#supplier').on('change', function() {
                table.ajax.reload();
            });
        <?php endif ?>
    });
</script>

<?= $this->endSection('isi') ?>
