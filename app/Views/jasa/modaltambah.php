<div class="modal fade" id="modaltambahjasa" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Form Input Jasa</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?= form_open('jasa/simpan', ['class' => 'formsimpan']) ?>
                <div class="form-group">
                    <label for="">Input Nama Jasa</label>
                    <input type="text" name="namajasa" id="namajasa" class="form-control">
                    <div class="invalid-feedback errorNamaJasa">
                    </div>
                </div>
                <div class="form-group">
                    <label for="harga_modal">Harga Modal / Pcs (Rp)</label>
                    <input type="number" min="0" step="0.01" name="harga_modal" id="harga_modal" class="form-control" value="0">
                    <small class="text-muted">Dipakai otomatis pada Reporting margin.</small>
                </div>
                <div class="form-group">
                    <label for=""></label>
                    <button type="submit" class="btn btn-block btn-success" id="tombolsimpan">
                        Simpan
                    </button>
                </div>
                <?= form_close() ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.formsimpan').submit(function(e) {
            e.preventDefault();

            $.ajax({
                type: "post",
                url: $(this).attr('action'),
                data: $(this).serialize(),
                dataType: "json",
                beforeSend: function() {
                    $('#tombolsimpan').prop('disabled', true);
                    $('#tombolsimpan').html('<i class="fa fa-spin fa-spinner"></i>');
                },
                complete: function() {
                    $('#tombolsimpan').prop('disabled', false);
                    $('#tombolsimpan').html('Simpan');
                },
                success: function(response) {
                    if (response.error) {
                        let err = response.error;

                        if (err.errNamaJasa) {
                            $('#namajasa').addClass('is-invalid');
                            $('.errorNamaJasa').html(err.errNamaJasa);
                        }
                    }

                    if (response.sukses) {
                        showBootstrapModal({
                            title: 'Berhasil',
                            text: response.sukses,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Ya, Ambil !',
                            cancelButtonText: 'Tidak !'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $('#namajasa').val(response.namajasa);
                                $('#idjasa').val(response.idjasa);
                                $('#modaltambahjasa').modal('hide');
                            } else {
                                $('#modaltambahjasa').modal('hide');
                            }
                        })
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
