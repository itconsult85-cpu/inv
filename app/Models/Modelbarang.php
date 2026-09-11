<?php

namespace App\Models;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\Model;

class Modelbarang extends Model
{
    protected $table            = 'barang';
    protected $primaryKey       = 'brgkode';
    protected $allowedFields    = [
        'brgkode',
        'brgnama',
        'brgmat',
        'tanpa_berat',
        'sumber_material',
        'brgkatid',
        'brgsatid',
        'brgstok',
        'satuanberat',
        'wise',
        'harga',
        'minstok',
        'idpel',
    ];

    public function tampildata()
    {
        return $this->table('barang')
            ->join('kategori', 'brgkatid=katid')
            ->join('satuan', 'brgsatid=satid')
            ->join('pelanggan', 'pelid=idpel');
    }

    public function cekId($kode)
    {
        return $this->table('barang')
            // ->join('stok', 'kodebarang=brgkode')
            ->join('kategori', 'brgkatid=katid')
            ->join('satuan', 'brgsatid=satid')
            ->join('pelanggan', 'pelid=idpel')
            ->getWhere([
                'sha1(brgkode)' => $kode
            ]);
    }

    public function tampildata_cari($cari)
    {
        return $this->table('barang')
            ->join('kategori', 'brgkatid=katid')
            ->join('satuan', 'brgsatid=satid')
            ->join('pelanggan', 'pelid=idpel')
            ->orlike('brgkode', $cari)
            ->orlike('brgnama', $cari);
    }
}
