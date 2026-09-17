<?php

namespace App\Controllers;

use App\Models\ModelDetailPoKeluar;
use App\Models\Modelpo;
use App\Models\ModelPoKeluar;
use App\Models\ModelSupplier;

class PoKeluar extends BaseController
{
    private $db;
    private ModelPoKeluar $poModel;
    private ModelDetailPoKeluar $detailModel;

    public function __construct()
    {
        $this->db = db_connect();
        $this->poModel = new ModelPoKeluar();
        $this->detailModel = new ModelDetailPoKeluar();
        $this->ensurePoKeluarJenisPoColumn();
        $this->ensurePoKeluarPrintColumns();
        $this->ensureSumberMaterialColumns();
        $this->ensureMaterialLabelSupplierTable();
    }

    public function data()
    {
        $rows = $this->poModel
            ->orderBy('tgl_po', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll();

        foreach ($rows as &$row) {
            $row['status_penerimaan'] = $this->hitungStatusPenerimaan($row);
        }

        return view('pokeluar/index', ['rows' => $rows]);
    }

    public function input()
    {
        return view('pokeluar/forminput', [
            'suppliers' => (new ModelSupplier())->orderBy('supnama', 'ASC')->findAll(),
            'items' => $this->getItemOptions(),
            'poAktifList' => $this->daftarPoKeluarBelumSelesai(),
            'poMasukAktifList' => $this->daftarPoMasukBelumSelesai(),
        ]);
    }

    public function simpan()
    {
        $rules = [
            'no_po' => 'required|max_length[100]|is_unique[po_keluar.no_po]',
            'tgl_po' => 'required|valid_date[Y-m-d]',
            'idsup' => 'required',
            'jenis_transaksi' => 'required|in_list[Beli,Titip Proses]',
            'jenis_po' => 'required|in_list[produk,jasa,material,habis_pakai]',
            'tipe_item.*' => 'required|in_list[produk,jasa,material,habis_pakai]',
            'kode_item.*' => 'required',
            'qty_pesan.*' => 'required|decimal|greater_than[0]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $tipeItems = (array) $this->request->getPost('tipe_item');
        $kodeItems = (array) $this->request->getPost('kode_item');
        $qtyItems = (array) $this->request->getPost('qty_pesan');
        $hargaItems = (array) $this->request->getPost('harga');
        $printSpecs = (array) $this->request->getPost('print_spec');

        if (!$tipeItems || !$kodeItems) {
            return redirect()->back()->withInput()->with('error', 'Minimal harus ada 1 item PO Keluar.');
        }

        $kombinasiTerpakai = [];
        foreach ($kodeItems as $index => $kode) {
            $tipe = strtolower(trim((string) ($tipeItems[$index] ?? '')));
            $key = $tipe . '|' . $kode;
            if (isset($kombinasiTerpakai[$key])) {
                return redirect()->back()->withInput()->with('error', 'Item yang sama tidak boleh diinput dua kali dalam satu PO Keluar.');
            }
            $kombinasiTerpakai[$key] = true;
        }

        $supplier = (new ModelSupplier())->find($this->request->getPost('idsup'));
        if (!$supplier) {
            return redirect()->back()->withInput()->with('error', 'Supplier tidak ditemukan.');
        }

        $jenisTransaksi = trim((string) $this->request->getPost('jenis_transaksi'));
        $jenisPo = strtolower(trim((string) $this->request->getPost('jenis_po')));
        $sumberMaterialProduksi = ($jenisPo === 'produk' && $this->request->getPost('sumber_material_produksi') === 'vendor')
            ? 'vendor'
            : 'tre';
        $tipeItemWajib = (strcasecmp($jenisTransaksi, 'Titip Proses') === 0 && $jenisPo === 'jasa') ? 'material' : $jenisPo;
        $poAsal = trim((string) $this->request->getPost('po_asal'));
        $kirimLangsung = $this->request->getPost('kirim_langsung') ? 1 : 0;
        $poMasukTerkait = trim((string) $this->request->getPost('po_masuk_terkait'));
        $noPo = trim((string) $this->request->getPost('no_po'));

        if ($poAsal !== '' && $this->db->table('po_keluar')->where('no_po', $poAsal)->countAllResults() === 0) {
            return redirect()->back()->withInput()->with('error', 'PO Asal tidak ditemukan.');
        }

        if ($poAsal !== '' && strcasecmp($poAsal, trim((string) $this->request->getPost('no_po'))) === 0) {
            return redirect()->back()->withInput()->with('error', 'PO Asal tidak boleh sama dengan No. PO ini sendiri.');
        }

        if ($poMasukTerkait !== '' && !(new Modelpo())->find($poMasukTerkait)) {
            return redirect()->back()->withInput()->with('error', 'PO Masuk Terkait tidak ditemukan.');
        }

        $itemMaster = $this->getItemMaster();
        $details = [];
        $totalQty = 0.0;
        $totalNominal = 0.0;

        foreach ($kodeItems as $index => $kode) {
            $tipe = strtolower(trim((string) ($tipeItems[$index] ?? '')));
            if ($tipe !== $tipeItemWajib) {
                return redirect()->back()->withInput()->with('error', 'Item PO harus sesuai dengan jenis transaksi yang dipilih.');
            }

            $key = $tipe . '|' . $kode;
            if (!isset($itemMaster[$key])) {
                return redirect()->back()->withInput()->with('error', 'Item PO Keluar tidak valid.');
            }

            $qty = (float) str_replace(',', '.', (string) ($qtyItems[$index] ?? 0));
            $harga = (float) str_replace(',', '.', (string) ($hargaItems[$index] ?? 0));
            if ($qty <= 0 || $harga < 0) {
                return redirect()->back()->withInput()->with('error', 'Qty harus lebih dari 0 dan harga tidak boleh negatif.');
            }

            $subtotal = $qty * $harga;
            $totalQty += $qty;
            $totalNominal += $subtotal;

            $details[] = [
                'tipe_item' => $tipe,
                'no_po' => $noPo,
                'kode_item' => $kode,
                'nama_item' => $this->resolveNamaItem($itemMaster[$key], (int) $supplier['supid']),
                'print_spec' => trim((string) ($printSpecs[$index] ?? '')),
                'satuan' => $itemMaster[$key]['satuan'],
                'qty_pesan' => $qty,
                'qty_masuk' => 0,
                'harga' => $harga,
                'subtotal' => $subtotal,
                'created_at' => date('Y-m-d H:i:s'),
            ];
        }

        $this->db->transBegin();
        try {
            $top = trim((string) $this->request->getPost('top'));
            $systemPayment = trim((string) $this->request->getPost('system_payment'));
            $shippingTo = trim((string) $this->request->getPost('shipping_to'));
            $quotNumber = trim((string) $this->request->getPost('quot_number'));
            $approvedBy = trim((string) $this->request->getPost('approved_by'));
            $printSpecLabel = trim((string) $this->request->getPost('print_spec_label'));
            $materialColumnEnabled = ($jenisPo === 'produk' && !$this->request->getPost('material_column_enabled')) ? 0 : 1;
            $printNotes = trim((string) $this->request->getPost('print_notes'));
            $discountEnabled = $this->request->getPost('discount_enabled') ? 1 : 0;
            $ppnIncluded = $this->request->getPost('ppn_included') ? 1 : 0;
            $pph23Enabled = $this->request->getPost('pph23_enabled') ? 1 : 0;
            $discountAmount = $discountEnabled ? max(0, (float) str_replace(',', '.', (string) $this->request->getPost('discount_amount'))) : 0;
            $pph23Amount = $pph23Enabled ? round(max($totalNominal - $discountAmount, 0) * 0.02, 2) : 0;

            $poId = $this->poModel->insert([
                'no_po' => $noPo,
                'tgl_po' => $this->request->getPost('tgl_po'),
                'idsup' => $supplier['supid'],
                'supplier_nama' => $supplier['supnama'],
                'keterangan' => trim((string) $this->request->getPost('keterangan')),
                'total_qty' => $totalQty,
                'total_nominal' => $totalNominal,
                'status' => 'AKTIF',
                'jenis_transaksi' => $jenisTransaksi,
                'jenis_po' => $jenisPo,
                'sumber_material_produksi' => $sumberMaterialProduksi,
                'po_asal' => $poAsal !== '' ? $poAsal : null,
                'kirim_langsung' => $kirimLangsung,
                'po_masuk_terkait' => $poMasukTerkait !== '' ? $poMasukTerkait : null,
                'created_by' => session()->get('namauser'),
                'top' => $top !== '' ? $top : null,
                'system_payment' => $systemPayment !== '' ? $systemPayment : null,
                'shipping_to' => $shippingTo !== '' ? $shippingTo : null,
                'quot_number' => $quotNumber !== '' ? $quotNumber : null,
                'approved_by' => $approvedBy !== '' ? $approvedBy : null,
                'print_spec_label' => $printSpecLabel !== '' ? $printSpecLabel : null,
                'material_column_enabled' => $materialColumnEnabled,
                'print_notes' => $printNotes !== '' ? $printNotes : null,
                'discount_enabled' => $discountEnabled,
                'discount_amount' => $discountAmount,
                'ppn_included' => $ppnIncluded,
                'pph23_enabled' => $pph23Enabled,
                'pph23_amount' => $pph23Amount,
            ], true);

            if (!$poId) {
                throw new \RuntimeException('Header PO Keluar gagal disimpan.');
            }

            foreach ($details as &$detail) {
                $detail['po_keluar_id'] = $poId;
                $detail['no_po'] = trim((string) $this->request->getPost('no_po'));
            }

            if (!$this->detailModel->insertBatch($details)) {
                throw new \RuntimeException('Detail PO Keluar gagal disimpan.');
            }

            if (!$this->db->transStatus()) {
                throw new \RuntimeException('Transaksi database gagal.');
            }

            $this->db->transCommit();
            return redirect()->to('/poKeluar/detail/' . $poId)->with('message', 'PO Keluar berhasil disimpan.');
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Gagal menyimpan PO Keluar: {message}', ['message' => $e->getMessage()]);
            return redirect()->back()->withInput()->with('error', 'PO Keluar gagal disimpan. ' . $e->getMessage());
        }
    }

    public function detail(int $id)
    {
        $po = $this->poModel->find($id);
        if (!$po) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('PO Keluar tidak ditemukan.');
        }

        $po['status_penerimaan'] = $this->hitungStatusPenerimaan($po);
        $ngQtyByItem = $this->getNgQtyByPoItem($id);
        $replacementQtyByItem = $this->getReplacementQtyByPoItem($id);
        $details = $this->detailModel->where('po_keluar_id', $id)->orderBy('id', 'ASC')->findAll();
        foreach ($details as &$detail) {
            $itemKey = (string) ($detail['kode_item'] ?? '');
            $detail['qty_ng'] = $ngQtyByItem[$itemKey] ?? 0;
            $detail['qty_pengganti'] = $replacementQtyByItem[$itemKey] ?? 0;
            $detail['qty_masuk_net'] = max(0, (float) $detail['qty_masuk'] - (float) $detail['qty_ng']);
        }
        unset($detail);

        $returMaterial = $this->db->table('retur_material r')
            ->select('r.id, r.nomor_retur, r.material_masuk_faktur, r.tgl_retur, r.catatan, SUM(rd.qty_retur) AS total_qty', false)
            ->join('retur_material_detail rd', 'rd.retur_id = r.id', 'inner')
            ->join('detail_materialmasuk dmm', 'dmm.id = rd.material_masuk_detail_id', 'left')
            ->groupStart()
                ->where('rd.po_keluar_id', $id)
                ->orWhere('dmm.po_keluar_id', $id)
            ->groupEnd()
            ->groupBy('r.id, r.nomor_retur, r.material_masuk_faktur, r.tgl_retur, r.catatan')
            ->orderBy('r.tgl_retur', 'DESC')
            ->get()->getResultArray();

        $returProduk = [];
        if ($this->db->tableExists('retur_produk') && $this->db->tableExists('retur_produk_detail')) {
            $returProduk = $this->db->table('retur_produk r')
                ->select('r.id, r.nomor_retur, r.barang_masuk_faktur, r.tgl_retur, r.catatan, SUM(rd.qty_retur) AS total_qty', false)
                ->join('retur_produk_detail rd', 'rd.retur_id = r.id', 'inner')
                ->where('rd.po_keluar_id', $id)
                ->groupBy('r.id, r.nomor_retur, r.barang_masuk_faktur, r.tgl_retur, r.catatan')
                ->orderBy('r.tgl_retur', 'DESC')->get()->getResultArray();
        }

        return view('pokeluar/detail', [
            'po' => $po,
            'details' => $details,
            'materialMasuk' => $this->db->table('materialmasuk')
                ->select('faktur, tglfaktur, no_do')
                ->where('po_keluar_id', $id)
                ->orderBy('tglfaktur', 'DESC')
                ->get()->getResultArray(),
            'statusPayment' => $this->hitungStatusPayment((string) $po['no_po']),
            'returMaterial' => $returMaterial,
            'returProduk' => $returProduk,
        ]);
    }

    /**
     * Status pembayaran PO Keluar ini, dilihat dari invoice_in yang
     * source-nya nunjuk ke PO ini (source_type='po_keluar', source_no=no_po).
     * Beda dari "Belum Dibayar" biasa -- kalau belum ada invoice sama sekali
     * dibedain jadi "Belum Ditagih", supaya nggak kesannya udah ditagih tapi
     * nggak dibayar-bayar padahal invoice-nya aja belum dibuat.
     */
    private function hitungStatusPayment(string $noPo): array
    {
        $invoices = $this->db->table('invoice_in')
            ->select('status, tanggal_lunas')
            ->where('source_type', 'po_keluar')
            ->where('source_no', $noPo)
            ->where('status !=', 'DIBATALKAN')
            ->get()->getResultArray();

        if (!$invoices) {
            return ['label' => 'Belum Ditagih', 'class' => 'po-badge-secondary'];
        }

        foreach ($invoices as $invoice) {
            if (empty($invoice['tanggal_lunas'])) {
                return ['label' => 'Belum Dibayar', 'class' => 'po-badge-warning'];
            }
        }

        return ['label' => 'Lunas', 'class' => 'po-badge-success'];
    }

    /**
     * Edit terbatas cuma untuk field header yang aman diubah belakangan
     * (Keterangan dan field cetak: TOP, System Payment, Shipping To, Quot
     * Number, Approved By). No PO/Supplier/Item sengaja tidak bisa diedit
     * di sini karena sudah dipakai sebagai referensi di tabel lain
     * (detail_po_keluar.no_po, invoice_in.source_no, materialmasuk/
     * barangmasuk.po_keluar_id) -- mengubahnya bisa bikin data itu nyasar.
     */
    public function edit(int $id)
    {
        $po = $this->poModel->find($id);
        if (!$po) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('PO Keluar tidak ditemukan.');
        }

        $referenceUsages = $this->getPoKeluarReferenceUsages($po);

        return view('pokeluar/edit', [
            'po' => $po,
            'details' => $this->detailModel->where('po_keluar_id', $id)->orderBy('id', 'ASC')->findAll(),
            'canFullEdit' => empty($referenceUsages),
            'referenceUsages' => $referenceUsages,
            'suppliers' => (new ModelSupplier())->orderBy('supnama', 'ASC')->findAll(),
            'items' => $this->getItemOptions(),
            'poAktifList' => array_values(array_filter($this->daftarPoKeluarBelumSelesai(), static fn(array $row) => (int) ($row['id'] ?? 0) !== $id)),
            'poMasukAktifList' => $this->daftarPoMasukBelumSelesai(),
        ]);
    }

