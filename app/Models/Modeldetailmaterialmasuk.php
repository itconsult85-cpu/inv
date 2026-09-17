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
            ->select('detail_materialmasuk.*, material.matkode, material.matnama, COALESCE(ngdata.beratng, 0) AS current_ng', false)
            ->join('material', 'detail_materialmasuk.detmatkode=material.matid')
            ->join('ngdata', 'ngdata.detfaktur=detail_materialmasuk.detfaktur AND ngdata.tgl=detail_materialmasuk.tgl AND ngdata.idsup=detail_materialmasuk.idsup AND ngdata.matjenis=detail_materialmasuk.detmatkode', 'left', false)
            ->where('detail_materialmasuk.detfaktur', $nofaktur)->get();
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
