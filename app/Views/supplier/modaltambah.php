<div class="modal fade" id="modaltambahsupplier" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Form Input Supplier</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?= form_open('supplier/simpan', ['class' => 'formsimpan']) ?>
                <div class="form-group">
                    <label for="namasup">Input Nama Supplier</label>
                    <input type="text" name="namasup" id="namasup" class="form-control">
                    <div class="invalid-feedback errorNamaSupplier">
                    </div>
                </div>
                <div class="form-group">
                    <label for="namapic">Input Nama PIC</label>
                    <input type="text" name="namapic" id="namapic" class="form-control">
                    <div class="invalid-feedback errorNamaPic">
                    </div>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control">
                    <div class="invalid-feedback errorEmail">
                    </div>
                </div>
                <div class="form-group">
                    <label for="telp">Telp / Handphone</label>
                    <input type="text" name="telp" id="telp" class="form-control">
                    <div class="invalid-feedback errorTelp">
                    </div>
                </div>
                <div class="form-group">
                    <label for="alamat">Alamat</label>
                    <input type="text" name="alamat" id="alamat" class="form-control">
                    <div class="invalid-feedback errorAlamat">
                    </div>
                </div>
                <div class="form-group">
                    <label for="tombolsimpan"></label>
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

                        if (err.errNamaSupplier) {
                            $('#namasup').addClass('is-invalid');
                            $('.errorNamaSupplier').html(err.errNamaSupplier);
                        }
                        if (err.errNamaPic) {
                            $('#namapic').addClass('is-invalid');
                            $('.errorNamaPic').html(err.errNamaPic);
                        }
                        if (err.errEmail) {
                            $('#email').addClass('is-invalid');
                            $('.errorEmail').html(err.errEmail);
                        }
                        if (err.errTelp) {
                            $('#telp').addClass('is-invalid');
                            $('.errorTelp').html(err.errTelp);
                        }
                        if (err.errAlamat) {
                            $('#alamat').addClass('is-invalid');
                            $('.errorAlamat').html(err.errAlamat);
                        }
                    }

                    if (response.sukses) {
                        showBootstrapModal({
                            title: 'Berhasil',
                            html: response.sukses,
                            icon: 'success',
                        }).then((result) => {
                            if (response.supplier) {
                                $(document).trigger('tre:supplierAdded', [response.supplier]);
                            }
                            if ($.fn.DataTable && $('#datasupplier').length) {
                                $('#datasupplier').DataTable().ajax.reload();
                            }
                            $('#modaltambahsupplier').modal('hide');
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
