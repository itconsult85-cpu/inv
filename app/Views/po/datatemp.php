<table class="table table-sm table-hover table-bordered po-draft-table" style="width:100%">
    <thead>
        <tr>
            <th colspan="11" style="text-align: right;">
                <?php
                $totalQty = 0;
                $totalharga = 0;
                foreach ($tampildata->getResultArray() as $row) :
                    $totalQty += $row['detqty'];
                    $totalharga += $row['detharga'];
                endforeach
                ?>
                <h2 style="font-weight:bold">Total : <?= number_format($totalQty, 0, ",", ".") ?> Pcs</h2>
                <input type="hidden" id="qty" value="<?= $totalQty ?>">
                <input type="hidden" id="hargapo" value="<?= $totalharga ?>">
            </th>
        </tr>
    </thead>
    <thead>
        <tr>
            <th style="text-align: center;">No</th>
            <th style="text-align: center;">Kode Produk</th>
            <th style="text-align: center;">Nama Produk</th>
            <th style="text-align: center;">Berat Satuan</th>
            <th style="text-align: center;">Jumlah</th>
            <th class="po-migrasi-draft-column" style="text-align: center;">QTY terkirim</th>
            <th class="po-migrasi-draft-column" style="text-align: center;">Nilai sudah ditagihkan</th>
            <th class="po-migrasi-draft-column" style="text-align: center;">Sisa Awal</th>
            <th style="text-align: center;">Subtotal (KG)</th>
            <th style="text-align: center;">Harga</th>
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
                <td style="text-align: center;"><?= $row['detkodebrg'] ?></td>
                <td style="text-align: center;"><?= $row['brgnama'] ?></td>
                <td style="text-align: right;"><?= number_format($row['detberat'], 4, ",", ".") ?> KG</td>
                <td style="text-align: right;"><?= number_format($row['detqty'], 0, ",", ".") ?> Pcs</td>
                <td class="po-migrasi-draft-column" style="text-align: right;"><?= number_format((float) ($row['detkirim_awal'] ?? 0), 0, ",", ".") ?> Pcs</td>
                <td class="po-migrasi-draft-column" style="text-align: right;">Rp <?= number_format((float) ($row['detinvoice_awal'] ?? 0), 0, ",", ".") ?></td>
                <td class="po-migrasi-draft-column" style="text-align: right;"><?= number_format(max((float) $row['detqty'] - (float) ($row['detkirim_awal'] ?? 0), 0), 0, ",", ".") ?> Pcs</td>
                <td style="text-align: right;"><?= number_format($row['detsubtotal'], 4, ",", ".") ?> KG</td>
                <td style="text-align: right;"><?= number_format($row['detharga'], 0, ",", ".") ?> </td>
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
        Swal.fire({
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
                    url: '<?= site_url('po/hapusItem') ?>',
                    data: {
                        [csrfToken]: csrfHash,
                        id: id
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.sukses) {
                            Swal.fire('Berhasil', response.sukses, 'success');
                            tampilDataTemp();
                            kosong();
                        }
                    }
                });
            }
        })
    }
</script>
