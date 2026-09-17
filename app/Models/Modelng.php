<?php

namespace App\Models;

use CodeIgniter\Model;

class Modelng extends Model
{
    protected $table = 'ngdata';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id', 'idsup', 'tgl', 'matjenis', 'beratmatkeluar', 'beratmatmasuk', 'beratng', 'satuan'];

    public function laporanPerPeriode($tglawal, $tglakhir)
    {
        return $this->table('ngdata')->where('tgl >=', $tglawal)->where('tgl <=', $tglakhir)->get();
    }
}
