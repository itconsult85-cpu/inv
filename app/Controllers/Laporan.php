<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ModelBarangKeluar;
use App\Models\Modelbarangmasuk;
use App\Models\Modelmaterial;
use App\Models\Modelng;
use App\Models\ModelSupplier;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Laporan extends BaseController
{
    private function laporanDinonaktifkan()
    {
        if ($this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON([
                'error' => 'Menu laporan sedang dinonaktifkan.',
            ]);
        }

        return redirect()->to('/main/index')->with('error', 'Menu laporan sedang dinonaktifkan.');
    }

    public function index()
    {
        return view('laporan/index');
    }

    //barang masuk
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

            $sheet->setCellValue('A1', "Data Raw Produk");
            $sheet->mergeCells('A1:G1');
            $sheet->getStyle('A1')->getFont()->setBold(true);

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

            $sheet->setCellValue('A3', "No");
            $sheet->setCellValue('B3', "Vendor");
            $sheet->setCellValue('C3', "Tanggal");
            $sheet->setCellValue('D3', "Material");
            $sheet->setCellValue('E3', "Berat Keluar (KG)");
            $sheet->setCellValue('F3', "Berat Masuk (KG)");
            $sheet->setCellValue('G3', "Stok Vendor (KG)");

            $sheet->getStyle('A1')->applyFromArray($styleColumn);
            $sheet->getStyle('A3')->applyFromArray($styleColumn);
            $sheet->getStyle('B3')->applyFromArray($styleColumn);
            $sheet->getStyle('C3')->applyFromArray($styleColumn);
            $sheet->getStyle('D3')->applyFromArray($styleColumn);
            $sheet->getStyle('E3')->applyFromArray($styleColumn);
            $sheet->getStyle('F3')->applyFromArray($styleColumn);
            $sheet->getStyle('G3')->applyFromArray($styleColumn);

            $sheet->getStyle('A3')->applyFromArray($borderArray);
            $sheet->getStyle('B3')->applyFromArray($borderArray);
            $sheet->getStyle('C3')->applyFromArray($borderArray);
            $sheet->getStyle('D3')->applyFromArray($borderArray);
            $sheet->getStyle('E3')->applyFromArray($borderArray);
            $sheet->getStyle('F3')->applyFromArray($borderArray);
            $sheet->getStyle('G3')->applyFromArray($borderArray);

            $no = 1;
            $numRow = 4;
            $stokVendor = [];

            foreach ($dataLaporan->getResultArray() as $row) {
                    $material = $modelMaterial->getMaterialByJenis($row['matjenis']);
                    $matnama = $material['matnama'];
                    $supplier = $modelSupplier->getSupllierById($row['idsup']);
                    $supnama = $supplier['supnama'];

                    $beratMatMasuk = $row['beratmatmasuk'];
                    $beratMatKeluar = $row['beratmatkeluar'];
                    $beratNg = $beratMatMasuk - $beratMatKeluar;

                    $key = $row['idsup'] . '-' . $row['matjenis'];
                    $stokVendor[$key] = isset($stokVendor[$key]) ? $stokVendor[$key] + $beratNg : $beratNg;

                    $sheet->setCellValue('A' . $numRow, $no);
                    $sheet->setCellValue('B' . $numRow, $supnama);
                    $sheet->setCellValue('C' . $numRow, $row['tgl']);
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
                $rowIndex = 4;
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

    function tampilGrafikRawProduk()
    {
        $bulan = $this->request->getPost('bulan');

        $db = \Config\Database::connect();

        $query = $db->query("SELECT tgl AS tgl,beratng FROM ngdata WHERE DATE_FORMAT(tgl, '%Y-%m') = ? ORDER BY tgl ASC", [$bulan])->getResult();

        $data = [
            'grafik' => $query
        ];

        $json = [
            'data' => view('laporan/grafikrawproduk', $data)
        ];

        echo json_encode($json);
    }

    //barang masuk
    public function cetak_barang_masuk()
    {
        return view('laporan/viewbarangmasuk');
    }

    public function cetak_barang_masuk_periode()
    {
        $tombolCetak = $this->request->getPost('btnCetak');
        $tombolExport = $this->request->getPost('btnExport');
        $tglawal = $this->request->getPost('tglawal');
        $tglakhir = $this->request->getPost('tglakhir');

        $modelBarangMasuk = new Modelbarangmasuk();

        $dataLaporan = $modelBarangMasuk->laporanPerPeriode($tglawal, $tglakhir);

        if (isset($tombolCetak)) {
            $data = [
                'datalaporan' => $dataLaporan,
                'tglawal' => $tglawal,
                'tglakhir' => $tglakhir
            ];

            return view('laporan/cetakLaporanBarangMasuk', $data);
        }

        if (isset($tombolExport)) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setCellValue('A1', "Data Produk Masuk");
            $sheet->mergeCells('A1:D1');
            $sheet->getStyle('A1')->getFont()->setBold(true);

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

            $sheet->setCellValue('A3', "No");
            $sheet->setCellValue('B3', "No. Faktur");
            $sheet->setCellValue('C3', "Tanggal");
            $sheet->setCellValue('D3', "Total Berat");

            $sheet->getStyle('A1')->applyFromArray($styleColumn);
            $sheet->getStyle('A3')->applyFromArray($styleColumn);
            $sheet->getStyle('B3')->applyFromArray($styleColumn);
            $sheet->getStyle('C3')->applyFromArray($styleColumn);
            $sheet->getStyle('D3')->applyFromArray($styleColumn);

            $sheet->getStyle('A3')->applyFromArray($borderArray);
            $sheet->getStyle('B3')->applyFromArray($borderArray);
            $sheet->getStyle('C3')->applyFromArray($borderArray);
            $sheet->getStyle('D3')->applyFromArray($borderArray);

            $no = 1;
            $numRow = 4;

            foreach ($dataLaporan->getResultArray() as $row) :
                $sheet->setCellValue('A' . $numRow, $no);
                $sheet->setCellValue('B' . $numRow, $row['faktur']);
                $sheet->setCellValue('C' . $numRow, $row['tglfaktur']);
                $sheet->setCellValue('D' . $numRow, $row['totalberatbarang']);

                $sheet->getStyle('A' . $numRow)->applyFromArray($styleColumn);

                $sheet->getStyle('A' . $numRow)->applyFromArray($borderArray);
                $sheet->getStyle('B' . $numRow)->applyFromArray($borderArray);
                $sheet->getStyle('C' . $numRow)->applyFromArray($borderArray);
                $sheet->getStyle('D' . $numRow)->applyFromArray($borderArray);
                $no++;
                $numRow++;
            endforeach;

            $sheet->getDefaultRowDimension()->setRowHeight(-1);
            $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
            $sheet->setTitle("Laporan Produk Masuk");

            header('Content-Type : application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename = "ProdukMasuk.xlsx"');
            header('Cache-Control:max-age=0');

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }
    }

    function tampilGrafikBarangMasuk()
    {
        $bulan = $this->request->getPost('bulan');

        $db = \Config\Database::connect();

        $query = $db->query("SELECT tglfaktur AS tgl,totalberatbarang FROM barangmasuk WHERE DATE_FORMAT(tglfaktur, '%Y-%m') = ? ORDER BY tglfaktur ASC", [$bulan])->getResult();

        $data = [
            'grafik' => $query
        ];

        $json = [
            'data' => view('laporan/gafikbarangmasuk', $data)
        ];

        echo json_encode($json);
    }


    //barang keluar
    public function cetak_barang_keluar()
    {
        return view('laporan/viewbarangkeluar');
    }

    public function cetak_barang_keluar_periode()
    {
        $tombolCetak = $this->request->getPost('btnCetak');
        $tombolExport = $this->request->getPost('btnExport');
        $tglawal = $this->request->getPost('tglawal');
        $tglakhir = $this->request->getPost('tglakhir');

        $modelBarangKeluar = new ModelBarangKeluar();

        $dataLaporan = $modelBarangKeluar->laporanPerPeriode($tglawal, $tglakhir);

        if (isset($tombolCetak)) {
            $data = [
                'datalaporan' => $dataLaporan,
                'tglawal' => $tglawal,
                'tglakhir' => $tglakhir
            ];

            return view('laporan/cetakLaporanBarangKeluar', $data);
        }

        if (isset($tombolExport)) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setCellValue('A1', "Data Produk Keluar");
            $sheet->mergeCells('A1:D1');
            $sheet->getStyle('A1')->getFont()->setBold(true);

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

            $sheet->setCellValue('A3', "No");
            $sheet->setCellValue('B3', "No. Faktur");
            $sheet->setCellValue('C3', "Tanggal");
            $sheet->setCellValue('D3', "Total Berat");

            $sheet->getStyle('A1')->applyFromArray($styleColumn);
            $sheet->getStyle('A3')->applyFromArray($styleColumn);
            $sheet->getStyle('B3')->applyFromArray($styleColumn);
            $sheet->getStyle('C3')->applyFromArray($styleColumn);
            $sheet->getStyle('D3')->applyFromArray($styleColumn);

            $sheet->getStyle('A3')->applyFromArray($borderArray);
            $sheet->getStyle('B3')->applyFromArray($borderArray);
            $sheet->getStyle('C3')->applyFromArray($borderArray);
            $sheet->getStyle('D3')->applyFromArray($borderArray);

            $no = 1;
            $numRow = 4;

            foreach ($dataLaporan->getResultArray() as $row) :
                $sheet->setCellValue('A' . $numRow, $no);
                $sheet->setCellValue('B' . $numRow, $row['faktur']);
                $sheet->setCellValue('C' . $numRow, $row['tglfaktur']);
                $sheet->setCellValue('D' . $numRow, $row['totalberatbarang']);

                $sheet->getStyle('A' . $numRow)->applyFromArray($styleColumn);

                $sheet->getStyle('A' . $numRow)->applyFromArray($borderArray);
                $sheet->getStyle('B' . $numRow)->applyFromArray($borderArray);
                $sheet->getStyle('C' . $numRow)->applyFromArray($borderArray);
                $sheet->getStyle('D' . $numRow)->applyFromArray($borderArray);
                $no++;
                $numRow++;
            endforeach;

            $sheet->getDefaultRowDimension()->setRowHeight(-1);
            $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
            $sheet->setTitle("Laporan Produk Keluar");

            header('Content-Type : application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename = "ProdukKeluar.xlsx"');
            header('Cache-Control:max-age=0');

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }
    }

    function tampilGrafikBarangKeluar()
    {
        $bulan = $this->request->getPost('bulan');

        $db = \Config\Database::connect();

        $query = $db->query("SELECT tglfaktur AS tgl,totalberatbarang FROM barangkeluar WHERE DATE_FORMAT(tglfaktur, '%Y-%m') = ? ORDER BY tglfaktur ASC", [$bulan])->getResult();

        $data = [
            'grafik' => $query
        ];

        $json = [
            'data' => view('laporan/gafikbarangkeluar', $data)
        ];

        echo json_encode($json);
    }
}
