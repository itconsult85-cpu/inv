<?php

namespace App\Controllers;

use App\Models\Modelbarang;
use App\Models\Modelgudang;
use App\Models\ModelPelanggan;
use App\Models\Modelstok;
use App\Models\Modelberat;
use App\Libraries\PublicId;

class Sampleproduk extends BaseController
{
    public function __construct()
    {
        $this->ensureSourceColumn();
    }

    private function ensureSourceColumn(): void
    {
        $db = db_connect();
        if ($db->tableExists('barangkeluar') && !$db->fieldExists('sumber', 'barangkeluar')) {
            db_connect()->query("ALTER TABLE barangkeluar ADD COLUMN sumber VARCHAR(30) NULL DEFAULT 'normal' AFTER gudang");
        }
    }

    public function index()
    {
        $db = db_connect();
        $rows = $db->table('barangkeluar bk')
            ->select('bk.faktur, bk.tglfaktur, bk.idpel, p.pelnama, bk.qtykeluar, bk.totalberatbarang, bk.gudang, g.gdgnama')
            ->join('pelanggan p', 'p.pelid = bk.idpel', 'left')
            ->join('gudang g', 'g.gdgid = bk.gudang', 'left')
            ->where('bk.sumber', 'sample')
            ->orderBy('bk.tglfaktur', 'DESC')->orderBy('bk.faktur', 'DESC')
            ->get()->getResultArray();
        foreach ($rows as &$row) {
            $row['details'] = $db->table('detail_barangkeluar d')
                ->select('d.detbrgkode, d.namabarang, d.detjml, d.detberat, d.detsubtotal')
                ->where('d.detfaktur', $row['faktur'])->orderBy('d.id')->get()->getResultArray();
        }
        return view('sampleproduk/index', ['rows' => $rows]);
    }

    public function tabData()
    {
        $db = db_connect();
        $rows = $db->table('barangkeluar bk')
            ->select('bk.faktur, bk.tglfaktur, p.pelnama, g.gdgnama')
            ->join('pelanggan p', 'p.pelid = bk.idpel', 'left')
            ->join('gudang g', 'g.gdgid = bk.gudang', 'left')
            ->where('bk.sumber', 'sample')
            ->orderBy('bk.tglfaktur', 'DESC')->orderBy('bk.faktur', 'DESC')
            ->get()->getResultArray();
        foreach ($rows as &$row) {
            $row['details'] = $db->table('detail_barangkeluar')
                ->select('detbrgkode, namabarang, detjml')
                ->where('detfaktur', $row['faktur'])->orderBy('id')->get()->getResultArray();
        }
        return view('sampleproduk/tab', ['rows' => $rows]);
    }

    public function input(string $id = '')
    {
        $db = db_connect();
        $fakturAsli = $id !== '' ? PublicId::decode($id, 'sample-faktur') : null;
        $header = $id !== '' ? $db->table('barangkeluar')
            ->where('sumber', 'sample')
            ->where('faktur', $fakturAsli ?? '__invalid_public_id__')
            ->get()->getRowArray() : null;
        if ($id !== '' && !$header) return redirect()->to(site_url('sampleproduk'))->with('error', 'Sample tidak ditemukan.');
        $details = $header ? $db->table('detail_barangkeluar')->where('detfaktur', $header['faktur'])->orderBy('id')->get()->getResultArray() : [];
        $poByProduct = [];
        if ($header && $db->tableExists('po') && $db->tableExists('detail_po')) {
            $poRows = $db->table('detail_po dp')
                ->select('dp.detnopo, dp.detkodebrg, dp.detqty, dp.detkirim_awal, dp.detkirim, dp.detkurang, p.tglpo')
                ->join('po p', 'p.nopo = dp.detnopo', 'inner')
                ->where('dp.detidpel', (int) $header['idpel'])
                ->orderBy('p.tglpo', 'DESC')->orderBy('dp.detnopo', 'DESC')
                ->get()->getResultArray();
            foreach ($poRows as $poRow) $poByProduct[(string) $poRow['detkodebrg']][] = $poRow;
        }
        return view('sampleproduk/form', [
            'header' => $header,
            'details' => $details,
            'poByProduct' => $poByProduct,
            'pelanggan' => (new ModelPelanggan())->orderBy('pelnama')->findAll(),
            'gudang' => (new Modelgudang())->findAll(),
            'produk' => (new Modelbarang())->select('brgkode, brgnama')->orderBy('brgkode')->findAll(),
        ]);
    }

