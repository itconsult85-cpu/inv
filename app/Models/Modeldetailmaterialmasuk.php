<?php

namespace App\Models;

use CodeIgniter\Model;

class Modeldetailmaterialmasuk extends Model
{
    protected $table            = 'detail_materialmasuk';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'detfaktur', 'po_keluar_id', 'idmat', 'tgl', 'idsup', 'detmatkode', 'detberat', 'detjml', 'detsubtotal', 'gudang'
    ];

    public function tampilDataTemp($nofaktur)
    {
        return $this->table('detail_materialmasuk')
            ->join('material', 'detmatkode=matid')
            ->where('detfaktur', $nofaktur)->get();
    }

    public function getDataMaterial($kodematerial)
    {
        // return $this->where('matid', $idmaterial)->first();
        return $this->where('matkode', $kodematerial)->first();
    }

    function ambilTotalBerat($nofaktur)
    {
        $query = $this->table('detail_materialmasuk')->getWhere([
            'detfaktur' => $nofaktur
        ]);

        $totalBerat = 0;
        foreach ($query->getResultArray() as $r) :
            $totalBerat += $r['detsubtotal'];
        endforeach;

        return $totalBerat;
    }
}
