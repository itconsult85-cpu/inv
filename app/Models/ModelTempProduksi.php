<?php

namespace App\Models;

use CodeIgniter\Model;

class ModelTempProduksi extends Model
{
    protected $table            = 'temp_produksi';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'det_no_produksi', 'det_kode_material', 'det_materialid', 'det_nama_material', 'det_satuan', 'det_qty', 'gudang',
    ];

    public function tampilDataTemp($noProduksi)
    {
        return $this->where('det_no_produksi', $noProduksi)->get();
    }

    public function hapusData($noProduksi)
    {
        return $this->where('det_no_produksi', $noProduksi)->delete();
    }
}
