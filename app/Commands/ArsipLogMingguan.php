<?php

namespace App\Commands;

use App\Libraries\ActivityLog;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Arsipkan semua baris `activity_log` yang ada sekarang jadi 1 file Excel,
 * lalu kirim ke email penerima yang udah diatur di menu Log Aktivitas.
 * Dijalankan mingguan lewat Cron Job cPanel (bukan lagi otomatis per-100-
 * baris kayak sebelumnya).
 *
 * Kalau server cPanel-nya nggak ada akses Terminal buat jalanin PHP CLI,
 * pakai endpoint HTTP-nya aja (Cron::arsipLogMingguan(), lihat
 * app/Controllers/Cron.php) -- logikanya sama persis, cuma dipicu lewat
 * URL (wget/curl) bukan command line.
 */
class ArsipLogMingguan extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'log:arsip-mingguan';
    protected $description = 'Arsipkan activity_log ke Excel, lalu kirim ke email penerima yang sudah diatur.';

    public function run(array $params)
    {
        $hasil = ActivityLog::arsipkanDanKirimEmail();

        $warna = match ($hasil['status']) {
            'terkirim' => 'green',
            'kosong', 'arsip_saja', 'belum_waktunya' => 'yellow',
            default => 'red',
        };

        CLI::write($hasil['pesan'], $warna);
    }
}
