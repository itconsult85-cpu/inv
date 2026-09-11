<?php

namespace App\Models;

use CodeIgniter\Model;

class ModelMaterialKeluar extends Model
{
    protected $table            = 'materialkeluar';
    protected $primaryKey       = 'faktur';
    protected $allowedFields    = [
        'faktur', 'tglfaktur', 'idsup', 'totalberatmaterial', 'satuan', 'gudang', 'keterangan'
    ];

    public function noFaktur($tanggalSekarang)
    {
        return $this->table('materialkeluar')->select('max(faktur) as nofaktur')->where('tglfaktur', $tanggalSekarang)->get();
    }

    public function cekFaktur($faktur)
    {
        return $this->table('materialkeluar')
            ->join('supplier', 'supid=idsup')
            ->join('gudang', 'gdgid=gudang')
            ->getWhere([
                'sha1(faktur)' => $faktur
            ]);
    }

    public function laporanPerPeriode($tglawal, $tglakhir)
    {
        return $this->table('materialkeluar')->where('tglfaktur >=', $tglawal)->where('tglfaktur <=', $tglakhir)->get();
    }
}
