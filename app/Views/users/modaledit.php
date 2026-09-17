<link href="<?= base_url() ?>/bootstrap4-toggle.min.css" rel="stylesheet">
<script src="<?= base_url() ?>/bootstrap4-toggle.min.js"></script>

<div class="modal fade" id="modaledit" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">View Data User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?= form_open('users/update', ['class' => 'frmsimpan']) ?>
            <div class="modal-body">
                <div class="form-group">
                    <label for="">ID User</label>
                    <input type="hidden" name="iduser" id="iduser" class="form-control form-control-sm" autocomplete="off" value="<?= $iduser ?>" readonly="true">
                    <input type="text" name="userid" id="userid" class="form-control form-control-sm" autocomplete="off" value="<?= $userid ?>" readonly="true">
                </div>
                <div class="form-group">
                    <label for="">Nama Lengkap</label>
                    <input type="text" name="namalengkap" id="namalengkap" class="form-control form-control-sm" autocomplete="off" value="<?= $namalengkap ?>">
                </div>
                <div class="form-group">
                    <label for="">Level User :</label>
                    <select name="level" id="level" class="form-control form-control-sm">
                        <?php foreach ($datalevel->getResultArray() as $l) : ?>

                            <?php if ($level == $l['levelid']) : ?>
                                <option selected value="<?= $l['levelid'] ?>"><?= $l['levelnama'] ?></option>
                            <?php else : ?>
                                <option value="<?= $l['levelid'] ?>"><?= $l['levelnama'] ?></option>
                            <?php endif ?>
                        <?php endforeach ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="">Status User :</label>
                    <input type="checkbox" <?= ($status == '1') ? 'checked' : ''; ?> data-toggle="toggle" data-on="Active" data-off="Non Active" data-onstyle="success" data-offstyle="danger" data-width="150" data-size="xs" class="chStatus">
                </div>
                <div class="form-group viewResetPassword" style="display:none">
                    <label for="">Password Baru Anda :</label>
                    <br>
                    <h3 class="passReset"></h3>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-purple btnreset">
                    <i class="fa fa-recycle"></i> Reset Password
                </button>
                <button type="button" class="btn btn-danger btnhapus">
                    <i class="fa fa-trash-alt"></i> Hapus
                </button>
                <button type="submit" class="btn btn-success btnsimpan">Update</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.btnreset').click(function(e) {
            e.preventDefault();
            let iduser = $('#iduser').val();
            let userid = $('#userid').val();
            showBootstrapModal({
                title: 'Reset Password',
                html: `Anda Yakin Reset ID User <strong>${userid}</strong> Ini ?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Reset !'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "post",
                        url: '<?= site_url('users/resetPassword') ?>',
                        data: {
                            [csrfToken]: csrfHash,
                            iduser: iduser,
                            userid: userid,
                        },
                        dataType: "json",
                        success: function(response) {
                            if (response.sukses == '') {
                                $('.viewResetPassword').show();
                                $('.passReset').html(response.passwordBaru);
                            }
                        }
                    });
                }
            })
        });

        $('.btnhapus').click(function(e) {
            e.preventDefault();
            let iduser = $('#iduser').val();
            let userid = $('#userid').val();
            showBootstrapModal({
                title: 'Hapus User',
                html: `Anda Yakin Hapus ID User <strong>${userid}</strong> Ini ?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus !'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "post",
                        url: '<?= site_url('users/hapus') ?>',
                        data: {
                            [csrfToken]: csrfHash,
                            iduser: iduser,
                            userid: userid,
                        },
                        dataType: "json",
                        success: function(response) {
                            if (response.sukses) {
                                showBootstrapModal({
                                    icon: 'success',
                                    title: 'Berhasil ',
                                    text: response.sukses,
                                });
                                $('#modaledit').modal('hide');
                                dataUser.ajax.reload();
                            }
                        }
                    });
                }
            })
        });

        $('.chStatus').change(function(e) {
            e.preventDefault();
            $.ajax({
                type: "post",
                url: '<?= site_url('users/updateStatus') ?>',
                data: {
                    [csrfToken]: csrfHash,
                    iduser: $('#iduser').val()
                },
                dataType: "json",
                success: function(response) {
                    if (response.sukses == '') {
                        $('#modaledit').modal('hide');
                        dataUser.ajax.reload();
                    }
                }
            });
        });

        $('.frmsimpan').submit(function(e) {
            e.preventDefault();
            $.ajax({
                type: "post",
                url: $(this).attr('action'),
                data: $(this).serialize(),
                dataType: "json",
                cache: false,
                beforeSend: function() {
                    $('.btnsimpan').prop('disable', true);
                    $('.btnsimpan').html('<i class="fa fa-spin fa-spinner"></i>');
                },
                complete: function() {
                    $('.btnsimpan').prop('disable', false);
                    $('.btnsimpan').html('Update');
                },
                success: function(response) {
                    if (response.error) {
                        const errors = Object.values(response.error)
                            .filter(Boolean)
                            .join('<br>');

                        showBootstrapModal({
                            icon: 'error',
                            title: 'Gagal',
                            html: errors
                        });
                        return;
                    }

                    if (response.sukses) {
                        showBootstrapModal({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.sukses
                        });
                        $('#modaledit').modal('hide');
                        dataUser.ajax.reload();
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
