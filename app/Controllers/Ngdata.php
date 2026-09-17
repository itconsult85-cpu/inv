<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Modelmaterial;
use App\Models\Modelng;
use App\Models\ModelSupplier;
use \Hermawan\DataTables\DataTable;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Ngdata extends BaseController
{
    protected $db;
    protected $modelSupplier;

    public function __construct()
    {
        $this->db = db_connect();
        $this->modelSupplier = new ModelSupplier();
    }

    public function data()
    {
        $suppliers = $this->modelSupplier->findAll();
        return view('ngdata/viewdata', ['suppliers' => $suppliers]);
    }

    public function listData()
    {
        if ($this->request->isAJAX()) {
            $tglawal = $this->request->getPost('tglawal');
            $tglakhir = $this->request->getPost('tglakhir');

            $db = \Config\Database::connect();
            $builder = $db->table('ngdata')
                ->select('idsup, supnama, matjenis, matnama, SUM(beratmatkeluar) as total_beratmatkeluar, SUM(beratmatmasuk) as total_beratmatmasuk, SUM(beratng) as total_beratng, tgl')
                ->join('supplier', 'supplier.supid = ngdata.idsup')
                ->join('material', 'material.matid = ngdata.matjenis')
                ->groupBy('idsup, matnama');

            if ($tglawal && $tglakhir) {
                $builder->where('ngdata.tgl >=', $tglawal)
                    ->where('ngdata.tgl <=', $tglakhir);
            }

            return DataTable::of($builder)
                ->addNumbering('nomor')
                ->add('aksi', function ($row) {
                    return '<button type="button" class="btn btn-sm btn-outline-primary" onclick="detailNg('
                        . (int) $row->idsup . ',' . (int) $row->matjenis
                        . ')" title="Lihat dan koreksi transaksi sumber">'
                        . '<i class="fas fa-edit"></i> Koreksi</button>';
                })
                ->setSearchableColumns(['supnama', 'matnama'])
                ->filter(function ($builder, $request) {
                    if (\App\Libraries\AccessControl::can('material.raw_produk.print')) {
                        if ($request->supplier) {
                            $builder->where('ngdata.idsup', $request->supplier);
                        }
                    }
                })
                ->format('total_beratmatkeluar', function ($value) {
                    $formatted = number_format(abs($value), 2, ',', '.');
                    if ($value < 0) {
                        return "<span style='color: red;'>$formatted</span>";
                    } elseif ($value > 0) {
                        return "<span style='color: green;'>$formatted</span>";
                    } else {
                        return $formatted;
                    }
                })
                ->format('total_beratmatmasuk', function ($value) {
                    $formatted = number_format(abs($value), 2, ',', '.');
                    if ($value < 0) {
                        return "<span style='color: red;'>$formatted</span>";
                    } elseif ($value > 0) {
                        return "<span style='color: green;'>$formatted</span>";
                    } else {
                        return $formatted;
                    }
                })
                ->format('total_beratng', function ($value) {
                    $formatted = number_format(abs($value), 2, ',', '.');
                    if ($value < 0) {
                        return "<span style='color: red;'>$formatted</span>";
                    } elseif ($value > 0) {
                        return "<span style='color: green;'>$formatted</span>";
                    } else {
                        return $formatted;
                    }
                })
                ->toJson(true);
        }
    }

    public function detail()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $supplierId = (int) $this->request->getPost('idsup');
        $materialId = (int) $this->request->getPost('matjenis');
        if ($supplierId <= 0 || $materialId <= 0) {
            return $this->response->setJSON(['error' => 'Parameter supplier atau material tidak valid.']);
        }

        $rows = (new Modelng())->detailBySupplierMaterial($supplierId, $materialId);
        return $this->response->setJSON([
            'data' => view('ngdata/modal_detail', ['rows' => $rows]),
        ]);
    }


    public function cetak_raw_produk()
    {
        return view('laporan/viewrawproduk');
    }

    public function cetak_raw_produk_periode()
    {
        $tombolCetak = $this->request->getPost('btnCetak');
        $tombolExport = $this->request->getPost('btnExport');
        $tglawal = $this->request->getPost('tglawal');
        $tglakhir = $this->request->getPost('tglakhir');

        $modelRawProduk = new Modelng();
        $modelMaterial = new Modelmaterial();
        $modelSupplier = new ModelSupplier();

        $dataLaporan = $modelRawProduk->laporanPerPeriode($tglawal, $tglakhir);

        if (isset($tombolCetak)) {
            $data = [
                'datalaporan' => $dataLaporan,
                'tglawal' => $tglawal,
                'tglakhir' => $tglakhir,
                'modelMaterial' => $modelMaterial,
                'modelSupplier' => $modelSupplier,
            ];

            return view('laporan/cetakLaporanRawProduk', $data);
        }

        if (isset($tombolExport)) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $tglawal = $_POST['tglawal'];
            $tglakhir = $_POST['tglakhir'];

            $tglawal = date('d F Y', strtotime($tglawal));
            $tglakhir = date('d F Y', strtotime($tglakhir));


            $sheet->setCellValue('A1', "TRISENTOSA RAYA");
            $sheet->setCellValue('A2', "Data Raw Produk");
            $sheet->setCellValue('A3', "Periode : $tglawal - $tglakhir");
            $sheet->mergeCells('A1:G1');
            $sheet->mergeCells('A2:G2');
            $sheet->mergeCells('A3:G3');
            $sheet->getStyle('A1')->getFont()->setBold(true);
            $sheet->getStyle('A2')->getFont()->setBold(true);
            $sheet->getStyle('A3')->getFont()->setBold(true);

            $styleColumn = [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ]
            ];

            $borderArray = [
                'borders' => [
                    'top' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                    'left' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                    'right' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                    'bottom' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ]
            ];

            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('B')->setAutoSize(true);
            $sheet->getColumnDimension('C')->setAutoSize(true);
            $sheet->getColumnDimension('D')->setAutoSize(true);
            $sheet->getColumnDimension('E')->setAutoSize(true);
            $sheet->getColumnDimension('F')->setAutoSize(true);
            $sheet->getColumnDimension('G')->setAutoSize(true);

            $sheet->setCellValue('A5', "No");
            $sheet->setCellValue('B5', "Vendor");
            $sheet->setCellValue('C5', "Tanggal");
            $sheet->setCellValue('D5', "Material");
            $sheet->setCellValue('E5', "Berat Keluar (KG)");
            $sheet->setCellValue('F5', "Berat Masuk (KG)");
            $sheet->setCellValue('G5', "Stok Vendor (KG)");

            $sheet->getStyle('A1')->applyFromArray($styleColumn);
            $sheet->getStyle('A2')->applyFromArray($styleColumn);
            $sheet->getStyle('A3')->applyFromArray($styleColumn);
            $sheet->getStyle('A5')->applyFromArray($styleColumn);
            $sheet->getStyle('B5')->applyFromArray($styleColumn);
            $sheet->getStyle('C5')->applyFromArray($styleColumn);
            $sheet->getStyle('D5')->applyFromArray($styleColumn);
            $sheet->getStyle('E5')->applyFromArray($styleColumn);
            $sheet->getStyle('F5')->applyFromArray($styleColumn);
            $sheet->getStyle('G5')->applyFromArray($styleColumn);

            $sheet->getStyle('A5')->applyFromArray($borderArray);
            $sheet->getStyle('B5')->applyFromArray($borderArray);
            $sheet->getStyle('C5')->applyFromArray($borderArray);
            $sheet->getStyle('D5')->applyFromArray($borderArray);
            $sheet->getStyle('E5')->applyFromArray($borderArray);
            $sheet->getStyle('F5')->applyFromArray($borderArray);
            $sheet->getStyle('G5')->applyFromArray($borderArray);

            $no = 1;
            $numRow = 6;
            $stokVendor = [];

            foreach ($dataLaporan->getResultArray() as $row) {
                    $tgl = date('d F Y', strtotime($row['tgl']));

                    $material = $modelMaterial->getMaterialByJenis($row['matjenis']);
                    $matnama = $material['matnama'];
                    $supplier = $modelSupplier->getSupllierById($row['idsup']);
                    $supnama = $supplier['supnama'];

                    $beratMatMasuk = $row['beratmatmasuk'];
                    $beratMatKeluar = $row['beratmatkeluar'];
                    $beratNg = $beratMatMasuk - $beratMatKeluar;

                    $key = $row['idsup'] . '-' . $row['matjenis'] . '-' . $row['tgl'];
                    $stokVendor[$key] = isset($stokVendor[$key]) ? $stokVendor[$key] + $beratNg : $beratNg;

                    $sheet->setCellValue('A' . $numRow, $no);
                    $sheet->setCellValue('B' . $numRow, $supnama);
                    $sheet->setCellValue('C' . $numRow, $tgl);
                    $sheet->setCellValue('D' . $numRow, $matnama);
                    $sheet->setCellValue('E' . $numRow, $row['beratmatkeluar']);
                    $sheet->setCellValue('F' . $numRow, $row['beratmatmasuk']);
                    if ($beratNg < 0) {
                        $beratNg = abs($beratNg);
                        $sheet->getStyle('G' . $numRow)->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
                    }
                    $sheet->setCellValue('G' . $numRow, $beratNg);

                    $sheet->getStyle('A' . $numRow)->applyFromArray($styleColumn);

                    $sheet->getStyle('A' . $numRow)->applyFromArray($borderArray);
                    $sheet->getStyle('B' . $numRow)->applyFromArray($borderArray);
                    $sheet->getStyle('C' . $numRow)->applyFromArray($borderArray);
                    $sheet->getStyle('D' . $numRow)->applyFromArray($borderArray);
                    $sheet->getStyle('E' . $numRow)->applyFromArray($borderArray);
                    $sheet->getStyle('F' . $numRow)->applyFromArray($borderArray);
                    $sheet->getStyle('G' . $numRow)->applyFromArray($borderArray);
                    $no++;
                    $numRow++;
            }

            foreach ($stokVendor as $key => $stok) {
                [$idsup, $matjenis] = explode('-', $key);
                $rowIndex = 6;
                foreach ($dataLaporan->getResultArray() as $row) {
                    if ($row['idsup'] == $idsup && $row['matjenis'] == $matjenis) {
                        $sheet->setCellValue('G' . $rowIndex, $stok >= 0 ? $stok : abs($stok));
                        if ($stok < 0) {
                            $sheet->getStyle('G' . $rowIndex)->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
                        }
                    }
                    $rowIndex++;
                }
            }

            $tanggalRow = $numRow + 1;
            $tanggal = "Cikarang, " . date('d F Y');
            $sheet->setCellValue('G' . $tanggalRow, $tanggal);
            $sheet->mergeCells("G{$tanggalRow}:G{$tanggalRow}");

            $keteranganRow = $tanggalRow + 1;
            $sheet->setCellValue('G' . $keteranganRow, 'Dilaporkan oleh,');
            $sheet->mergeCells("G{$keteranganRow}:G{$keteranganRow}");

            $userNama =  $keteranganRow + 5;
            $sheet->insertNewRowBefore($userNama, 4);
            $userCell = 'G' . $userNama;
            $sheet->setCellValue($userCell, session()->namauser);
            $sheet->mergeCells("G{$userNama}:G{$userNama}");

            $sheet->getDefaultRowDimension()->setRowHeight(-1);
            $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
            $sheet->setTitle("Laporan Raw Produk");

            header('Content-Type : application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename = "RawProduk.xlsx"');
            header('Cache-Control:max-age=0');

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }
    }
}
