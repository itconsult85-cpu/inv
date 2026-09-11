<?php

namespace App\Models;

use CodeIgniter\Model;

class ModelPermintaanPengiriman extends Model
{
    protected $table = 'permintaan_pengiriman';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'tanggal', 'tanggal_pengiriman', 'iduser', 'total_produk', 'jenis_pengiriman',
        'idpel', 'pic_pengirim', 'nominal', 'keterangan', 'status',
    ];

    public function cekHash(string $hash)
    {
        return $this->db->table($this->table . ' p')
            ->select('p.*, u.usernama')
            ->join('users u', 'u.id = p.iduser', 'left')
            ->where('SHA1(p.id)', $hash)
            ->get();
    }
}
