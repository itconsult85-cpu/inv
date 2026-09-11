<?php

namespace App\Models;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\Model;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\Config;
use CodeIgniter\Database\Query;

class Modelstokcikarang extends Model
{
    protected $table            = 'stokcikarang';
    protected $primaryKey       = 'brgkode';
    protected $allowedFields    = [
        'brgkode',
        'brgstok',
    ];

    public function tampildata()
    {
        return $this->table('stokcikarang')
            ->join('barang', 'brgkode=brgkode');
    }
}
