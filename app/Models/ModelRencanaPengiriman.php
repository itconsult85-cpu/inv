<?php

namespace App\Models;

use CodeIgniter\Model;

class ModelRencanaPengiriman extends Model
{
    protected $table = 'rencana_pengiriman';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'permintaan_id', 'detail_id', 'kode_produk', 'qty', 'sumber',
        'gudang_id', 'no_do', 'no_btb', 'btb_file', 'btb_original_name', 'no_po', 'tanggal_po', 'status', 'created_at',
    ];
}
