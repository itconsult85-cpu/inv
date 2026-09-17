<?php

namespace App\Controllers;

use App\Libraries\AccessControl;
use App\Models\ModelDetailPoHabisPakai;
use App\Models\ModelLogStokHabisPakai;
use App\Models\ModelPenerimaanPoHabisPakai;
use App\Models\ModelPoHabisPakai;
use App\Models\ModelStokHabisPakai;
use App\Models\ModelSupplier;

class PoHabisPakai extends BaseController
{
    public function index()
    {
        if (!AccessControl::can('order.po_habis_pakai.view')) return $this->deny();
        $db = db_connect();
        $rows = $db->table('po_habis_pakai p')->select('p.*, COALESCE(SUM(d.qty_pesan), 0) total_qty, COALESCE(SUM(d.qty_diterima), 0) total_diterima')->join('po_habis_pakai_detail d', 'd.po_id=p.id', 'left')->groupBy('p.id')->orderBy('p.tanggal_po', 'DESC')->orderBy('p.id', 'DESC')->get()->getResultArray();
        $details = $db->table('po_habis_pakai_detail d')->select('d.*, p.nomor_po, p.supplier_nama')->join('po_habis_pakai p', 'p.id=d.po_id')->where('d.qty_diterima < d.qty_pesan', null, false)->orderBy('p.tanggal_po', 'DESC')->get()->getResultArray();
        $receipts = $db->table('penerimaan_po_habis_pakai r')->select('r.*, p.nomor_po, p.supplier_nama, d.nama, d.kode, d.satuan')->join('po_habis_pakai p', 'p.id=r.po_id')->join('po_habis_pakai_detail d', 'd.id=r.detail_id')->orderBy('r.diterima_at', 'DESC')->orderBy('r.id', 'DESC')->get()->getResultArray();
        return view('pohabispakai/index', ['rows' => $rows, 'details' => $details, 'receipts' => $receipts, 'suppliers' => (new ModelSupplier())->orderBy('supnama')->findAll(), 'stok' => (new ModelStokHabisPakai())->where('aktif', 1)->orderBy('nama')->findAll()]);
    }

    public function simpan()
    {
        if (!AccessControl::can('order.po_habis_pakai.input')) return $this->deny();
        $data = $this->collectPoData();
        if ($data['error']) return redirect()->back()->withInput()->with('error', $data['error']);
        $now = date('Y-m-d H:i:s'); $db = db_connect(); $db->transStart();
        $poId = (new ModelPoHabisPakai())->insert(['nomor_po' => $this->newPoNumber(), 'tanggal_po' => $data['tanggal_po'], 'supplier_id' => $data['supplier']['supid'], 'supplier_nama' => $data['supplier']['supnama'], 'status' => 'DIBUAT', 'catatan' => $data['catatan'], 'created_by' => (string) session()->get('userid'), 'created_at' => $now, 'updated_at' => $now], true);
        foreach ($data['details'] as &$detail) $detail['po_id'] = $poId;
        (new ModelDetailPoHabisPakai())->insertBatch($data['details']); $db->transComplete(); $ok = $db->transStatus();
        return redirect()->to('poHabisPakai/index')->with($ok ? 'message' : 'error', $ok ? 'PO Barang Habis Pakai berhasil dibuat.' : 'PO gagal dibuat.');
    }

    public function edit($id)
    {
        if (!AccessControl::can('order.po_habis_pakai.input')) return $this->deny();
        $id = (int) $id; $po = (new ModelPoHabisPakai())->find($id);
        if (!$po) return redirect()->to('poHabisPakai/index')->with('error', 'PO tidak ditemukan.');
        if ($this->hasReceipts($id)) return redirect()->to('poHabisPakai/index')->with('error', 'PO yang sudah memiliki penerimaan tidak dapat diedit.');
        return view('pohabispakai/edit', ['po' => $po, 'details' => (new ModelDetailPoHabisPakai())->where('po_id', $id)->findAll(), 'suppliers' => (new ModelSupplier())->orderBy('supnama')->findAll(), 'stok' => (new ModelStokHabisPakai())->where('aktif', 1)->orderBy('nama')->findAll()]);
    }

