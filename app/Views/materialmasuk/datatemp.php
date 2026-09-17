<table class="table table-sm table-hover table bordered" style="width:100%">
    <thead>
        <tr>
            <th colspan="6" style="text-align: right;">
                <?php
                $totalBerat = 0;
                foreach ($tampildata->getResultArray() as $row) :
                    $totalBerat += $row['detsubtotal'];
                endforeach
                ?>
                <h2 style="font-weight:bold">Total : <?= number_format($totalBerat, 0, ",", ".") ?> KG</h2>
                <input type="hidden" id="totalberatmaterial" value="<?= $totalBerat ?>">
            </th>
        </tr>
    </thead>
    <thead>
        <tr>
            <th style="width: 2%; text-align: center;">No</th>
            <th style="text-align: center;">Kode Material</th>
            <th style="text-align: center;">Nama Material</th>
            <th style="text-align: center;">Jumlah</th>
            <th style="text-align: center;">Subtotal</th>
            <th style="width: 2%; text-align: center;">#</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $nomor = 1;
        foreach ($tampildata->getResultArray() as $row) :
        ?>
            <tr>
                <td style="width: 2%; text-align: center;"><?= $nomor++; ?></td>
                <td style="text-align: center;"><?= $row['matkode'] ?></td>
                <td style="text-align: center;"><?= $row['matnama'] ?></td>
                <td style="text-align: right;"><?= number_format($row['detjml'], 0, ",", ".") ?> KG</td>
                <td style="text-align: right;"><?= number_format($row['detsubtotal'], 0, ",", ".") ?> KG</td>
                <td style="width: 2%; text-align: center;">
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
                    url: '<?= site_url('materialmasuk/hapusItem') ?>',
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