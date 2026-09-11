<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<script src="<?= base_url() ?>/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>

<div class="modal fade" id="modalcarimaterial" data-backdrop="static" data-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Data Cari Material</h5>
            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body">
            <table id="datamaterialtransfer" class="table table-bordered table-hover">
                <thead><tr><th>No</th><th>Kode Material</th><th>Nama Material</th><th>Satuan</th><th>Gudang Cikarang</th><th>Gudang Cirebon</th><th>Aksi</th></tr></thead>
            </table>
        </div>
    </div></div>
</div>
<script>
    function pilihMaterial(kode) {
        $('#kodebarang').val(kode);
        $('#modalcarimaterial').one('hidden.bs.modal', ambilDataBarang).modal('hide');
    }
    $('#datamaterialtransfer').DataTable({
        processing: true, serverSide: true, order: [],
        ajax: { url: '<?= site_url('permintaanBarang/listDataMaterial') ?>', type: 'POST', data: function(d) { d[csrfToken] = csrfHash; } },
        columns: [
            {data:'nomor', orderable:false}, {data:'matkode'}, {data:'matnama'}, {data:'satnama'},
            {data:'stok_cikarang'}, {data:'stok_cirebon'}, {data:'aksi', orderable:false}
        ]
    });
</script>
