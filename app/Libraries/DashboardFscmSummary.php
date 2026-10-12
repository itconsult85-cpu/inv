<?php

namespace App\Libraries;

/**
 * Ringkasan FSCM untuk dashboard.
 *
 * Semua query di kelas ini bersifat read-only. Pengelompokan surat jalan
 * memakai source_no invoice_out_detail agar partial billing tetap terlihat
 * sebagai nilai sudah ditagihkan dan sisa yang belum ditagihkan.
 */
class DashboardFscmSummary
{
    public static function build($db, array $period): array
    {
        $summary = [
            'surat_jalan_belum' => 0,
            'nilai_belum' => 0.0,
            'surat_jalan_sudah' => 0,
            'nilai_sudah' => 0.0,
            'surat_jalan_total' => 0,
            'nilai_total' => 0.0,
            'due_dates' => [],
        ];

        if (!$db->tableExists('detail_barangkeluar') || !$db->tableExists('barangkeluar')
            || !$db->tableExists('invoice_out') || !$db->tableExists('invoice_out_detail')) {
            return $summary;
        }

        $shipmentRows = $db->query(
            "SELECT
                dk.detfaktur AS source_no,
                bk.tglfaktur,
                MAX(p.pelnama) AS customer_name,
                dk.detbrgkode AS product_code,
                SUM(dk.detjml) AS qty_shipped,
                COALESCE(MAX(b.harga), MAX(dp.unit_price), 0) AS unit_price
             FROM detail_barangkeluar dk
             LEFT JOIN barangkeluar bk ON bk.faktur = dk.detfaktur
             LEFT JOIN pelanggan p ON p.pelid = dk.detidpel
             LEFT JOIN barang b ON b.brgkode = dk.detbrgkode
             LEFT JOIN (
                 SELECT detnopo, detkodebrg,
                        MAX(detharga / NULLIF(detqty, 0)) AS unit_price
                 FROM detail_po
                 GROUP BY detnopo, detkodebrg
             ) dp ON dp.detnopo = dk.detpo AND dp.detkodebrg = dk.detbrgkode
             WHERE COALESCE(bk.tglfaktur, '1970-01-01') >= ?
               AND COALESCE(bk.tglfaktur, '2099-12-31') <= ?
               AND (
                    UPPER(COALESCE(dk.detfaktur, '')) LIKE '%FSCM%'
                    OR UPPER(COALESCE(p.pelnama, '')) LIKE '%FSCM%'
               )
             GROUP BY dk.detfaktur, bk.tglfaktur, dk.detbrgkode
             ORDER BY bk.tglfaktur DESC, dk.detfaktur ASC",
            [$period['start_date'], $period['end_date']]
        )->getResultArray();

        $invoiceRows = $db->query(
            "SELECT iod.source_no, iod.product_code,
                    SUM(iod.qty) AS qty_invoiced,
                    SUM(COALESCE(iod.amount, 0)) AS amount_invoiced
             FROM invoice_out_detail iod
             INNER JOIN invoice_out io ON io.id = iod.invoice_id
             WHERE io.status = 'AKTIF'
             GROUP BY iod.source_no, iod.product_code"
        )->getResultArray();

        $invoicesByLine = [];
        foreach ($invoiceRows as $row) {
            $key = (string) ($row['source_no'] ?? '') . '|' . (string) ($row['product_code'] ?? '');
            $invoicesByLine[$key] = [
                'qty' => (float) ($row['qty_invoiced'] ?? 0),
                'amount' => (float) ($row['amount_invoiced'] ?? 0),
            ];
        }

        $shipments = [];
        foreach ($shipmentRows as $row) {
            $sourceNo = (string) ($row['source_no'] ?? '');
            $productCode = (string) ($row['product_code'] ?? '');
            $lineInvoice = $invoicesByLine[$sourceNo . '|' . $productCode] ?? ['qty' => 0.0, 'amount' => 0.0];
            $qtyShipped = max((float) ($row['qty_shipped'] ?? 0), 0);
            $qtyInvoiced = min(max((float) $lineInvoice['qty'], 0), $qtyShipped);
            $unitPrice = max((float) ($row['unit_price'] ?? 0), 0);
            $key = $sourceNo;

            if (!isset($shipments[$key])) {
                $shipments[$key] = [
                    'qty_shipped' => 0.0,
                    'qty_invoiced' => 0.0,
                    'value_shipped' => 0.0,
                    'value_invoiced' => 0.0,
                ];
            }
            $shipments[$key]['qty_shipped'] += $qtyShipped;
            $shipments[$key]['qty_invoiced'] += $qtyInvoiced;
            $shipments[$key]['value_shipped'] += $qtyShipped * $unitPrice;
            // Nominal sudah ditagihkan memakai amount invoice aktual, karena
            // harga invoice dapat berbeda dari harga master produk.
            $shipments[$key]['value_invoiced'] += max((float) $lineInvoice['amount'], 0);
        }

        foreach ($shipments as $shipment) {
            $remainingQty = max($shipment['qty_shipped'] - $shipment['qty_invoiced'], 0);
            $remainingValue = max($shipment['value_shipped'] - $shipment['value_invoiced'], 0);
            $hasBilled = $shipment['qty_invoiced'] > 0.0001;
            $hasRemaining = $remainingQty > 0.0001;

            if ($hasBilled) {
                $summary['surat_jalan_sudah']++;
                $summary['nilai_sudah'] += $shipment['value_invoiced'];
            }
            if ($hasRemaining) {
                $summary['surat_jalan_belum']++;
                $summary['nilai_belum'] += $remainingValue;
            }
        }

        $summary['surat_jalan_total'] = count($shipments);
        $summary['nilai_total'] = $summary['nilai_belum'] + $summary['nilai_sudah'];

        $invoiceRows = $db->query(
            "SELECT io.invoice_date, io.grand_total, COALESCE(io.paid_total, 0) AS paid_total
             FROM invoice_out io
             WHERE io.status = 'AKTIF'
               AND io.grand_total > COALESCE(io.paid_total, 0)
               AND (
                    UPPER(COALESCE(io.customer_name, '')) LIKE '%FSCM%'
                    OR EXISTS (
                        SELECT 1 FROM invoice_out_detail iod
                        WHERE iod.invoice_id = io.id
                          AND UPPER(COALESCE(iod.source_no, '')) LIKE '%FSCM%'
                    )
               )
             ORDER BY io.invoice_date ASC, io.id ASC"
        )->getResultArray();

        foreach ($invoiceRows as $invoice) {
            $invoiceDate = (string) ($invoice['invoice_date'] ?? '');
            $timestamp = strtotime($invoiceDate);
            if (!$timestamp) {
                continue;
            }
            // Kebijakan FSCM: invoice tanggal 1-15 jatuh tempo tanggal 15
            // bulan berikutnya; invoice tanggal 16-akhir bulan jatuh tempo
            // tanggal 25 bulan berikutnya.
            $dueDay = (int) date('j', $timestamp) <= 15 ? 15 : 25;
            $nextMonth = (new \DateTimeImmutable($invoiceDate))->modify('first day of next month');
            $dueDate = $nextMonth->format('Y-m-') . str_pad((string) $dueDay, 2, '0', STR_PAD_LEFT);
            if (!isset($summary['due_dates'][$dueDate])) {
                $summary['due_dates'][$dueDate] = [
                    'due_date' => $dueDate,
                    'invoice_count' => 0,
                    'amount' => 0.0,
                ];
            }
            $summary['due_dates'][$dueDate]['invoice_count']++;
            $summary['due_dates'][$dueDate]['amount'] += max((float) $invoice['grand_total'] - (float) $invoice['paid_total'], 0);
        }

        $summary['due_dates'] = array_values($summary['due_dates']);
        usort($summary['due_dates'], static fn(array $a, array $b): int => strcmp($a['due_date'], $b['due_date']));

        return $summary;
    }
}
