<?php

namespace App\Models;

use CodeIgniter\Model;

class Modelberat extends Model
{
    protected $table            = 'berat';
    protected $primaryKey       = 'kodeprd';
    protected $allowedFields    = [
        'kodeprd',
        'kodemat',
        'berat',
        'satuan',
    ];

    public function getDataBeratKodeprd()
    {
        return $this->distinct()
            ->select('kodeprd')
            ->findAll();
    }

    public function getDataBeratExisting()
    {
        return $this->findAll(); // Sesuaikan dengan query yang sesuai
    }

    public function tampildata()
    {
        return $this->table('berat')
            ->join('barang', 'kodeprd=brgkode')
            ->join('satuan', 'satuan=satid')
            ->join('material', 'kodemat=matid');
    }

    public function cekKode($kode)
    {
        return $this->table('berat')
            ->join('barang', 'kodeprd=brgkode')
            ->join('satuan', 'satuan=satid')
            ->join('material', 'kodemat=matid')
            ->getWhere([
                'sha1(kodeprd)' => $kode
            ]);
    }

    public function tampildata_cari($cari)
    {
        return $this->table('berat')
            ->join('barang', 'kodeprd=brgkode')
            ->join('satuan', 'satuan=satid')
            ->join('material', 'kodemat=matid')
            ->orlike('kodeprd', $cari)
            ->orlike('kodemat', $cari);
    }
}
