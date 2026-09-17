<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;

class AccessControl
{
    private static ?bool $enabled = null;
    private static array $permissionKeysByUser = [];

    public static function isDynamicEnabled(): bool
    {
        if (self::$enabled !== null) {
            return self::$enabled;
        }

        try {
            $db = db_connect();
            self::$enabled = $db->tableExists('access_permissions') && $db->tableExists('user_access_permissions');
        } catch (\Throwable $e) {
            self::$enabled = false;
        }

        return self::$enabled;
    }

    public static function sections(): array
    {
        return [
            [
                'key' => 'dashboard',
                'label' => 'DASHBOARD',
                'icon' => 'fas fa-home text-primary',
                'features' => [
                    self::feature('dashboard', 'Dashboard', 'dashboard/data', 'fas fa-home text-primary', ['dashboard'], [], false),
                ],
            ],
            [
                'key' => 'transaksi_order',
                'label' => 'TRANSAKSI ORDER',
                'icon' => 'fas fa-file-invoice text-info',
                'features' => [
                    self::feature('order.po_masuk', 'PO Masuk', 'po/data', 'fa fa-arrow-circle-down text-info', ['po']),
                    self::feature('order.po_keluar', 'PO Keluar', 'poKeluar/data', 'fa fa-arrow-circle-up text-danger', ['pokeluar']),
                    self::feature('order.po_habis_pakai', 'PO Barang Habis Pakai', 'poHabisPakai/index', 'fas fa-clipboard-list text-success', ['pohabispakai']),
                    self::feature('order.outstanding', 'Outstanding', 'outstand/data', 'fa fa-ban text-warning', ['outstand']),
                    self::feature('order.invoice_hub', 'Keuangan', 'invoiceHub', 'fa fa-file-invoice-dollar text-info', ['invoicehub']),
                    self::feature('order.invoice_out', 'Invoice Out', 'invoiceOut/data', 'fa fa-file-export text-success', ['invoiceout'], [], false),
                    self::feature('order.invoice_in', 'Invoice In', 'invoiceIn/data', 'fa fa-file-import text-warning', ['invoicein'], [], false),
                ],
            ],
            [
                'key' => 'transaksi_material',
                'label' => 'TRANSAKSI MATERIAL',
                'icon' => 'fas fa-dolly-flatbed text-warning',
                'features' => [
                    self::feature('material.stok', 'Stok Material', 'stokmaterial/index', 'fa fa-box text-primary', ['stokmaterial']),
                    self::feature('material.kebutuhan', 'Kebutuhan Material', 'kebutuhanmaterial/index', 'fas fa-calculator text-info', ['kebutuhanmaterial']),
                    self::feature('material.waste', 'Material Terbuang', 'materialterbuang/index', 'fas fa-trash-alt text-danger', ['materialterbuang']),
                    self::feature('material.masuk', 'Material Masuk', 'materialmasuk/data', 'fa fa-arrow-circle-down text-success', ['materialmasuk']),
                    self::feature('material.keluar', 'Pemakaian Material', 'materialkeluar/data', 'fa fa-arrow-circle-up text-warning', ['materialkeluar']),
                    self::feature('material.produksi', 'Produksi dari Material', 'produksi/data', 'fa fa-industry text-info', ['produksi'], [], false),
                    self::feature('material.raw_produk', 'Data Raw Produk', 'ngdata/data', 'fa fa-truck-loading text-danger', ['ngdata'], [], false),
                ],
            ],
            [
                'key' => 'transaksi_produk',
                'label' => 'TRANSAKSI PRODUK',
                'icon' => 'fas fa-boxes text-success',
                'features' => [
                    self::feature('produk.masuk', 'Produk Masuk', 'barangmasuk/data', 'fa fa-arrow-circle-down text-success', ['barangmasuk']),
                    self::feature('produk.keluar', 'Pengiriman', 'barangkeluar/data', 'fa fa-history text-warning', ['barangkeluar', 'permintaanpengiriman']),
                    self::feature('produk.transfer', 'Antar Gudang', 'permintaanBarangKirim/datakirim', 'fa fa-box text-primary', ['permintaanbarang', 'permintaanbarangkirim']),
                ],
            ],
            [
                'key' => 'master',
                'label' => 'MASTER',
                'icon' => 'fas fa-database text-primary',
                'features' => [
                    self::feature('master.kategori', 'Kategori', 'kategori/index', 'fa fa-tasks text-primary', ['kategori']),
                    self::feature('master.satuan', 'Satuan', 'satuan/index', 'fa fa-box text-warning', ['satuan']),
                    self::feature('master.material', 'Material', 'material/index', 'fa fa-truck-loading text-success', ['material']),
                    self::feature('master.produk', 'Produk', 'barang/index', 'fa fa-truck-loading text-danger', ['barang']),
                    self::feature('master.stok_habis_pakai', 'Stok Barang Habis Pakai', 'baranghabispakai/index', 'fas fa-boxes text-success', ['baranghabispakai']),
                    self::feature('master.harga_produk', 'Harga Produk', 'barang/hargaProduk', 'fa fa-tags text-warning', ['barang/hargaProduk', 'barang/listDataHarga'], [], false),
                    self::feature('master.pelanggan', 'Pelanggan', 'pelanggan/index', 'fa fa-users text-success', ['pelanggan']),
                    self::feature('master.supplier', 'Supplier', 'supplier/index', 'fa fa-users text-success', ['supplier']),
                    self::feature('master.jasa', 'Jasa', 'jasa/modalData', 'fa fa-handshake text-info', ['jasa'], [], false),
                ],
            ],
            [
                'key' => 'packaging',
                'label' => 'PACKAGING',
                'icon' => 'fas fa-box-open text-warning',
                'features' => [
                    self::feature('packaging.data', 'Packaging', 'packaging/index', 'fa fa-box-open text-success', ['packaging']),
                ],
            ],
            [
                'key' => 'utility',
                'label' => 'UTILITY',
                'icon' => 'fas fa-tools text-danger',
                'features' => [
                    self::feature('utility.users', 'Management User', 'users/index', 'fa fa-users text-warning', ['users']),
                    self::feature('utility.access', 'Hak Akses User', 'users/akses', 'fa fa-user-shield text-info', ['users/akses', 'users/simpanAkses'], [], false),
                    self::feature('utility.activity_log', 'Log Aktivitas', 'logaktivitas/index', 'fa fa-history text-secondary', ['logaktivitas']),
                    self::feature('utility.panduan', 'Manual Book', 'panduan/index', 'fa fa-book-open text-info', ['panduan'], [], false),
                    self::feature('utility.laporan', 'Laporan', 'laporan/index', 'fa fa-file text-muted', ['laporan']),
                    self::feature('utility.backup', 'Backup DB', 'utility/index', 'fa fa-database text-primary', ['utility/index'], [], false),
                    self::feature('utility.password', 'Ganti Password', 'utility/gantipassword', 'fa fa-lock text-white', ['utility/gantipassword', 'utility/updatepassword']),
                    self::feature('utility.logout', 'Logout', 'login/keluar', 'fa fa-sign-out-alt text-success', ['login/keluar'], [], false),
                    // Tool maintenance sekali-pakai buat reset PHP opcache server
                    // (dipakai pas kode yang baru di-deploy belum kepakai karena
                    // masih ke-cache). Didaftarin jadi fitur resmi biar lewat
                    // jalur permission yang sudah terbukti jalan, bukan andalin
                    // fallback except-list untuk route yang belum terdaftar.
                    self::feature('utility.reset_opcache', 'Reset Opcache (Maintenance)', 'main/resetcache', 'fa fa-sync text-danger', ['main/resetcache', 'main/pingtool', 'main/databuild'], [], false),
                    // Toggle "sedang dalam perbaikan" per fitur -- dipakai pas
                    // ada perbaikan live di production biar user lain lihat
                    // halaman maintenance yang jelas, bukan fitur error/setengah
                    // jadi. Ini beda dari izin akses biasa: yang diblokir bukan
                    // "siapa boleh buka", tapi "fitur ini lagi dimatiin sementara
                    // buat semua orang" (kecuali superadmin yang lagi benerin).
                    self::feature('utility.maintenance_mode', 'Mode Maintenance', 'maintenance/index', 'fa fa-tools text-danger', ['maintenance'], [], false),
                ],
            ],
        ];
    }

