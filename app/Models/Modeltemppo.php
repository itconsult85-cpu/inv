<?php

namespace App\Models;

use CodeIgniter\Model;

class Modeltemppo extends Model
{
    protected $table            = 'temp_po';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'detnopo', 'detkodebrg', 'dettglpo', 'namabarang', 'idbarang', 'detberat', 'detqty', 'detkirim_awal', 'detinvoice_awal', 'detidpel', 'detidjasa', 'gudang', 'detsubtotal', 'detharga', 'material'
    ];

    public function tampilDataTemp($nopo)
    {
        return $this->table('temp_po')
            ->join('barang', 'detkodebrg=brgkode')
            // ->join('jasa', 'detidjasa=jasaid')
            ->where('detnopo', $nopo)->get();
    }

    public function hapusData($nopo)
    {
        return $this->where('detnopo', $nopo)->delete();
    }
}
