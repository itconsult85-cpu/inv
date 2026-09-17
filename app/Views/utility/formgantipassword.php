<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Form Ganti Password
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>

<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<?= session()->getFlashdata('pesan') ?>
<?= form_open('utility/updatepassword', ['class' => 'frmupdatepassword']) ?>
<input type="hidden" value="<?= session()->userid ?>">
<div class="form-group row">
    <label for="" class="col-sm-2 col-form-label">Password Lama</label>
    <div class="col-sm-4">
        <input autocomplete="off" type="password" class="form-control" name="passlama" id="passlama">
        <div id="msg-passlama" class="invalid-feedback"></div>
    </div>
</div>
<div class="form-group row">
    <label for="" class="col-sm-2 col-form-label">Password Baru</label>
    <div class="col-sm-4">
        <input autocomplete="off" type="password" class="form-control" name="passbaru" id="passbaru">
        <div id="msg-passbaru" class="invalid-feedback"></div>
    </div>
</div>
<div class="form-group row">
    <label for="" class="col-sm-2 col-form-label">Confirm Password Baru</label>
    <div class="col-sm-4">
        <input autocomplete="off" type="password" class="form-control" name="confirmpassbaru" id="confirmpassbaru">
        <div id="msg-confirmpassbaru" class="invalid-feedback"></div>
    </div>
</div>
<div class="form-group row">
    <label for="" class="col-sm-2 col-form-label"></label>
    <div class="col-sm-4">
        <button type="submit" class="btn btn-success btnsimpan">
            Ganti Password
        </button>
    </div>
</div>
<?= form_close(); ?>

<script>
    $(document).ready(function() {
        $('.frmupdatepassword').submit(function(e) {
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
                    $('.btnsimpan').html('Ganti Password');
                },
                success: function(response) {
                    if (response.error) {
                        let err = response.error;
                        if (err.passlama) {
                            $('#passlama').addClass('is-invalid');
                            $('#msg-passlama').html(err.passlama);
                        } else {
                            $('#passlama').removeClass('is-invalid');
                            $('#passlama').addClass('is-valid');
                            $('#msg-passlama').html('');
                        }
                        if (err.passbaru) {
                            $('#passbaru').addClass('is-invalid');
                            $('#msg-passbaru').html(err.passbaru);
                        } else {
                            $('#passbaru').removeClass('is-invalid');
                            $('#passbaru').addClass('is-valid');
                            $('#msg-passbaru').html('');
                        }
                        if (err.confirmpassbaru) {
                            $('#confirmpassbaru').addClass('is-invalid');
                            $('#msg-confirmpassbaru').html(err.confirmpassbaru);
                        } else {
                            $('#confirmpassbaru').removeClass('is-invalid');
                            $('#confirmpassbaru').addClass('is-valid');
                            $('#msg-confirmpassbaru').html('');
                        }
                    } else {
                        showBootstrapModal({
                            icon: 'success',
                            title: 'Ganti Password',
                            text: response.sukses,
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location = '/login/keluar';
                            }
                        });
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
<?= $this->endSection('isi') ?>