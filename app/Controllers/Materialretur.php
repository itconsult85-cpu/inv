<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Materialretur extends BaseController
{
    private function headerByHash(string $hash): ?array
    {
        return db_connect()->table('materialmasuk mm')
            ->select('mm.*, s.supnama, g.gdgnama')
            ->join('supplier s', 's.supid = mm.idsup', 'left')
            ->join('gudang g', 'g.gdgid = mm.gudang', 'left')
            ->where('SHA1(mm.faktur) = ' . db_connect()->escape($hash), null, false)
            ->get()->getRowArray() ?: null;
    }

    private function buatNomorRetur(): string
    {
        $db = db_connect();
        do {
            $nomor = 'RTM-' . date('Ymd-His') . '-' . random_int(100, 999);
        } while ($db->table('retur_material')->where('nomor_retur', $nomor)->countAllResults() > 0);

        return $nomor;
    }

    public function form(string $hash)
    {
        $header = $this->headerByHash($hash);
        if (!$header) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Transaksi Material Masuk tidak ditemukan.');
        }

        $db = db_connect();
        $details = $db->table('detail_materialmasuk dmm')
            ->select("dmm.id, dmm.detfaktur, dmm.idmat, dmm.detmatkode, dmm.detjml, m.matkode, m.matnama, s.supnama, COALESCE(r.qty_retur, 0) AS qty_retur, (dmm.detjml - COALESCE(r.qty_retur, 0)) AS sisa_retur", false)
            ->join('material m', 'm.matid = dmm.detmatkode', 'left')
            ->join('supplier s', 's.supid = dmm.idsup', 'left')
            ->join('(SELECT material_masuk_detail_id, SUM(qty_retur) AS qty_retur FROM retur_material_detail GROUP BY material_masuk_detail_id) r', 'r.material_masuk_detail_id = dmm.id', 'left')
            ->where('dmm.detfaktur', $header['faktur'])
            ->orderBy('dmm.id', 'ASC')
            ->get()->getResultArray();

        return view('materialretur/form', [
            'header' => $header,
            'details' => $details,
        ]);
    }

    public function simpan()
    {
        $db = db_connect();
        $faktur = trim((string) $this->request->getPost('faktur'));
        $tglRetur = trim((string) $this->request->getPost('tgl_retur'));
        $catatan = trim((string) $this->request->getPost('catatan'));
        $qtyInput = (array) $this->request->getPost('qty');
        $keteranganInput = (array) $this->request->getPost('keterangan');

        if ($faktur === '' || $tglRetur === '') {
            return redirect()->back()->withInput()->with('error', 'Transaksi dan tanggal retur wajib diisi.');
        }

        $header = $db->table('materialmasuk')->where('faktur', $faktur)->get()->getRowArray();
        if (!$header) {
            return redirect()->back()->withInput()->with('error', 'Transaksi Material Masuk tidak ditemukan.');
        }

        $detailIds = array_values(array_filter(array_map('intval', array_keys($qtyInput))));
        if (!$detailIds) {
            return redirect()->back()->withInput()->with('error', 'Isi minimal satu qty retur NG.');
        }

        $db->transStart();
        $returRows = [];
        $supplierIds = [];
        $error = null;

        foreach ($detailIds as $detailId) {
            $qty = (float) ($qtyInput[$detailId] ?? 0);
            if ($qty <= 0) {
                continue;
            }

            $detail = $db->table('detail_materialmasuk')
                ->where('id', $detailId)
                ->where('detfaktur', $faktur)
                ->get()->getRowArray();
            if (!$detail) {
                $error = 'Detail material retur tidak valid.';
                break;
            }

            $sudahDiretur = (float) $db->table('retur_material_detail')
                ->selectSum('qty_retur')
                ->where('material_masuk_detail_id', $detailId)
                ->get()->getRow()->qty_retur;
            $sisaRetur = (float) $detail['detjml'] - $sudahDiretur;
            if ($qty > $sisaRetur + 0.000001) {
                $error = "Qty retur untuk material {$detail['detmatkode']} melebihi sisa yang dapat diretur ({$sisaRetur}).";
                break;
            }

            $stok = $db->table('stokmaterial')->where('id', (int) $detail['idmat'])->get()->getRowArray();
            if (!$stok || (float) $stok['stok'] < $qty) {
                $stokSekarang = (float) ($stok['stok'] ?? 0);
                $error = "Stok material {$detail['detmatkode']} tidak cukup untuk retur. Stok saat ini: {$stokSekarang}.";
                break;
            }

            $returRows[] = [
                'detail' => $detail,
                'qty' => $qty,
                'keterangan' => trim((string) ($keteranganInput[$detailId] ?? '')) ?: 'NG',
            ];
            if (!empty($detail['idsup'])) {
                $supplierIds[(int) $detail['idsup']] = true;
            }
        }

        if (!$returRows && $error === null) {
            $error = 'Isi minimal satu qty retur NG lebih besar dari nol.';
        }

        if ($error === null) {
            $returId = $db->table('retur_material')->insert([
                'nomor_retur' => $this->buatNomorRetur(),
                'material_masuk_faktur' => $faktur,
                'tgl_retur' => $tglRetur,
                'idsup' => count($supplierIds) === 1 ? (int) array_key_first($supplierIds) : null,
                'gudang' => $header['gudang'] ?: null,
                'catatan' => $catatan !== '' ? $catatan : null,
                'user_id' => session()->get('userid'),
                'created_at' => date('Y-m-d H:i:s'),
            ], true);

            if (!$returId) {
                $error = 'Header retur gagal disimpan.';
            }
        }

        if ($error === null) {
            foreach ($returRows as $row) {
                $detail = $row['detail'];
                $okStok = $db->table('stokmaterial')
                    ->where('id', (int) $detail['idmat'])
                    ->where('stok >=', $row['qty'])
                    ->set('stok', 'stok - ' . $row['qty'], false)
                    ->update();
                if (!$okStok || $db->affectedRows() < 1) {
                    $error = 'Stok berubah saat proses retur. Transaksi dibatalkan, silakan ulangi.';
                    break;
                }

                $okDetail = $db->table('retur_material_detail')->insert([
                    'retur_id' => $returId,
                    'material_masuk_detail_id' => $detail['id'],
                    'idmat' => $detail['idmat'],
                    'materialid' => $detail['detmatkode'],
                    'detmatkode' => $detail['detmatkode'],
                    'qty_retur' => $row['qty'],
                    'keterangan' => $row['keterangan'],
                ]);
                if (!$okDetail) {
                    $error = 'Detail retur gagal disimpan.';
                    break;
                }
            }
        }

        if ($error !== null) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', $error);
        }

        $db->transComplete();
        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Retur material gagal disimpan. Tidak ada data yang diubah.');
        }

        return redirect()->to(site_url('materialmasuk/data'))->with('success', 'Retur material NG berhasil disimpan dan stok sudah dikurangi.');
    }
}
