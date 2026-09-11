<?php if (\App\Libraries\AccessControl::can('produk.keluar.override_stok')) :  ?><div class="row mb-3">
        <div class="col-md-4">
            <div class="form-group">
                <label for="jmlPO">Jumlah PO:</label>
                <input type="text" id="jmlPO" class="form-control" readonly>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="jmlSisaPO">Jumlah Sisa PO:</label>
                <input type="text" id="jmlSisaPO" class="form-control" readonly>
            </div>
        </div>
    </div>
<?php endif; ?>
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
            </th>
        </tr>
    </thead>
    <thead>
        <tr>
            <th style="text-align: center;">No</th>
            <th style="text-align: center;">No PO</th>
            <th style="text-align: center;">Kode Produk</th>
            <th style="text-align: center;">Nama Produk</th>
            <th style="text-align: center;">Berat/Ukuran</th>
            <th style="text-align: center;">Terkirim</th>
            <th style="text-align: center;">Belum Terkirim</th>
            <th style="text-align: center;">Subtotal</th>
            <th style="text-align: center;">Gudang</th>
            <?php if (\App\Libraries\AccessControl::can('produk.keluar.delete')) :  ?>
                <th style="text-align: center;">#</th>
            <?php endif ?>
        </tr>
    </thead>
    <tbody>
        <?php
        $nomor = 1;
        foreach ($tampildata->getResultArray() as $row) :
            // var_dump($row);
        ?>
            <tr>
                <td style="text-align: center;">
                    <?= $nomor++; ?>
                    <input type="hidden" value="<?= $row['id'] ?>" id="iddetail">
                </td>
                <td style="text-align: center;"><?= $row['detpo'] ?></td>
                <td style="text-align: center;"><?= $row['detbrgkode'] ?>
                    <input type="hidden" value="<?= $row['idbarang'] ?>" id="idbarang">
                </td>
                <td style="text-align: center;"><?= $row['namabarang'] ?>
                    <input type="hidden" value="<?= $row['namabarang'] ?>" id="namabarang">
                </td>
                <td style="text-align: right;"><?= number_format($row['detberat'], 4, ",", ".") ?> Kg
                    <input type="hidden" value="<?= $row['detberat'] ?>" id="berat">
                </td>
                <td style="text-align: right;"><?= number_format($row['detjml'], 0, ",", ".") ?> Pcs
                    <input type="hidden" value="<?= $row['stok'] ?>" id="stok">
                    <input type="hidden" value="<?= $row['detjml'] ?>" id="detjml">
                    <?php if (\App\Libraries\AccessControl::can('produk.keluar.override_stok')) :  ?>
                        <input type="text" value="<?= $row['stok'] ?>" id="stok">
                        <input type="text" value="<?= $row['detjml'] ?>" id="detjml">
                    <?php endif; ?>
                </td>
                <td style="text-align: right;"><?= number_format($row['detkurang'], 0, ",", ".") ?> Pcs
                    <input type="hidden" value="<?= $row['detkurang'] ?>" id="detkurang">
                </td>
                <td style="text-align: right;"><?= number_format($row['detsubtotal'], 4, ",", ".") ?> Kg
                    <input type="hidden" value="<?= $row['detqty'] ?>" id="detqty">
                    <?php if (\App\Libraries\AccessControl::can('produk.keluar.override_stok')) :  ?>
                        <input type="text" value="<?= $row['detqty'] ?>" id="detqty">
                    <?php endif; ?>
                </td>
                <td style="text-align: center;"><?= $row['gdgnama'] ?>
                    <input type="hidden" value="<?= $row['gdgid'] ?>" id="idgudang">
                </td>
                <?php if (\App\Libraries\AccessControl::can('produk.keluar.delete')) :  ?>
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
                    url: '<?= site_url('barangkeluar/hapusItemDetail') ?>',
                    data: {
                        [csrfToken]: csrfHash,
                        id: id
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.sukses) {
                            Swal.fire('Berhasil', response.sukses, 'success').then((result) => {
                                if (result.isConfirmed) {
                                    window.location.reload();
                                    $('#kodebarang').prop('readonly', false);
                                    $('#datapo').prop('disabled', false);
                                    $('#tombolCariBarang').prop('disabled', false);
                                    $('#tombolSimpanItem').fadeIn();
                                    $('#tombolEditItem').fadeOut();
                                    $('#tombolBatal').fadeOut();
                                }
                            });
                        }
                    }

                });
            }
        })
    }

    $(document).ready(function() {

        // function calculatePOValues() {
        //     var detQty = 0;
        //     var detJml = 0;

        //     $('#datadetail tbody tr').each(function() {
        //         var qty = parseInt($(this).find('#detqty').val()) || 0;
        //         var kurang = parseInt($(this).find('#detjml').val()) || 0;

        //         detQty += qty;
        //         detJml += kurang;
        //     });

        //     var jmlPO = detQty;
        //     var jmlSisaPO = detQty - detJml;

        //     $('#jmlPO').val(jmlPO);
        //     $('#jmlSisaPO').val(jmlSisaPO);
        // // }

        // calculatePOValues();

        // Mengisi input form saat memilih dari dropdown
        $('#datapo').on('change', function() {
            var selectedOption = $(this).find('option:selected');
            $('#kodebarang').val(selectedOption.data('detkodebrg'));
            $('#namabarang').val(selectedOption.data('namabrg'));
            $('#idbarang').val(selectedOption.data('idbarang'));
            $('#idgudang').val(selectedOption.data('idgudang'));
            $('#berat').val(selectedOption.data('detberat'));
            $('#detqty').val(selectedOption.data('detqty'));
            $('#detkurang').val(selectedOption.data('detkurang'));
            $('#stok').val(selectedOption.data('stok'));
        });

        // Mengisi input form saat memilih dari dropdown
        $('#datapo').on('change', function() {
            var selectedOption = $(this).find('option:selected');
            $('#kodebarang').val(selectedOption.data('detkodebrg'));
            $('#namabarang').val(selectedOption.data('namabrg'));
            $('#idbarang').val(selectedOption.data('idbarang'));
            $('#idgudang').val(selectedOption.data('idgudang'));
            $('#berat').val(selectedOption.data('detberat'));
            $('#detqty').val(selectedOption.data('detqty'));
            $('#detkurang').val(selectedOption.data('detkurang'));
            $('#stok').val(selectedOption.data('stok'));
        });

        // Mengisi input form saat memilih dari tabel
        $('#datadetail tbody').on('click', 'tr', function() {
            var row = $(this);
            $('#iddetail').val(row.find('td:eq(0) input').val());
            $('#nopo').val(row.find('td:eq(1)').text().trim());
            $('#kodebarang').val(row.find('td:eq(2)').text().trim());
            $('#idbarang').val(row.find('td:eq(2) input').val());
            $('#namabarang').val(row.find('td:eq(3)').text().trim());
            $('#berat').val(row.find('td:eq(4) input').val());
            $('#stok').val(row.find('td:eq(5) input').val());
            $('#detkurang').val(row.find('td:eq(6) input').val());
            $('#idgudang').val(row.find('td:eq(8) input').val());
            $('#jml').val(row.find('#detjml').val());
            $('#detjmlLama').val(row.find('#detjml').val());
            $('#detqty').val(row.find('#detqty').val());
            $('#tombolBatal').fadeIn();
            $('#tombolEditItem').fadeIn();
            $('#kodebarang').prop('readonly', true);
            $('#datapo').prop('disabled', true);
            $('#tombolSimpanItem').fadeOut();
        });

        // Reset form saat tombol Batal diklik
        $(document).on('click', '#tombolBatal', function(e) {
            e.preventDefault();
            kosong();
            $('#kodebarang').prop('readonly', false);
            $('#datapo').prop('disabled', false);
            $('#tombolSimpanItem').fadeIn();
            $('#tombolEditItem').fadeOut();
            $('#tombolBatal').fadeOut();
        });
    });
</script>
