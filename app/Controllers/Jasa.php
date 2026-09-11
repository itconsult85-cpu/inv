<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ModelDataJasa;
use App\Models\ModelJasa;
use \Hermawan\DataTables\DataTable;

class Jasa extends BaseController
{
    private function relasiJasa(int $id): array
    {
        return $this->periksaRelasiMaster([
            ['table' => 'po', 'column' => 'jasaid', 'label' => 'PO'],
            ['table' => 'detail_po', 'column' => 'jasaid', 'label' => 'Detail PO'],
            ['table' => 'outstanding', 'column' => 'jasaid', 'label' => 'Outstanding PO'],
            ['table' => 'temp_po', 'column' => 'detidjasa', 'label' => 'Draft PO'],
        ], $id);
    }

    public function pemakaian()
    {
        $id = (int) $this->request->getGet('id');
        return $this->response->setJSON(['pemakaian' => $this->relasiJasa($id)]);
    }

    public function formtambah()
    {
        $json = ['data' => view('jasa/modaltambah')];
        echo json_encode($json);
    }

    public function simpan()
    {
        $namajasa = $this->request->getPost('namajasa');
        $hargaModal = (float) $this->request->getPost('harga_modal');

        $validation = \Config\Services::validation();

        $valid = $this->validate([
            'namajasa' => [
                'rules' => 'required|is_unique[jasa.namajasa]',
                'label' => 'Nama Jasa',
                'errors' => [
                    'required' => '{field} tidak boleh kosong',
                    'is_unique' => '{field} sudah digunakan'
                ]
            ],
        ]);

        if (!$valid) {
            $json = [
                'error' => [
                    'errNamaJasa' => $validation->getError('namajasa'),
                ]
            ];
        } else {
            $modelJasa = new ModelJasa();

            $modelJasa->insert([
                'namajasa' => $namajasa,
                'harga_modal' => $hargaModal,
            ]);

            $rowData = $modelJasa->ambilDataTerakhir()->getRowArray();

            $json = [
                'sukses' => 'Data Jasa Berhasil di Simpan, ambil data terakhir ?',
                'namajasa' => $rowData['namajasa'],
                'idjasa' => $rowData['idjasa']
            ];
        }

        echo json_encode($json);
    }

    public function modalData()
    {
        if ($this->request->isAJAX()) {
            $json = [
                'data' => view('jasa/modaldata')
            ];
            echo json_encode($json);
        }
    }

    public function listData()
    {
        if ($this->request->isAJAX()) {
            $db = \Config\Database::connect();
            $builder = $db->table('jasa')->select('idjasa,namajasa,harga_modal');

            return DataTable::of($builder)
                ->addNumbering('nomor')
                ->add('aksi', function ($row) {
                    return "<button type=\"button\" class=\"btn btn-sm btn-info\" title=\"Pilih Data\" onclick=\"pilih('" . $row->idjasa . "','" . $row->namajasa . "')\"><i class=\"fa fa-check\"></i></button>&nbsp
                    <button type=\"button\" class=\"btn btn-sm btn-primary\" title=\"Edit Data\" id=\"tombolEditJasa\" onclick=\"editData('" . $row->idjasa . "','" . $row->namajasa . "','" . (float) $row->harga_modal . "')\"><i class=\"fa fa-edit\"></i></button>&nbsp
                    <button type=\"button\" class=\"btn btn-sm btn-danger\" title=\"Hapus Data\" onclick=\"hapus('" . $row->idjasa . "','" . $row->namajasa . "')\"><i class=\"fa fa-trash-alt\"></i></button>";
                })
                ->format('harga_modal', function ($value) {
                    return 'Rp ' . number_format((float) $value, 0, ',', '.');
                })
                ->toJson(true);
        }
    }

    public function update()
    {
        $idJasa = $this->request->getPost('id_jasa');
        $namajasa = $this->request->getPost('editNamaJasa');
        $hargaModal = (float) $this->request->getPost('editHargaModal');

        $modelJasa = new ModelJasa();
        $jasaLama = $modelJasa->find($idJasa);
        $pemakaian = $this->relasiJasa((int) $idJasa);
        if ($pemakaian && $jasaLama && trim((string) $jasaLama['namajasa']) !== trim((string) $namajasa)) {
            return $this->response->setJSON([
                'error' => [
                    'errnamaJasa' => $this->pesanRelasiMaster('jasa', $pemakaian),
                ],
            ]);
        }

        $validation = \Config\Services::validation();

        $valid = $this->validate([
            'editNamaJasa' => [
                'rules' => 'required|is_unique[jasa.namajasa,idjasa,' . $idJasa . ']',
                'label' => 'Nama Jasa',
                'errors' => [
                    'required' => '{field} tidak boleh kosong',
                    'is_unique' => '{field} sudah digunakan jasa lain'
                ]
            ],
        ]);

        if (!$valid) {
            $json = [
                'error' => [
                    'errnamaJasa' => $validation->getError('editNamaJasa'),
                ]
            ];
        } else {
            $modelJasa->update($idJasa, [
                'namajasa' => $namajasa,
                'harga_modal' => $hargaModal,
            ]);

            $json = [
                'sukses' => 'Data Jasa Berhasil di Update'
            ];
        }

        echo json_encode($json);
    }

    function hapus()
    {
        if ($this->request->isAJAX()) {
            $id = $this->request->getPost('id');

            $pemakaian = $this->relasiJasa((int) $id);
            if ($pemakaian) {
                return $this->response->setJSON([
                    'error' => $this->pesanRelasiMaster('jasa', $pemakaian),
                ]);
            }

            $modelJasa = new ModelJasa();

            $modelJasa->delete($id);

            $json = [
                'sukses' => 'Data Jasa Berhasil di Hapus'
            ];
            echo json_encode($json);
        }
    }
}
