<?php
require 'preload.php';
$db=\Config\Database::connect();
foreach(['barangkeluar','detail_barangkeluar','stok','pelanggan','gudang'] as $t){echo "---$t---\n"; if(!$db->tableExists($t)){echo "NOT_FOUND\n";continue;} $r=$db->query("SHOW CREATE TABLE `$t`")->getRowArray(); echo ($r['Create Table'] ?? ''); echo "\n";}
