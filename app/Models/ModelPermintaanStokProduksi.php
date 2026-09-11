<?php

namespace App\Models;

use CodeIgniter\Model;

class ModelPermintaanStokProduksi extends Model
{
    protected $table = 'permintaan_stok_produksi';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['nomor', 'tanggal', 'peminta_id', 'status', 'catatan', 'approved_by', 'approved_at', 'approval_note', 'created_at', 'updated_at'];
}
