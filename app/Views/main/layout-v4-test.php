<?php
$manualPreviewKey = (string) service('request')->getGet('manual_preview');
$manualPreviewMode = ($manualPreviewKey !== '');
$displayName = (string) (session()->namauser ?? '');
$csrfTokenName = csrf_token();
$csrfHash = csrf_hash();
?>
<!doctype html>
<html lang="id" data-bs-theme="light" style="background-color:#f8f9fa;">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token-name" content="<?= esc($csrfTokenName) ?>">
    <meta name="csrf-token-value" content="<?= esc($csrfHash) ?>">
    <meta name="csrf-token" content="<?= esc($csrfHash) ?>">
    <meta name="csrf-header" content="<?= esc(config('Security')->headerName) ?>">
    <title><?= esc($this->renderSection('title') ?: 'Trisentosa Inventory') ?></title>

    <!-- AdminLTE 4 / Bootstrap 5 / Bootstrap Icons -->
    <link rel="stylesheet" href="<?= base_url('vendor/adminlte4/icons/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('vendor/adminlte4/overlayscrollbars/overlayscrollbars.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('vendor/adminlte4/css/adminlte.min.css') ?>">

    <!-- Kompatibilitas sementara untuk DataTables, Select2, dan script lama proyek. -->
    <link rel="stylesheet" href="<?= base_url('plugins/select2/css/select2.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('vendor/adminlte4/css/adminlte-select2.min.css') ?>">
    <script src="<?= base_url('plugins/jquery/jquery.min.js') ?>"></script>
    <script src="<?= base_url('js/tre-datatables.js') ?>"></script>
    <script>
        (function ($) {
            let currentCsrfToken = document.querySelector('meta[name="csrf-token-value"]').content;
            const csrfTokenName = document.querySelector('meta[name="csrf-token-name"]').content;
            const csrfHeader = document.querySelector('meta[name="csrf-header"]').content;
            function syncCsrfToken(xhr) {
                const next = xhr && xhr.getResponseHeader ? xhr.getResponseHeader(csrfHeader) : null;
                if (next) {
                    currentCsrfToken = next;
                    document.querySelector('meta[name="csrf-token-value"]').content = next;
                    document.querySelector('meta[name="csrf-token"]').content = next;
                }
            }
            $.ajaxPrefilter(function (options, originalOptions, jqXHR) {
                const method = (options.type || options.method || 'GET').toUpperCase();
                if (typeof options.success === 'function') {
                    const success = options.success;
                    options.success = function (data, textStatus, xhr) { syncCsrfToken(xhr); return success.apply(this, arguments); };
                } else {
                    jqXHR.done(function (data, textStatus, xhr) { syncCsrfToken(xhr); });
                }
                if (!options.crossDomain && ['POST', 'PUT', 'PATCH', 'DELETE'].includes(method)) {
                    if (options.data instanceof FormData) options.data.set(csrfTokenName, currentCsrfToken);
                    else if (typeof options.data === 'string') { const params = new URLSearchParams(options.data); params.set(csrfTokenName, currentCsrfToken); options.data = params.toString(); }
                    else if (options.data && typeof options.data === 'object') options.data[csrfTokenName] = currentCsrfToken;
                    jqXHR.setRequestHeader(csrfHeader, currentCsrfToken);
                }
            });
            $(document).ajaxComplete(function (event, xhr) { syncCsrfToken(xhr); });
        })(jQuery);
    </script>


    <?= $this->renderSection('head') ?>
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary" style="background-color:#f8f9fa;">
<div class="app-wrapper">
    <nav class="app-header navbar navbar-expand bg-body<?= $manualPreviewMode ? ' d-none' : '' ?>">
        <div class="container-fluid">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="#" data-lte-toggle="sidebar" role="button" aria-label="Buka atau tutup sidebar">
                        <i class="bi bi-list"></i>
                    </a>
                </li>
            </ul>

            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle navbar-user" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle fs-5"></i>
                        <span class="navbar-user-name"><?= esc($displayName ?: 'Pengguna') ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="<?= site_url('utility/gantipassword') ?>">
                                <i class="bi bi-key me-2"></i>Ganti Password
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="<?= site_url('login/keluar') ?>">
                                <i class="bi bi-box-arrow-right me-2"></i>Keluar
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>

    <aside class="app-sidebar bg-body-secondary shadow<?= $manualPreviewMode ? ' d-none' : '' ?>" data-bs-theme="dark">
        <div class="sidebar-brand">
            <a href="<?= site_url('dashboard/data') ?>" class="brand-link">
                <img src="<?= base_url('dist/img/logo-pt-tre.png') ?>" alt="TRE" class="brand-image opacity-75 shadow">
                <span class="brand-text fw-light">Inventory</span>
            </a>
        </div>

        <div class="sidebar-wrapper">
            <?php
            $sidebarPath = trim(service('uri')->getPath(), '/');
            $sidebarIsActive = static function (array|string $paths) use ($sidebarPath): bool {
                foreach ((array) $paths as $path) {
                    $path = trim((string) $path, '/');
                    if ($path !== '' && ($sidebarPath === $path || str_starts_with($sidebarPath, $path . '/'))) {
                        return true;
                    }
                }
                return false;
            };
            $sidebarFeatureIcon = static function (string $icon): string {
                $icon = strtolower($icon);
                $map = [
                    'tasks' => 'bi bi-list-check',
                    'box-open' => 'bi bi-box-seam',
                    'box' => 'bi bi-box',
                    'truck-loading' => 'bi bi-truck',
                    'truck' => 'bi bi-truck',
                    'users' => 'bi bi-people',
                    'user' => 'bi bi-person',
                    'arrow-circle-down' => 'bi bi-arrow-down-circle',
                    'arrow-circle-up' => 'bi bi-arrow-up-circle',
                    'history' => 'bi bi-clock-history',
                    'ban' => 'bi bi-slash-circle',
                    'calculator' => 'bi bi-calculator',
                    'database' => 'bi bi-database',
                    'lock' => 'bi bi-lock',
                    'sign-out-alt' => 'bi bi-box-arrow-right',
                    'book-open' => 'bi bi-book',
                    'file' => 'bi bi-file-earmark-text',
                ];
                foreach ($map as $needle => $replacement) {
                    if (str_contains($icon, $needle)) {
                        return $replacement;
                    }
                }
                return 'bi bi-circle';
            };
            $sidebarFeature = static function (array $feature) use ($sidebarFeatureIcon, $sidebarIsActive): array {
                $patterns = $feature['active_patterns'] ?? [$feature['url'] ?? ''];
                return [
                    'icon' => $sidebarFeatureIcon((string) ($feature['icon'] ?? '')),
                    'active' => $sidebarIsActive($patterns),
                ];
            };
            ?>

            <div class="sidebar-search px-3 py-3">
                <div class="input-group input-group-sm">
                    <input type="search" id="sidebarMenuSearch" class="form-control" placeholder="Cari menu..." autocomplete="off" aria-label="Cari menu">
                    <button type="button" class="btn btn-outline-secondary" id="sidebarMenuSearchClear" title="Hapus pencarian" aria-label="Hapus pencarian">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="small text-white-50 text-center mt-2 d-none" id="sidebarMenuSearchEmpty">Menu tidak ditemukan.</div>
            </div>

            <nav class="mt-1" aria-label="Navigasi utama">
                <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
                    <?php if (\App\Libraries\AccessControl::can('dashboard.view')) : ?>
                        <li class="nav-header">DASHBOARD</li>
                        <li class="nav-item">
                            <a href="<?= site_url('dashboard/data') ?>" class="nav-link<?= $sidebarIsActive('dashboard') ? ' active' : '' ?>">
                                <i class="nav-icon bi bi-house-door text-primary"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (\App\Libraries\AccessControl::isDynamicEnabled()) : ?>
                        <?php foreach (\App\Libraries\AccessControl::menuSections() as $section) : ?>
                            <li class="nav-header"><?= esc($section['label']) ?></li>
                            <?php foreach ($section['features'] as $feature) : ?>
                                <?php $featureState = $sidebarFeature($feature); ?>
                                <li class="nav-item">
                                    <a href="<?= site_url($feature['url']) ?>" class="nav-link<?= $featureState['active'] ? ' active' : '' ?>">
                                        <i class="nav-icon <?= esc($featureState['icon']) ?>"></i>
                                        <p><?= esc($feature['label']) ?></p>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <?php if (in_array((int) session()->idlevel, [1, 4, 5], true)) : ?>
                            <li class="nav-header">MASTER</li>
                            <li class="nav-item"><a href="<?= site_url('kategori/index') ?>" class="nav-link<?= $sidebarIsActive('kategori') ? ' active' : '' ?>"><i class="nav-icon bi bi-list-check text-primary"></i><p>Kategori</p></a></li>
                            <li class="nav-item"><a href="<?= site_url('satuan/index') ?>" class="nav-link<?= $sidebarIsActive('satuan') ? ' active' : '' ?>"><i class="nav-icon bi bi-box text-warning"></i><p>Satuan</p></a></li>
                            <li class="nav-item"><a href="<?= site_url('material/index') ?>" class="nav-link<?= $sidebarIsActive('material') ? ' active' : '' ?>"><i class="nav-icon bi bi-truck text-success"></i><p>Material</p></a></li>
                            <li class="nav-item"><a href="<?= site_url('barang/index') ?>" class="nav-link<?= $sidebarIsActive('barang') ? ' active' : '' ?>"><i class="nav-icon bi bi-truck text-danger"></i><p>Produk</p></a></li>
                            <li class="nav-item"><a href="<?= site_url('pelanggan/index') ?>" class="nav-link<?= $sidebarIsActive('pelanggan') ? ' active' : '' ?>"><i class="nav-icon bi bi-people text-success"></i><p>Pelanggan</p></a></li>
                            <li class="nav-item"><a href="<?= site_url('supplier/index') ?>" class="nav-link<?= $sidebarIsActive('supplier') ? ' active' : '' ?>"><i class="nav-icon bi bi-people text-success"></i><p>Supplier</p></a></li>

                            <li class="nav-header">TRANSAKSI PRODUK</li>
                            <li class="nav-item"><a href="<?= site_url('barangmasuk/data') ?>" class="nav-link<?= $sidebarIsActive('barangmasuk') ? ' active' : '' ?>"><i class="nav-icon bi bi-arrow-down-circle text-success"></i><p>Produk Masuk</p></a></li>
                            <li class="nav-item"><a href="<?= site_url('barangkeluar/data') ?>" class="nav-link<?= $sidebarIsActive('barangkeluar') ? ' active' : '' ?>"><i class="nav-icon bi bi-clock-history text-warning"></i><p>Pengiriman</p></a></li>
                            <li class="nav-item"><a href="<?= site_url('permintaanBarangKirim/datakirim') ?>" class="nav-link<?= $sidebarIsActive(['permintaanBarangKirim', 'permintaanBarang']) ? ' active' : '' ?>"><i class="nav-icon bi bi-box text-primary"></i><p>Antar Gudang</p></a></li>

                            <li class="nav-header">TRANSAKSI ORDER</li>
                            <li class="nav-item"><a href="<?= site_url('po/data') ?>" class="nav-link<?= $sidebarIsActive('po') ? ' active' : '' ?>"><i class="nav-icon bi bi-arrow-down-circle text-info"></i><p>PO Masuk</p></a></li>
                            <li class="nav-item"><a href="<?= site_url('poKeluar/data') ?>" class="nav-link<?= $sidebarIsActive('poKeluar') ? ' active' : '' ?>"><i class="nav-icon bi bi-arrow-up-circle text-danger"></i><p>PO Keluar</p></a></li>
                            <li class="nav-item"><a href="<?= site_url('outstand/data') ?>" class="nav-link<?= $sidebarIsActive('outstand') ? ' active' : '' ?>"><i class="nav-icon bi bi-slash-circle text-warning"></i><p>Outstanding</p></a></li>

                            <li class="nav-header">TRANSAKSI MATERIAL</li>
                            <li class="nav-item"><a href="<?= site_url('stokmaterial/index') ?>" class="nav-link<?= $sidebarIsActive('stokmaterial') ? ' active' : '' ?>"><i class="nav-icon bi bi-box text-primary"></i><p>Stok Material</p></a></li>
                            <li class="nav-item"><a href="<?= site_url('kebutuhanmaterial/index') ?>" class="nav-link<?= $sidebarIsActive('kebutuhanmaterial') ? ' active' : '' ?>"><i class="nav-icon bi bi-calculator text-info"></i><p>Kebutuhan Material</p></a></li>
                            <li class="nav-item"><a href="<?= site_url('materialmasuk/data') ?>" class="nav-link<?= $sidebarIsActive('materialmasuk') ? ' active' : '' ?>"><i class="nav-icon bi bi-arrow-down-circle text-success"></i><p>Material Masuk</p></a></li>
                            <li class="nav-item"><a href="<?= site_url('materialkeluar/data') ?>" class="nav-link<?= $sidebarIsActive('materialkeluar') ? ' active' : '' ?>"><i class="nav-icon bi bi-arrow-up-circle text-warning"></i><p>Pemakaian Material</p></a></li>

                            <?php if ((int) session()->idlevel === 5) : ?>
                                <li class="nav-header">PACKAGING</li>
                                <li class="nav-item"><a href="<?= site_url('packaging/index') ?>" class="nav-link<?= $sidebarIsActive('packaging') ? ' active' : '' ?>"><i class="nav-icon bi bi-box-seam text-success"></i><p>Packaging</p></a></li>

                                <li class="nav-header">UTILITY</li>
                                <li class="nav-item"><a href="<?= site_url('users/index') ?>" class="nav-link<?= $sidebarIsActive('users') ? ' active' : '' ?>"><i class="nav-icon bi bi-person text-warning"></i><p>Management User</p></a></li>
                                <li class="nav-item"><a href="<?= site_url('utility/index') ?>" class="nav-link<?= $sidebarIsActive('utility') ? ' active' : '' ?>"><i class="nav-icon bi bi-database text-primary"></i><p>Backup DB</p></a></li>
                                <li class="nav-item"><a href="<?= site_url('utility/gantipassword') ?>" class="nav-link<?= $sidebarIsActive('utility/gantipassword') ? ' active' : '' ?>"><i class="nav-icon bi bi-lock text-white"></i><p>Ganti Password</p></a></li>
                                <li class="nav-item"><a href="<?= site_url('login/keluar') ?>" class="nav-link"><i class="nav-icon bi bi-box-arrow-right text-success"></i><p>Logout</p></a></li>
                            <?php elseif (in_array((int) session()->idlevel, [1, 4], true)) : ?>
                                <li class="nav-header">UTILITY</li>
                                <li class="nav-item"><a href="<?= site_url('users/index') ?>" class="nav-link<?= $sidebarIsActive('users') ? ' active' : '' ?>"><i class="nav-icon bi bi-people text-warning"></i><p>Management User</p></a></li>
                                <li class="nav-item"><a href="<?= site_url('utility/gantipassword') ?>" class="nav-link<?= $sidebarIsActive('utility/gantipassword') ? ' active' : '' ?>"><i class="nav-icon bi bi-lock text-white"></i><p>Ganti Password</p></a></li>
                                <li class="nav-item"><a href="<?= site_url('login/keluar') ?>" class="nav-link"><i class="nav-icon bi bi-box-arrow-right text-success"></i><p>Logout</p></a></li>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if ((int) session()->idlevel === 2) : ?>
                            <li class="nav-header">TRANSAKSI PRODUK</li>
                            <li class="nav-item"><a href="<?= site_url('barangmasuk/data') ?>" class="nav-link<?= $sidebarIsActive('barangmasuk') ? ' active' : '' ?>"><i class="nav-icon bi bi-arrow-down-circle text-success"></i><p>Produk Masuk</p></a></li>
                            <li class="nav-item"><a href="<?= site_url('barangkeluar/data') ?>" class="nav-link<?= $sidebarIsActive('barangkeluar') ? ' active' : '' ?>"><i class="nav-icon bi bi-clock-history text-warning"></i><p>Pengiriman</p></a></li>
                            <li class="nav-item"><a href="<?= site_url('permintaanBarangKirim/datakirim') ?>" class="nav-link<?= $sidebarIsActive(['permintaanBarangKirim', 'permintaanBarang']) ? ' active' : '' ?>"><i class="nav-icon bi bi-box text-primary"></i><p>Antar Gudang</p></a></li>
                            <li class="nav-header">TRANSAKSI MATERIAL</li>
                            <li class="nav-item"><a href="<?= site_url('stokmaterial/index') ?>" class="nav-link<?= $sidebarIsActive('stokmaterial') ? ' active' : '' ?>"><i class="nav-icon bi bi-box text-primary"></i><p>Stok Material</p></a></li>
                            <li class="nav-item"><a href="<?= site_url('kebutuhanmaterial/index') ?>" class="nav-link<?= $sidebarIsActive('kebutuhanmaterial') ? ' active' : '' ?>"><i class="nav-icon bi bi-calculator text-info"></i><p>Kebutuhan Material</p></a></li>
                            <li class="nav-item"><a href="<?= site_url('materialmasuk/data') ?>" class="nav-link<?= $sidebarIsActive('materialmasuk') ? ' active' : '' ?>"><i class="nav-icon bi bi-arrow-down-circle text-success"></i><p>Material Masuk</p></a></li>
                            <li class="nav-item"><a href="<?= site_url('materialkeluar/data') ?>" class="nav-link<?= $sidebarIsActive('materialkeluar') ? ' active' : '' ?>"><i class="nav-icon bi bi-arrow-up-circle text-warning"></i><p>Pemakaian Material</p></a></li>
                            <li class="nav-header">UTILITY</li>
                            <li class="nav-item"><a href="<?= site_url('utility/gantipassword') ?>" class="nav-link<?= $sidebarIsActive('utility/gantipassword') ? ' active' : '' ?>"><i class="nav-icon bi bi-lock text-white"></i><p>Ganti Password</p></a></li>
                            <li class="nav-item"><a href="<?= site_url('login/keluar') ?>" class="nav-link"><i class="nav-icon bi bi-box-arrow-right text-success"></i><p>Logout</p></a></li>
                        <?php endif; ?>

                        <?php if ((int) session()->idlevel === 3) : ?>
                            <li class="nav-header">TRANSAKSI PRODUK</li>
                            <li class="nav-item"><a href="<?= site_url('barangkeluar/data') ?>" class="nav-link<?= $sidebarIsActive('barangkeluar') ? ' active' : '' ?>"><i class="nav-icon bi bi-clock-history text-warning"></i><p>Pengiriman</p></a></li>
                            <li class="nav-header">UTILITY</li>
                            <li class="nav-item"><a href="<?= site_url('utility/gantipassword') ?>" class="nav-link<?= $sidebarIsActive('utility/gantipassword') ? ' active' : '' ?>"><i class="nav-icon bi bi-lock text-white"></i><p>Ganti Password</p></a></li>
                            <li class="nav-item"><a href="<?= site_url('login/keluar') ?>" class="nav-link"><i class="nav-icon bi bi-box-arrow-right text-success"></i><p>Logout</p></a></li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
            </nav>

            <?php $logoutFeature = \App\Libraries\AccessControl::logoutFeature(); ?>
            <?php $manualBookFeature = [
                'key' => 'utility.panduan',
                'label' => 'Manual Book',
                'url' => 'panduan/index',
                'icon' => 'bi bi-book',
                'active_patterns' => ['panduan', 'panduan/index'],
            ]; ?>
            <nav class="mt-auto pb-3" aria-label="Menu akun">
                <ul class="nav sidebar-menu flex-column">
                    <?php if (session()->get('userid')) : ?>
                        <li class="nav-item">
                            <a href="<?= site_url($manualBookFeature['url']) ?>" class="nav-link<?= $sidebarIsActive($manualBookFeature['active_patterns']) ? ' active' : '' ?>">
                                <i class="nav-icon bi bi-book text-info"></i>
                                <p>Manual Book</p>
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a href="<?= site_url($logoutFeature['url']) ?>" class="nav-link<?= $sidebarIsActive($logoutFeature['active_patterns'] ?? [$logoutFeature['url']]) ? ' active' : '' ?>">
                            <i class="nav-icon <?= esc($sidebarFeatureIcon((string) ($logoutFeature['icon'] ?? 'sign-out-alt'))) ?>"></i>
                            <p><?= esc($logoutFeature['label']) ?></p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <main class="app-main bg-body-tertiary<?= $manualPreviewMode ? ' m-0 min-vh-100' : '' ?>">
        <div class="app-content-header">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-sm-6">
                        <h3 class="mb-0"><?= esc($this->renderSection('judul')) ?></h3>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end mb-0">
                            <li class="breadcrumb-item"><a href="<?= site_url('dashboard/data') ?>">Home</a></li>
                            <li class="breadcrumb-item active"><?= esc($this->renderSection('judul')) ?></li>
                        </ol>
                    </div>
                </div>
                <?= $this->renderSection('subjudul') ?>
            </div>
        </div>

        <div class="app-content bg-body-tertiary">
            <div class="container-fluid">
                <?= $this->renderSection('isi') ?>
            </div>
        </div>
    </main>

    <footer class="app-footer<?= $manualPreviewMode ? ' d-none' : '' ?>">
        <div class="float-end d-none d-sm-inline">Version 1.0.1</div>
        <strong>Copyright &copy; 2023-<?= date('Y') ?> <a href="https://trisentosaraya.co.id" class="text-decoration-none">TRE</a>.</strong>
        All rights reserved.
    </footer>
