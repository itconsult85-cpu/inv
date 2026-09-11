<?php

namespace App\Models;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\Model;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\Config;
use CodeIgniter\Database\Query;

class Modelstokcirebon extends Model
{
    protected $table            = 'stokcirebon';
    protected $primaryKey       = 'brgkode';
    protected $allowedFields    = [
        'brgkode',
        'brgstok',
    ];

    public function tampildata()
    {
        return $this->table('stokcirebon')
            ->join('barang', 'brgkode=brgkode');
    }
}
