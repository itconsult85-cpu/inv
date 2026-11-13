<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ModelDetailBarangKeluar;
use App\Models\Modeldetailpo;
use App\Models\Modelstok;
use App\Models\Modelmaterial;
use App\Models\Modelkategori;
use App\Models\ModelPelanggan;
use Config\Database;
use Hermawan\DataTables\DataTable;
use Dompdf\Dompdf;

class Stok extends BaseController
{
    protected $db;
    protected $modelMaterial;
    protected $modelPelanggan;

    public function __construct()
    {
        $this->db = db_connect();
        $this->modelMaterial = new Modelmaterial();
        $this->modelPelanggan = new ModelPelanggan();
    }

    public function index()
    {
        $materials = $this->modelMaterial->findAll();
        $pelanggans = $this->modelPelanggan->whereNotIn('pelid', [1, 2])->findAll();
        $kategoris = (new Modelkategori())->orderBy('katnama', 'ASC')->findAll();
        return view('stok/viewdatastok', ['materials' => $materials, 'kategoris' => $kategoris, 'pelanggans' => $pelanggans]);
    }

    public function data()
    {
        if ($this->request->isAJAX()) {
            $db = Database::connect();
            $tglawal = trim((string) $this->request->getPost('tglawal'));
            $tglakhir = trim((string) $this->request->getPost('tglakhir'));
            $tanggalValid = static fn(string $tanggal): bool => (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal);
            $stokCikarangSql = 'SUM(CASE WHEN stok.gudang = 1 THEN GREATEST(COALESCE(stok.stok, 0), 0) ELSE 0 END)';
            $stokCirebonSql = 'SUM(CASE WHEN stok.gudang = 2 THEN GREATEST(COALESCE(stok.stok, 0), 0) ELSE 0 END)';
            $totalStokSql = '(' . $stokCikarangSql . ' + ' . $stokCirebonSql . ')';
            // PENTING: "sudah terkirim" itu bukan cuma SUM(detail_barangkeluar.detjml)
            // -- ada juga detkirim_awal (baseline pengiriman lama yang dulu ngga
            // pernah dicatat sebagai baris detail_barangkeluar tersendiri). Kalau
            // cuma detkirim doang yang dihitung, Sisa PO/Kekurangan Produksi jadi
            // kegedean banget krn dianggap belum kirim sama sekali padahal
            // sebenernya sebagian besar udah dikirim lewat detkirim_awal. Pakai
            // `detkurang` (detqty - detkirim_awal - detkirim) yang udah jadi
            // acuan resmi di seluruh sistem (lihat Modeloutstand::sinkronByPo()).
            $qtyTerkirimSql = 'COALESCE(totalterkirim, 0)';
            $poDateFilterSql = '';
            if ($tanggalValid($tglawal) && $tanggalValid($tglakhir)) {
                $tglawalSql = $db->escape($tglawal);
                $tglakhirSql = $db->escape($tglakhir);
                $poDateFilterSql = " AND dp.dettglpo >= {$tglawalSql} AND dp.dettglpo <= {$tglakhirSql}";
                $qtyTerkirimSql = "COALESCE((
                    SELECT SUM(dk.detjml)
                    FROM detail_barangkeluar dk
                    INNER JOIN barangkeluar bk ON bk.faktur = dk.detfaktur
                    WHERE dk.detbrgkode = stok.kodebarang
                      AND bk.tglfaktur >= {$tglawalSql}
                      AND bk.tglfaktur <= {$tglakhirSql}
                ), 0)";
            }
            $sisaPoSql = 'GREATEST(COALESCE(totalsisa, 0), 0)';
            $builder = $db->table('stok')
                ->select('stok.kodebarang, stok.material, stok.namabarang, stok.gudang, stok.idpel')
                ->select($stokCikarangSql . ' AS totmascik', false)
                ->select($stokCirebonSql . ' AS totmascir', false)
                ->select($totalStokSql . ' AS totalstok', false)
                ->join("(SELECT dp.detkodebrg,
                     SUM(CASE WHEN COALESCE(dp.detkurang, 0) > 0 THEN dp.detqty ELSE 0 END) as totalpo,
                     SUM(COALESCE(dp.detkirim_awal, 0) + COALESCE(dp.detkirim, 0)) as totalterkirim,
                     SUM(CASE WHEN COALESCE(dp.detkurang, 0) > 0 THEN dp.detkurang ELSE 0 END) as totalsisa
                 FROM detail_po dp
                 WHERE dp.detkodebrg NOT IN (SELECT kodebarang FROM stok WHERE idpel IN (1, 2))
                 AND dp.detidpel NOT IN (1, 2)
                 {$poDateFilterSql}
                 GROUP BY dp.detkodebrg) detail_po", 'stok.kodebarang = detail_po.detkodebrg', 'left')
                ->join('gudang', 'gudang.gdgid = stok.gudang', 'left')
                ->join('material', 'material.matid = stok.material', 'left')
                ->join('barang', 'barang.brgkode = stok.kodebarang', 'left')
                ->join('pelanggan', 'pelanggan.pelid = stok.idpel', 'left')
                ->groupBy('stok.kodebarang, stok.material, stok.idpel');

