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

    public function cekHash(string $token)
    {
        $id = \App\Libraries\PublicId::decode($token, 'permintaan-pengiriman-id');
        if ($id === null || !ctype_digit($id) || (int) $id <= 0) {
            return $this->db->table($this->table)->where('id', 0)->get();
        }

        return $this->db->table($this->table . ' p')
            ->select('p.*, u.usernama')
            ->join('users u', 'u.id = p.iduser', 'left')
            ->where('p.id', (int) $id)
            ->get();
    }
}
