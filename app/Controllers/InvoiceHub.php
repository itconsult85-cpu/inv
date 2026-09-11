<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ModelPelanggan;

/**
 * Pintu masuk tunggal untuk Invoice In, Invoice Out, dan Reporting.
 * Berisi data harga jual & margin yang sensitif, jadi aksesnya diatur lewat
 * RBAC dinamis (App\Libraries\AccessControl, feature order.invoice_hub) --
 * dicek per-URL oleh FilterAdmin/FilterPimpinan/FilterKasir/FilterGudang
 * (lihat app/Config/Filters.php) lewat FilterUrlHelper::urlDiizinkan().
 * Sejak RBAC dinamis, menu sidebarnya bisa dimunculkan (section TRANSAKSI
 * ORDER) tapi tetap cuma tampil untuk user yang diberi izin lewat Hak Akses
 * User; kartu Invoice Out/Invoice In/Reporting di invoicehub/index.php juga
 * disembunyikan kalau user tidak punya izin action tersebut.
 */
class InvoiceHub extends BaseController
{
    public function __construct()
    {
        $this->ensureInvoiceOutMaterialSnapshotColumns();
    }

    public function index()
    {
        return view('invoicehub/index');
    }

    /**
     * Tiap tab (Margin/Piutang/Hutang/Cashflow/Omzet per Surat Jalan) punya
     * filter periode+pelanggan sendiri-sendiri dan dimuat independen lewat
     * AJAX (lihat reportingMargin() dkk di bawah) -- halaman ini cuma
     * nge-render kerangkanya + opsi dropdown pelanggan.
     */
    public function reporting()
    {
        return view('invoicehub/reporting', [
            'pelanggans' => $this->daftarPelanggan(),
            'reportBulananAwal' => $this->ambilDataReportBulanan($this->periodeUntukTanggal(date('Y-m-d'))),
        ]);
    }

    private function daftarPelanggan(): array
    {
        return (new ModelPelanggan())
            ->whereNotIn('pelid', [1, 2])
            ->orderBy('pelnama', 'ASC')
            ->findAll();
    }

    /**
     * @return array{0: ?string, 1: ?string, 2: ?int, 3: bool, 4: string}
     */
    private function ambilFilterReporting(): array
    {
        $tglawal = $this->request->getPost('tglawal') ?? $this->request->getGet('tglawal');
        $tglakhir = $this->request->getPost('tglakhir') ?? $this->request->getGet('tglakhir');
        $pelangganRaw = $this->request->getPost('pelanggan') ?? $this->request->getGet('pelanggan');
        $pelanggan = $pelangganRaw !== null && $pelangganRaw !== '' ? (int) $pelangganRaw : null;
        $periodeDipilih = (bool) ($tglawal && $tglakhir);

        $namaPelanggan = '';
        if ($pelanggan !== null) {
            foreach ($this->daftarPelanggan() as $pel) {
                if ((int) $pel['pelid'] === $pelanggan) {
                    $namaPelanggan = $pel['pelnama'];
                    break;
                }
            }
        }

        return [$tglawal, $tglakhir, $pelanggan, $periodeDipilih, $namaPelanggan];
    }

    public function reportingMargin()
    {
        [$tglawal, $tglakhir, $pelanggan, $periodeDipilih, $namaPelanggan] = $this->ambilFilterReporting();
        $rows = $periodeDipilih ? $this->ambilDataReporting($tglawal, $tglakhir, $pelanggan) : [];

        return $this->response->setJSON([
            'data' => view('invoicehub/tab_margin', [
                'rows' => $rows,
                'periodeDipilih' => $periodeDipilih,
                'tglawal' => $tglawal,
                'tglakhir' => $tglakhir,
                'namaPelanggan' => $namaPelanggan,
            ]),
        ]);
    }

    public function reportingPiutang()
    {
        [$tglawal, $tglakhir, $pelanggan, $periodeDipilih, $namaPelanggan] = $this->ambilFilterReporting();
        $piutangRows = $periodeDipilih ? $this->ambilDataPiutang($tglawal, $tglakhir, $pelanggan) : [];

        return $this->response->setJSON([
            'data' => view('invoicehub/tab_piutang', [
                'piutangRows' => $piutangRows,
                'periodeDipilih' => $periodeDipilih,
                'tglawal' => $tglawal,
                'tglakhir' => $tglakhir,
                'namaPelanggan' => $namaPelanggan,
            ]),
        ]);
    }