    public function simpan()
    {
        $idLama = trim((string) $this->request->getPost('faktur_lama'));
        $faktur = trim((string) $this->request->getPost('faktur'));
        $tanggal = trim((string) $this->request->getPost('tanggal'));
        $pelanggan = (int) $this->request->getPost('pelanggan');
        $gudang = (int) $this->request->getPost('gudang');
        $kode = (array) $this->request->getPost('kode');
        $qty = (array) $this->request->getPost('qty');
        $poNos = (array) $this->request->getPost('po_no');
        $db = db_connect();

        if ($faktur === '' || $tanggal === '' || $pelanggan < 1 || $gudang < 1) return redirect()->back()->withInput()->with('error', 'Nomor surat jalan, tanggal, customer, dan gudang wajib diisi.');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) return redirect()->back()->withInput()->with('error', 'Tanggal tidak valid.');
        if (!$db->table('pelanggan')->where('pelid', $pelanggan)->countAllResults()) return redirect()->back()->withInput()->with('error', 'Customer tidak ditemukan.');
        if (!$db->table('gudang')->where('gdgid', $gudang)->countAllResults()) return redirect()->back()->withInput()->with('error', 'Gudang tidak ditemukan.');
        if ($db->table('barangkeluar')->where('faktur', $faktur)->where('faktur !=', $idLama)->countAllResults()) return redirect()->back()->withInput()->with('error', 'Nomor surat jalan sudah digunakan.');

        $items = [];
        foreach ($kode as $i => $kodeProduk) {
            $kodeProduk = trim((string) $kodeProduk);
            $jumlah = (int) ($qty[$i] ?? 0);
            if ($kodeProduk === '' || $jumlah <= 0) continue;
            $barang = $db->table('barang')->where('brgkode', $kodeProduk)->get()->getRowArray();
            $stok = $db->table('stok')->where('kodebarang', $kodeProduk)->where('gudang', $gudang)->get()->getRowArray();
            if (!$barang || !$stok) return redirect()->back()->withInput()->with('error', "Produk {$kodeProduk} atau stok gudangnya tidak ditemukan.");
            $beratRow = $db->table('berat')->where('kodeprd', $kodeProduk)->get()->getRowArray();
            $berat = (float) ($beratRow['berat'] ?? 0);
            $poNo = trim((string) ($poNos[$i] ?? ''));
            $poDetail = null;
            if ($poNo !== '') {
                $poDetail = $db->table('detail_po')->where('detnopo', $poNo)->where('detkodebrg', $kodeProduk)->where('detidpel', $pelanggan)->get()->getRowArray();
                if (!$poDetail) return redirect()->back()->withInput()->with('error', "Produk {$kodeProduk} tidak terdaftar pada PO {$poNo} customer tersebut.");
            }
            $items[] = ['kode' => $kodeProduk, 'nama' => $barang['brgnama'], 'material' => $this->materialUtamaProduk($barang['brgmat'] ?? ''), 'idbarang' => (int) $stok['id'], 'berat' => $berat, 'qty' => $jumlah, 'po_no' => $poNo, 'po_detail' => $poDetail, 'subtotal' => $berat * $jumlah];
        }
        if (!$items) return redirect()->back()->withInput()->with('error', 'Minimal satu produk sample harus diisi.');
        $requested = [];
        foreach ($items as $item) $requested[$item['kode']] = ($requested[$item['kode']] ?? 0) + $item['qty'];
        $oldQty = [];
        $oldPoNos = [];
        if ($idLama !== '') {
            foreach ($db->table('detail_barangkeluar')->select('detbrgkode, detpo, SUM(detjml) AS qty', false)->where('detfaktur', $idLama)->groupBy('detbrgkode, detpo')->get()->getResultArray() as $oldItem) {
                $oldQty[(string) $oldItem['detbrgkode']] = (int) $oldItem['qty'];
                if (trim((string) ($oldItem['detpo'] ?? '')) !== '') $oldPoNos[] = trim((string) $oldItem['detpo']);
            }
        }
        foreach ($requested as $kodeProduk => $jumlahDiminta) {
            $stok = $db->table('stok')->where('kodebarang', $kodeProduk)->where('gudang', $gudang)->get()->getRowArray();
            $tersedia = (int) ($stok['stok'] ?? 0) + (int) ($oldQty[$kodeProduk] ?? 0);
            if (!$stok || $tersedia < $jumlahDiminta) return redirect()->back()->withInput()->with('error', "Total stok {$kodeProduk} tidak mencukupi. Tersedia: {$tersedia}.");
        }
        foreach ($items as $item) {
            if (!$item['po_detail']) continue;
            $shippedByOthers = (float) ($db->table('detail_barangkeluar')
                ->selectSum('detjml', 'total')->where('detpo', $item['po_no'])
                ->where('detbrgkode', $item['kode'])->where('detfaktur !=', $idLama)
                ->get()->getRowArray()['total'] ?? 0);
            $poQty = (float) $item['po_detail']['detqty'];
            $initial = (float) ($item['po_detail']['detkirim_awal'] ?? 0);
            if ($shippedByOthers + $initial + $item['qty'] > $poQty + 0.000001) {
                $sisa = max($poQty - $initial - $shippedByOthers, 0);
                return redirect()->back()->withInput()->with('error', "Qty sample {$item['kode']} melebihi sisa PO {$item['po_no']} ({$sisa}).");
            }
        }