    public static function logoutFeature(): array
    {
        return self::feature('utility.logout', 'Logout', 'login/keluar', 'fa fa-sign-out-alt text-success', ['login/keluar']);
    }

    public static function permissions(): array
    {
        $permissions = [];
        $sort = 10;

        foreach (self::sections() as $section) {
            $permissions[] = [
                'key' => 'section.' . $section['key'],
                'section_key' => $section['key'],
                'section_label' => $section['label'],
                'feature_key' => '',
                'feature_label' => '',
                'action_key' => 'section',
                'action_label' => 'Buka Section',
                'patterns' => [],
                'sort_order' => $sort++,
                'type' => 'section',
            ];

            foreach ($section['features'] as $feature) {
                foreach ($feature['actions'] as $action) {
                    $permissions[] = [
                        'key' => $action['key'],
                        'section_key' => $section['key'],
                        'section_label' => $section['label'],
                        'feature_key' => $feature['key'],
                        'feature_label' => $feature['label'],
                        'action_key' => $action['action'],
                        'action_label' => $action['label'],
                        'patterns' => $action['patterns'],
                        'legacy_keys' => $action['legacy_keys'] ?? [],
                        'sort_order' => $sort++,
                        'type' => 'action',
                    ];
                }
            }
        }

        return $permissions;
    }

    public static function syncPermissionCatalog(?BaseConnection $db = null): void
    {
        $db ??= db_connect();

        if (!$db->tableExists('access_permissions') || !$db->tableExists('user_access_permissions')) {
            return;
        }

        $existing = [];
        foreach ($db->table('access_permissions')->select('permission_key')->get()->getResultArray() as $row) {
            $existing[$row['permission_key']] = true;
        }

        foreach (self::permissions() as $permission) {
            $data = [
                'permission_key' => $permission['key'],
                'section_key' => $permission['section_key'],
                'section_label' => $permission['section_label'],
                'feature_key' => $permission['feature_key'],
                'feature_label' => $permission['feature_label'],
                'action_key' => $permission['action_key'],
                'action_label' => $permission['action_label'],
                'route_patterns' => json_encode($permission['patterns']),
                'sort_order' => $permission['sort_order'],
                'is_active' => 1,
            ];

            if (isset($existing[$permission['key']])) {
                $db->table('access_permissions')->where('permission_key', $permission['key'])->update($data);
            } else {
                $db->table('access_permissions')->insert($data);
            }
        }
    }

    public static function can(string $permissionKey, ?string $userid = null): bool
    {
        $userid ??= (string) session()->get('userid');

        if (in_array($permissionKey, ['produk.masuk.print', 'material.masuk.return_ng'], true) && in_array((int) session()->get('idlevel'), [1, 4, 5], true)) {
            return true;
        }

        if ((int) session()->get('idlevel') === 5) {
            return true;
        }

        if (!self::isDynamicEnabled()) {
            return true;
        }

        $keys = self::keysForUser($userid);
        if (in_array($permissionKey, $keys, true)) {
            return true;
        }

        foreach (self::legacyKeysForPermission($permissionKey) as $legacyKey) {
            if (in_array($legacyKey, $keys, true)) {
                return true;
            }
        }

        return false;
    }

    public static function routeAllowed(string $path, ?string $userid = null): ?bool
    {
        $uri = strtolower(trim($path, '/ '));

        if ($uri === '' || preg_match('#^(login|login/.*|main|main/.*)$#', $uri)) {
            return true;
        }

        $userid ??= (string) session()->get('userid');

        if (preg_match('#^panduan(/.*)?$#', $uri) && $userid !== '') {
            return true;
        }
        if ((int) session()->get('idlevel') === 5) {
            return true;
        }

        if (!self::isDynamicEnabled()) {
            return null;
        }

        $matched = [];
        $knownFeatureMatch = false;
        foreach (self::sections() as $section) {
            foreach ($section['features'] as $feature) {
                foreach ($feature['base_patterns'] as $basePattern) {
                    if (self::matchPath($uri, $basePattern . '/*') || self::matchPath($uri, $basePattern)) {
                        $knownFeatureMatch = true;
                    }
                }

                foreach ($feature['actions'] as $action) {
                    foreach ($action['patterns'] as $pattern) {
                        if (self::matchPath($uri, $pattern)) {
                            $matched[] = $action['key'];
                        }
                    }
                }
            }
        }

        if (!empty($matched)) {
            foreach ($matched as $permissionKey) {
                if (self::can($permissionKey, $userid)) {
                    return true;
                }
            }

            return false;
        }

        return $knownFeatureMatch ? false : null;
    }

