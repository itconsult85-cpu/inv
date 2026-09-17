<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Data Produksi
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>

<button type="button" class="btn btn-primary" onclick="location.href=('/produksi/input')">
    <i class="fa fa-plus-circle"></i> Input Produksi
</button>

<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<script src="<?= base_url() ?>/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

<div class="row">
    <div class="col">
        <label>Filter Data</label>
    </div>
    <div class="col">
        <input type="date" name="tglawal" id="tglawal" class="form-control">
    </div>
    <div class="col">
        <input type="date" name="tglakhir" id="tglakhir" class="form-control">
    </div>
    <div class="col">
        <button type="button" class="btn btn-block btn-primary" id="tombolTampil">
            Tampilkan
        </button>
    </div>
</div>
<br>
<div class="table-responsive">
    <table id="dataproduksi" class="table table-bordered table-striped table-hover dataTable" style="width: 100%;">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th>No. Produksi</th>
                <th>Tanggal</th>
                <th>Kode Produk</th>
                <th>Nama Produk</th>
                <th>Qty Diproduksi</th>
                <th>Gudang</th>
                <th>Keterangan</th>
                <th style="width: 10%;">#</th>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
</div>

<script>
    let csrfToken = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';
    var table;

    $(document).ready(function() {
        table = $('#dataproduksi').DataTable({
            searching: true,
            searchDelay: 500,
            stateSave: true,
            stateDuration: -1,
            autoWidth: false,
            responsive: true,
            processing: true,
            serverSide: false,
            ordering: false,
            paging: false,
            ajax: {
                url: '<?= site_url('produksi/listData') ?>',
                type: 'POST',
                data: function(d) {
                    d.tglawal = $('#tglawal').val();
                    d.tglakhir = $('#tglakhir').val();
                    d[csrfToken] = csrfHash;
                    // serverSide:false gak ngirim draw/length/columns/search
                    // bawaan DataTables, padahal library Hermawan\DataTables
                    // di backend butuh itu (draw wajib ada, length=-1 berarti
                    // "ambil semua baris" -- perlu biar 1 grup gak kepotong).
                    d.draw = 1;
                    d.length = -1;
                    d.start = 0;
                    d.columns = [];
                    d.search = {
                        value: '',
                        regex: false
                    };
                }
            },
            drawCallback: function(settings) {
                // Pakai settings->api langsung (bukan variabel `table` di
                // luar) karena drawCallback pertama nembak SEBELUM baris
                // `table = $(...).DataTable(...)` selesai assign.
                window.treRenderMergedGroupRows(new $.fn.dataTable.Api(settings), {
                    groupBy: function(row) {
                        return row.no_produksi;
                    },
                    columns: [1, 2, 6, 7]
                });
            },
            columns: [{
                    data: 'nomor',
                    orderable: false,
                    className: 'text-center'
                },
                {
                    data: 'no_produksi'
                },
                {
                    data: 'tgl_produksi',
                    className: 'text-center'
                },
                {
                    data: 'kode_produk',
                    className: 'text-center'
                },
                {
                    data: 'nama_produk'
                },
                {
                    data: 'qty_produk',
                    className: 'text-right'
                },
                {
                    data: 'gdgnama',
                    className: 'text-center'
                },
                {
                    data: 'keterangan',
                    className: 'text-center'
                },
                {
                    data: 'aksi',
                    orderable: false,
                    className: 'text-center'
                },
            ]
        });

        $('#tombolTampil').on('click', function() {
            table.ajax.reload();
        });
    });

    function editProduksi(hashProduksi) {
        window.location.href = ('/produksi/edit/') + hashProduksi;
    }
    function hapusProduksi(produksiProdukId) {
        showBootstrapModal({
            title: 'Hapus Data Produksi',
            text: "Stok material yang tadi dipakai akan dikembalikan, dan stok produk hasil produksi ini akan dikurangi lagi. Yakin hapus?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus !'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "post",
                    url: '<?= site_url('produksi/hapusTransaksi') ?>',
                    data: {
                        [csrfToken]: csrfHash,
                        id: produksiProdukId
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.sukses) {
                            showBootstrapModal('Berhasil', response.sukses, 'success');
                            table.ajax.reload();
                        } else if (response.error) {
                            showBootstrapModal('Gagal', response.error, 'error');
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
