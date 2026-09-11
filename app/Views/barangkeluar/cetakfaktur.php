<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Struk Barang Keluar</title>
</head>

<body onload="window.print();">
    <table border="0" style="text-align:center; width:100%">
        <tr>
            <td colspan="2">
                <h3 style="height: 12px;">Tri Sentosa Raya</h3>
                <h5 style="height: 0px;">Jl. Cikarang Utama</h5>
                <h5 style="height: 0px;">Telp: 085156959685</h5>
                <hr style="border: none; border-top: 1px solid #000;">
            </td>
        </tr>
        <tr style="text-align: left;">
            <td>Faktur :</td>
            <td><?= $faktur ?></td>
        </tr>
        <tr style="text-align: left;">
            <td>Tanggal :</td>
            <td><?= date('d-m-Y', strtotime($tanggal)) ?></td>
        </tr>
        <tr style="text-align: left;">
            <td>Pelanggan :</td>
            <td><?= $namapelanggan ?></td>
        </tr>
        <tr>
            <td colspan="2">
                <hr style="border: none; border-top: 1px dashed #000;">
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <table style="width:100%; text-align:left; font-size: 10pt;">
                    <?php
                    $totalItem = 0;
                    $jmlItem = 0;
                    $totalBerat = 0;
                    foreach ($detailbarang->getResultArray() as $row) :
                        $totalItem += $row['detjml'];
                        $jmlItem++;
                        $totalBerat += $row['detsubtotal'];
                    ?>
                        <tr>
                            <td colspan="3"><?= $row['brgnama'] ?></td>
                        </tr>
                        <tr>
                            <td><?= number_format($row['detjml'], 0, ",", ".") . ' ' . $row['satnama'] ?></td>
                            <td style="text-align: rught;"><?= number_format($row['detberat'], 0, ",", ".") ?></td>
                            <td style="text-align: rught;"><?= number_format($row['detsubtotal'], 0, ",", ".") ?></td>
                        </tr>
                    <?php endforeach ?>
                    <tr>
                        <td colspan="3">
                            <hr style="border: none; border-top: 1px dashed #000;">
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            Jml.Item : <?= number_format($jmlItem, 0, ",", ".") . ' (' . number_format($totalItem, 0, ",", ".") . ')' ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <hr style="border: none; border-top: 1px dashed #000;">
                        </td>
                    </tr>
                    <tr style="text-align: right;">
                        <td></td>
                        <td>Total Berat:</td>
                        <td><?= number_format($totalBerat, 4, ",", ".") ?></td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <hr style="border: none; border-top: 1px dashed #000;">
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            Terima Kasih Atas Kunjungan Anda
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>