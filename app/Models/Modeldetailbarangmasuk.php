<?php

namespace App\Models;

use CodeIgniter\Model;

class Modeldetailbarangmasuk extends Model
{
    protected $table            = 'detail_barangmasuk';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'detfaktur', 'tgl', 'detbrgkode', 'detbrgnama', 'idsup', 'detmatkode', 'idbarang', 'detberat', 'detjml', 'gudang', 'detsubtotal'
    ];
    public function tampilDataTemp($nofaktur)
    {
        return $this->table('detail_barangmasuk')
            ->select('detail_barangmasuk.*, stok.kodebarang, stok.namabarang, stok.stok, material.matnama')
            ->join('stok', 'stok.kodebarang=detail_barangmasuk.detbrgkode AND stok.gudang = detail_barangmasuk.gudang')
            ->join('material', 'matid = detmatkode', 'left')
            // ->join('supplier', 'supid=idsup', 'left')
            ->where('detfaktur', $nofaktur)->get();
    }

    function ambilTotalBerat($nofaktur)
    {
        $query = $this->table('detail_barangmasuk')->getWhere([
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
        $query = $this->table('detail_barangmasuk')->getWhere([
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
        return $this->table('detail_barangmasuk')
            ->join('barang', 'detbrgkode=brgkode')
            ->join('satuan', 'brgsatid = satid')
            ->join('material', 'matid = detmatkode', 'left')
            ->join('supplier', 'supid=idsup', 'left')
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