    public function update($id)
    {
        if (!AccessControl::can('order.po_habis_pakai.input')) return $this->deny();
        $id = (int) $id; $po = (new ModelPoHabisPakai())->find($id);
        if (!$po) return redirect()->to('poHabisPakai/index')->with('error', 'PO tidak ditemukan.');
        if ($this->hasReceipts($id)) return redirect()->to('poHabisPakai/index')->with('error', 'PO yang sudah memiliki penerimaan tidak dapat diubah.');
        $data = $this->collectPoData(); if ($data['error']) return redirect()->back()->withInput()->with('error', $data['error']);
        $db = db_connect(); $db->transStart();
        (new ModelPoHabisPakai())->update($id, ['tanggal_po' => $data['tanggal_po'], 'supplier_id' => $data['supplier']['supid'], 'supplier_nama' => $data['supplier']['supnama'], 'catatan' => $data['catatan'], 'updated_at' => date('Y-m-d H:i:s')]);
        $detailModel = new ModelDetailPoHabisPakai(); $detailModel->where('po_id', $id)->delete();
        foreach ($data['details'] as &$detail) $detail['po_id'] = $id; $detailModel->insertBatch($data['details']); $db->transComplete(); $ok = $db->transStatus();
        return redirect()->to('poHabisPakai/index')->with($ok ? 'message' : 'error', $ok ? 'PO berhasil diubah.' : 'PO gagal diubah.');
    }

    public function hapus($id)
    {
        if (!AccessControl::can('order.po_habis_pakai.input')) return $this->deny();
        $id = (int) $id;
        if (!(new ModelPoHabisPakai())->find($id)) return redirect()->to('poHabisPakai/index')->with('error', 'PO tidak ditemukan.');
        if ($this->hasReceipts($id)) return redirect()->to('poHabisPakai/index')->with('error', 'PO yang sudah memiliki penerimaan tidak dapat dihapus.');
        $db = db_connect(); $db->transStart(); (new ModelDetailPoHabisPakai())->where('po_id', $id)->delete(); (new ModelPoHabisPakai())->delete($id); $db->transComplete(); $ok = $db->transStatus();
        return redirect()->to('poHabisPakai/index')->with($ok ? 'message' : 'error', $ok ? 'PO berhasil dihapus.' : 'PO gagal dihapus.');
    }

    public function terima()
    {
        if (!AccessControl::can('order.po_habis_pakai.receive')) return $this->deny();
        $detailId = (int) $this->request->getPost('detail_id'); $invoice = trim((string) $this->request->getPost('nomor_invoice')); $qty = (float) $this->request->getPost('qty');
        if (!$detailId || $invoice === '' || $qty <= 0) return redirect()->back()->with('error', 'Detail PO, Invoice, dan qty wajib diisi.');
        $detail = (new ModelDetailPoHabisPakai())->find($detailId); $stok = $detail ? (new ModelStokHabisPakai())->find($detail['stok_id']) : null;
        if (!$detail || $qty > ((float) $detail['qty_pesan'] - (float) $detail['qty_diterima'])) return redirect()->back()->with('error', 'Qty penerimaan melebihi sisa PO.');
        if (!$stok) return redirect()->back()->with('error', 'Master stok tidak ditemukan.');
        $now = date('Y-m-d H:i:s'); $suratJalan = trim((string) $this->request->getPost('nomor_surat_jalan')) ?: null; $db = db_connect(); $db->transStart();
        $before = (float) $stok['stok']; $after = $before + $qty;
        (new ModelStokHabisPakai())->update($stok['id'], ['stok' => $after, 'updated_at' => $now]); (new ModelDetailPoHabisPakai())->update($detailId, ['qty_diterima' => (float) $detail['qty_diterima'] + $qty]);
        $receiptId = (new ModelPenerimaanPoHabisPakai())->insert(['po_id' => $detail['po_id'], 'detail_id' => $detailId, 'stok_id' => $stok['id'], 'nomor_invoice' => $invoice, 'nomor_surat_jalan' => $suratJalan, 'qty' => $qty, 'diterima_oleh' => (string) session()->get('userid'), 'diterima_at' => $now], true);
        $logData = ['stok_id' => $stok['id'], 'nomor_invoice' => $invoice, 'nomor_surat_jalan' => $suratJalan, 'jenis' => 'MASUK', 'qty' => $qty, 'stok_sebelum' => $before, 'stok_sesudah' => $after, 'user_id' => (string) session()->get('userid'), 'catatan' => 'Penerimaan PO BHP', 'created_at' => $now];
        if ($this->hasReceiptLogColumn()) $logData['penerimaan_id'] = $receiptId;
        (new ModelLogStokHabisPakai())->insert($logData);
        $this->refreshPoStatus($detail['po_id']); $db->transComplete(); $ok = $db->transStatus();
        return redirect()->back()->with($ok ? 'message' : 'error', $ok ? 'Penerimaan berhasil, stok bertambah.' : 'Penerimaan gagal.');
    }

