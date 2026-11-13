<?php

namespace App\Libraries;

/**
 * Aturan nominal invoice: dibulatkan ke rupiah utuh dengan pecahan 0,50 naik.
 */
final class InvoiceRupiah
{
    public static function normalize($amount): int
    {
        return (int) round((float) ($amount ?? 0), 0, PHP_ROUND_HALF_UP);
    }

    public static function remaining($grandTotal, $paidTotal): int
    {
        return max(self::normalize($grandTotal) - self::normalize($paidTotal), 0);
    }
}
