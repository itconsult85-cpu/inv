<?php

namespace App\Filters;

use App\Libraries\AccessControl;
use App\Libraries\ActivityLog;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Nyatetin otomatis ke activity_log tiap ada aksi POST/PUT/DELETE yang
 * cocok sama fitur/aksi yang sudah terdaftar di AccessControl (misal
 * "Hapus Invoice In", "Tambah Produk") -- ngga perlu nambahin kode
 * logging manual di tiap controller satu-satu. Aksi 'view' (buka
 * halaman/lihat data) sengaja tidak dicatat biar log-nya ngga penuh
 * sama lalu-lintas halaman biasa. Login/Logout dicatat manual di
 * Login.php (di luar cakupan filter ini).
 */
class ActivityLogger implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        return;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $method = strtoupper($request->getMethod());
        if (!in_array($method, ['POST', 'PUT', 'DELETE'], true)) {
            return;
        }

        $userid = session()->get('userid');
        if (!$userid) {
            return;
        }

        $path = method_exists($request, 'getPath') ? $request->getPath() : $request->getUri()->getPath();
        $uri = trim((string) $path, '/ ');

        // Login/logout punya pencatatan sendiri di Login.php.
        if (preg_match('#^login(/.*)?$#i', $uri)) {
            return;
        }

        $info = AccessControl::describeRoute($uri);
        if (!$info || $info['action_key'] === 'view') {
            return;
        }

        $statusCode = $response->getStatusCode();
        $gagal = $statusCode >= 400;

        ActivityLog::catat(
            (string) $userid,
            (string) (session()->get('namauser') ?? $userid),
            $info['action_key'],
            $info['feature_label'],
            $info['action_label'] . ($gagal ? ' (gagal)' : ''),
            $uri,
            $method,
            $this->ambilDetailData($request)
        );
    }

    /**
     * Ambil field yang di-input user (POST/PUT/DELETE body) buat disimpen
     * sebagai detail di log -- password & CSRF token sengaja dibuang biar
     * ngga ada data sensitif yang kesimpen ke log.
     */
    private function ambilDetailData(RequestInterface $request): ?array
    {
        $post = method_exists($request, 'getPost') ? $request->getPost() : [];
        if (!is_array($post)) {
            $post = [];
        }

        $csrfField = function_exists('csrf_token') ? csrf_token() : null;

        $detail = [];
        foreach ($post as $key => $value) {
            if ($csrfField !== null && $key === $csrfField) {
                continue;
            }
            if (preg_match('/password|passwd|pwd/i', (string) $key)) {
                continue;
            }
            if ($key === 'permissions') {
                $detail[$key] = $this->perkayaPermissions($value);
                continue;
            }

            $detail[$key] = $this->perkayaKunciArray($key, $this->perkayaNilai($key, $value));
        }

        return $detail;
    }

    /**
     * Khusus field "permissions" dari form Hak Akses User -- isinya key
     * permission mentah (kayak "order.po_masuk.view"), dikelompokkan per
     * section & diterjemahin jadi label yang sama persis kayak yang
     * muncul di halaman Hak Akses ("Lihat PO Masuk"), biar nggak jadi
     * satu baris panjang yang susah dibaca. Bisa berupa list key biasa
     * (simpan 1 user) atau nested per-userid (simpan semua user
     * sekaligus) -- key "section.xxx" sendiri dibuang dari daftar aksi
     * karena udah kewakilin dari nama section-nya.
     */
    private function perkayaPermissions($value)
    {
        if (!is_array($value)) {
            return $value;
        }

        static $katalog = null;
        if ($katalog === null) {
            $katalog = [];
            try {
                foreach (AccessControl::permissions() as $permission) {
                    $katalog[$permission['key']] = $permission;
                }
            } catch (\Throwable $e) {
                $katalog = [];
            }
        }

        $kelompokkan = static function (array $keys) use ($katalog) {
            $grup = [];
            foreach ($keys as $key) {
                if (!is_string($key) || !isset($katalog[$key])) {
                    continue;
                }
                $permission = $katalog[$key];
                if (($permission['type'] ?? '') === 'section') {
                    continue;
                }
                $grup[$permission['section_label']][] = $permission['action_label'];
            }
            foreach ($grup as &$daftarAksi) {
                $daftarAksi = array_values(array_unique($daftarAksi));
            }
            return $grup;
        };

        $nestedPerUser = false;
        foreach ($value as $isi) {
            if (is_array($isi)) {
                $nestedPerUser = true;
                break;
            }
        }

        if ($nestedPerUser) {
            $hasil = [];
            foreach ($value as $uid => $keys) {
                $hasil[$uid] = is_array($keys) ? $kelompokkan($keys) : $keys;
            }
            return $hasil;
        }

        return $kelompokkan($value);
    }

    /**
     * Khusus field yang KEY array-nya adalah ID (misal berat_material[4] =
     * 0.0129, key "4" itu matid), bukan VALUE-nya kayak di perkayaNilai() --
     * key-nya ditukar jadi nama biar "4: 12.9 gram" jadi "Material SGCC
     * 0.25: 12.9 gram".
     */
    private function perkayaKunciArray(string $key, $value)
    {
        static $peta = [
            'berat_material' => ['material', 'matid', 'matnama'],
        ];

        if (!isset($peta[$key]) || !is_array($value) || empty($value)) {
            return $value;
        }

        [$table, $kolomId, $kolomNama] = $peta[$key];

        try {
            $db = \Config\Database::connect();
            if (!$db->tableExists($table)) {
                return $value;
            }

            $ids = array_keys($value);
            $rows = $db->table($table)->select("$kolomId, $kolomNama")->whereIn($kolomId, $ids)->get()->getResultArray();
            $namaPerId = array_column($rows, $kolomNama, $kolomId);

            $hasil = [];
            foreach ($value as $id => $isi) {
                $hasil[$namaPerId[$id] ?? $id] = $isi;
            }
            return $hasil;
        } catch (\Throwable $e) {
            return $value;
        }
    }

    /**
     * Field yang isinya cuma ID (kategori, satuan, pelanggan, gudang,
     * material, supplier) ditambahin nama aslinya biar kelihatan pas
     * dibaca di halaman Log Aktivitas, bukan angka mentah doang. Kalau
     * lookup-nya gagal (master data dihapus, dsb) nilainya dibiarkan apa
     * adanya -- ini murni kosmetik, gak boleh sampai bikin logging gagal.
     */
    private function perkayaNilai(string $key, $value)
    {
        static $peta = [
            'kategori' => ['kategori', 'katid', 'katnama'],
            'idkategori' => ['kategori', 'katid', 'katnama'],
            'satuan' => ['satuan', 'satid', 'satnama'],
            'satuanberat' => ['satuan', 'satid', 'satnama'],
            'idsatuan' => ['satuan', 'satid', 'satnama'],
            'idpel' => ['pelanggan', 'pelid', 'pelnama'],
            'idpelanggan' => ['pelanggan', 'pelid', 'pelnama'],
            'id_pelanggan' => ['pelanggan', 'pelid', 'pelnama'],
            'customer_id' => ['pelanggan', 'pelid', 'pelnama'],
            'gudang' => ['gudang', 'gdgid', 'gdgnama'],
            'idgudang' => ['gudang', 'gdgid', 'gdgnama'],
            'gdgid' => ['gudang', 'gdgid', 'gdgnama'],
            'editGudang' => ['gudang', 'gdgid', 'gdgnama'],
            'material' => ['material', 'matid', 'matnama'],
            'idmaterial' => ['material', 'matid', 'matnama'],
            'idsupplier' => ['supplier', 'supid', 'supnama'],
        ];

        if (!isset($peta[$key]) || $value === null || $value === '') {
            return $value;
        }

        [$table, $kolomId, $kolomNama] = $peta[$key];

        try {
            $db = \Config\Database::connect();
            if (!$db->tableExists($table)) {
                return $value;
            }

            if (is_array($value)) {
                $ids = array_filter($value, static fn($v) => $v !== null && $v !== '');
                if (!$ids) {
                    return $value;
                }
                $rows = $db->table($table)->select("$kolomId, $kolomNama")->whereIn($kolomId, $ids)->get()->getResultArray();
                $namaPerId = array_column($rows, $kolomNama, $kolomId);
                return array_map(static fn($id) => $namaPerId[$id] ?? $id, $value);
            }

            $row = $db->table($table)->select($kolomNama)->where($kolomId, $value)->get()->getRowArray();
            return $row ? $row[$kolomNama] : $value;
        } catch (\Throwable $e) {
            return $value;
        }
    }
}
