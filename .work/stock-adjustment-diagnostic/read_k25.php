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
$stock = $db->table('stok')
    ->select('id, kodebarang, gudang, stok, time')
    ->where('kodebarang', 'K25')
    ->orderBy('gudang')
    ->get()->getResultArray();
$details = $db->table('detail_barangmasuk d')
    ->select('d.id, d.detfaktur, d.detjml, d.idbarang, d.gudang, bm.tglfaktur, bm.sumber')
    ->join('barangmasuk bm', 'bm.faktur = d.detfaktur', 'left')
    ->where('d.detbrgkode', 'K25')
    ->orderBy('d.id', 'DESC')
    ->limit(12)
    ->get()->getResultArray();
$triggers = $db->query("SHOW TRIGGERS WHERE `Trigger` = 'tri_restore_stok'")->getResultArray();
$triggerSummary = array_map(static fn(array $trigger): array => [
    'name' => $trigger['Trigger'] ?? null,
    'table' => $trigger['Table'] ?? null,
    'statement' => $trigger['Statement'] ?? null,
], $triggers);

echo json_encode([
    'stock_rows' => $stock,
    'recent_receipts' => $details,
    'stock_delete_trigger' => $triggerSummary,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), PHP_EOL;
