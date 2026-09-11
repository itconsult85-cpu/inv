<?php

namespace App\Models;

use CodeIgniter\Model;

class Modelmaterialmasuk extends Model
{
    protected $table            = 'materialmasuk';
    protected $primaryKey       = 'faktur';
    protected $allowedFields    = [
        'faktur', 'no_invoice', 'po_keluar_id', 'no_do', 'tglfaktur', 'idsup', 'sumber', 'idpel', 'totalberatmaterial', 'satuan', 'gudang'
    ];

    public function tampildata_cari($cari)
    {
        return $this->table('materialmasuk')
            ->groupStart()
            ->like('faktur', $cari)
            ->orLike('no_invoice', $cari)
            ->groupEnd();
    }

    public function cekFaktur($faktur)
    {
        return $this->table('materialmasuk')
            ->join('supplier', 'supplier.supid = materialmasuk.idsup', 'left')
            ->join('pelanggan', 'pelanggan.pelid = materialmasuk.idpel', 'left')
            ->join('gudang', 'gudang.gdgid = materialmasuk.gudang')
            ->getWhere([
                'sha1(materialmasuk.faktur)' => $faktur
            ]);
    }

    public function laporanPerPeriode($tglawal, $tglakhir)
    {
        return $this->table('materialmasuk')->where('tglfaktur >=', $tglawal)->where('tglfaktur <=', $tglakhir)->get();
    }
}