    public function reportingHutang()
    {
        [$tglawal, $tglakhir, , $periodeDipilih, $namaPelanggan] = $this->ambilFilterReporting();
        $hutangRows = $periodeDipilih ? $this->ambilDataHutang($tglawal, $tglakhir) : [];

        return $this->response->setJSON([
            'data' => view('invoicehub/tab_hutang', [
                'hutangRows' => $hutangRows,
                'periodeDipilih' => $periodeDipilih,
                'tglawal' => $tglawal,
                'tglakhir' => $tglakhir,
                'namaPelanggan' => $namaPelanggan,
            ]),
        ]);
    }

    public function reportingCashflow()
    {
        [$tglawal, $tglakhir, $pelanggan, $periodeDipilih, $namaPelanggan] = $this->ambilFilterReporting();
        $cashflowRows = $periodeDipilih ? $this->ambilDataCashflow($tglawal, $tglakhir, $pelanggan) : [];

        return $this->response->setJSON([
            'data' => view('invoicehub/tab_cashflow', [
                'cashflowRows' => $cashflowRows,
                'periodeDipilih' => $periodeDipilih,
                'tglawal' => $tglawal,
                'tglakhir' => $tglakhir,
                'namaPelanggan' => $namaPelanggan,
            ]),
        ]);
    }

    /**
     * Titik acuan rantai siklus 4-mingguan (Senin) -- persis "Minggu Pertama"
     * di contoh laporan pertama dari user (Report Juli 2026). Semua periode
     * lain dihitung maju/mundur kelipatan 28 hari dari sini, jadi rantainya
     * dijamin gapless (nggak akan pernah ada tanggal yang ke-skip).
     */
    private const REPORT_BULANAN_ANCHOR = '2026-06-29';

    /**
     * Nomor periode (n) yang memuat tanggal tertentu, relatif ke anchor.
     * n=0 = periode anchor, n=1 = 28 hari setelahnya, n=-1 = 28 hari sebelumnya, dst.
     */
    private function periodeUntukTanggal(string $tanggal): int
    {
        $diffHari = (int) floor((strtotime($tanggal) - strtotime(self::REPORT_BULANAN_ANCHOR)) / 86400);
        return (int) floor($diffHari / 28);
    }

    /**
     * Report Bulanan (kartu mingguan Senin-Minggu + breakdown kategori per
     * Tahap) -- diminta user sebagai pengganti tampilan tabel per-surat-jalan.
     * Awalnya filter Bulan+Tahun, tapi itu selalu nyisain satu blok 28-hari
     * yang ke-skip di setiap pasangan bulan (28 hari nggak pernah pas sama
     * panjang bulan kalender 30/31 hari) -- makanya diganti navigasi
     * Periode Sebelumnya/Berikutnya yang jalan di rantai 28-harian yang sama,
     * dijamin nggak ada tanggal yang hilang. Tanggal acuannya tanggal Surat
     * Jalan (barangkeluar.tglfaktur), bukan tanggal invoice.
     */
    public function reportingOmzetSuratJalan()
    {
        $periodeRaw = $this->request->getPost('periode') ?? $this->request->getGet('periode');
        $n = ($periodeRaw !== null && $periodeRaw !== '') ? (int) $periodeRaw : $this->periodeUntukTanggal(date('Y-m-d'));

        $pelangganRaw = $this->request->getPost('pelanggan') ?? $this->request->getGet('pelanggan');
        $pelanggan = $pelangganRaw !== null && $pelangganRaw !== '' ? (int) $pelangganRaw : null;

        $namaPelanggan = '';
        if ($pelanggan !== null) {
            foreach ($this->daftarPelanggan() as $pel) {
                if ((int) $pel['pelid'] === $pelanggan) {
                    $namaPelanggan = $pel['pelnama'];
                    break;
                }
            }
        }

        $report = $this->ambilDataReportBulanan($n, $pelanggan);

        return $this->response->setJSON([
            'data' => view('invoicehub/tab_omzet_surat_jalan', [
                'periodeDipilih' => true,
                'namaPelanggan' => $namaPelanggan,
                'report' => $report,
            ]),
        ]);
    }

