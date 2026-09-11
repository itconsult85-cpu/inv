<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Cetak Data Outstanding</title>
    <style>
        @page { size: A4 portrait; margin: 12mm 14mm; }
        * { box-sizing: border-box; }
        body { color: #111; font-family: Arial, Helvetica, sans-serif; font-size: 11px; margin: 0; }
        .page { max-width: 178mm; margin: 0 auto; }
        .header { border-bottom: 2px solid #222; padding-bottom: 3mm; margin-bottom: 5mm; }
        .header h1 { font-size: 18px; margin: 0 0 3mm; text-align: center; }
        .header-meta { display: flex; justify-content: space-between; align-items: flex-end; font-size: 10px; color: #555; }
        .header-meta .pelanggan-terpilih { font-size: 12px; font-weight: 700; color: #111; }
        table.items { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table.items th, table.items td { border: 1px solid #999; padding: 1.3mm 2mm; overflow-wrap: break-word; }
        table.items th { background: #eee; text-align: center; font-size: 10px; }
        table.items td.right { text-align: right; font-variant-numeric: tabular-nums; }
        table.items td.center { text-align: center; }
        table.items tbody tr.po-first td.po-col { border-top: 1.6px solid #222; }
        table.items td.po-col { vertical-align: top; font-weight: 700; white-space: nowrap; }
        .footer-note { margin-top: 6mm; font-size: 10px; color: #555; }
        .no-print { margin-bottom: 5mm; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>
<div class="page">
    <div class="no-print">
        <button onclick="window.print()">Cetak</button>
    </div>

    <div class="header">
        <h1>Data Outstanding</h1>
        <div class="header-meta">
            <span class="pelanggan-terpilih"><?= esc($namaPelangganCetak) ?></span>
            <span>Dicetak: <?= date('d-m-Y H:i') ?> &mdash; oleh <?= esc($dicetakOleh) ?></span>
        </div>
    </div>

    <?php if (empty($poGroups)) : ?>
        <p>Tidak ada data outstanding untuk ditampilkan.</p>
    <?php else : ?>
        <table class="items">
            <thead>
                <tr>
                    <th style="width:5%;">No</th>
                    <th style="width:13%;">No. PO</th>
                    <th style="width:11%;">Tgl PO</th>
                    <th style="width:21%;">Kode Produk</th>
                    <th style="width:17%;">Qty PO</th>
                    <th style="width:17%;">Terkirim</th>
                    <th style="width:16%;">Belum Terkirim</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; ?>
                <?php foreach ($poGroups as $po) : ?>
                    <?php $jumlahItem = count($po['items']); ?>
                    <?php foreach ($po['items'] as $i => $item) : ?>
                        <tr class="<?= $i === 0 ? 'po-first' : '' ?>">
                            <?php if ($i === 0) : ?>
                                <td class="center po-col" rowspan="<?= $jumlahItem ?>"><?= $no++ ?></td>
                                <td class="po-col" rowspan="<?= $jumlahItem ?>"><?= esc($po['nopo']) ?></td>
                                <td class="center po-col" rowspan="<?= $jumlahItem ?>"><?= $po['tgl'] ? date('d-m-Y', strtotime($po['tgl'])) : '-' ?></td>
                            <?php endif ?>
                            <td><?= esc($item['kodebrg']) ?></td>
                            <td class="right"><?= number_format($item['qty'], 0, ',', '.') ?></td>
                            <td class="right"><?= number_format($item['terkirim'], 0, ',', '.') ?></td>
                            <td class="right"><?= number_format($item['kekurangan'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach ?>
                <?php endforeach ?>
            </tbody>
        </table>
    <?php endif ?>

    <div class="footer-note">
        Total PO: <?= count($poGroups) ?>
    </div>
</div>
<script>
    window.addEventListener('load', function() {
        window.print();
    });
</script>
</body>
</html>
