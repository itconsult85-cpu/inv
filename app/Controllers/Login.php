<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\ActivityLog;
use App\Models\ModelLogin;

class Login extends BaseController
{
    /**
     * Batas berapa lama (detik) `last_activity` dianggap masih "aktif".
     * Disamain sama session.expiration (Config/Session.php) -- kalau
     * user diem lebih lama dari ini, sesinya sendiri udah expired di
     * level CI4 juga, jadi status "sedang login" boleh dianggap basi
     * dan akun boleh dipakai login lagi dari tempat lain.
     */
    private const BATAS_AKTIF_DETIK = 7200;

    public function __construct()
    {
        $this->ensureSessionTrackingColumns();
    }

    /**
     * Nambahin kolom `is_logged_in` & `last_activity` ke tabel `users`
     * kalau belum ada -- dipakai buat batesin 1 akun cuma bisa login
     * di 1 tempat dalam satu waktu.
     */
    private function ensureSessionTrackingColumns(): void
    {
        $db = db_connect();
        if (!$db->tableExists('users')) {
            return;
        }

        $forge = \Config\Database::forge();

        if (!$db->fieldExists('is_logged_in', 'users')) {
            $forge->addColumn('users', [
                'is_logged_in' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                    'null' => false,
                ],
            ]);
        }

        if (!$db->fieldExists('last_activity', 'users')) {
            $forge->addColumn('users', [
                'last_activity' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'after' => 'is_logged_in',
                ],
            ]);
        }
    }

    public function index()
    {
        // Store the current URL as the last active page before showing the login view
        session()->set('previousPage', previous_url());

        // Check if the user is already logged in
        if (session()->has('userid')) {
            // Arahkan ke halaman sebelumnya atau ke '/main/index' jika tidak diatur
            return redirect()->to($this->halamanTujuanAman());
        }

        return view('login/index');
    }

    /**
     * Ambil `previousPage` dari session, tapi JANGAN pernah kembalikan '/'
     * atau halaman login itu sendiri -- itu bikin infinite redirect loop
     * (ERR_TOO_MANY_REDIRECTS) karena '/' juga diarahkan ke Login::index(),
     * dan Login::index() akan redirect balik ke previousPage lagi kalau
     * user sudah login. Kejadian kalau browser ngga punya referrer yang
     * jelas (mis. domain diketik langsung di address bar).
     */
    private function halamanTujuanAman(): string
    {
        $previousPage = session()->get('previousPage') ?? '/main/index';
        $previousPath = trim((string) parse_url($previousPage, PHP_URL_PATH), '/');

        if ($previousPath === '' || strtolower($previousPath) === 'login' || str_starts_with(strtolower($previousPath), 'login/')) {
            return '/main/index';
        }

        return $previousPage;
    }

    public function cekUser()
    {
        $userid = $this->request->getPost('userid');
        $password = $this->request->getPost('password');
        $modelLogin = new ModelLogin();

        $validation = \Config\Services::validation();
        $valid = $this->validate([
            'userid' => [
                'label' => 'ID User',
                'rules' => 'required',
                'errors' => [
                    'required' => '{field} tidak boleh kosong'
                ]
            ],
            'password' => [
                'label' => 'Password',
                'rules' => 'required',
                'errors' => [
                    'required' => '{field} tidak boleh kosong'
                ]
            ],
        ]);

        if (!$valid) {
            $sessError = [
                'errIdUser' => $validation->getError('userid'),
                'errPassword' => $validation->getError('password'),
            ];
            session()->setFlashdata($sessError);
            return redirect()->to(site_url('login/index'));
        }

        $cekUserLogin = $modelLogin->getUser($userid);

        if ($cekUserLogin === null) {
            ActivityLog::catat((string) $userid, (string) $userid, 'login_gagal', 'Login', 'Login gagal (user tidak terdaftar)');
            $sessError = [
                'errIdUser' => 'Maaf user tidak terdaftar',
            ];
            session()->setFlashdata($sessError);
            return redirect()->to(site_url('login/index'));
        }

        if ($cekUserLogin['useraktif'] != '1') {
            ActivityLog::catat((string) $userid, (string) $cekUserLogin['usernama'], 'login_gagal', 'Login', 'Login gagal (user tidak aktif)');
            $sessError = [
                'errIdUser' => 'Maaf user tidak aktif, silahkan hubungi Administrator anda',
            ];
            session()->setFlashdata($sessError);
            return redirect()->to(site_url('login/index'));
        }

        $passwordUser = $cekUserLogin['userpassword'];

        if (is_string($password) && is_string($passwordUser)) {
            if (password_verify($password, $passwordUser)) {
                // Cek apakah akun ini masih dianggap login aktif di tempat
                // lain (is_logged_in=1 DAN last_activity belum lewat batas
                // BATAS_AKTIF_DETIK). Kalau iya, tolak login baru ini --
                // jangan sampe 1 akun kepake di 2 tempat bersamaan.
                $isLoggedIn = (int) ($cekUserLogin['is_logged_in'] ?? 0) === 1;
                $lastActivity = $cekUserLogin['last_activity'] ?? null;
                $sesiMasihAktif = $isLoggedIn && $lastActivity
                    && (time() - strtotime($lastActivity)) < self::BATAS_AKTIF_DETIK;

                if ($sesiMasihAktif) {
                    ActivityLog::catat((string) $userid, (string) $cekUserLogin['usernama'], 'login_gagal', 'Login', 'Login gagal (akun sedang login di tempat lain)');
                    $sessError = [
                        'errIdUser' => 'Akun ini sedang login di perangkat/browser lain. Silakan logout dari sana dulu, atau coba lagi nanti.',
                    ];
                    session()->setFlashdata($sessError);
                    return redirect()->to(site_url('login/index'));
                }

                $idlevel = $cekUserLogin['userlevelid'];
                $simpan_session = [
                    'userid' => $userid,
                    'namauser' => $cekUserLogin['usernama'],
                    'idlevel' => $idlevel
                ];
                session()->set($simpan_session);

                db_connect()->table('users')->where('userid', $userid)->update([
                    'is_logged_in' => 1,
                    'last_activity' => date('Y-m-d H:i:s'),
                ]);

                ActivityLog::catat((string) $userid, (string) $cekUserLogin['usernama'], 'login', 'Login', 'Login berhasil');

                // Redirect to the last active page or to '/main/index' if not set
                return redirect()->to($this->halamanTujuanAman());
            } else {
                ActivityLog::catat((string) $userid, (string) $cekUserLogin['usernama'], 'login_gagal', 'Login', 'Login gagal (password salah)');
                $sessError = [
                    'errPassword' => 'Password salah',
                ];
                session()->setFlashdata($sessError);
                return redirect()->to(site_url('login/index'));
            }
        } else {
            error_log('Password and/or passwordUser are not strings.');
            $sessError = [
                'errPassword' => 'Terjadi kesalahan sistem',
            ];
            session()->setFlashdata($sessError);
            return redirect()->to(site_url('login/index'));
        }
    }

    public function keluar()
    {
        $userid = session()->get('userid');
        if ($userid) {
            ActivityLog::catat((string) $userid, (string) (session()->get('namauser') ?? $userid), 'logout', 'Login', 'Logout');
            db_connect()->table('users')->where('userid', $userid)->update(['is_logged_in' => 0]);
        }

        session()->destroy();
        return redirect()->to('/login/index');
    }
}
