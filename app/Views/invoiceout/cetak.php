<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Invoice <?= esc($invoice['invoice_no']) ?></title>
    <style>
        @page { size: A4 portrait; margin: 10mm 14mm; }
        * { box-sizing: border-box; }
        html, body { width: 100%; margin: 0; }
        body { color: #111; font-family: Arial, Helvetica, sans-serif; font-size: 10.5px; }
        .invoice-page { width: 100%; max-width: 180mm; margin: 0 auto; }
        .header { display: grid; grid-template-columns: 1fr 1fr; align-items: start; padding: 8mm 0 2mm; }
        .logo-wrap { width: 50mm; padding: 3mm 0 0 8mm; }
        .logo { display: block; width: 49mm; height: auto; max-width: 100%; }
        .company { width: 100%; text-align: right; line-height: 1.35; padding: 0; }
        .company-name { font-size: 12px; font-weight: 700; }
        .header-line { border: 0; border-top: 3px solid #222; margin: 0 0 4mm; }
        .top-grid { display: grid; grid-template-columns: 1.12fr .88fr; gap: 5mm; align-items: end; }
        .delivered-label { margin: 0 0 1mm 7mm; font-size: 11px; font-style: italic; }
        .box { border: 2px solid #222; }
        .delivered-box { height: 25mm; padding-left: 7mm; padding-right: 7mm; line-height: 1.50; font-size: 10px; }
        .invoice-title { border: 1.2px solid #222; font-size: 18px; font-weight: 900; text-align: center; padding: 1.5mm; margin-bottom: 4mm; background-color: #f0f0f0; }
        .invoice-meta { height: 25mm; padding: 0; }
        .meta-table { border-collapse: collapse; width: 100%; }
        .meta-table td { padding: .6mm 1.5mm; vertical-align: top; }
        .meta-table td:nth-child(2) { width: 3mm; padding-right: 0.5mm; }
        .meta-table td:first-child { width: 15mm; }
        .based-on { padding-top: 3mm !important; text-decoration: underline; }
        .items { width: 100%; table-layout: fixed; border-collapse: collapse; border: 2px solid #222; margin-top: 4mm; }
        .items th { border: 1px solid #222; height: 6mm; padding: 1mm 1.5mm; font-size: 11px; text-align: center; background-color: #f0f0f0; }
        .items td { border: 0; height: 6mm; padding: 1mm 1.5mm; overflow-wrap: anywhere; }
        .items tbody tr:first-child td { padding-top: 7mm; }
        .items tbody tr:last-child td { padding-bottom: 7mm; }
        .center { text-align: center; }
        .right { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
        .totals-wrap { display: flex; justify-content: flex-end; margin-top: -2px; font-size: 11px;}
        .totals { width: 56.45mm; border: 2px solid #222; border-collapse: collapse; }
        .totals th, .totals td { border: 1px solid #222; height: 2mm; padding: 0.25mm 1.5mm; }
        .totals th { text-align: center; font-weight: 700; width: 48%; }
        .terbilang-box { width: 110mm; border: 1.5px solid #222; border-collapse: collapse; margin-top: -8.5mm; }
        .terbilang-box th, .terbilang-box td { border: 1px solid #222; height: 8mm; padding: 1mm 2mm; vertical-align: middle; background-color: #fafafa; }
        .terbilang-box th { width: 22mm; text-align: left; font-weight: 100; background-color: #f0f0f0; }
        .total-row th, .total-row td,
        .grand th, .grand td { background-color: #f0f0f0; font-weight: 600; }
        .grand th, .grand td { font-weight: 700; }
        .footer-grid { display: grid; grid-template-columns: 1.25fr .75fr; gap: 12mm; margin-top: 10mm; align-items: start; }
        .bank { border: 1px solid #222; }
        .bank-title { border-bottom: 1px solid #222; text-align: center; padding: 0.5mm; font-weight: 700; background-color: #f0f0f0; }
        .bank-body { padding: 4mm 8mm; line-height: 1.55; }
        .bank-row { display: grid; grid-template-columns: 31mm 4mm 1fr; }
        .signature { min-height: 39mm; text-align: center; padding-top: 1mm; }
        .signature-space { height: 28mm; }
        .status-cancel { color: #c00; border: 2px solid #c00; font-size: 20px; font-weight: 700; text-align: center; padding: 2mm; margin-bottom: 3mm; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>
<?php
$bulan = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$tanggalIndonesia = static function (?string $tanggal) use ($bulan): string {
    if (!$tanggal) return '-';
    $time = strtotime($tanggal);
    return date('d', $time) . ' ' . $bulan[(int) date('n', $time)] . ' ' . date('Y', $time);
};
$minimumRows = max(7, count($details));
$suratJalanList = implode(', ', array_unique(array_filter(array_column($details, 'source_no'))));
?>
<div class="invoice-page">
    <?php if ($invoice['status'] !== 'AKTIF') : ?><div class="status-cancel">DIBATALKAN</div><?php endif ?>

    <header class="header">
        <div class="logo-wrap"><img src="<?= base_url('image/logo-pt-tre.png') ?>" class="logo" alt="TRE"></div>
        <div class="company">
            <div class="company-name">PT Trisentosa Raya Esolusi</div>
            Gedung Masindo Lantai III<br>
            Jl. Mampang Prapatan Raya no. 73A<br>
            Jakarta Selatan<br>
            Telp : (021) 798-9670 / 2215-7445<br>
            Email : cs@trisentosaraya.co.id
        </div>
    </header>
    <hr class="header-line">

    <section class="top-grid">
        <div>
            <div class="delivered-label">Delivered To :</div>
            <div class="box delivered-box">
                <strong><?= esc($invoice['customer_name']) ?></strong><br>
                <?= nl2br(esc($invoice['customer_address'] ?: '-')) ?><br>
                Telp&nbsp;&nbsp;&nbsp;&nbsp;: <?= esc($invoice['customer_phone'] ?: '-') ?><br>
                Fax&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?= esc($invoice['customer_fax'] ?: '-') ?><br>
                To&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?= esc($invoice['customer_to'] ?: 'Bag. Keuangan') ?>
            </div>
        </div>
        <div>
            <div class="invoice-title">INVOICE</div>
            <div class="box invoice-meta">
                <table class="meta-table">
                    <tr><td><strong>Inv. No.</strong></td><td>:</td><td><strong><?= esc($invoice['invoice_no']) ?></strong></td></tr>
                    <tr><td><strong>Inv. Date</strong></td><td>:</td><td><strong><?= $tanggalIndonesia($invoice['invoice_date']) ?></strong></td></tr>
                    <tr><td colspan="3" class="based-on">Based on</td></tr>
                    <tr><td><strong>P.O No.</strong></td><td>:</td><td><strong><?= esc($invoice['po_no']) ?></strong></td></tr>
                    <tr><td><strong>P.O Date</strong></td><td>:</td><td><strong><?= $tanggalIndonesia($invoice['po_date']) ?></strong></td></tr>
                </table>
            </div>
        </div>
    </section>

    <table class="items">
        <colgroup><col style="width:4%"><col style="width:37%"><col style="width:9%"><col style="width:5%"><col style="width:12%"><col style="width:13%"></colgroup>
        <thead><tr><th>NO</th><th>PART NO AND DESCRIPTION</th><th>QTY</th><th>UoM</th><th>PRICE (Rp)</th><th>AMOUNT (Rp)</th></tr></thead>
        <tbody>
        <?php for ($i = 0; $i < $minimumRows; $i++) : $detail = $details[$i] ?? null; ?>
            <tr>
                <td class="center"><?= $detail ? $i + 1 : '&nbsp;' ?></td>
                <td><?= $detail ? esc($detail['product_name']) : '&nbsp;' ?></td>
                <td class="center"><?= $detail ? number_format($detail['qty'], 0, ',', '.') : '&nbsp;' ?></td>
                <td class="center"><?= $detail ? esc($detail['unit']) : '&nbsp;' ?></td>
                <td class="center"><?= $detail ? number_format($detail['unit_price'], 0, ',', '.') : '&nbsp;' ?></td>
                <td class="right"><?= $detail ? number_format($detail['amount'], 0, ',', '.') : '&nbsp;' ?></td>
            </tr>
        <?php endfor ?>
        </tbody>
    </table>

    <div class="totals-wrap">
        <table class="totals">
            <tr class="total-row"><th>TOTAL</th><td class="right"><?= number_format($invoice['subtotal'], 0, ',', '.') ?></td></tr>
            <?php if ((int) ($invoice['ppn_enabled'] ?? 1) === 1) : ?>
                <tr><th>PPN <?= number_format((float) ($invoice['ppn_percent'] ?? 11), 0, ',', '.') ?>%</th><td class="right"><?= number_format($invoice['ppn'], 0, ',', '.') ?></td></tr>
            <?php endif ?>
            <?php if ((int) ($invoice['pph_enabled'] ?? 1) === 1) : ?>
                <tr><th>PPH 23 (<?= number_format((float) ($invoice['pph_percent'] ?? 2), 0, ',', '.') ?>%)</th><td class="right"><?= number_format($invoice['pph23'], 0, ',', '.') ?></td></tr>
            <?php endif ?>
            <?php if ((int) ($invoice['dp_enabled'] ?? 0) === 1) : ?>
                <tr><th>DP <?= number_format((float) ($invoice['dp_percent'] ?? 50), 0, ',', '.') ?>%</th><td class="right"><?= number_format((float) ($invoice['dp_amount'] ?? 0), 0, ',', '.') ?></td></tr>
            <?php endif ?>
            <tr class="grand"><th>GRAND TOTAL</th><td class="right"><?= number_format($invoice['grand_total'], 0, ',', '.') ?></td></tr>
        </table>
    </div>

    <table class="terbilang-box">
        <tr>
            <th>TERBILANG</th>
            <td><?= esc(trim(preg_replace('/\s+/', ' ', $terbilang))) ?></td>
        </tr>
    </table>

    <div class="footer-grid">
        <div class="bank">
            <div class="bank-title">Pembayaran Harap Di Transfer Ke Data Berikut :</div>
            <div class="bank-body">
                <div class="bank-row"><span>Pemilik Rekening</span><span>:</span><strong><?= esc($invoice['bank_owner']) ?></strong></div>
                <div class="bank-row"><span>Nama Bank</span><span>:</span><span><?= esc($invoice['bank_name']) ?></span></div>
                <div class="bank-row"><span>Nomor Rekening</span><span>:</span><span><?= esc($invoice['bank_account']) ?></span></div>
                <div class="bank-row"><span>NPWP</span><span>:</span><span><?= esc($invoice['bank_npwp']) ?></span></div>
            </div>
        </div>
        <div class="signature">
            Hormat Kami
            <div class="signature-space"></div>
            <strong><u><?= esc($invoice['signer_name']) ?></u></strong><br><?= esc($invoice['signer_position']) ?>
        </div>
    </div>
</div>
<script>window.addEventListener('load', function () { window.print(); });</script>
</body>
</html>