    public function update(int $id)
    {
        $po = $this->poModel->find($id);
        if (!$po) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('PO Keluar tidak ditemukan.');
        }

        $referenceUsages = $this->getPoKeluarReferenceUsages($po);
        $canFullEdit = empty($referenceUsages);

        $top = trim((string) $this->request->getPost('top'));
        $systemPayment = trim((string) $this->request->getPost('system_payment'));
        $shippingTo = trim((string) $this->request->getPost('shipping_to'));
        $quotNumber = trim((string) $this->request->getPost('quot_number'));
        $approvedBy = trim((string) $this->request->getPost('approved_by'));
        $keterangan = trim((string) $this->request->getPost('keterangan'));
        $printSpecLabel = trim((string) $this->request->getPost('print_spec_label'));
        $jenisPoUntukMaterialColumn = $canFullEdit
            ? strtolower(trim((string) $this->request->getPost('jenis_po')))
            : (string) ($po['jenis_po'] ?? '');
        $materialColumnEnabled = ($jenisPoUntukMaterialColumn === 'produk' && !$this->request->getPost('material_column_enabled')) ? 0 : 1;
        $printNotes = trim((string) $this->request->getPost('print_notes'));
        $discountEnabled = $this->request->getPost('discount_enabled') ? 1 : 0;
        $ppnIncluded = $this->request->getPost('ppn_included') ? 1 : 0;
        $pph23Enabled = $this->request->getPost('pph23_enabled') ? 1 : 0;
        $discountAmount = $discountEnabled ? max(0, (float) str_replace(',', '.', (string) $this->request->getPost('discount_amount'))) : 0;
        $pph23Amount = 0;

