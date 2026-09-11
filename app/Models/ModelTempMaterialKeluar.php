<?php

namespace App\Models;

use CodeIgniter\Model;

class ModelTempMaterialKeluar extends Model
{
    protected $table            = 'temp_materialkeluar';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'detfaktur', 'idmat', 'tgl', 'detmatkode', 'detmatkatid', 'detmatsatid', 'idsup', 'detjml', 'detsubtotal'
    ];

    public function tampilDataTemp($nofaktur)
    {
        return $this->table('temp_materialkeluar')
            ->join('material', 'detmatkode=matid')
            ->where('detfaktur', $nofaktur)
            ->get();
    }

    public function hapusData($nofaktur)
    {
        return $this->where('detfaktur', $nofaktur)->delete();
    }
}
