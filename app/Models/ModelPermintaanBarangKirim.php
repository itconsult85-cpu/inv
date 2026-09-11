<?php

namespace App\Models;

use CodeIgniter\Model;

class ModelPermintaanBarangKirim extends Model
{
    protected $table            = 'permintaanbarangkirim';
    protected $primaryKey       = 'faktur';
    protected $allowedFields    = [
        'faktur', 'detpermintaan', 'tglfaktur', 'iduser', 'qtykirim', 'totalberatbarang', 'satuan', 'gudang', 'jenispengiriman', 'picpengirim', 'nominal'
    ];

    public function cekFaktur($faktur)
    {
        $sql = '
            SELECT
                pk.faktur,
                pk.detpermintaan,
                pk.tglfaktur,
                pk.iduser,
                pk.qtykirim,
                pk.totalberatbarang,
                pk.satuan,
                pk.gudang,
                pk.jenispengiriman,
                pk.picpengirim,
                pk.nominal,
                u.usernama,
                g.gdgnama
            FROM permintaanbarangkirim pk
            JOIN users u ON u.id = pk.iduser
            JOIN gudang g ON g.gdgid = pk.gudang
            WHERE SHA1(pk.faktur) = ?
            LIMIT 1
        ';

        return $this->db->query($sql, [$faktur]);
    }

    public function noFaktur($tanggalSekarang)
    {
        return $this->table('permintaanbarangkirim')->select('max(faktur) as nofaktur')->where('tglfaktur', $tanggalSekarang)->get();
    }

    public function laporanPerPeriode($tglawal, $tglakhir)
    {
        return $this->table('permintaanbarangkirim')->where('tglfaktur >=', $tglawal)->where('tglfaktur <=', $tglakhir)->get();
    }
}
