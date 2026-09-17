<?php

namespace App\Controllers;

class Produkretur extends BaseController
{
    private function headerByHash(string $hash): ?array
    {
        return db_connect()->table('barangmasuk bm')->select('bm.*, s.supnama, g.gdgnama')->join('supplier s', 's.supid = bm.idsup', 'left')->join('gudang g', 'g.gdgid = bm.gudang', 'left')->where('SHA1(bm.faktur) = ' . db_connect()->escape($hash), null, false)->get()->getRowArray() ?: null;
    }

    private function nomor(): string
    {
        $db = db_connect(); do { $no = 'RTP-' . date('Ymd-His') . '-' . random_int(100, 999); } while ($db->table('retur_produk')->where('nomor_retur', $no)->countAllResults()); return $no;
    }

    public function form(string $hash)
    {
        $header = $this->headerByHash($hash); if (!$header) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Transaksi Produk Masuk tidak ditemukan.');
        $db = db_connect();
        $details = $db->table('detail_barangmasuk dbm')->select("dbm.id, dbm.detfaktur, dbm.detbrgkode, dbm.detbrgnama, dbm.detjml, COALESCE(r.qty_retur, 0) AS qty_retur, (dbm.detjml - COALESCE(r.qty_retur, 0)) AS sisa_retur", false)->join('(SELECT barang_masuk_detail_id, SUM(qty_retur) AS qty_retur FROM retur_produk_detail GROUP BY barang_masuk_detail_id) r', 'r.barang_masuk_detail_id = dbm.id', 'left')->where('dbm.detfaktur', $header['faktur'])->orderBy('dbm.id', 'ASC')->get()->getResultArray();
        return view('produkretur/form', ['header' => $header, 'details' => $details]);
    }

    public function simpan()
    {
        $db = db_connect(); $faktur = trim((string) $this->request->getPost('faktur')); $tgl = trim((string) $this->request->getPost('tgl_retur')); $qtyInput = (array) $this->request->getPost('qty'); $ketInput = (array) $this->request->getPost('keterangan');
        $header = $db->table('barangmasuk')->where('faktur', $faktur)->get()->getRowArray(); if (!$header || $tgl === '') return redirect()->back()->withInput()->with('error', 'Transaksi dan tanggal retur wajib diisi.');
        $rows = []; $error = null;
        foreach (array_keys($qtyInput) as $id) { $id = (int) $id; $qty = (float) ($qtyInput[$id] ?? 0); if ($qty <= 0) continue;
            $detail = $db->table('detail_barangmasuk')->where('id', $id)->where('detfaktur', $faktur)->get()->getRowArray(); if (!$detail) { $error = 'Detail produk retur tidak valid.'; break; }
            $sudah = (float) $db->table('retur_produk_detail')->selectSum('qty_retur')->where('barang_masuk_detail_id', $id)->get()->getRow()->qty_retur; $sisa = (float) $detail['detjml'] - $sudah;
            if ($qty > $sisa + 0.000001) { $error = 'Qty retur produk ' . $detail['detbrgkode'] . ' melebihi sisa (' . $sisa . ').'; break; }
            $stok = $db->table('stok')->where('kodebarang', $detail['detbrgkode'])->where('gudang', $detail['gudang'])->get()->getRowArray(); if (!$stok || (float) $stok['stok'] < $qty) { $error = 'Stok produk ' . $detail['detbrgkode'] . ' tidak cukup.'; break; }
            $rows[] = ['detail' => $detail, 'qty' => $qty, 'ket' => trim((string) ($ketInput[$id] ?? '')) ?: 'NG'];
        }
        if (!$rows && !$error) $error = 'Isi minimal satu qty retur NG.'; if ($error) return redirect()->back()->withInput()->with('error', $error);
        $db->transStart(); $returId = $db->table('retur_produk')->insert(['nomor_retur' => $this->nomor(), 'barang_masuk_faktur' => $faktur, 'tgl_retur' => $tgl, 'idsup' => $header['idsup'] ?: null, 'gudang' => $header['gudang'], 'catatan' => trim((string) $this->request->getPost('catatan')) ?: null, 'user_id' => session()->get('userid'), 'created_at' => date('Y-m-d H:i:s')], true);
        foreach ($rows as $row) { $d = $row['detail']; $db->table('stok')->where('id', $db->table('stok')->where('kodebarang', $d['detbrgkode'])->where('gudang', $d['gudang'])->get()->getRowArray()['id'])->set('stok', 'stok - ' . $row['qty'], false)->update(); $db->table('retur_produk_detail')->insert(['retur_id' => $returId, 'po_keluar_id' => $header['po_keluar_id'] ?: null, 'barang_masuk_detail_id' => $d['id'], 'kode_barang' => $d['detbrgkode'], 'qty_retur' => $row['qty'], 'keterangan' => $row['ket']]); if (!empty($header['po_keluar_id'])) { $poId = (int) $header['po_keluar_id']; $db->table('po_keluar')->where('id', $poId)->update(['status' => 'NG']); $db->table('detail_po_keluar')->where('po_keluar_id', $poId)->where('tipe_item', 'produk')->where('kode_item', $d['detbrgkode'])->update(['status' => 'NG']); } }
        $db->transComplete(); if ($db->transStatus() === false) return redirect()->back()->withInput()->with('error', 'Retur produk gagal disimpan.'); return redirect()->to(site_url('barangmasuk/data'))->with('success', 'Retur produk NG berhasil disimpan dan stok dikurangi.');
    }
}
