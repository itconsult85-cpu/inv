<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Modelmaterialmasuk;
use App\Models\ModelDataStokMaterial;
use App\Models\Modeldetailmaterialmasuk;
use App\Models\Modelgudang;
use App\Models\ModelStokMaterial;
use App\Models\Modeltempmaterialmasuk;
use App\Models\ModelSupplier;
use App\Models\Modelmaterial;
use App\Libraries\NoDoChecker;
use \Hermawan\DataTables\DataTable;
use Config\Services;

class Materialmasuk extends BaseController
{
    private function cariPemakaianNoDo(string $noDo): ?string
    {
        return (new NoDoChecker())->findSource($noDo);
    }

    /**
     * Kolom `gudang` di tabel stokmaterial/temp_materialmasuk itu int (gdgid),
     * tapi kadang yang kekirim dari form itu nama gudangnya (mis. "Cirebon"),
     * bukan id-nya -- biar nggak gagal simpan (kolomnya NOT NULL int), cari
     * dulu id aslinya lewat nama kalau yang dikirim bukan angka.
     */
    private function resolveGudangId($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        $row = db_connect()->table('gudang')->where('gdgnama', $value)->get()->getRowArray();

        return $row ? (int) $row['gdgid'] : null;
    }

    public function cekNoDo()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $noDo = trim((string) $this->request->getPost('no_do'));
        $sumber = $this->cariPemakaianNoDo($noDo);