    /**
     * Cari label fitur/aksi yang cocok buat sebuah URL, dipakai buat Log
     * Aktivitas biar nampilin nama yang gampang dibaca ("Hapus Invoice In")
     * tanpa perlu daftar terpisah dari daftar permission yang sudah ada.
     * Return null kalau URL-nya ngga cocok sama fitur/aksi manapun.
     */
    public static function describeRoute(string $uri): ?array
    {
        $uri = strtolower(trim($uri, '/ '));

        foreach (self::sections() as $section) {
            foreach ($section['features'] as $feature) {
                foreach ($feature['actions'] as $action) {
                    foreach ($action['patterns'] as $pattern) {
                        if (self::matchPath($uri, $pattern)) {
                            return [
                                'section_label' => $section['label'],
                                'feature_label' => $feature['label'],
                                'action_key' => $action['action'],
                                'action_label' => $action['label'],
                            ];
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * Cari fitur (key + label) yang base_pattern-nya cocok sama sebuah URL,
     * dipakai filter Mode Maintenance buat nentuin fitur mana yang lagi
     * diblokir. Beda dari describeRoute(): ini cocokin di level FITUR
     * (semua aksinya), bukan butuh match persis ke satu aksi tertentu --
     * jadi kalau fitur "Produk Masuk" dimatiin, semua rute di bawahnya
     * (lihat, tambah, edit, hapus) ikut keblokir, bukan cuma salah satu.
     */
    public static function featureKeyForRoute(string $uri): ?array
    {
        $uri = strtolower(trim($uri, '/ '));

        foreach (self::sections() as $section) {
            foreach ($section['features'] as $feature) {
                foreach ($feature['base_patterns'] as $basePattern) {
                    if (self::matchPath($uri, $basePattern) || self::matchPath($uri, $basePattern . '/*')) {
                        return ['key' => $feature['key'], 'label' => $feature['label']];
                    }
                }
            }
        }

        return null;
    }

    public static function menuSections(?string $userid = null): array
    {
        $userid ??= (string) session()->get('userid');
        $menus = [];

        foreach (self::sections() as $section) {
            $items = [];

            foreach ($section['features'] as $feature) {
                if (!$feature['show_menu']) {
                    continue;
                }

                if (!self::can($feature['view_key'], $userid)) {
                    continue;
                }

                $items[] = $feature;
            }

            if (!empty($items)) {
                $section['features'] = $items;
                $menus[] = $section;
            }
        }

        return $menus;
    }

    public static function activeMenuClass(array $feature): string
    {
        $seg1 = strtolower((string) current_url(true)->getSegment(1));
        $seg2 = strtolower((string) current_url(true)->getSegment(2));
        $current = trim($seg1 . '/' . $seg2, '/');

        foreach ($feature['active_patterns'] as $pattern) {
            if (self::matchPath($current, $pattern) || self::matchPath($seg1, $pattern)) {
                return 'active';
            }
        }

        return '';
    }

    // Fitur lama yang route/controller-nya masih ada tapi sudah tidak dipakai lewat
    // menu/tab manapun di UI saat ini -- disembunyikan dari halaman Hak Akses User
    // supaya tidak membingungkan (tidak ada menu yang bisa dikaitkan ke permission ini).
    // material.produksi TIDAK ikut disembunyikan lagi -- sekarang beneran dipakai
    // buat nge-gate tab "Dari Produksi" di halaman Data Produk Masuk.
    // utility.panduan (Manual Book) & utility.logout juga disembunyikan --
    // akses keduanya sudah dibuka buat semua user yang login lewat
    // routeAllowed() (bukan dicek lewat izin/permission), jadi checkbox-nya
    // di Hak Akses cuma dekorasi & bisa bikin admin ngira bisa nge-block
    // orang logout kalau di-uncheck, padahal nggak ngefek sama sekali.
    private const HIDDEN_FROM_PERMISSION_MATRIX = [
        'material.raw_produk',
        'utility.panduan',
        'utility.logout',
    ];

    public static function permissionsBySection(): array
    {
        $hiddenFeatureKeys = array_fill_keys(self::HIDDEN_FROM_PERMISSION_MATRIX, true);

        $grouped = [];
        foreach (self::permissions() as $permission) {
            if (isset($hiddenFeatureKeys[$permission['feature_key']])) {
                continue;
            }

            $sectionKey = $permission['section_key'];
            $featureKey = $permission['feature_key'] ?: '_section';

            $grouped[$sectionKey]['label'] = $permission['section_label'];
            $grouped[$sectionKey]['features'][$featureKey]['label'] = $permission['feature_label'] ?: $permission['section_label'];
            $grouped[$sectionKey]['features'][$featureKey]['permissions'][] = $permission;
        }

        return $grouped;
    }

    public static function selectedPermissions(string $userid, int $userLevelId = 0): array
    {
        if ($userLevelId === 5) {
            return array_column(self::permissions(), 'key');
        }

        $selected = self::keysForUser($userid);
        $selectedMap = array_fill_keys($selected, true);

        foreach (self::permissions() as $permission) {
            foreach ($permission['legacy_keys'] ?? [] as $legacyKey) {
                if (isset($selectedMap[$legacyKey])) {
                    $selectedMap[$permission['key']] = true;
                    break;
                }
            }
        }

        return array_keys($selectedMap);
    }

    private static function feature(string $key, string $label, string $url, string $icon, array $basePatterns, array $extraViewPatterns = [], bool $showMenu = true): array
    {
        $basePatterns = array_map([self::class, 'normalizePattern'], $basePatterns);
        $viewPatterns = array_merge(
            [$url],
            self::viewPatterns($basePatterns),
            $extraViewPatterns
        );

        $viewKey = $key . '.view';
        $actions = self::featureActions($key, $label, $basePatterns, $viewPatterns);

        return [
            'key' => $key,
            'label' => $label,
            'url' => $url,
            'icon' => $icon,
            'show_menu' => $showMenu,
            'view_key' => $viewKey,
            'base_patterns' => $basePatterns,
            'active_patterns' => array_merge($basePatterns, [$url]),
            'actions' => $actions,
        ];
    }

    private static function featureActions(string $key, string $label, array $basePatterns, array $viewPatterns): array
    {
        $default = [
            self::permissionAction($key, 'view', 'Lihat ' . $label, $viewPatterns),
            self::permissionAction($key, 'create', 'Tambah / Input ' . $label, self::actionPatterns($basePatterns, ['input', 'tambah', 'formtambah', 'simpan', 'simpandata', 'simpanItem', 'simpanImport', 'simpan-import', 'import', 'import-preview', 'import-simpan', 'previewImport', 'langsung'])),
            self::permissionAction($key, 'edit', 'Edit / Update ' . $label, self::actionPatterns($basePatterns, ['edit', 'formedit', 'update', 'updatedata', 'editItem', 'simpanItemDetail', 'update*'])),
            self::permissionAction($key, 'delete', 'Hapus / Batal ' . $label, self::actionPatterns($basePatterns, ['hapus', 'hapus*', 'deletedata', 'batal'])),
        ];

        switch ($key) {
            case 'dashboard':
                return [
                    self::permissionAction($key, 'view', 'Lihat Dashboard', self::patterns(['dashboard', 'dashboard/data']), 'view'),
                ];

            case 'master.material':
                return [
                    self::permissionAction($key, 'view', 'Lihat Material', array_merge($viewPatterns, self::patterns(['material/labelsupplier']))),
                    $default[1],
                    self::permissionAction($key, 'edit', 'Edit / Update ' . $label, array_merge(self::actionPatterns($basePatterns, ['edit', 'formedit', 'update', 'updatedata', 'editItem', 'simpanItemDetail', 'update*']), self::patterns(['material/simpanlabelsupplier']))),
                    $default[3],
                ];

            case 'master.produk':
                return [
                    self::permissionAction($key, 'view', 'Lihat Produk', array_merge($viewPatterns, self::patterns(['barang/hargamaterialterakhir']))),
                    self::permissionAction($key, 'create', 'Tambah Produk', self::patterns(['barang/tambah', 'barang/simpandata']), 'create'),
                    self::permissionAction($key, 'edit', 'Edit Produk', self::patterns(['barang/edit', 'barang/edit/*', 'barang/updatedata']), 'edit'),
                    self::permissionAction($key, 'history', 'Lihat Riwayat Perubahan Produk', self::patterns(['barang/riwayat', 'barang/riwayat/*']), 'view'),
                    self::permissionAction($key, 'usage_check', 'Cek Pemakaian Produk', self::patterns(['barang/pemakaian']), 'view'),
                    self::permissionAction($key, 'delete', 'Hapus Produk', self::patterns(['barang/hapus']), 'delete'),
                ];

            case 'master.harga_produk':
                return [
                    self::permissionAction($key, 'view', 'Lihat Harga Produk', self::patterns(['barang/hargaproduk', 'barang/listdataharga']), 'view'),
                ];

            case 'master.jasa':
                return [
                    self::permissionAction($key, 'view', 'Lihat Jasa', self::patterns(['jasa/modaldata', 'jasa/listdata']), 'view'),
                    self::permissionAction($key, 'create', 'Tambah Jasa', self::patterns(['jasa/formtambah', 'jasa/simpan']), 'create'),
                    self::permissionAction($key, 'edit', 'Edit Jasa', self::patterns(['jasa/update']), 'edit'),
                    self::permissionAction($key, 'usage_check', 'Cek Pemakaian Jasa', self::patterns(['jasa/pemakaian']), 'view'),
                    self::permissionAction($key, 'delete', 'Hapus Jasa', self::patterns(['jasa/hapus']), 'delete'),
                ];

            case 'produk.masuk':
                return [
                    self::permissionAction($key, 'view', 'Lihat Produk Masuk', $viewPatterns),
                    self::permissionAction($key, 'input', 'Input Produk Masuk', self::patterns(['barangmasuk/input', 'barangmasuk/simpanItem', 'barangmasuk/tampilDataTemp']), 'create'),
                    self::permissionAction($key, 'po_keluar', 'Pilih PO Keluar Produk', self::patterns(['barangmasuk/pilihanPoKeluarAktif', 'barangmasuk/itemPoKeluar', 'barangmasuk/listPoKeluar', 'barangmasuk/barangPoKeluar']), 'create'),
                    self::permissionAction($key, 'stock_lookup', 'Cari Produk & Stok', self::patterns(['barangmasuk/modalCariBarang', 'barangmasuk/modalCariGudang', 'barangmasuk/listDataBarang', 'barangmasuk/ambilDataBarang', 'barangmasuk/ambilStok', 'produksi/ambilDataBarangProduksi', 'produksi/materialProduk']), 'view'),
                    self::permissionAction($key, 'save_receipt', 'Simpan Transaksi Produk Masuk', self::patterns(['barangmasuk/selesaiTransaksi']), 'create'),
                    self::permissionAction($key, 'edit_detail', 'Edit Detail Produk Masuk', self::patterns(['barangmasuk/edit', 'barangmasuk/edit/*', 'barangmasuk/ambilTotalBerat', 'barangmasuk/tampilDataDetail', 'barangmasuk/editItem', 'barangmasuk/simpanItemDetail']), 'edit'),
                    self::permissionAction($key, 'print', 'Cetak Laporan Stok Produk Masuk', self::patterns(['stok/cetakLaporan']), 'print'),
                    self::permissionAction($key, 'delete', 'Hapus Produk Masuk', self::actionPatterns($basePatterns, ['hapus', 'hapusTransaksi', 'hapusItem', 'hapusItemDetail']), 'delete'),
                ];

            case 'produk.keluar':
                return [
                    self::permissionAction($key, 'view', 'Lihat Pengiriman', array_merge($viewPatterns, self::patterns(['barangkeluar/listDataPengiriman']))),
                    self::permissionAction($key, 'input_direct', 'Input Pengiriman Langsung', self::patterns(['permintaanPengiriman/langsung', 'permintaanPengiriman/langsung/*']), 'create'),
                    self::permissionAction($key, 'input_request', 'Input Permintaan Pengiriman', self::patterns(['permintaanPengiriman/input', 'permintaanPengiriman/simpanItem', 'permintaanPengiriman/tampilTemp', 'permintaanPengiriman/selesai']), 'create'),
                    self::permissionAction($key, 'input_manual', 'Input Pengiriman Manual', self::patterns(['barangkeluar/input', 'barangkeluar/simpanItem', 'barangkeluar/tampilDataTemp', 'barangkeluar/tampilDataTempKeluar']), 'create'),
                    self::permissionAction($key, 'po_lookup', 'Ambil Data PO & Item', self::patterns(['permintaanPengiriman/itemPo', 'permintaanPengiriman/ambilStok', 'permintaanPengiriman/modalCariBarang', 'permintaanPengiriman/listDataBarang', 'permintaanPengiriman/ambilDataBarang', 'permintaanPengiriman/cekNoDo', 'permintaanPengiriman/poListProduk']), 'create'),
                    self::permissionAction($key, 'manual_lookup', 'Ambil Data Manual Pengiriman', self::patterns(['barangkeluar/listDataPo', 'barangkeluar/ambilDataPo', 'barangkeluar/ambilDataBarang', 'barangkeluar/modalData', 'barangkeluar/modalCariBarang']), 'create'),
                    self::permissionAction($key, 'save_plan', 'Simpan Rencana Pengiriman', self::patterns(['permintaanPengiriman/simpanRencana', 'permintaanPengiriman/tampilRencana']), 'create'),
                    self::permissionAction($key, 'save_manual', 'Simpan Pengiriman Manual', self::patterns(['barangkeluar/selesaiTransaksi']), 'create'),
                    self::permissionAction($key, 'ship_product', 'Kirim Produk / Kurangi Stok', self::patterns(['permintaanPengiriman/kirimProduk']), 'create'),
                    self::permissionAction($key, 'process', 'Proses Pengiriman Existing', self::patterns(['permintaanPengiriman/proses', 'permintaanPengiriman/proses/*', 'barangkeluar/edit', 'barangkeluar/edit/*', 'barangkeluar/ambilTotalBerat', 'barangkeluar/tampilDataDetail', 'barangkeluar/tampilDataTempKeluar', 'barangkeluar/editItem', 'barangkeluar/simpanItemDetail', 'barangkeluar/ubahNoSuratJalan', 'barangkeluar/ubah-no-surat-jalan']), 'edit'),
                    self::permissionAction($key, 'edit_document', 'Edit Dokumen Pengiriman', self::patterns(['permintaanPengiriman/updateRencanaDokumen', 'permintaanPengiriman/updateDetailNoPo', 'permintaanPengiriman/updateDetailTanggalPo', 'permintaanPengiriman/updateDetailPelanggan', 'permintaanPengiriman/updateTanggalPengiriman', 'barangkeluar/dokumenPengiriman', 'barangkeluar/dokumen-pengiriman', 'barangkeluar/simpanDokumenPengiriman', 'barangkeluar/simpan-dokumen-pengiriman', 'barangkeluar/hapusDokumenPengiriman', 'barangkeluar/hapus-dokumen-pengiriman']), 'edit'),
                    self::permissionAction($key, 'split_po', 'Pisahkan PO Terkirim', self::patterns(['permintaanPengiriman/poTujuanSplit', 'permintaanPengiriman/pisahkanPoTerkirim']), 'edit'),
                    self::permissionAction($key, 'print', 'Cetak Surat Jalan', self::patterns(['permintaanPengiriman/pilihCetak', 'permintaanPengiriman/pilihCetak/*', 'permintaanPengiriman/pilih-cetak', 'permintaanPengiriman/pilih-cetak/*', 'permintaanPengiriman/cetak', 'permintaanPengiriman/cetak/*', 'barangkeluar/cetakDo', 'barangkeluar/cetakDo/*', 'barangkeluar/detailDo', 'barangkeluar/detailDo/*', 'barangkeluar/cetak-do', 'barangkeluar/cetak-do/*', 'barangkeluar/detail-do', 'barangkeluar/detail-do/*', 'barangkeluar/fileBtb', 'barangkeluar/fileBtb/*', 'barangkeluar/file-btb', 'barangkeluar/file-btb/*']), 'print'),
                    self::permissionAction($key, 'delete', 'Hapus Pengiriman / Rencana', self::patterns(['permintaanPengiriman/hapus', 'permintaanPengiriman/hapusItem', 'permintaanPengiriman/hapusRencana', 'permintaanPengiriman/hapusRiwayatPengiriman', 'permintaanPengiriman/hapusItemPermintaan', 'barangkeluar/hapus', 'barangkeluar/hapus/*', 'barangkeluar/hapusItem', 'barangkeluar/hapusItemDetail', 'barangkeluar/hapusTransaksi', 'barangkeluar/hapusPengirimanLangsung', 'barangkeluar/hapusSuratJalanLangsung', 'barangkeluar/batal', 'barangkeluar/batal/*']), 'delete'),
                    self::permissionAction($key, 'override_stok', 'Override Stok Manual (Pengiriman)', self::patterns([])),
                ];

            case 'produk.transfer':
                return [
                    self::permissionAction($key, 'view', 'Lihat Permintaan Transfer', $viewPatterns),
                    self::permissionAction($key, 'input_request', 'Input Permintaan Transfer', self::patterns(['permintaanBarang/input', 'permintaanBarang/simpanItem', 'permintaanBarang/selesaiTransaksi', 'permintaanBarang/modalCariBarang', 'permintaanBarang/listDataBarang', 'permintaanBarang/ambilDataBarang', 'permintaanBarang/modalCariMaterial', 'permintaanBarang/listDataMaterial', 'permintaanBarang/ambilDataMaterial']), 'create'),
                    self::permissionAction($key, 'ship_transfer', 'Kelola Pengiriman Transfer (Edit/Tambah Item, Info Pengiriman)', self::patterns(['permintaanBarangKirim/editProses', 'permintaanBarangKirim/editProses/*', 'permintaanBarangKirim/tampilDataTempKeluar', 'permintaanBarangKirim/tampilDataDetailProses', 'permintaanBarangKirim/ambilTotalQtyProses', 'permintaanBarangKirim/editItemProses', 'permintaanBarangKirim/simpanItemDetailProses', 'permintaanBarangKirim/updateHeader', 'permintaanBarang/ambilDataBarang', 'permintaanBarang/ambilDataMaterial']), 'create'),
                    self::permissionAction($key, 'edit', 'Edit Permintaan Transfer', self::patterns(['permintaanBarang/edit', 'permintaanBarang/edit/*', 'permintaanBarang/ambilTotalQty', 'permintaanBarang/updatePermintaan', 'permintaanBarang/tampilDataDetail', 'permintaanBarang/editItem', 'permintaanBarang/simpanItemDetail']), 'edit'),
                    self::permissionAction($key, 'print', 'Cetak Permintaan Transfer', self::actionPatterns($basePatterns, ['cetak', 'cetak*', 'cetakPeriode']), 'print'),
                    self::permissionAction($key, 'delete', 'Hapus / Batal Transfer', self::patterns(['permintaanBarang/hapus', 'permintaanBarang/hapusTransaksi', 'permintaanBarang/hapusItem', 'permintaanBarang/hapusItemDetail', 'permintaanBarang/hapusTransaksiProses', 'permintaanBarangKirim/hapusItemDetailProses']), 'delete'),
                    self::permissionAction($key, 'override_stok', 'Override Stok Manual (Antar Gudang)', self::patterns([])),
                    self::permissionAction($key, 'view_status', 'Lihat Kolom Status (Antar Gudang)', self::patterns([])),
                ];

            case 'order.po_masuk':
                return [
                    self::permissionAction($key, 'view', 'Lihat PO Masuk', $viewPatterns),
                    self::permissionAction($key, 'input_manual', 'Input PO Masuk Manual', self::patterns(['po/input', 'po/simpanItem', 'po/selesaiTransaksi', 'po/tampilDataTemp', 'po/modalCariBarang', 'po/modalPo', 'po/listDataBarang', 'po/ambilDataBarang', 'po/ambilTotalBerat', 'po/ambilTotalHarga']), 'create'),
                    self::permissionAction($key, 'import', 'Import PO Masuk', self::patterns(['po/import', 'po/import-preview', 'po/import-simpan', 'po/previewImport', 'po/simpanImport']), 'create'),
                    self::permissionAction($key, 'edit_detail', 'Edit Detail PO Masuk', self::patterns(['po/edit', 'po/edit/*', 'po/tampilDataDetail', 'po/ambilTotalBerat', 'po/ambilTotalHarga', 'po/editItem', 'po/simpanItemDetail', 'po/hapusItemDetail', 'po/updatePelanggan', 'po/updateTanggal']), 'edit'),
                    self::permissionAction($key, 'change_number', 'Ubah No PO Masuk', self::patterns(['po/updateNopo']), 'edit'),
                    self::permissionAction($key, 'close_item', 'Close Item / PO Masuk', self::patterns(['po/closeItem', 'po/closePo', 'po/reopenCloseLog', 'po/koreksiQtyItem', 'po/daftarPoTujuanClose'])),
                    self::permissionAction($key, 'progress', 'Lihat Progress PO', self::patterns(['po/progress', 'po/progress/*']), 'view'),
                    self::permissionAction($key, 'delete', 'Hapus PO Masuk', self::actionPatterns($basePatterns, ['hapus', 'hapus*', 'hapusTransaksi']), 'delete'),
                    self::permissionAction($key, 'view_price', 'Lihat Kolom Harga PO Masuk', self::patterns([])),
                ];

            case 'order.po_keluar':
                return [
                    self::permissionAction($key, 'view', 'Lihat PO Keluar', $viewPatterns),
                    self::permissionAction($key, 'input', 'Input PO Keluar', self::patterns(['poKeluar/input', 'poKeluar/simpan', 'poKeluar/pilihanAktif', 'poKeluar/pilihanAktif/*']), 'create'),
                    self::permissionAction($key, 'edit_header', 'Edit Header Cetak PO Keluar', self::patterns(['poKeluar/edit', 'poKeluar/edit/*', 'poKeluar/update', 'poKeluar/update/*']), 'edit'),
                    self::permissionAction($key, 'print', 'Cetak PO Keluar', self::patterns(['poKeluar/cetak', 'poKeluar/cetak/*']), 'print'),
                    self::permissionAction($key, 'cancel_delete', 'Batal / Hapus PO Keluar', self::patterns(['poKeluar/batal', 'poKeluar/batal/*', 'poKeluar/hapus', 'poKeluar/hapus/*']), 'delete'),
                ];

            case 'order.invoice_out':
                return [
                    self::permissionAction($key, 'view', 'Lihat Invoice Out', $viewPatterns),
                    self::permissionAction($key, 'generate', 'Generate Invoice Out', self::patterns(['invoiceOut/create', 'invoiceOut/create/*', 'invoiceOut/save']), 'create'),
                    self::permissionAction($key, 'payment', 'Catat Pembayaran Invoice Out', self::patterns(['invoiceOut/pembayaran', 'invoiceOut/simpanPembayaran', 'invoiceOut/tandaiLunas', 'invoiceOut/tandaiLunas/*']), 'edit'),
                    self::permissionAction($key, 'print', 'Cetak Invoice Out', self::patterns(['invoiceOut/cetak', 'invoiceOut/cetak/*', 'invoiceOut/cetakExcel', 'invoiceOut/cetakExcel/*']), 'print'),
                    self::permissionAction($key, 'cancel', 'Batalkan / Hapus Invoice Out', self::patterns(['invoiceOut/cancel', 'invoiceOut/cancel/*', 'invoiceOut/hapus', 'invoiceOut/hapus/*']), 'delete'),
                ];

            case 'order.invoice_in':
                return [
                    self::permissionAction($key, 'view', 'Lihat Invoice In', $viewPatterns),
                    self::permissionAction($key, 'create', 'Catat Invoice In', self::patterns(['invoiceIn/create', 'invoiceIn/save']), 'create'),
                    self::permissionAction($key, 'upload_invoice', 'Upload File Invoice Supplier', self::patterns(['invoiceIn/uploadFileInvoice', 'invoiceIn/uploadFileInvoice/*', 'invoiceIn/simpanFileInvoice', 'invoiceIn/simpanFileInvoice/*']), 'upload'),
                    self::permissionAction($key, 'upload_payment', 'Upload Bukti Transfer', self::patterns(['invoiceIn/uploadBuktiTransfer', 'invoiceIn/uploadBuktiTransfer/*', 'invoiceIn/simpanBuktiTransfer', 'invoiceIn/simpanBuktiTransfer/*', 'invoiceIn/buktiTransfer', 'invoiceIn/buktiTransfer/*']), 'upload'),
                    self::permissionAction($key, 'download_file', 'Download File Invoice', self::patterns(['invoiceIn/file', 'invoiceIn/file/*']), 'view'),
                    self::permissionAction($key, 'cancel', 'Batalkan Invoice In', self::patterns(['invoiceIn/cancel', 'invoiceIn/cancel/*']), 'delete'),
                    self::permissionAction($key, 'delete', 'Hapus Invoice In', self::patterns(['invoiceIn/hapus', 'invoiceIn/hapus/*']), 'delete'),
                ];

            case 'order.invoice_hub':
                return [
                    self::permissionAction($key, 'view', 'Buka Keuangan', self::patterns(['invoiceHub', 'invoiceHub/index']), 'view'),
                    self::permissionAction($key, 'reporting', 'Lihat Reporting Margin', self::patterns([
                        'invoiceHub/reporting',
                        'invoiceHub/reporting/*',
                        'invoiceHub/reportingMargin',
                        'invoiceHub/reportingPiutang',
                        'invoiceHub/reportingHutang',
                        'invoiceHub/reportingCashflow',
                        'invoiceHub/reportingOmzetSuratJalan',
                    ]), 'view'),
                ];

            case 'order.outstanding':
                return [
                    self::permissionAction($key, 'view', 'Lihat Outstanding', array_merge($viewPatterns, self::patterns(['outstand/counttotal'])), 'view'),
                    self::permissionAction($key, 'print', 'Cetak Outstanding', self::patterns(['outstand/cetak']), 'print'),
                    self::permissionAction($key, 'view_price', 'Lihat Kolom Nilai Tagihan Outstanding', self::patterns([])),
                ];

                        case 'material.stok':
                return [
                    self::permissionAction($key, 'view', 'Lihat Stok Material', self::patterns(['stokmaterial', 'stokmaterial/index', 'stokmaterial/data']), 'view'),
                ];
            case 'order.po_habis_pakai':
                return [
                    self::permissionAction($key, 'view', 'Lihat PO Barang Habis Pakai', self::patterns(['poHabisPakai', 'poHabisPakai/index']), 'view'),
                    self::permissionAction($key, 'input', 'Buat PO Barang Habis Pakai', self::patterns(['poHabisPakai/simpan']), 'create'),
                    self::permissionAction($key, 'receive', 'Terima PO Barang Habis Pakai', self::patterns(['poHabisPakai/terima']), 'create'),
                ];
            case 'master.stok_habis_pakai':
                return [
                    self::permissionAction($key, 'view', 'Lihat Stok Barang Habis Pakai', self::patterns(['baranghabispakai', 'baranghabispakai/index']), 'view'),
                    self::permissionAction($key, 'manage_stock', 'Kelola Master Barang Habis Pakai', self::patterns(['baranghabispakai/simpanBarang']), 'create'),
                    self::permissionAction($key, 'receive', 'Terima Barang dari PO Vendor', self::patterns(['baranghabispakai/terimaBarangVendor']), 'create'),
                    self::permissionAction($key, 'request', 'Ajukan Permintaan Produksi', self::patterns(['baranghabispakai/simpanPermintaan']), 'create'),
                    self::permissionAction($key, 'approve', 'Setujui/Tolak Permintaan', self::patterns(['baranghabispakai/setujui/*', 'baranghabispakai/tolak/*']), 'edit'),
                ];
            case 'material.kebutuhan':
                return [
                    self::permissionAction($key, 'view', 'Lihat Kebutuhan Material', self::patterns(['kebutuhanmaterial', 'kebutuhanmaterial/index', 'kebutuhanmaterial/data']), 'view'),
                    self::permissionAction($key, 'print', 'Cetak Kebutuhan Material', self::patterns(['kebutuhanmaterial/cetak']), 'print'),
                ];

            case 'material.masuk':
                return [
                    self::permissionAction($key, 'view', 'Lihat Material Masuk', $viewPatterns),
                    self::permissionAction($key, 'input', 'Input Material Masuk', self::patterns(['materialmasuk/input', 'materialmasuk/simpanItem', 'materialmasuk/tampilDataTemp']), 'create'),
                    self::permissionAction($key, 'po_keluar', 'Pilih PO Keluar Material', self::patterns(['materialmasuk/materialPoKeluar', 'materialmasuk/itemPoKeluar', 'materialmasuk/listPoKeluar']), 'create'),
                    self::permissionAction($key, 'lookup', 'Cari Material & Cek Nomor', self::patterns(['materialmasuk/modalCariMaterial', 'materialmasuk/listDataMaterial', 'materialmasuk/ambilDataMaterial', 'materialmasuk/cekNoDo', 'materialmasuk/cekNoInvoice']), 'view'),
                    self::permissionAction($key, 'save_receipt', 'Simpan Transaksi Material Masuk', self::patterns(['materialmasuk/selesaiTransaksi']), 'create'),
                    self::permissionAction($key, 'payment', 'Catat Pembayaran Material Masuk', self::patterns(['materialmasuk/simpanPembayaran']), 'edit'),
                    self::permissionAction($key, 'edit_detail', 'Edit Detail Material Masuk', self::patterns(['materialmasuk/edit', 'materialmasuk/edit/*', 'materialmasuk/updateInvoice', 'materialmasuk/editItem', 'materialmasuk/simpanItemDetail']), 'edit'),
                    self::permissionAction($key, 'return_ng', 'Retur Material NG ke Supplier', self::patterns(['materialmasuk/retur', 'materialmasuk/retur/*', 'materialretur/simpan']), 'edit'),
                    self::permissionAction($key, 'delete', 'Hapus Material Masuk', self::actionPatterns($basePatterns, ['hapus', 'hapusTransaksi', 'hapusItem', 'hapusItemDetail']), 'delete'),
                ];

            case 'material.keluar':
                return [
                    self::permissionAction($key, 'view', 'Lihat Pemakaian Material', $viewPatterns),
                    self::permissionAction($key, 'input', 'Input Pemakaian Material', self::actionPatterns($basePatterns, ['input', 'cekNoDo', 'simpanItem', 'selesaiTransaksi', 'modalCariMaterial', 'listDataMaterial', 'ambilDataMaterial']), 'create'),
                    self::permissionAction($key, 'edit_detail', 'Edit Pemakaian Material', self::actionPatterns($basePatterns, ['edit', 'editItem', 'simpanItemDetail', 'update*']), 'edit'),
                    self::permissionAction($key, 'delete', 'Hapus Pemakaian Material', self::actionPatterns($basePatterns, ['hapus', 'hapus*', 'hapusTransaksi', 'hapusItemDetail']), 'delete'),
                ];

            case 'material.produksi':
                return [
                    self::permissionAction($key, 'view', 'Lihat Produksi dari Material', $viewPatterns),
                    self::permissionAction($key, 'input', 'Input Produksi', self::actionPatterns($basePatterns, ['input', 'hitungOtomatis', 'simpanOtomatis', 'simpanItem', 'selesaiTransaksi', 'materialProduk', 'ambilDataBarangProduksi', 'modal*', 'listData*', 'ambilData*', 'tampilDataTemp']), 'create'),
                    self::permissionAction($key, 'edit', 'Edit Produksi', self::actionPatterns($basePatterns, ['edit', 'editItem', 'simpanItemDetail', 'materialProduk', 'ambilDataBarangProduksi', 'update*']), 'edit'),
                    self::permissionAction($key, 'delete', 'Hapus Produksi', self::actionPatterns($basePatterns, ['hapus', 'hapus*', 'hapusTransaksi']), 'delete'),
                ];

            case 'material.raw_produk':
                return [
                    self::permissionAction($key, 'view', 'Lihat Data Raw Produk', self::patterns(['ngdata', 'ngdata/data', 'ngdata/listdata']), 'view'),
                    self::permissionAction($key, 'print', 'Cetak Data Raw Produk', self::patterns(['ngdata/cetak_raw_produk', 'ngdata/cetak_raw_produk_periode']), 'print'),
                ];

            case 'utility.users':
                return [
                    self::permissionAction($key, 'view', 'Lihat User', array_merge($viewPatterns, self::patterns(['users/listDataModal'])), 'view'),
                    self::permissionAction($key, 'create', 'Tambah User', self::patterns(['users/formtambah', 'users/simpan']), 'create'),
                    self::permissionAction($key, 'edit_profile', 'Edit User & Status', self::patterns(['users/formedit', 'users/update', 'users/updateStatus']), 'edit'),
                    self::permissionAction($key, 'reset_password', 'Reset Password User', self::patterns(['users/resetPassword']), 'edit'),
                    self::permissionAction($key, 'delete', 'Hapus User', self::patterns(['users/hapus']), 'delete'),
                ];

            case 'utility.access':
                return [
                    self::permissionAction($key, 'view', 'Lihat Hak Akses User', self::patterns(['users/akses']), 'view'),
                    self::permissionAction($key, 'save', 'Simpan Hak Akses User', self::patterns(['users/simpanakses', 'users/simpanAkses']), 'edit'),
                    self::permissionAction($key, 'manage_level', 'Tambah Role User', self::patterns(['users/formtambahlevel', 'users/simpanlevel']), 'create'),
                ];

            case 'utility.activity_log':
                return [
                    self::permissionAction($key, 'view', 'Lihat Log Aktivitas', self::patterns(['logaktivitas', 'logaktivitas/index', 'logaktivitas/listdata', 'logaktivitas/listarchive', 'logaktivitas/downloadarchive', 'logaktivitas/downloadarchive/*', 'logaktivitas/simpanemailpenerima']), 'view'),
                ];

            case 'utility.laporan':
                return [
                    self::permissionAction($key, 'view', 'Buka Dashboard Laporan', self::patterns(['laporan', 'laporan/index', 'laporan/tampilgrafikrawproduk', 'laporan/tampilgrafikbarangmasuk', 'laporan/tampilgrafikbarangkeluar']), 'view'),
                    self::permissionAction($key, 'print_raw_produk', 'Cetak Laporan Raw Produk', self::patterns(['laporan/cetak_raw_produk', 'laporan/cetak_raw_produk_periode', 'laporan/cetak-raw-produk', 'laporan/cetak-raw-produk-periode']), 'print'),
                    self::permissionAction($key, 'print_produk_masuk', 'Cetak Laporan Produk Masuk', self::patterns(['laporan/cetak_barang_masuk', 'laporan/cetak_barang_masuk_periode', 'laporan/cetak-barang-masuk', 'laporan/cetak-barang-masuk-periode']), 'print'),
                    self::permissionAction($key, 'print_produk_keluar', 'Cetak Laporan Produk Keluar', self::patterns(['laporan/cetak_barang_keluar', 'laporan/cetak_barang_keluar_periode', 'laporan/cetak-barang-keluar', 'laporan/cetak-barang-keluar-periode']), 'print'),
                ];

            case 'utility.backup':
                return [
                    self::permissionAction($key, 'view', 'Buka Backup DB', self::patterns(['utility/index']), 'view'),
                    self::permissionAction($key, 'backup', 'Download Backup DB', self::patterns(['utility/dobackup']), 'create'),
                    self::permissionAction($key, 'restore', 'Restore DB', self::patterns(['utility/dorestore']), 'upload'),
                ];

            case 'utility.password':
                return [
                    self::permissionAction($key, 'view', 'Buka Ganti Password', self::patterns(['utility/gantipassword']), 'view'),
                    self::permissionAction($key, 'change', 'Simpan Password Baru', self::patterns(['utility/updatepassword']), 'edit'),
                ];

            case 'utility.logout':
                return [
                    self::permissionAction($key, 'view', 'Logout', self::patterns(['login/keluar']), 'view'),
                ];

            case 'utility.maintenance_mode':
                return [
                    self::permissionAction($key, 'view', 'Buka Mode Maintenance', self::patterns(['maintenance/index']), 'view'),
                    self::permissionAction($key, 'manage', 'Aktif/Nonaktifkan Maintenance', self::patterns(['maintenance/toggle']), 'edit'),
                ];
        }

        return $default;
    }

    private static function permissionAction(string $featureKey, string $action, string $label, array $patterns, ?string $legacyAction = null): array
    {
        $key = $featureKey . '.' . $action;
        $legacyKeys = [];
        if ($legacyAction !== null) {
            $legacyKey = $featureKey . '.' . $legacyAction;
            if ($legacyKey !== $key) {
                $legacyKeys[] = $legacyKey;
            }
        }

        return [
            'key' => $key,
            'action' => $action,
            'label' => $label,
            'patterns' => self::normalizePatterns($patterns),
            'legacy_keys' => $legacyKeys,
        ];
    }

    private static function patterns(array $patterns): array
    {
        return self::normalizePatterns($patterns);
    }

    private static function legacyKeysForPermission(string $permissionKey): array
    {
        foreach (self::permissions() as $permission) {
            if ($permission['key'] === $permissionKey) {
                return $permission['legacy_keys'] ?? [];
            }
        }

        return [];
    }

    private static function actionPatterns(array $basePatterns, array $methods): array
    {
        $patterns = [];
        foreach ($basePatterns as $basePattern) {
            foreach ($methods as $method) {
                $patterns[] = $basePattern . '/' . $method;
                $patterns[] = $basePattern . '/' . $method . '/*';
            }
        }

        return array_values(array_unique(self::normalizePatterns($patterns)));
    }

    private static function viewPatterns(array $basePatterns): array
    {
        $methods = [
            'index',
            'data',
            'datakirim',
            'listdata',
            'listdatakirim',
            'listdatabarang',
            'listdatamaterial',
            'listdataview',
            'cekdata',
            'modaldata',
            'modal*',
            'modalcari*',
            'tampildata*',
            'ambildata*',
            'ambilstok',
            'ambiltotal*',
            'pembayaran',
            'reporting',
            'progress',
            'progress/*',
            'pilihanaktif',
            'pilihanaktif/*',
            'pilihanpokeluaraktif',
            'detail',
            'detail/*',
            'file/*',
            'buktitransfer/*',
            'pemakaian',
        ];
        $patterns = [];

        foreach ($basePatterns as $basePattern) {
            $patterns[] = $basePattern;

            foreach ($methods as $method) {
                $patterns[] = $basePattern . '/' . $method;
                if (strpos($method, '*') === false) {
                    $patterns[] = $basePattern . '/' . $method . '/*';
                }
            }
        }

        return self::normalizePatterns($patterns);
    }

    private static function normalizePatterns(array $patterns): array
    {
        return array_values(array_unique(array_map([self::class, 'normalizePattern'], $patterns)));
    }

    private static function normalizePattern(string $pattern): string
    {
        return strtolower(trim($pattern, '/ '));
    }

    public static function matchesPattern(string $uri, string $pattern): bool
    {
        return self::matchPath($uri, $pattern);
    }

    private static function matchPath(string $uri, string $pattern): bool
    {
        $pattern = preg_quote(self::normalizePattern($pattern), '#');
        $pattern = str_replace('\*', '.*', $pattern);

        return preg_match('#^' . $pattern . '$#', $uri) === 1;
    }

    private static function keysForUser(string $userid): array
    {
        if (isset(self::$permissionKeysByUser[$userid])) {
            return self::$permissionKeysByUser[$userid];
        }

        try {
            $rows = db_connect()
                ->table('user_access_permissions')
                ->select('permission_key')
                ->where('userid', $userid)
                ->get()
                ->getResultArray();
        } catch (\Throwable $e) {
            return [];
        }

        self::$permissionKeysByUser[$userid] = array_column($rows, 'permission_key');

        return self::$permissionKeysByUser[$userid];
    }

    /**
     * Daftar prefix permission-key yang jadi titik-awal (starting point) tiap level,
     * dipakai buat pre-check pilihan hak akses saat bikin user baru, dan sebagai
     * fallback seeding kalau seorang user belum punya baris apapun di
     * user_access_permissions.
     */
    public static function defaultPermissionKeysForLevel(int $levelId): array
    {
        $defaults = [
            1 => ['dashboard.', 'master.', 'produk.', 'order.', 'material.', 'utility.users.', 'utility.access.', 'utility.panduan.', 'utility.password.', 'utility.logout.', 'section.'],
            2 => ['dashboard.', 'order.po_habis_pakai.', 'produk.masuk.', 'produk.keluar.', 'produk.transfer.', 'master.harga_produk.', 'master.supplier.view', 'master.stok_habis_pakai.', 'material.stok.', 'material.kebutuhan.', 'material.masuk.', 'material.keluar.', 'utility.panduan.', 'utility.password.', 'utility.logout.', 'section.transaksi_produk', 'section.transaksi_material', 'section.utility'],
            3 => ['dashboard.', 'produk.keluar.', 'produk.transfer.', 'master.harga_produk.', 'master.pelanggan.view', 'utility.panduan.', 'utility.password.', 'utility.logout.', 'section.transaksi_produk', 'section.utility'],
            4 => ['dashboard.', 'master.', 'produk.', 'order.', 'material.', 'utility.users.', 'utility.access.', 'utility.panduan.', 'utility.password.', 'utility.logout.', 'section.'],
        ];

        if (!isset($defaults[$levelId])) {
            return [];
        }

        $matched = [];
        foreach (array_column(self::permissions(), 'key') as $permissionKey) {
            foreach ($defaults[$levelId] as $prefix) {
                if (self::startsWith($permissionKey, $prefix)) {
                    $matched[] = $permissionKey;
                    break;
                }
            }
        }

        return $matched;
    }

    /**
     * Seed default permissions for one user based on their level, mirroring what
     * that level used to grant everyone. Only runs if the user has zero rows yet,
     * so it's safe to call both for brand-new users and for backfilling old ones.
     */
    public static function grantDefaultPermissionsForUser(string $userid, int $levelId, ?BaseConnection $db = null): void
    {
        $db ??= db_connect();

        $keys = self::defaultPermissionKeysForLevel($levelId);
        if (empty($keys)) {
            return;
        }

        $hasRows = $db->table('user_access_permissions')->where('userid', $userid)->countAllResults() > 0;
        if ($hasRows) {
            return;
        }

        foreach ($keys as $permissionKey) {
            $db->table('user_access_permissions')->insert([
                'userid' => $userid,
                'permission_key' => $permissionKey,
            ]);
        }

        unset(self::$permissionKeysByUser[$userid]);
    }

    /**
     * Simpan pilihan hak akses eksplisit (dari picker saat bikin user baru atau
     * halaman Hak Akses User) untuk satu user, replace-semua seperti simpanakses().
     */
    public static function saveUserPermissions(string $userid, array $selectedKeys, ?BaseConnection $db = null): void
    {
        $db ??= db_connect();

        $allowedKeys = array_column(self::permissions(), 'key');
        $sectionByPermission = [];
        foreach (self::permissions() as $permission) {
            $sectionByPermission[$permission['key']] = 'section.' . $permission['section_key'];
        }

        $selected = array_values(array_unique(array_filter($selectedKeys, static fn ($key) => in_array($key, $allowedKeys, true))));

        foreach ($selected as $permissionKey) {
            if (isset($sectionByPermission[$permissionKey])) {
                $selected[] = $sectionByPermission[$permissionKey];
            }
        }

        $selected = array_values(array_unique($selected));

        $db->table('user_access_permissions')->where('userid', $userid)->delete();

        foreach ($selected as $permissionKey) {
            $db->table('user_access_permissions')->insert([
                'userid' => $userid,
                'permission_key' => $permissionKey,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        unset(self::$permissionKeysByUser[$userid]);
    }

    private static function startsWith(string $value, string $prefix): bool
    {
        return substr($value, 0, strlen($prefix)) === $prefix;
    }
}
