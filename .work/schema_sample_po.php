<?php
require 'preload.php';
$db=\Config\Database::connect();
foreach(['barangkeluar','detail_barangkeluar','po','detail_po','outstanding','stok'] as $t){
 echo "---$t---\n";
 if(!$db->tableExists($t)){echo "NOT_FOUND\n";continue;}
 echo $db->query("SHOW COLUMNS FROM `$t`")->getResultArray() ? json_encode($db->query("SHOW COLUMNS FROM `$t`")->getResultArray(), JSON_PRETTY_PRINT) : '';
 echo "\n";
}
