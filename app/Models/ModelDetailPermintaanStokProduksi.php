<?php

namespace App\Models;

use CodeIgniter\Model;

class ModelDetailPermintaanStokProduksi extends Model
{
    protected $table = 'permintaan_stok_produksi_detail';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['permintaan_id', 'stok_id', 'qty_diminta', 'qty_disetujui', 'catatan'];
}