</div>

<!-- Modal reusable untuk konfirmasi tindakan -->
<div class="modal fade" id="bootstrapConfirmModal" tabindex="-1" aria-labelledby="bootstrapConfirmTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bootstrapConfirmTitle">Konfirmasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body" id="bootstrapConfirmMessage">Lanjutkan tindakan ini?</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="bootstrapConfirmSubmit">Lanjutkan</button>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('vendor/adminlte4/js/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= base_url('vendor/adminlte4/js/overlayscrollbars.browser.es6.min.js') ?>"></script>
<script src="<?= base_url('vendor/adminlte4/js/adminlte.min.js') ?>"></script>
<script src="<?= base_url('plugins/select2/js/select2.full.min.js') ?>"></script>

<script>
    // Adapter dialog proyek: seluruh notifikasi, konfirmasi, input, dan loading

    window.AppDialog = (function () {
        const state = { element: null, modal: null, resolve: null, options: null, settled: false, result: null, timer: null };

        function ensureModal() {
            if (state.element) return state.element;
            const element = document.createElement('div');
            element.id = 'appDialogModal';
            element.className = 'modal fade';
            element.tabIndex = -1;
            element.setAttribute('aria-hidden', 'true');
            element.innerHTML = `
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content shadow">
                        <div class="modal-header">
                            <h5 class="modal-title d-flex align-items-center gap-2" data-app-dialog-title></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body">
                            <div class="app-dialog-message"></div>
                            <div class="invalid-feedback d-block d-none app-dialog-validation"></div>
                            <div class="app-dialog-input-wrap mt-3 d-none"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary app-dialog-cancel" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-primary app-dialog-confirm">OK</button>
                        </div>
                    </div>
                </div>`;
            document.body.appendChild(element);
            state.element = element;
            state.modal = bootstrap.Modal.getOrCreateInstance(element);
            element.addEventListener('hidden.bs.modal', function () {
                if (state.timer) window.clearTimeout(state.timer);
                state.timer = null;
                if (state.resolve) {
                    state.settled = true;
                    state.resolve(state.result || { isConfirmed: false, isDismissed: true });
                }
                state.resolve = null;
                state.options = null;
                state.result = null;
                state.settled = false;
            });
            return element;
        }

        function normalise(first, second, third) {
            if (first && typeof first === 'object') return Object.assign({}, first);
            return { title: first || '', text: second || '', icon: third || 'info' };
        }

        function iconMarkup(icon) {
            const icons = {
                success: ['check-circle-fill', 'text-success'],
                error: ['x-circle-fill', 'text-danger'],
                warning: ['exclamation-triangle-fill', 'text-warning'],
                info: ['info-circle-fill', 'text-info'],
                question: ['question-circle-fill', 'text-primary']
            };
            const item = icons[String(icon || '').toLowerCase()];
            return item ? `<i class="bi bi-${item[0]} ${item[1]}" aria-hidden="true"></i>` : '';
        }

        function inputElement(options) {
            const input = options.input;
            if (!input || String(input).charAt(0) === '#') return null;
            const type = String(input).toLowerCase();
            if (type === 'textarea') {
                const area = document.createElement('textarea');
                area.className = 'form-control';
                area.rows = 4;
                area.placeholder = options.inputPlaceholder || '';
                area.value = options.inputValue || '';
                return area;
            }
            const control = document.createElement('input');
            control.type = ['text', 'number', 'email', 'password', 'date', 'url', 'tel'].includes(type) ? type : 'text';
            control.className = 'form-control';
            control.placeholder = options.inputPlaceholder || '';
            control.value = options.inputValue || '';
            return control;
        }

        async function confirm() {
            const options = state.options || {};
            const input = state.element.querySelector('.app-dialog-input-wrap input, .app-dialog-input-wrap textarea, .app-dialog-input-wrap select');
            const value = input ? input.value : undefined;
            let resultValue = value;
            try {
                if (typeof options.preConfirm === 'function') {
                    const result = await options.preConfirm(value);
                    if (result === false) return;
                    if (result !== undefined) resultValue = result;
                }
                state.settled = true;
                state.result = { isConfirmed: true, isDismissed: false, value: resultValue };
                state.modal.hide();
            } catch (error) {
                window.AppDialog.showValidationMessage(error && error.message ? error.message : 'Data tidak valid.');
            }
        }

        function fire(first, second, third) {
            const options = normalise(first, second, third);
            const element = ensureModal();
            const title = element.querySelector('[data-app-dialog-title]');
            const message = element.querySelector('.app-dialog-message');
            const validation = element.querySelector('.app-dialog-validation');
            const inputWrap = element.querySelector('.app-dialog-input-wrap');
            const confirmButton = element.querySelector('.app-dialog-confirm');
            const cancelButton = element.querySelector('.app-dialog-cancel');
            title.innerHTML = `${iconMarkup(options.icon)}<span>${options.title || 'Informasi'}</span>`;
            message.innerHTML = options.html !== undefined ? String(options.html) : '';
            if (options.html === undefined) message.textContent = options.text || '';
            validation.textContent = '';
            validation.classList.add('d-none');
            inputWrap.innerHTML = '';
            inputWrap.classList.add('d-none');
            const input = inputElement(options);
            if (input) {
                inputWrap.appendChild(input);
                inputWrap.classList.remove('d-none');
            }
            confirmButton.textContent = options.confirmButtonText || 'OK';
            confirmButton.disabled = false;
            cancelButton.textContent = options.cancelButtonText || 'Batal';
            confirmButton.className = 'btn app-dialog-confirm ' + (options.confirmButtonColor === '#d33' || options.icon === 'error' || options.icon === 'warning' ? 'btn-danger' : 'btn-primary');
            cancelButton.classList.toggle('d-none', options.showCancelButton !== true);
            confirmButton.classList.toggle('d-none', options.showConfirmButton === false);
            const dialog = element.querySelector('.modal-dialog');
            dialog.style.maxWidth = options.width ? String(options.width).replace('px', '') + 'px' : '';
            state.options = options;
            state.result = null;
            state.settled = false;
            if (state.timer) window.clearTimeout(state.timer);
            if (state.modal) state.modal.dispose();
            state.modal = bootstrap.Modal.getOrCreateInstance(element, {
                backdrop: options.allowOutsideClick === false ? 'static' : true,
                keyboard: options.allowEscapeKey !== false
            });
            confirmButton.onclick = confirm;
            return new Promise(function (resolve) {
                state.resolve = resolve;
                state.modal.show();
                window.setTimeout(function () {
                    if (typeof options.didOpen === 'function') options.didOpen();
                }, 0);
                if (options.timer) state.timer = window.setTimeout(function () { state.modal.hide(); }, options.timer);
            });
        }

        return {
            fire: fire,
            close: function () { if (state.modal) state.modal.hide(); },
            getHtmlContainer: function () { return ensureModal().querySelector('.app-dialog-message'); },
            getConfirmButton: function () { return ensureModal().querySelector('.app-dialog-confirm'); },
            showLoading: function () {
                const button = ensureModal().querySelector('.app-dialog-confirm');
                button.disabled = true;
                button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Memproses...';
            },
            showValidationMessage: function (message) {
                const validation = ensureModal().querySelector('.app-dialog-validation');
                validation.textContent = message || 'Data tidak valid.';
                validation.classList.remove('d-none');
            }
        };
    })();

    // Menjaga script jQuery lama tetap kompatibel, tetapi eksekusinya memakai Bootstrap 5.
    if (window.jQuery && !window.jQuery.fn.modal) {
        window.jQuery.fn.modal = function (action) {
            return this.each(function () {
                const instance = bootstrap.Modal.getOrCreateInstance(this);
                if (action === 'show') instance.show();
                if (action === 'hide') instance.hide();
                if (action === 'toggle') instance.toggle();
            });
        };
    }

    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('sidebarMenuSearch');
        const searchClear = document.getElementById('sidebarMenuSearchClear');
        const searchEmpty = document.getElementById('sidebarMenuSearchEmpty');
        const menuItems = Array.from(document.querySelectorAll('.sidebar-menu .nav-item'));
        function filterSidebarMenu() {
            if (!searchInput) return;
            const query = searchInput.value.trim().toLowerCase();
            let visible = 0;
            menuItems.forEach(function (item) {
                const label = (item.textContent || '').toLowerCase();
                const match = !query || label.includes(query);
                item.classList.toggle('d-none', !match);
                if (match) visible += 1;
            });
            searchEmpty?.classList.toggle('d-none', visible > 0 || !query);
        }
        searchInput?.addEventListener('input', filterSidebarMenu);
        searchClear?.addEventListener('click', function () {
            if (searchInput) searchInput.value = '';
            filterSidebarMenu();
            searchInput?.focus();
        });

        const confirmElement = document.getElementById('bootstrapConfirmModal');
        const confirmModal = confirmElement ? new bootstrap.Modal(confirmElement) : null;
        const confirmTitle = document.getElementById('bootstrapConfirmTitle');
        const confirmMessage = document.getElementById('bootstrapConfirmMessage');
        const confirmSubmit = document.getElementById('bootstrapConfirmSubmit');
        let pendingAction = null;

        document.addEventListener('click', function (event) {
            const trigger = event.target.closest('.js-confirm-action');
            if (!trigger || !confirmModal) return;

            event.preventDefault();
            pendingAction = trigger;
            confirmTitle.textContent = trigger.dataset.confirmTitle || 'Konfirmasi';
            confirmMessage.textContent = trigger.dataset.confirmMessage || 'Lanjutkan tindakan ini?';
            confirmSubmit.className = trigger.dataset.confirmButtonClass || 'btn btn-danger';
            confirmModal.show();
        });

        confirmSubmit?.addEventListener('click', async function () {
            if (!pendingAction) return;

            const url = pendingAction.dataset.confirmUrl || pendingAction.getAttribute('href');
            const method = (pendingAction.dataset.confirmMethod || 'POST').toUpperCase();
            const tokenName = document.querySelector('meta[name="csrf-token-name"]')?.content;
            const tokenValue = document.querySelector('meta[name="csrf-token-value"]')?.content;
            const payload = new URLSearchParams();

            if (tokenName && tokenValue) payload.append(tokenName, tokenValue);
            confirmSubmit.disabled = true;

            try {
                if (method === 'GET') {
                    window.location.href = url;
                    return;
                }

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                    },
                    body: payload.toString()
                });

                if (!response.ok) throw new Error('Server mengembalikan HTTP ' + response.status);
                window.location.reload();
            } catch (error) {
                confirmMessage.textContent = error.message || 'Tindakan gagal diproses.';
            } finally {
                confirmSubmit.disabled = false;
            }
        });

        confirmElement?.addEventListener('hidden.bs.modal', function () {
            pendingAction = null;
            confirmSubmit.disabled = false;
        });
    });
</script>

<?= $this->renderSection('scripts') ?>
</body>
</html>