            $builder
                ->select($qtyTerkirimSql . ' AS kirim', false)
                ->select($sisaPoSql . ' AS kekurangan', false)
                ->select('CEIL(GREATEST(' . $sisaPoSql . ' - ' . $totalStokSql . ', 0)) AS kekuranganproduksi', false)
                ->select('CEIL(GREATEST(' . $totalStokSql . ' - ' . $sisaPoSql . ', 0)) AS kelebihanproduksi', false)
                ->select('CEIL(GREATEST(' . $sisaPoSql . ' - ' . $totalStokSql . ', 0)) AS kebutuhanprod', false);

            return DataTable::of($builder)
                ->addNumbering('nomor')
                ->setSearchableColumns(['kodebarang'])
                ->filter(function ($builder, $request) {

                    if ($request->material)
                        $builder->where('stok.material', $request->material);

                    if ($request->pelanggan) {
                        $builder->where('stok.idpel', $request->pelanggan);
                    }

                    if ($request->kategori) {
                        $builder->where('barang.brgkatid', $request->kategori);
                    }
                })
                ->format('totalstok', function ($value) {
                    return number_format($value, 0, ',', '.');
                })
                ->format('totmascik', function ($value) {
                    return number_format($value, 0, ',', '.');
                })
                ->format('totmascir', function ($value) {
                    return number_format($value, 0, ',', '.');
                })
                ->format('totalpo', function ($value) {
                    return number_format($value, 0, ',', '.');
                })
                ->format('kirim', function ($value) {
                    return number_format($value, 0, ',', '.');
                })
                ->format('kekurangan', function ($value) {
                    return number_format($value, 0, ',', '.');
                })
                ->format('kebutuhanprod', function ($value) {
                    $formattedValue = number_format(max(0, $value), 0, ",", ".");
                    if ($value > 0) {
                        return '<span style="color: red;">' . $formattedValue . '</span>';
                    }
                    return $formattedValue;
                })
                ->format('kekuranganproduksi', function ($value) {
                    $formattedValue = number_format(max(0, $value), 0, ",", ".");
                    if ($value > 0) {
                        return '<span style="color: red;">' . $formattedValue . '</span>';
                    }
                    return $formattedValue;
                })
                ->format('kelebihanproduksi', function ($value) {
                    $formattedValue = number_format(max(0, $value), 0, ",", ".");
                    if ($value > 0) {
                        return '<span style="color: green;">' . $formattedValue . '</span>';
                    }
                    return $formattedValue;
                })
                ->toJson(true);
        }
    }

    public function cetakLaporan()
    {
        if (!\App\Libraries\AccessControl::can('produk.masuk.print')) {
            return $this->response->setStatusCode(403)->setBody('Anda tidak memiliki akses untuk mencetak laporan stok produk.');
        }

        $material = $this->request->getPost('material');
        $kategori = $this->request->getPost('kategori');
        $pelanggan_id = $this->request->getPost('pelanggan');
        $bulan = trim((string) $this->request->getPost('bulan'));
        $tglawal = trim((string) $this->request->getPost('tglawal'));
        $tglakhir = trim((string) $this->request->getPost('tglakhir'));
        if (preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $bulan) === 1) {
            $tglawal = $bulan . '-01';
            $tglakhir = (new \DateTimeImmutable($tglawal))->modify('last day of this month')->format('Y-m-d');
        }
        $tanggalValid = static fn(string $tanggal): bool => (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal);

        $modelPelanggan = new ModelPelanggan();
        $pelanggan = $modelPelanggan->find($pelanggan_id);

        $nama_pelanggan = '-';

        if ($pelanggan) {
            $nama_pelanggan = $pelanggan['pelnama'];
        }

        $db = Database::connect();
        $stokCikarangSql = 'SUM(CASE WHEN stok.gudang = 1 THEN GREATEST(COALESCE(stok.stok, 0), 0) ELSE 0 END)';
        $stokCirebonSql = 'SUM(CASE WHEN stok.gudang = 2 THEN GREATEST(COALESCE(stok.stok, 0), 0) ELSE 0 END)';
        $totalStokSql = '(' . $stokCikarangSql . ' + ' . $stokCirebonSql . ')';
        $qtyTerkirimSql = 'COALESCE(detail_po.totalterkirim, 0)';
        $sisaPoDetailSql = 'GREATEST(COALESCE(dp.detqty, 0) - COALESCE(dp.detkirim_awal, 0) - COALESCE(dp.detkirim, 0), 0)';
        $poDateFilterSql = '';
        if ($tanggalValid($tglawal) && $tanggalValid($tglakhir)) {
            $tglawalSql = $db->escape($tglawal);
            $tglakhirSql = $db->escape($tglakhir);
            $poDateFilterSql = " AND dp.dettglpo >= {$tglawalSql} AND dp.dettglpo <= {$tglakhirSql}";
            $qtyTerkirimSql = "COALESCE((
                SELECT SUM(dk.detjml)
                FROM detail_barangkeluar dk
                INNER JOIN barangkeluar bk ON bk.faktur = dk.detfaktur
                WHERE dk.detbrgkode = stok.kodebarang
                  AND bk.tglfaktur >= {$tglawalSql}
                  AND bk.tglfaktur <= {$tglakhirSql}
            ), 0)";
        }

        $builder = $db->table('stok')
            ->select('stok.kodebarang, stok.material, stok.namabarang, stok.gudang, stok.idpel')
            ->select($stokCikarangSql . ' AS totmascik', false)
            ->select($stokCirebonSql . ' AS totmascir', false)
            ->select($totalStokSql . ' AS totalstok', false)
            ->select('COALESCE(detail_po.totalpo, 0) AS total_po')
            ->select($qtyTerkirimSql . ' AS qty_terkirim', false)
            ->select('COALESCE(detail_po.totalsisa, 0) AS total_sisa')
            ->join('barang', 'barang.brgkode = stok.kodebarang', 'left')
            ->join("(SELECT dp.detkodebrg,
                     SUM(CASE WHEN {$sisaPoDetailSql} > 0 THEN dp.detqty ELSE 0 END) as totalpo,
                     SUM(COALESCE(dp.detkirim_awal, 0) + COALESCE(dp.detkirim, 0)) as totalterkirim,
                     SUM({$sisaPoDetailSql}) as totalsisa
                 FROM detail_po dp
                 WHERE dp.detkodebrg NOT IN (SELECT kodebarang FROM stok WHERE idpel IN (1, 2))
                 AND dp.detidpel NOT IN (1, 2)
                 {$poDateFilterSql}
                 GROUP BY dp.detkodebrg) detail_po", 'stok.kodebarang = detail_po.detkodebrg', 'left')
            ->groupBy('stok.kodebarang, stok.material, stok.idpel, detail_po.totalpo, detail_po.totalterkirim, detail_po.totalsisa');

        if ($material) {
            $builder->where('stok.material', $material);
        }

        if ($pelanggan_id) {
            $builder->where('stok.idpel', $pelanggan_id);
        }

        if ($kategori) {
            $builder->where('barang.brgkatid', $kategori);
        }

        $data = $builder->get()->getResultArray();

        foreach ($data as &$row) {
            $total_stok = max(0, (float) $row['totalstok']);
            $row['totalstok'] = $total_stok;

            // Sisa PO = SUM(detkurang) langsung dari detail_po (sudah
            // memperhitungkan detkirim_awal, bukan cuma detail_barangkeluar).
            $row['kekurangan'] = max(0, (float) $row['total_sisa']);

            $kebutuhan_produksi = $row['kekurangan'] - $total_stok;
            $row['kebutuhan_produksi'] = $kebutuhan_produksi > 0 ? $kebutuhan_produksi : 0;
            $kelebihan_produksi = $total_stok - $row['kekurangan'];
            $row['kelebihan_produksi'] = $kelebihan_produksi > 0 ? $kelebihan_produksi : 0;

            $row['color_kekurangan'] = $row['kekurangan'] > 0 ? 'red' : 'black';

            $row['color_kebutuhan_produksi'] = $kebutuhan_produksi > 0 ? 'red' : 'black';
            $row['color_kelebihan_produksi'] = $kelebihan_produksi > 0 ? 'green' : 'black';
        }


        return view('laporan/cetakLaporanStok', [
            'data' => $data,
            'nama_pelanggan' => $nama_pelanggan,
            'tglawal' => $tglawal,
            'tglakhir' => $tglakhir,
        ]);

        // $dompdf = new Dompdf();
        // $dompdf->set_option('isRemoteEnabled', true);
        // $dompdf->loadHtml(view('laporan/stokReportPdf', ['data' => $data, 'nama_pelanggan' => $nama_pelanggan]));
        // $dompdf->setPaper('A4', 'landscape');
        // $dompdf->render();

        // Menyimpan file PDF
        // $dompdf->stream('Report_Stok.pdf', ['Attachment' => false]);
        // exit();
    }


    public function cetakLaporan1()
    {
        $material = $this->request->getPost('material');
        $kategori = $this->request->getPost('kategori');
        $pelanggan_id = $this->request->getPost('pelanggan');

        $modelPelanggan = new ModelPelanggan();
        $pelanggan = $modelPelanggan->find($pelanggan_id);

        $nama_pelanggan = '-';

        if ($pelanggan) {
            $nama_pelanggan = $pelanggan['pelnama'];
        }

        $db = Database::connect();
        $stokCikarangSql = 'SUM(CASE WHEN stok.gudang = 1 THEN GREATEST(COALESCE(stok.stok, 0), 0) ELSE 0 END)';
        $stokCirebonSql = 'SUM(CASE WHEN stok.gudang = 2 THEN GREATEST(COALESCE(stok.stok, 0), 0) ELSE 0 END)';
        $totalStokSql = '(' . $stokCikarangSql . ' + ' . $stokCirebonSql . ')';

        $builder = $db->table('stok')
            ->select('stok.kodebarang, stok.material, stok.namabarang, stok.gudang, stok.idpel')
            ->select($stokCikarangSql . ' AS totmascik', false)
            ->select($stokCirebonSql . ' AS totmascir', false)
            ->select($totalStokSql . ' AS totalstok', false)
            ->select('COALESCE(detail_po.totalpo, 0) AS total_po')
            ->select('COALESCE(detail_po.totalterkirim, 0) AS qty_terkirim')
            ->select('COALESCE(detail_po.totalsisa, 0) AS total_sisa')
            ->join('barang', 'barang.brgkode = stok.kodebarang', 'left')
            ->join('(SELECT detkodebrg,
                     SUM(detqty) as totalpo,
                     SUM(COALESCE(detkirim_awal, 0) + COALESCE(detkirim, 0)) as totalterkirim,
                     SUM(detkurang) as totalsisa
                 FROM detail_po
                 WHERE detkodebrg NOT IN (SELECT kodebarang FROM stok WHERE idpel IN (1, 2))
                 AND detidpel NOT IN (1, 2)
                 GROUP BY detkodebrg) detail_po', 'stok.kodebarang = detail_po.detkodebrg', 'left')
            ->groupBy('stok.kodebarang, stok.material, stok.idpel, detail_po.totalpo, detail_po.totalterkirim, detail_po.totalsisa');

        if ($material) {
            $builder->where('stok.material', $material);
        }

        if ($pelanggan_id) {
            $builder->where('stok.idpel', $pelanggan_id);
        }

        if ($kategori) {
            $builder->where('barang.brgkatid', $kategori);
        }

        $data = $builder->get()->getResultArray();

        foreach ($data as &$row) {
            $total_stok = max(0, (float) $row['totalstok']);
            $row['totalstok'] = $total_stok;

            // Sisa PO = SUM(detkurang) langsung dari detail_po (sudah
            // memperhitungkan detkirim_awal, bukan cuma detail_barangkeluar).
            $row['kekurangan'] = max(0, (float) $row['total_sisa']);

            $kebutuhan_produksi = $row['kekurangan'] - $total_stok;
            $row['kebutuhan_produksi'] = $kebutuhan_produksi > 0 ? $kebutuhan_produksi : 0;
            $kelebihan_produksi = $total_stok - $row['kekurangan'];
            $row['kelebihan_produksi'] = $kelebihan_produksi > 0 ? $kelebihan_produksi : 0;

            $row['color_kekurangan'] = $row['kekurangan'] > 0 ? 'red' : 'black';

            $row['color_kebutuhan_produksi'] = $kebutuhan_produksi > 0 ? 'red' : 'black';
            $row['color_kelebihan_produksi'] = $kelebihan_produksi > 0 ? 'green' : 'black';
        }

        return view('laporan/cetakLaporanStok', ['data' => $data, 'nama_pelanggan' => $nama_pelanggan]);
        // $html = view('laporan/cetakLaporanStok', ['data' => $data, 'nama_pelanggan' => $nama_pelanggan]);

        // $dompdf = new \Dompdf\Dompdf();

        // $dompdf->set_option('isRemoteEnabled', true);
        // $dompdf->set_option('isPhpEnabled', true);
        // $dompdf->set_option('isFontSubsettingEnabled', true);
        // $dompdf->set_option('defaultMediaType', 'all');
        // $dompdf->loadHtml($html);
        // $dompdf->setPaper('A4', 'potrait');
        // $dompdf->setPaper('A4', 'landscape');
        // $dompdf->render();
        // $dompdf->stream();

        // Menyimpan file PDF
        // $dompdf->stream('Report_Stok.pdf', ['Attachment' => false]);
        // exit();
    }

    public function cetak_stok_produk_periode()
    {
        $tombolCetak = $this->request->getPost('btnCetak');
        $tombolExport = $this->request->getPost('btnExport');
        $tglawal = $this->request->getPost('tglawal');
        $tglakhir = $this->request->getPost('tglakhir');

        $modelStokProduk = new ModelDetailBarangKeluar();
        $modelStok = new Modelstok();
        $modelDetailPo = new Modeldetailpo();

        $dataLaporan = $modelStokProduk->laporanPerPeriode($tglawal, $tglakhir);

        if (isset($tombolCetak)) {
            $data = [
                'datalaporan' => $dataLaporan,
                'tglawal' => $tglawal,
                'tglakhir' => $tglakhir,
                'modelStok' => $modelStok,
                'modelDetailPo' => $modelDetailPo,
            ];

            return view('laporan/cetakLaporanStok', $data);
        }
    }
}
