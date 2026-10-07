$p = 'app\Views\pohabispakai\index.php'
$s = [IO.File]::ReadAllText($p)
$s = $s.Replace('\\App\\Libraries\\PublicId', '\App\Libraries\PublicId')
$s = $s.Replace('poHabisPakai/edit/'' . $r[''id'']', 'poHabisPakai/edit/'' . \App\Libraries\PublicId::encode($r[''id''], ''po-bhp-id'')')
$s = $s.Replace('poHabisPakai/hapus/'' . $r[''id'']', 'poHabisPakai/hapus/'' . \App\Libraries\PublicId::encode($r[''id''], ''po-bhp-id'')')
$s = $s.Replace('poHabisPakai/hapusPenerimaan/'' . $r[''id'']', 'poHabisPakai/hapusPenerimaan/'' . \App\Libraries\PublicId::encode($r[''id''], ''po-bhp-receipt-id'')')
$s = $s.Replace('poHabisPakai/updatePenerimaan/'' . $r[''id'']', 'poHabisPakai/updatePenerimaan/'' . \App\Libraries\PublicId::encode($r[''id''], ''po-bhp-receipt-id'')')
[IO.File]::WriteAllText($p, $s, [Text.UTF8Encoding]::new($false))