    /**
     * Qty, berat, dan harga jual diambil dari Invoice Out aktif.
     * Modal material dihitung dari master kebutuhan material per pcs
     * (berat_material) dikali harga material terakhir dari Invoice In
     * pembelian material. Invoice In dari PO Titip Proses tidak dipakai
     * sebagai harga material.
     * Modal jasa belum dihitung otomatis karena mapping jasa per produk/PO
     * masih ditahan. Untuk sementara kolom jasa tetap bisa diisi manual.
     */
    private function ambilDataReporting(string $tglawal, string $tglakhir, ?int $pelanggan = null): array
    {
        $db = db_connect();

        $builder = $db->table('invoice_out_detail iod')
            ->select("
                iod.product_code,
                iod.product_name,
                MAX(iod.unit) AS unit,
                SUM(iod.qty) AS qty_pcs,
                SUM(iod.qty * COALESCE(b.berat, 0)) AS qty_kg,
                AVG(iod.unit_price) AS harga_jual,
                MAX(b.berat) AS berat_per_pcs,
                SUM(CASE WHEN iod.material_cost_snapshot IS NOT NULL THEN iod.qty * iod.material_cost_snapshot ELSE 0 END)
                    / NULLIF(SUM(CASE WHEN iod.material_cost_snapshot IS NOT NULL THEN iod.qty ELSE 0 END), 0) AS material_cost_snapshot,
                MIN(CASE WHEN iod.material_cost_snapshot IS NULL THEN 0 ELSE COALESCE(iod.material_cost_complete_snapshot, 0) END) AS material_snapshot_lengkap,
                GROUP_CONCAT(DISTINCT iod.material_cost_detail_snapshot SEPARATOR '; ') AS material_snapshot_detail
            ", false)
            ->join('invoice_out io', 'io.id = iod.invoice_id')
            ->join('berat b', 'b.kodeprd = iod.product_code', 'left')
            ->where('io.status', 'AKTIF')
            ->where('io.invoice_date >=', $tglawal)
            ->where('io.invoice_date <=', $tglakhir)
            ->groupBy('iod.product_code, iod.product_name')
            ->orderBy('iod.product_name', 'ASC');

        if ($pelanggan !== null) {
            $builder->where('io.customer_id', $pelanggan);
        }

        $rows = $builder->get()->getResultArray();
        foreach ($rows as &$row) {
            // Snapshot cuma dipercaya kalau lengkap (material_snapshot_lengkap=1).
            // Snapshot yang ditandai belum lengkap (dibuat saat Invoice In
            // harga materialnya belum ada) beku selamanya di 0 kalau ngga
            // dihitung ulang -- jadi tetap coba ambil harga acuan terbaru
            // dari Invoice In yang mungkin sudah ada sekarang.
            $snapshotLengkap = (int) ($row['material_snapshot_lengkap'] ?? 0) === 1;
            if ($row['material_cost_snapshot'] !== null && $snapshotLengkap) {
                $row['harga_material_per_pcs'] = (float) $row['material_cost_snapshot'];
                $row['material_detail'] = ($row['material_snapshot_detail'] ?: 'Harga modal dikunci saat Invoice Out dibuat.');
                $row['material_lengkap'] = true;
                $row['material_sumber'] = 'Harga Modal Terkunci';
            } else {
                $material = $this->hargaMaterialPerPcs((string) $row['product_code']);
                $row['harga_material_per_pcs'] = $material['total'];
                $row['material_detail'] = $material['detail'];
                $row['material_lengkap'] = $material['lengkap'];
                $row['material_sumber'] = 'Harga acuan terakhir';

                // Begitu berhasil ketemu harga LENGKAP (semua material di
                // resep produk ini ada harganya), langsung kunci permanen
                // ke baris invoice_out_detail yang masih belum lengkap --
                // supaya ngga perlu dihitung ulang lagi tiap Reporting
                // dibuka. Baris yang sudah lengkap/terkunci sebelumnya
                // (mungkin dengan harga lama) tidak disentuh.
                if ($material['lengkap']) {
                    $db->table('invoice_out_detail')
                        ->where('product_code', $row['product_code'])
                        ->groupStart()
                            ->where('material_cost_snapshot', null)
                            ->orWhere('material_cost_complete_snapshot', 0)
                        ->groupEnd()
                        ->update([
                            'material_cost_snapshot' => $material['total'],
                            'material_cost_detail_snapshot' => $material['detail'],
                            'material_cost_complete_snapshot' => 1,
                        ]);
                    $row['material_sumber'] = 'Harga Modal Terkunci';
                }
            }
            $row['harga_jasa_per_pcs'] = 0.0;
            $row['jasa_lengkap'] = false;
            $row['jasa_detail'] = 'Jasa belum otomatis, isi manual kalau ada.';
        }
        unset($row);

        return $rows;
    }

    private function hargaMaterialPerPcs(string $kodeProduk): array
    {
        $materials = db_connect()->table('berat_material bm')
            ->select('bm.berat, m.matid, m.matkode, m.matnama')
            ->join('material m', 'm.matid = bm.matid')
            ->where('bm.kodeprd', $kodeProduk)
            ->where('bm.berat >', 0)
            ->get()->getResultArray();

        $total = 0.0;
        $detail = [];
        $lengkap = true;

        foreach ($materials as $material) {
            $harga = $this->hargaMaterialTerakhir((string) $material['matkode'], (string) $material['matid']);
            if ($harga === null) {
                $lengkap = false;
                $detail[] = $material['matnama'] . ': belum ada harga Invoice In';
                continue;
            }

            $subtotal = (float) $material['berat'] * $harga;
            $total += $subtotal;
            $detail[] = $material['matnama'] . ': ' . number_format((float) $material['berat'], 4, ',', '.') . ' x Rp ' . number_format($harga, 0, ',', '.');
        }

        if (!$materials) {
            $lengkap = false;
            $detail[] = 'Belum ada master kebutuhan material';
        }

        return [
            'total' => $total,
            'detail' => implode('; ', $detail),
            'lengkap' => $lengkap,
        ];
    }

    private function hargaMaterialTerakhir(string $kodeMaterial, string $idMaterial): ?float
    {
        $row = db_connect()->table('invoice_in_detail iid')
            ->select('iid.unit_price')
            ->join('invoice_in ii', 'ii.id = iid.invoice_id')
            ->join('po_keluar pk', "ii.source_type = 'po_keluar' AND pk.no_po = ii.source_no", 'left')
            ->where('ii.status !=', 'DIBATALKAN')
            ->groupStart()
                ->where('ii.source_type', 'material')
                ->orGroupStart()
                    ->where('ii.source_type', 'po_keluar')
                    ->where('pk.jenis_transaksi', 'Beli')
                ->groupEnd()
            ->groupEnd()
            ->groupStart()
                ->where('iid.item_code', $kodeMaterial)
                ->orWhere('iid.item_code', $idMaterial)
            ->groupEnd()
            ->where('iid.unit_price >', 0)
            ->orderBy('ii.invoice_date', 'DESC')
            ->orderBy('iid.id', 'DESC')
            ->get()->getRowArray();

        return $row ? (float) $row['unit_price'] : null;
    }

    private function ensureInvoiceOutMaterialSnapshotColumns(): void
    {
        $db = db_connect();
        if (!$db->tableExists('invoice_out_detail')) {
            return;
        }

        $forge = \Config\Database::forge();
        $columns = [
            'material_cost_snapshot' => ['type' => 'DECIMAL', 'constraint' => '18,2', 'null' => true, 'after' => 'amount'],
            'material_cost_detail_snapshot' => ['type' => 'TEXT', 'null' => true, 'after' => 'material_cost_snapshot'],
            'material_cost_complete_snapshot' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'material_cost_detail_snapshot'],
        ];

        foreach ($columns as $name => $definition) {
            if (!$db->fieldExists($name, 'invoice_out_detail')) {
                $forge->addColumn('invoice_out_detail', [$name => $definition]);
            }
        }
    }

