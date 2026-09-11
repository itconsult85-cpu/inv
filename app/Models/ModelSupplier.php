<?php

namespace App\Models;

use CodeIgniter\Model;

class ModelSupplier extends Model
{
    protected $table            = 'supplier';
    protected $primaryKey       = 'supid';
    protected $allowedFields    = ['supnama', 'suppic', 'supemail', 'suptelp', 'alamat'];

    public function ambilDataTerakhir()
    {
        return $this->table('supplier')->limit(1)->orderBy('supid', 'DESC')->get();
    }

    public function getSupllierById($id)
    {
        return $this->db->table('supplier')->where('supid', $id)->get()->getRowArray();
    }
}
