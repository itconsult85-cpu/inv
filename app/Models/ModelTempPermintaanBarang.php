<?php

namespace App\Models;

use CodeIgniter\Model;

class ModelTempPermintaanBarang extends Model
{
    protected $table            = 'temp_permintaanbarang';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'detpermintaan',
        'dettglpermintaan',
        'detkodebrg',
        'namabarang',
        'jenis_item',
        'material',
        'detberat',
        'detqty',
        'detkirim',
        'detkurang',
        'detsubtotal',
        'gudang',
        'iduser'
    ];
    public function tampilDataTemp($permintaan)
    {
        return $this->select('temp_permintaanbarang.*, gudang.gdgnama')
            ->join('gudang', 'gudang.gdgid = temp_permintaanbarang.gudang', 'left')
            ->where('temp_permintaanbarang.detpermintaan', $permintaan)
            ->get();
    }

    public function hapusData($permintaan)
    {
        return $this->where('detpermintaan', $permintaan)->delete();
    }
}
