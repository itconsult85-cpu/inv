<div class="modal fade" id="modaltambahlevel" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabelLevel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabelLevel">Tambah Role</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?= form_open('users/simpanlevel', ['class' => 'frmsimpanlevel']) ?>
            <div class="modal-body">
                <div class="form-group">
                    <label for="">Nama Role</label>
                    <input type="text" name="levelnama" id="levelnama" class="form-control form-control-sm" autocomplete="off" placeholder="Contoh: SUPERVISOR">
                    <div id="msg-levelnama" class="invalid-feedback"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success btnsimpanlevel">Simpan</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.frmsimpanlevel').submit(function(e) {
            e.preventDefault();
            $.ajax({
                type: "post",
                url: $(this).attr('action'),
                data: $(this).serialize(),
                dataType: "json",
                cache: false,
                beforeSend: function() {
                    $('.btnsimpanlevel').prop('disable', true);
                    $('.btnsimpanlevel').html('<i class="fa fa-spin fa-spinner"></i>');
                },
                complete: function() {
                    $('.btnsimpanlevel').prop('disable', false);
                    $('.btnsimpanlevel').html('Simpan');
                },
                success: function(response) {
                    if (response.error) {
                        let err = response.error;
                        let msg = typeof err === 'string' ? err : (err.levelnama || 'Gagal menyimpan role.');
                        $('#levelnama').addClass('is-invalid');
                        $('#msg-levelnama').html(msg);
                    } else {
                        $('#levelnama').removeClass('is-invalid');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.sukses
                        });
                        $('#modaltambahlevel').modal('hide');
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert(xhr.status + '\n' + thrownError)
                }
            });
            return false;
        });
    });
</script>
