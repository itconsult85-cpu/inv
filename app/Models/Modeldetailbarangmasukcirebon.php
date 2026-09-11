<?php

namespace App\Models;

use CodeIgniter\Model;

class Modeldetailbarangmasukcirebon extends Model
{
    protected $table            = 'detail_barangmasukcirebon';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'detfaktur', 'detbrgkode', 'idsup', 'detmatkode', 'detberat', 'detjml', 'gudang', 'detsubtotal'
    ];

    public function tampilDataTemp($nofaktur)
    {
        return $this->table('detail_barangmasukcirebon')
            ->join('barang', 'detbrgkode=brgkode')
            ->join('satuan', 'brgsatid = satid')
            ->join('material', 'matid = detmatkode')
            ->join('supplier', 'supid=idsup')
            ->join('gudang', 'gdgid=gudang')
            ->where('detfaktur', $nofaktur)->get();
    }

    function ambilTotalBerat($nofaktur)
    {
        $query = $this->table('detail_barangmasukcirebon')->getWhere([
            'detfaktur' => $nofaktur
        ]);

        $totalBerat = 0;
        foreach ($query->getResultArray() as $r) :
            $totalBerat += $r['detsubtotal'];
        endforeach;

        return $totalBerat;
    }

    function ambilTotalQty($nofaktur)
    {
        $query = $this->table('detail_barangkeluarcirebon')->getWhere([
            'detfaktur' => $nofaktur
        ]);

        $totalQty = 0;
        foreach ($query->getResultArray() as $r) :
            $totalQty += $r['detjml'];
        endforeach;

        return $totalQty;
    }

    public function ambilDetailBerdasarkanID($iddetail)
    {
        return $this->table('detail_barangmasukcirebon')
            ->join('barang', 'detbrgkode=brgkode')
            ->join('satuan', 'brgsatid = satid')
            ->join('material', 'matid = detmatkode')
            ->join('supplier', 'supid=idsup')
            ->join('gudang', 'gdgid=gudang')
            ->where('id', $iddetail)->get();
    }

    public function sumDetsubtotalMasuk($idsup, $detmatkode)
    {
        return $this->selectSum('detsubtotal')
            ->where(['idsup' => $idsup, 'detmatkode' => $detmatkode])
            ->get()
            ->getRow()
            ->detsubtotal;
    }
}
