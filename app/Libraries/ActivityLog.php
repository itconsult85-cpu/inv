<?php

namespace App\Libraries;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ActivityLog
{
    public static function catat(string $userid, string $usernama, string $aksi, string $modul, string $keterangan, ?string $url = null, ?string $method = null, ?array $detailData = null): void
    {
        try {
            $db = \Config\Database::connect();
            if (!$db->tableExists('activity_log')) {
                return;
            }

            $data = [
                'userid' => $userid,
                'usernama' => $usernama,
                'aksi' => $aksi,
                'modul' => $modul,
                'keterangan' => $keterangan,
                'url' => $url,
                'method' => $method,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            if ($detailData !== null && $db->fieldExists('detail_data', 'activity_log')) {
                // Disimpan base64 (bukan JSON polos) karena Hermawan\DataTables
                // otomatis nge-esc() (HTML-escape) semua kolom pas dikirim ke
                // browser -- kalau JSON polos, tanda kutipnya keubah jadi
                // &quot; dan bikin JSON.parse() di JS gagal. Base64 kebal dari
                // itu karena isinya cuma huruf/angka.
                $data['detail_data'] = !empty($detailData) ? base64_encode(json_encode($detailData, JSON_UNESCAPED_UNICODE)) : null;
            }

            $db->table('activity_log')->insert($data);
        } catch (\Throwable $e) {
            // Log aktivitas tidak boleh sampai bikin aksi utamanya gagal.
            log_message('error', 'Gagal mencatat activity_log: {message}', ['message' => $e->getMessage()]);
        }
    }

    /**
     * Arsipkan SEMUA baris yang ada sekarang di `activity_log` jadi 1 file
     * Excel (dipanggil dari spark command mingguan, bukan lagi otomatis
     * per-100-baris). Baris yang sudah diarsipkan dihapus dari
     * `activity_log`. Balikin null kalau tidak ada baris sama sekali
     * (tidak perlu bikin arsip kosong).
     *
     * @return array{path:string,row_count:int,periode_awal:string,periode_akhir:string,original_name:string}|null
     */
    public static function arsipkanSemua(): ?array
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('activity_log') || !$db->tableExists('activity_log_archive')) {
            return null;
        }

        $rows = $db->table('activity_log')
            ->orderBy('id', 'ASC')
            ->get()->getResultArray();

        if (empty($rows)) {
            return null;
        }

        $dirUpload = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'activity_log_archive';
        if (!is_dir($dirUpload)) {
            mkdir($dirUpload, 0755, true);
        }

        $periodeAwal = $rows[0]['created_at'];
        $periodeAkhir = $rows[count($rows) - 1]['created_at'];

        $namaFile = 'log-aktivitas_' . date('Ymd-His') . '_' . substr(bin2hex(random_bytes(3)), 0, 6) . '.xlsx';
        $pathFile = $dirUpload . DIRECTORY_SEPARATOR . $namaFile;

        self::tulisExcel($rows, $pathFile, $periodeAwal, $periodeAkhir);

        $namaAsli = 'Log Aktivitas ' . date('d-m-Y', strtotime($periodeAwal)) . ' s.d. ' . date('d-m-Y', strtotime($periodeAkhir)) . '.xlsx';