        $updateData = [
            'keterangan' => $keterangan,
            'top' => $top !== '' ? $top : null,
            'system_payment' => $systemPayment !== '' ? $systemPayment : null,
            'shipping_to' => $shippingTo !== '' ? $shippingTo : null,
            'quot_number' => $quotNumber !== '' ? $quotNumber : null,
            'approved_by' => $approvedBy !== '' ? $approvedBy : null,
            'print_spec_label' => $printSpecLabel !== '' ? $printSpecLabel : null,
            'material_column_enabled' => $materialColumnEnabled,
            'print_notes' => $printNotes !== '' ? $printNotes : null,
            'discount_enabled' => $discountEnabled,
            'discount_amount' => $discountAmount,
            'ppn_included' => $ppnIncluded,
            'pph23_enabled' => $pph23Enabled,
            'pph23_amount' => $pph23Amount,
        ];

        $details = [];
        if ($canFullEdit) {
            $noPo = trim((string) $this->request->getPost('no_po'));
            $tglPo = trim((string) $this->request->getPost('tgl_po'));
            $idsup = trim((string) $this->request->getPost('idsup'));
            $jenisTransaksi = trim((string) $this->request->getPost('jenis_transaksi'));
            $jenisPo = strtolower(trim((string) $this->request->getPost('jenis_po')));
            $tipeItemWajib = (strcasecmp($jenisTransaksi, 'Titip Proses') === 0 && $jenisPo === 'jasa') ? 'material' : $jenisPo;
            $poAsal = trim((string) $this->request->getPost('po_asal'));
            $poMasukTerkait = trim((string) $this->request->getPost('po_masuk_terkait'));
            $kirimLangsung = $this->request->getPost('kirim_langsung') ? 1 : 0;
            $sumberMaterialProduksi = ($jenisPo === 'produk' && $this->request->getPost('sumber_material_produksi') === 'vendor') ? 'vendor' : 'tre';

            if ($noPo === '' || $tglPo === '' || $idsup === '' || !in_array($jenisTransaksi, ['Beli', 'Titip Proses'], true) || !in_array($jenisPo, ['produk', 'jasa', 'material'], true)) {
                return redirect()->back()->withInput()->with('error', 'Data header PO Keluar belum lengkap.');
            }

            if ($this->poModel->where('no_po', $noPo)->where('id <>', $id)->first()) {
                return redirect()->back()->withInput()->with('error', 'No. PO Keluar sudah dipakai PO lain.');
            }

            $supplier = (new ModelSupplier())->find($idsup);
            if (!$supplier) {
                return redirect()->back()->withInput()->with('error', 'Supplier tidak ditemukan.');
            }

            if ($poAsal !== '' && strcasecmp($poAsal, $noPo) === 0) {
                return redirect()->back()->withInput()->with('error', 'PO Asal tidak boleh sama dengan No. PO ini sendiri.');
            }
            if ($poAsal !== '' && $this->db->table('po_keluar')->where('no_po', $poAsal)->where('id <>', $id)->countAllResults() === 0) {
                return redirect()->back()->withInput()->with('error', 'PO Asal tidak ditemukan.');
            }
            if ($poMasukTerkait !== '' && !(new Modelpo())->find($poMasukTerkait)) {
                return redirect()->back()->withInput()->with('error', 'PO Masuk Terkait tidak ditemukan.');
            }

            $tipeItems = (array) $this->request->getPost('tipe_item');
            $kodeItems = (array) $this->request->getPost('kode_item');
            $qtyItems = (array) $this->request->getPost('qty_pesan');
            $hargaItems = (array) $this->request->getPost('harga');
            $printSpecs = (array) $this->request->getPost('print_spec');
            if (!$kodeItems) {
                return redirect()->back()->withInput()->with('error', 'Minimal harus ada 1 item PO Keluar.');
            }

            $itemMaster = $this->getItemMaster();
            $kombinasiTerpakai = [];
            $totalQty = 0.0;
            $totalNominal = 0.0;
            foreach ($kodeItems as $index => $kode) {
                $tipe = strtolower(trim((string) ($tipeItems[$index] ?? $jenisPo)));
                $kode = trim((string) $kode);
                if ($kode === '') {
                    continue;
                }
                if ($tipe !== $tipeItemWajib) {
                    return redirect()->back()->withInput()->with('error', 'Item PO harus sesuai dengan jenis transaksi yang dipilih.');
                }
                $key = $tipe . '|' . $kode;
                if (isset($kombinasiTerpakai[$key])) {
                    return redirect()->back()->withInput()->with('error', 'Item yang sama tidak boleh diinput dua kali dalam satu PO Keluar.');
                }
                if (!isset($itemMaster[$key])) {
                    return redirect()->back()->withInput()->with('error', 'Item PO Keluar tidak valid.');
                }
                $kombinasiTerpakai[$key] = true;

                $qty = (float) str_replace(',', '.', (string) ($qtyItems[$index] ?? 0));
                $harga = (float) str_replace(',', '.', (string) ($hargaItems[$index] ?? 0));
                if ($qty <= 0 || $harga < 0) {
                    return redirect()->back()->withInput()->with('error', 'Qty harus lebih dari 0 dan harga tidak boleh negatif.');
                }
                $subtotal = $qty * $harga;
                $totalQty += $qty;
                $totalNominal += $subtotal;
                $details[] = [
                    'po_keluar_id' => $id,
                    'no_po' => $noPo,
                    'tipe_item' => $tipe,
                    'kode_item' => $kode,
                    'nama_item' => $this->resolveNamaItem($itemMaster[$key], (int) $supplier['supid']),
                    'print_spec' => trim((string) ($printSpecs[$index] ?? '')),
                    'satuan' => $itemMaster[$key]['satuan'],
                    'qty_pesan' => $qty,
                    'qty_masuk' => 0,
                    'harga' => $harga,
                    'subtotal' => $subtotal,
                    'created_at' => date('Y-m-d H:i:s'),
                ];
            }
            if (!$details) {
                return redirect()->back()->withInput()->with('error', 'Minimal harus ada 1 item PO Keluar.');
            }

            $updateData = array_merge($updateData, [
                'no_po' => $noPo,
                'tgl_po' => $tglPo,
                'idsup' => $supplier['supid'],
                'supplier_nama' => $supplier['supnama'],
                'total_qty' => $totalQty,
                'total_nominal' => $totalNominal,
                'jenis_transaksi' => $jenisTransaksi,
                'jenis_po' => $jenisPo,
                'sumber_material_produksi' => $sumberMaterialProduksi,
                'po_asal' => $poAsal !== '' ? $poAsal : null,
                'kirim_langsung' => $kirimLangsung,
                'po_masuk_terkait' => $poMasukTerkait !== '' ? $poMasukTerkait : null,
            ]);
        } else {
            $updateData['sumber_material_produksi'] = (($po['jenis_po'] ?? '') === 'produk' && $this->request->getPost('sumber_material_produksi') === 'vendor') ? 'vendor' : 'tre';
        }

