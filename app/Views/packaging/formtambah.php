<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Form Tambah Data Material
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>

<button type="button" class="btn btn-warning" onclick="location.href=('/packaging/index')">
    <i class="fa fa-undo"></i> Kembali
</button>

<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>

<div class="form-group row">
    <label for="kodematerial" class="col-sm-4 col-form-label">Kode Material</label>
    <div class="col-sm-4">
        <input type="text" class="form-control" id="kodematerial" name="kodematerial" placeholder="Input Kode Material" autofocus>
    </div>
</div>

<div class="form-group row">
    <label for="namamaterial" class="col-sm-4 col-form-label">Nama Material</label>
    <div class="col-sm-4">
        <input type="text" class="form-control" id="namamaterial" name="namamaterial" placeholder="Input Nama Material" autofocus>
    </div>
</div>

<div class="form-group row">
    <label for="stok" class="col-sm-4 col-form-label">Stok</label>
    <div class="col-sm-4">
        <input type="number" class="form-control" id="stok" name="stok" placeholder="0">
    </div>
</div>

<div class="form-group row">
    <label for="gambar" class="col-sm-4 col-form-label"></label>
    <div class="col-sm-4">
        <button type="submit" class="btn btn-success" id="tombolSimpanItem">Simpan</button>&nbsp;
        <button type="reset" class="btn btn-warning" id="tombolReload">Reset</button>
    </div>
</div>

<script>
    let csrfToken = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';

    function kosong() {
        $('#kodematerial').val('').focus();
        $('#namamaterial').val('');
        $('#stok').val('');
    }

    $(document).ready(function() {
        $('#tombolReload').click(function(e) {
            e.preventDefault();
            window.location.reload();
        });

        $('#tombolSimpanItem').click(function(e) {
            e.preventDefault();
            let kodematerial = $('#kodematerial').val();
            let namamaterial = $('#namamaterial').val();
            let stok = $('#stok').val();

            // Validasi Kode Material di sisi server
            $.ajax({
                type: "post",
                url: '<?= site_url('packaging/cekdata') ?>',
                data: {
                    [csrfToken]: csrfHash,
                    kodematerial: kodematerial,
                },
                dataType: "json",
                success: function(response) {
                    if (kodematerial.length == 0) {
                        Swal.fire('Error', 'Kode Material harus diinputkan', 'error');
                        kosong();
                        return;
                    }
                    if (response.existKode) {
                        Swal.fire('Error', 'Kode Material sudah terpakai', 'error');
                    } else {
                        // Validasi Nama Material di sisi server
                        $.ajax({
                            type: "post",
                            url: '<?= site_url('packaging/cekdata') ?>',
                            data: {
                                [csrfToken]: csrfHash,
                                namamaterial: namamaterial,
                            },
                            dataType: "json",
                            success: function(response) {
                                if (namamaterial.length == 0) {
                                    Swal.fire('Error', 'Nama Material harus diinputkan', 'error');
                                    kosong();
                                    return;
                                }
                                if (response.existNama) {
                                    Swal.fire('Error', 'Nama Material sudah terpakai', 'error');
                                } else {
                                    
                                    Swal.fire({
                                        title: 'Selesai Transaksi',
                                        text: "Yakin transaksi ini disimpan?",
                                        icon: 'warning',
                                        showCancelButton: true,
                                        confirmButtonColor: '#3085d6',
                                        cancelButtonColor: '#d33',
                                        confirmButtonText: 'Ya, Simpan Data Transaksi!',
                                        cancelButtonText: 'Tidak'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            // Lakukan penyimpanan transaksi di sini
                                            $.ajax({
                                                type: "post",
                                                url: '<?= site_url('packaging/simpandata') ?>',
                                                data: {
                                                    [csrfToken]: csrfHash,
                                                    kodematerial: kodematerial,
                                                    namamaterial: namamaterial,
                                                    stok: stok,
                                                },
                                                dataType: "json",
                                                success: function(response) {
                                                    if (response.error) {
                                                        Swal.fire({
                                                            title: 'Error',
                                                            icon: 'error',
                                                            html: response.error
                                                        });
                                                    } else if (response.sukses) {
                                                        Swal.fire({
                                                            title: 'Berhasil',
                                                            icon: 'success',
                                                            text: response.sukses
                                                        }).then((result) => {
                                                            if (result.isConfirmed) {
                                                                // Redirect ke halaman indeks atau lakukan tindakan lain
                                                                window.location.href = '/packaging/index';
                                                            }
                                                        });
                                                    }
                                                },
                                                error: function(xhr, ajaxOptions, thrownError) {
                                                    alert(xhr.status + '\n' + thrownError);
                                                }
                                            });
                                        }
                                    });
                                }
                            },
                            error: function(xhr, ajaxOptions, thrownError) {
                                alert(xhr.status + '\n' + thrownError);
                            }
                        });
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert(xhr.status + '\n' + thrownError);
                }
            });
        })
    });
</script>
<?= form_close() ?>
<?= $this->endSection('isi') ?>