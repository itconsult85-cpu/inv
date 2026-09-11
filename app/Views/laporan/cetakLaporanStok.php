<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Trisentosa | Laporan Stok</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="<?= base_url() ?>/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url() ?>/dist/css/adminlte.min.css">
    <style type="text/css">
        * {
            font-family: Verdana, Arial, sans-serif;
        }

        /* table {
            font-size: small;
        } */

        .table th {
            background-color: #343a40;
            color: #fff;
            vertical-align: middle;
        }

        tfoot tr td {
            font-weight: bold;
            font-size: medium;
        }

        .gray {
            background-color: lightgray
        }

        @media print {
            /* @page {
                margin: 10mm;
            } */

            .table th {
                background-color: #e5e5e5 !important;
                color: #000 !important;
                vertical-align: middle;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .table-striped tbody tr:nth-of-type(odd) {
                background-color: rgba(0, 0, 0, 0.05) !important;
            }
        }
    </style>
</head>

<body>
    <!-- <div class="wrapper"> -->
    <!-- <section class="invoice"> -->
    <!-- <div class="row"> -->
    <div class="col-10 mx-auto">
        <table width="100%">
            <tr>
                <td valign="middle"><img src="<?= base_url('image/logo.png') ?>" /></td>
                <!-- <td align="center" style="text-align: center;">
                            <h4>Laporan Stok</h4>
                        </td> -->
                <td align="right">
                    <h4>Trisentosa Raya Esolusi</h4>
                    <p>
                        <small> Kawasan Central Cikarang Industrial Park <br>
                            Blok K1 No : 3A Cicau <br>
                            Kec. Cikarang Pusat, Kabupaten Bekasi <br>
                            Jawa Barat 17530 <br>
                            Phone: (62) 812 3311 1163 <br>
                            Email: cs@trisentosaraya.co.id
                        </small>
                    </p>
                </td>
            </tr>
        </table>

        <table width="100%">
            <tr>
                <!-- <td><strong>Pelanggan:</strong> <?= $nama_pelanggan ?></td> -->
                <td><strong>Tanggal Cetak:</strong> <?= date('d-m-Y') ?></td>
            </tr>

        </table>
    </div>
    <!-- </div> -->


    <!-- <div class="row"> -->
    <div class="col-10 mx-auto">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th style="width: 2%; text-align: center; vertical-align: middle;">No</th>
                        <th style="width: auto; text-align: center; vertical-align: middle;">Kode Barang</th>
                        <!-- <th style="text-align: center; width: auto; vertical-align: middle;">Stok Cikarang (Pcs)</th> -->
                        <!-- <th style="text-align: center; width: auto; vertical-align: middle;">Stok Cirebon (Pcs)</th> -->
                        <th style="text-align: center; width: auto; vertical-align: middle;">Total Stok (Pcs)</th>
                        <th style="text-align: center; width: auto; vertical-align: middle;">Total PO</th>
                        <th style="text-align: center; width: auto; vertical-align: middle;">Total Sisa PO</th>
                        <th style="text-align: center; width: auto; vertical-align: middle;">QTY Terkirim (Pcs)</th>
                        <th style="text-align: center; width: auto; vertical-align: middle;">Belum Terkirim (Pcs)</th>
                        <th style="text-align: center; width: auto; vertical-align: middle;">Kekurangan Produksi (Pcs)</th>
                        <th style="text-align: center; width: auto; vertical-align: middle;">Kelebihan Produksi (Pcs)</th>
                    </tr>
                </thead>
                <tbody id="datastok">
                    <?php
                    $nomor = 1;
                    foreach ($data as $index => $row) : ?>
                        <?php
                        $kebutuhanProduksi = $row['kebutuhan_produksi'] ?? 0;
                        $kelebihanProduksi = $row['kelebihan_produksi'] ?? 0;
                        $warnaKebutuhanProduksi = $row['color_kebutuhan_produksi'] ?? ($kebutuhanProduksi > 0 ? 'red' : 'black');
                        $warnaKelebihanProduksi = $row['color_kelebihan_produksi'] ?? ($kelebihanProduksi > 0 ? 'green' : 'black');
                        ?>
                        <tr>
                            <td class="text-center"><?= $nomor++ ?></td>
                            <td class="text-center"><?= $row['kodebarang'] ?></td>
                            <!-- <td class="text-right"><?= number_format($row['totmascik'], 0, ',', '.') ?></td> -->
                            <!-- <td class="text-right"><?= number_format($row['totmascir'], 0, ',', '.') ?></td> -->
                            <td class="text-right"><?= number_format($row['totalstok'], 0, ',', '.') ?></td>
                            <td class="text-right"><?= number_format($row['total_po'], 0, ',', '.') ?></td>
                            <td class="text-right" style="color: <?= $row['color_kekurangan'] ?>"><?= number_format($row['total_sisa'] ?? $row['kekurangan'], 0, ',', '.') ?></td>
                            <td class="text-right"><?= number_format($row['qty_terkirim'], 0, ',', '.') ?></td>
                            <td class="text-right" style="color: <?= $row['color_kekurangan'] ?>">
                                <?= number_format($row['kekurangan'], 0, ',', '.') ?>
                            </td>
                            <td class="text-right" style="color: <?= $warnaKebutuhanProduksi ?>">
                                <?= number_format($kebutuhanProduksi, 0, ',', '.') ?>
                            </td>
                            <td class="text-right" style="color: <?= $warnaKelebihanProduksi ?>">
                                <?= number_format($kelebihanProduksi, 0, ',', '.') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- </div> -->

    <!-- <div class="row"> -->
    <!-- <div class="col-10 mx-auto">
        <p class="text-center">
            Generated by Trisentosa Raya Esolusi &copy; <?= date('Y') ?>
        </p>
    </div> -->
    <!-- </div> -->
    <!-- </section> -->
    <!-- </div> -->

</body>

</html>