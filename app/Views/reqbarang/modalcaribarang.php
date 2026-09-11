<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<script src="<?= base_url() ?>/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

<div class="modal fade" id="modalcaribarang" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Data Cari Produk</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table id="databarang" class="table table-bordered table-hover dataTable dtr-inline collapsed">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Produk</th>
                            <th>Nama Produk</th>
                            <th>Gudang Cikarang</th>
                            <th>Gudang Cirebon</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    // function pilih(id, kode, gudang) {
    //     $('#idbarang').val(id);
    //     $('#kodebarang').val(kode);
    //     $('#idgudang').val(gudang);
    //     $('#modalcaribarang').on('hidden.bs.modal', function(event) {
    //         ambilDataBarang();
    //     });
    //     $('#modalcaribarang').modal('hide');
    // }
    function pilih(kodebarang) {
        $('#kodebarang').val(kodebarang);
        $('#modalcaribarang').on('hidden.bs.modal', function(event) {
            ambilDataBarang();
        });
        $('#modalcaribarang').modal('hide');
    }

    function listDataBarang() {
        var table = $('#databarang').DataTable({
            destroy: true,
            "responsive": true,
            "processing": true,
            "serverSide": true,
            "order": [],
            "ajax": {
                "url": "/permintaanBarang/listDataBarang",
                "type": "POST",
                "data": {
                    [csrfToken]: csrfHash,
                }
            },
            "columnDefs": [{
                "targets": [0, 4],
                "orderable": false,
            }, ],
        });
    }

    $(document).ready(function() {
        listDataBarang();
    });
</script>