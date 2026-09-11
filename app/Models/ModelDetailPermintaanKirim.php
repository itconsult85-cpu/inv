<?php

namespace App\Models;

use CodeIgniter\Model;

class ModelDetailPermintaanKirim extends Model
{
    protected $table            = 'detail_permintaanbarangkirim';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'detpermintaan', 'detfaktur', 'defaktur', 'dettglkirim', 'detkodebrg', 'namabarang', 'jenis_item', 'idbarang', 'material', 'detberat', 'detqty', 'detsubtotal', 'gudang', 'iduser'
    ];

    private function fakturColumn(): string
    {
        return $this->db->fieldExists('detfaktur', $this->table)
            ? 'detfaktur'
            : 'defaktur';
    }

    private function detailQuery(string $faktur)
    {
        $fakturColumn = $this->fakturColumn();

        return $this->db->table($this->table)
            ->select('detail_permintaanbarangkirim.id, detail_permintaanbarangkirim.detkodebrg, COALESCE(detail_permintaanbarang.detkurang, 0) AS detkurang, detail_permintaanbarangkirim.namabarang, detail_permintaanbarangkirim.idbarang, detail_permintaanbarangkirim.detberat, detail_permintaanbarangkirim.detqty, gudang.gdgnama, detail_permintaanbarangkirim.gudang AS gdgid, detail_permintaanbarangkirim.detsubtotal, COALESCE(stok.stok, 0) AS stok')
            ->join('gudang', 'detail_permintaanbarangkirim.gudang = gudang.gdgid', 'left')
            ->join('detail_permintaanbarang', 'detail_permintaanbarang.detkodebrg = detail_permintaanbarangkirim.detkodebrg AND detail_permintaanbarang.detpermintaan = detail_permintaanbarangkirim.detpermintaan', 'left')
            ->join('stok', 'stok.kodebarang = detail_permintaanbarangkirim.detkodebrg AND stok.gudang = detail_permintaanbarangkirim.gudang', 'left')
            ->where("detail_permintaanbarangkirim.{$fakturColumn}", $faktur)
            ->get();
    }

    public function tampilDataTemp($nofaktur, $permintaan = null, $tanggal = null, $gudang = null, $totalQty = null)
    {
        $detail = $this->detailQuery($nofaktur);

        if ($detail->getNumRows() > 0 || empty($permintaan)) {
            return $detail;
        }

        $fakturColumn = $this->fakturColumn();
        $candidate = $this->db->table($this->table)
            ->select("{$fakturColumn} AS faktur")
            ->where('detpermintaan', $permintaan);

        if (!empty($tanggal)) {
            $candidate->where('dettglkirim', $tanggal);
        }

        if ($gudang !== null && $gudang !== '') {
            $candidate->where('gudang', $gudang);
        }

        $candidate->groupBy($fakturColumn);

        if ($totalQty !== null) {
            $candidate->having('SUM(detqty)', (int) $totalQty);
        }

        $matched = $candidate
            ->orderBy('MAX(id)', 'DESC')
            ->get()
            ->getRowArray();

        return !empty($matched['faktur'])
            ? $this->detailQuery($matched['faktur'])
            : $detail;
    }

    function ambilTotalBerat($nofaktur)
    {
        $fakturColumn = $this->fakturColumn();
        $query = $this->table('detail_permintaanbarangkirim')->getWhere([
            "detail_permintaanbarangkirim.{$fakturColumn}" => $nofaktur
        ]);

        $totalBerat = 0;
        foreach ($query->getResultArray() as $r) :
            $totalBerat += $r['detsubtotal'];
        endforeach;

        return $totalBerat;
    }

    function ambilTotalQty($nofaktur)
    {
        $fakturColumn = $this->fakturColumn();
        $query = $this->table('detail_permintaanbarangkirim')->getWhere([
            "detail_permintaanbarangkirim.{$fakturColumn}" => $nofaktur
        ]);

        $totalQty = 0;
        foreach ($query->getResultArray() as $r) :
            $totalQty += $r['detqty'];
        endforeach;

        return $totalQty;
    }

    // protected $tableDetailPermintaanKirim = 'detail_permintaanbarangkirim'; // Ganti dengan nama tabel detail_permintaanbarangkirim yang sesuai
    // protected $tableDetailPermintaanBarang = 'detail_permintaanbarang'; // Ganti dengan nama tabel detail_permintaanbarang yang sesuai

    // public function updateDetailFromDetailPermintaanKirim()
    // {
    //     // Query UPDATE untuk mengupdate data di tabel `detail_permintaanbarang` berdasarkan data di tabel `detail_permintaanbarangkirim`
    //     $db = db_connect();

    //     // Query UPDATE untuk mengupdate data di tabel detail_permintaanbarang berdasarkan data di tabel detail_permintaanbarangkirim
    //     $query = "
    //     UPDATE {$this->tableDetailPermintaanBarang}
    //     JOIN {$this->tableDetailPermintaanKirim} ON {$this->tableDetailPermintaanBarang}.detpermintaan = {$this->tableDetailPermintaanKirim}.detpermintaan
    //     AND {$this->tableDetailPermintaanBarang}.detkodebrg = {$this->tableDetailPermintaanKirim}.detkodebrg
    //     AND {$this->tableDetailPermintaanBarang}.detpermintaan = {$this->tableDetailPermintaanKirim}.detpermintaan
    //     SET {$this->tableDetailPermintaanBarang}.detkirim = {$this->tableDetailPermintaanKirim}.detqty;
    //     ";

    //     $db->query($query); // Eksekusi query
    // }
}