        $db->table('activity_log_archive')->insert([
            'filename' => $namaFile,
            'original_name' => $namaAsli,
            'row_count' => count($rows),
            'periode_awal' => $periodeAwal,
            'periode_akhir' => $periodeAkhir,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $ids = array_column($rows, 'id');
        $db->table('activity_log')->whereIn('id', $ids)->delete();

        return [
            'path' => $pathFile,
            'row_count' => count($rows),
            'periode_awal' => $periodeAwal,
            'periode_akhir' => $periodeAkhir,
            'original_name' => $namaAsli,
        ];
    }

    /**
     * Arsipkan (arsipkanSemua()) LALU kirim email ke penerima yang udah
     * diatur di menu Log Aktivitas -- dipanggil bareng dari spark command
     * `log:arsip-mingguan` ATAU dari endpoint HTTP (Cron::arsipLogMingguan())
     * buat cron job yang cuma bisa manggil URL (wget/curl), bukan CLI PHP
     * langsung.
     *
     * PENTING: cron/spark-nya sengaja dipanggil SERING (harian) dari luar,
     * tapi baru beneran ngearsip+ngirim kalau udah lewat `interval_hari`
     * sejak arsip TERAKHIR (dicek dari activity_log_archive.created_at
     * paling baru) -- jadi user bisa ganti-ganti frekuensi (7 hari, 14
     * hari, dst) langsung dari tab Memory, TANPA perlu balik ubah jadwal
     * di cPanel lagi.
     *
     * @return array{status:string, pesan:string}
     */
    public static function arsipkanDanKirimEmail(): array
    {
        $db = \Config\Database::connect();

        $intervalHari = 7;
        $emailPenerima = null;
        if ($db->tableExists('pengaturan_email_log')) {
            $row = $db->table('pengaturan_email_log')->get(1)->getRowArray();
            $emailPenerima = $row['email_penerima'] ?? null;
            if (!empty($row['interval_hari'])) {
                $intervalHari = (int) $row['interval_hari'];
            }
        }

        if ($db->tableExists('activity_log_archive')) {
            $terakhir = $db->table('activity_log_archive')->orderBy('created_at', 'DESC')->get(1)->getRowArray();
            if ($terakhir) {
                $selisihHari = (time() - strtotime($terakhir['created_at'])) / 86400;
                if ($selisihHari < $intervalHari) {
                    $sisaHari = ceil($intervalHari - $selisihHari);
                    return ['status' => 'belum_waktunya', 'pesan' => "Belum waktunya arsip (tiap {$intervalHari} hari) -- masih {$sisaHari} hari lagi."];
                }
            }
        }

        $hasil = self::arsipkanSemua();

        if ($hasil === null) {
            return ['status' => 'kosong', 'pesan' => 'Tidak ada baris activity_log untuk diarsipkan.'];
        }

        $ringkasan = "Arsip dibuat: {$hasil['original_name']} ({$hasil['row_count']} baris).";

        if (empty($emailPenerima)) {
            return ['status' => 'arsip_saja', 'pesan' => $ringkasan . ' Email penerima belum diatur, tidak dikirim.'];
        }

        $emailConfig = config('Email');
        if (empty($emailConfig->SMTPHost) && $emailConfig->protocol === 'mail') {
            return ['status' => 'arsip_saja', 'pesan' => $ringkasan . ' Konfigurasi SMTP di .env belum diisi, tidak dikirim.'];
        }

        $email = \Config\Services::email();
        $email->setTo($emailPenerima);
        $email->setSubject('Arsip Log Aktivitas ' . date('d-m-Y', strtotime($hasil['periode_awal'])) . ' s.d. ' . date('d-m-Y', strtotime($hasil['periode_akhir'])));
        $email->setMessage(
            'Terlampir arsip log aktivitas TRE Inventory periode '
            . date('d-m-Y H:i', strtotime($hasil['periode_awal'])) . ' s.d. '
            . date('d-m-Y H:i', strtotime($hasil['periode_akhir']))
            . " ({$hasil['row_count']} baris).\n\nEmail ini dikirim otomatis, tidak perlu dibalas."
        );
        $email->attach($hasil['path']);

        if ($email->send()) {
            self::catat('system', 'System', 'kirim_email', 'Log Aktivitas', "Arsip log aktivitas terkirim ke {$emailPenerima} ({$hasil['row_count']} baris).");
            return ['status' => 'terkirim', 'pesan' => $ringkasan . " Berhasil dikirim ke {$emailPenerima}."];
        }

        $debug = $email->printDebugger(['headers']);
        self::catat('system', 'System', 'kirim_email_gagal', 'Log Aktivitas', 'Gagal kirim arsip log aktivitas ke ' . $emailPenerima);
        return ['status' => 'gagal_kirim', 'pesan' => $ringkasan . ' Gagal kirim email: ' . $debug];
    }

    private static function tulisExcel(array $rows, string $pathFile, string $periodeAwal, string $periodeAkhir): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Log Aktivitas');

        $sheet->setCellValue('A1', 'TRISENTOSA RAYA');
        $sheet->setCellValue('A2', 'Arsip Log Aktivitas');
        $sheet->setCellValue('A3', 'Periode : ' . date('d-m-Y H:i', strtotime($periodeAwal)) . ' s.d. ' . date('d-m-Y H:i', strtotime($periodeAkhir)));
        $sheet->mergeCells('A1:F1');
        $sheet->mergeCells('A2:F2');
        $sheet->mergeCells('A3:F3');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getFont()->setBold(true);
        $sheet->getStyle('A3')->getFont()->setItalic(true);

        $baris = 5;
        $header = ['No', 'Waktu', 'User', 'Aksi', 'Lokasi', 'Keterangan'];
        $kolom = ['A', 'B', 'C', 'D', 'E', 'F'];
        foreach ($header as $i => $judul) {
            $sheet->setCellValue($kolom[$i] . $baris, $judul);
        }

        $rentangHeader = 'A' . $baris . ':F' . $baris;
        $sheet->getStyle($rentangHeader)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle($rentangHeader)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF16869A');
        $sheet->getStyle($rentangHeader)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        $borderTipis = [
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFB0B0B0']],
            ],
        ];

        $no = 1;
        foreach ($rows as $row) {
            $baris++;
            $sheet->setCellValue('A' . $baris, $no++);
            $sheet->setCellValue('B' . $baris, date('d-m-Y H:i:s', strtotime($row['created_at'])));
            $sheet->setCellValue('C' . $baris, $row['usernama']);
            $sheet->setCellValue('D' . $baris, ucfirst(str_replace('_', ' ', $row['aksi'])));
            $sheet->setCellValue('E' . $baris, $row['modul']);
            $sheet->setCellValue('F' . $baris, $row['keterangan']);
            $sheet->getStyle('A' . $baris . ':F' . $baris)->applyFromArray($borderTipis);
        }
        $sheet->getStyle($rentangHeader)->applyFromArray($borderTipis);

        $sheet->getStyle('A6:A' . $baris)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B6:B' . $baris)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $kolomLebar) {
            $sheet->getColumnDimension($kolomLebar)->setAutoSize(true);
        }
        $sheet->freezePane('A6');

        $writer = new Xlsx($spreadsheet);
        $writer->save($pathFile);
    }
}
