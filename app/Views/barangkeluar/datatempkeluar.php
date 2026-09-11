<table class="table table-sm table-hover table bordered" style="width:100%">
    <thead>
        <tr>
            <th style="text-align: center;">No</th>
            <th style="text-align: center;">No Surat Jalan</th>
            <th style="text-align: center;">No. PO</th>
            <th style="text-align: center;">Kode Produk</th>
            <th style="text-align: center;">Akan Dikirim</th>
            <th style="text-align: center;">#</th>
        </tr>
    </thead>
    <tbody id="shippingData">
        <?php
        $nomor = 1;
        foreach ($tampildatakeluar->getResultArray() as $row) :
        ?>
            <tr data-id="<?= $row['id'] ?>" data-kodebrg="<?= $row['detbrgkode'] ?>">
                <td style="text-align: center;"><?= $nomor++; ?></td>
                <td style="text-align: center;"><?= $row['detfaktur'] ?></td>
                <td style="text-align: center;"><?= $row['detpo'] ?></td>
                <td style="text-align: center;"><?= $row['detbrgkode'] ?></td>
                <td style="text-align: right;" class="akanDikirim"><?= number_format($row['detjml'], 0, ",", ".") ?> Pcs</td>
                <td style="text-align: right;">
                    <button type="button" class="btn btn-sm btn-danger" onclick="hapusItem('<?= $row['id'] ?>')">
                        <i class="fa fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        <?php
        endforeach;
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
                    url: '<?= site_url('barangkeluar/hapusItem') ?>',
                    data: {
                        [csrfToken]: csrfHash,
                        id: id
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.sukses) {
                            Swal.fire('Berhasil', response.sukses, 'success');
                            tampilDataTemp();
                            tampilDataTempKeluar();
                        }
                    }
                });
            }
        })
    }
</script>
