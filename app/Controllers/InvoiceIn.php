<?php

namespace App\Controllers;

use App\Models\ModelInvoiceIn;
use App\Models\ModelInvoiceInDetail;

class InvoiceIn extends BaseController
{
    private $db;
    private ModelInvoiceIn $invoiceModel;
    private ModelInvoiceInDetail $detailModel;

    public function __construct()
    {
        $this->db = db_connect();
        $this->invoiceModel = new ModelInvoiceIn();
        $this->detailModel = new ModelInvoiceInDetail();
        $this->ensurePoKeluarJenisPoColumn();
        $this->ensureInvoiceInPrintColumns();
    }

    private function ensureInvoiceInPrintColumns(): void
    {
        if (!$this->db->tableExists('invoice_in')) {
            return;
        }

        $forge = \Config\Database::forge();
        $columns = [
            'ppn_enabled' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'after' => 'subtotal'],
            'ppn_percent' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 11, 'after' => 'ppn_enabled'],
            'pph_enabled' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'after' => 'pph23'],
            'pph_percent' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 2, 'after' => 'pph_enabled'],
            'dp_enabled' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'pph_percent'],
            'dp_percent' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 50, 'after' => 'dp_enabled'],
            'dp_amount' => ['type' => 'DECIMAL', 'constraint' => '18,2', 'default' => 0, 'after' => 'dp_percent'],
            'invoice_uploaded_at' => ['type' => 'DATETIME', 'null' => true, 'after' => 'invoice_original_name'],
            'bukti_transfer_uploaded_at' => ['type' => 'DATETIME', 'null' => true, 'after' => 'bukti_transfer_original_name'],
        ];

        foreach ($columns as $name => $definition) {
            if (!$this->db->fieldExists($name, 'invoice_in')) {
                $forge->addColumn('invoice_in', [$name => $definition]);
            }
        }

        if ($this->db->fieldExists('invoice_uploaded_at', 'invoice_in')) {
            $this->db->table('invoice_in')
                ->set('invoice_uploaded_at', 'COALESCE(updated_at, created_at, NOW())', false)
                ->where('invoice_file IS NOT NULL', null, false)
                ->where('invoice_file !=', '')
                ->where('invoice_uploaded_at IS NULL', null, false)
                ->update();
        }
    }

    public function data()
    {
        return view('invoicein/index', [
            'invoices' => $this->invoiceModel->orderBy('invoice_date', 'DESC')->orderBy('id', 'DESC')->findAll(),
        ]);
    }

    public function create()
    {
        $type = strtolower((string) $this->request->getGet('type'));
        $sourceNo = (string) $this->request->getGet('source');
        $selectedFakturs = array_values(array_filter(array_map(
            'strval',
            $this->request->getGet('faktur') ?? []
        )));
        $shipments = ($type === 'po_keluar' && $sourceNo) ? $this->getPoKeluarShipments($sourceNo) : [];
        $sourceNoForSave = ($type === 'po_keluar' && $selectedFakturs)
            ? $this->composePoKeluarSourceNo($sourceNo, $selectedFakturs)
            : $sourceNo;
        $source = null;
        if ($type && $sourceNo && ($type !== 'po_keluar' || !$shipments || $selectedFakturs)) {
            $source = $this->getSourceData($type, $sourceNoForSave);
        }

        return view('invoicein/form', [
            'sources' => $this->getSources(),
            'shipments' => $shipments,
            'selectedFakturs' => $selectedFakturs,
            'source' => $source,
            'selectedType' => $type,
            'selectedSource' => $sourceNo,
            'sourceNoForSave' => $sourceNoForSave,
        ]);
    }

    public function save()
    {
        $rules = [
            'invoice_no' => 'required|max_length[100]',
            'invoice_date' => 'required|valid_date[Y-m-d]',
            'source_type' => 'required|in_list[material,produk,po_keluar]',
            'source_no' => 'required|max_length[255]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $type = (string) $this->request->getPost('source_type');
        $sourceNo = (string) $this->request->getPost('source_no');
        $invoiceNo = trim((string) $this->request->getPost('invoice_no'));
        $source = $this->getSourceData($type, $sourceNo);
        if (!$source) {
            return redirect()->back()->withInput()->with('error', 'Transaksi penerimaan tidak ditemukan.');
        }
        if (!$source['lines']) {
            return redirect()->back()->withInput()->with('error', 'Transaksi penerimaan belum memiliki detail item.');
        }
        if ($this->invoiceModel->where('source_type', $type)->where('source_no', $sourceNo)->first()) {
            return redirect()->back()->withInput()->with('error', 'Transaksi penerimaan ini sudah memiliki Invoice In.');
        }
        if ($type === 'po_keluar') {
            [$poSourceNo, $selectedFakturs] = $this->splitPoKeluarSourceNo($sourceNo);
            foreach ($selectedFakturs as $faktur) {
                if ($this->poKeluarShipmentAlreadyInvoiced($poSourceNo, $faktur)) {
                    return redirect()->back()->withInput()->with('error', 'Surat jalan ' . $faktur . ' sudah memiliki Invoice In.');
                }
            }
        }
        if ($this->invoiceModel->where('supplier_id', $source['header']['supplier_id'])->where('invoice_no', $invoiceNo)->first()) {
            return redirect()->back()->withInput()->with('error', 'Nomor invoice ini sudah tercatat untuk supplier yang sama.');
        }

        $uploadedFileName = null;
        $invoiceFile = $this->request->getFile('invoice_file');
        if ($invoiceFile && $invoiceFile->getError() !== UPLOAD_ERR_NO_FILE) {
            $fileRules = [
                'invoice_file' => [
                    'label' => 'File Invoice',
                    'rules' => 'max_size[invoice_file,10240]|ext_in[invoice_file,pdf,jpg,jpeg,png]|mime_in[invoice_file,application/pdf,image/jpg,image/jpeg,image/png]',
                ],
            ];
            if (!$this->validate($fileRules)) {
                return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
            }
            if (!$invoiceFile->isValid()) {
                return redirect()->back()->withInput()->with('error', 'File invoice tidak valid.');
            }

            $uploadDir = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'invoice_in';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }
            $extension = strtolower($invoiceFile->getClientExtension() ?: $invoiceFile->getExtension() ?: 'dat');
            $uploadedFileName = bin2hex(random_bytes(16)) . '.' . $extension;
            $invoiceFile->move($uploadDir, $uploadedFileName);
        }

        $postedPrices = (array) $this->request->getPost('harga');
        $details = [];
        $subtotal = 0.0;
        foreach ($source['lines'] as $line) {
            $key = (string) $line['source_detail_id'];
            $price = isset($postedPrices[$key]) ? (float) $postedPrices[$key] : 0.0;
            if ($price < 0) {
                return redirect()->back()->withInput()->with('error', 'Harga item tidak boleh negatif.');
            }
            $amount = (float) $line['qty'] * $price;
            $subtotal += $amount;
            $details[] = $line + ['unit_price' => $price, 'amount' => $amount];
        }
        if ($subtotal <= 0) {
            return redirect()->back()->withInput()->with('error', 'Total Invoice In harus lebih besar dari nol.');
        }

        $ppnEnabled = $this->request->getPost('ppn_enabled') ? 1 : 0;
        $ppnPercent = max(0, (float) str_replace(',', '.', (string) $this->request->getPost('ppn_percent')));
        $ppnPercent = $ppnPercent > 0 ? $ppnPercent : 11;
        $ppn = $ppnEnabled ? round($subtotal * ($ppnPercent / 100), 2) : 0;

        $pphEnabled = $this->request->getPost('pph_enabled') ? 1 : 0;
        $pphPercent = max(0, (float) str_replace(',', '.', (string) $this->request->getPost('pph_percent')));
        $pphPercent = $pphPercent > 0 ? $pphPercent : 2;
        $pph23 = $pphEnabled ? round($subtotal * ($pphPercent / 100), 2) : 0;

        $dpEnabled = $this->request->getPost('dp_enabled') ? 1 : 0;
        $dpPercent = max(0, min(100, (float) str_replace(',', '.', (string) $this->request->getPost('dp_percent'))));
        $dpPercent = $dpPercent > 0 ? $dpPercent : 50;
        // Sama seperti Invoice Out: PPh 23 ditampilkan sebagai informasi,
        // Grand Total = subtotal + PPN, dikurangi DP kalau dipakai.
        $dpAmount = $dpEnabled ? round(($subtotal + $ppn) * ($dpPercent / 100), 2) : 0;
        $grandTotal = max(($subtotal + $ppn) - $dpAmount, 0);

        $this->db->transBegin();
        try {
            $invoiceId = $this->invoiceModel->insert([
                'invoice_no' => $invoiceNo,
                'invoice_date' => $this->request->getPost('invoice_date'),
                'supplier_id' => $source['header']['supplier_id'],
                'supplier_name' => $source['header']['supplier_name'],
                'supplier_address' => $source['header']['supplier_address'],
                'supplier_phone' => $source['header']['supplier_phone'],
                'source_type' => $type,
                'source_no' => $sourceNo,
                'invoice_file' => $uploadedFileName,
                'invoice_original_name' => $uploadedFileName,
                'invoice_uploaded_at' => $uploadedFileName ? date('Y-m-d H:i:s') : null,
                'subtotal' => $subtotal,
                'ppn' => $ppn,
                'ppn_enabled' => $ppnEnabled,
                'ppn_percent' => $ppnPercent,
                'pph23' => $pph23,
                'pph_enabled' => $pphEnabled,
                'pph_percent' => $pphPercent,
                'dp_enabled' => $dpEnabled,
                'dp_percent' => $dpPercent,
                'dp_amount' => $dpAmount,
                'grand_total' => $grandTotal,
                'status' => 'AKTIF',
                'created_by' => session()->get('namauser'),
            ], true);
            if (!$invoiceId) {
                throw new \RuntimeException('Header invoice gagal disimpan.');
            }

            $rows = [];
            foreach ($details as $detail) {
                $rows[] = [
                    'invoice_id' => $invoiceId,
                    'item_type' => $type,
                    'no_surat_jalan' => $detail['no_surat_jalan'] ?? null,
                    'item_code' => $detail['item_code'],
                    'item_name' => $detail['item_name'],
                    'qty' => $detail['qty'],
                    'unit' => $detail['unit'],
                    'unit_price' => $detail['unit_price'],
                    'amount' => $detail['amount'],
                    'created_at' => date('Y-m-d H:i:s'),
                ];
            }
            if (!$this->detailModel->insertBatch($rows)) {
                throw new \RuntimeException('Detail invoice gagal disimpan.');
            }
            if (!$this->db->transStatus()) {
                throw new \RuntimeException('Transaksi database gagal.');
            }
            $this->db->transCommit();

            return redirect()->to('/invoiceIn/detail/' . $invoiceId)->with('message', 'Invoice In berhasil dicatat.');
        } catch (\Throwable $e) {
            $this->db->transRollback();
            if ($uploadedFileName) {
                $uploadedPath = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'invoice_in' . DIRECTORY_SEPARATOR . $uploadedFileName;
                if (is_file($uploadedPath)) {
                    unlink($uploadedPath);
                }
            }
            log_message('error', 'Gagal menyimpan Invoice In: {message}', ['message' => $e->getMessage()]);
            return redirect()->back()->withInput()->with('error', 'Invoice In gagal disimpan. ' . $e->getMessage());
        }
    }

    public function detail(int $id)
    {
        $data = $this->getInvoice($id);
        if (!$data) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Invoice In tidak ditemukan.');
        }
        return view('invoicein/detail', $data);
    }

    public function uploadFileInvoice(int $id)
    {
        $data = $this->getInvoice($id);
        if (!$data) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Invoice In tidak ditemukan.');
        }
        return view('invoicein/upload_file_invoice', $data);
    }

    public function simpanFileInvoice(int $id)
    {
        $invoice = $this->invoiceModel->find($id);
        if (!$invoice) {
            return redirect()->to('/invoiceIn/data')->with('error', 'Invoice In tidak ditemukan.');
        }
        if (($invoice['status'] ?? '') === 'DIBATALKAN') {
            return redirect()->to('/invoiceIn/data')->with('error', 'Invoice In yang sudah dibatalkan tidak bisa diupload file invoice.');
        }

        $rules = [
            'invoice_file' => [
                'label' => 'File Invoice',
                'rules' => 'uploaded[invoice_file]|max_size[invoice_file,10240]|ext_in[invoice_file,pdf,jpg,jpeg,png]|mime_in[invoice_file,application/pdf,image/jpg,image/jpeg,image/png]',
            ],
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $file = $this->request->getFile('invoice_file');
        if (!$file || !$file->isValid()) {
            return redirect()->back()->withInput()->with('error', 'File invoice tidak valid.');
        }

        $uploadDir = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'invoice_in';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $extension = strtolower($file->getClientExtension() ?: $file->getExtension() ?: 'dat');
        $uploadedFileName = bin2hex(random_bytes(16)) . '.' . $extension;

        try {
            $file->move($uploadDir, $uploadedFileName);
            $this->invoiceModel->update($id, [
                'invoice_file' => $uploadedFileName,
                'invoice_original_name' => $uploadedFileName,
                'invoice_uploaded_at' => date('Y-m-d H:i:s'),
            ]);

            if (!empty($invoice['invoice_file'])) {
                $oldPath = $uploadDir . DIRECTORY_SEPARATOR . $invoice['invoice_file'];
                if (is_file($oldPath) && $invoice['invoice_file'] !== $uploadedFileName) {
                    unlink($oldPath);
                }
            }

            return redirect()->to('/invoiceIn/detail/' . $id)->with('message', 'File invoice berhasil diupload.');
        } catch (\Throwable $e) {
            $uploadedPath = $uploadDir . DIRECTORY_SEPARATOR . $uploadedFileName;
            if (is_file($uploadedPath)) {
                unlink($uploadedPath);
            }
            log_message('error', 'Gagal upload file invoice in: {message}', ['message' => $e->getMessage()]);
            return redirect()->back()->withInput()->with('error', 'File invoice gagal diupload. ' . $e->getMessage());
        }
    }

    public function hapusFileInvoice(int $id)
    {
        $invoice = $this->invoiceModel->find($id);
        if (!$invoice) {
            return redirect()->to('/invoiceIn/data')->with('error', 'Invoice In tidak ditemukan.');
        }
        if (($invoice['status'] ?? '') === 'DIBATALKAN') {
            return redirect()->to('/invoiceIn/detail/' . $id)->with('error', 'File invoice pada Invoice In yang dibatalkan tidak bisa dihapus.');
        }
        if (empty($invoice['invoice_file'])) {
            return redirect()->to('/invoiceIn/detail/' . $id)->with('error', 'File invoice belum tersedia.');
        }

        $uploadDir = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'invoice_in';
        $path = $uploadDir . DIRECTORY_SEPARATOR . $invoice['invoice_file'];

        try {
            if (is_file($path)) {
                unlink($path);
            }

            $this->invoiceModel->update($id, [
                'invoice_file' => null,
                'invoice_original_name' => null,
                'invoice_uploaded_at' => null,
            ]);

            return redirect()->to('/invoiceIn/detail/' . $id)->with('message', 'File invoice berhasil dihapus.');
        } catch (\Throwable $e) {
            log_message('error', 'Gagal hapus file invoice in: {message}', ['message' => $e->getMessage()]);
            return redirect()->to('/invoiceIn/detail/' . $id)->with('error', 'File invoice gagal dihapus. ' . $e->getMessage());
        }
    }

    public function uploadBuktiTransfer(int $id)
    {
        $data = $this->getInvoice($id);
        if (!$data) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Invoice In tidak ditemukan.');
        }
        return view('invoicein/upload_bukti_transfer', $data);
    }

    public function simpanBuktiTransfer(int $id)
    {
        $invoice = $this->invoiceModel->find($id);
        if (!$invoice) {
            return redirect()->to('/invoiceIn/data')->with('error', 'Invoice In tidak ditemukan.');
        }
        if (($invoice['status'] ?? '') === 'DIBATALKAN') {
            return redirect()->to('/invoiceIn/data')->with('error', 'Invoice In yang sudah dibatalkan tidak bisa dilunasi.');
        }

        $rules = [
            'bukti_transfer' => [
                'label' => 'Bukti Transfer',
                'rules' => 'uploaded[bukti_transfer]|max_size[bukti_transfer,5120]|ext_in[bukti_transfer,pdf,jpg,jpeg,png]|mime_in[bukti_transfer,application/pdf,image/jpg,image/jpeg,image/png]',
            ],
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $file = $this->request->getFile('bukti_transfer');
        if (!$file || !$file->isValid()) {
            return redirect()->back()->withInput()->with('error', 'File bukti transfer tidak valid.');
        }

        $uploadDir = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'invoice_in_transfer';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $extension = strtolower($file->getClientExtension() ?: $file->getExtension() ?: 'dat');
        $uploadedFileName = bin2hex(random_bytes(16)) . '.' . $extension;

        try {
            $file->move($uploadDir, $uploadedFileName);
            $this->invoiceModel->update($id, [
                'bukti_transfer_file' => $uploadedFileName,
                'bukti_transfer_original_name' => $uploadedFileName,
                'status' => 'Lunas',
                'tanggal_lunas' => date('Y-m-d H:i:s'),
            ]);

            if (!empty($invoice['bukti_transfer_file'])) {
                $oldPath = $uploadDir . DIRECTORY_SEPARATOR . $invoice['bukti_transfer_file'];
                if (is_file($oldPath) && $invoice['bukti_transfer_file'] !== $uploadedFileName) {
                    unlink($oldPath);
                }
            }

            return redirect()->to('/invoiceIn/detail/' . $id)->with('message', 'Bukti transfer berhasil diupload. Invoice In ditandai Lunas.');
        } catch (\Throwable $e) {
            $uploadedPath = $uploadDir . DIRECTORY_SEPARATOR . $uploadedFileName;
            if (is_file($uploadedPath)) {
                unlink($uploadedPath);
            }
            log_message('error', 'Gagal upload bukti transfer Invoice In: {message}', ['message' => $e->getMessage()]);
            return redirect()->back()->withInput()->with('error', 'Bukti transfer gagal diupload. ' . $e->getMessage());
        }
    }

    public function file(int $id)
    {
        $invoice = $this->invoiceModel->find($id);
        if (!$invoice || empty($invoice['invoice_file'])) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('File invoice tidak ditemukan.');
        }

        $path = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'invoice_in' . DIRECTORY_SEPARATOR . $invoice['invoice_file'];
        if (!is_file($path)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('File invoice tidak ditemukan.');
        }

        return $this->response
            ->download($path, null)
            ->setFileName($invoice['invoice_original_name'] ?: $invoice['invoice_file']);
    }

    public function buktiTransfer(int $id)
    {
        $invoice = $this->invoiceModel->find($id);
        if (!$invoice || empty($invoice['bukti_transfer_file'])) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Bukti transfer tidak ditemukan.');
        }

        $path = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'invoice_in_transfer' . DIRECTORY_SEPARATOR . $invoice['bukti_transfer_file'];
        if (!is_file($path)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Bukti transfer tidak ditemukan.');
        }

        return $this->response
            ->download($path, null)
            ->setFileName($invoice['bukti_transfer_original_name'] ?: $invoice['bukti_transfer_file']);
    }

    public function cancel(int $id)
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return redirect()->to('/invoiceIn/data');
        }
        if (!$this->invoiceModel->find($id)) {
            return redirect()->to('/invoiceIn/data')->with('error', 'Invoice In tidak ditemukan.');
        }
        $this->invoiceModel->update($id, ['status' => 'DIBATALKAN']);
        return redirect()->to('/invoiceIn/data')->with('message', 'Invoice In berhasil dibatalkan.');
    }

    public function hapus(int $id)
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return redirect()->to('/invoiceIn/data');
        }

        $invoice = $this->invoiceModel->find($id);
        if (!$invoice) {
            return redirect()->to('/invoiceIn/data')->with('error', 'Invoice In tidak ditemukan.');
        }

        if ($invoice['status'] !== 'DIBATALKAN') {
            return redirect()->to('/invoiceIn/data')->with('error', 'Invoice In hanya bisa dihapus setelah dibatalkan.');
        }

        $this->db->transStart();
        $this->detailModel->where('invoice_id', $id)->delete();
        $this->invoiceModel->delete($id);
        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return redirect()->to('/invoiceIn/data')->with('error', 'Invoice In gagal dihapus.');
        }

        $uploadDirInvoice = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'invoice_in';
        if (!empty($invoice['invoice_file'])) {
            $path = $uploadDirInvoice . DIRECTORY_SEPARATOR . $invoice['invoice_file'];
            if (is_file($path)) {
                unlink($path);
            }
        }

        $uploadDirTransfer = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'invoice_in_transfer';
        if (!empty($invoice['bukti_transfer_file'])) {
            $path = $uploadDirTransfer . DIRECTORY_SEPARATOR . $invoice['bukti_transfer_file'];
            if (is_file($path)) {
                unlink($path);
            }
        }

        return redirect()->to('/invoiceIn/data')->with('message', 'Invoice In berhasil dihapus.');
    }

    private function getInvoice(int $id): ?array
    {
        $invoice = $this->invoiceModel->find($id);
        if (!$invoice) return null;
        $invoice['ppn_enabled'] = isset($invoice['ppn_enabled']) ? (int) $invoice['ppn_enabled'] : 1;
        $invoice['ppn_percent'] = (float) ($invoice['ppn_percent'] ?? 11);
        $invoice['pph_enabled'] = isset($invoice['pph_enabled']) ? (int) $invoice['pph_enabled'] : 1;
        $invoice['pph_percent'] = (float) ($invoice['pph_percent'] ?? 2);
        $invoice['dp_enabled'] = (int) ($invoice['dp_enabled'] ?? 0);
        $invoice['dp_percent'] = (float) ($invoice['dp_percent'] ?? 50);
        $invoice['dp_amount'] = (float) ($invoice['dp_amount'] ?? 0);
        return [
            'invoice' => $invoice,
            'details' => $this->detailModel->where('invoice_id', $id)->orderBy('id')->findAll(),
        ];
    }

    private function getSources(): array
    {
        // Supplier "TRE-CKG"/"TRE-CRB" dkk (nama diawali "TRE-") itu bukan
        // vendor luar, tapi label transfer stok antar gudang internal TRE
        // sendiri, jadi tidak perlu (dan tidak boleh) ditagihkan lewat
        // Invoice In.
        // Penerimaan yang sudah terhubung ke PO Keluar (po_keluar_id terisi)
        // tidak ditawarkan satu-satu di sini -- biar tidak kepilih 2x untuk
        // PO yang sama, mereka digabung jadi satu opsi per PO Keluar di
        // bawah (lihat $poKeluarViaPenerimaan), meskipun barangnya datang
        // bertahap/partial lewat beberapa kali Material/Barang Masuk.
        $material = $this->db->table('materialmasuk mm')
            ->select("'material' AS source_type, mm.faktur AS source_no, COALESCE(NULLIF(mm.no_invoice, ''), CONCAT('SJ ', mm.no_do), mm.faktur) AS source_label, mm.tglfaktur AS source_date, mm.idsup AS supplier_id, s.supnama AS supplier_name", false)
            ->join('supplier s', 's.supid = mm.idsup')
            ->where('mm.po_keluar_id IS NULL', null, false)
            ->where("COALESCE(mm.sumber, 'beli') !=", 'adjustment')
            ->where('EXISTS (SELECT 1 FROM detail_materialmasuk dm WHERE dm.detfaktur = mm.faktur)', null, false)
            ->where("NOT EXISTS (SELECT 1 FROM invoice_in ii WHERE ii.source_type = 'material' AND ii.source_no = mm.faktur)", null, false)
            ->where('s.supnama NOT LIKE', 'TRE-%')
            ->get()->getResultArray();
        $produk = $this->db->table('barangmasuk bm')
            ->select("'produk' AS source_type, bm.faktur AS source_no, bm.faktur AS source_label, bm.tglfaktur AS source_date, bm.idsup AS supplier_id, s.supnama AS supplier_name", false)
            ->join('supplier s', 's.supid = bm.idsup')
            ->where('bm.po_keluar_id IS NULL', null, false)
            ->where('EXISTS (SELECT 1 FROM detail_barangmasuk dbm WHERE dbm.detfaktur = bm.faktur)', null, false)
            ->where("NOT EXISTS (SELECT 1 FROM invoice_in ii WHERE ii.source_type = 'produk' AND ii.source_no = bm.faktur)", null, false)
            ->where('s.supnama NOT LIKE', 'TRE-%')
            ->get()->getResultArray();

        // PO Keluar yang ditandai "kirim langsung" (barangnya tidak resmi
        // masuk stok TRE, mis. dikirim terus dari satu vendor ke vendor lain
        // buat diproses) tidak akan pernah punya Barang/Material Masuk --
        // jadi ditagih langsung dari PO Keluar-nya sendiri, bukan dari
        // transaksi penerimaan.
        $poKeluar = $this->db->table('po_keluar pk')
            ->select("'po_keluar' AS source_type, pk.no_po AS source_no, pk.no_po AS source_label, pk.tgl_po AS source_date, pk.idsup AS supplier_id, s.supnama AS supplier_name, pk.kirim_langsung AS kirim_langsung, pk.jenis_po AS jenis_po, pk.jenis_transaksi AS jenis_transaksi", false)
            ->join('supplier s', 's.supid = pk.idsup', 'left')
            ->groupStart()
                ->where('pk.kirim_langsung', 1)
                ->orWhere("(pk.jenis_po = 'jasa' AND COALESCE(pk.jenis_transaksi, 'Beli') != 'Titip Proses')", null, false)
                ->orWhere("EXISTS (SELECT 1 FROM po_keluar child WHERE child.po_asal = pk.no_po AND child.jenis_transaksi = 'Titip Proses' AND child.status = 'AKTIF')", null, false)
            ->groupEnd()
            ->where('pk.status', 'AKTIF')
            ->where("NOT EXISTS (SELECT 1 FROM invoice_in ii WHERE ii.source_type = 'po_keluar' AND ii.source_no = pk.no_po)", null, false)
            ->groupStart()
                ->where('s.supnama IS NULL')
                ->orWhere('s.supnama NOT LIKE', 'TRE-%')
            ->groupEnd()
            ->get()->getResultArray();

        // PO Keluar biasa yang penerimaannya sudah tercatat lewat
        // Material/Barang Masuk ditawarkan satu baris per PO. Detail surat
        // jalan dipilih di langkah berikutnya, seperti Generate Invoice Out.
        $poKeluarViaPenerimaan = $this->db->table('po_keluar pk')
            ->select("'po_keluar' AS source_type, pk.no_po AS source_no, pk.no_po AS source_label, pk.tgl_po AS source_date, pk.idsup AS supplier_id, s.supnama AS supplier_name, pk.kirim_langsung AS kirim_langsung, pk.jenis_po AS jenis_po, pk.jenis_transaksi AS jenis_transaksi", false)
            ->join('supplier s', 's.supid = pk.idsup', 'left')
            ->where('pk.kirim_langsung', 0)
            ->groupStart()
                ->where('pk.jenis_po !=', 'jasa')
                ->orWhere("(pk.jenis_po = 'jasa' AND pk.jenis_transaksi = 'Titip Proses')", null, false)
            ->groupEnd()
            ->where('pk.status', 'AKTIF')
            ->where("(EXISTS (SELECT 1 FROM materialmasuk mm WHERE mm.po_keluar_id = pk.id AND EXISTS (SELECT 1 FROM detail_materialmasuk dm WHERE dm.detfaktur = mm.faktur)) OR EXISTS (SELECT 1 FROM barangmasuk bm WHERE bm.po_keluar_id = pk.id AND EXISTS (SELECT 1 FROM detail_barangmasuk dbm WHERE dbm.detfaktur = bm.faktur)))", null, false)
            ->where("NOT EXISTS (SELECT 1 FROM invoice_in ii WHERE ii.source_type = 'po_keluar' AND ii.source_no = pk.no_po)", null, false)
            ->groupStart()
                ->where('s.supnama IS NULL')
                ->orWhere('s.supnama NOT LIKE', 'TRE-%')
            ->groupEnd()
            ->get()->getResultArray();

        $poKeluarViaPenerimaan = array_values(array_filter(
            $poKeluarViaPenerimaan,
            fn($row) => $this->getPoKeluarShipments((string) $row['source_no']) !== []
        ));

        $rows = array_merge($material, $produk, $poKeluar, $poKeluarViaPenerimaan);
        $uniqueRows = [];
        foreach ($rows as $row) {
            $uniqueRows[$row['source_type'] . '|' . $row['source_no']] = $row;
        }
        $rows = array_values($uniqueRows);
        usort($rows, fn($a, $b) => strcmp((string) $b['source_date'], (string) $a['source_date']));
        return $rows;
    }

    private function splitPoKeluarSourceNo(string $sourceNo): array
    {
        $sourceParts = explode('||', $sourceNo, 2);
        $poNo = $sourceParts[0];
        $fakturs = isset($sourceParts[1])
            ? array_values(array_filter(array_map('trim', explode(',', $sourceParts[1]))))
            : [];

        return [$poNo, array_values(array_unique($fakturs))];
    }

    private function composePoKeluarSourceNo(string $poNo, array $fakturs): string
    {
        $fakturs = array_values(array_unique(array_filter(array_map('strval', $fakturs))));
        sort($fakturs, SORT_NATURAL);

        return $fakturs ? $poNo . '||' . implode(',', $fakturs) : $poNo;
    }

    private function poKeluarShipmentAlreadyInvoiced(string $poNo, string $faktur): bool
    {
        $rows = $this->invoiceModel
            ->select('source_no')
            ->where('source_type', 'po_keluar')
            ->groupStart()
                ->where('source_no', $poNo)
                ->orLike('source_no', $poNo . '||', 'after')
            ->groupEnd()
            ->findAll();

        foreach ($rows as $row) {
            [$invoicePoNo, $invoiceFakturs] = $this->splitPoKeluarSourceNo((string) ($row['source_no'] ?? ''));
            if ($invoicePoNo === $poNo && (!$invoiceFakturs || in_array($faktur, $invoiceFakturs, true))) {
                return true;
            }
        }

        return false;
    }

    private function getPoKeluarShipments(string $poNo): array
    {
        [$poSourceNo] = $this->splitPoKeluarSourceNo($poNo);
        $po = $this->db->table('po_keluar')
            ->select('id')
            ->where('no_po', $poSourceNo)
            ->get()
            ->getRowArray();
        if (!$po) {
            return [];
        }

        $shipments = [];
        $materialLines = $this->db->table('detail_materialmasuk dm')
            ->select("mm.faktur, COALESCE(NULLIF(mm.no_do, ''), mm.faktur) AS no_surat_jalan, mm.tglfaktur, m.matkode AS item_code, m.matnama AS item_name, dm.detjml AS qty, COALESCE(st.satnama, sm.satnama, '-') AS unit", false)
            ->join('materialmasuk mm', 'mm.faktur = dm.detfaktur')
            ->join('material m', 'm.matid = dm.detmatkode')
            ->join('satuan st', 'st.satid = dm.satuan', 'left')
            ->join('satuan sm', 'sm.satid = m.matsatid', 'left')
            ->where('mm.po_keluar_id', $po['id'])
            ->orderBy('mm.tglfaktur', 'ASC')
            ->orderBy('mm.faktur', 'ASC')
            ->get()->getResultArray();

        $produkLines = $this->db->table('detail_barangmasuk dbm')
            ->select("bm.faktur, bm.faktur AS no_surat_jalan, bm.tglfaktur, dbm.detbrgkode AS item_code, dbm.detbrgnama AS item_name, dbm.detjml AS qty, COALESCE(st.satnama, sb.satnama, '-') AS unit", false)
            ->join('barangmasuk bm', 'bm.faktur = dbm.detfaktur')
            ->join('satuan st', 'st.satid = dbm.satuan', 'left')
            ->join('barang b', 'b.brgkode = dbm.detbrgkode', 'left')
            ->join('satuan sb', 'sb.satid = b.brgsatid', 'left')
            ->where('bm.po_keluar_id', $po['id'])
            ->orderBy('bm.tglfaktur', 'ASC')
            ->orderBy('bm.faktur', 'ASC')
            ->get()->getResultArray();

        foreach (array_merge($materialLines, $produkLines) as $line) {
            $faktur = (string) $line['faktur'];
            if ($this->poKeluarShipmentAlreadyInvoiced($poSourceNo, $faktur)) {
                continue;
            }
            if (!isset($shipments[$faktur])) {
                $shipments[$faktur] = [
                    'faktur' => $faktur,
                    'no_surat_jalan' => $line['no_surat_jalan'] ?: $faktur,
                    'tglfaktur' => $line['tglfaktur'],
                    'lines' => [],
                ];
            }
            $shipments[$faktur]['lines'][] = $line;
        }

        return array_values($shipments);
    }

    private function getSourceData(string $type, string $sourceNo): ?array
    {
        if ($type === 'material') {
            $header = $this->db->table('materialmasuk mm')
                ->select("mm.faktur AS source_no, COALESCE(NULLIF(mm.no_invoice, ''), CONCAT('SJ ', mm.no_do), mm.faktur) AS source_label, mm.tglfaktur AS source_date, mm.idsup AS supplier_id, s.supnama AS supplier_name, s.alamat AS supplier_address, s.suptelp AS supplier_phone", false)
                ->join('supplier s', 's.supid = mm.idsup')
                ->where("COALESCE(mm.sumber, 'beli') !=", 'adjustment')
                ->where('mm.faktur', $sourceNo)->get()->getRowArray();
            if (!$header) return null;
            $lines = $this->db->table('detail_materialmasuk dm')
                ->select("CONCAT('m_', dm.id) AS source_detail_id, mm.no_do AS no_surat_jalan, m.matkode AS item_code, m.matnama AS item_name, dm.detjml AS qty, COALESCE(st.satnama, sm.satnama, '-') AS unit", false)
                ->join('materialmasuk mm', 'mm.faktur = dm.detfaktur')
                ->join('material m', 'm.matid = dm.detmatkode')
                ->join('satuan st', 'st.satid = dm.satuan', 'left')
                ->join('satuan sm', 'sm.satid = m.matsatid', 'left')
                ->where('dm.detfaktur', $sourceNo)->get()->getResultArray();
            return ['header' => $header, 'lines' => $lines];
        }
        if ($type === 'produk') {
            $header = $this->db->table('barangmasuk bm')
                ->select('bm.faktur AS source_no, bm.faktur AS source_label, bm.tglfaktur AS source_date, bm.idsup AS supplier_id, s.supnama AS supplier_name, s.alamat AS supplier_address, s.suptelp AS supplier_phone')
                ->join('supplier s', 's.supid = bm.idsup')
                ->where('bm.faktur', $sourceNo)->get()->getRowArray();
            if (!$header) return null;
            $lines = $this->db->table('detail_barangmasuk dbm')
                ->select("CONCAT('p_', dbm.id) AS source_detail_id, '-' AS no_surat_jalan, dbm.detbrgkode AS item_code, dbm.detbrgnama AS item_name, dbm.detjml AS qty, COALESCE(st.satnama, sb.satnama, '-') AS unit", false)
                ->join('satuan st', 'st.satid = dbm.satuan', 'left')
                ->join('barang b', 'b.brgkode = dbm.detbrgkode', 'left')
                ->join('satuan sb', 'sb.satid = b.brgsatid', 'left')
                ->where('dbm.detfaktur', $sourceNo)->get()->getResultArray();
            return ['header' => $header, 'lines' => $lines];
        }
        if ($type === 'po_keluar') {
            [$poSourceNo, $selectedFakturs] = $this->splitPoKeluarSourceNo($sourceNo);
            $fakturSourceNo = $selectedFakturs[0] ?? null;
            $header = $this->db->table('po_keluar pk')
                ->select('pk.id AS po_id, pk.no_po AS source_no, pk.no_po AS source_label, pk.tgl_po AS source_date, pk.idsup AS supplier_id, s.supnama AS supplier_name, s.alamat AS supplier_address, s.suptelp AS supplier_phone, pk.kirim_langsung, pk.jenis_po, pk.jenis_transaksi')
                ->join('supplier s', 's.supid = pk.idsup', 'left')
                ->where('pk.no_po', $poSourceNo)
                ->get()->getRowArray();
            if (!$header) return null;

            $isTitipProses = strcasecmp((string) ($header['jenis_transaksi'] ?? 'Beli'), 'Titip Proses') === 0;

            $punyaTitipProsesTurunan = $this->db->table('po_keluar')
                ->where('po_asal', $poSourceNo)
                ->where('jenis_transaksi', 'Titip Proses')
                ->where('status', 'AKTIF')
                ->countAllResults() > 0;

            if ($fakturSourceNo === null && ((int) $header['kirim_langsung'] === 1 || $punyaTitipProsesTurunan || (($header['jenis_po'] ?? '') === 'jasa' && !$isTitipProses))) {
                $lines = $this->db->table('detail_po_keluar')
                    ->select("CONCAT('k_', id) AS source_detail_id, '-' AS no_surat_jalan, kode_item AS item_code, nama_item AS item_name, qty_pesan AS qty, COALESCE(satuan, '-') AS unit, COALESCE(harga, 0) AS unit_price_default", false)
                    ->where('no_po', $poSourceNo)
                    ->get()->getResultArray();
            } else {
                if (!$selectedFakturs) {
                    return ['header' => $header, 'lines' => []];
                }

                // Digabung dari semua Material/Barang Masuk yang terhubung ke
                // PO Keluar ini, walau barangnya diterima bertahap/partial --
                // no_surat_jalan dipakai buat lihat item ini datang dari
                // pengiriman yang mana. Harga satuan diambil dari
                // detail_po_keluar (sudah diisi user waktu bikin PO) supaya
                // tidak perlu input ulang. Catatan: kode_item di
                // detail_po_keluar menyimpan matid untuk tipe_item=material
                // (bukan matkode), jadi dijodohkan ke detmatkode yang juga
                // matid -- lihat PoKeluar::simpan().
                $materialLines = $this->db->table('detail_materialmasuk dm')
                    ->select("CONCAT('m_', dm.id) AS source_detail_id, mm.no_do AS no_surat_jalan, m.matkode AS item_code, m.matnama AS item_name, dm.detjml AS qty, COALESCE(st.satnama, sm.satnama, '-') AS unit, COALESCE(dpk.harga, 0) AS unit_price_default", false)
                    ->join('materialmasuk mm', 'mm.faktur = dm.detfaktur')
                    ->join('material m', 'm.matid = dm.detmatkode')
                    ->join('satuan st', 'st.satid = dm.satuan', 'left')
                    ->join('satuan sm', 'sm.satid = m.matsatid', 'left')
                    ->join("detail_po_keluar dpk", "dpk.po_keluar_id = mm.po_keluar_id AND dpk.tipe_item = 'material' AND dpk.kode_item = dm.detmatkode", 'left')
                    ->where('mm.po_keluar_id', $header['po_id'])
                    ->whereIn('mm.faktur', $selectedFakturs)
                    ->get()->getResultArray();
                $produkLines = $this->db->table('detail_barangmasuk dbm')
                    ->select("CONCAT('p_', dbm.id) AS source_detail_id, '-' AS no_surat_jalan, dbm.detbrgkode AS item_code, dbm.detbrgnama AS item_name, dbm.detjml AS qty, COALESCE(st.satnama, sb.satnama, '-') AS unit, COALESCE(dpk.harga, 0) AS unit_price_default", false)
                    ->join('barangmasuk bm', 'bm.faktur = dbm.detfaktur')
                    ->join('satuan st', 'st.satid = dbm.satuan', 'left')
                    ->join('barang b', 'b.brgkode = dbm.detbrgkode', 'left')
                    ->join('satuan sb', 'sb.satid = b.brgsatid', 'left')
                    ->join("detail_po_keluar dpk", "dpk.po_keluar_id = bm.po_keluar_id AND dpk.tipe_item = 'produk' AND dpk.kode_item = dbm.detbrgkode", 'left')
                    ->where('bm.po_keluar_id', $header['po_id'])
                    ->whereIn('bm.faktur', $selectedFakturs)
                    ->get()->getResultArray();
                $lines = array_merge($materialLines, $produkLines);
            }

            return ['header' => $header, 'lines' => $lines];
        }
        return null;
    }
}