    public function updatePenerimaan($id)
    {
        if (!AccessControl::can('order.po_habis_pakai.receive')) return $this->deny();
        $id = (int) $id; $receiptModel = new ModelPenerimaanPoHabisPakai(); $receipt = $receiptModel->find($id); $newQty = (float) $this->request->getPost('qty'); $invoice = trim((string) $this->request->getPost('nomor_invoice'));
        if (!$receipt || $newQty <= 0 || $invoice === '') return redirect()->back()->with('error', 'Data penerimaan tidak valid.');
        $detail = (new ModelDetailPoHabisPakai())->find($receipt['detail_id']); $stok = (new ModelStokHabisPakai())->find($receipt['stok_id']);
        if (!$detail || !$stok) return redirect()->back()->with('error', 'Detail PO atau master stok tidak ditemukan.');
        $max = (float) $detail['qty_pesan'] - (float) $detail['qty_diterima'] + (float) $receipt['qty']; if ($newQty > $max) return redirect()->back()->with('error', 'Qty baru melebihi sisa PO.');
        $delta = $newQty - (float) $receipt['qty']; $after = (float) $stok['stok'] + $delta; if ($after < 0) return redirect()->back()->with('error', 'Penerimaan tidak dapat dikurangi karena stok sudah terpakai.');
        $now = date('Y-m-d H:i:s'); $suratJalan = trim((string) $this->request->getPost('nomor_surat_jalan')) ?: null; $db = db_connect(); $db->transStart();
        (new ModelStokHabisPakai())->update($stok['id'], ['stok' => $after, 'updated_at' => $now]); (new ModelDetailPoHabisPakai())->update($detail['id'], ['qty_diterima' => (float) $detail['qty_diterima'] + $delta]); $receiptModel->update($id, ['nomor_invoice' => $invoice, 'nomor_surat_jalan' => $suratJalan, 'qty' => $newQty]);
        $this->updateReceiptLog($receipt, $stok, $newQty, $invoice, $suratJalan, (float) $stok['stok'] - (float) $receipt['qty'], $after); $this->refreshPoStatus($receipt['po_id']); $db->transComplete(); $ok = $db->transStatus();
        return redirect()->back()->with($ok ? 'message' : 'error', $ok ? 'Penerimaan berhasil diubah dan stok disesuaikan.' : 'Perubahan penerimaan gagal.');
    }

    public function hapusPenerimaan($id)
    {
        if (!AccessControl::can('order.po_habis_pakai.receive')) return $this->deny();
        $id = (int) $id; $receiptModel = new ModelPenerimaanPoHabisPakai(); $receipt = $receiptModel->find($id); if (!$receipt) return redirect()->back()->with('error', 'Penerimaan tidak ditemukan.');
        $stok = (new ModelStokHabisPakai())->find($receipt['stok_id']); $detail = (new ModelDetailPoHabisPakai())->find($receipt['detail_id']);
        if (!$stok || !$detail || (float) $stok['stok'] < (float) $receipt['qty']) return redirect()->back()->with('error', 'Penerimaan tidak dapat dihapus karena stok sudah terpakai.');
        $now = date('Y-m-d H:i:s'); $after = (float) $stok['stok'] - (float) $receipt['qty']; $db = db_connect(); $db->transStart();
        (new ModelStokHabisPakai())->update($stok['id'], ['stok' => $after, 'updated_at' => $now]); (new ModelDetailPoHabisPakai())->update($detail['id'], ['qty_diterima' => max(0, (float) $detail['qty_diterima'] - (float) $receipt['qty'])]); $this->deleteReceiptLog($receipt); $receiptModel->delete($id); $this->refreshPoStatus($receipt['po_id']); $db->transComplete(); $ok = $db->transStatus();
        return redirect()->back()->with($ok ? 'message' : 'error', $ok ? 'Penerimaan berhasil dihapus dan stok dikembalikan.' : 'Penerimaan gagal dihapus.');
    }

