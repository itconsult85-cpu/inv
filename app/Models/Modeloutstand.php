<?php

namespace App\Models;

use CodeIgniter\Model;

class Modeloutstand extends Model
{
    protected $table            = 'outstanding';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'nopo', 'kodebrg', 'idbarang', 'tgl', 'qty', 'terkirim', 'tglkirim', 'kekurangan', 'keterangan', 'idpel', 'jasaid', 'outharga'
    ];

    public function updateDetailOutstandingKurang()
    {
        $query = "UPDATE outstanding 
              JOIN (
                  SELECT kodebarang, harga as harga
                  FROM stok
                  GROUP BY kodebarang
              ) stok ON outstanding.kodebrg = stok.kodebarang
              SET 
                  outstanding.kekurangan = GREATEST(outstanding.qty - outstanding.terkirim, 0),
                  outstanding.outkirim = (outstanding.terkirim * stok.harga),
                  outstanding.outharga = GREATEST(outstanding.qty - outstanding.terkirim, 0) * stok.harga";

        // Eksekusi query
        $this->db->query($query);
    }

    public function sinkronByPo(string $noPo): void
    {
        $noPo = trim($noPo);
        if ($noPo === '') {
            return;
        }

        $this->db->query(
            "UPDATE detail_po dp
             LEFT JOIN (
                 SELECT detpo, detbrgkode, SUM(detjml) AS total_kirim
                 FROM detail_barangkeluar
                 WHERE detpo = ?
                 GROUP BY detpo, detbrgkode
             ) kirim ON kirim.detpo = dp.detnopo AND kirim.detbrgkode = dp.detkodebrg
             SET dp.detkirim = COALESCE(kirim.total_kirim, 0),
                 dp.detkurang = GREATEST(dp.detqty - COALESCE(dp.detkirim_awal, 0) - COALESCE(kirim.total_kirim, 0), 0)
             WHERE dp.detnopo = ?",
            [$noPo, $noPo]
        );

        $this->db->query(
            "INSERT INTO outstanding
                (nopo, kodebrg, idbarang, tgl, qty, terkirim, kekurangan, idpel, outkirim, outharga)
             SELECT
                dp.detnopo,
                dp.detkodebrg,
                COALESCE(stok.idbarang, 0),
                COALESCE(dp.dettglpo, po.tglpo),
                dp.detqty,
                COALESCE(dp.detkirim_awal, 0) + COALESCE(dp.detkirim, 0),
                dp.detkurang,
                dp.detidpel,
                (COALESCE(dp.detkirim_awal, 0) + COALESCE(dp.detkirim, 0)) * COALESCE(stok.harga, 0),
                dp.detkurang * COALESCE(stok.harga, 0)
             FROM detail_po dp
             LEFT JOIN po ON po.nopo = dp.detnopo
             LEFT JOIN (
                 SELECT kodebarang, MIN(id) AS idbarang, MAX(harga) AS harga
                 FROM stok
                 GROUP BY kodebarang
             ) stok ON stok.kodebarang = dp.detkodebrg
             LEFT JOIN outstanding o ON o.nopo = dp.detnopo AND o.kodebrg = dp.detkodebrg
             WHERE dp.detnopo = ? AND o.id IS NULL",
            [$noPo]
        );

        $this->db->query(
            "UPDATE outstanding o
             JOIN detail_po dp ON dp.detnopo = o.nopo AND dp.detkodebrg = o.kodebrg
             LEFT JOIN po ON po.nopo = dp.detnopo
             LEFT JOIN (
                 SELECT kodebarang, MAX(harga) AS harga
                 FROM stok
                 GROUP BY kodebarang
             ) stok ON stok.kodebarang = o.kodebrg
             SET o.tgl = COALESCE(dp.dettglpo, po.tglpo, o.tgl),
                 o.qty = dp.detqty,
                 o.terkirim = COALESCE(dp.detkirim_awal, 0) + COALESCE(dp.detkirim, 0),
                 o.kekurangan = dp.detkurang,
                 o.idpel = dp.detidpel,
                 o.outkirim = (COALESCE(dp.detkirim_awal, 0) + COALESCE(dp.detkirim, 0)) * COALESCE(stok.harga, 0),
                 o.outharga = dp.detkurang * COALESCE(stok.harga, 0)
             WHERE o.nopo = ?",
            [$noPo]
        );

        $this->db->query(
            "UPDATE po p
             LEFT JOIN (
                 SELECT detnopo, SUM(detqty) AS total_qty, SUM(detharga) AS total_harga
                 FROM detail_po
                 WHERE detnopo = ?
                 GROUP BY detnopo
             ) dp ON dp.detnopo = p.nopo
             SET p.qty = COALESCE(dp.total_qty, 0),
                 p.hargapo = COALESCE(dp.total_harga, 0)
             WHERE p.nopo = ?",
            [$noPo, $noPo]
        );
    }

    public function sinkronByPoList(array $noPoList): void
    {
        foreach (array_unique(array_filter(array_map(static fn($noPo) => trim((string) $noPo), $noPoList))) as $noPo) {
            $this->sinkronByPo($noPo);
        }
    }
}
