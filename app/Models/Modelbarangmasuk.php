<?php

namespace App\Models;

use CodeIgniter\Model;

class Modelbarangmasuk extends Model
{
    protected $table            = 'barangmasuk';
    protected $primaryKey       = 'faktur';
    protected $allowedFields    = [
        'faktur', 'po_keluar_id', 'sumber', 'tglfaktur', 'idsup', 'gudang', 'qtymasuk', 'totalberatbarang'
    ];

    public function cekFaktur($faktur)
    {
        return $this->table('barangmasuk')
            ->select("barangmasuk.*, supplier.supid, COALESCE(supplier.supnama, 'Adjustment Stok') AS supnama, gudang.gdgid, gudang.gdgnama", false)
            ->join('supplier', 'supid = idsup', 'left')
            ->join('gudang', 'gdgid = gudang')
            // ->join('detail_barangmasuk', 'detail_barangmasuk.detfaktur = barangmasuk.faktur')
            // ->join('stok', 'stok.id = detail_barangmasuk.idbarang')
            ->getWhere([
                'sha1(faktur)' => $faktur,
            ]);
    }

    public function tampildata_cari($cari)
    {
        return $this->table('barangmasuk')->like('faktur', $cari);
    }

    public function laporanPerPeriode($tglawal, $tglakhir)
    {
        return $this->table('barangmasuk')->where('tglfaktur >=', $tglawal)->where('tglfaktur <=', $tglakhir)->get();
    }
}
