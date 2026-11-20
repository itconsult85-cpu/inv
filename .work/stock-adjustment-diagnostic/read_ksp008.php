<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__, 2);
define('FCPATH', $projectRoot . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', getenv('CI_ENVIRONMENT') ?: 'development');
}
if (!defined('CI_DEBUG')) {
    define('CI_DEBUG', true);
}
require $projectRoot . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Paths.php';
$paths = new Config\Paths();
require $paths->systemDirectory . DIRECTORY_SEPARATOR . 'Boot.php';
CodeIgniter\Boot::bootConsole($paths);

$db = db_connect();
$code = 'KSP008-2013B';
$start = $argv[1] ?? '2026-08-01';
$end = $argv[2] ?? '2026-08-31';

$poRows = $db->query(
    'SELECT detnopo, dettglpo, detqty, detkirim_awal, detkirim, detkurang,
            GREATEST(COALESCE(detqty, 0) - COALESCE(detkirim_awal, 0) - COALESCE(detkirim, 0), 0) AS sisa_hitung
     FROM detail_po
     WHERE detkodebrg = ? AND detidpel NOT IN (1, 2)
     ORDER BY dettglpo, detnopo',
    [$code]
)->getResultArray();

$poSummary = $db->query(
    'SELECT COALESCE(SUM(COALESCE(detqty, 0)), 0) AS total_po_kumulatif,
            COALESCE(SUM(GREATEST(COALESCE(detqty, 0) - COALESCE(detkirim_awal, 0) - COALESCE(detkirim, 0), 0)), 0) AS total_sisa_kumulatif
     FROM detail_po
     WHERE detkodebrg = ? AND detidpel NOT IN (1, 2) AND dettglpo <= ?',
    [$code, $end]
)->getRowArray();

$periodPo = $db->query(
    'SELECT COALESCE(SUM(COALESCE(detqty, 0)), 0) AS po_qty_periode,
            COALESCE(SUM(GREATEST(COALESCE(detqty, 0) - COALESCE(detkirim_awal, 0) - COALESCE(detkirim, 0), 0)), 0) AS sisa_po_periode
     FROM detail_po
     WHERE detkodebrg = ? AND detidpel NOT IN (1, 2) AND dettglpo >= ? AND dettglpo <= ?',
    [$code, $start, $end]
)->getRowArray();

$shipments = $db->query(
    'SELECT COALESCE(SUM(dk.detjml), 0) AS qty_terkirim,
            COUNT(*) AS baris_surat_jalan
     FROM detail_barangkeluar dk
     INNER JOIN barangkeluar bk ON bk.faktur = dk.detfaktur
     WHERE dk.detbrgkode = ? AND bk.tglfaktur >= ? AND bk.tglfaktur <= ?',
    [$code, $start, $end]
)->getRowArray();

$stockRows = $db->query(
    'SELECT id, kodebarang, gudang, idpel, stok FROM stok WHERE kodebarang = ? ORDER BY gudang',
    [$code]
)->getResultArray();

$beforeStart = $db->query(
    'SELECT COALESCE(SUM(GREATEST(COALESCE(detqty, 0) - COALESCE(detkirim_awal, 0) - COALESCE(detkirim, 0), 0)), 0) AS outstanding_before
     FROM detail_po WHERE detkodebrg = ? AND detidpel NOT IN (1, 2) AND dettglpo < ?',
    [$code, $start]
)->getRowArray();

echo json_encode([
    'product_code' => $code,
    'period' => ['start' => $start, 'end' => $end],
    'rows_po' => $poRows,
    'outstanding_before_period' => $beforeStart,
    'po_summary_through_period_end' => $poSummary,
    'po_summary_inside_period' => $periodPo,
    'shipments_inside_period' => $shipments,
    'stock_rows' => $stockRows,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), PHP_EOL;
