<?php

namespace App\Models;

use CodeIgniter\Model;

class Modeldetailpo extends Model
{
    protected $table            = 'detail_po';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'detnopo', 'dettglpo', 'detkodebrg', 'namabarang', 'detberat', 'detqty', 'detkirim', 'detkirim_awal', 'detinvoice_awal', 'detkurang', 'detidpel', 'detsubtotal', 'detharga', 'material', 'gudang'
    ];

    public function getTotalQtyByKodeBarangAndPelanggan($detkodebrg, $pelanggan)
    {
        return $this->selectSum('detqty')
            ->where('detkodebrg', $detkodebrg)
            ->where('detidpel', $pelanggan)
            ->get()
            ->getRow()
            ->detqty;
    }


    public function getDataByBulanPo($yearpo)
    {
        return $this->select('detkodebrg, MONTH(dettglpo) as bulan, SUM(detqty) as total')
            ->where('YEAR(dettglpo)', $yearpo)
            ->groupBy('detkodebrg, bulan')
            ->get()
            ->getResultArray();
    }

    public function getComparisonDataPo($yearpo)
    {
        $db = \Config\Database::connect();

        $currentYearQuery = $db->table($this->table)
            ->select('MONTH(dettglpo) as bulan, detkodebrg, SUM(detqty) as total')
            ->where('YEAR(dettglpo)', $yearpo)
            ->groupBy('bulan, detkodebrg')
            ->get();

        $previousYearQuery = $db->table($this->table)
            ->select('MONTH(dettglpo) as bulan, detkodebrg, SUM(detqty) as total')
            ->where('YEAR(dettglpo)', $yearpo - 1)
            ->groupBy('bulan, detkodebrg')
            ->get();

        return [
            'currentYearPo' => $currentYearQuery->getResultArray(),
            'previousYearPo' => $previousYearQuery->getResultArray()
        ];
    }

    public function getTop5ProductsPerMonthPo($yearpo)
    {
        $db = \Config\Database::connect();

        $currentYearQuery = $db->table($this->table)
            ->select('detkodebrg, MONTH(dettglpo) as bulan, SUM(detqty) as total')
            ->where('YEAR(dettglpo)', $yearpo)
            ->groupBy('bulan, detkodebrg')
            ->orderBy('bulan, total', 'DESC')
            ->get();

        $previousYearQuery = $db->table($this->table)
            ->select('detkodebrg, MONTH(dettglpo) as bulan, SUM(detqty) as total')
            ->where('YEAR(dettglpo)', $yearpo - 1)
            ->groupBy('bulan, detkodebrg')
            ->orderBy('bulan, total', 'DESC')
            ->get();

        $currentYearResults = $currentYearQuery->getResultArray();
        $previousYearResults = $previousYearQuery->getResultArray();

        $top5ProductsCurrentYear = [];
        $top5ProductsPreviousYear = [];

        foreach ($currentYearResults as $row) {
            $bulan = $row['bulan'];
            if (!isset($top5ProductsCurrentYear[$bulan])) {
                $top5ProductsCurrentYear[$bulan] = [];
            }
            if (count($top5ProductsCurrentYear[$bulan]) < 5) {
                $top5ProductsCurrentYear[$bulan][] = $row;
            }
        }

        foreach ($previousYearResults as $row) {
            $bulan = $row['bulan'];
            if (!isset($top5ProductsPreviousYear[$bulan])) {
                $top5ProductsPreviousYear[$bulan] = [];
            }
            if (count($top5ProductsPreviousYear[$bulan]) < 5) {
                $top5ProductsPreviousYear[$bulan][] = $row;
            }
        }

        return [
            'currentYearPo' => $top5ProductsCurrentYear,
            'previousYearPo' => $top5ProductsPreviousYear
        ];
    }

    public function getTotalQtyByKodeBarangNopo($detnopo, $detkodebrg)
    {
        $query = $this->selectSum('detqty')
            ->where('detkodebrg', $detkodebrg)
            ->where('detnopo', $detnopo)
            ->get();
        $result = $query->getRow();

        return $result->detqty;
    }

    public function laporanPerPeriode($tglawal, $tglakhir)
    {
        return $this->table('detail_po')->where('dettglpo >=', $tglawal)->where('dettglpo <=', $tglakhir)->get();
    }

    public function updateDetailPoKurang()
    {
        // Query UPDATE untuk mengupdate data di tabel `detail_po` dengan kolom `detkurang`
        $query = "UPDATE detail_po SET detkurang = GREATEST(detqty - COALESCE(detkirim_awal, 0) - COALESCE(detkirim, 0), 0)";

        // Eksekusi query
        $this->db->query($query);
    }

    public function tampilDataTemp($nopo)
    {
        return $this->db->table('detail_po')
            ->select('detail_po.*, SUM(stok.stok) as total_stok')
            ->select('MAX(COALESCE(outstanding.outharga, 0) + COALESCE(outstanding.outkirim, 0)) as harga_outstanding', false)
            ->select('MAX(barang.harga) as harga_master', false)
            ->join('stok', 'stok.kodebarang = detail_po.detkodebrg', 'left')
            ->join('outstanding', 'outstanding.nopo = detail_po.detnopo AND outstanding.kodebrg = detail_po.detkodebrg', 'left')
            ->join('barang', 'barang.brgkode = detail_po.detkodebrg', 'left')
            ->where('detail_po.detnopo', $nopo)
            ->groupBy('detail_po.id') // Group by primary key detail_po
            ->get();
    }

    public function cekstok($nopo)
    {
        return $this->table('detail_po')
            ->select('detail_po.*, SUM(stok.stok) as total_stok')
            ->join('stok', 'stok.kodebarang=detail_po.detkodebrg')
            ->where('detnopo', $nopo)
            ->groupBy('detail_po.id')
            ->get();
    }

    function ambilTotalBerat($nopo)
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
}
