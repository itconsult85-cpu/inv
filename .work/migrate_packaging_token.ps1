$p='app\Controllers\Packaging.php'
$s=[IO.File]::ReadAllText($p)
$s=$s.Replace('($row->matpid)', '$this->publicId($row->matpid, ''packaging-id'')')
$s=$s.Replace('$row->matpid . "''', '$this->publicId($row->matpid, ''packaging-id'') . "''')
$s=$s.Replace('$cekData = $this->packaging->find($id);', '$idAsli = $this->resolvePublicId((string) $id, ''packaging-id'');`r`n        if ($idAsli === null || !ctype_digit($idAsli)) { return redirect()->to(''/packaging/index'')->with(''error'', ''Token packaging tidak valid.''); }`r`n        $id = (int) $idAsli;`r`n        $cekData = $this->packaging->find($id);')
$s=$s.Replace("'id' => $id,", "'id' => $this->publicId($id, 'packaging-id'),")
$s=$s.Replace('$idmaterial = $this->request->getVar(''idmaterial'');', '$idmaterial = $this->resolvePublicId($this->request->getVar(''idmaterial''), ''packaging-id'');`r`n            if ($idmaterial === null || !ctype_digit($idmaterial)) { return redirect()->to(''/packaging/index'')->with(''error'', ''Token packaging tidak valid.''); }`r`n            $idmaterial = (int) $idmaterial;')
$s=$s.Replace('$kode = $this->request->getPost(''kode'');', '$kode = $this->resolvePublicId($this->request->getPost(''kode''), ''packaging-id'');`r`n            if ($kode === null || !ctype_digit($kode)) { return $this->response->setJSON([''error'' => ''Token packaging tidak valid'']); }`r`n            $kode = (int) $kode;')
[IO.File]::WriteAllText($p,$s,(New-Object Text.UTF8Encoding($false)))
