<?php

namespace App\Models;

use CodeIgniter\Model;

class ModelTempBarangKeluar extends Model
{
    protected $table            = 'temp_barangkeluar';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'detfaktur', 'detpo', 'detidpel', 'tgl', 'detbrgkode', 'namabarang', 'material', 'idbarang', 'detberat', 'detjml', 'detqtykeluar', 'gudang', 'detsubtotal'
    ];

    public function tampilDataTemp($nofaktur)
    {
        return $this->table('temp_barangkeluar')
            // ->join('stok', 'id=idbarang')
            ->where('detfaktur', $nofaktur)->get();
    }

    public function hapusData($nofaktur)
    {
        return $this->where('detfaktur', $nofaktur)->delete();
    }
}
