<?php

namespace App\Models;

use CodeIgniter\Model;

class ModelPermintaanBarang extends Model
{
    protected $table            = 'permintaanbarang';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'permintaan',
        'tglpermintaan',
        'iduser',
        'qtypermintaan',
        'ketpermintaan',
        'jenispengiriman',
        'picpengirim',
        'nominal',
    ];

    public function cekPermintaan($id)
    {
        return $this->table('permintaanbarang')
            ->join('users', 'users.id=permintaanbarang.iduser')
            ->getWhere([
                'sha1(permintaanbarang.id)' => $id
            ]);
    }

    public function noPermintaan($tanggalSekarang)
    {
        return $this->table('permintaanbarang')
            ->select('max(permintaan) as permintaan')
            ->where('tglpermintaan', $tanggalSekarang)
            ->get();
    }


    public function laporanPerPeriode($tglawal, $tglakhir)
    {
        return $this->table('permintaanbarang')
            ->where('tglpermintaan >=', $tglawal)
            ->where('tglpermintaan <=', $tglakhir)
            ->get();
    }

    function ambilTotalQty($permintaan)
    {
        $query = $this->table('detail_permintaanbarang')
            ->getWhere([
                'detpermintaan' => $permintaan
            ]);

        $totalQty = 0;
        foreach ($query->getResultArray() as $r) :
            $totalQty += $r['detqty'];
        endforeach;

        return $totalQty;
    }
}