        $db->transStart();
        if ($idLama !== '') {
            $old = $db->table('barangkeluar')->where('faktur', $idLama)->where('sumber', 'sample')->get()->getRowArray();
            if (!$old) { $db->transRollback(); return redirect()->back()->withInput()->with('error', 'Sample lama tidak ditemukan.'); }
            $db->table('detail_barangkeluar')->where('detfaktur', $idLama)->delete();
            $db->table('barangkeluar')->where('faktur', $idLama)->update(['faktur' => $faktur, 'detpo' => $items[0]['po_no'] !== '' ? $items[0]['po_no'] : null, 'tglfaktur' => $tanggal, 'idpel' => $pelanggan, 'qtykeluar' => array_sum(array_column($items, 'qty')), 'totalberatbarang' => array_sum(array_column($items, 'subtotal')), 'gudang' => $gudang]);
            $targetFaktur = $faktur;
        } else {
            $targetFaktur = $faktur;
            $db->table('barangkeluar')->insert(['faktur' => $targetFaktur, 'detpo' => $items[0]['po_no'] !== '' ? $items[0]['po_no'] : null, 'tglfaktur' => $tanggal, 'idpel' => $pelanggan, 'qtykeluar' => array_sum(array_column($items, 'qty')), 'totalberatbarang' => array_sum(array_column($items, 'subtotal')), 'gudang' => $gudang, 'sumber' => 'sample']);
        }
        $detailRows = [];
        foreach ($items as $item) {
            $detailRows[] = ['detfaktur' => $targetFaktur, 'tgl' => $tanggal, 'detpo' => $item['po_no'] !== '' ? $item['po_no'] : null, 'detidpel' => $pelanggan, 'detbrgkode' => $item['kode'], 'namabarang' => $item['nama'], 'material' => $item['material'], 'idbarang' => $item['idbarang'], 'detberat' => $item['berat'], 'detjml' => $item['qty'], 'gudang' => $gudang, 'detsubtotal' => $item['subtotal']];
        }
        $db->table('detail_barangkeluar')->insertBatch($detailRows);
        $poNosToSync = array_values(array_unique(array_filter(array_merge($oldPoNos, array_column($items, 'po_no')))));
        if ($poNosToSync) (new \App\Models\Modeloutstand())->sinkronByPoList($poNosToSync);
        $db->transComplete();
        if (!$db->transStatus()) return redirect()->to(site_url('barangkeluar/data#sample'))->with('error', 'Sample gagal disimpan.');
        return redirect()->to(site_url('barangkeluar/data#sample'))->with('success', 'Produk sample berhasil disimpan sebagai surat jalan.');
    }

    public function hapus(string $faktur)
    {
        $db = db_connect();
        $fakturAsli = PublicId::decode($faktur, 'sample-faktur');
        if ($fakturAsli === null) return redirect()->to(site_url('barangkeluar/data#sample'))->with('error', 'Identifier sample tidak valid.');
        $row = $db->table('barangkeluar')->where('faktur', $fakturAsli)->where('sumber', 'sample')->get()->getRowArray();
        if (!$row) return redirect()->to(site_url('barangkeluar/data#sample'))->with('error', 'Sample tidak ditemukan.');
        $db->transStart();
        $db->table('detail_barangkeluar')->where('detfaktur', $row['faktur'])->delete();
        $db->table('barangkeluar')->where('faktur', $row['faktur'])->delete();
        $db->transComplete();
        return redirect()->to(site_url('barangkeluar/data#sample'))->with($db->transStatus() ? 'success' : 'error', $db->transStatus() ? 'Sample dihapus dan stok dikembalikan.' : 'Sample gagal dihapus.');
    }
}
