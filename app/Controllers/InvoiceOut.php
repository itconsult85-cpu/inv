<?php

namespace App\Controllers;

use App\Models\ModelInvoiceOut;
use App\Models\ModelInvoiceOutDetail;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class InvoiceOut extends BaseController
{
    private $db;
    private ModelInvoiceOut $invoiceModel;
    private ModelInvoiceOutDetail $detailModel;

    public function __construct()
    {
        $this->db = db_connect();
        $this->invoiceModel = new ModelInvoiceOut();
        $this->detailModel = new ModelInvoiceOutDetail();
        $this->ensureInvoiceOutPrintColumns();
        $this->ensurePoMigrationInvoiceColumns();
    }

    public function data()
    {
        $this->syncInvoicePaymentStatuses();

        $invoices = $this->invoiceModel
            ->orderBy('invoice_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll();

        return view('invoiceout/index', ['invoices' => $invoices]);
    }

    public function create(?string $poHash = null)
    {
        $poNo = null;
        if ($poHash) {
            $po = $this->db->table('po')
                // Nilai hash tetap harus di-escape. Parameter false sebelumnya
                // membuat hash dibaca MySQL sebagai nama kolom.
                ->where('SHA1(nopo)', $poHash)
                ->get()->getRowArray();
            $poNo = $po['nopo'] ?? null;
        }

        $shipments = $poNo ? $this->getShipmentsForPo($poNo) : [];

        $selectedFakturs = array_values(array_filter(array_map(
            'strval',
            $this->request->getGet('faktur') ?? []
        )));

        $invoiceData = ($poNo && $selectedFakturs)
            ? $this->getInvoiceLinesForShipments($poNo, $selectedFakturs)
            : null;

        return view('invoiceout/form', [
            'candidates' => $this->getPoCandidates(),
            'shipments' => $shipments,
            'selectedFakturs' => $selectedFakturs,
            'invoiceData' => $invoiceData,
            'selectedPo' => $poNo,
        ]);
    }

    public function save()
    {
        $rules = [
            'invoice_no' => 'required|max_length[100]',
            'invoice_date' => 'required|valid_date[Y-m-d]',
            'po_no' => 'required|max_length[255]',
            'signer_name' => 'required|max_length[100]',
            'signer_position' => 'required|max_length[100]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $invoiceNo = trim((string) $this->request->getPost('invoice_no'));
        $poNo = (string) $this->request->getPost('po_no');
        $fakturs = array_values(array_filter(array_map(
            'strval',
            $this->request->getPost('faktur') ?? []
        )));

        if (!$fakturs) {
            return redirect()->back()->withInput()->with('error', 'Pilih minimal satu No. Surat Jalan yang akan ditagihkan.');
        }

        if ($this->invoiceModel->where('invoice_no', $invoiceNo)->first()) {
            return redirect()->back()->withInput()->with('error', 'Nomor Invoice Out sudah digunakan.');
        }

        // Qty selalu dihitung ulang di server dari data surat jalan yang
        // dipilih (bukan dari nilai yang dikirim browser), supaya tidak bisa
        // dimanipulasi dan selalu sinkron dengan sisa qty terbaru. Harga per
        // baris defaultnya juga dari server (PO/master produk), tapi boleh
        // ditimpa manual lewat input "harga[<line_key>]" di form.
        $data = $this->getInvoiceLinesForShipments($poNo, $fakturs);
        if (!$data || !$data['lines']) {
            return redirect()->back()->withInput()->with('error', 'Tidak ada qty terkirim yang belum ditagihkan untuk surat jalan yang dipilih.');
        }

        $hargaOverrides = (array) $this->request->getPost('harga');
        foreach ($data['lines'] as &$line) {
            $rowKey = $line['source_no'] . '||' . $line['product_code'];
            if (isset($hargaOverrides[$rowKey]) && is_numeric($hargaOverrides[$rowKey]) && (float) $hargaOverrides[$rowKey] > 0) {
                $line['unit_price'] = (float) $hargaOverrides[$rowKey];
                $line['amount'] = $line['qty_invoice'] * $line['unit_price'];
            }
        }
        unset($line);

        foreach ($data['lines'] as $line) {
            if ((float) $line['unit_price'] <= 0) {
                return redirect()->back()->withInput()->with(
                    'error',
                    'Harga produk ' . $line['product_code'] . ' belum diisi. Isi manual pada kolom Harga.'
                );
            }
        }

        $subtotal = array_sum(array_column($data['lines'], 'amount'));

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
        $dpAmount = $dpEnabled ? round(($subtotal + $ppn) * ($dpPercent / 100), 2) : 0;
        $grandTotal = max(($subtotal + $ppn) - $pph23 - $dpAmount, 0);
        $customerProfile = $this->customerProfile($data['po']);
        $bankDefault = $this->defaultBankInfo();

        $this->db->transBegin();
        try {
            $invoiceId = $this->invoiceModel->insert([
                'invoice_no' => $invoiceNo,
                'invoice_date' => $this->request->getPost('invoice_date'),
                'po_no' => $data['po']['nopo'],
                'po_date' => $data['po']['tglpo'],
                'customer_id' => $data['po']['pelid'],
                'customer_name' => $data['po']['pelnama'],
                'customer_address' => $customerProfile['address'],
                'customer_phone' => $customerProfile['phone'],
                'customer_fax' => $customerProfile['fax'],
                'customer_to' => $customerProfile['to'],
                'signer_name' => trim((string) $this->request->getPost('signer_name')),
                'signer_position' => trim((string) $this->request->getPost('signer_position')),
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
                'paid_total' => 0,
                'bank_owner' => trim((string) $this->request->getPost('bank_owner')) ?: $bankDefault['owner'],
                'bank_name' => trim((string) $this->request->getPost('bank_name')) ?: $bankDefault['name'],
                'bank_account' => trim((string) $this->request->getPost('bank_account')) ?: $bankDefault['account'],
                'bank_npwp' => trim((string) $this->request->getPost('bank_npwp')) ?: $bankDefault['npwp'],
                'status' => 'AKTIF',
                'status_bayar' => 'Belum Lunas',
                'created_by' => session()->get('namauser'),
            ], true);

            if (!$invoiceId) {
                throw new \RuntimeException('Header invoice gagal disimpan.');
            }

            $details = [];
            $materialCostCache = [];
            foreach ($data['lines'] as $line) {
                $kodeProduk = (string) $line['product_code'];
                if (!isset($materialCostCache[$kodeProduk])) {
                    $materialCostCache[$kodeProduk] = $this->hargaMaterialPerPcs($kodeProduk);
                }
                $materialCost = $materialCostCache[$kodeProduk];

                $details[] = [
                    'invoice_id' => $invoiceId,
                    'product_code' => $kodeProduk,
                    'source_no' => $line['source_no'],
                    'status' => 'AKTIF',
                    'product_name' => $line['product_name'],
                    'qty' => $line['qty_invoice'],
                    'unit' => $line['unit'],
                    'unit_price' => $line['unit_price'],
                    'amount' => $line['amount'],
                    'material_cost_snapshot' => $materialCost['total'],
                    'material_cost_detail_snapshot' => $materialCost['detail'],
                    'material_cost_complete_snapshot' => $materialCost['lengkap'] ? 1 : 0,
                    'created_at' => date('Y-m-d H:i:s'),
                ];
            }
            if (!$this->detailModel->insertBatch($details)) {
                throw new \RuntimeException('Detail invoice gagal disimpan.');
            }

            if (!$this->db->transStatus()) {
                throw new \RuntimeException('Transaksi database gagal.');
            }
            $this->db->transCommit();

            return redirect()->to('/invoiceOut/detail/' . $invoiceId)
                ->with('message', 'Invoice Out berhasil dibuat.');
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Gagal menyimpan Invoice Out: {message}', ['message' => $e->getMessage()]);

            $pesan = str_contains($e->getMessage(), 'unique_guard') || str_contains(strtolower($e->getMessage()), 'duplicate')
                ? 'Sebagian item pada surat jalan yang dipilih baru saja ditagihkan lewat invoice lain. Silakan muat ulang halaman ini.'
                : 'Invoice Out gagal disimpan. ' . $e->getMessage();

            return redirect()->back()->withInput()->with('error', $pesan);
        }
    }

    public function detail(int $id)
    {
        $data = $this->getInvoice($id);
        if (!$data) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Invoice Out tidak ditemukan.');
        }
        return view('invoiceout/detail', $data);
    }

    public function cetak(int $id)
    {
        $data = $this->getInvoice($id);
        if (!$data) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Invoice Out tidak ditemukan.');
        }
        $data['terbilang'] = $this->terbilang((int) round($data['invoice']['grand_total'])) . ' Rupiah';
        $data['details'] = $this->gabungkanDetailUntukCetak($data['details']);
        return view('invoiceout/cetak', $data);
    }

    /**
     * Versi Excel dari invoice cetak -- layoutnya niru invoiceout/cetak.php
     * (header, delivered to, tabel item, totals, terbilang, bank, tanda
     * tangan) tapi dalam bentuk sheet asli, bukan cuma dump data mentah.
     */
    public function cetakExcel(int $id)
    {
        $data = $this->getInvoice($id);
        if (!$data) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Invoice Out tidak ditemukan.');
        }
        $invoice = $data['invoice'];
        $details = $this->gabungkanDetailUntukCetak($data['details']);
        $terbilang = trim(preg_replace('/\s+/', ' ', $this->terbilang((int) round($invoice['grand_total'])) . ' Rupiah'));

        $spreadsheet = \App\Libraries\InvoiceOutExcel::build($invoice, $details, $terbilang);
        $filename = 'Invoice_' . preg_replace('/[^A-Za-z0-9_-]+/', '_', $invoice['invoice_no']) . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }

    /**
     * Halaman cetak nggak nampilin kolom No Surat Jalan (customer nggak
     * perlu tau itu), jadi item yang sama (kode+nama+satuan+harga sama)
     * digabung jadi 1 baris dengan qty & amount dijumlah -- biar nggak
     * keliatan produk yang sama berulang-ulang cuma karena kekirim lewat
     * beberapa surat jalan berbeda. Layar "Detail Invoice" tetap pecah per
     * surat jalan seperti biasa, ini cuma buat tampilan cetaknya aja.
     */
    private function gabungkanDetailUntukCetak(array $details): array
    {
        $grouped = [];
        foreach ($details as $detail) {
            $key = implode('|', [
                $detail['product_code'] ?? '',
                $detail['product_name'] ?? '',
                $detail['unit'] ?? '',
                (string) ($detail['unit_price'] ?? 0),
            ]);

            if (!isset($grouped[$key])) {
                $grouped[$key] = $detail;
            } else {
                $grouped[$key]['qty'] += $detail['qty'];
                $grouped[$key]['amount'] += $detail['amount'];
            }
        }

        return array_values($grouped);
    }

    public function cancel(int $id)
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return redirect()->to('/invoiceOut/data');
        }
        $invoice = $this->invoiceModel->find($id);
        if (!$invoice) {
            return redirect()->to('/invoiceOut/data')->with('error', 'Invoice Out tidak ditemukan.');
        }
        $this->invoiceModel->update($id, ['status' => 'DIBATALKAN']);
        $this->detailModel->where('invoice_id', $id)->set(['status' => 'DIBATALKAN'])->update();
        return redirect()->to('/invoiceOut/data')->with('message', 'Invoice Out berhasil dibatalkan.');
    }

    public function hapus(int $id)
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return redirect()->to('/invoiceOut/data');
        }

        $invoice = $this->invoiceModel->find($id);
        if (!$invoice) {
            return redirect()->to('/invoiceOut/data')->with('error', 'Invoice Out tidak ditemukan.');
        }

        if (($invoice['status'] ?? '') !== 'DIBATALKAN') {
            return redirect()->to('/invoiceOut/data')->with('error', 'Hanya Invoice Out yang sudah dibatalkan yang bisa dihapus.');
        }

        $this->db->transBegin();
        try {
            if ($this->db->tableExists('invoice_out_payment_detail')) {
                $this->db->table('invoice_out_payment_detail')->where('invoice_out_id', $id)->delete();
            }

            $this->detailModel->where('invoice_id', $id)->delete();
            $this->invoiceModel->delete($id);

            if (!$this->db->transStatus()) {
                throw new \RuntimeException('Transaksi database gagal.');
            }

            $this->db->transCommit();
            return redirect()->to('/invoiceOut/data')->with('message', 'Invoice Out yang dibatalkan berhasil dihapus.');
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Gagal menghapus Invoice Out: {message}', ['message' => $e->getMessage()]);
            return redirect()->to('/invoiceOut/data')->with('error', 'Invoice Out gagal dihapus. ' . $e->getMessage());
        }
    }

    public function tandaiLunas(int $id)
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return redirect()->to('/invoiceOut/data');
        }
        $invoice = $this->invoiceModel->find($id);
        if (!$invoice) {
            return redirect()->back()->with('error', 'Invoice Out tidak ditemukan.');
        }
        $this->invoiceModel->update($id, [
            'paid_total' => (float) $invoice['grand_total'],
            'status_bayar' => 'Lunas',
            'tanggal_lunas' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->back()->with('message', 'Invoice Out ditandai Lunas.');
    }

    public function pembayaran()
    {
        $this->syncInvoicePaymentStatuses();

        $customerId = $this->request->getGet('customer_id');
        $customers = $this->unpaidCustomers();
        $invoices = [];
        $selectedCustomer = null;

        if ($customerId !== null && $customerId !== '') {
            foreach ($customers as $customer) {
                if ((string) $customer['customer_id'] === (string) $customerId) {
                    $selectedCustomer = $customer;
                    break;
                }
            }

            $invoices = $this->unpaidInvoicesByCustomer((string) $customerId);
        }

        return view('invoiceout/pembayaran', [
            'customers' => $customers,
            'selectedCustomerId' => $customerId,
            'selectedCustomer' => $selectedCustomer,
            'invoices' => $invoices,
        ]);
    }

    public function simpanPembayaran()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return redirect()->to('/invoiceOut/pembayaran');
        }

        $rules = [
            'payment_date' => 'required|valid_date[Y-m-d]',
            'customer_id' => 'required',
            'amount' => 'required|numeric',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $customerId = (string) $this->request->getPost('customer_id');
        $amount = (float) str_replace(',', '.', (string) $this->request->getPost('amount'));
        $allocations = (array) $this->request->getPost('alokasi');
        $invoiceIds = array_keys($allocations);

        if ($amount <= 0) {
            return redirect()->back()->withInput()->with('error', 'Nominal pembayaran harus lebih dari 0.');
        }

        if (empty($invoiceIds)) {
            return redirect()->back()->withInput()->with('error', 'Pilih minimal satu invoice yang dibayar.');
        }

        $invoices = $this->db->table('invoice_out')
            ->whereIn('id', $invoiceIds)
            ->where('status', 'AKTIF')
            ->where('customer_id', $customerId)
            ->orderBy('invoice_date', 'ASC')
            ->get()
            ->getResultArray();

        if (empty($invoices)) {
            return redirect()->back()->withInput()->with('error', 'Invoice yang dipilih tidak valid.');
        }

        $rowsById = [];
        foreach ($invoices as $invoice) {
            $rowsById[(int) $invoice['id']] = $invoice;
        }

        $details = [];
        $allocatedTotal = 0.0;
        foreach ($allocations as $invoiceId => $allocationValue) {
            $invoiceId = (int) $invoiceId;
            if (!isset($rowsById[$invoiceId])) {
                continue;
            }

            $allocation = (float) str_replace(',', '.', (string) $allocationValue);
            if ($allocation <= 0) {
                continue;
            }

            $remaining = max((float) $rowsById[$invoiceId]['grand_total'] - (float) ($rowsById[$invoiceId]['paid_total'] ?? 0), 0);
            if ($allocation - $remaining > 0.01) {
                return redirect()->back()->withInput()->with('error', 'Alokasi pembayaran melebihi sisa tagihan invoice ' . $rowsById[$invoiceId]['invoice_no'] . '.');
            }

            $details[] = [
                'invoice' => $rowsById[$invoiceId],
                'amount' => $allocation,
            ];
            $allocatedTotal += $allocation;
        }

        if (empty($details)) {
            return redirect()->back()->withInput()->with('error', 'Isi alokasi pembayaran minimal pada satu invoice.');
        }

        if (abs($allocatedTotal - $amount) > 0.01) {
            return redirect()->back()->withInput()->with('error', 'Total alokasi harus sama dengan nominal pembayaran.');
        }

        $customerName = (string) ($details[0]['invoice']['customer_name'] ?? '-');
        $paymentNo = trim((string) $this->request->getPost('payment_no'));
        if ($paymentNo === '') {
            $paymentNo = $this->generatePaymentNo();
        }

        if ($this->db->table('invoice_out_payment')->where('payment_no', $paymentNo)->countAllResults() > 0) {
            return redirect()->back()->withInput()->with('error', 'No. pembayaran/bukti sudah digunakan.');
        }

        $this->db->transBegin();
        try {
            $paymentData = [
                'payment_no' => $paymentNo,
                'payment_date' => $this->request->getPost('payment_date'),
                'customer_id' => is_numeric($customerId) ? (int) $customerId : null,
                'customer_name' => $customerName,
                'amount' => $allocatedTotal,
                'note' => trim((string) $this->request->getPost('note')),
                'created_by' => session()->get('namauser'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            $this->db->table('invoice_out_payment')->insert($paymentData);
            $paymentId = (int) $this->db->insertID();

            foreach ($details as $detail) {
                $invoice = $detail['invoice'];
                $this->db->table('invoice_out_payment_detail')->insert([
                    'payment_id' => $paymentId,
                    'invoice_out_id' => (int) $invoice['id'],
                    'invoice_no' => $invoice['invoice_no'],
                    'allocated_amount' => $detail['amount'],
                    'created_at' => date('Y-m-d H:i:s'),
                ]);

                $paidTotal = (float) ($invoice['paid_total'] ?? 0) + $detail['amount'];
                $this->updateInvoicePaymentStatus((int) $invoice['id'], $paidTotal, (float) $invoice['grand_total']);
            }

            if (!$this->db->transStatus()) {
                throw new \RuntimeException('Transaksi database gagal.');
            }

            $this->db->transCommit();

            return redirect()->to('/invoiceOut/data')->with('message', 'Pembayaran Invoice Out berhasil dicatat.');
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Gagal menyimpan pembayaran Invoice Out: {message}', ['message' => $e->getMessage()]);

            return redirect()->back()->withInput()->with('error', 'Pembayaran gagal disimpan. ' . $e->getMessage());
        }
    }

    private function getInvoice(int $id): ?array
    {
        $invoice = $this->invoiceModel->find($id);
        if (!$invoice) {
            return null;
        }
        // Invoice lama (sebelum kolom customer_address/fax/to ada di invoice_out)
        // fallback ke data master Pelanggan yang aktif sekarang.
        $pelangganAktif = !empty($invoice['customer_id'])
            ? $this->db->table('pelanggan')->where('pelid', $invoice['customer_id'])->get()->getRowArray()
            : null;
        $profile = $this->customerProfile($pelangganAktif ?: [
            'pelnama' => $invoice['customer_name'],
            'peltelp' => $invoice['customer_phone'],
        ]);
        $invoice['customer_address'] = $invoice['customer_address'] ?: $profile['address'];
        $invoice['customer_phone'] = $invoice['customer_phone'] ?: $profile['phone'];
        $invoice['customer_fax'] = $invoice['customer_fax'] ?: $profile['fax'];
        $invoice['customer_to'] = $invoice['customer_to'] ?: $profile['to'];
        $invoice['signer_name'] = $invoice['signer_name'] ?: 'PUJIONO';
        $invoice['signer_position'] = $invoice['signer_position'] ?: 'Direktur';
        $bankDefault = $this->defaultBankInfo();
        $invoice['ppn_enabled'] = isset($invoice['ppn_enabled']) ? (int) $invoice['ppn_enabled'] : 1;
        $invoice['ppn_percent'] = (float) ($invoice['ppn_percent'] ?? 11);
        $invoice['pph_enabled'] = isset($invoice['pph_enabled']) ? (int) $invoice['pph_enabled'] : 1;
        $invoice['pph_percent'] = (float) ($invoice['pph_percent'] ?? 2);
        $invoice['dp_enabled'] = (int) ($invoice['dp_enabled'] ?? 0);
        $invoice['dp_percent'] = (float) ($invoice['dp_percent'] ?? 50);
        $invoice['dp_amount'] = (float) ($invoice['dp_amount'] ?? 0);
        $invoice['bank_owner'] = $invoice['bank_owner'] ?: $bankDefault['owner'];
        $invoice['bank_name'] = $invoice['bank_name'] ?: $bankDefault['name'];
        $invoice['bank_account'] = $invoice['bank_account'] ?: $bankDefault['account'];
        $invoice['bank_npwp'] = $invoice['bank_npwp'] ?: $bankDefault['npwp'];
        return [
            'invoice' => $invoice,
            'details' => $this->detailModel->where('invoice_id', $id)->orderBy('id')->findAll(),
            'payments' => $this->invoicePayments($id),
        ];
    }

    private function getPoCandidates(): array
    {
        $rows = $this->db->table('po p')
            ->select('p.nopo, p.tglpo, p.idpel, pl.pelnama')
            ->join('pelanggan pl', 'pl.pelid = p.idpel')
            ->groupStart()
                ->where('EXISTS (SELECT 1 FROM detail_barangkeluar dk WHERE dk.detpo = p.nopo)', null, false)
                ->orWhere('EXISTS (SELECT 1 FROM detail_po dp WHERE dp.detnopo = p.nopo AND (COALESCE(dp.detkirim_awal, 0) * COALESCE(dp.detharga / NULLIF(dp.detqty, 0), 0)) > COALESCE(dp.detinvoice_awal, 0))', null, false)
            ->groupEnd()
            ->orderBy('p.tglpo', 'DESC')
            ->get()->getResultArray();

        return array_values(array_filter($rows, function ($row) {
            return count($this->getShipmentsForPo($row['nopo'])) > 0;
        }));
    }

    /**
     * Alamat/telp/fax/to selalu diambil dari master data Pelanggan (pelalamat,
     * peltelp, pelfax, pelto) -- kalau kosong ditampilkan strip "-", tidak ada
     * lagi nilai hardcode per-pelanggan tertentu.
     */
    private function customerProfile(array $customer): array
    {
        $address = trim((string) ($customer['pelalamat'] ?? ''));
        $phone = trim((string) ($customer['peltelp'] ?? ''));
        $fax = trim((string) ($customer['pelfax'] ?? ''));
        $to = trim((string) ($customer['pelto'] ?? ''));

        return [
            'address' => $address !== '' ? $address : '-',
            'phone' => $phone !== '' ? $phone : '-',
            'fax' => $fax !== '' ? $fax : '-',
            'to' => $to !== '' ? $to : '-',
        ];
    }

    private function defaultBankInfo(): array
    {
        return [
            'owner' => 'TRISENTOSA RAYA ESOLUSI',
            'name' => 'Maybank Kantor Cabang Bukit Indah',
            'account' => '2-784-001326',
            'npwp' => '076.249.385.6-014.000',
        ];
    }

    private function ensureInvoiceOutPrintColumns(): void
    {
        if (!$this->db->tableExists('invoice_out')) {
            return;
        }

        $forge = \Config\Database::forge();
        $columns = [
            'ppn_enabled' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'after' => 'subtotal'],
            'ppn_percent' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 11, 'after' => 'ppn_enabled'],
            'pph_enabled' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'after' => 'pph23'],
            'pph_percent' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 2, 'after' => 'pph_enabled'],
            'dp_enabled' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'after' => 'pph_percent'],
            'dp_percent' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 50, 'after' => 'dp_enabled'],
            'dp_amount' => ['type' => 'DECIMAL', 'constraint' => '18,2', 'default' => 0, 'after' => 'dp_percent'],
            'bank_owner' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true, 'after' => 'paid_total'],
            'bank_name' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true, 'after' => 'bank_owner'],
            'bank_account' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'bank_name'],
            'bank_npwp' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'bank_account'],
        ];

        foreach ($columns as $name => $definition) {
            if (!$this->db->fieldExists($name, 'invoice_out')) {
                $forge->addColumn('invoice_out', [$name => $definition]);
            }
        }

        if ($this->db->tableExists('invoice_out_detail')) {
            $detailColumns = [
                'material_cost_snapshot' => ['type' => 'DECIMAL', 'constraint' => '18,2', 'null' => true, 'after' => 'amount'],
                'material_cost_detail_snapshot' => ['type' => 'TEXT', 'null' => true, 'after' => 'material_cost_snapshot'],
                'material_cost_complete_snapshot' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'material_cost_detail_snapshot'],
            ];

            foreach ($detailColumns as $name => $definition) {
                if (!$this->db->fieldExists($name, 'invoice_out_detail')) {
                    $forge->addColumn('invoice_out_detail', [$name => $definition]);
                }
            }
        }
    }

    private function ensurePoMigrationInvoiceColumns(): void
    {
        $forge = \Config\Database::forge();

        if ($this->db->tableExists('detail_po') && !$this->db->fieldExists('detinvoice_awal', 'detail_po')) {
            $forge->addColumn('detail_po', [
                'detinvoice_awal' => [
                    'type' => 'DOUBLE',
                    'default' => 0,
                    'after' => 'detkirim_awal',
                ],
            ]);
        }

        if ($this->db->tableExists('temp_po') && !$this->db->fieldExists('detinvoice_awal', 'temp_po')) {
            $forge->addColumn('temp_po', [
                'detinvoice_awal' => [
                    'type' => 'DOUBLE',
                    'default' => 0,
                    'after' => 'detkirim_awal',
                ],
            ]);
        }
    }

    private function hargaMaterialPerPcs(string $kodeProduk): array
    {
        $materials = $this->db->table('berat_material bm')
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
        $row = $this->db->table('invoice_in_detail iid')
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

    private function getPoHeader(string $poNo): ?array
    {
        return $this->db->table('po p')
            ->select('p.nopo, p.tglpo, p.idpel AS pelid, pl.pelnama, pl.peltelp, pl.pelemail, pl.pelalamat, pl.pelfax, pl.pelto')
            ->join('pelanggan pl', 'pl.pelid = p.idpel')
            ->where('p.nopo', $poNo)
            ->get()->getRowArray() ?: null;
    }

    /**
     * Qty terkirim per surat jalan (barangkeluar.faktur) + produk untuk satu PO,
     * dikurangi qty yang sudah pernah ditagihkan dari surat jalan yang sama
     * (dilacak lewat invoice_out_detail.source_no). Baris invoice_out_detail
     * lama (sebelum kolom source_no ada, nilainya NULL) tetap dihitung sebagai
     * "sudah ditagihkan" secara agregat PO+produk, supaya qty yang dulu sudah
     * pernah diinvoice tidak bisa tertagih dobel di sini.
     */
    private function shipmentLineRows(string $poNo, ?array $fakturs = null): array
    {
        $params = [$poNo];
        $fakturFilter = '';
        $realFakturs = [];
        $includeMigrasi = !$fakturs;
        if ($fakturs) {
            foreach ($fakturs as $faktur) {
                if ($this->isMigrasiSourceNo((string) $faktur)) {
                    $includeMigrasi = true;
                    continue;
                }
                $realFakturs[] = (string) $faktur;
            }
        }

        if ($realFakturs) {
            $placeholders = implode(',', array_fill(0, count($realFakturs), '?'));
            $fakturFilter = "AND dk.detfaktur IN ($placeholders)";
            array_push($params, ...$realFakturs);
        } elseif ($fakturs && !$realFakturs) {
            $fakturFilter = "AND 1 = 0";
        }

        $sql = "SELECT dk.detfaktur AS source_no,
                    bk.tglfaktur AS tglfaktur,
                    dk.detbrgkode AS product_code,
                    COALESCE(MAX(b.brgnama), MAX(dk.namabarang)) AS product_name,
                    COALESCE(MAX(s.satnama), 'Pcs') AS unit,
                    COALESCE(MAX(b.harga), MAX(dp.detharga / NULLIF(dp.detqty, 0)), 0) AS unit_price,
                    SUM(dk.detjml) AS qty_shipped,
                    COALESCE((SELECT SUM(iod.qty) FROM invoice_out_detail iod
                        INNER JOIN invoice_out io ON io.id = iod.invoice_id
                        WHERE io.status = 'AKTIF' AND iod.product_code = dk.detbrgkode
                          AND (iod.source_no = dk.detfaktur OR (iod.source_no IS NULL AND io.po_no = dk.detpo))), 0) AS qty_invoiced
                FROM detail_barangkeluar dk
                LEFT JOIN barang b ON b.brgkode = dk.detbrgkode
                LEFT JOIN satuan s ON s.satid = b.brgsatid
                LEFT JOIN barangkeluar bk ON bk.faktur = dk.detfaktur
                LEFT JOIN detail_po dp ON dp.detnopo = dk.detpo AND dp.detkodebrg = dk.detbrgkode
                WHERE dk.detpo = ? $fakturFilter
                GROUP BY dk.detfaktur, dk.detbrgkode
                ORDER BY bk.tglfaktur ASC, dk.detfaktur ASC";

        $rows = $this->db->query($sql, $params)->getResultArray();
        if ($includeMigrasi) {
            array_push($rows, ...$this->migrationInvoiceLineRows($poNo));
        }

        $lines = [];
        foreach ($rows as $row) {
            $remaining = max((float) $row['qty_shipped'] - (float) $row['qty_invoiced'], 0);
            if ($remaining <= 0) {
                continue;
            }
            $row['qty_invoice'] = $remaining;
            $row['amount'] = $remaining * (float) $row['unit_price'];
            $lines[] = $row;
        }

        return $lines;
    }

    private function migrationInvoiceLineRows(string $poNo): array
    {
        return $this->db->query(
            "SELECT
                CONCAT('MIGRASI-', dp.id) AS source_no,
                COALESCE(dp.dettglpo, p.tglpo) AS tglfaktur,
                dp.detkodebrg AS product_code,
                COALESCE(NULLIF(dp.namabarang, ''), b.brgnama, dp.detkodebrg) AS product_name,
                COALESCE(s.satnama, 'Pcs') AS unit,
                COALESCE(MAX(b.harga), MAX(dp.detharga / NULLIF(dp.detqty, 0)), 0) AS unit_price,
                GREATEST(
                    COALESCE(dp.detkirim_awal, 0)
                    - COALESCE(
                        COALESCE(dp.detinvoice_awal, 0) / NULLIF(COALESCE(MAX(b.harga), MAX(dp.detharga / NULLIF(dp.detqty, 0)), 0), 0),
                        0
                    ),
                    0
                ) AS qty_shipped,
                COALESCE((
                    SELECT SUM(iod.qty)
                    FROM invoice_out_detail iod
                    INNER JOIN invoice_out io ON io.id = iod.invoice_id
                    WHERE io.status = 'AKTIF'
                      AND io.po_no = dp.detnopo
                      AND iod.product_code = dp.detkodebrg
                      AND iod.source_no = CONCAT('MIGRASI-', dp.id)
                ), 0) AS qty_invoiced
            FROM detail_po dp
            LEFT JOIN po p ON p.nopo = dp.detnopo
            LEFT JOIN barang b ON b.brgkode = dp.detkodebrg
            LEFT JOIN satuan s ON s.satid = b.brgsatid
            WHERE dp.detnopo = ?
              AND COALESCE(dp.detkirim_awal, 0) > 0
            GROUP BY dp.id, p.tglpo, b.brgnama, b.harga, s.satnama
            HAVING qty_shipped > 0
            ORDER BY dp.id ASC",
            [$poNo]
        )->getResultArray();
    }

    private function isMigrasiSourceNo(string $sourceNo): bool
    {
        return str_starts_with($sourceNo, 'MIGRASI-');
    }

    /**
     * Daftar surat jalan (No. Surat Jalan) untuk sebuah PO yang masih punya
     * qty tersisa untuk ditagihkan, dikelompokkan per surat jalan supaya user
     * bisa memilih mau menerbitkan invoice dari surat jalan yang mana saja.
     */
    private function getShipmentsForPo(string $poNo): array
    {
        $shipments = [];
        foreach ($this->shipmentLineRows($poNo) as $line) {
            $faktur = $line['source_no'];
            if (!isset($shipments[$faktur])) {
                $shipments[$faktur] = [
                    'faktur' => $faktur,
                    'tglfaktur' => $line['tglfaktur'],
                    'lines' => [],
                ];
            }
            $shipments[$faktur]['lines'][] = $line;
        }

        return array_values($shipments);
    }

    private function getInvoiceLinesForShipments(string $poNo, array $fakturs): ?array
    {
        $po = $this->getPoHeader($poNo);
        if (!$po) {
            return null;
        }

        return ['po' => $po, 'lines' => $this->shipmentLineRows($poNo, $fakturs)];
    }

    private function unpaidCustomers(): array
    {
        return $this->db->table('invoice_out')
            ->select('customer_id, customer_name, COUNT(*) AS total_invoice, SUM(grand_total - COALESCE(paid_total, 0)) AS total_sisa', false)
            ->where('status', 'AKTIF')
            ->where('grand_total > COALESCE(paid_total, 0)', null, false)
            ->groupBy('customer_id, customer_name')
            ->orderBy('customer_name', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function invoicePayments(int $invoiceId): array
    {
        if (!$this->db->tableExists('invoice_out_payment_detail')) {
            return [];
        }

        return $this->db->table('invoice_out_payment_detail d')
            ->select('d.allocated_amount, p.payment_no, p.payment_date, p.amount, p.note, p.created_by')
            ->join('invoice_out_payment p', 'p.id = d.payment_id')
            ->where('d.invoice_out_id', $invoiceId)
            ->orderBy('p.payment_date', 'ASC')
            ->orderBy('p.id', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function unpaidInvoicesByCustomer(string $customerId): array
    {
        return $this->db->table('invoice_out')
            ->select('*, (grand_total - COALESCE(paid_total, 0)) AS remaining_total', false)
            ->where('status', 'AKTIF')
            ->where('customer_id', $customerId)
            ->where('grand_total > COALESCE(paid_total, 0)', null, false)
            ->orderBy('invoice_date', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function syncInvoicePaymentStatuses(): void
    {
        if (!$this->db->fieldExists('paid_total', 'invoice_out')) {
            return;
        }

        $invoices = $this->db->table('invoice_out')
            ->select('id, grand_total, paid_total, status_bayar')
            ->where('status', 'AKTIF')
            ->get()
            ->getResultArray();

        foreach ($invoices as $invoice) {
            if (($invoice['status_bayar'] ?? '') === 'Lunas' && (float) ($invoice['paid_total'] ?? 0) <= 0) {
                $this->updateInvoicePaymentStatus((int) $invoice['id'], (float) $invoice['grand_total'], (float) $invoice['grand_total']);
                continue;
            }

            $this->updateInvoicePaymentStatus((int) $invoice['id'], (float) ($invoice['paid_total'] ?? 0), (float) $invoice['grand_total']);
        }
    }

    private function updateInvoicePaymentStatus(int $invoiceId, float $paidTotal, float $grandTotal): void
    {
        $paidTotal = min(max($paidTotal, 0), $grandTotal);
        $status = 'Belum Lunas';
        $current = $this->invoiceModel->find($invoiceId) ?: [];
        $tanggalLunas = $current['tanggal_lunas'] ?? null;

        if ($paidTotal >= $grandTotal - 0.01) {
            $status = 'Lunas';
            $tanggalLunas = $tanggalLunas ?: date('Y-m-d H:i:s');
        } elseif ($paidTotal > 0) {
            $status = 'Dibayar Sebagian';
            $tanggalLunas = null;
        } else {
            $tanggalLunas = null;
        }

        if (
            isset($current['paid_total'], $current['status_bayar'])
            && abs((float) $current['paid_total'] - $paidTotal) < 0.01
            && (string) $current['status_bayar'] === $status
            && (string) ($current['tanggal_lunas'] ?? '') === (string) ($tanggalLunas ?? '')
        ) {
            return;
        }

        $this->invoiceModel->update($invoiceId, [
            'paid_total' => $paidTotal,
            'status_bayar' => $status,
            'tanggal_lunas' => $tanggalLunas,
        ]);
    }

    private function generatePaymentNo(): string
    {
        $prefix = 'PAY-OUT-' . date('Ymd');
        $count = $this->db->table('invoice_out_payment')
            ->like('payment_no', $prefix, 'after')
            ->countAllResults() + 1;

        return $prefix . '-' . str_pad((string) $count, 3, '0', STR_PAD_LEFT);
    }

    private function terbilang(int $nilai): string
    {
        $nilai = abs($nilai);
        $huruf = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
        if ($nilai < 12) return $huruf[$nilai];
        if ($nilai < 20) return $this->terbilang($nilai - 10) . ' Belas';
        if ($nilai < 100) return $this->terbilang(intdiv($nilai, 10)) . ' Puluh ' . $this->terbilang($nilai % 10);
        if ($nilai < 200) return 'Seratus ' . $this->terbilang($nilai - 100);
        if ($nilai < 1000) return $this->terbilang(intdiv($nilai, 100)) . ' Ratus ' . $this->terbilang($nilai % 100);
        if ($nilai < 2000) return 'Seribu ' . $this->terbilang($nilai - 1000);
        if ($nilai < 1000000) return $this->terbilang(intdiv($nilai, 1000)) . ' Ribu ' . $this->terbilang($nilai % 1000);
        if ($nilai < 1000000000) return $this->terbilang(intdiv($nilai, 1000000)) . ' Juta ' . $this->terbilang($nilai % 1000000);
        if ($nilai < 1000000000000) return $this->terbilang(intdiv($nilai, 1000000000)) . ' Miliar ' . $this->terbilang($nilai % 1000000000);
        return $this->terbilang(intdiv($nilai, 1000000000000)) . ' Triliun ' . $this->terbilang($nilai % 1000000000000);
    }
}
