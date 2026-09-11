<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class FilterAdmin implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (session()->idlevel == '') {
            if ($request->isAJAX()) {
                return service('response')->setStatusCode(401)->setJSON(['error' => 'Sesi login sudah habis. Silakan login ulang.']);
            }
            return redirect()->to('/login/index');
        }

        if (!FilterUrlHelper::masihAktif()) {
            $userid = session()->get('userid');
            if ($userid) {
                db_connect()->table('users')->where('userid', $userid)->update(['is_logged_in' => 0]);
            }
            session()->destroy();
            if ($request->isAJAX()) {
                return service('response')->setStatusCode(401)->setJSON(['error' => 'Akun ini sudah dinonaktifkan.']);
            }
            return redirect()->to('/login/index')->with('errIdUser', 'Akun ini sudah dinonaktifkan.');
        }

        if (session()->idlevel == 1 && !FilterUrlHelper::urlDiizinkan($request, 'filterAdmin')) {
            if ($request->isAJAX()) {
                return service('response')->setStatusCode(403)->setJSON(['error' => 'Anda tidak punya akses ke fitur ini.']);
            }
            session()->setFlashdata('access_denied', 'Anda tidak punya akses ke fitur ini.');
            return redirect()->to('/main/index');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        if ($request->isAJAX()) {
            return;
        }

        if (session()->idlevel == 1) {
            return redirect()->to('/main/index');
        }
    }
}
