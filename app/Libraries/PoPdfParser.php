<?php

namespace App\Libraries;

use RuntimeException;

class PoPdfParser
{
    public function parse(string $pdfPath): array
    {
        $text = $this->extractText($pdfPath);
        $lines = $this->normaliseLines($text);

        return [
            'nopo' => $this->findPoNumber($lines),
            'tglpo' => $this->findPoDate($lines),
            'pelanggan_hint' => $this->findCustomerHint($lines),
            'items' => $this->findItems($lines),
            'raw_text' => $text,
        ];
    }

    private function extractText(string $pdfPath): string
    {
        if (!class_exists('\Smalot\PdfParser\Parser') && defined('ROOTPATH')) {
            $composerAutoload = rtrim(ROOTPATH, '\\/ ') . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
            if (is_file($composerAutoload)) {
                require_once $composerAutoload;
            }
        }

        $smalotAvailable = class_exists('\Smalot\PdfParser\Parser');

        if ($smalotAvailable) {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($pdfPath);
            $text = trim($pdf->getText());

            if ($text !== '') {
                return $text;
            }
        }

        if (!function_exists('shell_exec')) {
            if ($smalotAvailable) {
                throw new RuntimeException('PDF tidak mengandung teks yang bisa dibaca oleh smalot/pdfparser. Coba input manual, gunakan file PDF asli yang text-based, atau aktifkan fallback pdftotext di server.');
            }

            throw new RuntimeException('Server belum mendukung pembacaan PDF otomatis. Aktifkan shell_exec atau pasang library PDF parser.');
        }

        $uploadPath = defined('WRITEPATH') ? WRITEPATH . 'uploads' : sys_get_temp_dir();
        if (!is_dir($uploadPath)) {
            @mkdir($uploadPath, 0775, true);
        }

        $target = $uploadPath . DIRECTORY_SEPARATOR . 'po_import_' . uniqid('', true) . '.txt';
        $command = 'pdftotext -layout ' . escapeshellarg($pdfPath) . ' ' . escapeshellarg($target);
        $command .= PHP_OS_FAMILY === 'Windows' ? ' 2>NUL' : ' 2>/dev/null';
        @shell_exec($command);

        if (is_file($target)) {
            $text = trim((string) file_get_contents($target));
            @unlink($target);

            if ($text !== '') {
                return $text;
            }
        }

        if ($smalotAvailable) {
            throw new RuntimeException('PDF tidak mengandung teks yang bisa dibaca oleh smalot/pdfparser. Coba input manual, gunakan file PDF asli yang text-based, atau aktifkan fallback pdftotext di server.');
        }

        throw new RuntimeException('PDF belum bisa dibaca otomatis. Pastikan Poppler/pdftotext tersedia di server, atau install package smalot/pdfparser.');
    }

    private function normaliseLines(string $text): array
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $lines = array_map(
            static fn ($line) => trim(preg_replace('/\s+/', ' ', $line)),
            explode("\n", $text)
        );

