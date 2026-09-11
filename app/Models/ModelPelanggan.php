<?php

namespace App\Models;

use CodeIgniter\Model;

class ModelPelanggan extends Model
{
    protected $table            = 'pelanggan';
    protected $primaryKey       = 'pelid';
    protected $allowedFields    = ['pelnama', 'pelpic', 'pelemail', 'pelalamat', 'peltelp', 'pelfax', 'pelto', 'gdgid'];

    public function ambilDataTerakhir()
    {
        return $this->table('pelanggan')->limit(1)->orderBy('pelid', 'DESC')->get();
    }
}
