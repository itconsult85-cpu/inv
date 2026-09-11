<?php

namespace App\Libraries;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

/**
 * Bikin sheet Excel Invoice Out yang layoutnya niru PERSIS format Excel
 * invoice yang udah biasa dipakai user (kolom lebar, border, fill, font --
 * diukur langsung dari sheet template terbaru mereka, bukan tebakan) --
 * biar hasil downloadnya nggak perlu dirapihin lagi manual.
 *
 * Batasan yang nggak bisa 100% sama persis karena ini generator otomatis
 * (bukan template yang dirapihin tangan per invoice):
 * - Alamat pelanggan yang panjangnya beda-beda di-wrap dalam 1 kotak
 *   (bukan diketik manual jadi beberapa baris terpisah).
 * - Baris PPN/PPh 23/DP cuma muncul kalau memang diaktifkan buat invoice
 *   itu (mengikuti aturan yang sama dengan versi cetak/PDF), bukan selalu
 *   ditampilin kosong kayak template manualnya.
 */
class InvoiceOutExcel
{
    private const FILL_ABU_MUDA = 'FFF2F2F2';
    private const FILL_ABU_TUA = 'FFD9D9D9';
    private const FILL_ABU_TOTAL = 'FFEDEDED';
    private const FMT_ANGKA = '_(* #,##0_);_(* \(#,##0\);_(* "-"_);_(@_)';

    public static function build(array $invoice, array $details, string $terbilang): Spreadsheet
    {
        $bulan = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
        $tanggalIndonesia = static function (?string $tanggal) use ($bulan): string {
            if (!$tanggal) {
                return '-';
            }
            $time = strtotime($tanggal);
            return date('d', $time) . ' ' . $bulan[(int) date('n', $time)] . ' ' . date('Y', $time);
        };
        $persenTampil = static function (float $persen): string {
            return rtrim(rtrim(number_format($persen, 2, ',', '.'), '0'), ',');
        };

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Invoice');

        // Lebar kolom persis kayak template asli (A cuma margin kosong).
        $sheet->getColumnDimension('A')->setWidth(1.109);
        $sheet->getColumnDimension('B')->setWidth(6);
        $sheet->getColumnDimension('C')->setWidth(7.555);
        $sheet->getColumnDimension('D')->setWidth(10.555);
        $sheet->getColumnDimension('E')->setWidth(36.887);
        $sheet->getColumnDimension('F')->setWidth(3.441);
        $sheet->getColumnDimension('G')->setWidth(10.109);
        $sheet->getColumnDimension('H')->setWidth(10);
        $sheet->getColumnDimension('I')->setWidth(15.555);
        $sheet->getColumnDimension('J')->setWidth(18);
        $sheet->getDefaultRowDimension()->setRowHeight(15);

        $row = 1;
        if (($invoice['status'] ?? '') !== 'AKTIF') {
            $sheet->mergeCells("A{$row}:J{$row}");
            $sheet->setCellValue("A{$row}", 'DIBATALKAN');
            $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14)->getColor()->setARGB('FFCC0000');
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row += 2;
        }

