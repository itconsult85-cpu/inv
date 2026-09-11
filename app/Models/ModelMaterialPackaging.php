<?php
// Aziz - create new model MaterialPackaging
namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Database\Query;

class ModelMaterialPackaging extends Model
{
    protected $table            = 'materialpackaging';
    protected $primaryKey       = 'matpid';
    protected $allowedFields    = [
        'matpid',
        'matpkode',
        'matpnama',
        'matpstok',
    ];

    /**
     * Mengambil satu baris data berdasarkan matid
     *
     * @param int $idmaterial ID material yang akan diambil datanya
     * @return array|null        Data material jika ditemukan, null jika tidak ditemukan
     */
    public function getMaterialByJenis($jenis)
    {
        return $this->db->table('materialpackaging')->where('matpid', $jenis)->get()->getRowArray();
    }
    public function getDataMaterial($idmaterial, $kodematerial)
    {
        $result = $this->where('matpid', $idmaterial)
            ->orWhere('matpkode', $kodematerial) // Menggunakan orWhere() untuk mencari berdasarkan matkode
            ->first();
        return $result;
    }

    public function tampildata()
    {
        return $this->table('materialpackaging');
    }

    public function cekId($id)
    {
        return $this->table('materialpackaging')
            ->getWhere([
                'sha1(matpid)' => $id
            ]);
    }

    public function tampildata_cari($cari)
    {
        return $this->table('materialpackaging')
            ->orlike('matpkode', $cari)
            ->orlike('matpnama', $cari);
    }
}
