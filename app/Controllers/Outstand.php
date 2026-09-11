<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ModelPelanggan;
use \Hermawan\DataTables\DataTable;

class Outstand extends BaseController
{
    protected $db;
    protected $modelPelanggan;

    public function __construct()
    {
        $this->db = db_connect();
        $this->modelPelanggan = new ModelPelanggan();
    }

    public function data()
    {
        $pelanggans = $this->modelPelanggan->whereNotIn('pelid', [1, 2])->findAll();
        return view('outstand/viewdata', ['pelanggans' => $pelanggans]);
    }

    public function listData()
    {
        if ($this->request->isAJAX()) {
            $db = \Config\Database::connect();
            $builder = $db->table('outstanding')->select('nopo, kodebrg, qty, tgl, terkirim, kekurangan, outharga, outkirim, idpel')
                ->whereNotIn('outstanding.idpel', [1, 2]);

            return DataTable::of($builder)
                ->addNumbering('nomor')
                ->setSearchableColumns(['kodebrg', 'nopo'])
                ->filter(function ($builder, $request) {
                    if ($request->pelanggan) {
                        $builder->where('outstanding.idpel', $request->pelanggan);
                    }
                    if ($request->filter_kekurangan === '0') {
                        $builder->where('kekurangan', 0);
                    } elseif ($request->filter_kekurangan === '1') {
                        $builder->where('kekurangan !=', 0);
                    } else {
                        $builder->where('kekurangan <', 0);
                    }
                })
                ->format('qty', function ($value) {
                    return number_format($value, 0, ',', '.');
                })
                ->format('terkirim', function ($value) {
                    return number_format($value, 0, ',', '.');
                })
                ->format('kekurangan', function ($value) {
                    $formattedValue = number_format(abs($value), 0, ',', '.');
                    if ($value < 0) {
                        return '<span style="color: red;">' . $formattedValue . '</span>';
                    }
                    return $formattedValue;
                })
                ->format('outharga', function ($value) {
                    $formattedValue = number_format(abs($value), 0, ',', '.');
                    if ($value < 0) {
                        return '<span style="color: green;">' . $formattedValue . '</span>';
                    }
                    return $formattedValue;
                })
                ->format('outkirim', function ($value) {
                    return number_format($value, 0, ',', '.');
                })
                ->format('tgl', function ($value) {
                    return date('d-m-Y', strtotime($value));
                })
                ->toJson(true);
        }
    }

    public function cetak()
    {
        $idpel = $this->request->getGet('pelanggan');
        $filterKekurangan = $this->request->getGet('filter_kekurangan');

        $builder = $this->db->table('outstanding o')
            ->select('o.nopo, o.kodebrg, o.qty, o.tgl, o.terkirim, o.kekurangan, p.pelnama')
            ->join('pelanggan p', 'p.pelid = o.idpel', 'left')
            ->whereNotIn('o.idpel', [1, 2])
            ->orderBy('o.nopo', 'ASC')
            ->orderBy('o.kodebrg', 'ASC');

        $namaPelangganCetak = 'Semua Pelanggan';
        if ($idpel) {
            $builder->where('o.idpel', $idpel);
            $pelangganTerpilih = $this->modelPelanggan->find($idpel);
            if ($pelangganTerpilih) {
                $namaPelangganCetak = $pelangganTerpilih['pelnama'];
            }
        }

        if ($filterKekurangan === '0') {
            $builder->where('o.kekurangan', 0);
        } elseif ($filterKekurangan === '1') {
            $builder->where('o.kekurangan !=', 0);
        } else {
            $builder->where('o.kekurangan <', 0);
        }

        $rows = $builder->get()->getResultArray();

        $poGroups = [];
        foreach ($rows as $row) {
            $nopo = $row['nopo'];
            if (!isset($poGroups[$nopo])) {
                $poGroups[$nopo] = [
                    'nopo' => $nopo,
                    'tgl' => $row['tgl'],
                    'pelnama' => $row['pelnama'] ?: '-',
                    'items' => [],
                ];
            }
            $poGroups[$nopo]['items'][] = [
                'kodebrg' => $row['kodebrg'],
                'qty' => $row['qty'],
                'terkirim' => $row['terkirim'],
                'kekurangan' => $row['kekurangan'],
            ];
        }

        return view('outstand/cetak', [
            'poGroups' => array_values($poGroups),
            'dicetakOleh' => session()->namauser ?? '-',
            'namaPelangganCetak' => $namaPelangganCetak,
        ]);
    }

    public function countTotal()
    {
        $filterValue = $this->request->getPost('filter_kekurangan');
        $db = \Config\Database::connect();
        $builder = $db->table('outstanding')
            ->whereNotIn('outstanding.idpel', [1, 2])
            ->groupStart()
            ->where('MONTH(tglkirim)', date('m'))
            ->where('YEAR(tglkirim)', date('Y'))
            ->orWhere('tglkirim', null)
            ->groupEnd();;

        if ($filterValue === '0') {
            $builder->where('kekurangan', 0);
        } elseif ($filterValue === '1') {
            $builder->where('kekurangan !=', 0);
        } else {
            $builder->where('kekurangan <', 0);
        }

        $builder->selectSum('outharga')->selectSum('outkirim');
        $result = $builder->get()->getRow();

        $totalOutharga = $result->outharga;
        $totalOutkirim = $result->outkirim;

        return [
            'totalOutharga' => $totalOutharga,
            'totalOutkirim' => $totalOutkirim,
        ];
    }
}
