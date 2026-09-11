<?php

namespace App\Models;

use CodeIgniter\Model;

class ModelBarangRiwayat extends Model
{
    protected $table = 'barang_riwayat';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'brgkode', 'data_lama', 'data_baru', 'diubah_oleh', 'diubah_pada',
    ];
}
