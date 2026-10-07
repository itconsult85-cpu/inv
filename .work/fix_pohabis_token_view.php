<?php
$p = __DIR__ . '/../app/Views/pohabispakai/index.php';
$s = file_get_contents($p);
$s = str_replace('\\\\App\\\\Libraries\\\\PublicId', '\\App\\Libraries\\PublicId', $s);
$s = str_replace("poHabisPakai/edit/' . $r['id']", "poHabisPakai/edit/' . \\App\\Libraries\\PublicId::encode($r['id'], 'po-bhp-id')", $s);
$s = str_replace("poHabisPakai/hapus/' . $r['id']", "poHabisPakai/hapus/' . \\App\\Libraries\\PublicId::encode($r['id'], 'po-bhp-id')", $s);
$s = str_replace("poHabisPakai/hapusPenerimaan/' . $r['id']", "poHabisPakai/hapusPenerimaan/' . \\App\\Libraries\\PublicId::encode($r['id'], 'po-bhp-receipt-id')", $s);
$s = str_replace("poHabisPakai/updatePenerimaan/' . $r['id']", "poHabisPakai/updatePenerimaan/' . \\App\\Libraries\\PublicId::encode($r['id'], 'po-bhp-receipt-id')", $s);
file_put_contents($p, $s);
