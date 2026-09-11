<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?= base_url() ?>/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<script src="<?= base_url() ?>/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

<div class="modal fade" id="modaldatapo" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Cari Data PO</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table id="datapo" class="table table-bordered table-hover dataTable dtr-inline collapsed" style="width: 100%;">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 5;">No</th>
                            <th class="text-center">No. PO</th>
                            <th class="text-center">Tanggal</th>
                            <th class="text-center">Kode Barang</th>
                            <th class="text-center">Total QTY</th>
                            <th class="text-center">Total Terkirim</th>
                            <th class="text-center">Total Outstanding</th>
                            <th class="text-center all">#</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#datapo').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            ajax: '<?= site_url('barangkeluar/listDataPo') ?>',
            order: [
                [1, 'asc'],
                [3, 'asc']
            ],
            columns: [{
                    data: 'nomor',
                    orderable: false,
                    className: 'text-center'
                },
                {
                    data: 'nopo',
                    className: 'text-center'
                },
                {
                    data: 'tgl',
                    className: 'text-center'
                },
                {
                    data: 'kodebrg',
                    className: 'text-center'
                },
                {
                    data: 'qty',
                    className: 'text-right'
                },
                {
                    data: 'terkirim',
                    className: 'text-right'
                },
                {
                    data: 'kekurangan',
                    className: 'text-right'
                },
                {
                    data: 'aksi',
                    className: 'text-center all',
                    orderable: false
                },
            ],
            drawCallback: function() {
                const api = this.api();
                let poTerakhir = null;
                let nomorGrup = 0;

                api.rows({
                    page: 'current'
                }).every(function() {
                    const data = this.data();
                    const cells = $(this.node()).find('td');

                    if (data.nopo === poTerakhir) {
                        cells.eq(0).html('');
                        cells.eq(1).html('');
                        return;
                    }

                    nomorGrup++;
                    poTerakhir = data.nopo;
                    cells.eq(0).text(nomorGrup);
                });
            }
        });
    });

    function pilih(id, napel, nama, gudangPelanggan) {
        if (!gudangPelanggan) {
            Swal.fire(
                'Gudang belum ditentukan',
                'Silakan tentukan gudang pelanggan pada menu Pelanggan terlebih dahulu.',
                'warning'
            );
            return;
        }

        $('#nopo').val(id);
        $('#idpelanggan').val(napel);
        $('#namapelanggan').val(nama);
        $('#gudang').val(String(gudangPelanggan));
        $('#idgudang').val(gudangPelanggan);

        $('#modaldatapo').modal('hide');
        $('#nofaktur').focus();
        tampilDataTempKeluar();
        tampilDataTemp();
    }
</script>
