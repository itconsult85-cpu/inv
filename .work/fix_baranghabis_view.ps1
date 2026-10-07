$p = 'app\Views\baranghabispakai\index.php'
$s = [IO.File]::ReadAllText($p)
$s = $s.Replace('\\App\\Libraries\\PublicId', '\App\Libraries\PublicId')
$s = $s.Replace('baranghabispakai/setujui/'' . $row[''id'']', 'baranghabispakai/setujui/'' . \App\Libraries\PublicId::encode($row[''id''], ''bhp-request-id'')')
$s = $s.Replace('baranghabispakai/tolak/'' . $row[''id'']', 'baranghabispakai/tolak/'' . \App\Libraries\PublicId::encode($row[''id''], ''bhp-request-id'')')
[IO.File]::WriteAllText($p, $s, [Text.UTF8Encoding]::new($false))
