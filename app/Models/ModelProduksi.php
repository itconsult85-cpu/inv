<?php

namespace App\Models;

use CodeIgniter\Model;

class ModelProduksi extends Model
{
    protected $table            = 'produksi';
    protected $primaryKey       = 'no_produksi';
    // no_produksi sekarang header batch -- kode_produk/nama_produk/qty_produk
    // pindah ke ModelProduksiProduk (banyak produk per batch), kolomnya
    // masih ada di tabel (nullable) buat data lama tapi tidak lagi ditulis.
    protected $allowedFields    = [
        'no_produksi', 'tgl_produksi', 'gudang', 'iduser', 'created_at', 'keterangan',
    ];

    public function cekProduksi($noProduksi)
    {
        $db = db_connect();

        return $db->table('produksi p')
            ->select('p.*, g.gdgnama')
            ->join('gudang g', 'g.gdgid = p.gudang', 'left')
            ->join('users u', 'u.id = p.iduser', 'left')
            ->where('SHA1(p.no_produksi) = ' . $db->escape($noProduksi), null, false)
            ->get();
    }

    public function laporanPerPeriode($tglawal, $tglakhir)
    {
        return $this->table('produksi')->where('tgl_produksi >=', $tglawal)->where('tgl_produksi <=', $tglakhir)->get();
    }
}
