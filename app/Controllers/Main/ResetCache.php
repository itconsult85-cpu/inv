<?php

namespace App\Controllers\Main;

use App\Controllers\BaseController;

/**
 * File BARU (belum pernah ke-cache PHP sama sekali) khusus buat maksa
 * opcache di server compile ulang dari nol -- dipakai sekali pas file lain
 * (InvoiceHub.php, Main.php, dst) sudah benar di-upload tapi efeknya belum
 * kelihatan karena PHP masih jalanin bytecode versi lama yang ke-cache.
 * Ditaruh di subfolder Main/ supaya URL-nya (main/resetcache) otomatis
 * lolos dari pengecekan RBAC, sama seperti main/index (Dashboard).
 * Aman dihapus/dibiarkan setelah dipakai.
 */
class ResetCache extends BaseController
{
    public function index()
    {
        if (function_exists('opcache_reset')) {
            $berhasil = opcache_reset();
            echo $berhasil ? 'OK: opcache berhasil di-reset.' : 'GAGAL: opcache_reset() mengembalikan false.';
            return;
        }

        echo 'INFO: fungsi opcache_reset() tidak tersedia di server ini (kemungkinan bukan ini penyebabnya).';
    }
}
