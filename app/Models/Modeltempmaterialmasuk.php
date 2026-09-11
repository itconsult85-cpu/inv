<?php

namespace App\Models;

use CodeIgniter\Model;

class Modeltempmaterialmasuk extends Model
{
    protected $table            = 'temp_materialmasuk';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'detfaktur', 'po_keluar_id', 'idmat', 'tgl', 'idsup', 'detmatid', 'detmatkode', 'detmatkatid', 'detmatsatid', 'dethargajual', 'detjml', 'detsubtotal', 'gudang'
    ];

    public function tampilDataTemp($faktur)
    {
        return $this->table('temp_materialmasuk')
            ->join('material', 'matid=detmatkode')
            ->where(['detfaktur' => $faktur])
            ->get();
    }

    public function hapusData($nofaktur)
    {
        return $this->where('detfaktur', $nofaktur)->delete();
    }
}
