<?php

namespace App\Models;

use CodeIgniter\Model;

class Modelbarangmasukcirebon extends Model
{
    protected $table            = 'stokcirebon';
    protected $primaryKey       = 'brgkode';
    protected $allowedFields    = [
        'brgkode', 'brgstok'
    ];

    public function tampildata()
    {
        return $this->table('stokcirebon')
            ->join('barang', 'barang.brgkode = stokcirebon.brgkode', 'inner')
            ->join('satuan', 'brgsatid=satid')
            ->join('material', 'matid = CAST(SUBSTRING_INDEX(barang.brgmat, ",", 1) AS UNSIGNED)', 'left', false);
    }

    public function copyFromBarang($brgkode)
    {
        $modelBarang = new ModelBarang();

        $barangData = $modelBarang->where('brgkode', $brgkode)->first();
        if ($barangData) {
            $existingData = $this->where('brgkode', $brgkode)->first();
            if (!$existingData) {
                $data = [
                    'brgkode' => $barangData['brgkode'],
                    'brgstok' => 0
                ];

                $this->insert($data);
                return $this->db->affectedRows();
            }
        }

        return 0;
    }
}
