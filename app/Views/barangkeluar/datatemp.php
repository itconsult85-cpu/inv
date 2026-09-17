<table class="table table-sm table-hover table bordered" style="width:100%" id="datadetail">
    <thead>
        <tr>
            <th colspan="7" style="text-align: right;">
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
            <th style="text-align: center;">Berat/Ukuran</th>
            <th style="text-align: center;">Jumlah (Pcs)</th>
            <th style="text-align: center;">Subtotal (KG)</th>
            <th style="text-align: center;">Terkirim (Pcs)</th>
            <th style="text-align: center;">Belum Terkirim (Pcs)</th>
        </tr>
    </thead>
    <tbody id="productData">
        <?php
        $nomor = 1;
        foreach ($tampildata->getResultArray() as $row) :
        ?>
            <tr data-id="<?= $row['detkodebrg'] ?>">
                <td style="text-align: center;"><?= $nomor++; ?>
                    <input type="hidden" value="<?= $row['id'] ?>" class="id">
                </td>
                <td style="text-align: center;"><?= $row['detkodebrg'] ?> </td>
                <td style="text-align: right;"><?= number_format($row['detberat'], 4, ",", ".") ?> KG
                    <input type="hidden" value="<?= $row['detberat'] ?>" class="detberat">
                </td>
                <td style="text-align: right;"><?= number_format($row['detqty'], 0, ",", ".") ?> Pcs
                    <input type="hidden" value="<?= $row['detqty'] ?>" class="detqty">
                </td>
                <td style="text-align: right;"><?= number_format($row['detsubtotal'], 4, ",", ".") ?> KG
                </td>
                <td style="text-align: right;"><?= number_format($row['detkirim'], 0, ",", ".") ?> Pcs
                </td>
                <td style="text-align: right;" class="belumTerkirim"><?= number_format($row['detqty'] - $row['detkirim'], 0, ",", ".") ?> Pcs
                    <input type="hidden" value="<?= $row['detqty'] - $row['detkirim'] ?>" class="kirim">
                </td>
            </tr>
        <?php
        endforeach;
        ?>
    </tbody>
</table>

<script>
    $('#datadetail tbody').on('click', 'tr', function() {
        if (document.getElementById('gudang').value === "") {
            showBootstrapModal('Maaf', 'Silahkan pilih lokasi gudang terlebih dahulu', 'error');
            return; // Jika gudang belum dipilih, keluar dari fungsi
        }

        let row = $(this).closest('tr');

        let kodebarang = row.find('td:eq(1)').text();
        let id = row.find('td input').val();
        let detberat = row.find('td:eq(2) input').val();
        let detqty = row.find('td:eq(3) input').val();
        let kirim = row.find('td:eq(6) input').val();

        $('#iddetail').val(id);
        $('#kirim').val(kirim);
        $('#kodebarang').val(kodebarang);
        $('#detqty').val(detqty);
        $('#detberat').val(detberat);

        $('#tombolBatal').fadeIn();
        $('#tombolEditItem').fadeIn();
        $('#kodebarang').prop('readonly', true);
        $('#tombolCariBarang').prop('disabled', true);
        $('#tombolSimpanItem').fadeIn();
        ambilDataBarang();
    });

    $(document).on('click', '#tombolBatal', function(e) {
        e.preventDefault();
        kosong();
        tampilDataTemp();
        tampilDataDetail();
        $('#kodebarang').prop('readonly', false);
        $('#tombolCariBarang').prop('disabled', false);
        $('#tombolEditItem').fadeOut();
        $('#tombolBatal').fadeOut();
    });
</script>