<?php

namespace App\Models;

use CodeIgniter\Model;

class Modelstok extends Model
{
    protected $table            = 'stok';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'kodebarang',
        'namabarang',
        'material',
        'gudang',
        'berat',
        'stok',
        'harga',
        'idpel',
        'satuan',
        'satuanberat',
    ];

    public function getCikarang($kodebarang)
    {
        $stok = $this->selectSum('stok')
            ->where('kodebarang', $kodebarang)
            ->whereIn('gudang', [1])
            ->get()
            ->getRow()
            ->stok;

        return max(0, (float) $stok);
    }

    public function getCirebon($kodebarang)
    {
        $stok = $this->selectSum('stok')
            ->where('kodebarang', $kodebarang)
            ->whereIn('gudang', [2])
            ->get()
            ->getRow()
            ->stok;

        return max(0, (float) $stok);
    }

    public function getStokCikarang($kodebarang)
    {
        return max(0, (float) $this->where('kodebarang', $kodebarang)
            ->where('gudang', 1)
            ->sum('stok'));
    }

    public function getStokCirebon($kodebarang)
    {
        return max(0, (float) $this->where('kodebarang', $kodebarang)
            ->where('gudang', 2)
            ->sum('stok'));
    }

    public function updateOrInsertBatch($data)
    {
        // Mempersiapkan data untuk update dan insert
        $updateData = [];
        $insertData = [];

        // Memisahkan data menjadi array untuk update dan insert
        foreach ($data as $row) {
            $existingData = $this->where(['kodebarang' => $row['kodebarang'], 'gudang' => $row['gudang']])->first();

            if ($existingData) {
                // Update nilai stok di gudang yang bersangkutan
                $existingData['stok'] += $row['stok'];

                $updateData[] = $existingData;
            } else {
                // Jika data belum ada, tambahkan ke data insert
                $insertData[] = $row;
            }
        }

        // Update data jika ada data yang perlu diupdate
        if (!empty($updateData)) {
            if (!$this->updateBatch($updateData, 'id')) {
                return false;
            }
        }

        // Insert data jika ada data yang perlu ditambahkan
        if (!empty($insertData)) {
            if (!$this->insertBatch($insertData)) {
                return false;
            }
        }

        return true;
    }

    public function getStokByKodebarangGudang($kodebarang, $gudang)
    {
        return $this->select('stok')
            ->where(['kodebarang' => $kodebarang, 'gudang' => $gudang])
            ->first();
    }

    public function getStokByGudang($kodebarang, $gudang)
    {
        $query = $this->where('kodebarang', $kodebarang)
            ->where('gudang', $gudang)
            ->first();

        if ($query != null) {
            return max(0, (float) $query['stok']);
        } else {
            return 0;
        }
    }

    public function getBarangIdByKodeGudang($kodebarang, $gudang)
    {
        $query = $this->where('kodebarang', $kodebarang)
            ->where('gudang', $gudang)
            ->first();

        if ($query != null) {
            return $query['id'];
        } else {
            return null;
        }
    }

    public function getHargaByKodeBarang($kodebarang)
    {
        // Cari data berdasarkan kodebarang
        $query = $this->where('kodebarang', $kodebarang)
            ->first();

        // Kembalikan harga jika data ditemukan, atau 0 jika tidak ditemukan
        return $query != null ? $query['harga'] : 0;
    }

    public function getTotalStokByKodeBarang($kodebarang)
    {
        $stok = $this->selectSum('stok')
            ->where('kodebarang', $kodebarang)
            ->get()
            ->getRow()
            ->stok;

        return max(0, (float) $stok);
    }

    public function getStokByIdBarang($idbarang)
    {
        $query = $this->where('idbarang', $idbarang)
            ->first()['stok'];

        return max(0, (float) ($query ?: 0));
    }

    public function cariStok($idbarang)
    {
        $query = $this->where('id', $idbarang)->first();

        if ($query != null) {
            return max(0, (float) $query['stok']);
        } else {
            return 0;
        }
    }

    public function cariIdStok($kodebarang, $gudang)
    {
        // Cari ID stok berdasarkan kombinasi kodebarang dan gudang
        $query = $this->select('id')
            ->where(['kodebarang' => $kodebarang, 'gudang' => $gudang])
            ->first();

        if ($query != null) {
            return $query['id'];
        } else {
            return null;
        }
    }

    public function cariMaterial($kodebarang, $gudang)
    {
        // Cari ID stok berdasarkan kombinasi kodebarang dan gudang
        $query = $this->select('material')
            ->where(['kodebarang' => $kodebarang, 'gudang' => $gudang])
            ->first();

        if ($query != null) {
            return $query['material'];
        } else {
            return null;
        }
    }

    public function cariStokKeluar($id)
    {
        // Cari stok berdasarkan ID
        $query = $this->find($id);

        if ($query != null) {
            return max(0, (float) $query['stok']);
        } else {
            return 0;
        }
    }
}