        return $this->response->setJSON([
            'terpakai' => $sumber !== null,
            'pesan' => $sumber !== null
                ? "No Surat Jalan {$noDo} sudah digunakan pada {$sumber}"
                : '',
        ]);
    }

    public function cekNoInvoice()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $noInvoice = trim((string) $this->request->getPost('nofaktur'));
        $db = db_connect();
        $terpakai = $noInvoice !== ''
            && $db->fieldExists('no_invoice', 'materialmasuk')
            && $db->table('materialmasuk')->where('no_invoice', $noInvoice)->countAllResults() > 0;

        return $this->response->setJSON([
            'terpakai' => $terpakai,
            'pesan' => $terpakai ? "No. Invoice {$noInvoice} sudah digunakan" : '',
        ]);
    }

    private function buatNomorMaterialMasuk(): string
    {
        do {
            // materialmasuk.faktur dan detail_materialmasuk.detfaktur pada
            // schema lama bertipe CHAR(20).
            $nomor = 'MM-' . date('ymd-His') . '-' . random_int(100, 999);
        } while (db_connect()->table('materialmasuk')->where('faktur', $nomor)->countAllResults() > 0);

        return $nomor;
    }

    private function nomorMaterialMasukDariRequest(): string
    {
        $nomorInternal = trim((string) $this->request->getPost('faktur_internal'));
        return $nomorInternal !== '' ? $nomorInternal : $this->buatNomorMaterialMasuk();
    }

    private function noInvoiceDariRequest(): ?string
    {
        $noInvoice = trim((string) $this->request->getPost('nofaktur'));
        return $noInvoice !== '' ? $noInvoice : null;
    }

    private function materialMasukPunyaKolomInvoice(): bool
    {
        return db_connect()->fieldExists('no_invoice', 'materialmasuk');
    }

    /**
     * Buat transaksi Material Masuk yang item-nya nyebar ke beberapa
     * supplier berbeda (header idsup NULL) -- ambil nama-nama supplier
     * unik dari tiap item transaksi itu, digabung jadi 1 teks.
     */
    private function namaSupplierPerItem($db, string $faktur): string
    {
        $rows = $db->table('detail_materialmasuk dmm')
            ->select('supplier.supnama', false)
            ->join('supplier', 'supplier.supid = dmm.idsup', 'left')
            ->where('dmm.detfaktur', $faktur)
            ->groupBy('supplier.supnama')
            ->get()->getResultArray();

        $nama = array_filter(array_unique(array_column($rows, 'supnama')));

        return $nama ? implode(', ', $nama) : '-';
    }

    public function data()
    {
        $modelgudang = new Modelgudang();
        $data = [
            'datagudang' => $modelgudang->findAll(),
        ];
        return view('materialmasuk/viewdata', $data);
    }

    public function listData()
    {
        if ($this->request->isAJAX()) {
            $tglawal = $this->request->getPost('tglawal');
            $tglakhir = $this->request->getPost('tglakhir');

            $db = \Config\Database::connect();
            $kolomInvoice = $db->fieldExists('no_invoice', 'materialmasuk')
                ? 'materialmasuk.no_invoice'
                : 'materialmasuk.faktur AS no_invoice';
            // idsup di header cuma keisi kalau SEMUA item transaksi ini dari
            // supplier yang sama (lihat selesaiTransaksi()) -- kalau NULL
            // padahal sumber-nya 'beli', berarti item-nya nyebar ke beberapa
            // supplier berbeda (dari PO Keluar yang beda-beda). Nilai
            // "Beberapa Supplier"-nya dihitung di PHP (lewat ->add() di bawah),
            // BUKAN lewat SQL CASE/COALESCE di select() -- library DataTable
            // yang dipakai di sini ngeparse ulang string select() pakai
            // parser-nya sendiri, dan crash (Undefined array key) kalau
            // ekspresinya lumayan kompleks.
            $builder = $db->table('materialmasuk')
                ->select("materialmasuk.faktur, {$kolomInvoice}, materialmasuk.no_do, materialmasuk.tglfaktur, materialmasuk.idsup, materialmasuk.sumber, supplier.supnama AS supplier_nama, pelanggan.pelnama AS pelanggan_nama, materialmasuk.totalberatmaterial, gudang.gdgnama", false)
                ->join('supplier', 'supplier.supid = materialmasuk.idsup', 'left')
                ->join('pelanggan', 'pelanggan.pelid = materialmasuk.idpel', 'left')
                ->join('gudang', 'gudang.gdgid = materialmasuk.gudang');

            if ($tglawal && $tglakhir) {
                $builder->whereIn('materialmasuk.faktur', function ($subQuery) use ($tglawal, $tglakhir) {
                    $subQuery->select('materialmasuk.faktur')
                        ->from('materialmasuk')
                        ->where('materialmasuk.tglfaktur >=', $tglawal)
                        ->where('materialmasuk.tglfaktur <=', $tglakhir);
                });
            }

            return DataTable::of($builder)
                ->addNumbering('nomor')
                ->add('supnama', function ($row) use ($db) {
                    if ($row->sumber === 'adjustment') {
                        return 'Adjustment Stok';
                    }
                    if ($row->sumber === 'konsinyasi') {
                        return $row->pelanggan_nama ?: '-';
                    }
                    if (!empty($row->idsup)) {
                        return $row->supplier_nama ?: '-';
                    }
                    // idsup di header NULL berarti item-nya nyebar ke
                    // beberapa supplier -- ambil nama-nama supplier unik
                    // dari tiap item transaksi ini, bukan cuma 1 nama.
                    return $this->namaSupplierPerItem($db, $row->faktur);
                })
                ->add('aksi', function ($row) {
                    $returButton = \App\Libraries\AccessControl::can('material.masuk.return_ng')
                        ? "<button type=\"button\" class=\"btn btn-sm btn-warning\" title=\"Retur Material NG\" onclick=\"returMaterial('" . sha1($row->faktur) . "')\"><i class=\"fa fa-undo\"></i></button>&nbsp"
                        : '';
                    $kelolaButton = \App\Libraries\AccessControl::can('material.masuk.return_ng')
                        ? "<button type=\"button\" class=\"btn btn-sm btn-secondary\" title=\"Koreksi/Batalkan Retur NG\" onclick=\"location.href='" . site_url('materialretur/kelola/' . sha1($row->faktur)) . "'\"><i class=\"fa fa-tools\"></i></button>&nbsp"
                        : '';
                    return "<button type=\"button\" class=\"btn btn-sm btn-primary\" onclick=\"edit('" . sha1($row->faktur) . "')\"><i class=\"fa fa-edit\"></i></button>&nbsp"
                        . $returButton
                        . $kelolaButton
                        . "<button type=\"button\" class=\"btn btn-sm btn-danger\" onclick=\"hapus('" . $row->faktur . "')\"><i class=\"fa fa-trash-alt\"></i></button>";
                })
                ->format('totalberatmaterial', function ($value) {
                    return number_format($value, 2, ',', '.');
                })
                ->format('no_invoice', function ($value) {
                    return trim((string) $value) !== '' ? esc($value) : '<span class="text-muted">Belum ada</span>';
                })
                ->format('tglfaktur', function ($value) {
                    return date('d-m-Y', strtotime($value));
                })
                ->toJson(true);
        }
    }

    public function input()
    {
        $modelgudang = new Modelgudang();
        $penerimaanNg = (string) $this->request->getGet('penerimaan_ng') === '1';
        $poNgId = (int) $this->request->getGet('po_keluar_id');
        $poKeluar = new PoKeluar();
        $datapokeluar = $penerimaanNg
            ? $poKeluar->daftarPoNgUntukTipe('material')
            : $poKeluar->daftarPoAktifUntukTipe('material');
        $poNgTerpilih = null;
        foreach ($datapokeluar as $po) {
            if ((int) $po['id'] === $poNgId) {
                $poNgTerpilih = $po;
                break;
            }
        }
        $data = [
            'datagudang' => $modelgudang->findAll(),
            'datapokeluar' => $datapokeluar,
            'penerimaanNg' => $penerimaanNg,
            'poNgTerpilih' => $poNgTerpilih,
            'datapelanggan' => (new \App\Models\ModelPelanggan())->whereNotIn('pelid', [1, 2])->findAll(),
            'datasupplier' => (new ModelSupplier())->whereNotIn('supid', [1, 2])->orderBy('supnama', 'ASC')->findAll(),
            'datamaterial' => (new Modelmaterial())->select('matid, matkode, matnama')->orderBy('matkode', 'ASC')->findAll(),
        ];
        return view('materialmasuk/forminput', $data);
    }

    public function tampilDataTemp()
    {
        if ($this->request->isAJAX()) {
            $nofaktur = trim((string) $this->request->getPost('nofaktur'));

            $modalTempMaterialMasuk = new Modeltempmaterialmasuk();

            $dataTemp = $modalTempMaterialMasuk->tampilDataTemp($nofaktur);
            $data = [
                'tampildata' => $dataTemp
            ];

            $json = [
                'data' => view('materialmasuk/datatemp', $data)
            ];
            echo json_encode($json);
        }
    }

    public function ambilDataMaterial()
    {
        if ($this->request->isAJAX()) {
            $materialid = $this->request->getPost('materialid');
            $idgudang = $this->resolveGudangId($this->request->getPost('idgudang'));

            $modelStokMaterial = new ModelStokMaterial();

            $cekData = $modelStokMaterial->where('materialid', $materialid)->first();

            if ($cekData == null) {
                $json = [
                    'error' => 'Maaf data material tidak ditemukan'
                ];
            } else {
                $stokGudang = $modelStokMaterial->getStokByMaterialIdGudang($materialid, $idgudang);
                $idMat = $modelStokMaterial->getIdByMaterialGudang($materialid, $idgudang);

                $data = [
                    'namamaterial' => $cekData['namamaterial'],
                    'kodematerial' => $cekData['kodematerial'],
                    'materialkatid' => $cekData['materialkatid'],
                    'materialsatid' => $cekData['materialsatid'],
                    'materialid' => $cekData['materialid'],
                    'stok' => $stokGudang,
                    'idmat' => $idMat,
                ];

                $json = [
                    'sukses' => $data
                ];
            }
            echo json_encode($json);
        }
    }

    function listDataMaterial()
    {
        $request = Services::request();
        $datamodel = new ModelDataStokMaterial($request);
        if ($request->getMethod(true) == 'POST') {
            $lists = $datamodel->get_datatables();
            $data = [];
            $no = $request->getPost("start");
            foreach ($lists as $list) {
                $no++;
                $row = [];

                $stokGudang1 = $datamodel->where('materialid', $list->materialid)->where('gudang', 1)->first();
                $stokGudang2 = $datamodel->where('materialid', $list->materialid)->where('gudang', 2)->first();

                $stokGudang1Value = isset($stokGudang1['stok']) ? $stokGudang1['stok'] : 0;
                $stokGudang2Value = isset($stokGudang2['stok']) ? $stokGudang2['stok'] : 0;

                $tombolPilih = "<button type=\"button\" class=\"btn btn-sm btn-info\" onclick=\"pilih('" . $list->materialid . "','" . $list->kodematerial . "','" . $list->materialkatid . "','" . $list->materialsatid . "')\">Pilih</button>";

                $row[] = $no;
                $row[] = $list->kodematerial;
                $row[] = $list->namamaterial;
                $row[] = number_format($stokGudang1Value, 0, ",", ".");
                $row[] = number_format($stokGudang2Value, 0, ",", ".");
                $row[] = $tombolPilih;
                $data[] = $row;
            }
            $output = [
                "draw" => $request->getPost('draw'),
                "recordsTotal" => $datamodel->count_all(),
                "recordsFiltered" => $datamodel->count_filtered(),
                "data" => $data
            ];
            echo json_encode($output);
        }
    }

    function simpanItem()
    {
        if ($this->request->isAJAX()) {
            $nofaktur = $this->nomorMaterialMasukDariRequest();
            $noInvoice = $this->noInvoiceDariRequest();
            $tglfaktur = $this->request->getPost('tglfaktur');
            $gudang = $this->resolveGudangId($this->request->getPost('gudang'));
            $materialid = $this->request->getPost('materialid');
            $materialkatid = $this->request->getPost('materialkatid');
            $materialsatid = $this->request->getPost('materialsatid');
            $idmat = $this->request->getPost('idmat');
            $jml = $this->request->getPost('jml');
            $idsupplier = $this->request->getPost('idsupplier');
            $sumberInput = $this->request->getPost('sumber');
            $sumber = in_array($sumberInput, ['konsinyasi', 'adjustment', 'retur_ng'], true) ? $sumberInput : 'beli';
            $idpelanggan = $this->request->getPost('idpelanggan');
            $poKeluarId = (int) $this->request->getPost('po_keluar_id') ?: null;

            $modelTempMaterialMasuk = new Modeltempmaterialmasuk();

            $validation = \Config\Services::validation();

            $rules = [];

            // Konsinyasi wajib pelanggan, beli wajib supplier. Adjustment Stok
            // tidak punya pihak luar, jadi tidak wajib supplier/pelanggan.
            if ($sumber === 'konsinyasi') {
                $rules['idpelanggan'] = [
                    'rules' => 'required',
                    'label' => 'Pelanggan',
                    'errors' => ['required' => '{field} belum dipilih'],
                ];
            } elseif (in_array($sumber, ['beli', 'retur_ng'], true)) {
                $rules['idsupplier'] = [
                    'rules' => 'required|not_in_list[1,2]',
                    'label' => 'Supplier',
                    'errors' => [
                        'required' => '{field} belum dipilih',
                        'not_in_list' => 'TRE-CKG dan TRE-CRB harus diproses melalui Permintaan Transfer',
                    ],
                ];
            }
            // CI4 sengaja bikin validate() balikin false kalau $rules kosong
            // (jaga-jaga programmer lupa isi aturan) -- bukan otomatis lolos.
            // Untuk "Adjustment Stok" $rules emang sengaja dikosongin (nggak
            // butuh Supplier/Pelanggan), jadi harus dilewatin manual di sini,
            // bukan ikut lewat validate().
            $valid = empty($rules) ? true : $this->validate($rules);
            if (!$valid) {
                $json = [
                    'error' => 'Maaf, ' . $validation->listErrors() . ''
                ];
            } elseif ($gudang === null) {
                $json = [
                    'error' => 'Maaf, Lokasi Gudang tidak valid. Silakan pilih ulang lokasi gudangnya.'
                ];
            } elseif ($noInvoice !== null && $this->materialMasukPunyaKolomInvoice() && db_connect()->table('materialmasuk')->where('no_invoice', $noInvoice)->countAllResults() > 0) {
                $json = [
                    'error' => 'Maaf, No. Invoice sudah terpakai'
                ];
            } elseif (db_connect()->table('materialmasuk')->where('faktur', $nofaktur)->countAllResults() > 0) {
                $json = [
                    'error' => 'Maaf, nomor transaksi internal sudah terpakai. Silakan refresh halaman lalu coba lagi.'
                ];
            } else {
                // Baris dianggap "sama" (boleh digabung jumlahnya) cuma kalau
                // material & PO Keluar asalnya sama. Item yang sama tapi dari
                // PO Keluar berbeda harus jadi baris terpisah, supaya nanti
                // qty_masuk PO Keluar-nya bisa dikreditkan ke PO yang benar
                // masing-masing (bukan numpuk ke satu PO doang).
                $existingRow = $modelTempMaterialMasuk
                    ->where('detfaktur', $nofaktur)
                    ->where('idmat', $idmat)
                    ->where('po_keluar_id', $poKeluarId)
                    ->first();

                if ($existingRow) {
                    $newJml = $existingRow['detjml'] + $jml;
                    $newSubtotal = $existingRow['detsubtotal'] + $jml;

                    $modelTempMaterialMasuk->update($existingRow['id'], [
                        'detjml' => $newJml,
                        'detsubtotal' => $newSubtotal
                    ]);
                } else {
                    $modelTempMaterialMasuk->insert([
                        'detfaktur' => $nofaktur,
                        'po_keluar_id' => $poKeluarId,
                        'idsup' => in_array($sumber, ['beli', 'retur_ng'], true) ? ($idsupplier ?: null) : null,
                        'tgl' => $tglfaktur,
                        'detmatkode' => $materialid,
                        'idmat' => $idmat,
                        'detmatkatid' => $materialkatid,
                        'detmatsatid' => $materialsatid,
                        'gudang' => $gudang,
                        'detjml' => $jml,
                        'detsubtotal' => intval($jml)
                    ]);
                }
                $json = [
                    'sukses' => 'Item berhasil di tambahkan',
                    'faktur_internal' => $nofaktur,
                ];
            }
            echo json_encode($json);
        } else {
            exit('Maaf tidak bisa dipanggil');
        }
    }

    function hapusItem()
    {
        if ($this->request->isAJAX()) {
            $id = $this->request->getPost('id');

            $modelTempMaterialMasuk = new Modeltempmaterialmasuk();
            $modelTempMaterialMasuk->delete($id);

            $json = [
                'sukses' => 'Item Berhasil di Hapus'
            ];
            echo json_encode($json);
        }
    }

    /**
     * AJAX: begitu user pilih PO Keluar di form Input Material Masuk, kasih
     * balik supplier + daftar item material dari PO Keluar itu supaya
     * langsung kelihatan tinggal pilih & isi qty yang datang (boleh partial,
     * dihitung dari sisa qty_pesan - qty_masuk).
     */
    public function itemPoKeluar()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $poKeluarId = (int) $this->request->getPost('po_keluar_id');
        $db = db_connect();

        $header = $db->table('po_keluar')->where('id', $poKeluarId)->get()->getRowArray();
        if (!$header) {
            return $this->response->setJSON(['error' => 'PO Keluar tidak ditemukan.']);
        }

        $items = $db->table('detail_po_keluar dpk')
            ->select('dpk.kode_item, m.matkode, m.matnama, m.matkatid, m.matsatid, dpk.qty_pesan, dpk.qty_masuk')
            ->join('material m', 'm.matid = dpk.kode_item', 'left')
            ->where('dpk.po_keluar_id', $poKeluarId)
            ->where('dpk.tipe_item', 'material')
            ->get()->getResultArray();

        $ngByItem = [];
        $replacementByItem = [];
        if (strtoupper((string) ($header['status'] ?? '')) === 'NG' && $db->tableExists('retur_material_detail')) {
            foreach ($db->table('retur_material_detail rd')
                ->select('dmm.detmatkode, SUM(rd.qty_retur) AS qty_ng', false)
                ->join('detail_materialmasuk dmm', 'dmm.id = rd.material_masuk_detail_id', 'inner')
                ->groupStart()->where('rd.po_keluar_id', $poKeluarId)->orWhere('dmm.po_keluar_id', $poKeluarId)->groupEnd()
                ->groupBy('dmm.detmatkode')->get()->getResultArray() as $row) {
                $ngByItem[(string) $row['detmatkode']] = (float) $row['qty_ng'];
            }
            foreach ($db->table('detail_materialmasuk dmm')
                ->select('dmm.detmatkode, SUM(dmm.detjml) AS qty_pengganti', false)
                ->join('materialmasuk mm', 'mm.faktur = dmm.detfaktur', 'inner')
                ->where('dmm.po_keluar_id', $poKeluarId)
                ->where('mm.sumber', 'retur_ng')
                ->groupBy('dmm.detmatkode')->get()->getResultArray() as $row) {
                $replacementByItem[(string) $row['detmatkode']] = (float) $row['qty_pengganti'];
            }
        }

        foreach ($items as &$item) {
            $itemKey = (string) $item['kode_item'];
            $item['sisa'] = strtoupper((string) ($header['status'] ?? '')) === 'NG'
                ? max(0, ($ngByItem[$itemKey] ?? 0) - ($replacementByItem[$itemKey] ?? 0))
                : max(0, (float) $item['qty_pesan'] - (float) $item['qty_masuk']);
        }

        return $this->response->setJSON([
            'sukses' => [
                'idsupplier' => $header['idsup'],
                'namasupplier' => $header['supplier_nama'],
                'items' => $items,
            ],
        ]);
    }

    public function modalCariMaterial()
    {
        if ($this->request->isAJAX()) {
            $json = [
                'data' => view('materialmasuk/modalcarimaterial')
            ];
            echo json_encode($json);
        }
    }

    function modalMaterialMasuk()
    {
        $nofaktur = $this->request->getPost('nofaktur');
        $tglfaktur = $this->request->getPost('tglfaktur');
        $idsupplier = $this->request->getPost('idsupplier');
        $totalberatmaterial = $this->request->getPost('totalberatmaterial');

        $modelTemp = new Modeltempmaterialmasuk();
        $cekdata = $modelTemp->tampilDataTemp($nofaktur);

        if ($cekdata->getNumrows() > 0) {
            $data = [
                'nofaktur' => $nofaktur,
                'totalberatmaterial' => $totalberatmaterial,
                'tglfaktur' => $tglfaktur,
                'idsupplier' => $idsupplier,
            ];

            $json = [
                'data' => view('materialmasuk/modalmaterialmasuk', $data)
            ];
        } else {
            $json = [
                'error' => 'Maaf item belum ada'
            ];
        }
        echo json_encode($json);
    }

    function selesaiTransaksi()
    {
        if ($this->request->isAJAX()) {
            $nofaktur = $this->nomorMaterialMasukDariRequest();
            $noInvoice = $this->noInvoiceDariRequest();
            $noDo = trim((string) $this->request->getPost('no_do'));
            $tglfaktur = $this->request->getPost('tglfaktur');
            $idsupplier = $this->request->getPost('idsupplier');
            $idgudang = $this->request->getPost('idgudang');
            $sumberInput = $this->request->getPost('sumber');
            $sumber = in_array($sumberInput, ['konsinyasi', 'adjustment', 'retur_ng'], true) ? $sumberInput : 'beli';
            $idpelanggan = $this->request->getPost('idpelanggan');

            if ($sumber === 'adjustment') {
                $idsupplier = null;
                $idpelanggan = null;
                if ($noDo === '') {
                    $noDo = $nofaktur;
                }
            } elseif ($sumber === 'konsinyasi') {
                if (empty($idpelanggan)) {
                    return $this->response->setJSON(['error' => 'Data pelanggan (sumber konsinyasi) belum dipilih']);
                }
            } elseif (in_array((int) $idsupplier, [1, 2], true)) {
                return $this->response->setJSON([
                    'error' => 'TRE-CKG dan TRE-CRB harus diproses melalui Permintaan Transfer',
                ]);
            }
            $modelTemp = new Modeltempmaterialmasuk();
            $dataTemp = $modelTemp->getWhere(['detfaktur' => $nofaktur]);

            $invoiceTerpakai = $noInvoice !== null
                && $this->materialMasukPunyaKolomInvoice()
                && db_connect()->table('materialmasuk')
                    ->where('no_invoice', $noInvoice)
                    ->countAllResults() > 0;
            $nomorTransaksiTerpakai = db_connect()->table('materialmasuk')
                ->where('faktur', $nofaktur)
                ->countAllResults() > 0;
            $nomorTransaksiSudahTersimpan = $nomorTransaksiTerpakai
                && db_connect()->table('detail_materialmasuk')
                    ->where('detfaktur', $nofaktur)
                    ->countAllResults() > 0;
            $sumberNoDo = $this->cariPemakaianNoDo($noDo);

            if ($sumber !== 'adjustment' && $noDo === '') {
                $json = ['error' => 'No Surat Jalan tidak boleh kosong'];
            } elseif ($invoiceTerpakai) {
                $json = ['error' => "No. Invoice {$noInvoice} sudah digunakan"];
            } elseif ($nomorTransaksiSudahTersimpan) {
                $json = ['sukses' => 'Transaksi sudah tersimpan sebelumnya.'];
            } elseif ($nomorTransaksiTerpakai) {
                $json = ['error' => 'Nomor transaksi internal sudah terpakai. Silakan refresh halaman lalu coba lagi.'];
            } elseif ($sumberNoDo !== null && $sumber !== 'retur_ng') {
                $json = [
                    'error' => "No Surat Jalan {$noDo} sudah digunakan pada {$sumberNoDo}"
                ];
            } elseif ($dataTemp->getNumRows() == 0) {
                $json = [
                    'error' => 'Maaf, data item untuk invoice ini belum ada'
                ];
            } elseif (strlen($nofaktur) > 20) {
                $json = [
                    'error' => "Nomor transaksi {$nofaktur} memiliki " . strlen($nofaktur) . " karakter, sedangkan kolom materialmasuk.faktur maksimal 20 karakter. Muat ulang halaman lalu tambahkan item kembali."
                ];
            } else {
                $db = db_connect();
                if ($sumber === 'retur_ng') {
                    foreach ($modelTemp->getWhere(['detfaktur' => $nofaktur])->getResultArray() as $tempRow) {
                        if (empty($tempRow['po_keluar_id'])) {
                            return $this->response->setJSON(['error' => 'Penerimaan NG wajib memilih item dari PO NG. Silakan pilih ulang item PO tersebut.']);
                        }
                    }
                }
                $db->transStart();

                $langkahGagal = null;

                $modelMaterialMasuk = new Modelmaterialmasuk();
                $totalSubTotal = 0;
                $poKeluarIdPerItem = [];
                $idsupPerItem = [];
                foreach ($dataTemp->getResultArray() as $total) {
                    $totalSubTotal += intval($total['detsubtotal']);
                    if (!empty($total['po_keluar_id'])) {
                        $poKeluarIdPerItem[(int) $total['po_keluar_id']] = true;
                    }
                    if (!empty($total['idsup'])) {
                        $idsupPerItem[(int) $total['idsup']] = true;
                    }
                }
                // Header cuma nyimpen 1 PO Keluar/supplier buat kompatibilitas
                // lama -- kalau item-nya nyebar ke beberapa PO Keluar/supplier
                // berbeda, dibiarkan kosong (nggak ambigu) karena sumber
                // kebenarannya sekarang di detail_materialmasuk per item.
                $poKeluarIdHeader = count($poKeluarIdPerItem) === 1 ? array_key_first($poKeluarIdPerItem) : null;
                $idsupHeader = count($idsupPerItem) === 1 ? array_key_first($idsupPerItem) : null;

                $headerData = [
                    'faktur' => $nofaktur,
                    'po_keluar_id' => in_array($sumber, ['beli', 'retur_ng'], true) ? $poKeluarIdHeader : null,
                    'no_do' => $noDo,
                    'tglfaktur' => $tglfaktur,
                    'idsup' => in_array($sumber, ['beli', 'retur_ng'], true) ? $idsupHeader : null,
                    'sumber' => $sumber,
                    'idpel' => $sumber === 'konsinyasi' ? $idpelanggan : null,
                    'totalberatmaterial' => $totalSubTotal,
                    'gudang' => $idgudang,
                ];
                if ($this->materialMasukPunyaKolomInvoice()) {
                    $headerData['no_invoice'] = $noInvoice;
                }

                $okHeader = $modelMaterialMasuk->insert($headerData);
                if ($okHeader === false) {
                    $dbErrorMessage = (string) ($db->error()['message'] ?? '');
                    $modelErrorMessage = implode(', ', $modelMaterialMasuk->errors() ?: []);
                    $duplicateRetry = stripos($dbErrorMessage, 'Duplicate entry') !== false
                        || stripos($modelErrorMessage, 'Duplicate entry') !== false;
                    $existingHasDetails = $duplicateRetry
                        && $db->table('detail_materialmasuk')->where('detfaktur', $nofaktur)->countAllResults() > 0;

                    if ($existingHasDetails) {
                        // Request sebelumnya sudah berhasil, tetapi response
                        // mungkin terlambat/terputus sehingga browser mencoba
                        // lagi. Anggap retry tersebut sukses dan jangan
                        // mengulang pengurangan stok atau PO.
                        $db->transRollback();
                        echo json_encode(['sukses' => 'Transaksi sudah tersimpan sebelumnya.']);
                        return;
                    }

                    $langkahGagal = 'header (' . ($modelErrorMessage !== '' ? $modelErrorMessage : ($dbErrorMessage !== '' ? $dbErrorMessage : 'unknown')) . ')';
                }

                $fieldDetail = [];
                $stokData = [];
                foreach ($dataTemp->getResultArray() as $row) {
                    // Item Adjustment Stok/Konsinyasi nggak boleh kecantol ke
                    // PO Keluar manapun, sama kayak aturan lama.
                    $poKeluarIdItem = (in_array($sumber, ['beli', 'retur_ng'], true) && !empty($row['po_keluar_id'])) ? (int) $row['po_keluar_id'] : null;

                    $fieldDetail[] = [
                        'detfaktur' => $row['detfaktur'],
                        'po_keluar_id' => $poKeluarIdItem,
                        'tgl' => $row['tgl'],
                        'idsup' => $row['idsup'],
                        'detmatkode' => $row['detmatkode'],
                        'idmat' => $row['idmat'],
                        'detjml' => $row['detjml'],
                        'detsubtotal' => $row['detsubtotal'],
                        'gudang' => $idgudang,
                    ];

                    $stokData[] = [
                        'materialid' => $row['detmatkode'],
                        'gudang' => $idgudang,
                        'stok' => $row['detjml']
                    ];

                }

                if ($langkahGagal === null) {
                    $modelDetail = new Modeldetailmaterialmasuk();
                    $okDetail = $modelDetail->insertBatch($fieldDetail);
                    if ($okDetail === false) {
                        $langkahGagal = 'detail item (' . implode(', ', $modelDetail->errors() ?: [$db->error()['message'] ?? 'unknown']) . ')';
                    }
                }

                if ($langkahGagal === null) {
                    $modelStok = new ModelStokMaterial();
                    $stokOk = $modelStok->updateOrInsertBatch($stokData);
                    if ($stokOk === false) {
                        $langkahGagal = 'update stok material gagal';
                    }
                    $errStok = $db->error();
                    if (!empty($errStok['message'])) {
                        $langkahGagal = 'update stok material (' . $errStok['message'] . ')';
                    }
                }

                // Update PO hanya setelah header, detail, dan stok berhasil.
                // Dengan urutan ini kegagalan tidak membuat PO atau stok
                // terlihat berubah sementara header belum tersimpan.
                if ($langkahGagal === null) {
                    foreach ($dataTemp->getResultArray() as $row) {
                        $poKeluarIdItem = (in_array($sumber, ['beli', 'retur_ng'], true) && !empty($row['po_keluar_id'])) ? (int) $row['po_keluar_id'] : null;
                        if ($poKeluarIdItem) {
                            (new PoKeluar())->terimaQty($poKeluarIdItem, 'material', (string) $row['detmatkode'], (float) $row['detjml']);
                        }
                    }
                }

                if ($langkahGagal === null && $sumber === 'retur_ng') {
                    foreach (array_keys($poKeluarIdPerItem) as $poId) {
                        (new PoKeluar())->refreshStatusAfterReplacement((int) $poId);
                    }
                }

                if ($langkahGagal === null) {
                    $modelTemp->hapusData($nofaktur);
                }

                if ($langkahGagal !== null) {
                    $db->transRollback();
                } else {
                    $db->transComplete();
                }

                $persisted = $db->table('materialmasuk')->where('faktur', $nofaktur)->countAllResults() === 1
                    && $db->table('detail_materialmasuk')->where('detfaktur', $nofaktur)->countAllResults() > 0;
                if ($langkahGagal !== null || $db->transStatus() === false || !$persisted) {
                    $detailPesan = $langkahGagal ?? ($db->error()['message'] ?? '');
                    log_message('error', 'Gagal simpan Material Masuk: {message}', ['message' => $detailPesan]);
                    $json = ['error' => 'Transaksi Material Masuk gagal disimpan. Tidak ada data yang diubah.' . ($detailPesan !== '' ? ' Sebab: ' . $detailPesan : '')];
                } else {
                    $json = ['sukses' => 'Transaksi Berhasil di Simpan'];
                }
            }

            // // Integrasi Pusher
            // $options = array(
            //     'cluster' => 'ap1',
            //     'useTLS' => true
            // );
            // $pusher = new \Pusher\Pusher(
            //     '8f027ac11961f0fa1906',
            //     '21c84fc4aee41d737c58',
            //     '1826619',
            //     $options
            // );

            // $data['message'] = 'success';
            // $pusher->trigger('my-channel', 'my-event', $data);

            echo json_encode($json);
        } else {
            exit('Maaf tidak bisa dipanggil');
        }
    }

    function simpanPembayaran()
    {
        if ($this->request->isAJAX()) {
            $nofaktur = $this->request->getPost('nofaktur');
            $tglfaktur = $this->request->getPost('tglfaktur');
            $idsupplier = $this->request->getPost('idsupplier');
            $totalbayar = $this->request->getPost('totalbayar');

            $modelMaterialMasuk = new Modelmaterialmasuk();
            $modelMaterialMasuk->insert([
                'faktur' => $nofaktur,
                'tglfaktur' => $tglfaktur,
                'idsup' => $idsupplier,
                'totalberatmaterial' => $totalbayar,
            ]);

            $modelTemp = new Modeltempmaterialmasuk();
            $dataTemp = $modelTemp->getWhere(['detfaktur' => $nofaktur]);

            $fieldDetail = [];
            foreach ($dataTemp->getResultArray() as $row) {
                $fieldDetail[] = [
                    'detfaktur' => $row['detfaktur'],
                    'detmatkode' => $row['detmatkode'],
                    'detberat' => $row['detberat'],
                    'detjml' => $row['detjml'],
                    'detsubtotal' => $row['detsubtotal']
                ];
            }

            $modelDetail = new Modeldetailmaterialmasuk();
            $modelDetail->insertBatch($fieldDetail);

            $modelTemp->hapusData($nofaktur);

            $json = [
                'sukses' => 'Transaksi berhasil di Simpan',
                'cetakfaktur' => site_url('materialmasuk/cetakfaktur/' . $nofaktur)
            ];

            echo json_encode($json);
        }
    }

    function hapusTransaksi()
    {
        if ($this->request->isAJAX()) {
            $faktur = $this->request->getPost('faktur');

            $modelMaterialMasuk = new Modelmaterialmasuk();

            $db = \Config\Database::connect();

            $header = $db->table('materialmasuk')->where('faktur', $faktur)->get()->getRowArray();
            $details = $db->table('detail_materialmasuk')->where('detfaktur', $faktur)->get()->getResultArray();

            // Trigger tri_restore_stokmaterial bakal ngurangin stokmaterial
            // pas baris detail-nya dihapus (SET stok = stok - detjml WHERE
            // id = idmat). Kalau material itu udah kepake transaksi lain
            // (Produksi/Pemakaian Material) sebelum masuk ini dihapus,
            // stoknya bakal minus. Cek dulu stok saat ini masih cukup buat
            // dikurangin -- kalau nggak, tolak & suruh hapus transaksi
            // pemakaiannya dulu.
            $totalPerStokMaterial = [];
            foreach ($details as $detail) {
                $idmat = (int) $detail['idmat'];
                $totalPerStokMaterial[$idmat] = ($totalPerStokMaterial[$idmat] ?? 0) + (float) $detail['detjml'];
            }

            foreach ($totalPerStokMaterial as $idmat => $totalAkanDikurangi) {
                $stokMaterialRow = $db->table('stokmaterial')->where('id', $idmat)->get()->getRowArray();
                $stokSekarang = (float) ($stokMaterialRow['stok'] ?? 0);
                $namaMaterial = $stokMaterialRow['namamaterial'] ?? ('id ' . $idmat);

                if ($stokSekarang < $totalAkanDikurangi) {
                    echo json_encode([
                        'error' => "Material {$namaMaterial} sudah terpakai (Produksi / Pemakaian Material) sehingga stok saat ini ({$stokSekarang}) tidak cukup untuk dikurangi. Hapus dulu transaksi yang memakai material ini, baru hapus Material Masuk ini.",
                    ]);
                    return;
                }
            }

            $db->transStart();

            $db->table('detail_materialmasuk')->delete(['detfaktur' => $faktur]);
            $modelMaterialMasuk->delete($faktur);

            // Utamakan po_keluar_id per baris item (support 1 transaksi isi
            // item dari beberapa PO Keluar). Fallback ke po_keluar_id header
            // cuma buat data lama sebelum kolom ini ada.
            $poKeluarIdHeader = (int) ($header['po_keluar_id'] ?? 0);
            $poKeluar = new PoKeluar();
            foreach ($details as $detail) {
                $poKeluarIdItem = (int) ($detail['po_keluar_id'] ?? $poKeluarIdHeader);
                if ($poKeluarIdItem > 0) {
                    $poKeluar->batalkanQty($poKeluarIdItem, 'material', (string) $detail['detmatkode'], (float) $detail['detjml']);
                }
            }

            $db->transComplete();

            // // Integrasi Pusher
            // $options = array(
            //     'cluster' => 'ap1',
            //     'useTLS' => true
            // );
            // $pusher = new \Pusher\Pusher(
            //     '8f027ac11961f0fa1906',
            //     '21c84fc4aee41d737c58',
            //     '1826619',
            //     $options
            // );

            // $data['message'] = 'success';
            // $pusher->trigger('my-channel', 'my-event', $data);

            $json = $db->transStatus() === false
                ? ['error' => 'Transaksi Material Masuk gagal dihapus. Tidak ada data yang diubah.']
                : ['sukses' => 'Transaksi berhasil di Hapus'];
            echo json_encode($json);
        }
    }

    public function edit($faktur)
    {
        $modelMaterialMasuk = new Modelmaterialmasuk();
        $cekFaktur = $modelMaterialMasuk->cekFaktur($faktur);

        if ($cekFaktur->getNumRows() > 0) {
            $row = $cekFaktur->getRowArray();

            // idsup di header cuma keisi kalau SEMUA item transaksi ini dari
            // supplier yang sama (lihat selesaiTransaksi()) -- kalau NULL
            // padahal sumber-nya 'beli', item-nya nyebar ke beberapa
            // supplier berbeda (dari PO Keluar yang beda-beda).
            if (($row['sumber'] ?? 'beli') === 'adjustment') {
                $namaSupplierTampil = 'Adjustment Stok';
            } elseif (($row['sumber'] ?? '') === 'konsinyasi') {
                $namaSupplierTampil = $row['pelnama'] ?? '-';
            } elseif (empty($row['idsup'])) {
                $namaSupplierTampil = $this->namaSupplierPerItem(db_connect(), $row['faktur']);
            } else {
                $namaSupplierTampil = $row['supnama'] ?? '-';
            }

            $data = [
                'nofaktur' => $row['faktur'],
                'no_invoice' => $row['no_invoice'] ?? '',
                'no_do' => $row['no_do'] ?? '-',
                'tanggal' => $row['tglfaktur'],
                'namasupplier' => $namaSupplierTampil,
                'idsupplier' => $row['supid'],
                'namagudang' => $row['gdgnama'],
                'idgudang' => $row['gdgid'],
            ];
            return view('materialmasuk/formedit', $data);
        } else {
            exit('Data tidak ditemukan');
        }
    }

    public function updateInvoice()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        if (!$this->materialMasukPunyaKolomInvoice()) {
            return $this->response->setJSON([
                'error' => 'Kolom No. Invoice belum tersedia. Jalankan migrasi database dulu.'
            ]);
        }

        $faktur = trim((string) $this->request->getPost('faktur'));
        $noInvoice = $this->noInvoiceDariRequest();

        if ($faktur === '') {
            return $this->response->setJSON(['error' => 'Nomor transaksi tidak ditemukan']);
        }

        $builder = db_connect()->table('materialmasuk')->where('faktur !=', $faktur);
        if ($noInvoice !== null && $builder->where('no_invoice', $noInvoice)->countAllResults() > 0) {
            return $this->response->setJSON(['error' => "No. Invoice {$noInvoice} sudah digunakan"]);
        }

        (new Modelmaterialmasuk())->update($faktur, ['no_invoice' => $noInvoice]);
        return $this->response->setJSON([
            'sukses' => $noInvoice === null ? 'No. Invoice dikosongkan' : 'No. Invoice berhasil disimpan'
        ]);
    }

    function ambilTotalBerat()
    {
        if ($this->request->isAJAX()) {
            $nofaktur = $this->request->getPost('nofaktur');
            $modelDetail = new Modeldetailmaterialmasuk();
            $totalBerat = $modelDetail->ambilTotalBerat($nofaktur);

            $json = [
                'totalberat' => "Total : " . number_format($totalBerat, 0, ",", ".") . " " . "KG"
            ];
            echo json_encode($json);
        }
    }

    function tampilDataDetail()
    {
        if ($this->request->isAJAX()) {
            $nofaktur = $this->request->getPost('nofaktur');

            $modelDetail = new Modeldetailmaterialmasuk();
            $dataTemp = $modelDetail->tampilDataTemp($nofaktur);
            $data = [
                'tampildata' => $dataTemp
            ];

            $json = [
                'data' => view('materialmasuk/datadetail', $data)
            ];
            echo json_encode($json);
        }
    }

    function hapusItemDetail()
    {
        if ($this->request->isAJAX()) {
            $id = $this->request->getPost('id');

            $modelDetail = new Modeldetailmaterialmasuk();
            $modelMaterialMasuk = new Modelmaterialmasuk();

            $rowData = $modelDetail->find($id);
            $noFaktur = $rowData['detfaktur'];

            $db = db_connect();

            $header = $db->table('materialmasuk')->where('faktur', $noFaktur)->get()->getRowArray();

            // Sama kayak hapusTransaksi() -- cek dulu stokmaterial masih
            // cukup buat dikurangin trigger tri_restore_stokmaterial,
            // soalnya material ini bisa aja udah kepake Produksi/Pemakaian
            // Material duluan.
            $idmat = (int) $rowData['idmat'];
            $stokMaterialRow = $db->table('stokmaterial')->where('id', $idmat)->get()->getRowArray();
            $stokSekarang = (float) ($stokMaterialRow['stok'] ?? 0);
            $namaMaterial = $stokMaterialRow['namamaterial'] ?? ('id ' . $idmat);
            if ($stokSekarang < (float) $rowData['detjml']) {
                echo json_encode([
                    'error' => "Material {$namaMaterial} sudah terpakai (Produksi / Pemakaian Material) sehingga stok saat ini ({$stokSekarang}) tidak cukup untuk dikurangi. Hapus dulu transaksi yang memakai material ini, baru hapus item ini.",
                ]);
                return;
            }

            $db->transStart();

            $modelDetail->delete($id);

            // Utamakan po_keluar_id di baris item itu sendiri (support 1
            // transaksi isi item dari beberapa PO Keluar). Fallback ke
            // po_keluar_id header cuma buat data lama sebelum kolom ini ada.
            $poKeluarId = (int) ($rowData['po_keluar_id'] ?? ($header['po_keluar_id'] ?? 0));
            if ($poKeluarId > 0) {
                (new PoKeluar())->batalkanQty($poKeluarId, 'material', (string) $rowData['detmatkode'], (float) $rowData['detjml']);
            }

            $totalBerat = $modelDetail->ambilTotalBerat($noFaktur);

            $modelMaterialMasuk->update($noFaktur, [
                'totalberatmaterial' => $totalBerat
            ]);
            $db->transComplete();

            // // Integrasi Pusher
            // $options = array(
            //     'cluster' => 'ap1',
            //     'useTLS' => true
            // );
            // $pusher = new \Pusher\Pusher(
            //     '8f027ac11961f0fa1906',
            //     '21c84fc4aee41d737c58',
            //     '1826619',
            //     $options
            // );

            // $data['message'] = 'success';
            // $pusher->trigger('my-channel', 'my-event', $data);

            $json = $db->transStatus() === false
                ? ['error' => 'Item Material Masuk gagal dihapus. Tidak ada data yang diubah.']
                : ['sukses' => 'Item Berhasil di Hapus'];
            echo json_encode($json);
        }
    }

    public function editItem()
    {
        if ($this->request->isAJAX()) {
            $iddetail = $this->request->getPost('iddetail');
            $idmat = $this->request->getPost('idmat');
            $jml = $this->request->getPost('jml');

            $modelDetail = new Modeldetailmaterialmasuk();
            $modelMaterialMasuk = new Modelmaterialmasuk();
            $modelStok = new ModelStokMaterial();

            $rowData = $modelDetail->find($iddetail);
            $noFaktur = $rowData['detfaktur'];
            $jumlahSebelumnya = $rowData['detjml'];

            $selisih = $jml - $jumlahSebelumnya;

            $existingStok = $modelStok->where('id', $idmat)->first();
            $db = db_connect();

            $header = $db->table('materialmasuk')->where('faktur', $noFaktur)->get()->getRowArray();
            // Utamakan po_keluar_id di baris item itu sendiri (support 1
            // transaksi isi item dari beberapa PO Keluar). Fallback ke
            // po_keluar_id header cuma buat data lama sebelum kolom ini ada.
            $poKeluarId = (int) ($rowData['po_keluar_id'] ?? ($header['po_keluar_id'] ?? 0));

            $db->transStart();

            if ($existingStok) {
                $newStok = $existingStok['stok'] + $selisih;
                $modelStok->update($existingStok['id'], ['stok' => $newStok]);
            }

            $modelDetail->update($iddetail, [
                'detjml' => $jml,
                'detsubtotal' => $jml
            ]);

            if ($poKeluarId > 0 && $selisih != 0) {
                $poKeluar = new PoKeluar();
                if ($selisih > 0) {
                    $poKeluar->terimaQty($poKeluarId, 'material', (string) $rowData['detmatkode'], (float) $selisih);
                } else {
                    $poKeluar->batalkanQty($poKeluarId, 'material', (string) $rowData['detmatkode'], (float) abs($selisih));
                }
            }

            $totalBerat = $modelDetail->ambilTotalBerat($noFaktur);

            $modelMaterialMasuk->update($noFaktur, [
                'totalberatmaterial' => $totalBerat
            ]);
            $db->transComplete();

            // // Integrasi Pusher
            // $options = array(
            //     'cluster' => 'ap1',
            //     'useTLS' => true
            // );
            // $pusher = new \Pusher\Pusher(
            //     '8f027ac11961f0fa1906',
            //     '21c84fc4aee41d737c58',
            //     '1826619',
            //     $options
            // );

            // $data['message'] = 'success';
            // $pusher->trigger('my-channel', 'my-event', $data);

            $json = $db->transStatus() === false
                ? ['error' => 'Item Material Masuk gagal diperbarui. Tidak ada data yang diubah.']
                : ['sukses' => 'Item Berhasil di Update'];
            echo json_encode($json);
        }
    }

    public function simpanItemDetail()
    {
        if ($this->request->isAJAX()) {
            $nofaktur = $this->request->getPost('nofaktur');
            $idsupplier = $this->request->getPost('idsupplier');
            $idgudang = $this->request->getPost('idgudang');
            $idmat = $this->request->getPost('idmat');
            $tglfaktur = $this->request->getPost('tglfaktur');
            $materialid = $this->request->getPost('materialid');
            $jml = $this->request->getPost('jml');

            $modelTempMaterialMasuk = new Modeldetailmaterialmasuk();
            $modelMaterialMasuk = new Modelmaterialmasuk();
            $modelStok = new ModelStokMaterial();

            $existingStok = $modelStok->where('id', $idmat)->first();

            $db = db_connect();
            $db->transStart();

            if ($existingStok) {
                $newStok = $existingStok['stok'] + $jml;
                $modelStok->update($existingStok['id'], ['stok' => $newStok]);
            } else {
                $modelStok->insert([
                    'materialid' => $materialid,
                    'gudang' => $idgudang,
                    'stok' => $jml
                ]);
            }

            $existingRow = $modelTempMaterialMasuk->where('detmatkode', $materialid)->where('detfaktur', $nofaktur)->first();

            if ($existingRow) {
                $newJml = $existingRow['detjml'] + $jml;
                $newSubtotal = $existingRow['detsubtotal'] + $jml;

                $modelTempMaterialMasuk->update($existingRow['id'], [
                    'detjml' => $newJml,
                    'detsubtotal' => $newSubtotal
                ]);
            } else {
                $modelTempMaterialMasuk->insert([
                    'detfaktur' => $nofaktur,
                    'tgl' => $tglfaktur,
                    'idsup' => $idsupplier,
                    'detmatkode' => $materialid,
                    'detjml' => $jml,
                    'detsubtotal' => intval($jml)
                ]);
            }

            $totalBerat = $modelTempMaterialMasuk->ambilTotalBerat($nofaktur);

            $modelMaterialMasuk->update($nofaktur, [
                'totalberatmaterial' => $totalBerat
            ]);
            $db->transComplete();

            // // Integrasi Pusher
            // $options = array(
            //     'cluster' => 'ap1',
            //     'useTLS' => true
            // );
            // $pusher = new \Pusher\Pusher(
            //     '8f027ac11961f0fa1906',
            //     '21c84fc4aee41d737c58',
            //     '1826619',
            //     $options
            // );

            // $data['message'] = 'success';
            // $pusher->trigger('my-channel', 'my-event', $data);

            $json = $db->transStatus() === false
                ? ['error' => 'Item Material Masuk gagal ditambahkan. Tidak ada data yang diubah.']
                : ['sukses' => 'Item berhasil ditambahkan'];
            echo json_encode($json);
        } else {
            exit('Maaf tidak bisa dipanggil');
        }
    }
}
