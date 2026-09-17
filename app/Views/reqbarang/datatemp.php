<table class="table table-sm table-hover table bordered" style="width:100%">
    <thead>
        <tr>
            <th colspan="8" style="text-align: right;">
                <?php
                $totalQty = 0;
                foreach ($tampildata->getResultArray() as $row) :
                    $totalQty += $row['detqty'];
                endforeach
                ?>
                <h2 style="font-weight:bold">Total Qty : <?= number_format($totalQty, 0, ",", ".") ?></h2>
                <input type="hidden" id="qty" value="<?= $totalQty ?>">
            </th>
        </tr>
    </thead>
    <thead>
        <tr>
            <th style="text-align: center;">No</th>
            <th style="text-align: center;">Jenis</th>
            <th style="text-align: center;">Kode Item</th>
            <th style="text-align: center;">Nama Item</th>
            <th style="text-align: center;">Gudang Asal</th>
            <th style="text-align: center;">Jumlah</th>
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
                <td style="text-align: center;"><?= ucfirst($row['jenis_item'] ?? 'produk') ?></td>
                <td style="text-align: center;"><?= $row['detkodebrg'] ?></td>
                <td style="text-align: center;"><?= $row['namabarang'] ?></td>
                <td style="text-align: center;"><?= esc($row['gdgnama'] ?? '-') ?></td>
                <td style="text-align: right;"><?= number_format($row['detqty'], 0, ",", ".") ?> <?= ($row['jenis_item'] ?? 'produk') === 'material' ? 'Kg' : 'Pcs' ?></td>
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
                    url: '<?= site_url('permintaanBarang/hapusItem') ?>',
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
