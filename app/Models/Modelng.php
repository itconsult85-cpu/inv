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

    public function detailBySupplierMaterial(int $supplierId, int $materialId): array
    {
        return $this->select('ngdata.*, material.matkode, material.matnama')
            ->join('material', 'material.matid = ngdata.matjenis', 'left')
            ->where('ngdata.idsup', $supplierId)
            ->where('ngdata.matjenis', $materialId)
            ->orderBy('ngdata.tgl', 'DESC')
            ->orderBy('ngdata.id', 'DESC')
            ->findAll();
    }
}
