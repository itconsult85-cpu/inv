<table class="table table-sm table-hover table bordered" style="width:100%" id="datadetail">
    <thead>
        <tr>
            <th style="text-align: center;">No</th>
            <th style="text-align: center;">Kode Produk</th>
            <th style="text-align: center;">Nama Produk</th>
            <th style="text-align: center;">Jumlah</th>
            <?php if (\App\Libraries\AccessControl::can('produk.transfer.delete')) :  ?>
                <th style="text-align: center;">#</th>
            <?php endif ?>
        </tr>
    </thead>
    <tbody>
        <?php
        $nomor = 1;
        foreach ($tampildata->getResultArray() as $row) :
        ?>
            <tr>
                <td style="text-align: center;">
                    <?= $nomor++; ?>
                    <input type="hidden" value="<?= $row['id'] ?>" id="iddetail">
                </td>
                <td style="text-align: center;"><?= $row['detkodebrg'] ?></td>
                <td style="text-align: center;"><?= $row['namabarang'] ?></td>
                <td style="text-align: right;"><?= number_format($row['detqty'], 0, ",", ".") ?> Pcs</td>
                <?php if (\App\Libraries\AccessControl::can('produk.transfer.delete')) :  ?>
                    <td style="text-align: center;">
                        <button type="button" class="btn btn-sm btn-danger" onclick="hapusItem('<?= $row['id'] ?>')">
                            <i class="fa fa-trash-alt"></i>
                        </button>
                    </td>
                <?php endif ?>
            </tr>

        <?php
        endforeach
        ?>
    </tbody>
</table>

<script>
    $('#datadetail tbody').on('click', 'tr', function() {
        let row = $(this).closest('tr');

        let kodebarang = row.find('td:eq(1)').text();
        let id = row.find('td input').val();

        $('#iddetail').val(id);
        $('#kodebarang').val(kodebarang);

        $('#tombolBatal').fadeIn();
        $('#tombolEditItem').fadeIn();
        $('#kodebarang').prop('readonly', true);
        $('#tombolCariBarang').prop('disabled', true);
        $('#tombolSimpanItem').fadeOut();
        ambilDataBarang();
    });

    $(document).on('click', '#tombolBatal', function(e) {
        e.preventDefault();
        kosong();
        tampilDataDetail();
        $('#kodebarang').prop('readonly', true);
        $('#tombolCariBarang').prop('disabled', false);
        $('#tombolSimpanItem').fadeIn();
        $('#tombolEditItem').fadeOut();
        $('#tombolBatal').fadeOut();
    });

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
                    url: '<?= site_url('permintaanBarangKirim/hapusItemDetailProses') ?>',
                    data: {
                        [csrfToken]: csrfHash,
                        id: id
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.sukses) {
                            showBootstrapModal('Berhasil', response.sukses, 'success');
                            tampilDataDetail();
                            ambilTotalQty();
                            kosong();
                            $('#kodebarang').prop('readonly', false);
                            $('#tombolCariBarang').prop('disabled', false);
                            $('#tombolSimpanItem').fadeIn();
                            $('#tombolEditItem').fadeOut();
                            $('#tombolBatal').fadeOut();
                        }
                    }
                });
            }
        })
    }
</script>