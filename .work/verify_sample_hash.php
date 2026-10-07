<?php
require 'preload.php';
$db = db_connect();
$row = $db->table('barangkeluar')->select('faktur')->where('sumber','sample')->like('faktur','/')->orderBy('tglfaktur','DESC')->get(1)->getRowArray();
if (!$row) { echo "NO_SLASH_SAMPLE\n"; exit; }
$hash = sha1($row['faktur']);
$found = $db->table('barangkeluar')->where('sumber','sample')->groupStart()->where('faktur',$hash)->orWhere('SHA1(faktur)',$hash)->groupEnd()->get()->getRowArray();
echo json_encode(['faktur'=>$row['faktur'],'hash'=>$hash,'found'=>$found ? $found['faktur'] : null], JSON_UNESCAPED_SLASHES) . PHP_EOL;
