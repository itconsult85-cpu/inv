<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kebutuhan Material</title>
    <style>
        @page { size: A4 landscape; margin: 12mm; }
        body { font-family: Arial, sans-serif; font-size: 10px; color: #111; }
        h1 { text-align: center; font-size: 18px; margin: 0 0 4px; }
        .subjudul { text-align: center; margin-bottom: 18px; color: #555; }
        .summary { width: 100%; margin-bottom: 14px; border-collapse: collapse; }
        .summary td { padding: 7px; text-align: center; background: #eeeeee; }
        .summary strong { display: block; font-size: 16px; margin-bottom: 3px; }
        .data { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .data th { background: #444; color: #fff; padding: 6px 4px; }
        .data td { padding: 5px 4px; border-bottom: 1px solid #ccc; word-wrap: break-word; }
        .angka { text-align: right; }
        .center { text-align: center; }
        .kurang { color: #c00; font-weight: bold; }
        .belum { color: #9a6700; font-weight: bold; }
        .aman { color: #087b31; font-weight: bold; }
        .warnings { margin-top: 16px; padding: 10px 14px; background: #fff3cd; }
        .vendor { margin-top: 16px; padding: 10px 14px; background: #e8f4ff; }
        .warnings h3 { font-size: 11px; margin: 0 0 5px; }
        .vendor h3 { font-size: 11px; margin: 0 0 5px; }
        .warnings ul, .vendor ul { margin: 0; padding-left: 16px; }
        .footer { margin-top: 10px; font-size: 9px; color: #666; text-align: right; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <?php
        $formatAngka = static function ($nilai): string {
            $hasil = number_format((float) $nilai, 4, ',', '.');
            return rtrim(rtrim($hasil, '0'), ',');
        };
    ?>
    <button class="no-print" onclick="window.print()">Print</button>
    <h1>Laporan Kebutuhan Material</h1>
    <div class="subjudul">
        Berdasarkan outstanding, stok produk jadi, dan stok material<br>
        Dicetak: <?= esc($hasil['generated_at']) ?>
    </div>

    <table class="summary">
        <tr>
            <td><strong><?= (int) $hasil['summary']['total'] ?></strong>Material Dibutuhkan</td>
            <td><strong><?= (int) $hasil['summary']['aman'] ?></strong>Stok Aman</td>
            <td><strong><?= (int) $hasil['summary']['kurang'] ?></strong>Stok Kurang</td>
            <td><strong><?= (int) $hasil['summary']['peringatan'] ?></strong>Peringatan Data</td>
            <td><strong><?= (int) ($hasil['summary']['vendor_supply'] ?? 0) ?></strong>Material Customer</td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th style="width:3%">No</th>
                <th style="width:10%">Kode Material</th>
                <th style="width:14%">Nama Material</th>
                <th style="width:5%">Satuan</th>
                <th>Kebutuhan</th>
                <th>Stok Cikarang</th>
                <th>Stok Cirebon</th>
                <th>Total Stok</th>
                <th>Stok Minimum</th>
                <th>Kekurangan</th>
                <th>Saran Beli</th>
                <th style="width:8%">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$hasil['data']) : ?>
                <tr><td colspan="12" class="center">Tidak ada kebutuhan material untuk filter yang dipilih.</td></tr>
            <?php endif ?>
            <?php foreach ($hasil['data'] as $nomor => $row) : ?>
                <?php
                    $kelasStatus = $row['status'] === 'kurang'
                        ? 'kurang'
                        : ($row['status'] === 'belum_lengkap' ? 'belum' : 'aman');
                    $teksStatus = $row['status'] === 'kurang'
                        ? 'Kurang'
                        : ($row['status'] === 'belum_lengkap' ? 'Belum Lengkap' : 'Aman');
                ?>
                <tr>
                    <td class="center"><?= $nomor + 1 ?></td>
                    <td><?= esc($row['kode_material']) ?></td>
                    <td><?= esc($row['nama_material']) ?></td>
                    <td class="center"><?= esc($row['satuan']) ?></td>
                    <td class="angka"><?= $formatAngka($row['kebutuhan']) ?></td>
                    <td class="angka"><?= $formatAngka($row['stok_cikarang']) ?></td>
                    <td class="angka"><?= $formatAngka($row['stok_cirebon']) ?></td>
                    <td class="angka"><?= $formatAngka($row['total_stok']) ?></td>
                    <td class="angka"><?= $formatAngka($row['stok_minimum']) ?></td>
                    <td class="angka <?= $row['kekurangan'] > 0 ? 'kurang' : '' ?>"><?= $formatAngka($row['kekurangan']) ?></td>
                    <td class="angka"><?= $formatAngka($row['saran_beli']) ?></td>
                    <td class="center <?= $kelasStatus ?>"><?= $teksStatus ?></td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>

    <?php if ($hasil['warnings']) : ?>
        <div class="warnings">
            <h3>Data yang perlu dilengkapi:</h3>
            <ul>
                <?php foreach ($hasil['warnings'] as $warning) : ?>
                    <li><?= esc($warning) ?></li>
                <?php endforeach ?>
            </ul>
        </div>
    <?php endif ?>

    <?php if (!empty($hasil['vendor_supply'])) : ?>
        <div class="vendor">
            <h3>Produk dengan material dari customer:</h3>
            <ul>
                <?php foreach ($hasil['vendor_supply'] as $row) : ?>
                    <li>
                        <strong><?= esc($row['kode_produk']) ?></strong> -
                        <?= esc($row['nama_produk']) ?> (<?= esc($row['pelanggan']) ?>),
                        perlu produksi <?= $formatAngka($row['perlu_produksi']) ?> Pcs.
                        <?= esc($row['keterangan']) ?>
                    </li>
                <?php endforeach ?>
            </ul>
        </div>
    <?php endif ?>

    <div class="footer">Laporan ini merupakan forecast dan tidak mengubah stok atau transaksi.</div>
</body>
</html>
