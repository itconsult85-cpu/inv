<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\AccessControl;
use \Hermawan\DataTables\DataTable;

class LogAktivitas extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();

        // Semua user terdaftar (bukan cuma yang udah pernah kecatet di log).
        $users = $db->table('users')
            ->select('userid, usernama')
            ->orderBy('usernama', 'ASC')
            ->get()->getResultArray();

        // Semua lokasi/fitur yang ada di web ini (dari daftar Hak Akses),
        // bukan cuma yang udah pernah kecatet di log aktivitas.
        $lokasiList = ['Login'];
        foreach (AccessControl::sections() as $section) {
            foreach ($section['features'] as $feature) {
                $lokasiList[] = $feature['label'];
            }
        }
        $lokasiList = array_values(array_unique($lokasiList));
        sort($lokasiList);

        $aksiList = $db->tableExists('activity_log')
            ? $db->table('activity_log')
                ->select('aksi', true)
                ->groupBy('aksi')
                ->orderBy('aksi', 'ASC')
                ->get()->getResultArray()
            : [];

        $emailPenerima = '';
        $intervalHari = 7;
        if ($db->tableExists('pengaturan_email_log')) {
            $row = $db->table('pengaturan_email_log')->get(1)->getRowArray();
            $emailPenerima = $row['email_penerima'] ?? '';
            if (!empty($row['interval_hari'])) {
                $intervalHari = (int) $row['interval_hari'];
            }
        }

        return view('logaktivitas/index', [
            'users' => $users,
            'lokasiList' => $lokasiList,
            'aksiList' => $aksiList,
            'emailPenerima' => $emailPenerima,
            'intervalHari' => $intervalHari,
        ]);
    }

    /**
     * Simpan email penerima + interval (tiap berapa hari) arsip Log
     * Aktivitas mingguan (satu baris pengaturan aja, di-update terus).
     */
    public function simpanEmailPenerima()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $email = trim((string) $this->request->getPost('email_penerima'));
        $intervalHari = (int) $this->request->getPost('interval_hari');

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setJSON(['error' => 'Format email tidak valid.']);
        }

        if ($intervalHari < 1) {
            return $this->response->setJSON(['error' => 'Interval minimal 1 hari.']);
        }

        $db = \Config\Database::connect();
        if (!$db->tableExists('pengaturan_email_log')) {
            return $this->response->setJSON(['error' => 'Tabel pengaturan belum tersedia. Hubungi developer.']);
        }

        $data = [
            'email_penerima' => $email !== '' ? $email : null,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($db->fieldExists('interval_hari', 'pengaturan_email_log')) {
            $data['interval_hari'] = $intervalHari;
        }

        $ada = $db->table('pengaturan_email_log')->get(1)->getRowArray();
        if ($ada) {
            $db->table('pengaturan_email_log')->where('id', $ada['id'])->update($data);
        } else {
            $db->table('pengaturan_email_log')->insert($data);
        }

        return $this->response->setJSON(['sukses' => 'Pengaturan berhasil disimpan.']);
    }

    public function listData()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $db = \Config\Database::connect();
        if (!$db->tableExists('activity_log')) {
            return $this->response->setJSON([
                'draw' => (int) $this->request->getGet('draw'),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }

        $kolomDetail = $db->fieldExists('detail_data', 'activity_log') ? ', detail_data' : '';
        $builder = $db->table('activity_log')
            ->select('id, userid, usernama, aksi, modul, keterangan, created_at' . $kolomDetail);

        $userid = trim((string) $this->request->getGet('f_userid'));
        if ($userid !== '') {
            $builder->where('userid', $userid);
        }

        $modul = trim((string) $this->request->getGet('f_modul'));
        if ($modul !== '') {
            $builder->where('modul', $modul);
        }

        $aksi = trim((string) $this->request->getGet('f_aksi'));
        if ($aksi !== '') {
            $builder->where('aksi', $aksi);
        }

        $tglawal = trim((string) $this->request->getGet('f_tglawal'));
        if ($tglawal !== '' && $this->tanggalValid($tglawal)) {
            $builder->where('created_at >=', $tglawal . ' 00:00:00');
        }

        $tglakhir = trim((string) $this->request->getGet('f_tglakhir'));
        if ($tglakhir !== '' && $this->tanggalValid($tglakhir)) {
            $builder->where('created_at <=', $tglakhir . ' 23:59:59');
        }

        $builder->orderBy('created_at', 'DESC');

        return DataTable::of($builder)
            ->addNumbering('nomor')
            ->toJson(true);
    }

    private function tanggalValid(string $tanggal): bool
    {
        $parsed = \DateTime::createFromFormat('Y-m-d', $tanggal);

        return $parsed && $parsed->format('Y-m-d') === $tanggal;
    }

    /**
     * Tab "Memory" -- daftar file Excel arsip yang dibuat tiap minggu lewat
     * spark command `log:arsip-mingguan` (lihat ActivityLog::arsipkanSemua()).
     */
    public function listArchive()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $db = \Config\Database::connect();
        if (!$db->tableExists('activity_log_archive')) {
            return $this->response->setJSON([
                'draw' => (int) $this->request->getGet('draw'),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }

        $builder = $db->table('activity_log_archive')
            ->select('id, original_name, row_count, periode_awal, periode_akhir, created_at')
            ->orderBy('created_at', 'DESC');

        return DataTable::of($builder)
            ->addNumbering('nomor')
            ->add('aksi', function ($row) {
                return "<a class=\"btn btn-sm btn-info\" href=\"" . site_url('logaktivitas/downloadArchive/' . $row->id) . "\"><i class=\"fa fa-download\"></i> Download</a>";
            })
            ->toJson(true);
    }

    public function downloadArchive(int $id)
    {
        $db = \Config\Database::connect();
        $arsip = $db->table('activity_log_archive')->where('id', $id)->get()->getRowArray();

        if (!$arsip) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Arsip log aktivitas tidak ditemukan.');
        }

        $path = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'activity_log_archive' . DIRECTORY_SEPARATOR . $arsip['filename'];
        if (!is_file($path)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('File arsip tidak ditemukan.');
        }

        return $this->response
            ->download($path, null)
            ->setFileName($arsip['original_name'] ?: $arsip['filename']);
    }
}
