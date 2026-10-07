$p='app\Controllers\Material.php'
$s=[IO.File]::ReadAllText($p)
$old=@'
        $hash = trim((string) $this->request->getPost('hash'));
        $cekId = $this->material->cekId($hash);
'@
$new=@'
        $materialId = $this->resolvePublicId($this->request->getPost('hash'), 'material-id');
        if ($materialId === null || !ctype_digit($materialId)) {
            return $this->response->setJSON(['error' => 'Token material tidak valid.']);
        }
        $cekId = $this->material->cekId(sha1($materialId));
'@
$s=$s.Replace($old,$new)
[IO.File]::WriteAllText($p,$s,(New-Object Text.UTF8Encoding($false)))
