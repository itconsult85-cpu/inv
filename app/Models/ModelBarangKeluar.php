<?php

namespace App\Models;

use CodeIgniter\Model;

class ModelBarangKeluar extends Model
{
    protected $table            = 'barangkeluar';
    protected $primaryKey       = 'faktur';
    protected $allowedFields    = [
        'faktur', 'detpo', 'tglfaktur', 'idpel', 'qtykeluar', 'totalberatbarang', 'satuan', 'gudang'
    ];

    public function cekFaktur($faktur)
    {
        $sql = '
            SELECT bk.*, p.pelnama, g.gdgnama, dp.*
            FROM barangkeluar bk
            JOIN pelanggan p ON p.pelid = bk.idpel
            JOIN gudang g ON g.gdgid = bk.gudang
            JOIN po ON po.nopo = bk.detpo
            JOIN detail_po dp ON dp.detnopo = bk.detpo
            WHERE SHA1(bk.faktur) = ?
        ';

        return $this->db->query($sql, [$faktur]);
    }

    public function noFaktur($tanggalSekarang)
    {
        return $this->table('barangkeluar')->select('max(faktur) as nofaktur')->where('tglfaktur', $tanggalSekarang)->get();
    }

    public function laporanPerPeriode($tglawal, $tglakhir)
    {
        return $this->table('barangkeluar')->where('tglfaktur >=', $tglawal)->where('tglfaktur <=', $tglakhir)->get();
    }
}
