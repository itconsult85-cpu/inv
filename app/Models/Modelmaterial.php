<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Database\Query;

class Modelmaterial extends Model
{
    protected $table            = 'material';
    protected $primaryKey       = 'matid';
    protected $allowedFields    = [
        'matid',
        'matkode',
        'matnama',
        'matkatid',
        'matsatid',
        'matstok',
        'minmat',
        'matgudang',
    ];

    /**
     * Mengambil satu baris data berdasarkan matid
     *
     * @param int $idmaterial ID material yang akan diambil datanya
     * @return array|null        Data material jika ditemukan, null jika tidak ditemukan
     */
    public function getMaterialByJenis($jenis)
    {
        return $this->db->table('material')->where('matid', $jenis)->get()->getRowArray();
    }
    public function getDataMaterial($idmaterial, $kodematerial)
    {
        $result = $this->where('matid', $idmaterial)
            ->orWhere('matkode', $kodematerial) // Menggunakan orWhere() untuk mencari berdasarkan matkode
            ->first();
        return $result;
    }

    public function tampildata()
    {
        return $this->table('material')
            ->join('kategori', 'matkatid=katid')
            ->join('satuan', 'matsatid=satid');
    }

    public function cekId($id)
    {
        return $this->table('material')
            ->join('kategori', 'matkatid=katid')
            ->join('satuan', 'matsatid=satid')
            ->getWhere([
                'sha1(matid)' => $id
            ]);
    }

    public function tampildata_cari($cari)
    {
        return $this->table('material')
            ->join('kategori', 'matkatid=katid')
            ->join('satuan', 'matsatid=satid')
            ->orlike('matkode', $cari)
            ->orlike('matnama', $cari);
    }
}
