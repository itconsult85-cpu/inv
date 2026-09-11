<?php
$jenisPo = strtolower((string) ($po['jenis_po'] ?? 'material'));
$formatMoney = static fn($value) => number_format((float) $value, 0, ',', '.');
$total = (float) ($po['total_nominal'] ?? 0);
$discount = ((int) ($po['discount_enabled'] ?? 0) === 1) ? max(0, (float) ($po['discount_amount'] ?? 0)) : 0;
$totalAfterDiscount = max(0, $total - $discount);
$ppnIncluded = (int) ($po['ppn_included'] ?? 0) === 1;
$ppn = $ppnIncluded ? 0 : ($totalAfterDiscount * 0.11);
$pph23 = ((int) ($po['pph23_enabled'] ?? 0) === 1) ? max(0, (float) ($po['pph23_amount'] ?? 0)) : 0;
$grandTotal = $totalAfterDiscount + $ppn - $pph23;
// Kalau Discount/PPN terpisah/PPh23 semuanya tidak dipakai, Total dan Grand
// Total nilainya bakal sama persis -- daripada nampilin 2 baris kembar,
// cukup 1 baris "TOTAL" aja.
$adaPenyesuaian = $discount > 0 || !$ppnIncluded || (int) ($po['pph23_enabled'] ?? 0) === 1;
$specLabel = trim((string) ($po['print_spec_label'] ?? '')) ?: 'Lebar Sliting';
$notes = trim((string) ($po['print_notes'] ?? ''));
$materialColumnEnabled = $jenisPo !== 'produk' || (int) ($po['material_column_enabled'] ?? 1) === 1;
$summaryLabelColspan = 2;
$totalColumns = ($jenisPo === 'produk' && !$materialColumnEnabled) ? 6 : 7;
$summaryBlankColspan = $totalColumns - $summaryLabelColspan - 1;
$preparedName = trim((string) ($po['created_by'] ?? ''));
$approvedName = trim((string) ($po['approved_by'] ?? ''));
$attnLines = [];
$supplierPic = trim((string) ($supplier['suppic'] ?? ''));
$supplierEmail = trim((string) ($supplier['supemail'] ?? ''));
$supplierPhone = trim((string) ($supplier['suptelp'] ?? ''));
if ($supplierPic !== '' || $supplierEmail !== '') {
    $attnLines[] = trim($supplierPic . ($supplierEmail !== '' ? ' /' : ''));
    if ($supplierEmail !== '') {
        $attnLines[] = $supplierEmail;
    }
}
if ($supplierPhone !== '') {
    $attnLines[] = $supplierPhone;
}
$notesHtml = [];
if ($notes !== '') {
    foreach (preg_split('/\R/', $notes) as $line) {
        $safeLine = esc($line);
        if (stripos($line, 'PT. TRISENTOSA RAYA ESOLUSI') !== false) {
            $safeLine = '<strong><u>' . $safeLine . '</u></strong>';
        }
        $notesHtml[] = $safeLine;
    }
}
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Purchase Order <?= esc($po['no_po']) ?></title>
    <style>
        @page { size: A4; margin: 13mm 15mm; }

        body { font: 14px Calibri, sans-serif; color: #111; }

        .page { max-width: 190mm; margin: auto; }

        .header {
            align-items: end;
            border-bottom: 1px solid #000;
            display: grid;
            gap: 16px;
            grid-template-columns: 44mm 1fr;
            min-height: 25mm;
            padding-bottom: 1px;
        }

        .header .logo { width: 32mm; padding-bottom: 15px; padding-left: 20px; }

        .header .company { text-align: center; flex: 1; }

        .header .company h1 {
            font-family: "Lucida Fax", Times, serif;
            font-size: 24px;
            line-height: 1;
            margin: 0 0 4px;
            letter-spacing: .5px;
        }

        .header .company p { 
            font-family: "Calibri", Arial, sans-serif;
            margin: 2px 0; 
            font-size: 12px; 
            line-height: 1.15; 
        }

        .title {
            font-family: "Arial Black", Times, serif;
            border-bottom: 4px double #000;
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 1px;
            line-height: 1;
            margin: 11px 0 21px;
            padding: 0 0 9px;
            text-align: center;
        }

        .info {
            display: grid;
            grid-template-columns: 1.08fr 1fr;
            gap: 10mm;
            margin: 4mm 0 24px;
        }

        .info table { width: 100%; border-collapse: collapse; }

        .info th, .info td { text-align: left; vertical-align: top; padding: 1px 0; font-size: 12.5px; line-height: 1.1; }

        .info th { width: 29mm; font-weight: normal; white-space: nowrap; font-size: 12.5px;}

        .info th.underline { text-decoration: underline; }

        .info td.underline { text-decoration: underline; font-style: italic; }

        .info .strong { font-weight: 700; }

        .info .po-no { font-weight: 700; text-decoration: underline; }

        .info td.hanging .hanging-row { display: flex; }

        .info td.hanging .colon { flex: none; margin-right: 4px; }

        .info td.hanging .value { flex: 1 1 auto; }

        .items { width: 100%; border-collapse: collapse; margin-bottom: 0; }

        .items th, .items td { padding: 6px; font-size: 12.5px; }

        .items th {
            -webkit-print-color-adjust: exact;
            background: #c9c9c9;
            border: 1px solid #000;
            box-shadow: inset 0 0 0 9999px #c9c9c9;
            font-weight: bold;
            print-color-adjust: exact;
            text-align: center;
        }

        .items thead tr:first-child th { border-top: 2px solid #000; }

        .items thead th:first-child,
        .items tbody td:first-child { border-left: 2px solid #000; }

        .items thead th:last-child,
        .items tbody td:last-child,
        .items tfoot td:last-child { border-right: 2px solid #000; }

        .items tbody td { border: none; vertical-align: top; }

        .items tbody tr.item-row td,
        .items tbody tr.blank-row td { height: 24px; }

        .items tbody tr.last-body-row td { border-bottom: 2px solid #000; }

        .items td.right { text-align: right; }

        .items td.center { text-align: center; }

        .items tfoot td { border: 1px solid #000; padding: 1px 8px; font-size: 12.5px; }

        .items tfoot td.summary-label { font-weight: bold; text-align: left; }

        .items tfoot td.summary-value { font-weight: bold; text-align: right; }

        .items tfoot td.summary-value-normal { font-weight: normal; text-align: right; }

        .items tfoot td.summary-blank { border: none !important; padding: 0; }

        .items tfoot tr:first-child td.summary-label,
        .items tfoot tr:first-child td.summary-value { border-top: 2px solid #000; }

        .items tfoot tr:last-child td.summary-label,
        .items tfoot tr:last-child td.summary-value { border-bottom: 2px solid #000; }

        .items td.label { text-align: right; font-weight: bold; }

        .items td.value { text-align: right; }

        .note {
            display: grid;
            font-size: 14px;
            gap: 20px;
            grid-template-columns: 72px 1fr;
            line-height: 1.45;
            margin: 4mm 4mm 0;
        }

        .note .note-title { font-weight: bold; text-decoration: underline; }

        .note .note-body div { min-height: 19px; }

        .sign {
            display: grid;
            font-size: 14px;
            grid-template-columns: 24% 24% 30%;
            justify-content: space-between;
            margin: 36mm 0 0;
            text-align: center;
        }

        .sign > div { width: 100%; }

        .sign .signature-slot {
            align-items: center;
            display: flex;
            height: 31mm;
            justify-content: center;
        }

        .sign .name {
            display: inline-block;
            font-weight: bold;
            text-decoration: underline;
        }

        .sign .supplier-line {
            border-top: 1px solid #000;
            display: inline-block;
            min-width: 48mm;
            height: 1px;
        }

        @media print {
            body { zoom: 0.88; }

            .no-print { display: none; }
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="no-print" style="text-align:right; margin-bottom: 10px;">
            <button onclick="window.print()">Cetak</button>
        </div>

        <div class="header">
            <img class="logo" src="<?= base_url('image/logo-pt-tre.png') ?>" alt="TRE">
            <div class="company">
                <h1>PT. TRISENTOSA RAYA ESOLUSI</h1>
                <p>Gedung Masindo lantai III, Jl. Mampang Prapatan Raya no. 73A Jakarta Selatan</p>
                <p>Telp: (021) 798 9670/2215 7445, e-mail: cs@trisentosaraya.co.id</p>
                <p>NPWP : 076.249.385.6-014.000</p>
            </div>
        </div>

        <div class="title">PURCHASE ORDER</div>

        <div class="info">
            <table>
                <tr><th class="underline">To</th><td class="strong">: <?= esc($supplier['supnama'] ?? $po['supplier_nama']) ?></td></tr>
                <tr style="transform: translateY(-1mm);"><th>Address</th><td>: <?= esc($supplier['alamat'] ?? '-') ?></td></tr>
                <tr style="transform: translateY(1mm);">
                    <th>Attn/email</th>
                    <td class="hanging">
                        <div class="hanging-row">
                            <span class="colon">:</span>
                            <span class="value">
                                <?php if (!empty($attnLines)) : ?>
                                    <?php foreach ($attnLines as $i => $attnLine) : ?>
                                        <?= $i > 0 ? '<br>' : '' ?><?= esc($attnLine) ?>
                                    <?php endforeach ?>
                                <?php else : ?>
                                    -
                                <?php endif ?>
                            </span>
                        </div>
                    </td>
                </tr>
            </table>
            <table>
                <tr><th class="strong">P.O No</th><td>: <span class="po-no"><?= esc($po['no_po']) ?></span></td></tr>
                <tr><th>Date</th><td>: <?= esc($tglIndo) ?></td></tr>
                <tr><th>Delivery Time</th><td>: ASAP</td></tr>
                <tr><th>Shipping To</th><td class="hanging"><div class="hanging-row"><span class="colon">:</span><span class="value"><?= esc($po['shipping_to'] ?: '-') ?></span></div></td></tr>
                <tr><th>Quot Number</th><td>: <?= esc($po['quot_number'] ?: '-') ?></td></tr>
                <tr><th>TOP</th><td>: <?= esc($po['top'] ?: '-') ?></td></tr>
                <tr><th>System Payment</th><td>: <?= esc($po['system_payment'] ?: '-') ?></td></tr>
            </table>
        </div>

        <table class="items">
            <thead>
                <?php if ($jenisPo === 'jasa') : ?>
                    <tr>
                        <th rowspan="2" style="width:28px;">No</th>
                        <th style="width:120px;">UKURAN</th>
                        <th rowspan="2">DESCRIPTION</th>
                        <th rowspan="2" style="width:80px;">Quantity</th>
                        <th rowspan="2" style="width:60px;">UoM</th>
                        <th rowspan="2" style="width:90px;">Unit<br>Price (Rp)</th>
                        <th rowspan="2" style="width:100px;">Total<br>Price (Rp)</th>
                    </tr>
                    <tr>
                        <th><?= esc($specLabel) ?></th>
                    </tr>
                <?php elseif ($jenisPo === 'produk') : ?>
                    <tr>
                        <th style="width:28px;">No</th>
                        <?php if ($materialColumnEnabled) : ?>
                            <th style="width:120px;">MATERIAL</th>
                        <?php endif ?>
                        <th>DESCRIPTION</th>
                        <th style="width:70px;">Unit</th>
                        <th style="width:80px;">Qty</th>
                        <th style="width:90px;">Unit<br>Price (Rp)</th>
                        <th style="width:100px;">Total<br>Price (Rp)</th>
                    </tr>
                <?php else : ?>
                    <tr>
                        <th style="width:28px;">No</th>
                        <th style="width:60px;">SIZE</th>
                        <th>DESCRIPTION</th>
                        <th style="width:80px;">QTY</th>
                        <th style="width:60px;">UoM</th>
                        <th style="width:90px;">Unit<br>Price (Rp)</th>
                        <th style="width:100px;">Total<br>Price (Rp)</th>
                    </tr>
                <?php endif ?>
            </thead>
            <tbody>
                <tr class="blank-row">
                    <?php for ($c = 0; $c < $totalColumns; $c++) : ?>
                        <td>&nbsp;</td>
                    <?php endfor ?>
                </tr>
                <?php foreach ($details as $i => $d) : ?>
                    <?php $printSpec = trim((string) ($d['print_spec'] ?? '')); ?>
                    <tr class="item-row">
                        <td class="center"><?= $i + 1 ?></td>
                        <?php if ($materialColumnEnabled) : ?>
                            <td class="center"><?= esc($printSpec !== '' ? $printSpec : '-') ?></td>
                        <?php endif ?>
                        <td><?= esc($d['nama_item']) ?></td>
                        <?php if ($jenisPo === 'produk') : ?>
                            <td class="center"><?= esc($d['satuan']) ?></td>
                            <td class="center"><?= $formatMoney($d['qty_pesan']) ?></td>
                        <?php else : ?>
                            <td class="center"><?= $formatMoney($d['qty_pesan']) ?></td>
                            <td class="center"><?= esc($d['satuan']) ?></td>
                        <?php endif ?>
                        <td class="center"><?= $formatMoney($d['harga']) ?></td>
                        <td class="right"><?= $formatMoney($d['subtotal']) ?></td>
                    </tr>
                <?php endforeach ?>
                <tr class="blank-row last-body-row">
                    <?php for ($c = 0; $c < $totalColumns; $c++) : ?>
                        <td>&nbsp;</td>
                    <?php endfor ?>
                </tr>
            </tbody>
            <tfoot>
                <?php if (!$adaPenyesuaian) : ?>
                    <tr>
                        <td colspan="<?= $summaryBlankColspan ?>" class="summary-blank"></td>
                        <td colspan="<?= $summaryLabelColspan ?>" class="summary-label">TOTAL</td>
                        <td class="summary-value"><?= $formatMoney($total) ?></td>
                    </tr>
                <?php else : ?>
                    <tr>
                        <td colspan="<?= $summaryBlankColspan ?>" class="summary-blank"></td>
                        <td colspan="<?= $summaryLabelColspan ?>" class="summary-label">TOTAL</td>
                        <td class="summary-value"><?= $formatMoney($total) ?></td>
                    </tr>
                    <?php if ($discount > 0) : ?>
                        <tr>
                            <td colspan="<?= $summaryBlankColspan ?>" class="summary-blank"></td>
                            <td colspan="<?= $summaryLabelColspan ?>" class="summary-label">Discount</td>
                            <td class="summary-value"><?= $formatMoney($discount) ?></td>
                        </tr>
                    <?php endif ?>
                    <?php if (!$ppnIncluded) : ?>
                        <tr>
                            <td colspan="<?= $summaryBlankColspan ?>" class="summary-blank"></td>
                            <td colspan="<?= $summaryLabelColspan ?>" class="summary-label">PPN 11%</td>
                            <td class="summary-value-normal"><?= $formatMoney($ppn) ?></td>
                        </tr>
                    <?php endif ?>
                    <?php if ((int) ($po['pph23_enabled'] ?? 0) === 1) : ?>
                        <tr>
                            <td colspan="<?= $summaryBlankColspan ?>" class="summary-blank"></td>
                            <td colspan="<?= $summaryLabelColspan ?>" class="summary-label">PPH 23 (2%)</td>
                            <td class="summary-value-normal"><?= $pph23 > 0 ? $formatMoney($pph23) : '-' ?></td>
                        </tr>
                    <?php endif ?>
                    <tr>
                        <td colspan="<?= $summaryBlankColspan ?>" class="summary-blank"></td>
                        <td colspan="<?= $summaryLabelColspan ?>" class="summary-label">GRAND TOTAL</td>
                        <td class="summary-value"><?= $formatMoney($grandTotal) ?></td>
                    </tr>
                <?php endif ?>
            </tfoot>
        </table>

        <?php if (!empty($notesHtml)) : ?>
        <div class="note">
            <div class="note-title">NOTES</div>
            <div class="note-body">
                <?php foreach ($notesHtml as $i => $line) : ?>
                    <div><?= $i === 0 ? ': ' : '&nbsp;&nbsp;' ?><?= $line ?></div>
                <?php endforeach ?>
            </div>
        </div>
        <?php endif ?>

        <div class="sign">
            <div>
                <div>Prepared by,</div>
                <div class="signature-slot"></div>
                <div class="name"><?= $preparedName !== '' ? esc($preparedName) : '&nbsp;' ?></div>
            </div>
            <div>
                <div>Approved by,</div>
                <div class="signature-slot"></div>
                <div class="name"><?= $approvedName !== '' ? esc($approvedName) : '&nbsp;' ?></div>
            </div>
            <div>
                <div>Supplier</div>
                <div class="signature-slot"></div>
                <div class="supplier-line"></div>
            </div>
        </div>
    </div>
    <script>window.onload = () => window.print();</script>
</body>

</html>
