<?php

namespace App\Models;

use CodeIgniter\Model;

class ModelDetailPermintaanPengiriman extends Model
{
    protected $table = 'detail_permintaan_pengiriman';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'permintaan_id', 'idpel', 'tanggal_po', 'kode_produk', 'nama_produk', 'berat', 'qty',
    ];
}
