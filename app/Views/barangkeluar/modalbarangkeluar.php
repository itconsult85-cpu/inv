<div class="modal fade" id="modalbarangkeluar" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Data Faktur</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?= form_open('barangkeluar/simpanPembayaran', ['class' => 'frmbarangkeluar']) ?>
            <div class="modal-body">
                <div class="form-group">
                    <label for="">No. Faktur</label>
                    <input type="text" name="nofaktur" id="nofaktur" class="form-control" value="<?= $nofaktur ?>" readonly>
                    <input type="hidden" name="tglfaktur" value="<?= $tglfaktur ?>">
                    <input type="hidden" name="idpelanggan" value="<?= $idpelanggan ?>">
                </div>
                <div class="form-group">
                    <label for="">Total Berat (KG)</label>
                    <input type="number" name="totalbayar" id="totalbayar" class="form-control" value="<?= $totalberatbarang ?>" readonly>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success btnsimpan">
                    Simpan
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<script src="<?= base_url('dist/js/autoNumeric.js') ?>"></script>
<script>
    $(document).ready(function() {

        $('.frmbarangkeluar').submit(function(e) {
            e.preventDefault();

            $.ajax({
                type: "post",
                url: $(this).attr('action'),
                data: $(this).serialize(),
                dataType: "json",
                beforeSend: function() {
                    $('.btnsimpan').prop('disabled', true);
                    $('.btnsimpan').html('<i class="fa fa-spin fa-spinner"></i>');
                },
                complete: function() {
                    $('.btnsimpan').prop('disabled', false);
                    $('.btnsimpan').html('Simpan');
                },
                success: function(response) {
                    if (response.sukses) {
                        window.location.reload();
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    showBootstrapModal('Error', xhr.status + '\n' + thrownError, 'error')
                }
            });

            return false;
        });
    });
</script>