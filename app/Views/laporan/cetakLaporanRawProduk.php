<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Trisentosa | Laporan Raw</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="<?= base_url() ?>/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url() ?>/dist/css/adminlte.min.css">
    <style type="text/css">
        * {
            font-family: Verdana, Arial, sans-serif;
        }

        table {
            font-size: x-small;
        }

        tfoot tr td {
            font-weight: bold;
            font-size: x-small;
        }

        .gray {
            background-color: lightgray
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <section class="invoice">
            <div class="row">
                <div class="col-4">
                    <h4>
                        <small>
                            <strong>Trisentosa Raya Esolusi</strong><br>
                            Kawasan Central Cikarang Industrial Park<br>
                            Blok K1 No : 3A Cicau,<br>
                            Kec. Cikarang Pusat, Kabupaten Bekasi,<br>
                            Jawa Barat 17530<br>
                            Phone: (62) 812 3311 1163<br>
                            Email: cs@trisentosaraya.co.id
                        </small>
                    </h4>
                </div>
                <!-- <div class="col-4 d-flex justify-content-center align-items-end" style="text-align: center;"> -->
                <div class="col-4 d-flex justify-content-center align-items-center" style="text-align: center;">
                    <img src="<?= base_url() ?>//dist/img/logo.png">
                </div>
                <div class="col-4">
                    <h4>
                        <small class="float-right">Cikarang, <?= date('d F Y') ?></small>
                    </h4>
                </div>
                <!-- /.col -->
            </div>
            <div class="row">
                <div class="col-12 " style="text-align:center;">
                    <h3>
                        Laporan Raw Produk <br>
                        Periode : <?= $tglawal . " s/d " . $tglakhir ?>
                    </h3>
                </div>
            </div>
            <div class="row">
                <div class="col-10 mx-auto">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Supplier</th>
                                    <th>Tanggal</th>
                                    <th>Material</th>
                                    <th>Berat Keluar (KG)</th>
                                    <th>Berat Masuk (KG)</th>
                                    <th>Stok Vendor (KG)</th>
                                </tr>
                            </thead>
                            <tbody id="datastok">
                                <?php
                                $nomor = 1;
                                $totalSeluruhBerat = 0;
                                foreach ($datalaporan->getResultArray() as $row) :
                                    $totalSeluruhBerat += $row['beratng'];
                                    $material = $modelMaterial->getMaterialByJenis($row['matjenis']);
                                    $matnama = $material['matnama'];
                                    $supplier = $modelSupplier->getSupllierById($row['idsup']);
                                    $supnama = $supplier['supnama'];
                                    $beratMatKeluar = $row['beratmatkeluar'];
                                    $beratMatMasuk = $row['beratmatmasuk'];
                                    $beratNg = $beratMatMasuk - $beratMatKeluar;
                                    if ($beratNg < 0) {
                                        $beratNgAbs = abs($beratNg); // Menghilangkan tanda negatif
                                        $beratNgFormatted = '<span style="color: red;">' . number_format($beratNgAbs, 4, ",", ".") . '</span>';
                                        $totalSeluruhBeratFormatted = '<span style="color: red;">' . number_format($totalSeluruhBerat, 4, ",", ".") . '</span>';
                                    } else {
                                        $beratNgFormatted = number_format($beratNg, 4, ",", ".");
                                        $totalSeluruhBeratFormatted = number_format($totalSeluruhBerat, 4, ",", ".");
                                    }
                                    // if ($beratNg < 0) {
                                    //     $beratNg = abs($beratNg);
                                    //     $style = 'color: red;';
                                    // } else {
                                    //     $style = '';
                                    // }
                                ?>
                                    <tr>
                                        <td><?= $nomor++; ?></td>
                                        <td><?= $supnama; ?></td>
                                        <td><?= $row['tgl']; ?></td>
                                        <td><?= $matnama; ?></td>
                                        <td>
                                            <?= number_format($row['beratmatkeluar'], 4, ",", ".") ?>
                                        </td>
                                        <td>
                                            <?= number_format($row['beratmatmasuk'], 4, ",", ".") ?>
                                        </td>
                                        <td>
                                            <?= $beratNgFormatted ?>
                                        </td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th style="text-align :center" colspan="6">Total Stok Vendor</th>
                                    <td style=>
                                        <?= $totalSeluruhBeratFormatted ?>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-10 mx-auto">
                    <p class="lead">
                        Di Laporkan Oleh :
                    </p><br>
                    <p class="text-muted well well-sm shadow-none" style="margin-top: 10px;">
                        <?= session()->namauser ?>
                    </p>
                </div>
            </div>
        </section>
    </div>
    <script>
        window.addEventListener("load", window.print());
    </script>
</body>

</html>