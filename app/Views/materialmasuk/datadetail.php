<table class="table table-sm table-hover table bordered" style="width:100%" id="datadetail">
    <thead>
        <tr>
            <?php
            $totalBerat = 0;
            foreach ($tampildata->getResultArray() as $row) :
                $totalBerat += $row['detsubtotal'];
            endforeach
            ?>
            <input type="hidden" id="totalberat" value="<?= $totalBerat ?>">
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
                <td style="width: 2%; text-align: center;">
                    <?= $nomor++; ?>
                    <input type="hidden" value="<?= $row['id'] ?>" id="id">
                </td>
                <td style="text-align: center;"><?= $row['matkode'] ?>
                    <input type="hidden" value="<?= $row['detmatkode'] ?>" id="materialid">
                    <input type="hidden" value="<?= $row['idmat'] ?>" id="idmat">
                </td>
                <td style="text-align: center;"><?= $row['matnama'] ?></td>
                <td style="text-align: center;"><?= number_format($row['detjml'], 0, ",", ".") ?></td>
                <td style="text-align: right;"><?= number_format($row['detsubtotal'], 0, ",", ".") ?></td>
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
    function kosong() {
        $('#kodematerial').val('');
        $('#materialid').val('');
        $('#idmat').val('');
        $('#stok').val('');
        $('#namamaterial').val('');
        $('#jml').val('1');
        $('#kodematerial').focus();
    }

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
                    url: '<?= site_url('materialmasuk/hapusItemDetail') ?>',
                    data: {
                        [csrfToken]: csrfHash,
                        id: id
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.sukses) {
                            Swal.fire('Berhasil', response.sukses, 'success');
                            tampilDataDetail();
                            ambilTotalBerat();
                            kosong();
                            $('#kodematerial').prop('readonly', false);
                            $('#tombolCariMaterial').prop('disabled', false);
                            $('#tombolSimpanItem').fadeIn();
                            $('#tombolEditItem').fadeOut();
                            $('#tombolBatal').fadeOut();
                        }
                    }
                });
            }
        })
    }

    $('#datadetail tbody').on('click', 'tr', function() {
        let row = $(this).closest('tr');

        let kodematerial = row.find('td:eq(1)').text();
        let materialid = row.find('td:eq(1) input#materialid').val();
        let idmat = row.find('td:eq(1) input#idmat').val();
        let id = row.find('td input').val();

        $('#iddetail').val(id);
        $('#materialid').val(materialid);
        $('#kodematerial').val(kodematerial);
        $('#idmat').val(idmat);

        $('#tombolBatal').fadeIn();
        $('#tombolEditItem').fadeIn();
        $('#kodematerial').prop('readonly', true);
        $('#tombolCariMaterial').prop('disabled', true);
        $('#tombolSimpanItem').fadeOut();
        ambilDataMaterial();
    });

    $(document).on('click', '#tombolBatal', function(e) {
        e.preventDefault();
        kosong();
        tampilDataDetail();
        $('#kodematerial').prop('readonly', false);
        $('#tombolCariMaterial').prop('disabled', false);
        $('#tombolSimpanItem').fadeIn();
        $('#tombolEditItem').fadeOut();
        $('#tombolBatal').fadeOut();
    });
</script>