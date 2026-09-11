<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Delivery Order <?= esc($header['faktur']) ?></title>
    <style>
        /* Format lapangan A5 landscape: 210 x 148 mm; area kerja 194 x 143,5 mm. */
        @page {
            size: A5 landscape;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
        }

        body {
            background: #fff;
            color: #111;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5pt;
            line-height: 1.12;
        }

        .do-page {
            width: 210mm;
            height: 148mm;
            margin: 0;
            /* Lebar kerja mengikuti format fisik: 210 - (8 + 8) = 194 mm. */
            padding: 2.25mm 8mm;
            page-break-after: always;
            overflow: hidden;
        }

        .do-page:last-child {
            page-break-after: auto;
        }

        .do-frame {
            width: 194mm;
            height: 72mm;
            outline: .45mm solid #111;
            border: 0;
            display: block;
            overflow: hidden;
        }

        .brand {
            height: 20mm;
            flex: 0 0 20mm;
            display: flex;
            align-items: center;
            padding: 2.2mm 5.5mm;
            border-bottom: .45mm solid #111;
        }

        .brand-logo {
            width: 30mm;
            height: auto;
            max-height: 15mm;
            object-fit: contain;
            display: block;
            margin-right: 5mm;
        }

        .brand-logo-fallback {
            width: 30mm;
            height: 15mm;
            margin-right: 5mm;
            display: none;
            font-size: 28px;
            font-weight: 900;
            letter-spacing: -5px;
            line-height: 15mm;
            white-space: nowrap;
        }

        .brand-logo-fallback span:nth-child(1) {
            color: #111;
        }

        .brand-logo-fallback span:nth-child(2) {
            color: #d42e2e;
        }

        .brand-logo-fallback span:nth-child(3) {
            color: #111;
        }

        .brand-title {
            font-size: 22px;
            line-height: 1;
            font-weight: 800;
            letter-spacing: .6px;
            white-space: nowrap;
        }

        .info-grid {
            height: 27mm;
            flex: 0 0 27mm;
            display: grid;
            /* Ruang panel DO diperlebar agar nomor DO panjang tetap terbaca. */
            /* Pembagian fisik: area office 124 mm dan DO 70 mm. */
            grid-template-columns: 124mm 70mm;
            border-bottom: .45mm solid #111;
        }

        .office-box {
            /* Tabel Office diposisikan di tengah tinggi panel informasi. */
            padding: 0 5.5mm;
            display: flex;
            align-items: center;
            border-right: .45mm solid #111;
        }

        .office-table,
        .do-meta {
            width: 100%;
            border-collapse: collapse;
        }

        .office-table {
            /* Ukuran sebelumnya terlalu kecil saat dicetak di A5 landscape. */
            font-size: 7.2pt;
            line-height: 1.08;
        }

        .office-table td,
        .do-meta td {
            padding: .25mm 0;
            vertical-align: middle;
        }

        .office-table td:last-child {
            white-space: nowrap;
        }

        .office-table td:first-child {
            width: 18mm;
        }

        .office-table td:nth-child(2) {
            width: 4mm;
            text-align: center;
        }

        .do-box {
            min-width: 0;
        }

        .do-title {
            height: 7mm;
            display: flex;
            justify-content: center;
            align-items: center;
            border-bottom: .45mm solid #111;
            font-family: "Times New Roman", serif;
            font-size: 14px;
            font-style: italic;
            font-weight: 700;
            letter-spacing: .7px;
        }

        .do-meta {
            width: 100%;
            table-layout: fixed;
            margin-top: .7mm;
            font-size: 8.1pt;
        }

        .do-meta td {
            height: 3.9mm;
            padding: .2mm 1.5mm;
            vertical-align: middle;
            /* Nilai boleh membungkus, tetapi tidak lagi dipotong dengan ellipsis. */
            white-space: normal;
            overflow: visible;
            text-overflow: clip;
            overflow-wrap: anywhere;
        }

        .do-meta td:first-child {
            width: 25.5mm;
            white-space: nowrap;
        }

        .do-meta td:nth-child(2) {
            width: 3.5mm;
            text-align: center;
        }

        .do-meta td:last-child {
            width: auto;
        }

        .do-meta tr:nth-child(2) td:last-child {
            font-size: 7.1pt;
            font-weight: 700;
        }

        .do-meta .do-number {
            white-space: nowrap;
            overflow: visible;
            overflow-wrap: normal;
            letter-spacing: -.2px;
        }

        .delivery {
            height: 25mm;
            flex: 0 0 25mm;
            padding: 1.9mm 5.5mm;
            border-bottom: .45mm solid #111;
        }

        .delivery-label {
            margin-bottom: 2.4mm;
        }

        .recipient {
            margin-left: 28mm;
            width: 145mm;
            font-size: 9pt;
        }

        .recipient-name {
            margin-bottom: 1.1mm;
            font-size: 11pt;
            font-weight: 800;
        }

        .recipient-address {
            white-space: pre-line;
        }

        .items {
            width: 194mm;
            height: 44mm;
            flex: 0 0 44mm;
            margin-top: 3mm;
            border: .45mm solid #111;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .items th,
        .items td {
            border-right: .3mm solid #111;
            border-bottom: 0;
            padding: .9mm 1.5mm;
            vertical-align: top;
            overflow-wrap: anywhere;
        }

        .items th:last-child,
        .items td:last-child {
            border-right: 0;
        }

        .items th {
            height: 8mm;
            text-align: center;
            font-size: 8.5pt;
            font-weight: 800;
            vertical-align: middle;
            border-bottom: .45mm solid #111;
        }

        /* Baris produk dibuat rapat; ruang kosong tetap ditampung oleh baris kosong. */
        .items .body-row td {
            height: 4.5mm;
            padding-top: .35mm;
            padding-bottom: .35mm;
            line-height: 1.05;
        }

        .items .empty-row td {
            height: 27mm;
            padding-top: 0;
            padding-bottom: 0;
            color: transparent;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }

        .product-name {
            font-weight: 400;
        }

        .spec-line {
            display: none;
        }

        .signature-grid {
            width: 194mm;
            height: 24mm;
            flex: 0 0 24mm;
            display: grid;
            /* Pembatas tanda tangan sejajar dengan batas kolom 11 + 113 mm. */
            grid-template-columns: 124mm 70mm;
            border: .45mm solid #111;
        }

        .signature-box {
            position: relative;
            padding: 2.5mm 4mm;
            text-align: center;
        }

        .signature-box:first-child {
            border-right: .45mm solid #111;
        }

        .signature-title {
            font-size: 9pt;
        }

        .signature-name {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 2.2mm;
            font-size: 9pt;
        }

        /* Format tanpa tabel: hanya teks data pada kertas A5 landscape putih. */
        .plain-page {
            position: relative;
            width: 210mm;
            height: 148mm;
            padding: 0;
            page-break-after: always;
            overflow: hidden;
        }

        .plain-value {
            position: absolute;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: clip;
            font-size: 8.5pt;
            line-height: 1.1;
        }

        .plain-tanggal {
            left: 124mm;
            top: 22mm;
            width: 70mm;
        }

        .plain-do {
            left: 124mm;
            top: 26mm;
            width: 70mm;
            font-size: 7.6pt;
            font-weight: 700;
            letter-spacing: -.2px;
        }

        .plain-po {
            left: 124mm;
            top: 30mm;
            width: 70mm;
            font-size: 7.8pt;
        }

        .plain-kendaraan {
            left: 124mm;
            top: 34mm;
            width: 70mm;
        }

        .plain-pelanggan {
            left: 32mm;
            top: 56mm;
            width: 164mm;
            font-size: 11pt;
            font-weight: 700;
        }

        .plain-alamat {
            left: 32mm;
            top: 61.3mm;
            width: 170mm;
            font-size: 10pt;
        }

        .plain-row {
            position: absolute;
            left: 0;
            width: 210mm;
            height: 4.5mm;
            font-size: 8.5pt;
            line-height: 1.1;
        }

        .plain-row-number {
            position: absolute;
            left: 13mm;
            width: 5mm;
            text-align: center;
        }

        .plain-row-part {
            position: absolute;
            left: 20.5mm;
            width: 111mm;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: clip;
        }

        .plain-row-qty {
            position: absolute;
            left: 98mm;
            width: 15mm;
            text-align: right;
            white-space: nowrap;
        }

        .plain-row-unit {
            position: absolute;
            left: 116.5mm;
            width: 15mm;
            white-space: nowrap;
        }

        .screen-tools {
            position: fixed;
            right: 5mm;
            top: 5mm;
            z-index: 10;
        }

        .screen-tools button {
            border: 1px solid #555;
            background: #fff;
            padding: 2mm 4mm;
            cursor: pointer;
        }

        @media print {
            .screen-tools {
                display: none;
            }

            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>
    <?php
    $hari = [
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu',
    ];
    $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $tanggalLengkap = static function (?string $tanggal) use ($hari, $bulan): string {
        if (!$tanggal) return '-';
        $time = strtotime($tanggal);
        return $hari[date('l', $time)] . ', ' . date('d', $time) . ' ' . $bulan[(int) date('n', $time)] . ' ' . date('Y', $time);
    };
    $normalizeUom = static function (?string $satuan): string {
        $satuan = strtoupper(trim((string) $satuan));
        return in_array($satuan, ['PCS', 'PCS.', 'PC'], true) ? 'PC' : ($satuan ?: 'PC');
    };
    $noKendaraan = trim((string) ($noKendaraan ?? ''));
    $pengirimBarang = trim((string) ($pengirimBarang ?? ''));
    $notes = is_array($notes ?? null) ? $notes : [];
    $preview = (bool) ($preview ?? false);
    $format = in_array(($format ?? 'lengkap'), ['lengkap', 'ringkas'], true) ? $format : 'lengkap';
    $isRingkas = $format === 'ringkas';
    $details = is_array($details ?? null) ? $details : [];
    $detailPages = array_chunk($details, 4);
    if ($detailPages === []) $detailPages = [[]];
    $daftarPoText = implode(', ', array_filter($header['daftar_po'] ?? [$header['detpo']])) ?: '-';
    $alamatPelanggan = trim((string) ($header['pelalamat'] ?? ''));
    ?>
    <?php if ($isRingkas): ?>
        <main class="plain-page">
            <span class="plain-value plain-tanggal"><?= esc(date('d-m-Y', strtotime((string) $header['tglfaktur']))) ?></span>
            <span class="plain-value plain-do"><?= esc($header['faktur']) ?></span>
            <span class="plain-value plain-po"><?= esc($daftarPoText) ?></span>
            <span class="plain-value plain-kendaraan"><?= esc($noKendaraan !== '' ? $noKendaraan : '') ?></span>
            <span class="plain-value plain-pelanggan"><?= esc($header['pelnama']) ?></span>
            <span class="plain-value plain-alamat"><?= esc($alamatPelanggan) ?></span>
            <?php foreach ($details as $index => $detail): ?>
                <div class="plain-row" style="top: <?= 80.5 + ($index * 4.5) ?>mm;">
                    <span class="plain-row-number"><?= $index + 1 ?></span>
                    <span class="plain-row-part"><?= esc($detail['detbrgkode'] ?? '') ?></span>
                    <span class="plain-row-qty"><?= number_format((float) ($detail['detjml'] ?? 0), 0, ',', '.') ?></span>
                    <span class="plain-row-unit"><?= esc($normalizeUom($detail['satnama'] ?? 'Pcs')) ?></span>
                </div>
            <?php endforeach; ?>
        </main>
    <?php else: ?>
        <?php foreach ($detailPages as $pageIndex => $pageDetails): ?>
            <main class="do-page">
                <div class="do-frame">
                    <section class="brand">
                        <img src="<?= base_url('image/logo-pt-tre.png') ?>" class="brand-logo" alt="TRE" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <div class="brand-logo-fallback" aria-hidden="true"><span>T</span><span>T</span><span>E</span></div>
                        <div class="brand-title">PT. TRISENTOSA RAYA ESOLUSI</div>
                    </section>

                    <section class="info-grid">
                        <div class="office-box">
                            <table class="office-table">
                                <tr>
                                    <td>Office I</td>
                                    <td>:</td>
                                    <td><?= $isRingkas ? '' : 'Gedung Masindo Lantai III No. 73A Jakarta Selatan' ?></td>
                                </tr>
                                <tr>
                                    <td>Office II</td>
                                    <td>:</td>
                                    <td><?= $isRingkas ? '' : 'Kawasan Industri CCIP Blok K1 No. 3A, Cicau, Cikarang Pusat, Bekasi' ?></td>
                                </tr>
                                <tr>
                                    <td>Office III</td>
                                    <td>:</td>
                                    <td><?= $isRingkas ? '' : 'Blok Karya Bhakti, Desa Tegal Sari, Kec. Plered, Kab. Cirebon' ?></td>
                                </tr>
                                <tr>
                                    <td>No. Telp</td>
                                    <td>:</td>
                                    <td><?= $isRingkas ? '' : '021 - 22157445' ?></td>
                                </tr>
                                <tr>
                                    <td>Email</td>
                                    <td>:</td>
                                    <td><?= $isRingkas ? '' : '<strong>cs@trisentosaraya.co.id</strong>' ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="do-box">
                            <div class="do-title">DELIVERY ORDER</div>
                            <table class="do-meta">
                                <tr>
                                    <td>Tanggal</td>
                                    <td>:</td>
                                    <td><?= esc(date('d-m-Y', strtotime((string) $header['tglfaktur']))) ?></td>
                                </tr>
                                <tr>
                                    <td>No. DO</td>
                                    <td>:</td>
                                    <td class="do-value do-number"><?= esc($header['faktur']) ?></td>
                                </tr>
                                <tr>
                                    <td>No. PO</td>
                                    <td>:</td>
                                    <td class="do-value"><?= esc($daftarPoText) ?></td>
                                </tr>
                                <tr>
                                    <td>No. Kendaraan</td>
                                    <td>:</td>
                                    <td class="do-value"><?= esc($noKendaraan !== '' ? $noKendaraan : '-') ?></td>
                                </tr>
                            </table>
                        </div>
                    </section>

                    <section class="delivery">
                        <div class="delivery-label">Di Kirim&nbsp;&nbsp;Kepada Yth :</div>
                        <div class="recipient">
                            <div class="recipient-name"><?= $isRingkas ? '' : esc($header['pelnama']) ?></div>
                            <div class="recipient-address"><?= $isRingkas ? '' : ($alamatPelanggan !== '' ? nl2br(esc($alamatPelanggan)) : '-') ?></div>
                        </div>
                    </section>
                </div>

                <table class="items">
                    <colgroup>
                        <col style="width:11mm">
                        <col style="width:113mm">
                        <col style="width:15mm">
                        <col style="width:15mm">
                        <col style="width:40mm">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>NO</th>
                            <th>PART NUMBER / DESCRIPTION</th>
                            <th>QTY</th>
                            <th>UNIT</th>
                            <th>NOTES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pageDetails as $index => $detail): ?>
                            <?php
                            $qty = (float) ($detail['detjml'] ?? 0);
                            $uom = $normalizeUom($detail['satnama'] ?? 'Pcs');
                            $materialText = trim((string) (($detail['matkode'] ?? '') . ' ' . ($detail['matnama'] ?? '')));
                            $noteProduk = trim((string) ($notes[(string) ($detail['id'] ?? '')] ?? ''));
                            ?>
                            <tr class="body-row">
                                <td class="center"><?= ($pageIndex * 4) + $index + 1 ?></td>
                                <td>
                                    <div class="product-name"><?= esc($isRingkas ? ($detail['detbrgkode'] ?? '') : ($detail['namabarang'] ?? '')) ?></div>
                                </td>
                                <td class="right"><?= $isRingkas ? '' : number_format($qty, 0, ',', '.') ?></td>
                                <td class="center"><?= $isRingkas ? '' : esc($uom) ?></td>
                                <td><?= $isRingkas ? '' : ($noteProduk !== '' ? esc($noteProduk) : '&nbsp;') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (count($pageDetails) < 4): ?>
                            <tr class="empty-row">
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <section class="signature-grid">
                    <div class="signature-box">
                        <div class="signature-title">Hormat Kami</div>
                        <div class="signature-name">( <?= $isRingkas ? '' : esc($pengirimBarang) ?> )</div>
                    </div>
                    <div class="signature-box">
                        <div class="signature-title">Yang Menerima Barang</div>
                        <div class="signature-name">( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</div>
                    </div>
                </section>
            </main>
        <?php endforeach; ?>
    <?php endif; ?>
    <div class="screen-tools"><button type="button" onclick="window.print()">Cetak</button><?php if ($preview): ?><button type="button" onclick="window.close()">Tutup</button><?php endif; ?></div>
    <?php if (!$preview): ?><script>
            window.addEventListener('load', function() {
                window.print();
            });
        </script><?php endif; ?>
</body>

</html>