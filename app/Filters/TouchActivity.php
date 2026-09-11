<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Update `users.last_activity` tiap kali user yang sudah login bikin
 * request -- dipakai bareng `is_logged_in` di Login.php buat batasin
 * satu akun cuma bisa login di satu tempat. Selama user ini masih
 * aktif (last_activity ke-update terus), percobaan login lain pakai
 * akun yang sama akan ditolak.
 */
class TouchActivity implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $userid = session()->get('userid');
        if ($userid) {
            db_connect()->table('users')
                ->where('userid', $userid)
                ->update(['last_activity' => date('Y-m-d H:i:s')]);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
