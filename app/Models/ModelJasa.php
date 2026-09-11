<?php

namespace App\Models;

use CodeIgniter\Model;

class ModelJasa extends Model
{
    protected $table            = 'jasa';
    protected $primaryKey       = 'idjasa';
    protected $allowedFields    = ['namajasa', 'harga_modal'];

    public function ambilDataTerakhir()
    {
        return $this->table('jasa')->limit(1)->orderBy('idjasa', 'DESC')->get();
    }
}
