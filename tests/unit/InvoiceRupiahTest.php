<?php

use App\Libraries\InvoiceRupiah;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class InvoiceRupiahTest extends CIUnitTestCase
{
    public function testRoundHalfUpAndDownBelowHalf(): void
    {
        $this->assertSame(42804, InvoiceRupiah::normalize(42804.30));
        $this->assertSame(42805, InvoiceRupiah::normalize(42804.50));
        $this->assertSame(42804, InvoiceRupiah::normalize(42804.49));
    }

    public function testRemainingBalanceUsesRoundedRupiahAmounts(): void
    {
        $this->assertSame(0, InvoiceRupiah::remaining(42804.30, 42804.00));
        $this->assertSame(17231, InvoiceRupiah::remaining(17230.72, 0));
        $this->assertSame(1, InvoiceRupiah::remaining(42804.50, 42804.00));
    }
}
