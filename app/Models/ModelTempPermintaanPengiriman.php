<?php

namespace App\Models;

use CodeIgniter\Model;

class ModelTempPermintaanPengiriman extends Model
{
    protected $table = 'temp_permintaan_pengiriman';
    protected $primaryKey = 'id';
    protected $allowedFields = ['token', 'kode_produk', 'nama_produk', 'berat', 'qty'];

    public function totalQty(string $token): int
    {
        return (int) ($this->selectSum('qty')->where('token', $token)->first()['qty'] ?? 0);
    }
}
