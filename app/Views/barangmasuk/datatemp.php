<table class="table table-sm table-hover table bordered" style="width:100%">
    <thead>
        <tr>
            <th colspan="8" style="text-align: right;">
                <?php
                $totalBerat = 0;
                foreach ($tampildata->getResultArray() as $row) :
                    $totalBerat += $row['detsubtotal'];
                endforeach
                ?>
                <h2 style="font-weight:bold">Total : <?= number_format($totalBerat, 4, ",", ".") ?> Kg</h2>
                <input type="hidden" id="totalberatbarang" value="<?= $totalBerat ?>">
            </th>
        </tr>
    </thead>
    <thead>
        <tr>
            <th style="text-align: center;">No</th>
            <th style="text-align: center;">Kode Produk</th>
            <th style="text-align: center;">Nama Produk</th>
            <th style="text-align: center;">Material</th>
            <th style="text-align: center;">Berat/Ukuran</th>
            <th style="text-align: center;">Jumlah</th>
            <th style="text-align: center;">Total Berat/Ukuran</th>
            <th style="text-align: center;">#</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $nomor = 1;
        foreach ($tampildata->getResultArray() as $row) :
        ?>
            <tr>
                <td style="text-align: center;"><?= $nomor++; ?></td>
                <td style="text-align: center;"><?= $row['detbrgkode'] ?></td>
                <td style="text-align: center;"><?= $row['detbrgnama'] ?></td>
                <td style="text-align: center;"><?= esc($row['matnama'] ?? '-') ?>
                    <input type="hidden" value="<?= esc($row['detmatkode'] ?? '') ?>" id="material" name="material">
                </td>
                <td style="text-align: right;"><?= number_format($row['detberat'], 4, ",", ".") ?>
                    <input type="hidden" value="<?= $row['satuan'] ?>" id="satuan" name="satuan">
                </td>
                <td style="text-align: right;"><?= number_format($row['detjml'], 0, ",", ".") ?> Pcs</td>
                <td style="text-align: right;"><?= number_format($row['detsubtotal'], 4, ",", ".") ?>
                    <!-- <input type="hidden" value="<?= $row['satuanberat'] ?>" id="satuanberat" name="satuanberat"> -->
                </td>
                <td style="text-align: right;">
                    <button type="button" class="btn btn-sm btn-danger" onclick="hapusItem('<?= $row['id'] ?>')">
                        <i class="fa fa-trash-alt"></i>
                    </button>
                </td>
            </tr>

        <?php
        endforeach
        ?>
    </tbody>
</table>

<script>
    function hapusItem(id) {
        showBootstrapModal({
            title: 'Hapus Item ?',
            text: "Yakin item ini dihapus ?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus !'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "post",
                    url: '<?= site_url('barangmasuk/hapusItem') ?>',
                    data: {
                        [csrfToken]: csrfHash,
                        id: id
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.sukses) {
                            showBootstrapModal('Berhasil', response.sukses, 'success');
                            tampilDataTemp();
                            kosong();
                        }
                    }
                });
            }
        })
    }
</script>