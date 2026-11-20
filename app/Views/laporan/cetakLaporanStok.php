<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Trisentosa | Laporan Stok</title>

    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
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

        .report-header {
            border-bottom: 2px solid #2d6577;
            border-collapse: collapse;
            margin-bottom: 12px;
            width: 100%;
        }

        .report-header td {
            padding: 0 0 12px;
            vertical-align: middle;
        }

        .report-brand {
            padding-right: 18px !important;
            width: 25%;
        }

        .report-logo {
            display: block;
            height: auto;
            max-height: 68px;
            max-width: 160px;
            object-fit: contain;
            object-position: left center;
            width: 100%;
        }

        .report-company {
            text-align: right;
            width: 75%;
        }

        .report-company-name {
            color: #203647;
            font-size: 18px;
            font-weight: 700;
            letter-spacing: .2px;
            line-height: 1.2;
            margin: 0 0 5px;
        }

        .report-company-address {
            color: #52616b;
            font-size: 10px;
            line-height: 1.5;
            margin: 0;
        }

        .report-meta {
            background-color: #f6f8f9;
            border: 1px solid #d9e1e5;
            border-collapse: collapse;
            margin: 0 0 16px;
            table-layout: fixed;
            width: 100%;
        }

        .report-title-cell {
            padding-left: 0 !important;
            width: 64%;
        }

        .report-info-cell {
            padding: 4px 10px 4px 0 !important;
            text-align: right;
            vertical-align: middle;
            width: 36%;
        }

        .report-info {
            border-collapse: collapse;
            margin-left: auto;
            width: auto;
        }

        .report-kicker {
            color: #2d6577;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 1.5px;
            margin-bottom: 3px;
            text-transform: uppercase;
        }

        .report-title {
            color: #203647;
            font-size: 21px;
            font-weight: 700;
            letter-spacing: .3px;
            line-height: 1.15;
            margin: 0;
        }

        .report-info th,
        .report-info td {
            border-bottom: 1px solid #e1e7ea;
            padding: 5px 0 5px 10px;
            vertical-align: middle;
        }

        .report-info tr:last-child td,
        .report-info tr:last-child th {
            border-bottom: 0;
        }

        .report-info th {
            color: #64717a;
            font-size: 9px;
            font-weight: 600;
            text-align: right;
            white-space: nowrap;
            width: 1%;
        }

        .report-info td {
            color: #253746;
            font-size: 10px;
            font-weight: 600;
            padding-right: 0;
            text-align: right;
            white-space: nowrap;
        }

        .report-total-row td {
            background-color: #eaf0f3;
            border-top: 2px solid #2d6577 !important;
            color: #203647;
            font-size: inherit;
            font-weight: 700;
            padding: .35rem .25rem !important;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 8mm;
            }

            .col-10 {
                max-width: 100%;
                width: 100%;
            }

            .table {
                font-size: 9px;
            }

            .table td,
            .table th {
                padding: .25rem;
            }

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

            .report-header {
                margin-bottom: 5mm;
            }

            .report-header td {
                padding-bottom: 3mm;
            }

            .report-logo {
                max-height: 19mm;
                max-width: 48mm;
            }

            .report-company-name {
                font-size: 15pt;
            }

            .report-company-address {
                font-size: 8.5pt;
            }

            .report-title {
                font-size: 17pt;
            }

            .report-meta {
                margin-bottom: 5mm;
            }

            .report-meta,
            .report-info th,
            .report-info td {
                background-color: #f6f8f9 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .report-total-row td {
                background-color: #eaf0f3 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>
    <!-- <div class="wrapper"> -->
    <!-- <section class="invoice"> -->
    <!-- <div class="row"> -->
    <div class="col-10 mx-auto">
        <table class="report-header">
            <tr>
                <td class="report-brand">
                    <img class="report-logo" src="<?= base_url('image/logo-pt-tre.png') ?>"
                        alt="Logo Trisentosa Raya Esolusi">
                </td>
                <td class="report-company">
                    <h1 class="report-company-name">Trisentosa Raya Esolusi</h1>
                    <p class="report-company-address">
                        Kawasan Central Cikarang Industrial Park, Blok K1 No. 3A Cicau<br>
                        Kec. Cikarang Pusat, Kabupaten Bekasi, Jawa Barat 17530<br>
                        Phone: (62) 812 3311 1163 &nbsp;|&nbsp; Email: cs@trisentosaraya.co.id
                    </p>
                </td>
            </tr>
        </table>

        <table class="report-meta">
            <colgroup>
                <col style="width: 64%;">
                <col style="width: 36%;">
            </colgroup>
            <tr>
                <td class="report-title-cell">
                    <h2 class="report-title">Laporan Stok</h2>
                </td>
                <td class="report-info-cell">
                    <table class="report-info">
                        <tr>
                            <th>Periode Data</th>
                            <td><?= !empty($tglawal) && !empty($tglakhir)
                                ? esc(date('d-m-Y', strtotime($tglawal)) . ' s.d. ' . date('d-m-Y', strtotime($tglakhir)))
                                : 'Semua periode' ?></td>
                        </tr>
                        <tr>
                            <th>Tanggal Cetak</th>
                            <td><?= date('d-m-Y') ?></td>
                        </tr>
                    </table>
                </td>
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
                        <th style="text-align: center; width: auto; vertical-align: middle;">Stok Cikarang (Pcs)</th>
                        <th style="text-align: center; width: auto; vertical-align: middle;">Stok Cirebon (Pcs)</th>
                        <th style="text-align: center; width: auto; vertical-align: middle;">Total Stok (Pcs)</th>
                        <th style="text-align: center; width: auto; vertical-align: middle;">
                            <?= ($mode_po ?? 'all') === 'open' && !empty($tglawal) && !empty($tglakhir)
                                ? 'Total PO (Pcs)'
                                : ((($mode_po ?? 'all') === 'open') ? 'Qty PO Awal pada PO Terbuka Saat Ini (Pcs)' : 'Total PO Kumulatif (Pcs)') ?>
                        </th>
                        <th style="text-align: center; width: auto; vertical-align: middle;">QTY Terkirim (Pcs)</th>
                        <th style="text-align: center; width: auto; vertical-align: middle;">Sisa PO (Pcs)</th>
                        <th style="text-align: center; width: auto; vertical-align: middle;">Kekurangan Produksi (Pcs)
                        </th>
                        <th style="text-align: center; width: auto; vertical-align: middle;">Kelebihan Produksi (Pcs)
                        </th>
                    </tr>
                </thead>
                <tbody id="datastok">
                    <?php
                    $nomor = 1;
                    $total = [
                        'totmascik' => 0,
                        'totmascir' => 0,
                        'totalstok' => 0,
                        'total_po' => 0,
                        'qty_terkirim' => 0,
                        'total_sisa' => 0,
                        'kebutuhan_produksi' => 0,
                        'kelebihan_produksi' => 0,
                    ];
                    foreach ($data as $index => $row) : ?>
                        <?php
                        $kebutuhanProduksi = $row['kebutuhan_produksi'] ?? 0;
                        $kelebihanProduksi = $row['kelebihan_produksi'] ?? 0;
                        $warnaKebutuhanProduksi = $row['color_kebutuhan_produksi'] ?? ($kebutuhanProduksi > 0 ? 'red' : 'black');
                        $warnaKelebihanProduksi = $row['color_kelebihan_produksi'] ?? ($kelebihanProduksi > 0 ? 'green' : 'black');
                        $sisaPo = $row['total_sisa'] ?? $row['kekurangan'] ?? 0;
                        $total['totmascik'] += (float) ($row['totmascik'] ?? 0);
                        $total['totmascir'] += (float) ($row['totmascir'] ?? 0);
                        $total['totalstok'] += (float) ($row['totalstok'] ?? 0);
                        $total['total_po'] += (float) ($row['total_po'] ?? 0);
                        $total['qty_terkirim'] += (float) ($row['qty_terkirim'] ?? 0);
                        $total['total_sisa'] += (float) $sisaPo;
                        $total['kebutuhan_produksi'] += (float) $kebutuhanProduksi;
                        $total['kelebihan_produksi'] += (float) $kelebihanProduksi;
                        ?>
                        <tr>
                            <td class="text-center"><?= $nomor++ ?></td>
                            <td class="text-center"><?= $row['kodebarang'] ?></td>
                            <td class="text-right"><?= number_format($row['totmascik'] ?? 0, 0, ',', '.') ?></td>
                            <td class="text-right"><?= number_format($row['totmascir'] ?? 0, 0, ',', '.') ?></td>
                            <td class="text-right"><?= number_format($row['totalstok'], 0, ',', '.') ?></td>
                            <td class="text-right"><?= number_format($row['total_po'], 0, ',', '.') ?></td>
                            <td class="text-right"><?= number_format($row['qty_terkirim'], 0, ',', '.') ?></td>
                            <td class="text-right" style="color: <?= $row['color_kekurangan'] ?>">
                                <?= number_format($sisaPo, 0, ',', '.') ?></td>
                            <td class="text-right" style="color: <?= $warnaKebutuhanProduksi ?>">
                                <?= number_format($kebutuhanProduksi, 0, ',', '.') ?>
                            </td>
                            <td class="text-right" style="color: <?= $warnaKelebihanProduksi ?>">
                                <?= number_format($kelebihanProduksi, 0, ',', '.') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="report-total-row">
                        <td colspan="2">TOTAL</td>
                        <td class="text-right"><?= number_format($total['totmascik'], 0, ',', '.') ?></td>
                        <td class="text-right"><?= number_format($total['totmascir'], 0, ',', '.') ?></td>
                        <td class="text-right"><?= number_format($total['totalstok'], 0, ',', '.') ?></td>
                        <td class="text-right"><?= number_format($total['total_po'], 0, ',', '.') ?></td>
                        <td class="text-right"><?= number_format($total['qty_terkirim'], 0, ',', '.') ?></td>
                        <td class="text-right"><?= number_format($total['total_sisa'], 0, ',', '.') ?></td>
                        <td class="text-right"><?= number_format($total['kebutuhan_produksi'], 0, ',', '.') ?></td>
                        <td class="text-right"><?= number_format($total['kelebihan_produksi'], 0, ',', '.') ?></td>
                    </tr>
                </tfoot>
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
