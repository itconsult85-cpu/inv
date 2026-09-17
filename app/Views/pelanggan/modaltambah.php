<div class="modal fade" id="modaltambahpelanggan" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Form Input Pelanggan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?= form_open('pelanggan/simpan', ['class' => 'formsimpan']) ?>
                <div class="form-group">
                    <label for="">Input Nama Pelanggan</label>
                    <input type="text" name="namapel" id="namapel" class="form-control">
                    <div class="invalid-feedback errorNamaPelanggan">
                    </div>
                </div>
                <div class="form-group">
                    <label for="namapic">Nama PIC</label>
                    <input type="text" name="namapic" id="namapic" class="form-control">
                    <div class="invalid-feedback errorNamaPic"></div>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control">
                    <div class="invalid-feedback errorEmail"></div>
                </div>
                <div class="form-group">
                    <label for="alamat">Alamat</label>
                    <textarea name="alamat" id="alamat" class="form-control" rows="3"></textarea>
                    <div class="invalid-feedback errorAlamat"></div>
                </div>
                <div class="form-group">
                    <label for="">Telp / Handphone</label>
                    <input type="text" name="telp" id="telp" class="form-control">
                    <div class="invalid-feedback errorTelp">
                    </div>
                </div>
                <div class="form-group">
                    <label for="fax">Fax <small class="text-muted">(opsional, kosongkan kalau tidak ada)</small></label>
                    <input type="text" name="fax" id="fax" class="form-control">
                    <div class="invalid-feedback errorFax"></div>
                </div>
                <div class="form-group">
                    <label for="to">To / Bagian Penerima <small class="text-muted">(opsional, kosongkan kalau tidak ada)</small></label>
                    <input type="text" name="to" id="to" class="form-control" placeholder="Contoh: Bag. Keuangan">
                    <div class="invalid-feedback errorTo"></div>
                </div>
                <div class="form-group">
                    <label for="gdgid">Gudang</label>
                    <select name="gdgid" id="gdgid" class="form-control">
                        <option value="">-- Pilih Gudang --</option>

                        <?php foreach ($gudang as $item): ?>
                            <option value="<?= esc($item['gdgid']) ?>">
                                <?= esc($item['gdgnama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <div class="invalid-feedback errorGudang"></div>
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

                        if (err.errNamaPelanggan) {
                            $('#namapel').addClass('is-invalid');
                            $('.errorNamaPelanggan').html(err.errNamaPelanggan);
                        }
                        if (err.errTelp) {
                            $('#telp').addClass('is-invalid');
                            $('.errorTelp').html(err.errTelp);
                        }
                        if (err.errNamaPic) {
                            $('#namapic').addClass('is-invalid');
                            $('.errorNamaPic').html(err.errNamaPic);
                        }
                        if (err.errEmail) {
                            $('#email').addClass('is-invalid');
                            $('.errorEmail').html(err.errEmail);
                        }
                        if (err.errAlamat) {
                            $('#alamat').addClass('is-invalid');
                            $('.errorAlamat').html(err.errAlamat);
                        }
                        if (err.errFax) {
                            $('#fax').addClass('is-invalid');
                            $('.errorFax').html(err.errFax);
                        }
                        if (err.errTo) {
                            $('#to').addClass('is-invalid');
                            $('.errorTo').html(err.errTo);
                        }
                        if (err.errGudang) {
                            $('#gdgid').addClass('is-invalid');
                            $('.errorGudang').html(err.errGudang);
                        }
                    }

                    if (response.sukses) {
                        showBootstrapModal({
                            title: 'Berhasil',
                            html: response.sukses,
                            icon: 'success',
                        }).then((result) => {
                            if ($.fn.DataTable && $.fn.DataTable.isDataTable('#datapelanggan')) {
                                $('#datapelanggan').DataTable().ajax.reload();
                            }
                            if (response.pelanggan) {
                                $(document).trigger('tre:pelangganAdded', [response.pelanggan]);
                            }
                            $('#modaltambahpelanggan').modal('hide');
                        });
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