        return array_values(array_filter($lines, static fn ($line) => $line !== ''));
    }

    private function findPoNumber(array $lines): string
    {
        $patterns = [
            '/^([A-Z0-9][A-Z0-9\-]+)\s*Order\s*No\.?/i',
            '/\bP\.?\s*O\.?(?![A-Z])\s*(?:No|Number)?\s*[:：]?\s*([A-Z0-9][A-Z0-9\/\.\-]+)/i',
            '/\bOrder\s*No\.?\s*[:：]?\s*([A-Z0-9][A-Z0-9\/\.\-]+)/i',
            '/^\s*NO\s*[:：]\s*([A-Z0-9][A-Z0-9\/\.\-]+)/i',
            '/\b(BPL[A-Z0-9]+)/i',
            '/\b(40[0-9]{8})\b/',
        ];

        foreach ($lines as $line) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $line, $matches)) {
                    $value = trim($matches[1]);
                    if (!in_array(strtoupper($value), ['NO', 'DATE'], true)) {
                        return $value;
                    }
                }
            }
        }

        return '';
    }

    private function findPoDate(array $lines): string
    {
        foreach (array_reverse($lines) as $line) {
            if (preg_match('/\b[A-Z][A-Za-z\.\s-]{2,},\s*([0-9]{1,2}\s+[A-Za-z]{3,9}\s+[0-9]{2,4})\b/i', $line, $matches)) {
                $date = $this->parseDate($matches[1]);
                if ($date !== '') {
                    return $date;
                }
            }
        }

        foreach ($lines as $line) {
            if (preg_match('/(?:P\.?\s*O\.?\s*)?Date\s*[:：]\s*([0-9]{1,2}[\-\/\s][A-Za-z]{3,9}[\-\/\s][0-9]{2,4}|[0-9]{1,2}[\-\/][0-9]{1,2}[\-\/][0-9]{2,4})/i', $line, $matches)) {
                $date = $this->parseDate($matches[1]);
                if ($date !== '') {
                    return $date;
                }
            }
        }

        foreach ($lines as $line) {
            if (stripos($line, 'IDR') !== false || stripos($line, 'Rp') !== false) {
                continue;
            }

            if (preg_match('/\b([0-9]{1,2}[\-\/\s][A-Za-z]{3,9}[\-\/\s][0-9]{2,4}|[0-9]{1,2}[\-\/][0-9]{1,2}[\-\/][0-9]{2,4})\b/i', $line, $matches)) {
                $date = $this->parseDate($matches[1]);
                if ($date !== '') {
                    return $date;
                }
            }
        }

        return date('Y-m-d');
    }

    private function parseDate(string $value): string
    {
        $value = trim(str_replace(',', ' ', $value));
        $months = [
            'jan' => '01', 'january' => '01', 'januari' => '01',
            'feb' => '02', 'february' => '02', 'februari' => '02',
            'mar' => '03', 'march' => '03', 'maret' => '03',
            'apr' => '04', 'april' => '04',
            'may' => '05', 'mei' => '05',
            'jun' => '06', 'june' => '06', 'juni' => '06',
            'jul' => '07', 'july' => '07', 'juli' => '07',
            'aug' => '08', 'august' => '08', 'agustus' => '08',
            'sep' => '09', 'sept' => '09', 'september' => '09',
            'oct' => '10', 'october' => '10', 'okt' => '10', 'oktober' => '10',
            'nov' => '11', 'november' => '11',
            'dec' => '12', 'december' => '12', 'des' => '12', 'desember' => '12',
        ];

        if (preg_match('/^([0-9]{1,2})[\-\/\s]+([A-Za-z]{3,9})[\-\/\s]+([0-9]{2,4})$/i', $value, $matches)) {
            $monthKey = strtolower($matches[2]);
            if (!isset($months[$monthKey])) {
                return '';
            }

            $year = strlen($matches[3]) === 2 ? '20' . $matches[3] : $matches[3];
            return sprintf('%04d-%02d-%02d', (int) $year, (int) $months[$monthKey], (int) $matches[1]);
        }

        foreach (['d-m-Y', 'd/m/Y', 'm-d-Y', 'm/d/Y', 'Y-m-d', 'Y/m/d'] as $format) {
            $date = \DateTime::createFromFormat($format, $value);
            if ($date instanceof \DateTime) {
                return $date->format('Y-m-d');
            }
        }

        return '';
    }

    private function findCustomerHint(array $lines): string
    {
        foreach ($lines as $line) {
            if (stripos($line, 'PT ') !== false && stripos($line, 'TRISENTOSA') === false) {
                if (preg_match('/(PT\.?\s+[A-Z0-9][A-Z0-9\s\.\-&()]+)/i', $line, $matches)) {
                    return trim($matches[1]);
                }
            }
        }

        return '';
    }

    private function findItems(array $lines): array
    {
        $items = [];

        foreach ($lines as $line) {
            if (!preg_match('/^\s*(\d+)\s+(.+?)\s+([0-9][0-9\.,]*)\s+(PCS|PC|Pcs|KG|Kg|SET|EA)\b(?:\s+([0-9][0-9\.,]*))?/i', $line, $matches)) {
                continue;
            }

            $description = trim($matches[2]);
            if (preg_match('/^(TOTAL|SUBTOTAL|GRAND|PPN|PPH|Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\b/i', $description)) {
                continue;
            }

            $unitPrice = isset($matches[5]) ? $this->toNumber($matches[5]) : 0;
            $items[] = [
                'description' => $description,
                'qty' => $this->toNumber($matches[3]),
                'uom' => strtoupper($matches[4]) === 'PC' ? 'Pcs' : strtoupper($matches[4]),
                'unit_price' => $unitPrice,
                'product_code_hint' => $this->findProductCodeInText($description),
            ];
        }

        foreach ($this->findMultiLineItems($lines) as $item) {
            $items[] = $item;
        }

        foreach ($this->findSummitmasItems($lines) as $item) {
            $items[] = $item;
        }

        return $this->uniqueItems($items);
    }

    private function findMultiLineItems(array $lines): array
    {
        $items = [];
        $lineCount = count($lines);

        for ($i = 0; $i < $lineCount; $i++) {
            $line = $lines[$i];

            if (!preg_match('/^\s*(\d+)\s+([A-Z0-9][A-Z0-9\-\/\.]+)\s+(.+?)\s*-?\s*$/i', $line, $matches)) {
                continue;
            }

            if (!preg_match('/[0-9\-]/', $matches[2])) {
                continue;
            }

            $description = trim($matches[2] . ' ' . $matches[3]);

            if (preg_match('/^(NO|DATE|TOTAL|SUBTOTAL|GRAND|PPN|PPH|SURC|DISC)\b/i', $description)) {
                continue;
            }

            $qty = 0;
            $uom = 'Pcs';
            $unitPrice = 0;

            for ($j = $i + 1; $j < min($lineCount, $i + 8); $j++) {
                $nextLine = trim($lines[$j]);

                if ($j > $i + 1 && preg_match('/^\s*\d+\s+[A-Z0-9][A-Z0-9\-\/\.]+\s+.+/i', $nextLine)) {
                    break;
                }

                if ($qty <= 0 && preg_match('/^([0-9][0-9\.,]*)\s*$/', $nextLine, $qtyMatch)) {
                    $qty = $this->toNumber($qtyMatch[1]);
                    continue;
                }

                if (preg_match('/^(PCS|PC|Pcs|KG|Kg|SET|EA)$/i', $nextLine, $uomMatch)) {
                    $uom = strtoupper($uomMatch[1]) === 'PC' ? 'Pcs' : strtoupper($uomMatch[1]);
                    continue;
                }

                if ($unitPrice <= 0 && preg_match('/(?:IDR|Rp)\s*([0-9][0-9\.,]*)/i', $nextLine, $priceMatch)) {
                    $unitPrice = $this->toNumber($priceMatch[1]);
                }
            }

            if ($qty <= 0) {
                continue;
            }

            $items[] = [
                'description' => $description,
                'qty' => $qty,
                'uom' => $uom,
                'unit_price' => $unitPrice,
                'product_code_hint' => $this->findProductCodeInText($description),
            ];
        }

        return $items;
    }

    private function findSummitmasItems(array $lines): array
    {
        $items = [];
        $usedConfirmationLines = [];
        $lineCount = count($lines);

        for ($i = 0; $i < $lineCount; $i++) {
            $line = $lines[$i];

            if (preg_match('/^([0-9,]+)\s*([A-Za-z].+?)\s*(\d+)\s+([0-9][0-9,\.]*)\s+.*Rp.*pcs/i', $line, $matches)) {
                $split = $this->splitAmountQty($matches[1], $this->toNumber($matches[4]));
                if ($split === null) {
                    continue;
                }

                $items[] = [
                    'description' => trim($matches[2]),
                    'qty' => $split['qty'],
                    'uom' => 'Pcs',
                    'unit_price' => $this->toNumber($matches[4]),
                    'product_code_hint' => $this->findProductCodeInText($matches[2]),
                ];
                $usedConfirmationLines[$i] = true;
                continue;
            }

            if (!preg_match('/^(\d+)\s+([0-9][0-9,\.]*)\s+.*Rp.*pcs/i', $line, $matches)) {
                continue;
            }

            if (isset($usedConfirmationLines[$i])) {
                continue;
            }

            $unitPrice = $this->toNumber($matches[2]);
            $amountQtyIndex = null;

            for ($j = $i - 1; $j >= max(0, $i - 5); $j--) {
                if (preg_match('/^[0-9,]+$/', $lines[$j])) {
                    $amountQtyIndex = $j;
                    break;
                }
            }

            if ($amountQtyIndex === null) {
                continue;
            }

            $split = $this->splitAmountQty($lines[$amountQtyIndex], $unitPrice);
            if ($split === null) {
                continue;
            }

            $descriptionParts = [];
            for ($j = $amountQtyIndex + 1; $j < $i; $j++) {
                $part = trim($lines[$j]);
                if ($part === '' || preg_match('/^(dan|and)$/i', $part)) {
                    continue;
                }
                $descriptionParts[] = $part;
            }

            $description = trim(implode(' ', $descriptionParts));
            if ($description === '') {
                continue;
            }

            $items[] = [
                'description' => $description,
                'qty' => $split['qty'],
                'uom' => 'Pcs',
                'unit_price' => $unitPrice,
                'product_code_hint' => $this->findProductCodeInText($description),
            ];
        }

        return $items;
    }

    private function splitAmountQty(string $amountQty, float $unitPrice): ?array
    {
        $amountQty = trim($amountQty);
        $length = strlen($amountQty);
        $best = null;
        $bestDiff = null;

        for ($i = 1; $i < $length; $i++) {
            $amount = $this->toNumber(substr($amountQty, 0, $i));
            $qty = $this->toNumber(substr($amountQty, $i));

            if ($amount <= 0 || $qty <= 0) {
                continue;
            }

            if ($qty < 1 || abs($qty - round($qty)) > 0.000001) {
                continue;
            }

            $calculatedPrice = $amount / $qty;
            $diff = abs($calculatedPrice - $unitPrice);

            if (
                $bestDiff === null
                || $diff < $bestDiff
                || (abs($diff - $bestDiff) < 0.000001 && $best !== null && $qty > $best['qty'])
            ) {
                $bestDiff = $diff;
                $best = [
                    'amount' => $amount,
                    'qty' => $qty,
                ];
            }
        }

        if ($best === null || $bestDiff === null || $bestDiff > max(1, $unitPrice * 0.02)) {
            return null;
        }

        return $best;
    }

    private function uniqueItems(array $items): array
    {
        $unique = [];
        $seen = [];

        foreach ($items as $item) {
            $key = md5(strtoupper((string) $item['description']) . '|' . (float) $item['qty'] . '|' . strtoupper((string) $item['uom']));
            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $unique[] = $item;
        }

        return $unique;
    }

    private function findProductCodeInText(string $text): string
    {
        if (preg_match('/\b([A-Z]{1,5}[A-Z0-9]*[-\/][A-Z0-9][A-Z0-9\-\/\.]*)\b/', $text, $matches)) {
            return $matches[1];
        }

        if (preg_match('/\b([A-Z0-9]{4,})\b/', $text, $matches)) {
            return $matches[1];
        }

        return '';
    }

    private function toNumber(string $value): float
    {
        $value = trim($value);

        if (strpos($value, ',') !== false && strpos($value, '.') !== false) {
            if (strrpos($value, ',') < strrpos($value, '.')) {
                $value = str_replace(',', '', $value);
            } else {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            }
        } elseif (preg_match('/^\d{1,3}(,\d{3})+$/', $value)) {
            $value = str_replace(',', '', $value);
        } elseif (substr_count($value, '.') > 1) {
            $value = str_replace('.', '', $value);
        } elseif (substr_count($value, ',') > 1) {
            $value = str_replace(',', '', $value);
        } elseif (preg_match('/^\d+\.\d{3}$/', $value)) {
            $value = str_replace('.', '', $value);
        } elseif (strpos($value, ',') !== false) {
            $value = str_replace(',', '.', $value);
        }

        return (float) preg_replace('/[^0-9.\-]/', '', $value);
    }
}
