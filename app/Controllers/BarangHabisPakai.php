<?php

namespace App\Controllers;

use App\Libraries\AccessControl;
use App\Models\ModelDetailPermintaanStokProduksi;
use App\Models\ModelDetailPoKeluar;
use App\Models\ModelLogStokHabisPakai;
use App\Models\ModelPermintaanStokProduksi;
use App\Models\ModelStokHabisPakai;

class BarangHabisPakai extends BaseController
{
    private function deny(string $message = 'Anda tidak punya akses ke fitur ini.')
    {
        return $this->response->setStatusCode(403)->setJSON(['error' => $message]);
    }

    public function index()
    {
        if (!AccessControl::can('master.stok_habis_pakai.view')) {
            return redirect()->to('/')->with('error', 'Anda tidak punya akses ke fitur ini.');
        }

        $db = db_connect();
        $stok = (new ModelStokHabisPakai())->where('aktif', 1)->orderBy('nama', 'ASC')->findAll();
        $permintaan = $db->table('permintaan_stok_produksi r')
            ->select('r.*, COUNT(d.id) AS jumlah_item', false)
            ->join('permintaan_stok_produksi_detail d', 'd.permintaan_id = r.id', 'left')
            ->groupBy('r.id')
            ->orderBy('r.created_at', 'DESC')
            ->get()->getResultArray();
        $poPenerimaan = $db->table('detail_po_keluar d')
            ->select('d.id AS po_detail_id, d.po_keluar_id, p.no_po, p.tgl_po, p.supplier_nama, d.kode_item, d.nama_item, d.satuan, d.qty_pesan, d.qty_masuk')
            ->join('po_keluar p', 'p.id = d.po_keluar_id')
            ->where('d.tipe_item', 'habis_pakai')
            ->where('p.status !=', 'DIBATALKAN')
            ->where('d.qty_masuk < d.qty_pesan', null, false)
            ->orderBy('p.tgl_po', 'DESC')->orderBy('d.id', 'DESC')->get()->getResultArray();
        $log = $db->table('log_stok_habis_pakai l')
            ->select('l.*, s.kode, s.nama, s.satuan')
            ->join('stok_habis_pakai s', 's.id = l.stok_id', 'left')
            ->orderBy('l.created_at', 'DESC')
            ->limit(300)->get()->getResultArray();

        return view('baranghabispakai/index', compact('stok', 'permintaan', 'poPenerimaan', 'log'));
    }

    public function simpanBarang()
    {
        if (!AccessControl::can('master.stok_habis_pakai.manage_stock')) return $this->deny();
        $kodeInput = strtoupper(trim((string) $this->request->getPost('kode')));
        $nama = trim((string) $this->request->getPost('nama'));
        $satuan = trim((string) $this->request->getPost('satuan'));
        $stokAwal = 0.0;
        $minimum = (float) $this->request->getPost('stok_minimum');
        if ($nama === '' || $satuan === '' || $minimum < 0) {
            return $this->response->setJSON(['error' => 'Nama dan satuan barang wajib diisi, sedangkan stok hanya bertambah melalui penerimaan vendor.']);
        }
        if ($kodeInput !== '' && !preg_match('/^[A-Z0-9][A-Z0-9._\/-]{0,99}$/', $kodeInput)) {
            return $this->response->setJSON(['error' => 'Kode barang hanya boleh berisi huruf, angka, titik, garis bawah, garis miring, atau tanda hubung dengan panjang maksimal 100 karakter.']);
        }
        $model = new ModelStokHabisPakai();
        if ($kodeInput !== '' && $model->where('kode', $kodeInput)->first()) {
            return $this->response->setJSON(['error' => 'Kode barang tersebut sudah digunakan. Silakan gunakan kode lain.']);
        }
        if ($kodeInput === '') {
            do {
                $kode = 'BHP-' . date('ymdHis') . '-' . random_int(10, 99);
            } while ($model->where('kode', $kode)->first());
        } else {
            $kode = $kodeInput;
        }
        $now = date('Y-m-d H:i:s');
        $id = $model->insert([
            'kode' => $kode, 'nama' => $nama, 'satuan' => $satuan,
            'stok' => $stokAwal, 'stok_minimum' => $minimum, 'aktif' => 1,
            'created_by' => (string) session()->get('userid'), 'created_at' => $now, 'updated_at' => $now,
        ], true);
        return $this->response->setJSON(['sukses' => 'Barang habis pakai berhasil ditambahkan.']);
    }