        $pph23BaseNominal = (float) ($updateData['total_nominal'] ?? ($po['total_nominal'] ?? 0));
        $updateData['pph23_amount'] = $pph23Enabled ? round(max($pph23BaseNominal - $discountAmount, 0) * 0.02, 2) : 0;

        $this->db->transBegin();
        try {
            $this->poModel->update($id, $updateData);

            if ($canFullEdit) {
                $this->detailModel->where('po_keluar_id', $id)->delete();
                $this->detailModel->insertBatch($details);
            } else {
                foreach ((array) $this->request->getPost('detail_print_spec') as $detailId => $printSpec) {
                    $this->db->table('detail_po_keluar')
                        ->where('po_keluar_id', $id)
                        ->where('id', (int) $detailId)
                        ->update(['print_spec' => trim((string) $printSpec)]);
                }
            }

            if (!$this->db->transStatus()) {
                throw new \RuntimeException('Transaksi database gagal.');
            }
            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Gagal update PO Keluar: {message}', ['message' => $e->getMessage()]);
            return redirect()->back()->withInput()->with('error', 'PO Keluar gagal diperbarui. ' . $e->getMessage());
        }

        return redirect()->to('/poKeluar/detail/' . $id)->with('message', 'PO Keluar berhasil diperbarui.');
    }
    public function cetak(int $id)
    {
        $po = $this->poModel->find($id);
        if (!$po) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('PO Keluar tidak ditemukan.');
        }

        $supplier = $this->db->table('supplier')->where('supid', $po['idsup'])->get()->getRowArray();

        return view('pokeluar/cetak', [
            'po' => $po,
            'supplier' => $supplier,
            'tglIndo' => $this->tanggalIndo($po['tgl_po']),
            'details' => $this->detailModel->where('po_keluar_id', $id)->orderBy('id', 'ASC')->findAll(),
        ]);
    }

    /**
     * Format tanggal ke Bahasa Indonesia, mis. "Jumat, 03 Juli 2026",
     * dipakai di lembar cetak PO Keluar supaya sama seperti dokumen PO
     * manual yang dipakai TRE.
     */
    private function tanggalIndo(string $tanggal): string
    {
        $hari = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
        $bulan = ['January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret', 'April' => 'April', 'May' => 'Mei', 'June' => 'Juni', 'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September', 'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'];

        $ts = strtotime($tanggal);
        if ($ts === false) return $tanggal;

        return $hari[date('l', $ts)] . ', ' . date('d', $ts) . ' ' . $bulan[date('F', $ts)] . ' ' . date('Y', $ts);
    }

    /**
     * AJAX: daftar PO Keluar AKTIF yang masih punya sisa qty belum diterima
     * untuk tipe item tertentu, dipakai buat isi dropdown "Pilih PO Keluar"
     * secara dinamis (mis. saat gudang/tipe berubah) dari sisi form.
     */
    public function pilihanAktif(string $tipeItem)
    {
        return $this->response->setJSON(['data' => $this->daftarPoAktifUntukTipe($tipeItem)]);
    }

    /**
     * Daftar PO Keluar AKTIF yang masih punya sisa qty belum diterima untuk
     * tipe item tertentu ('produk' atau 'material'). Dipakai untuk dropdown
     * "Pilih PO Keluar" di form Barang Masuk / Material Masuk. Dibuat
     * sebagai method biasa (bukan action HTTP) supaya bisa dipanggil
     * langsung dari controller lain tanpa bolak-balik lewat JSON.
     */
    public function daftarPoAktifUntukTipe(string $tipeItem): array
    {
        $tipeItem = strtolower($tipeItem);

        $calon = $this->db->table('po_keluar pk')
            ->select('pk.id, pk.no_po, pk.supplier_nama, pk.tgl_po, pk.jenis_po, pk.jenis_transaksi, pk.kirim_langsung, COALESCE(SUM(COALESCE(dpk.qty_pesan, 0) - COALESCE(dpk.qty_masuk, 0)), 0) AS sisa_qty')
            ->join('detail_po_keluar dpk', 'dpk.po_keluar_id = pk.id', 'inner')
            ->where('LOWER(dpk.tipe_item) = ' . $this->db->escape($tipeItem), null, false)
            ->where('UPPER(pk.status) = ' . $this->db->escape('AKTIF'), null, false)
            ->where('COALESCE(dpk.qty_masuk, 0) < COALESCE(dpk.qty_pesan, 0)', null, false)
            ->groupBy('pk.id, pk.no_po, pk.supplier_nama, pk.tgl_po, pk.jenis_po, pk.jenis_transaksi, pk.kirim_langsung')
            ->orderBy('pk.tgl_po', 'DESC')
            ->orderBy('pk.id', 'DESC')
            ->get()->getResultArray();

        // PO yang statusnya "Dikirim untuk Proses ke Vendor" DIKECUALIKAN
        // dari dropdown ini -- barangnya masih di vendor, belum waktunya
        // dicatat "masuk". User ngga perlu takut kepilih/lupa pilih PO yang
        // belum waktunya, karena memang ngga akan muncul di pilihan.
        return array_values(array_filter($calon, function ($po) {
            return $this->hitungStatusPenerimaan($po) !== 'Dikirim untuk Proses ke Vendor';
        }));
    }

    public function daftarPoNgUntukTipe(string $tipeItem): array
    {
        return $this->db->table('po_keluar pk')
            ->select('pk.id, pk.no_po, pk.supplier_nama, pk.tgl_po, pk.jenis_po, pk.jenis_transaksi, pk.kirim_langsung')
            ->join('detail_po_keluar dpk', 'dpk.po_keluar_id = pk.id', 'inner')
            ->where('LOWER(dpk.tipe_item) = ' . $this->db->escape(strtolower($tipeItem)), null, false)
            ->where('UPPER(pk.status) = ' . $this->db->escape('NG'), null, false)
            ->groupBy('pk.id, pk.no_po, pk.supplier_nama, pk.tgl_po, pk.jenis_po, pk.jenis_transaksi, pk.kirim_langsung')
            ->orderBy('pk.tgl_po', 'DESC')
            ->orderBy('pk.id', 'DESC')
            ->get()->getResultArray();
    }

    /**
     * Menambahkan qty yang baru diterima ke detail_po_keluar yang cocok
     * (berdasarkan po_keluar_id + tipe_item + kode_item), dipanggil dari
     * Materialmasuk/Barangmasuk::selesaiTransaksi() setelah detail
     * masuk/material tersimpan. qty_masuk sengaja tidak dibatasi maksimal
     * qty_pesan supaya kelebihan kirim tetap kelihatan apa adanya, bukan
     * malah didiamkan/disembunyikan.
     */
    public function terimaQty(int $poKeluarId, string $tipeItem, string $kodeItem, float $qty): void
    {
        if ($poKeluarId <= 0 || $qty <= 0) {
            return;
        }

        $this->db->table('detail_po_keluar')
            ->where('po_keluar_id', $poKeluarId)
            ->where('tipe_item', $tipeItem)
            ->where('kode_item', $kodeItem)
            ->set('qty_masuk', 'qty_masuk + ' . (float) $qty, false)
            ->update();
    }

    public function refreshStatusAfterReplacement(int $poKeluarId): void
    {
        if ($poKeluarId <= 0) return;
        $ng = $this->getNgQtyByPoItem($poKeluarId);
        $replacement = $this->getReplacementQtyByPoItem($poKeluarId);
        foreach ($ng as $kode => $qtyNg) {
            if ($qtyNg - ($replacement[$kode] ?? 0) > 0.000001) return;
        }
        $this->db->table('po_keluar')->where('id', $poKeluarId)->where('status', 'NG')->update(['status' => 'AKTIF']);
        $this->db->table('detail_po_keluar')->where('po_keluar_id', $poKeluarId)->where('status', 'NG')->update(['status' => 'NORMAL']);
    }

    /**
     * Kebalikan dari terimaQty() -- dipanggil saat transaksi/item Barang
     * Masuk atau Material Masuk yang tadinya nambah qty_masuk dihapus,
     * supaya qty_masuk di PO Keluar ikut balik berkurang. qty_masuk tidak
     * dibiarkan minus (dikunci ke 0) kalau ternyata sudah diubah manual
     * jadi lebih kecil dari yang mau dikurangi.
     */
    public function batalkanQty(int $poKeluarId, string $tipeItem, string $kodeItem, float $qty): void
    {
        if ($poKeluarId <= 0 || $qty <= 0) {
            return;
        }

        $this->db->table('detail_po_keluar')
            ->where('po_keluar_id', $poKeluarId)
            ->where('tipe_item', $tipeItem)
            ->where('kode_item', $kodeItem)
            ->set('qty_masuk', 'GREATEST(0, qty_masuk - ' . (float) $qty . ')', false)
            ->update();
    }

    /**
     * Status penerimaan dihitung langsung tiap kali dibutuhkan supaya tidak
     * ada risiko datanya basi. Untuk alur titip proses, PO beli yang barangnya
     * dikirim ke vendor dianggap masih "Dikirim untuk Proses ke Vendor" sampai
     * ada PO Keluar Titip Proses yang memakai PO itu sebagai PO Asal.
     */
    private function hitungStatusPenerimaan(array $po): string
    {
        if (strtoupper((string) ($po['status'] ?? '')) === 'NG' || $this->poPunyaReturNg((int) ($po['id'] ?? 0))) {
            return 'NG';
        }

        $jenisTransaksi = trim((string) ($po['jenis_transaksi'] ?? 'Beli'));
        $isTitipProses = strcasecmp($jenisTransaksi, 'Titip Proses') === 0;

        if ($isTitipProses) {
            return $this->hitungStatusQtyPenerimaan((int) $po['id']);
        }

        if (($po['jenis_po'] ?? '') === 'jasa') {
            $sudahDiInvoice = $this->db->table('invoice_in')
                ->where('source_type', 'po_keluar')
                ->where('source_no', $po['no_po'])
                ->countAllResults() > 0;

            return $sudahDiInvoice ? 'Selesai' : 'Menunggu Invoice Jasa';
        }

        if (!$isTitipProses && $this->punyaPoTitipProsesTurunan((string) $po['no_po'])) {
            return 'Selesai';
        }

        if ((int) ($po['kirim_langsung'] ?? 0) === 1) {
            return 'Dikirim untuk Proses ke Vendor';
        }

        // Begitu PO Keluar ini sudah punya Invoice In, dianggap selesai --
        // walaupun qty yang diterima kurang dari qty pesan (mis. supplier
        // cuma sanggup kirim sebagian dan sisanya tidak akan pernah
        // dikirim). Status "Selesai" ini menggantikan "Diterima
        // Lengkap"/"Diterima Sebagian" supaya tidak nyangkut selamanya di
        // "Diterima Sebagian" walau secara bisnis sudah ditagih & lunas.
        $sudahDiInvoice = $this->db->table('invoice_in')
            ->where('source_type', 'po_keluar')
            ->where('source_no', $po['no_po'])
            ->countAllResults() > 0;
        if ($sudahDiInvoice) {
            return 'Selesai';
        }

        return $this->hitungStatusQtyPenerimaan((int) $po['id']);
    }

    private function poPunyaReturNg(int $poId): bool
    {
        if ($poId <= 0 || !$this->db->tableExists('retur_material_detail')) {
            return false;
        }

        return $this->db->table('retur_material_detail rd')
            ->join('detail_materialmasuk dmm', 'dmm.id = rd.material_masuk_detail_id', 'left')
            ->groupStart()
                ->where('rd.po_keluar_id', $poId)
                ->orWhere('dmm.po_keluar_id', $poId)
            ->groupEnd()
            ->countAllResults() > 0;
    }

    private function getNgQtyByPoItem(int $poId): array
    {
        if ($poId <= 0 || !$this->db->tableExists('retur_material_detail')) {
            return [];
        }

        $rows = $this->db->table('retur_material_detail rd')
            ->select('dmm.detmatkode AS kode_item, SUM(rd.qty_retur) AS qty_ng', false)
            ->join('detail_materialmasuk dmm', 'dmm.id = rd.material_masuk_detail_id', 'inner')
            ->groupStart()->where('rd.po_keluar_id', $poId)->orWhere('dmm.po_keluar_id', $poId)->groupEnd()
            ->groupBy('dmm.detmatkode')
            ->get()->getResultArray();

        $result = [];
        foreach ($rows as $row) {
            $result[(string) $row['kode_item']] = (float) $row['qty_ng'];
        }
        if ($this->db->tableExists('retur_produk_detail')) {
            foreach ($this->db->table('retur_produk_detail')->select('kode_barang AS kode_item, SUM(qty_retur) AS qty_ng', false)->where('po_keluar_id', $poId)->groupBy('kode_barang')->get()->getResultArray() as $row) {
                $result[(string) $row['kode_item']] = ($result[(string) $row['kode_item']] ?? 0) + (float) $row['qty_ng'];
            }
        }
        return $result;
    }

    private function getReplacementQtyByPoItem(int $poId): array
    {
        $rows = $this->db->table('detail_materialmasuk dmm')
            ->select('dmm.detmatkode AS kode_item, SUM(dmm.detjml) AS qty_pengganti', false)
            ->join('materialmasuk mm', 'mm.faktur = dmm.detfaktur', 'inner')
            ->where('dmm.po_keluar_id', $poId)
            ->where('mm.sumber', 'retur_ng')
            ->groupBy('dmm.detmatkode')
            ->get()->getResultArray();

        $result = [];
        foreach ($rows as $row) {
            $result[(string) $row['kode_item']] = (float) $row['qty_pengganti'];
        }
        if ($this->db->fieldExists('sumber', 'barangmasuk')) {
            foreach ($this->db->table('detail_barangmasuk d')->select('d.detbrgkode AS kode_item, SUM(d.detjml) AS qty_pengganti', false)->join('barangmasuk bm', 'bm.faktur = d.detfaktur')->where('bm.po_keluar_id', $poId)->where('bm.sumber', 'retur_ng')->groupBy('d.detbrgkode')->get()->getResultArray() as $row) {
                $result[(string) $row['kode_item']] = ($result[(string) $row['kode_item']] ?? 0) + (float) $row['qty_pengganti'];
            }
        }
        return $result;
    }

    private function punyaPoTitipProsesTurunan(string $noPo): bool
    {
        return $this->db->table('po_keluar')
            ->where('po_asal', $noPo)
            ->where('jenis_transaksi', 'Titip Proses')
            ->where('status', 'AKTIF')
            ->countAllResults() > 0;
    }

    private function getPoKeluarReferenceUsages(array $po): array
    {
        $id = (int) ($po['id'] ?? 0);
        $noPo = (string) ($po['no_po'] ?? '');
        $usages = [];

        $checks = [
            'Penerimaan Produk' => ['table' => 'barangmasuk', 'where' => ['po_keluar_id' => $id]],
            'Penerimaan Material' => ['table' => 'materialmasuk', 'where' => ['po_keluar_id' => $id]],
            'Invoice Masuk' => ['table' => 'invoice_in', 'where' => ['source_type' => 'po_keluar', 'source_no' => $noPo]],
            'PO Keluar Turunan' => ['table' => 'po_keluar', 'where' => ['po_asal' => $noPo]],
        ];

        $qtyMasukTercatat = $this->db->table('detail_po_keluar')
            ->where('po_keluar_id', $id)
            ->where('COALESCE(qty_masuk, 0) >', 0)
            ->countAllResults();
        if ($qtyMasukTercatat > 0) {
            $usages['Qty Penerimaan Tercatat'] = $qtyMasukTercatat;
        }
        foreach ($checks as $label => $check) {
            if (!$this->db->tableExists($check['table'])) {
                continue;
            }
            $builder = $this->db->table($check['table']);
            foreach ($check['where'] as $column => $value) {
                $builder->where($column, $value);
            }
            $count = $builder->countAllResults();
            if ($count > 0) {
                $usages[$label] = $count;
            }
        }

        return $usages;
    }

    private function hitungStatusQtyPenerimaan(int $poKeluarId): string
    {
        $totals = $this->db->table('detail_po_keluar')
            ->select('COALESCE(SUM(qty_pesan), 0) AS total_pesan, COALESCE(SUM(qty_masuk), 0) AS total_masuk')
            ->where('po_keluar_id', $poKeluarId)
            ->get()->getRowArray();

        $totalPesan = (float) ($totals['total_pesan'] ?? 0);
        $totalMasuk = (float) ($totals['total_masuk'] ?? 0);
        $ngQty = array_sum($this->getNgQtyByPoItem($poKeluarId));
        $totalMasuk = max(0, $totalMasuk - $ngQty);

        if ($totalMasuk <= 0) {
            return 'Belum Diterima';
        }

        if ($totalMasuk >= $totalPesan) {
            return 'Diterima Lengkap';
        }

        return 'Diterima Sebagian';
    }

    private function daftarPoKeluarBelumSelesai(): array
    {
        $rows = $this->poModel
            ->where('status', 'AKTIF')
            ->orderBy('tgl_po', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll();

        return array_values(array_filter($rows, function (array $row) {
            return $this->hitungStatusPenerimaan($row) !== 'Selesai';
        }));
    }

    private function daftarPoMasukBelumSelesai(): array
    {
        return $this->db->table('po p')
            ->select("
                p.nopo,
                pel.pelnama,
                COALESCE(SUM(dp.detqty), 0) AS total_qty,
                COALESCE(SUM(dp.detkirim), 0) AS total_kirim,
                COUNT(DISTINCT io.id) AS total_invoice,
                COUNT(DISTINCT CASE WHEN io.id IS NOT NULL AND COALESCE(io.status_bayar, 'Belum Lunas') = 'Lunas' THEN io.id END) AS invoice_lunas
            ", false)
            ->join('pelanggan pel', 'pel.pelid = p.idpel', 'left')
            ->join('detail_po dp', 'dp.detnopo = p.nopo', 'left')
            ->join('invoice_out io', "io.po_no = p.nopo AND io.status = 'AKTIF'", 'left')
            ->groupBy('p.nopo, pel.pelnama, p.tglpo')
            ->having('COALESCE(SUM(dp.detkirim), 0) < COALESCE(SUM(dp.detqty), 0)', null, false)
            ->orHaving('COUNT(DISTINCT io.id) = 0', null, false)
            ->orHaving("COUNT(DISTINCT CASE WHEN io.id IS NOT NULL AND COALESCE(io.status_bayar, 'Belum Lunas') = 'Lunas' THEN io.id END) < COUNT(DISTINCT io.id)", null, false)
            ->orderBy('p.tglpo', 'DESC')
            ->get()->getResultArray();
    }

    public function batal(int $id)
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return redirect()->to('/poKeluar/data');
        }

        $po = $this->poModel->find($id);
        if (!$po) {
            return redirect()->to('/poKeluar/data')->with('error', 'PO Keluar tidak ditemukan.');
        }

        $this->poModel->update($id, ['status' => 'DIBATALKAN']);
        return redirect()->to('/poKeluar/data')->with('message', 'PO Keluar berhasil dibatalkan.');
    }

    public function hapus(int $id)
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return redirect()->to('/poKeluar/data');
        }

        $po = $this->poModel->find($id);
        if (!$po) {
            return redirect()->to('/poKeluar/data')->with('error', 'PO Keluar tidak ditemukan.');
        }

        if ($po['status'] !== 'DIBATALKAN') {
            return redirect()->to('/poKeluar/data')->with('error', 'Hanya PO Keluar berstatus DIBATALKAN yang bisa dihapus.');
        }

        $punyaPenerimaan = $this->db->table('materialmasuk')->where('po_keluar_id', $id)->countAllResults() > 0
            || $this->db->table('barangmasuk')->where('po_keluar_id', $id)->countAllResults() > 0;

        if ($punyaPenerimaan) {
            return redirect()->to('/poKeluar/data')->with('error', 'PO Keluar ini sudah punya riwayat penerimaan barang/material, tidak bisa dihapus.');
        }

        $this->db->transBegin();
        try {
            $this->detailModel->where('po_keluar_id', $id)->delete();
            $this->poModel->delete($id);

            if (!$this->db->transStatus()) {
                throw new \RuntimeException('Transaksi database gagal.');
            }
            $this->db->transCommit();

            return redirect()->to('/poKeluar/data')->with('message', 'PO Keluar berhasil dihapus.');
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Gagal menghapus PO Keluar: {message}', ['message' => $e->getMessage()]);

            return redirect()->to('/poKeluar/data')->with('error', 'PO Keluar gagal dihapus. ' . $e->getMessage());
        }
    }

    private function getItemOptions(): array
    {
        $items = [];

        foreach ($this->db->table('barang b')
            ->select("b.brgkode AS kode_item, b.brgnama AS nama_item, COALESCE(s.satnama, '-') AS satuan", false)
            ->join('satuan s', 's.satid = b.brgsatid', 'left')
            ->orderBy('b.brgkode', 'ASC')
            ->get()->getResultArray() as $row) {
            $items[] = [
                'tipe_item' => 'produk',
                'kode_item' => $row['kode_item'],
                'nama_item' => $row['nama_item'],
                'satuan' => $row['satuan'],
            ];
        }

        // label_per_supplier: alias nama material per-supplier (Master Data
        // Material > Label Nama per Supplier) -- fisik materialnya sama, tapi
        // nama yang tercantum di PO Keluar bisa beda tergantung supplier yang
        // dituju. Dipakai buat nge-override nama_item pas item ini dipilih
        // untuk PO Keluar dengan supplier yang punya label.
        $labelPerMaterial = [];
        foreach ($this->db->table('material_label_supplier')
            ->select('material_id, supplier_id, label_nama')
            ->get()->getResultArray() as $label) {
            $labelPerMaterial[(int) $label['material_id']][(int) $label['supplier_id']] = $label['label_nama'];
        }

        foreach ($this->db->table('material m')
            ->select("m.matid AS kode_item, m.matkode, m.matnama AS nama_item, COALESCE(s.satnama, '-') AS satuan", false)
            ->join('satuan s', 's.satid = m.matsatid', 'left')
            ->orderBy('m.matkode', 'ASC')
            ->get()->getResultArray() as $row) {
            $items[] = [
                'tipe_item' => 'material',
                'kode_item' => (string) $row['kode_item'],
                'label_kode' => $row['matkode'],
                'nama_item' => $row['nama_item'],
                'satuan' => $row['satuan'],
                'label_per_supplier' => $labelPerMaterial[(int) $row['kode_item']] ?? [],
            ];
        }

        foreach ($this->db->table('stok_habis_pakai')
            ->select("kode AS kode_item, nama AS nama_item, satuan, 0 AS harga_default", false)
            ->where('aktif', 1)
            ->orderBy('nama', 'ASC')
            ->get()->getResultArray() as $row) {
            $items[] = [
                'tipe_item' => 'habis_pakai',
                'kode_item' => (string) $row['kode_item'],
                'label_kode' => $row['kode_item'],
                'nama_item' => $row['nama_item'],
                'satuan' => $row['satuan'],
                'harga_default' => 0,
            ];
        }
        foreach ($this->db->table('jasa')
            ->select('idjasa AS kode_item, namajasa AS nama_item, harga_modal')
            ->orderBy('namajasa', 'ASC')
            ->get()->getResultArray() as $row) {
            $items[] = [
                'tipe_item' => 'jasa',
                'kode_item' => (string) $row['kode_item'],
                'label_kode' => 'JASA-' . $row['kode_item'],
                'nama_item' => $row['nama_item'],
                'satuan' => 'Jasa',
                'harga_default' => (float) ($row['harga_modal'] ?? 0),
            ];
        }

        return $items;
    }

    private function getItemMaster(): array
    {
        $master = [];
        foreach ($this->getItemOptions() as $item) {
            $master[$item['tipe_item'] . '|' . $item['kode_item']] = $item;
        }
        return $master;
    }

    /**
     * Nama item yang disimpan sebagai snapshot ke detail_po_keluar.nama_item.
     * Buat material, dicek dulu apakah supplier tujuan PO ini punya Label
     * Nama sendiri (Master Data Material > Label Nama per Supplier) -- kalau
     * ada, itu yang dipakai; kalau tidak ada, fallback ke nama_item biasa.
     */
    private function resolveNamaItem(array $itemData, int $supplierId): string
    {
        if (($itemData['tipe_item'] ?? '') === 'material') {
            $label = $itemData['label_per_supplier'][$supplierId] ?? '';
            if ($label !== '') {
                return $label;
            }
        }

        return $itemData['nama_item'];
    }

    private function ensureMaterialLabelSupplierTable(): void
    {
        $db = \Config\Database::connect();
        if ($db->tableExists('material_label_supplier')) {
            return;
        }

        $forge = \Config\Database::forge();
        $forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'material_id' => ['type' => 'INT', 'constraint' => 11],
            'supplier_id' => ['type' => 'INT', 'constraint' => 11],
            'label_nama' => ['type' => 'VARCHAR', 'constraint' => 150],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $forge->addKey('id', true);
        $forge->addUniqueKey(['material_id', 'supplier_id']);
        $forge->createTable('material_label_supplier');
    }
}
