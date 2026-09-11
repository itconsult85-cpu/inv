<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Permintaan Antar Gudang - <?= esc($header['permintaan']) ?></title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; color: #111; background: #fff; font: 11pt Arial, sans-serif; }
        .page { width: 196mm; min-height: 259mm; margin: 0 auto; padding: 13mm 10mm; }
        .letterhead { display: grid; grid-template-columns: 49% 51%; align-items: center; margin-bottom: 13mm; }
        .logo { width: 76mm; max-height: 38mm; object-fit: contain; object-position: left center; }
        .company { line-height: 1.35; }
        .company-name { margin-bottom: 1.5mm; font-size: 15pt; font-weight: 700; }
        .company-row { display: grid; grid-template-columns: 27mm 4mm 1fr; }
        .company-address { padding-left: 31mm; }
        .report-title {
            margin-bottom: 9mm; padding: 3mm; border: 1px solid #111;
            color: #fff; background: #444; text-align: center; font-size: 17pt; font-weight: 700;
        }
        .summary { width: 56%; margin: 0 2mm 22mm; border-collapse: collapse; table-layout: fixed; }
        .summary th, .summary td { padding: 2.4mm 0; border: 0; }
        .summary th { width: 42%; text-align: left; }
        .summary-label { display: grid; grid-template-columns: 1fr auto; column-gap: 4mm; }
        .summary td { text-align: right; }
        .detail { width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 9pt; }
        .detail th, .detail td {
            padding: 2.4mm 1.5mm;
            border: 0;
            vertical-align: middle;
            overflow-wrap: anywhere;
            word-break: break-word;
        }
        .detail thead th { background: #d0d0d0; text-align: center; font-weight: 700; }
        .detail tbody tr.total-row td { background: #d0d0d0; font-weight: 700; }
        .detail th:last-child,
        .detail td:last-child { padding-left: 3mm; padding-right: 3mm; }
        .detail td:last-child { white-space: nowrap; }
        .center { text-align: center; }
        .right { text-align: right; }
        #debug-icon, #debug-bar { display: none !important; }
        @page { size: Letter portrait; margin: 0; }
        @media print {
            body { width: 216mm; height: 279mm; }
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

    <section class="report-title">Report Permintaan Antar Gudang</section>

    <table class="summary">
        <tbody>
            <tr><th><span class="summary-label"><span>Tanggal</span><span>:</span></span></th><td><?= date('d/m/Y', strtotime($header['tglpermintaan'])) ?></td></tr>
            <tr><th><span class="summary-label"><span>Gudang Asal</span><span>:</span></span></th><td><?= esc($gudangAsal) ?></td></tr>
            <tr><th><span class="summary-label"><span>Gudang Tujuan</span><span>:</span></span></th><td><?= esc($gudangTujuan) ?></td></tr>
            <tr><th><span class="summary-label"><span>Total QTY</span><span>:</span></span></th><td><?= number_format($header['qtypermintaan'], 0, ',', '.') ?></td></tr>
        </tbody>
    </table>

    <table class="detail">
        <colgroup>
            <col style="width: 6%"><col style="width: 14%"><col style="width: 22%"><col style="width: 12%"><col style="width: 15%"><col style="width: 19%"><col style="width: 12%">
        </colgroup>
        <thead>
            <tr>
                <th>No</th><th>Tgl Kirim</th><th>No Surat Jalan</th><th>Pengirim</th>
                <th>Nominal</th><th>Kode Item</th><th>QTY Terkirim</th>
            </tr>
        </thead>
        <tbody>
            <?php $grupSebelumnya = null; $nomor = 0; ?>
            <?php foreach ($rows as $row) : ?>
                <?php $barisPertama = $grupSebelumnya !== $row['faktur']; ?>
                <?php if ($barisPertama) $nomor++; ?>
                <tr>
                    <td class="center"><?= $barisPertama ? $nomor : '' ?></td>
                    <td class="center"><?= $barisPertama ? date('d/m/Y', strtotime($row['tglfaktur'])) : '' ?></td>
                    <td><?= $barisPertama ? esc($header['permintaan']) : '' ?></td>
                    <td class="center"><?= $barisPertama ? esc(($row['picpengirim'] ?? '') ?: $row['usernama']) : '' ?></td>
                    <td class="right"><?= $barisPertama ? 'Rp ' . number_format((float) $row['nominal'], 0, ',', '.') : '' ?></td>
                    <td><?= esc($row['detkodebrg']) ?></td>
                    <td class="right"><?= number_format($row['detqty'], 0, ',', '.') ?> <?= ($row['jenis_item'] ?? 'produk') === 'material' ? 'Kg' : 'Pcs' ?></td>
                </tr>
                <?php $grupSebelumnya = $row['faktur']; ?>
            <?php endforeach ?>
            <tr class="total-row">
                <td colspan="4"></td>
                <td class="right">Rp <?= number_format((float) $totalNominal, 0, ',', '.') ?></td>
                <td></td>
                <td class="right"><?= number_format($totalTerkirim, 0, ',', '.') ?></td>
            </tr>
        </tbody>
    </table>
</main>
</body>
</html>