    private function collectPoData(): array
    {
        $supplier = (new ModelSupplier())->find((int) $this->request->getPost('supplier_id')); $ids = (array) $this->request->getPost('stok_id'); $qtys = (array) $this->request->getPost('qty_pesan'); $prices = (array) $this->request->getPost('harga');
        if (!$supplier || !$ids) return ['error' => 'Supplier dan minimal satu barang wajib diisi.']; $stockRows = (new ModelStokHabisPakai())->find($ids); $map = [];
        foreach ($stockRows as $stock) $map[(int) $stock['id']] = $stock; $details = [];
        foreach ($ids as $i => $stockId) { $stockId = (int) $stockId; $qty = (float) ($qtys[$i] ?? 0); if ($qty <= 0 || !isset($map[$stockId])) continue; $price = max(0, (float) ($prices[$i] ?? 0)); $details[] = ['stok_id' => $stockId, 'kode' => $map[$stockId]['kode'], 'nama' => $map[$stockId]['nama'], 'satuan' => $map[$stockId]['satuan'], 'qty_pesan' => $qty, 'qty_diterima' => 0, 'harga' => $price, 'subtotal' => $qty * $price]; }
        if (!$details) return ['error' => 'Detail barang tidak valid.']; return ['error' => null, 'supplier' => $supplier, 'details' => $details, 'tanggal_po' => $this->request->getPost('tanggal_po') ?: date('Y-m-d'), 'catatan' => trim((string) $this->request->getPost('catatan'))];
    }

    private function newPoNumber(): string
    {
        do { $number = 'PO-BHP-' . date('YmdHis') . '-' . random_int(100, 999); } while ((new ModelPoHabisPakai())->where('nomor_po', $number)->first()); return $number;
    }
    private function hasReceipts(int $poId): bool { return (bool) (new ModelPenerimaanPoHabisPakai())->where('po_id', $poId)->first(); }
    private function refreshPoStatus(int $poId): void { $row = db_connect()->table('po_habis_pakai_detail')->select('SUM(qty_pesan) total, SUM(qty_diterima) received')->where('po_id', $poId)->get()->getRowArray(); $status = ((float) ($row['total'] ?? 0) > 0 && (float) ($row['received'] ?? 0) >= (float) ($row['total'] ?? 0)) ? 'SELESAI' : 'DIBUAT'; (new ModelPoHabisPakai())->update($poId, ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]); }
    private function updateReceiptLog(array $receipt, array $stok, float $qty, string $invoice, ?string $suratJalan, float $before, float $after): void
    {
        $model = new ModelLogStokHabisPakai(); $log = $this->hasReceiptLogColumn() ? $model->where('penerimaan_id', $receipt['id'])->first() : null; if (!$log) $log = $model->where('stok_id', $receipt['stok_id'])->where('jenis', 'MASUK')->where('nomor_invoice', $receipt['nomor_invoice'])->where('qty', $receipt['qty'])->orderBy('id', 'DESC')->first();
        $fields = ['nomor_invoice' => $invoice, 'nomor_surat_jalan' => $suratJalan, 'qty' => $qty, 'stok_sebelum' => $before, 'stok_sesudah' => $after]; if ($this->hasReceiptLogColumn()) $fields['penerimaan_id'] = $receipt['id']; if ($log) $model->update($log['id'], $fields); else $model->insert(array_merge($fields, ['stok_id' => $stok['id'], 'jenis' => 'MASUK', 'catatan' => 'Penerimaan PO BHP', 'created_at' => date('Y-m-d H:i:s')]));
    }
    private function deleteReceiptLog(array $receipt): void
    {
        $model = new ModelLogStokHabisPakai(); $log = $this->hasReceiptLogColumn() ? $model->where('penerimaan_id', $receipt['id'])->first() : null; if (!$log) $log = $model->where('stok_id', $receipt['stok_id'])->where('jenis', 'MASUK')->where('nomor_invoice', $receipt['nomor_invoice'])->where('qty', $receipt['qty'])->orderBy('id', 'DESC')->first(); if ($log) $model->delete($log['id']);
    }

    private function hasReceiptLogColumn(): bool
    {
        static $hasColumn;
        if ($hasColumn === null) $hasColumn = db_connect()->tableExists('log_stok_habis_pakai') && in_array('penerimaan_id', db_connect()->getFieldNames('log_stok_habis_pakai'), true);
        return $hasColumn;
    }
}
