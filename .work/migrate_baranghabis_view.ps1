$p = 'app\Views\baranghabispakai\index.php'
$s = [IO.File]::ReadAllText($p)
$s = $s.Replace('data-id="<?= $item[''id''] ?>"', 'data-id="<?= \\App\\Libraries\\PublicId::encode($item[''id''], ''bhp-stock-id'') ?>"')
$s = $s.Replace('data-detail="<?= $row[''po_detail_id''] ?>"', 'data-detail="<?= \\App\\Libraries\\PublicId::encode($row[''po_detail_id''], ''bhp-po-detail-id'') ?>"')
$s = $s.Replace('name="stok_id[]" value="<?= $item[''id''] ?>"', 'name="stok_id[]" value="<?= \\App\\Libraries\\PublicId::encode($item[''id''], ''bhp-stock-id'') ?>"')
$s = $s.Replace("baranghabispakai/setujui/' . $row['id']", "baranghabispakai/setujui/' . \\App\\Libraries\\PublicId::encode($row['id'], 'bhp-request-id')")
$s = $s.Replace("baranghabispakai/tolak/' . $row['id']", "baranghabispakai/tolak/' . \\App\\Libraries\\PublicId::encode($row['id'], 'bhp-request-id')")
$s = $s.Replace('\\\\App\\\\Libraries\\\\PublicId', '\App\Libraries\PublicId')
[IO.File]::WriteAllText($p, $s, [Text.UTF8Encoding]::new($false))
