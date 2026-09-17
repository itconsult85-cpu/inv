<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Form Tambah Data Material
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>

<button type="button" class="btn btn-warning" onclick="location.href=('/material/index')">
    <i class="fa fa-undo"></i> Kembali
</button>

<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<div class="card-body">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="kategori">Nama Kategori</label>
                <select name="kategori" id="kategori" class="form-control select2bs4" style="width: 100%;">
                    <option selected value="">-- Pilih Kategori --</option>
                    <?php foreach ($datakategori as $kat) : ?>
                        <option value="<?= $kat['katid'] ?>"><?= $kat['katnama'] ?></option>
                    <?php endforeach ?>
                </select>
            </div>

            <div class="form-group">
                <label for="namamaterial">Nama Material</label>
                <input type="text" class="form-control" id="namamaterial" name="namamaterial" placeholder="Input Nama Material" autofocus>
            </div>

            <div class="form-group">
                <label for="kodematerial">Kode Material</label>
                <input type="text" class="form-control" id="kodematerial" name="kodematerial" placeholder="Input Kode Material" autofocus>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="satuan">Satuan Material</label>
                <select name="satuan" id="satuan" class="form-control">
                    <option selected value="">-- Pilih Satuan --</option>
                    <?php foreach ($datasatuan as $sat) : ?>
                        <option value="<?= $sat['satid'] ?>"><?= $sat['satnama'] ?></option>
                    <?php endforeach ?>
                </select>
            </div>

            <div class="form-group">
                <label for="minstok">Minimal Stok Material</label>
                <input type="number" class="form-control" id="minstok" name="minstok" placeholder="Input Minimal Stok Produk">
            </div>

        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-success" id="tombolSimpanItem">Simpan</button>&nbsp;
            <button type="reset" class="btn btn-warning" id="tombolReload">Reset</button>
        </div>
    </div>
</div>

<script>
    let csrfToken = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';

    $(document).ready(function() {
        $('#tombolReload').click(function(e) {
            e.preventDefault();
            window.location.reload();
        });

        $('#tombolSimpanItem').click(function(e) {
            e.preventDefault();
            let kodematerial = $('#kodematerial').val();
            let namamaterial = $('#namamaterial').val();
            let kategori = $('#kategori').val();
            let satuan = $('#satuan').val();
            let stok = $('#stok').val();
            let minstok = $('#minstok').val();

            // Validasi Kode Material di sisi server
            $.ajax({
                type: "post",
                url: '<?= site_url('material/cekdata') ?>',
                data: {
                    [csrfToken]: csrfHash,
                    kodematerial: kodematerial,
                },
                dataType: "json",
                success: function(response) {
                    if (kodematerial.length == 0) {
                        showBootstrapModal('Error', 'Kode Material harus diinputkan', 'error');
                        return;
                    }
                    if (response.existKode) {
                        showBootstrapModal('Error', 'Kode Material sudah terpakai', 'error');
                    } else {
                        // Validasi Nama Material di sisi server
                        $.ajax({
                            type: "post",
                            url: '<?= site_url('material/cekdata') ?>',
                            data: {
                                [csrfToken]: csrfHash,
                                namamaterial: namamaterial,
                            },
                            dataType: "json",
                            success: function(response) {
                                if (namamaterial.length == 0) {
                                    showBootstrapModal('Error', 'Nama Material harus diinputkan', 'error');
                                    return;
                                }
                                if (response.existNama) {
                                    showBootstrapModal('Error', 'Nama Material sudah terpakai', 'error');
                                } else {
                                    if (kategori.length == 0) {
                                        showBootstrapModal('Error', 'Kategori belum terpilih', 'error');
                                        return;
                                    }

                                    if (satuan.length == 0) {
                                        showBootstrapModal('Error', 'Satuan belum terpilih', 'error');
                                        return;
                                    }

                                    if (minstok.length == 0) {
                                        showBootstrapModal('Error', 'Minimal Stok belum diisi', 'error');
                                        return;
                                    }
                                    showBootstrapModal({
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
                                                url: '<?= site_url('material/simpandata') ?>',
                                                data: {
                                                    [csrfToken]: csrfHash,
                                                    kodematerial: kodematerial,
                                                    namamaterial: namamaterial,
                                                    kategori: kategori,
                                                    satuan: satuan,
                                                    stok: stok,
                                                    minstok: minstok,
                                                },
                                                dataType: "json",
                                                success: function(response) {
                                                    if (response.error) {
                                                        showBootstrapModal({
                                                            title: 'Error',
                                                            icon: 'error',
                                                            html: response.error
                                                        });
                                                    } else if (response.sukses) {
                                                        showBootstrapModal({
                                                            title: 'Berhasil',
                                                            icon: 'success',
                                                            text: response.sukses
                                                        }).then((result) => {
                                                            if (result.isConfirmed) {
                                                                // Redirect ke halaman indeks atau lakukan tindakan lain
                                                                window.location.href = '/material/index';
                                                            }
                                                        });
                                                    }
                                                },
                                                error: function(xhr, ajaxOptions, thrownError) {
                                                    showBootstrapModal('Error', xhr.status + '\n' + thrownError, 'error');
                                                }
                                            });
                                        }
                                    });
                                }
                            },
                            error: function(xhr, ajaxOptions, thrownError) {
                                showBootstrapModal('Error', xhr.status + '\n' + thrownError, 'error');
                            }
                        });
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    showBootstrapModal('Error', xhr.status + '\n' + thrownError, 'error');
                }
            });
        })
    });
</script>
<?= form_close() ?>
<?= $this->endSection('isi') ?>