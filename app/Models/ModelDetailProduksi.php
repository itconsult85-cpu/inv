<?php

namespace App\Models;

use CodeIgniter\Model;

class ModelDetailProduksi extends Model
{
    protected $table            = 'detail_produksi';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'no_produksi', 'produksi_produk_id', 'kode_material', 'materialid', 'nama_material', 'satuan', 'qty_material',
    ];

    public function tampilDataDetail($noProduksi)
    {
        return $this->where('no_produksi', $noProduksi)->get();
    }
}
