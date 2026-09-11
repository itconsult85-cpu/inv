<?php

namespace App\Models;

use CodeIgniter\Model;

class ModelProduksiProduk extends Model
{
    protected $table            = 'produksi_produk';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'no_produksi', 'kode_produk', 'nama_produk', 'qty_produk', 'created_at',
    ];
}
