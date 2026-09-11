<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\ActivityLog;

/**
 * Endpoint HTTP buat trigger tugas terjadwal, dipakai kalau hosting-nya
 * (cPanel) nggak punya akses Terminal buat jalanin `php spark ...`
 * langsung -- cron job-nya tinggal `wget`/`curl` ke URL ini tiap minggu.
 *
 * Route ini SENGAJA dikecualikan dari semua filter login (lihat
 * app/Config/Filters.php) karena yang manggil itu cron job, bukan user
 * yang login -- keamanannya dijaga pakai token rahasia di `.env`
 * (CRON_SECRET_TOKEN), bukan session.
 */
class Cron extends BaseController
{
    public function arsipLogMingguan()
    {
        $tokenDikirim = (string) $this->request->getGet('token');
        $tokenAsli = (string) env('CRON_SECRET_TOKEN', '');

        if ($tokenAsli === '' || !hash_equals($tokenAsli, $tokenDikirim)) {
            return $this->response->setStatusCode(403)->setBody("Token salah atau belum diatur.\n");
        }

        $hasil = ActivityLog::arsipkanDanKirimEmail();

        return $this->response->setContentType('text/plain')->setBody($hasil['status'] . ': ' . $hasil['pesan'] . "\n");
    }
}
