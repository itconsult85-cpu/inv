<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Report Permintaan Transfer</title>

    <style>
        .report-table {
            width: calc(100% - 4mm) !important;
            max-width: calc(100% - 4mm) !important;
            margin: 0 auto;
            border-collapse: collapse;
            table-layout: fixed;
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            width: auto;
            max-width: 100%;
            color: #111;
            font-family: Arial, sans-serif;
            font-size: 9px;
        }

        h2 {
            margin-bottom: 6px;
            text-align: center;
        }

        .periode {
            margin-bottom: 20px;
            text-align: center;
        }

        table {
            width: 100%;
            max-width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .report-table th,
        .report-table td {
            padding: 5px 3px;
            border: none !important;
            box-sizing: border-box;
            white-space: normal;
            overflow-wrap: anywhere;
            vertical-align: middle;
        }

        .report-table thead th {
            color: #ffffff !important;
            background-color: #444444 !important;
            font-weight: bold;
            text-align: center;
        }

        #debug-icon,
        #debug-bar {
            display: none !important;
        }


        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        @page {
            size: A4 portrait;
            margin: 12mm;
        }

        @media print {
            .report-table thead th {
                color: #ffffff !important;
                background-color: #444444 !important;
            }

            * {
                print-color-adjust: exact !important;
                -webkit-print-color-adjust: exact !important;
            }
        }
    </style>
</head>

<body onload="window.print()">
    <h2>Report Permintaan Transfer</h2>

    <div class="periode">
        Periode:
        <?= date('d-m-Y', strtotime($tglAwal)) ?>
        sampai
        <?= date('d-m-Y', strtotime($tglAkhir)) ?>
    </div>


    <table class="report-table">

        <colgroup>
            <col style="width: 4%;">
            <col style="width: 15%;">
            <col style="width: 11%;">
            <col style="width: 13%;">
            <col style="width: 8%;">
            <col style="width: 11%;">
            <col style="width: 12%;">
            <col style="width: 12%;">
            <col style="width: 14%;">
        </colgroup>

        <thead>
            <tr>
                <th>No</th>
                <th>No Surat Jalan</th>
                <th>Tanggal Permintaan</th>
                <th>Tanggal Pengiriman</th>
                <th>User</th>
                <th>Total Qty</th>
                <th>Jenis Pengiriman</th>
                <th>PIC Pengirim</th>
                <th>Nominal</th>
            </tr>
        </thead>

        <tbody>
            <?php if (empty($dataPermintaan)) : ?>
                <tr>
                    <td colspan="9" class="text-center">
                        Tidak ada permintaan transfer selesai pada periode ini.
                    </td>
                </tr>
            <?php else : ?>
                <?php $idPermintaanSebelumnya = null; $nomor = 0; ?>
                <?php foreach ($dataPermintaan as $row) : ?>
                    <?php
                    $barisPertama = $idPermintaanSebelumnya !== $row['id'];
                    if ($barisPertama) {
                        $nomor++;
                    }
                    ?>
                    <tr>
                        <td class="text-center">
                            <?= $barisPertama ? $nomor : '' ?>
                        </td>

                        <td><?= $barisPertama ? esc($row['permintaan']) : '' ?></td>

                        <td class="text-center">
                            <?= date(
                                'd-m-Y',
                                strtotime($row['tglpermintaan'])
                            ) ?>
                        </td>

                        <td class="text-center">
                            <?= !empty($row['tglpengiriman'])
                                ? date('d-m-Y', strtotime($row['tglpengiriman']))
                                : '-' ?>
                        </td>

                        <td><?= esc($row['usernama'] ?? '-') ?></td>

                        <td class="text-right">
                            <?= number_format(
                                $row['qtypengiriman'],
                                0,
                                ',',
                                '.'
                            ) ?>
                        </td>

                        <td>
                            <?= esc($row['jenispengiriman'] ?: '-') ?>
                        </td>

                        <td>
                            <?= esc($row['picpengirim'] ?: '-') ?>
                        </td>

                        <td class="text-right">
                            Rp <?= number_format(
                                (float) $row['nominal'],
                                2,
                                ',',
                                '.'
                            ) ?>
                        </td>

                    </tr>
                    <?php $idPermintaanSebelumnya = $row['id']; ?>
                <?php endforeach ?>
            <?php endif ?>
        </tbody>
    </table>
</body>
</html>
