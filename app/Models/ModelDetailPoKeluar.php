<?php

namespace App\Models;

use CodeIgniter\Model;

class ModelDetailPoKeluar extends Model
{
    protected $table = 'detail_po_keluar';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'po_keluar_id',
        'no_po',
        'tipe_item',
        'kode_item',
        'nama_item',
        'print_spec',
        'satuan',
        'qty_pesan',
        'qty_masuk',
        'status',
        'harga',
        'subtotal',
        'created_at',
    ];
}
