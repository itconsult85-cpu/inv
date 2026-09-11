<?php

namespace App\Models;

use CodeIgniter\Model;

class Modeltempbarangmasuk extends Model
{
    protected $table            = 'temp_barangmasuk';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'detfaktur', 'detbrgkode', 'detbrgnama', 'idsup', 'detmatkode', 'idbarang', 'detberat', 'dethargajual', 'detjml', 'gudang', 'detsubtotal'
    ];

    public function tampilDataTemp($faktur)
    {
        return $this->table('temp_barangmasuk')
            // ->join('stok', 'stok.id=temp_barangmasuk.idbarang')
            ->join('material', 'matid=detmatkode', 'left')
            // ->join('supplier', 'supid=idsup')
            ->where(['detfaktur' => $faktur])->get();
    }

    public function hapusData($nofaktur)
    {
        return $this->where('detfaktur', $nofaktur)->delete();
    }
}
