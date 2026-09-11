<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Permintaan Pengiriman - <?= esc($poDipilih) ?></title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; color: #111; font: 9pt Arial, sans-serif; background: #fff; }
        .page { width: 190mm; min-height: 277mm; margin: 0 auto; padding: 10mm 9mm; }
        .letterhead { display: grid; grid-template-columns: 50% 50%; align-items: center; margin-bottom: 13mm; }
        .logo { width: 62mm; max-height: 30mm; object-fit: contain; object-position: left center; }
        .company { line-height: 1.35; }
        .company-name { margin-bottom: 1.5mm; font-size: 12pt; font-weight: 700; }
        .company-row { display: grid; grid-template-columns: 23mm 4mm 1fr; }
        .company-address { padding-left: 27mm; }
        .report-title {
            margin-bottom: 9mm; padding: 2.5mm; border: 1px solid #111;
            color: #fff; background: #444; text-align: center; font-size: 14pt; font-weight: 700;
        }
        .summary { margin: 0 2mm 20mm; }
        .summary-table { width: 58%; table-layout: fixed; }
        .summary-table th,
        .summary-table td { padding: 2mm 0; border: 0; }
        .summary-table th {
            width: 43%;
            text-align: left;
            font-weight: 700;
        }
        .summary-heading {
            display: grid;
            grid-template-columns: 1fr auto;
            column-gap: 4mm;
        }
        .summary-table td { text-align: right; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .detail th,
        .detail td { padding: 2.2mm 1.5mm; border: 0; vertical-align: middle; }
        .detail thead th { color: #111; background: #d0d0d0; font-size: 9pt; text-align: center; }
        .detail td { overflow-wrap: anywhere; word-break: break-word; }
        .center { text-align: center; }
        .number { text-align: right; }
        .total-label,
        .outstanding-label { color: #111; background: #d0d0d0; text-align: center; font-weight: 700; }
        .total-value { font-weight: 700; }
        #debug-icon,
        #debug-bar { display: none !important; }
        @page { size: A4 portrait; margin: 0; }
        @media print {
            body { width: 210mm; height: 297mm; }
            .page { margin: 0 auto; }
            * { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body onload="window.print()">
<main class="page">
    <header class="letterhead">
        <img class="logo" src="<?= base_url('/dist/img/logo-pt-tre.png') ?>" alt="PT Trisentosa Raya E Solusi">
        <div class="company">
            <div class="company-name">PT TRISENTOSA RAYA ESOLUSI</div>
            <div class="company-row"><span>OFFICE</span><span>:</span><span>Gedung Masindo Lantai III No. 72</span></div>
            <div class="company-row"><span>Workshop</span><span>:</span><span>Kawasan CCIP Blok K1 No.3A</span></div>
            <div class="company-address">Cicau, Cikarang Pusat - Bekasi</div>
            <div class="company-row"><span>Telp</span><span>:</span><span>021-221157445</span></div>
            <div class="company-row"><span>Email</span><span>:</span><span>cs@trisentosaraya.co.id</span></div>
        </div>
    </header>

    <section class="report-title">Report Permintaan Pengiriman</section>

    <section class="summary">
        <table class="summary-table">
            <tbody>
                <tr>
                    <th><span class="summary-heading"><span>No. PO</span><span>:</span></span></th>
                    <td><?= esc($poDipilih) ?></td>
                </tr>
                <tr>
                    <th><span class="summary-heading"><span>Tanggal PO</span><span>:</span></span></th>
                    <td><?= $tanggalPo ? date('d/m/Y', strtotime($tanggalPo)) : '-' ?></td>
                </tr>
                <tr>
                    <th><span class="summary-heading"><span>Nama Pelanggan</span><span>:</span></span></th>
                    <td><?= esc($namaPelanggan) ?></td>
                </tr>
                <tr>
                    <th><span class="summary-heading"><span>Total QTY</span><span>:</span></span></th>
                    <td><?= number_format($totalQty, 0, ',', '.') ?></td>
                </tr>
            </tbody>
        </table>
    </section>

    <table class="detail">
        <colgroup>
            <col style="width: 9%"><col style="width: 22%"><col style="width: 24%"><col style="width: 27%"><col style="width: 18%">
        </colgroup>
        <thead>
            <tr><th>No</th><th>Tgl Pengiriman</th><th>No. Surat Jalan</th><th>Nama Barang</th><th>QTY Terkirim</th></tr>
        </thead>
        <tbody>
            <?php if ($rows) : ?>
                <?php foreach ($rows as $index => $row) : ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td class="center"><?= !empty($row['tanggal_pengiriman']) ? date('d/m/Y', strtotime($row['tanggal_pengiriman'])) : '-' ?></td>
                        <td><?= esc($row['no_do']) ?></td>
                        <td><?= esc($row['nama_produk']) ?></td>
                        <td class="number"><?= number_format($row['terkirim'], 0, ',', '.') ?></td>
                    </tr>
                <?php endforeach ?>
            <?php else : ?>
                <tr><td colspan="5" class="center">Belum ada produk yang dikirim untuk PO ini</td></tr>
            <?php endif ?>
            <tr>
                <td colspan="4" class="total-label">Total Terkirim</td>
                <td class="number total-value"><?= number_format($totalTerkirim, 0, ',', '.') ?></td>
            </tr>
            <tr>
                <td colspan="4" class="outstanding-label">Total Outstanding</td>
                <td class="number total-value"><?= number_format($totalOutstanding, 0, ',', '.') ?></td>
            </tr>
        </tbody>
    </table>
</main>
</body>
</html>
