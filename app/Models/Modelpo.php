<?php

namespace App\Models;

use CodeIgniter\Model;

class Modelpo extends Model
{
    protected $table            = 'po';
    protected $primaryKey       = 'nopo';
    protected $allowedFields    = [
        'nopo', 'tglpo', 'idpel', 'qty', 'hargapo', 'is_migrasi'
    ];

    public function noPo($tanggalSekarang)
    {
        return $this->table('po')->select('max(nopo) as nopo')->where('tglpo', $tanggalSekarang)->get();
    }

    public function cekPo($nopo)
    {
        return $this->table('po')->join('pelanggan', 'pelid=idpel')->getWhere([
            'sha1(nopo)' => $nopo
        ]);
    }

    public function laporanPerPeriode($tglawal, $tglakhir)
    {
        return $this->table('po')->where('tglpo >=', $tglawal)->where('tglpo <=', $tglakhir)->get();
    }

    function ambilTotalQty($nopo)
    {
        $query = $this->table('detail_po')->getWhere([
            'detnopo' => $nopo
        ]);

        $totalQty = 0;
        foreach ($query->getResultArray() as $r) :
            $totalQty += $r['detqty'];
        endforeach;

        return $totalQty;
    }

    function ambilTotalharga($nopo)
    {
        $query = $this->table('detail_po')->getWhere([
            'detnopo' => $nopo
        ]);

        $totalharga = 0;
        foreach ($query->getResultArray() as $r) :
            $totalharga += $r['detharga'];
        endforeach;

        return $totalharga;
    }

    public function updateOrInsertBatch($data)
    {
        // Mempersiapkan data untuk update dan insert
        $updateData = [];
        $insertData = [];

        // Memisahkan data menjadi array untuk update dan insert
        foreach ($data as $row) {
            $kodebrg = $row['kodebrg'];
            $gudang = $row['gudang'];
            // $stok = $row['stok'];

            // Cek apakah data sudah ada berdasarkan kodebrg dan gudang
            $existingData = $this->where(['kodebrg' => $kodebrg, 'gudang' => $gudang])->first();

            if ($existingData) {
                // Jika data sudah ada, tambahkan ke data update
                // $existingData['stok'] = $stok;
                $updateData[] = $existingData;
            } else {
                // Jika data belum ada, cek apakah ada data dengan kodebrg sama
                $existingDataWithkodebrg = $this->where('kodebrg', $kodebrg)->first();

                if ($existingDataWithkodebrg) {
                    // Jika ada data dengan kodebrg sama, cek apakah gudangnya berbeda
                    $existingDataWithkodebrgDanGudang = $this->where([
                        'kodebrg' => $kodebrg,
                        'gudang' => $gudang
                    ])->first();

                    if ($existingDataWithkodebrgDanGudang) {
                        // Jika ada data dengan kodebrg dan gudang yang sama, tambahkan ke data update
                        // $existingDataWithkodebrgDanGudang['stok'] = $stok;
                        $updateData[] = $existingDataWithkodebrgDanGudang;
                    } else {
                        // Jika ada data dengan kodebrg sama tapi gudang berbeda, tambahkan ke data insert
                        $insertData[] = [
                            'kodebrg' => $kodebrg,
                            'gudang' => $gudang,
                            // 'stok' => $stok,
                        ];
                    }
                } else {
                    // Jika data belum ada, tambahkan ke data insert
                    $insertData[] = [
                        'kodebrg' => $kodebrg,
                        'gudang' => $gudang,
                        // 'stok' => $stok,
                    ];
                }
            }
        }

        // Update data jika ada data yang perlu diupdate
        if (!empty($updateData)) {
            $this->updateBatch($updateData, 'id');
        }

        // Insert data jika ada data yang perlu ditambahkan
        if (!empty($insertData)) {
            $this->insertBatch($insertData);
        }
    }
}
