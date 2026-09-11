<?php

namespace App\Models;

use CodeIgniter\Model;

class Modelgudang extends Model
{
    protected $table            = 'gudang';
    protected $primaryKey       = 'gdgid';
    protected $allowedFields    = [
        'gdgid', 'gdgnama'
    ];

    public function cariData($cari)
    {
        return $this->table('gudang')->like('gdgnama', $cari);
    }

    public function cekId($id)
    {
        return $this->table('gudang')
            ->getWhere([
                'sha1(gdgid)' => $id
            ]);
    }
}