        // Header: logo (kolom B) + nama & alamat perusahaan (kolom J saja,
        // rata kanan -- teksnya numpang overflow ke kiri karena kolom
        // tetangganya kosong, sama kayak template aslinya).
        $headerTop = $row;
        $companyLines = [
            'PT Trisentosa Raya Esolusi',
            'Gedung Masindo Lantai III',
            'Jl. Mampang Prapatan Raya no. 73A',
            'Jakarta Selatan',
            'Telp : (021) 798-9670 / 2215-7445',
            'Email : cs@trisentosaraya.co.id',
        ];
        foreach ($companyLines as $i => $line) {
            $sheet->setCellValue("J{$row}", $line);
            $sheet->getStyle("J{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);
            if ($i === 0) {
                $sheet->getStyle("J{$row}")->getFont()->setBold(true);
            }
            $row++;
        }
        $sheet->getRowDimension($headerTop + 1)->setRowHeight(15.75);

        $logoPath = FCPATH . 'image/logo-pt-tre.png';
        if (is_file($logoPath)) {
            // Ukuran & posisi diukur langsung dari file Excel invoice yang
            // dipakai user (bukan tebakan): lebar 1781175 EMU x tinggi
            // 933450 EMU, anchor kolom B baris 1, offset 174349 x 77857
            // EMU. 1 px = 9525 EMU.
            $drawing = new Drawing();
            $drawing->setPath($logoPath);
            $drawing->setResizeProportional(false);
            $drawing->setWidth(187);
            $drawing->setHeight(98);
            $drawing->setCoordinates('B' . $headerTop);
            $drawing->setOffsetX(18);
            $drawing->setOffsetY(8);
            $drawing->setWorksheet($sheet);
        }

        // Garis pemisah nempel langsung di baris alamat terakhir (row-1,
        // BUKAN $row -- $row di sini udah geser ke baris kosong berikutnya).
        $lastCompanyRow = $row - 1;
        $sheet->getStyle("B{$lastCompanyRow}:J{$lastCompanyRow}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getRowDimension($row)->setRowHeight(4.5);
        $row++;

        // Judul INVOICE (kotak kanan atas, G:J, 2 baris).
        $judulRow = $row;
        $sheet->mergeCells("G{$judulRow}:J" . ($judulRow + 1));
        $sheet->setCellValue("G{$judulRow}", 'INVOICE');
        $sheet->getStyle("G{$judulRow}")->getFont()->setBold(true)->setSize(16)->setName('Arial Black');
        $sheet->getStyle("G{$judulRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("G{$judulRow}:J" . ($judulRow + 1))->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::FILL_ABU_MUDA);
        $sheet->getStyle("G{$judulRow}:J" . ($judulRow + 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getRowDimension($judulRow)->setRowHeight(16.2);
        $row += 2;

        // Label "Delivered To :"
        $sheet->mergeCells("C{$row}:D{$row}");
        $sheet->setCellValue("C{$row}", 'Delivered To :');
        $sheet->getStyle("C{$row}")->getFont()->setSize(12)->setBold(true)->setItalic(true);
        $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("C{$row}:D{$row}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);
        $row++;

        // Kotak kiri (Delivered To, kolom B:E) & kanan (Inv/PO meta, G:J).
        $metaStart = $row;

        // Baris 1: Nama pelanggan | Inv. No.
        // (Label/isi kotak kiri-kanan defaultnya rata kiri/general persis
        // template asli -- cuma "Delivered To :", judul INVOICE & header
        // tabel yang beneran center horizontal.)
        $sheet->setCellValue("C{$row}", $invoice['customer_name']);
        foreach (['B', 'C', 'D', 'E'] as $col) {
            $sheet->getStyle("{$col}{$row}")->getFont()->setBold(true);
            $sheet->getStyle("{$col}{$row}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        }
        $sheet->setCellValue("G{$row}", 'Inv. No.');
        $sheet->getStyle("G{$row}")->getFont()->setBold(true);
        $sheet->getStyle("G{$row}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->mergeCells("H{$row}:J{$row}");
        $sheet->setCellValue("H{$row}", ': ' . $invoice['invoice_no']);
        $sheet->getStyle("H{$row}")->getFont()->setBold(true);
        $sheet->getStyle("H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
        $row++;

        // Baris 2-3: Alamat (wrap, 2 baris) | Inv. Date (baris pertama aja,
        // baris ke-2 di kanan kosong -- sejajar sama baris ke-2 alamat).
        $alamatRow = $row;
        $sheet->mergeCells("C{$alamatRow}:E" . ($alamatRow + 1));
        $sheet->setCellValue("C{$alamatRow}", $invoice['customer_address'] ?: '-');
        $sheet->getStyle("C{$alamatRow}")->getFont()->setSize(10);
        $sheet->getStyle("C{$alamatRow}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);
        $sheet->setCellValue("G{$row}", 'Inv. Date');
        $sheet->getStyle("G{$row}")->getFont()->setBold(true);
        $sheet->getStyle("G{$row}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->mergeCells("H{$row}:J{$row}");
        $sheet->setCellValue("H{$row}", ': ' . $tanggalIndonesia($invoice['invoice_date']));
        $sheet->getStyle("H{$row}")->getFont()->setBold(true);
        $sheet->getStyle("H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
        $row++;
        $row++; // baris ke-2 alamat (udah kepakai lewat merge C:E 2 baris)

        // Baris: Telp (kiri) | Based on (kanan) -- SEJAJAR, bukan geser ke
        // baris alamat kayak sebelumnya.
        $sheet->setCellValue("C{$row}", 'Telp       :');
        $sheet->setCellValue("D{$row}", $invoice['customer_phone'] ?: '-');
        foreach (['C', 'D'] as $col) {
            $sheet->getStyle("{$col}{$row}")->getFont()->setSize(10);
            $sheet->getStyle("{$col}{$row}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        }
        $sheet->setCellValue("G{$row}", 'Based on');
        $sheet->getStyle("G{$row}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("G{$row}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $row++;

        // Baris: Fax (kiri) | P.O No. (kanan)
        $sheet->setCellValue("C{$row}", 'Fax         :');
        $sheet->setCellValue("D{$row}", $invoice['customer_fax'] ?: '-');
        foreach (['C', 'D'] as $col) {
            $sheet->getStyle("{$col}{$row}")->getFont()->setSize(10);
            $sheet->getStyle("{$col}{$row}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        }
        $sheet->setCellValue("G{$row}", 'P.O No.');
        $sheet->getStyle("G{$row}")->getFont()->setBold(true);
        $sheet->getStyle("G{$row}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->mergeCells("H{$row}:J{$row}");
        $sheet->setCellValue("H{$row}", ': ' . $invoice['po_no']);
        $sheet->getStyle("H{$row}")->getFont()->setBold(true);
        $sheet->getStyle("H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
        $row++;

        // Baris: To (kiri) | P.O Date (kanan) -- nutup kotak kiri & kanan
        // bareng, border bawah medium.
        $sheet->setCellValue("C{$row}", 'To          :');
        $sheet->setCellValue("D{$row}", $invoice['customer_to'] ?: 'Bag. Keuangan');
        foreach (['C', 'D'] as $col) {
            $sheet->getStyle("{$col}{$row}")->getFont()->setSize(10);
            $sheet->getStyle("{$col}{$row}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        }
        $sheet->setCellValue("G{$row}", 'P.O Date');
        $sheet->getStyle("G{$row}")->getFont()->setBold(true);
        $sheet->getStyle("G{$row}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->mergeCells("H{$row}:J{$row}");
        $sheet->setCellValue("H{$row}", ': ' . $tanggalIndonesia($invoice['po_date']));
        $sheet->getStyle("H{$row}")->getFont()->setBold(true);
        $sheet->getStyle("H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
        $metaEnd = $row;
        $row++;

        // Cuma bingkai luar kotak (bukan grid tiap sel) -- getAllBorders()
        // di sini bakal nimpa garis "Based on" jadi ikutan tebal & bikin
        // kotak keliatan kayak papan catur, nggak kayak template aslinya.
        $sheet->getStyle("B{$metaStart}:E{$metaEnd}")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyle("G{$metaStart}:J{$metaEnd}")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_MEDIUM);

        $row++;

        // Tabel item -- "PART NO AND DESCRIPTION" digabung 1 kolom (C:F),
        // isinya hanya nama produk, disamakan dengan versi cetak/PDF.
        $tableHeaderRow = $row;
        $sheet->setCellValue("B{$tableHeaderRow}", 'NO');
        $sheet->mergeCells("C{$tableHeaderRow}:F{$tableHeaderRow}");
        $sheet->setCellValue("C{$tableHeaderRow}", 'PART NO AND DESCRIPTION');
        $sheet->setCellValue("G{$tableHeaderRow}", 'QTY');
        $sheet->setCellValue("H{$tableHeaderRow}", 'UoM');
        $sheet->setCellValue("I{$tableHeaderRow}", 'PRICE (Rp)');
        $sheet->setCellValue("J{$tableHeaderRow}", 'AMOUNT (Rp)');
        $sheet->getStyle("B{$tableHeaderRow}:J{$tableHeaderRow}")->getFont()->setBold(true);
        $sheet->getStyle("B{$tableHeaderRow}:J{$tableHeaderRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        $sheet->getStyle("B{$tableHeaderRow}:J{$tableHeaderRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::FILL_ABU_MUDA);
        $sheet->getStyle("B{$tableHeaderRow}:J{$tableHeaderRow}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyle("B{$tableHeaderRow}:J{$tableHeaderRow}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        foreach (['B', 'C', 'G', 'H', 'I', 'J'] as $col) {
            $sheet->getStyle("{$col}{$tableHeaderRow}")->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle("{$col}{$tableHeaderRow}")->getBorders()->getRight()->setBorderStyle(Border::BORDER_THIN);
        }
        $sheet->getStyle("B{$tableHeaderRow}")->getBorders()->getLeft()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyle("J{$tableHeaderRow}")->getBorders()->getRight()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getRowDimension($tableHeaderRow)->setRowHeight(15.75);
        $row++;

        $itemStartRow = $row;
        foreach ($details as $i => $detail) {
            $sheet->setCellValue("B{$row}", $i + 1);
            $sheet->mergeCells("C{$row}:F{$row}");
            $sheet->setCellValue("C{$row}", $detail['product_name']);
            $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->setCellValue("G{$row}", (float) $detail['qty']);
            $sheet->setCellValue("H{$row}", $detail['unit']);
            $sheet->setCellValue("I{$row}", (float) $detail['unit_price']);
            $sheet->setCellValue("J{$row}", (float) $detail['amount']);
            $row++;
        }
        $itemEndRow = max($row - 1, $itemStartRow);

        $sheet->getStyle("B{$itemStartRow}:B{$itemEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        $sheet->getStyle("G{$itemStartRow}:G{$itemEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("G{$itemStartRow}:G{$itemEndRow}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("H{$itemStartRow}:H{$itemEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("I{$itemStartRow}:I{$itemEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("I{$itemStartRow}:I{$itemEndRow}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("J{$itemStartRow}:J{$itemEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("J{$itemStartRow}:J{$itemEndRow}")->getNumberFormat()->setFormatCode(self::FMT_ANGKA);

        // Isi tabel (bukan headernya) sengaja polos tanpa grid, kayak versi
        // cetak/PDF -- cuma bingkai luar (nyambung dari bingkai header di
        // atasnya), bukan garis di tiap sel/baris. 1 baris kosong di bawah
        // item terakhir ikut masuk bingkai ini juga (bukan gap di luar
        // tabel).
        $blankRow = $itemEndRow + 1;
        $sheet->getStyle("B{$tableHeaderRow}:J{$blankRow}")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_MEDIUM);

        $row = $blankRow + 1;

        // Totals (label kolom I, nilai kolom J).
        $totalsRows = [['TOTAL', (float) $invoice['subtotal']]];
        if ((int) ($invoice['ppn_enabled'] ?? 1) === 1) {
            $totalsRows[] = ['PPN ' . $persenTampil((float) $invoice['ppn_percent']) . '%', (float) $invoice['ppn']];
        }
        if ((int) ($invoice['pph_enabled'] ?? 1) === 1) {
            $totalsRows[] = ['PPH 23 (' . $persenTampil((float) $invoice['pph_percent']) . '%)', (float) $invoice['pph23']];
        }
        if ((int) ($invoice['dp_enabled'] ?? 0) === 1) {
            $totalsRows[] = ['DP ' . $persenTampil((float) $invoice['dp_percent']) . '%', (float) $invoice['dp_amount']];
        }
        $totalsRows[] = ['GRAND TOTAL', (float) $invoice['grand_total']];

        $totalsStart = $row;
        $totalsLastIndex = count($totalsRows) - 1;
        foreach ($totalsRows as $idx => $t) {
            $isFirst = $idx === 0;
            $isLast = $idx === $totalsLastIndex;

            $sheet->setCellValue("I{$row}", $t[0]);
            $sheet->getStyle("I{$row}")->getFont()->setBold(true);
            $sheet->getStyle("I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue("J{$row}", $t[1]);
            $sheet->getStyle("J{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("J{$row}")->getNumberFormat()->setFormatCode(self::FMT_ANGKA);

            $sheet->getStyle("I{$row}:J{$row}")->getBorders()->getTop()->setBorderStyle($isFirst ? Border::BORDER_MEDIUM : Border::BORDER_THIN);
            $sheet->getStyle("I{$row}:J{$row}")->getBorders()->getBottom()->setBorderStyle($isLast ? Border::BORDER_MEDIUM : Border::BORDER_THIN);
            $sheet->getStyle("I{$row}")->getBorders()->getLeft()->setBorderStyle(Border::BORDER_MEDIUM);
            $sheet->getStyle("J{$row}")->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle("J{$row}")->getBorders()->getRight()->setBorderStyle(Border::BORDER_MEDIUM);

            if ($isFirst || $isLast) {
                $sheet->getStyle("J{$row}")->getFont()->setBold(true);
            }
            if ($isFirst) {
                $sheet->getStyle("I{$row}:J{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::FILL_ABU_TOTAL);
            }
            if ($isLast) {
                $sheet->getStyle("I{$row}:J{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::FILL_ABU_MUDA);
            }

            $row++;
        }
        $totalsEnd = $row - 1;

        // Terbilang -- sejajar sama 2 baris TERAKHIR kotak totals (biasanya
        // PPh 23 + GRAND TOTAL), persis posisinya di template aslinya.
        $terbilangStart = max($totalsStart, $totalsEnd - 1);
        $terbilangEnd = $totalsEnd;
        $sheet->mergeCells("B{$terbilangStart}:C{$terbilangEnd}");
        $sheet->setCellValue("B{$terbilangStart}", 'TERBILANG');
        $sheet->getStyle("B{$terbilangStart}")->getFont()->setSize(12);
        $sheet->getStyle("B{$terbilangStart}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("B{$terbilangStart}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::FILL_ABU_TUA);
        $sheet->mergeCells("D{$terbilangStart}:G{$terbilangEnd}");
        $sheet->setCellValue("D{$terbilangStart}", $terbilang);
        $sheet->getStyle("D{$terbilangStart}")->getFont()->setSize(12);
        $sheet->getStyle("D{$terbilangStart}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        $sheet->getStyle("D{$terbilangStart}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::FILL_ABU_MUDA);
        $sheet->getStyle("B{$terbilangStart}:G{$terbilangEnd}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $row = $totalsEnd + 2;

        // "Hormat Kami" duduk sendiri 1 baris di atas kotak info bank.
        $sheet->mergeCells("I{$row}:J{$row}");
        $sheet->setCellValue("I{$row}", 'Hormat Kami');
        $sheet->getStyle("I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $row++;

        // Info bank (kiri, B:E) & baris kosong buat padding atas kotak.
        $footerStart = $row;
        $sheet->mergeCells("B{$row}:E{$row}");
        $sheet->setCellValue("B{$row}", 'Pembayaran Harap Di Transfer Ke Data Berikut :');
        $sheet->getStyle("B{$row}")->getFont()->setBold(true);
        $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("B{$row}:E{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::FILL_ABU_TUA);
        $row++;
        $row++; // baris kosong (padding) sebelum daftar rekening mulai

        $bankLines = [
            ['Pemilik Rekening', $invoice['bank_owner']],
            ['Nama Bank', $invoice['bank_name']],
            ['Nomor Rekening', $invoice['bank_account']],
            ['NPWP', $invoice['bank_npwp']],
        ];
        foreach ($bankLines as [$label, $value]) {
            $sheet->setCellValue("C{$row}", $label);
            $sheet->setCellValue("E{$row}", ': ' . $value);
            $sheet->getStyle("E{$row}")->getFont()->setBold(true);
            $row++;
        }
        $bankRowEnd = $row - 1;
        // Bingkai luar kotak aja (bukan grid tiap sel) -- baris judulnya
        // (merged) tetap dapat bingkai penuh sendiri sebagai "tutup" kotak.
        $sheet->getStyle("B{$footerStart}:E{$bankRowEnd}")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("B{$footerStart}:E{$footerStart}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $signatureNameRow = $footerStart + 6;
        $sheet->mergeCells("I{$signatureNameRow}:J{$signatureNameRow}");
        $sheet->setCellValue("I{$signatureNameRow}", $invoice['signer_name']);
        $sheet->getStyle("I{$signatureNameRow}")->getFont()->setBold(true)->setUnderline(true);
        $sheet->getStyle("I{$signatureNameRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        $signaturePosRow = $signatureNameRow + 1;
        $sheet->mergeCells("I{$signaturePosRow}:J{$signaturePosRow}");
        $sheet->setCellValue("I{$signaturePosRow}", $invoice['signer_position']);
        $sheet->getStyle("I{$signaturePosRow}")->getFont()->setItalic(true)->setSize(10);
        $sheet->getStyle("I{$signaturePosRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->setSelectedCell('A1');
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
        // Tanpa ini, Excel defaultnya "Automatic" (nggak di-scale sama
        // sekali) -- lebar kolomnya lebih dari 1 halaman portrait, jadi pas
        // print/print-preview kepotong di sebelah kanan.
        $sheet->getPageSetup()->setFitToPage(true);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(1);

        return $spreadsheet;
    }
}
