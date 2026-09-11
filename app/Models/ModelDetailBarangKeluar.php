<?php

namespace App\Models;

use CodeIgniter\Model;

class ModelDetailBarangKeluar extends Model
{
    protected $table            = 'detail_barangkeluar';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'detfaktur', 'tgl', 'detpo',  'detbrgkode', 'namabarang', 'material', 'idbarang', 'detberat', 'detjml', 'detqtykeluar', 'gudang', 'detsubtotal', 'detidpel', 'dari_migrasi'
    ];

    public function getQtyTerkirimByKodeBarangAndPelanggan($detbrgkode, $pelanggan)
    {
        return $this->selectSum('detjml')
            ->where('detbrgkode', $detbrgkode)
            ->where('detidpel', $pelanggan)
            ->get()
            ->getRow()
            ->detjml;
    }

    public function getQtyTerkirimByKodeBarangAndPelangganAndPO($detbrgkode, $detpo)
    {
        return $this->selectSum('detjml')
            ->where('detbrgkode', $detbrgkode)
            ->where('detpo', $detpo)
            ->where('MONTH(tgl)', date('m'))
            ->where('YEAR(tgl)', date('Y'))
            ->get()
            ->getRow()
            ->detjml;
    }

    public function laporanPerPeriode($tglawal, $tglakhir)
    {
        return $this->table('detail_barangkeluar')->where('tgl >=', $tglawal)->where('tgl <=', $tglakhir)->get();
    }

    public function laporanPerPelanggan($pelangganId)
    {
        $builder = $this->db->table('detail_barangkeluar');
        $builder->select('detail_barangkeluar.*, pelanggan.pelnama');
        $builder->join('pelanggan', 'pelanggan.pelid = detail_barangkeluar.detidpel', 'left');
        $builder->where('detail_barangkeluar.detidpel', $pelangganId);
        return $builder->get();
    }

    public function tampilDataTemp($nofaktur)
    {
        return $this->table('detail_barangkeluar')
            ->select('detail_barangkeluar.id, detail_barangkeluar.detpo, detail_barangkeluar.detbrgkode, detail_po.detkurang, detail_po.detqty, detail_barangkeluar.namabarang, detail_barangkeluar.idbarang, detail_barangkeluar.detberat, detail_barangkeluar.detjml, gudang.gdgnama, gudang.gdgid, detail_barangkeluar.detsubtotal, stok.stok')
            ->join('gudang', 'gudang = gdgid')
            ->join('detail_po', 'detail_po.detkodebrg = detail_barangkeluar.detbrgkode AND detail_po.detnopo = detail_barangkeluar.detpo', 'left')
            ->join('po', 'po.nopo = detail_po.detnopo', 'left')
            ->join('stok', 'stok.kodebarang = detail_barangkeluar.detbrgkode AND stok.gudang = detail_barangkeluar.gudang')
            ->where('detfaktur', $nofaktur)
            ->get();
    }

    function ambilTotalBerat($nofaktur)
    {
        $query = $this->table('detail_barangkeluar')->getWhere([
            'detfaktur' => $nofaktur
        ]);

        $totalBerat = 0;
        foreach ($query->getResultArray() as $r) :
            $totalBerat += $r['detsubtotal'];
        endforeach;

        return $totalBerat;
    }

    function ambilTotalQty($nofaktur)
    {
        $query = $this->table('detail_barangkeluar')->getWhere([
            'detfaktur' => $nofaktur
        ]);

        $totalQty = 0;
        foreach ($query->getResultArray() as $r) :
            $totalQty += $r['detjml'];
        endforeach;

        return $totalQty;
    }

    protected $tableDetailBarangKeluar = 'detail_barangkeluar'; // Ganti dengan nama tabel detail_barangkeluar yang sesuai
    protected $tableDetailPo = 'detail_po'; // Ganti dengan nama tabel detail_po yang sesuai

    public function updateDetailPoFromDetailBarangKeluar()
    {
        // Query UPDATE untuk mengupdate data di tabel `detail_po` berdasarkan data di tabel `detail_barangkeluar`
        $db = db_connect();

        // Query UPDATE untuk mengupdate data di tabel detail_po berdasarkan data di tabel detail_barangkeluar
        $query = "
        UPDATE {$this->tableDetailPo}
        JOIN {$this->tableDetailBarangKeluar} ON {$this->tableDetailPo}.detnopo = {$this->tableDetailBarangKeluar}.detpo
        AND {$this->tableDetailPo}.idbarang = {$this->tableDetailBarangKeluar}.idbarang
        AND {$this->tableDetailPo}.detnopo = {$this->tableDetailBarangKeluar}.detpo
        SET {$this->tableDetailPo}.detkirim = {$this->tableDetailBarangKeluar}.detjml;
        ";

        $db->query($query); // Eksekusi query
    }

    public function getDataByBulan($year)
    {
        return $this->select('detbrgkode, MONTH(tgl) as bulan, SUM(detjml) as total')
            ->where('YEAR(tgl)', $year)
            ->groupBy('detbrgkode, bulan')
            ->get()
            ->getResultArray();
    }

    public function getComparisonData($year)
    {
        $db = \Config\Database::connect();

        $currentYearQuery = $db->table($this->table)
            ->select('MONTH(tgl) as bulan, detbrgkode, SUM(detjml) as total')
            ->where('YEAR(tgl)', $year)
            ->groupBy('bulan, detbrgkode')
            ->get();

        $previousYearQuery = $db->table($this->table)
            ->select('MONTH(tgl) as bulan, detbrgkode, SUM(detjml) as total')
            ->where('YEAR(tgl)', $year - 1)
            ->groupBy('bulan, detbrgkode')
            ->get();

        return [
            'currentYear' => $currentYearQuery->getResultArray(),
            'previousYear' => $previousYearQuery->getResultArray()
        ];
    }
}