    private function ambilDataPiutang(string $tglawal, string $tglakhir, ?int $pelanggan = null): array
    {
        $builder = db_connect()->table('invoice_out')
            ->select('invoice_no, invoice_date, po_no, customer_name, grand_total, paid_total, status_bayar, tanggal_lunas')
            ->where('status', 'AKTIF')
            ->where('invoice_date >=', $tglawal)
            ->where('invoice_date <=', $tglakhir)
            ->where('grand_total > COALESCE(paid_total, 0)', null, false)
            ->orderBy('invoice_date', 'ASC')
            ->orderBy('id', 'ASC');

        if ($pelanggan !== null) {
            $builder->where('customer_id', $pelanggan);
        }

        return array_map(static function (array $row): array {
            $row['sisa'] = max((float) $row['grand_total'] - (float) ($row['paid_total'] ?? 0), 0);
            return $row;
        }, $builder->get()->getResultArray());
    }

    private function ambilDataHutang(string $tglawal, string $tglakhir): array
    {
        return array_map(static function (array $row): array {
            $row['sisa'] = (string) ($row['status'] ?? '') === 'Lunas' ? 0.0 : (float) $row['grand_total'];
            return $row;
        }, db_connect()->table('invoice_in')
            ->select('invoice_no, invoice_date, source_type, source_no, supplier_name, grand_total, status, tanggal_lunas')
            ->where('status !=', 'DIBATALKAN')
            ->where('invoice_date >=', $tglawal)
            ->where('invoice_date <=', $tglakhir)
            ->where('status !=', 'Lunas')
            ->orderBy('invoice_date', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()->getResultArray());
    }

    private function ambilDataCashflow(string $tglawal, string $tglakhir, ?int $pelanggan = null): array
    {
        $db = db_connect();
        $rows = [];

        if ($db->tableExists('invoice_out_payment')) {
            $builder = $db->table('invoice_out_payment')
                ->select("payment_date AS tanggal, payment_no AS nomor, customer_name AS pihak, amount AS nominal, note AS keterangan, 'Masuk' AS jenis", false)
                ->where('payment_date >=', $tglawal)
                ->where('payment_date <=', $tglakhir);

            if ($pelanggan !== null) {
                $builder->where('customer_id', $pelanggan);
            }

            $rows = array_merge($rows, $builder->get()->getResultArray());
        }

        $invoiceInRows = $db->table('invoice_in')
            ->select("DATE(tanggal_lunas) AS tanggal, invoice_no AS nomor, supplier_name AS pihak, grand_total AS nominal, source_no AS keterangan, 'Keluar' AS jenis", false)
            ->where('status', 'Lunas')
            ->where('tanggal_lunas IS NOT NULL', null, false)
            ->where('DATE(tanggal_lunas) >=', $tglawal)
            ->where('DATE(tanggal_lunas) <=', $tglakhir)
            ->get()->getResultArray();

        $rows = array_merge($rows, $invoiceInRows);
        usort($rows, static function (array $a, array $b): int {
            return strcmp((string) ($a['tanggal'] ?? ''), (string) ($b['tanggal'] ?? ''));
        });

        return $rows;
    }

    private function namaBulan(int $bulan): string
    {
        $nama = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        return $nama[$bulan] ?? '';
    }

    /**
     * Nilai per hari dihitung dari ITEM DI SURAT JALAN (detail_barangkeluar),
     * bukan dari Invoice Out -- sengaja begitu karena banyak surat jalan lama
     * yang udah ditagih di luar aplikasi ini (belum tentu ada Invoice Out-nya),
     * jadi laporan ini harus tetap ngitung semua pengiriman yang beneran
     * kejadian. Nilai per baris = qty di surat jalan x harga jual produk
     * SEKARANG (Master Data Produk) -- bukan harga historis, karena
     * detail_barangkeluar cuma nyimpen qty & berat, nggak nyimpen harga.
     *
     * Periode = blok 28 hari (Senin-Minggu x4) ke-$n di rantai gapless yang
     * diacu dari REPORT_BULANAN_ANCHOR (lihat periodeUntukTanggal()) -- BUKAN
     * dihitung ulang independen per bulan (itu yang dulu bikin satu blok
     * 28-hari ke-skip total kapanpun panjang siklusnya nggak pas sama
     * panjang bulan kalender, misal Mei->Juni 2026). "Bulan" buat judul
     * ditentukan dari bulan yang paling banyak "memiliki" hari dalam blok
     * ini (mayoritas), karena satu blok 28 hari kadang nyerempet 2 bulan
     * kalender sekaligus. Per Kategori per Tahap pakai rentang minggu yang
     * SAMA PERSIS kayak kartu mingguan (termasuk hari yang nyerempet bulan
     * lain) -- sengaja diseragamkan supaya totalnya selalu pas sama dengan
     * TOTAL KESELURUHAN di atasnya, nggak ada lagi selisih karena klip bulan.
     */
    private function ambilDataReportBulanan(int $n, ?int $pelanggan = null): array
    {
        $db = db_connect();

        $startMonday = date('Y-m-d', strtotime(self::REPORT_BULANAN_ANCHOR . sprintf(' %+d days', $n * 28)));
        $endDate = date('Y-m-d', strtotime($startMonday . ' +27 days'));

        $jumlahHariPerBulan = [];
        for ($d = 0; $d < 28; $d++) {
            $tgl = strtotime($startMonday . " +{$d} days");
            $kunciBulan = date('Y-m', $tgl);
            $jumlahHariPerBulan[$kunciBulan] = ($jumlahHariPerBulan[$kunciBulan] ?? 0) + 1;
        }
        arsort($jumlahHariPerBulan);
        $bulanMayoritas = array_key_first($jumlahHariPerBulan);
        $firstOfMonth = $bulanMayoritas . '-01';

        $namaHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $namaMinggu = ['Minggu Pertama', 'Minggu Kedua', 'Minggu Ketiga', 'Minggu Keempat'];

        $dailyBuilder = $db->table('detail_barangkeluar dbk')
            ->select('bk.tglfaktur AS tanggal, SUM(dbk.detjml * b.harga) AS total', false)
            ->join('barangkeluar bk', 'bk.faktur = dbk.detfaktur')
            ->join('barang b', 'b.brgkode = dbk.detbrgkode')
            ->where('bk.tglfaktur >=', $startMonday)
            ->where('bk.tglfaktur <=', $endDate)
            ->groupBy('bk.tglfaktur');
        if ($pelanggan !== null) {
            $dailyBuilder->where('dbk.detidpel', $pelanggan);
        }

        $dailyTotals = [];
        foreach ($dailyBuilder->get()->getResultArray() as $row) {
            $dailyTotals[$row['tanggal']] = (float) $row['total'];
        }

        $weeks = [];
        $cursor = $startMonday;
        for ($w = 0; $w < 4; $w++) {
            $days = [];
            $weekTotal = 0.0;
            for ($d = 0; $d < 7; $d++) {
                $tgl = date('Y-m-d', strtotime($cursor . " +{$d} days"));
                $namaHariIni = $namaHari[((int) date('N', strtotime($tgl))) - 1];
                $total = $dailyTotals[$tgl] ?? 0.0;
                $days[] = ['tanggal' => $tgl, 'nama_hari' => $namaHariIni, 'total' => $total];
                $weekTotal += $total;
            }
            $weeks[] = ['label' => $namaMinggu[$w], 'days' => $days, 'total' => $weekTotal];
            $cursor = date('Y-m-d', strtotime($cursor . ' +7 days'));
        }

        $tahap = [
            ['label' => 'Tahap 1', 'total' => $weeks[0]['total'] + $weeks[1]['total']],
            ['label' => 'Tahap 2', 'total' => $weeks[2]['total'] + $weeks[3]['total']],
        ];

        $tahapRanges = [
            [$weeks[0]['days'][0]['tanggal'], $weeks[1]['days'][6]['tanggal']],
            [$weeks[2]['days'][0]['tanggal'], $weeks[3]['days'][6]['tanggal']],
        ];

        $kategoriPerTahap = [];
        foreach ($tahapRanges as [$rangeStart, $rangeEnd]) {
            $catBuilder = $db->table('detail_barangkeluar dbk')
                ->select('k.katnama, SUM(dbk.detjml * b.harga) AS total', false)
                ->join('barangkeluar bk', 'bk.faktur = dbk.detfaktur')
                ->join('barang b', 'b.brgkode = dbk.detbrgkode')
                ->join('kategori k', 'k.katid = b.brgkatid')
                ->where('bk.tglfaktur >=', $rangeStart)
                ->where('bk.tglfaktur <=', $rangeEnd)
                ->groupBy('k.katid, k.katnama')
                ->orderBy('k.katnama', 'ASC');
            if ($pelanggan !== null) {
                $catBuilder->where('dbk.detidpel', $pelanggan);
            }

            $categories = [];
            $catTotal = 0.0;
            foreach ($catBuilder->get()->getResultArray() as $row) {
                $categories[] = ['nama' => $row['katnama'], 'total' => (float) $row['total']];
                $catTotal += (float) $row['total'];
            }

            $kategoriPerTahap[] = [
                'label' => 'Tahap ' . date('d', strtotime($rangeStart)) . ' ' . strtoupper($this->namaBulan((int) date('n', strtotime($rangeStart))))
                    . ' - ' . date('d', strtotime($rangeEnd)) . ' ' . strtoupper($this->namaBulan((int) date('n', strtotime($rangeEnd)))) . ' ' . date('Y', strtotime($rangeEnd)),
                'categories' => $categories,
                'total' => $catTotal,
            ];
        }

        return [
            'periode' => $n,
            'namaBulan' => $this->namaBulan((int) date('n', strtotime($firstOfMonth))),
            'tahun' => (int) date('Y', strtotime($firstOfMonth)),
            'rentangLabel' => date('d M Y', strtotime($startMonday)) . ' - ' . date('d M Y', strtotime($endDate)),
            'weeks' => $weeks,
            'tahap' => $tahap,
            'kategoriPerTahap' => $kategoriPerTahap,
            'grandTotal' => $tahap[0]['total'] + $tahap[1]['total'],
        ];
    }
}
