<?php
require __DIR__ . '/../preload.php';
$db = \Config\Database::connect();
if (!$db->tableExists('ngdata')) { echo "TABLE_NOT_FOUND\n"; exit; }
$row = $db->query('SHOW CREATE TABLE ngdata')->getRowArray();
echo ($row['Create Table'] ?? '') . "\n";
