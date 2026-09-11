<?php

namespace App\Models;

use CodeIgniter\Model;

class ModelDetailMaterialKeluar extends Model
{
    protected $table            = 'detail_materialkeluar';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'detfaktur', 'idmat', 'tgl', 'detmatkode', 'idsup', 'detjml', 'detsubtotal', 'gudang'
    ];

    public function tampilDataTemp($nofaktur)
    {
        return $this->table('detail_materialkeluar')
            ->join('material', 'detmatkode=matid')
            ->join('supplier', 'supid=idsup')
            ->join('satuan', 'matsatid = satid')
            ->where('detfaktur', $nofaktur)->get();
    }

    function ambilTotalBerat($nofaktur)
    {
        $query = $this->table('detail_materialkeluar')->getWhere([
            'detfaktur' => $nofaktur
        ]);

        $totalBerat = 0;
        foreach ($query->getResultArray() as $r) :
            $totalBerat += $r['detsubtotal'];
        endforeach;

        return $totalBerat;
    }

    public function sumDetsubtotalKeluar($idsup, $detmatkode)
    {
        return $this->selectSum('detsubtotal')
            ->where(['idsup' => $idsup, 'detmatkode' => $detmatkode])
            ->get()
            ->getRow()
            ->detsubtotal;
    }
}
