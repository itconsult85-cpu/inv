<?php

namespace App\Models;

use CodeIgniter\Model;

class ModelStokHabisPakai extends Model
{
    protected $table = 'stok_habis_pakai';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['kode', 'nama', 'satuan', 'stok', 'stok_minimum', 'aktif', 'created_by', 'created_at', 'updated_at'];
}
