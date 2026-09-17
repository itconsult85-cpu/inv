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
            <th style="text-align: center;">No</th>
            <th style="text-align: center;">Kode Produk</th>
            <th style="text-align: center;">Nama Produk</th>
            <th style="text-align: center;">Berat/Ukuran</th>
            <th style="text-align: center;">Jumlah</th>
            <th style="text-align: center;">Total Berat/Ukuran</th>
            <?php if (\App\Libraries\AccessControl::can('produk.masuk.delete')) :  ?>
                <th style="text-align: center;">#</th>
            <?php endif ?>
        </tr>
    </thead>
    <tbody>
        <?php
        $nomor = 1;
        if ($tampildata) {
            foreach ($tampildata->getResultArray() as $row) :
        ?>
                <tr>
                    <td style="text-align: center;">
                        <?= $nomor++; ?>
                        <input type="hidden" value="<?= $row['id'] ?>" id="iddetail">
                    </td>
                    <td style="text-align: center;"><?= $row['kodebarang'] ?>
                        <input type="hidden" value="<?= $row['detbrgkode'] ?>" class="kodebarang">
                    </td>
                    <td style="text-align: center;"><?= $row['namabarang'] ?>
                        <input type="hidden" value="<?= $row['idbarang'] ?>" class="idbarang">
                    </td>
                    <td style="text-align: right;"><?= number_format($row['detberat'], 4, ",", ".") ?>
                        <input type="hidden" value="<?= $row['detberat'] ?>" id="berat">
                    </td>
                    <td id="detjml" style="text-align: right;"><?= number_format($row['detjml'], 0, ",", ".") ?>
                        <input type="hidden" value="<?= $row['stok'] ?>" id="stok">
                    </td>
                    <td style="text-align: right;"><?= number_format($row['detsubtotal'], 2, ",", ".") ?>
                        <input type="hidden" value="<?= $row['detmatkode'] ?>" class="idmaterial">
                    </td>
                    <?php if (\App\Libraries\AccessControl::can('produk.masuk.delete')) :  ?>
                        <td style="text-align: center;">
                            <button type="button" class="btn btn-sm btn-danger" onclick="hapusItem('<?= $row['id'] ?>')">
                                <i class="fa fa-trash-alt"></i>
                            </button>
                            <input type="hidden" value="<?= $row['gudang'] ?>" id="idgudang">
                        </td>
                    <?php endif ?>
                </tr>

        <?php
            endforeach;
        }
        ?>
    </tbody>
</table>

<script>
    $('#datadetail tbody').on('click', 'tr', function() {
        let row = $(this).closest('tr');

        let iddetail = row.find('td input').val();
        let kodebarang = row.find('td:eq(1)').text();
        let namabarang = row.find('td:eq(2)').text();
        let idbarang = row.find('td:eq(2) input').val();
        let berat = row.find('td:eq(3) input').val();
        let stok = row.find('td:eq(4) input').val();
        let idmaterial = row.find('td:eq(5) input').val();
        let idgudang = row.find('td:eq(6) input').val();

        $('#iddetail').val(iddetail);
        $('#idbarang').val(idbarang);
        $('#idgudang').val(idgudang);
        $('#stok1').val(stok);
        $('#idmaterial').val(idmaterial);
        $('#kodebarang').val(kodebarang);
        $('#namabarang').val(namabarang);
        $('#berat').val(berat);

        $('#tombolBatal').fadeIn();
        $('#tombolEditItem').fadeIn();
        $('#kodebarang').prop('disabled', true);
        $('#tombolSimpanItem').fadeOut();
        $('#tombolReload').fadeOut();
        ambilDataBarang();
    });

    $(document).on('click', '#tombolBatal', function(e) {
        e.preventDefault();
        kosong();
        tampilDataDetail();
        $('#kodebarang').prop('disabled', false);
        $('#tombolSimpanItem').fadeIn();
        $('#tombolReload').fadeIn();
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
                    url: '<?= site_url('barangmasuk/hapusItemDetail') ?>',
                    data: {
                        [csrfToken]: csrfHash,
                        id: id
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.sukses) {
                            showBootstrapModal('Berhasil', response.sukses, 'success');
                            tampilDataDetail();
                            ambilTotalBerat();
                            kosong();
                            $('#kodebarang').prop('disabled', false);
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