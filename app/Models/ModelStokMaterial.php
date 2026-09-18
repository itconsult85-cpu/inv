<?php

namespace App\Models;

use CodeIgniter\Model;

class ModelStokMaterial extends Model
{
    protected $table            = 'stokmaterial';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'materialid',
        'kodematerial',
        'namamaterial',
        'materialkatid',
        'materialsatid',
        'gudang',
        'stok'
    ];

    public function updateOrInsertBatch($data)
    {
        // Mempersiapkan data untuk update dan insert
        $updateData = [];
        $insertData = [];

        // Memisahkan data menjadi array untuk update dan insert
        foreach ($data as $row) {
            $existingData = $this->where(['materialid' => $row['materialid'], 'gudang' => $row['gudang']])->first();

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

    public function getStokById($id)
    {
        return $this->find($id)['stok'];
    }

    public function getStokByMaterialIdGudang($materialid, $gudang)
    {
        return $this->selectSum('stok')
            ->where('materialid', $materialid)
            ->where('gudang', $gudang)
            ->get()
            ->getRow()
            ->stok;
    }

    public function getIdByMaterialGudang($materialid, $gudang)
    {
        $query = $this->where('materialid', $materialid)
            ->where('gudang', $gudang)
            ->first();

        if ($query != null) {
            return $query['id'];
        } else {
            return null;
        }
    }
}
