<?php

namespace App\Models;

use CodeIgniter\Model;

class ModelDetailPermintaanBarang extends Model
{
    protected $table            = 'detail_permintaanbarang';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'detpermintaan',
        'dettglpermintaan',
        'detkodebrg',
        'namabarang',
        'jenis_item',
        'material',
        'detberat',
        'detqty',
        'detkirim',
        'detkurang',
        'detsubtotal',
        'gudang',
        'iduser'
    ];

    public function tampilDataTemp($permintaan)
    {
        return $this->db->table('detail_permintaanbarang')
            ->select('detail_permintaanbarang.*, CASE WHEN detail_permintaanbarang.jenis_item = "material" THEN COALESCE(SUM(stokmaterial.stok), 0) ELSE COALESCE(SUM(stok.stok), 0) END as total_stok')
            ->join('stok', 'stok.kodebarang = detail_permintaanbarang.detkodebrg AND detail_permintaanbarang.jenis_item = "produk"', 'left')
            ->join('stokmaterial', 'stokmaterial.kodematerial = detail_permintaanbarang.detkodebrg AND detail_permintaanbarang.jenis_item = "material"', 'left')
            ->where('detail_permintaanbarang.detpermintaan', $permintaan)
            ->groupBy('detail_permintaanbarang.id') // Group by primary key detail_permintaanbarang
            ->get();
    }

    function ambilTotalQty($permintaan)
    {
        $query = $this->table('detail_permintaanbarang')->getWhere([
            'detpermintaan' => $permintaan
        ]);

        $totalQty = 0;
        foreach ($query->getResultArray() as $r) :
            $totalQty += $r['detqty'];
        endforeach;

        return $totalQty;
    }

    function ambilTotalQtyid($id)
    {
        $query = $this->table('detail_permintaanbarang')->getWhere([
            'id' => $id
        ]);

        $totalQty = 0;
        foreach ($query->getResultArray() as $r) :
            $totalQty += $r['detqty'];
        endforeach;

        return $totalQty;
    }

    public function updateDetailPermintaanKurang()
    {
        // Query UPDATE untuk mengupdate data di tabel `detail_permintaanbarang` dengan kolom `detkurang`
        $query = "UPDATE detail_permintaanbarang SET detkurang = detqty - detkirim";

        // Eksekusi query
        $this->db->query($query);
    }
}