    public function updateBarang()
    {
        if (!AccessControl::can('master.stok_habis_pakai.manage_stock')) return $this->deny();

        $id = (int) $this->request->getPost('id');
        $kode = strtoupper(trim((string) $this->request->getPost('kode')));
        $nama = trim((string) $this->request->getPost('nama'));
        $satuan = trim((string) $this->request->getPost('satuan'));
        $minimum = (float) $this->request->getPost('stok_minimum');
        $model = new ModelStokHabisPakai();

        if ($id <= 0 || !$model->find($id)) return $this->response->setJSON(['error' => 'Data stok tidak ditemukan.']);
        if ($kode === '' || $nama === '' || $satuan === '' || $minimum < 0) {
            return $this->response->setJSON(['error' => 'Kode, nama, dan satuan barang wajib diisi.']);
        }
        if (!preg_match('/^[A-Z0-9][A-Z0-9._\/-]{0,99}$/', $kode)) {
            return $this->response->setJSON(['error' => 'Kode barang hanya boleh berisi huruf, angka, titik, garis bawah, garis miring, atau tanda hubung dengan panjang maksimal 100 karakter.']);
        }
        if ((new ModelStokHabisPakai())->where('kode', $kode)->where('id !=', $id)->first()) {
            return $this->response->setJSON(['error' => 'Kode barang tersebut sudah digunakan. Silakan gunakan kode lain.']);
        }

        $model->update($id, [
            'kode' => $kode,
            'nama' => $nama,
            'satuan' => $satuan,
            'stok_minimum' => $minimum,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON(['sukses' => 'Master barang habis pakai berhasil diperbarui.']);
    }

    public function hapusBarang()
    {
        if (!AccessControl::can('master.stok_habis_pakai.manage_stock')) return $this->deny();

        $id = (int) $this->request->getPost('id');
        $model = new ModelStokHabisPakai();
        $item = $id > 0 ? $model->find($id) : null;
        if (!$item) return $this->response->setJSON(['error' => 'Data stok tidak ditemukan.']);

        // Jangan menghapus fisik: PO, penerimaan, dan log stok harus tetap terbaca.
        $model->update($id, ['aktif' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
        return $this->response->setJSON(['sukses' => 'Barang dinonaktifkan dan histori transaksinya tetap aman.']);
    }

    public function terimaBarangVendor()
    {
        if (!AccessControl::can('master.stok_habis_pakai.receive')) return $this->deny();
        $detailId = (int) $this->request->getPost('po_detail_id');
        $invoice = trim((string) $this->request->getPost('nomor_invoice'));
        $suratJalan = trim((string) $this->request->getPost('nomor_surat_jalan'));
        $qty = (float) $this->request->getPost('qty_diterima');
        if ($detailId <= 0 || $invoice === '' || $qty <= 0) return $this->response->setJSON(['error' => 'PO, nomor Invoice, dan qty diterima wajib diisi.']);
        $db = db_connect();
        $detail = $db->table('detail_po_keluar d')->select('d.*, p.no_po, p.supplier_nama')->join('po_keluar p', 'p.id = d.po_keluar_id')->where('d.id', $detailId)->where('d.tipe_item', 'habis_pakai')->get()->getRowArray();
        if (!$detail) return $this->response->setJSON(['error' => 'Detail PO barang habis pakai tidak ditemukan.']);
        $sisa = (float) $detail['qty_pesan'] - (float) $detail['qty_masuk'];
        if ($qty > $sisa) return $this->response->setJSON(['error' => 'Qty diterima melebihi sisa PO (' . $sisa . ').']);
        $stockModel = new ModelStokHabisPakai();
        $stock = $stockModel->where('kode', $detail['kode_item'])->where('aktif', 1)->first();
        if (!$stock) return $this->response->setJSON(['error' => 'Master stok untuk item PO tidak ditemukan atau tidak aktif.']);
        $now = date('Y-m-d H:i:s'); $before = (float) $stock['stok']; $after = $before + $qty;
        $db->transStart();
        $stockModel->update($stock['id'], ['stok' => $after, 'updated_at' => $now]);
        (new ModelDetailPoKeluar())->update($detailId, ['qty_masuk' => (float) $detail['qty_masuk'] + $qty]);
        (new ModelLogStokHabisPakai())->insert([
            'stok_id' => $stock['id'], 'po_keluar_id' => $detail['po_keluar_id'], 'po_detail_id' => $detailId,
            'nomor_invoice' => $invoice, 'nomor_surat_jalan' => $suratJalan ?: null, 'jenis' => 'MASUK', 'qty' => $qty,
            'stok_sebelum' => $before, 'stok_sesudah' => $after, 'user_id' => (string) session()->get('userid'),
            'catatan' => 'Penerimaan vendor dari PO ' . $detail['no_po'] . ' (' . $detail['supplier_nama'] . ')', 'created_at' => $now,
        ]);
        $db->transComplete();
        return $this->response->setJSON($db->transStatus() ? ['sukses' => 'Penerimaan berhasil disimpan dan stok otomatis bertambah.'] : ['error' => 'Penerimaan gagal disimpan.']);
    }

    public function simpanPermintaan()
    {
        if (!AccessControl::can('master.stok_habis_pakai.request')) return $this->deny();
        $ids = (array) $this->request->getPost('stok_id');
        $qtys = (array) $this->request->getPost('qty');
        $catatan = trim((string) $this->request->getPost('catatan'));
        $items = [];
        foreach ($ids as $index => $stockId) {
            $stockId = (int) $stockId;
            $qty = (float) ($qtys[$index] ?? 0);
            if ($stockId > 0 && $qty > 0) $items[$stockId] = ($items[$stockId] ?? 0) + $qty;
        }
        if (!$items) return $this->response->setJSON(['error' => 'Pilih minimal satu barang dan jumlah yang diminta.']);
        $modelStock = new ModelStokHabisPakai();
        $validItems = [];
        foreach ($items as $stockId => $qty) {
            if ($modelStock->where('id', $stockId)->where('aktif', 1)->first()) {
                $validItems[$stockId] = $qty;
            }
        }
        if (!$validItems) return $this->response->setJSON(['error' => 'Barang yang dipilih tidak valid atau sudah tidak aktif.']);
        $modelRequest = new ModelPermintaanStokProduksi();
        $modelDetail = new ModelDetailPermintaanStokProduksi();
        $now = date('Y-m-d H:i:s');
        do { $nomor = 'REQ-BHP-' . date('Ymd-His') . '-' . random_int(100, 999); } while ($modelRequest->where('nomor', $nomor)->first());
        $requestId = $modelRequest->insert([
            'nomor' => $nomor, 'tanggal' => date('Y-m-d'), 'peminta_id' => (string) session()->get('userid'),
            'status' => 'DIAJUKAN', 'catatan' => $catatan ?: null, 'created_at' => $now, 'updated_at' => $now,
        ], true);
        foreach ($validItems as $stockId => $qty) {
            $modelDetail->insert(['permintaan_id' => $requestId, 'stok_id' => $stockId, 'qty_diminta' => $qty, 'qty_disetujui' => 0]);
        }
        return $this->response->setJSON(['sukses' => "Permintaan {$nomor} berhasil diajukan dan menunggu persetujuan Supervisor."]);
    }

    public function setujui($id)
    {
        if (!AccessControl::can('master.stok_habis_pakai.approve') || !in_array((int) session()->get('idlevel'), [1, 4, 5], true)) return $this->deny('Persetujuan hanya dapat dilakukan oleh Supervisor/Admin/Pimpinan.');
        return $this->prosesPersetujuan((int) $id, 'DISETUJUI', trim((string) $this->request->getPost('catatan')));
    }

    public function tolak($id)
    {
        if (!AccessControl::can('master.stok_habis_pakai.approve') || !in_array((int) session()->get('idlevel'), [1, 4, 5], true)) return $this->deny('Penolakan hanya dapat dilakukan oleh Supervisor/Admin/Pimpinan.');
        return $this->prosesPersetujuan((int) $id, 'DITOLAK', trim((string) $this->request->getPost('catatan')));
    }

    private function prosesPersetujuan(int $id, string $status, string $catatan)
    {
        $requestModel = new ModelPermintaanStokProduksi();
        $request = $requestModel->find($id);
        if (!$request || $request['status'] !== 'DIAJUKAN') return $this->response->setJSON(['error' => 'Permintaan tidak ditemukan atau sudah diproses.']);
        $db = db_connect();
        $details = (new ModelDetailPermintaanStokProduksi())->where('permintaan_id', $id)->findAll();
        $stockModel = new ModelStokHabisPakai();
        if ($status === 'DISETUJUI') {
            foreach ($details as $detail) {
                $stock = $stockModel->find($detail['stok_id']);
                if (!$stock || (float) $stock['stok'] < (float) $detail['qty_diminta']) {
                    return $this->response->setJSON(['error' => 'Stok tidak cukup untuk ' . ($stock['nama'] ?? 'barang') . '. Permintaan tidak dapat disetujui.']);
                }
            }
        }
        $now = date('Y-m-d H:i:s');
        $db->transStart();
        foreach ($details as $detail) {
            $stock = $stockModel->find($detail['stok_id']);
            if ($status === 'DISETUJUI') {
                $before = (float) $stock['stok']; $after = $before - (float) $detail['qty_diminta'];
                $stockModel->update($stock['id'], ['stok' => $after, 'updated_at' => $now]);
                (new ModelDetailPermintaanStokProduksi())->update($detail['id'], ['qty_disetujui' => $detail['qty_diminta']]);
                (new ModelLogStokHabisPakai())->insert([
                    'stok_id' => $stock['id'], 'permintaan_id' => $id, 'jenis' => 'KELUAR', 'qty' => $detail['qty_diminta'],
                    'stok_sebelum' => $before, 'stok_sesudah' => $after, 'user_id' => (string) session()->get('userid'),
                    'catatan' => 'Pengeluaran berdasarkan persetujuan ' . $request['nomor'], 'created_at' => $now,
                ]);
            }
        }
        $requestModel->update($id, ['status' => $status, 'approved_by' => (string) session()->get('userid'), 'approved_at' => $now, 'approval_note' => $catatan ?: null, 'updated_at' => $now]);
        $db->transComplete();
        return $this->response->setJSON($db->transStatus() ? ['sukses' => 'Permintaan berhasil ' . strtolower($status) . '.'] : ['error' => 'Perubahan status gagal disimpan.']);
    }
}
