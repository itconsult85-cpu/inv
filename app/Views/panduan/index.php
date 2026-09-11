<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Manual Book
<?= $this->endSection('judul') ?>

<?= $this->section('subjudul') ?>
Pusat informasi terkait sistem inventory TRE
<?= $this->endSection('subjudul') ?>

<?= $this->section('isi') ?>
<style>
    .manual-shell { color: #172033; overflow: visible; }
    .manual-hero { background: #fff; border: 1px solid rgba(15, 23, 42, .08); border-radius: 10px; box-shadow: 0 14px 35px rgba(15, 23, 42, .06); padding: 24px; }
    .manual-search { align-items: center; background: #f8fafc; border: 1px solid #dbe5ef; border-radius: 999px; display: flex; gap: 12px; max-width: 620px; padding: 12px 18px; }
    .manual-search i { color: #6b778c; }
    .manual-search input { background: transparent; border: 0; color: #172033; flex: 1; outline: 0; }
    .manual-tabs { border-bottom: 1px solid #e4edf5; gap: 8px; }
    .manual-tabs .nav-link { border: 1px solid transparent; border-radius: 999px; color: #65748a; font-weight: 700; margin-bottom: 10px; padding: 10px 16px; }
    .manual-tabs .nav-link.active { background: #12879a; border-color: #12879a; color: #fff; }
    .manual-card { background: #fff; border: 1px solid #e3ebf3; border-radius: 10px; box-shadow: 0 10px 24px rgba(15, 23, 42, .04); height: 100%; }
    .manual-card-header { align-items: center; border-bottom: 1px solid #edf2f7; display: flex; gap: 12px; padding: 16px 18px; }
    .manual-icon { align-items: center; background: #e9f7fa; border-radius: 10px; color: #12879a; display: inline-flex; height: 42px; justify-content: center; width: 42px; }
    .manual-card-body { padding: 18px; }
    .manual-card-title { font-size: 1rem; font-weight: 800; margin: 0; }
    .manual-card-subtitle { color: #718096; font-size: .88rem; margin: 3px 0 0; }
    .manual-badge { background: #fff7d6; border: 1px solid #ffc107; border-radius: 999px; color: #7a5700; display: inline-flex; font-size: .82rem; font-weight: 700; padding: 6px 12px; }
    .manual-empty { display: none; padding: 28px; text-align: center; }
    .manual-section-card { background: #fff; border: 1px solid #e3ebf3; border-radius: 8px; box-shadow: 0 6px 16px rgba(15, 23, 42, .035); overflow: visible; position: relative; }
    .manual-section-head { align-items: center; background: #f8fafc; border-bottom: 1px solid #edf2f7; display: flex; gap: 10px; min-height: 46px; padding: 8px 12px; }
    .manual-feature-list { list-style: none; margin: 0; padding: 0; }
    .manual-feature-list li { border-bottom: 1px solid #edf2f7; padding: 10px 12px; }
    .manual-feature-list li:last-child { border-bottom: 0; }
    .manual-feature-list strong { display: block; font-size: .9rem; margin-bottom: 3px; }
    .manual-feature-list span { color: #67758a; display: block; font-size: .86rem; line-height: 1.35; }
    .manual-section-toggle { border: 0; color: inherit; cursor: pointer; text-align: left; width: 100%; }
    .manual-section-toggle:focus { outline: 0; }
    .manual-section-toggle:focus-visible { box-shadow: inset 0 0 0 2px rgba(18, 135, 154, .35); }
    .manual-section-title-wrap { align-items: center; display: flex; flex: 1; gap: 10px; min-width: 0; }
    .manual-section-icon { border-radius: 8px; height: 30px; width: 30px; }
    .manual-section-icon i { font-size: .78rem; }
    .manual-section-chevron { color: #12879a; margin-left: auto; transition: transform .22s ease; }
    .manual-section-toggle[aria-expanded="false"] .manual-section-chevron { transform: rotate(-90deg); }
    .manual-section-card.is-open { z-index: 50; }
    .manual-section-card.is-open .manual-section-toggle { border-radius: 8px 8px 0 0; }
    .manual-group { overflow: visible; }
    .manual-group .manual-item { position: relative; }
    .tab-content, .tab-pane { overflow: visible; }
    .manual-section-body { background: #fff; border: 1px solid #e3ebf3; border-radius: 0 0 8px 8px; box-shadow: 0 16px 30px rgba(15, 23, 42, .12); left: -1px; max-height: var(--manual-dropdown-max, 420px); opacity: 1; overflow-x: hidden; overflow-y: auto; position: absolute; right: -1px; top: calc(100% - 1px); transform: translateY(0); transition: max-height .28s ease, opacity .2s ease, transform .22s ease; z-index: 35; }
    .manual-section-body.is-closed { max-height: 0; opacity: 0; pointer-events: none; transform: translateY(-6px); }
    .manual-feature-action { background: transparent; border: 0; color: inherit; display: block; padding: 0; text-align: left; width: 100%; }
    .manual-feature-action:hover strong { color: #12879a; }
    .manual-feature-action:focus { outline: 0; }
    .manual-feature-action:focus-visible { box-shadow: inset 0 0 0 2px rgba(18, 135, 154, .28); border-radius: 6px; }
    .manual-preview-backdrop { align-items: center; background: rgba(15, 23, 42, .42); display: none; inset: 0; justify-content: center; padding: 24px; position: fixed; z-index: 2000; }
    .manual-preview-backdrop.is-open { display: flex; }
    .manual-preview-dialog { background: #fff; border: 1px solid #dbe5ef; border-radius: 12px; box-shadow: 0 24px 70px rgba(15, 23, 42, .25); max-height: calc(100vh - 28px); overflow: hidden; width: min(1520px, calc(100vw - 28px)); }
    .manual-preview-header { align-items: center; border-bottom: 1px solid #edf2f7; display: flex; justify-content: space-between; padding: 16px 20px; }
    .manual-preview-close { align-items: center; background: #f1f5f9; border: 0; border-radius: 999px; color: #172033; display: inline-flex; height: 38px; justify-content: center; width: 38px; }
    .manual-preview-body { background: #f5f9fc; padding: 0; }
    .manual-preview-iframe { background: #eef6f9; border: 0; display: block; height: min(780px, calc(100vh - 118px)); width: 100%; }
    .manual-preview-panel { border: 1px solid #e3ebf3; border-radius: 8px; padding: 16px; }
    .manual-preview-list li { align-items: center; border-top: 1px solid #edf2f7; display: flex; justify-content: space-between; padding: 10px 0; }
    @media (max-width: 991.98px) { .manual-preview-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } .manual-preview-main { grid-template-columns: 1fr; } }
    @media (max-width: 767.98px) { .manual-search { max-width: 100%; width: 100%; } .manual-hero { padding: 18px; } }
    .faq-list { align-items: start; display: grid; gap: 10px 16px; grid-template-columns: repeat(2, minmax(0, 1fr)); overflow: visible; }
    @media (max-width: 767.98px) { .faq-list { grid-template-columns: 1fr; } }
    .faq-item { background: #fff; border: 1px solid #e3ebf3; border-radius: 8px; box-shadow: 0 6px 16px rgba(15, 23, 42, .035); position: relative; }
    .faq-item.is-open { z-index: 20; }
    .faq-question { align-items: center; background: transparent; border: 0; color: #172033; display: flex; font-size: .95rem; font-weight: 700; gap: 12px; justify-content: space-between; padding: 14px 16px; text-align: left; width: 100%; }
    .faq-question:focus { outline: 0; }
    .faq-question:focus-visible { box-shadow: inset 0 0 0 2px rgba(18, 135, 154, .28); }
    .faq-question:hover { color: #12879a; }
    .faq-chevron { color: #12879a; flex: 0 0 auto; transition: transform .2s ease; }
    .faq-question[aria-expanded="true"] .faq-chevron { transform: rotate(180deg); }
    .faq-answer { background: #fff; border: 1px solid #e3ebf3; border-top: 0; border-radius: 0 0 8px 8px; box-shadow: 0 16px 30px rgba(15, 23, 42, .12); left: -1px; max-height: 0; opacity: 0; overflow: hidden auto; position: absolute; right: -1px; top: calc(100% - 1px); transition: max-height .22s ease, opacity .18s ease; }
    .faq-answer.is-open { opacity: 1; }
    .faq-answer p { color: #4b5563; font-size: .88rem; line-height: 1.55; margin: 0; padding: 12px 16px 16px; }
</style>

<div class="manual-shell">
    <div class="manual-hero mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start">
            <div class="mb-3 pr-md-3">
                <h4 class="mb-2 font-weight-bold">Pusat informasi terkait website inventory TRE</h4>
                <p class="text-muted mb-0">Gunakan halaman ini untuk melihat ringkasan fitur, alur kerja utama, dan jawaban atas pertanyaan yang sering muncul.</p>
            </div>
            <div class="manual-search">
                <i class="fas fa-search"></i>
                <input type="text" id="manualSearch" placeholder="Cari fitur, alur, atau problem...">
            </div>
        </div>
    </div>

    <ul class="nav manual-tabs mb-4" id="manualTabs" role="tablist">
        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#fitur" role="tab"><i class="fas fa-cubes mr-2"></i>Fitur</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#alur" role="tab"><i class="fas fa-route mr-2"></i>Alur TRE</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#faq" role="tab"><i class="fas fa-question-circle mr-2"></i>FAQ</a></li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="fitur" role="tabpanel">
            <div class="row manual-group">
                <?php
                $manualPreviewUrls = [
                    'Dashboard' => [
                        'title' => 'Preview Dashboard',
                        'subtitle' => 'Gambaran ringkas tampilan dashboard inventory TRE.',
                        'url' => site_url('dashboard/data') . '?manual_preview=dashboard',
                    ],
                    'PO Masuk' => [
                        'title' => 'Preview PO Masuk',
                        'subtitle' => 'Tampilan daftar dan pengelolaan PO masuk dari pelanggan.',
                        'url' => site_url('po/data') . '?manual_preview=po-masuk',
                    ],
                    'PO Keluar' => [
                        'title' => 'Preview PO Keluar',
                        'subtitle' => 'Tampilan daftar PO keluar untuk supplier/vendor.',
                        'url' => site_url('poKeluar/data') . '?manual_preview=po-keluar',
                    ],
                    'Outstanding' => [
                        'title' => 'Preview Outstanding',
                        'subtitle' => 'Tampilan sisa pesanan pelanggan yang belum terkirim dan progres tagihan.',
                        'url' => site_url('outstand/data') . '?manual_preview=outstanding',
                    ],
                    'Keuangan' => [
                        'title' => 'Preview Keuangan',
                        'subtitle' => 'Tampilan hub keuangan untuk Invoice Out, Invoice In, pembayaran, dan reporting.',
                        'url' => site_url('invoiceHub') . '?manual_preview=keuangan',
                    ],
                    'Stok Material' => [
                        'title' => 'Preview Stok Material',
                        'subtitle' => 'Tampilan stok material per gudang dan total stok yang tersedia.',
                        'url' => site_url('stokmaterial/index') . '?manual_preview=stok-material',
                    ],
                    'Kebutuhan Material' => [
                        'title' => 'Preview Kebutuhan Material',
                        'subtitle' => 'Tampilan forecast kebutuhan material dari outstanding PO dan stok produk yang tersedia.',
                        'url' => site_url('kebutuhanmaterial/index') . '?manual_preview=kebutuhan-material',
                    ],
                    'Material Terbuang' => [
                        'title' => 'Preview Material Terbuang',
                        'subtitle' => 'Tampilan estimasi material waste dari produksi.',
                        'url' => site_url('materialterbuang/index') . '?manual_preview=material-terbuang',
                    ],
                    'Material Masuk' => [
                        'title' => 'Preview Material Masuk',
                        'subtitle' => 'Tampilan daftar dan pencatatan material yang diterima dari supplier, konsinyasi, PO keluar, atau adjustment stok.',
                        'url' => site_url('materialmasuk/data') . '?manual_preview=material-masuk',
                    ],
                    'Pemakaian Material' => [
                        'title' => 'Preview Pemakaian Material',
                        'subtitle' => 'Tampilan daftar dan pencatatan material yang keluar/dipakai untuk kebutuhan produksi atau proses.',
                        'url' => site_url('materialkeluar/data') . '?manual_preview=pemakaian-material',
                    ],
                    'Produk Masuk' => [
                        'title' => 'Preview Produk Masuk',
                        'subtitle' => 'Tampilan daftar dan pencatatan produk jadi dari produksi, supplier/PO out, atau adjustment stok.',
                        'url' => site_url('barangmasuk/data') . '?manual_preview=produk-masuk',
                    ],
                    'Pengiriman' => [
                        'title' => 'Preview Pengiriman',
                        'subtitle' => 'Tampilan daftar pengiriman barang ke pelanggan berdasarkan PO dan surat jalan.',
                        'url' => site_url('barangkeluar/data') . '?manual_preview=pengiriman',
                    ],
                    'Antar Gudang' => [
                        'title' => 'Preview Antar Gudang',
                        'subtitle' => 'Tampilan daftar perpindahan produk/material antar lokasi gudang.',
                        'url' => site_url('permintaanBarangKirim/datakirim') . '?manual_preview=antar-gudang',
                    ],
                    'Kategori' => [
                        'title' => 'Preview Kategori',
                        'subtitle' => 'Tampilan data kategori untuk mengelompokkan produk dan material.',
                        'url' => site_url('kategori/index') . '?manual_preview=kategori',
                    ],
                    'Satuan' => [
                        'title' => 'Preview Satuan',
                        'subtitle' => 'Tampilan data satuan yang dipakai pada produk, material, dan transaksi.',
                        'url' => site_url('satuan/index') . '?manual_preview=satuan',
                    ],
                    'Material' => [
                        'title' => 'Preview Material',
                        'subtitle' => 'Tampilan master kode material, nama material, stok minimum, dan data pendukung material.',
                        'url' => site_url('material/index') . '?manual_preview=master-material',
                    ],
                    'Produk' => [
                        'title' => 'Preview Produk',
                        'subtitle' => 'Tampilan master kode produk, nama produk, berat satuan, harga, dan stok minimum.',
                        'url' => site_url('barang/index') . '?manual_preview=master-produk',
                    ],
                    'Pelanggan' => [
                        'title' => 'Preview Pelanggan',
                        'subtitle' => 'Tampilan data pelanggan yang dipakai pada PO masuk, pengiriman, outstanding, dan invoice out.',
                        'url' => site_url('pelanggan/index') . '?manual_preview=master-pelanggan',
                    ],
                    'Supplier' => [
                        'title' => 'Preview Supplier',
                        'subtitle' => 'Tampilan data supplier/vendor yang dipakai pada PO keluar, material masuk, produk masuk, dan invoice in.',
                        'url' => site_url('supplier/index') . '?manual_preview=master-supplier',
                    ],
                    'Management User' => [
                        'title' => 'Preview Management User',
                        'subtitle' => 'Tampilan daftar user, reset password, status user, serta pengaturan hak akses menu dan aksi.',
                        'url' => site_url('users/index') . '?manual_preview=management-user',
                    ],
                    'Log Aktivitas' => [
                        'title' => 'Preview Log Aktivitas',
                        'subtitle' => 'Tampilan riwayat aktivitas user untuk kebutuhan audit dan pengecekan awal.',
                        'url' => site_url('logaktivitas/index') . '?manual_preview=log-aktivitas',
                    ],
                    'Ganti Password' => [
                        'title' => 'Preview Ganti Password',
                        'subtitle' => 'Tampilan form ubah password akun yang sedang login.',
                        'url' => site_url('utility/gantipassword') . '?manual_preview=ganti-password',
                    ],
                ];

                $featureSections = [
                    [
                        'section' => 'Dashboard',
                        'icon' => 'fas fa-home',
                        'items' => [
                            ['Dashboard', 'Ringkasan stok, PO berjalan, PO selesai, produk/material perlu restok, dan filter periode laporan.'],
                        ],
                    ],
                    [
                        'section' => 'Transaksi Order',
                        'icon' => 'fas fa-file-invoice',
                        'items' => [
                            ['PO Masuk', 'Mencatat pesanan dari pelanggan. Bisa input manual atau import PDF, termasuk PO sudah berjalan/migrasi.'],
                            ['PO Keluar', 'Mencatat pesanan ke supplier/vendor, termasuk beli material, PO jasa, titip proses, dan kirim langsung.'],
                            ['Outstanding', 'Melihat sisa pesanan pelanggan yang belum terkirim dan progress tagihan.'],
                            ['Keuangan', 'Menu gabungan untuk Invoice Out, Invoice In, pembayaran, dan reporting keuangan. Invoice Out dibuat dari pengiriman/surat jalan, sedangkan Invoice In dicatat dari PO keluar atau surat jalan yang diterima.'],
                        ],
                    ],
                    [
                        'section' => 'Transaksi Material',
                        'icon' => 'fas fa-dolly-flatbed',
                        'items' => [
                            ['Stok Material', 'Melihat stok material per gudang dan total stok yang tersedia.'],
                            ['Kebutuhan Material', 'Forecast kebutuhan material dari outstanding PO dan stok produk yang tersedia.'],
                            ['Material Terbuang', 'Mencatat material waste agar pengurangan material tetap terlacak.'],
                            ['Material Masuk', 'Mencatat material diterima dari supplier, konsinyasi, PO keluar, atau adjustment stok.'],
                            ['Pemakaian Material', 'Mencatat material yang keluar/dipakai untuk kebutuhan produksi atau proses.'],
                        ],
                    ],
                    [
                        'section' => 'Transaksi Produk',
                        'icon' => 'fas fa-boxes',
                        'items' => [
                            ['Produk Masuk', 'Mencatat produk jadi dari produksi, supplier/PO out, atau adjustment stok.'],
                            ['Pengiriman', 'Mencatat barang yang dikirim ke pelanggan berdasarkan PO dan surat jalan.'],
                            ['Antar Gudang', 'Mencatat perpindahan produk/material antar lokasi gudang.'],
                        ],
                    ],
                    [
                        'section' => 'Master',
                        'icon' => 'fas fa-database',
                        'items' => [
                            ['Kategori', 'Data kategori untuk mengelompokkan produk dan material.'],
                            ['Satuan', 'Data satuan yang dipakai pada produk, material, dan transaksi.'],
                            ['Material', 'Master kode material, nama material, stok minimum, dan data pendukung material.'],
                            ['Produk', 'Master kode produk, nama produk, berat satuan, harga, dan stok minimum.'],
                            ['Pelanggan', 'Data pelanggan yang dipakai pada PO masuk, pengiriman, outstanding, dan invoice out.'],
                            ['Supplier', 'Data supplier/vendor yang dipakai pada PO keluar, material masuk, produk masuk, dan invoice in.'],
                        ],
                    ],
                    [
                        'section' => 'Utility',
                        'icon' => 'fas fa-tools',
                        'items' => [
                            ['Management User', 'Mengelola data user, reset password, status user, serta pengaturan hak akses menu dan aksi.'],
                            ['Log Aktivitas', 'Melihat riwayat aktivitas user untuk kebutuhan audit dan pengecekan awal.'],
                            ['Ganti Password', 'Mengubah password akun yang sedang login.'],
                        ],
                    ],
                ];
                foreach ($featureSections as $sectionIndex => $featureSection) :
                    $keywords = $featureSection['section'] . ' ';
                    foreach ($featureSection['items'] as $item) {
                        $keywords .= $item[0] . ' ' . $item[1] . ' ';
                    }
                ?>
                    <div class="col-sm-6 col-lg-4 col-xl-3 mb-2 manual-item" data-keywords="<?= esc(strtolower($keywords)) ?>">
                        <div class="manual-section-card">
                            <button class="manual-section-head manual-section-toggle" type="button" data-target="#manualFeatureSection<?= $sectionIndex ?>" aria-expanded="false">
                                <span class="manual-section-title-wrap">
                                    <span class="manual-icon manual-section-icon"><i class="<?= esc($featureSection['icon']) ?>"></i></span>
                                    <span>
                                        <span class="manual-card-title d-block"><?= esc($featureSection['section']) ?></span>
                                        <span class="manual-card-subtitle d-block"><?= count($featureSection['items']) ?> fitur di section ini</span>
                                    </span>
                                </span>
                                <i class="fas fa-chevron-down manual-section-chevron"></i>
                            </button>
                            <div class="manual-section-body is-closed" id="manualFeatureSection<?= $sectionIndex ?>">
                                <ul class="manual-feature-list">
                                    <?php foreach ($featureSection['items'] as $item) : ?>
                                        <li>
                                            <?php $previewConfig = $manualPreviewUrls[$item[0]] ?? null; ?>
                                            <?php if ($previewConfig) : ?>
                                                <button
                                                    class="manual-feature-action"
                                                    type="button"
                                                    data-manual-preview-url="<?= esc($previewConfig['url'], 'attr') ?>"
                                                    data-manual-preview-title="<?= esc($previewConfig['title'], 'attr') ?>"
                                                    data-manual-preview-subtitle="<?= esc($previewConfig['subtitle'], 'attr') ?>"
                                                >
                                                    <strong><?= esc($item[0]) ?></strong>
                                                    <span><?= esc($item[1]) ?></span>
                                                </button>
                                            <?php else : ?>
                                                <strong><?= esc($item[0]) ?></strong>
                                                <span><?= esc($item[1]) ?></span>
                                            <?php endif ?>
                                        </li>
                                    <?php endforeach ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>
        </div>

        <div class="tab-pane fade" id="alur" role="tabpanel">
            <div class="row manual-group">
                <?php
                $tutorials = [
                    [
                        'title' => 'Input PO Masuk',
                        'desc' => 'Cara mencatat PO baru dari pelanggan.',
                        'icon' => 'fas fa-file-invoice-dollar',
                        'steps' => [
                            ['title' => 'Buka Form Input PO', 'desc' => 'Klik di sini untuk mulai mencatat PO baru dari pelanggan.', 'url' => site_url('po/data') . '?manual_preview=po-masuk', 'selector' => '[data-manual-preview-href*="po/input"], [onclick*="po/input"]'],
                            ['title' => 'Tanggal PO', 'desc' => 'Sudah terisi otomatis hari ini. Ubah di sini kalau PO-nya bertanggal lain.', 'url' => site_url('po/input') . '?manual_preview=po-masuk', 'selector' => '#tglpo_display'],
                            ['title' => 'Isi No. PO', 'desc' => 'Isi nomor PO dari pelanggan di sini.', 'url' => site_url('po/input') . '?manual_preview=po-masuk', 'selector' => '#nopo'],
                            ['title' => 'Isi Nama Pelanggan', 'desc' => "Ketik nama pelanggan di sini, pilih dari daftar yang muncul. Kalau belum terdaftar, klik tombol '+' di sampingnya untuk tambah pelanggan baru.", 'url' => site_url('po/input') . '?manual_preview=po-masuk', 'selector' => '#namapelanggan'],
                            ['title' => 'Status PO (Opsional)', 'desc' => "Centang 'PO sudah berjalan / migrasi' kalau PO ini sudah berjalan sebelum dicatat di sistem -- nanti muncul kolom tambahan buat isi qty yang sudah terkirim/ditagih sebelumnya.", 'url' => site_url('po/input') . '?manual_preview=po-masuk', 'selector' => '#poMigrasi'],
                            ['title' => 'Cari Produk', 'desc' => 'Ketik kode atau nama produk yang dipesan di sini, lalu pilih dari daftar yang muncul.', 'url' => site_url('po/input') . '?manual_preview=po-masuk', 'selector' => '#kodebarang'],
                            ['title' => 'Isi Qty', 'desc' => 'Atur jumlah (Qty) sesuai kebutuhan pelanggan.', 'url' => site_url('po/input') . '?manual_preview=po-masuk', 'selector' => '#jml'],
                            ['title' => 'Tambahkan ke Draft', 'desc' => 'Klik di sini untuk menambahkan produk ke Draft Item PO.', 'url' => site_url('po/input') . '?manual_preview=po-masuk', 'selector' => '#tombolSimpanItem'],
                            ['title' => 'Simpan PO', 'desc' => 'Ulangi buat produk lain kalau perlu, lalu klik di sini untuk menyimpan PO.', 'url' => site_url('po/input') . '?manual_preview=po-masuk', 'selector' => '#tombolSelesaiTransaksi'],
                        ],
                    ],
                    [
                        'title' => 'Close, Reopen, dan Koreksi PO Masuk',
                        'desc' => 'Menutup sisa qty yang tidak dilanjutkan, buka lagi PO yang sudah di-close, atau mengoreksi qty yang salah input.',
                        'icon' => 'fas fa-tasks',
                        'steps' => [
                            ['title' => 'Buka Halaman Ubah PO', 'desc' => 'Cari PO yang mau ditutup/dibuka/dikoreksi dari daftar ini, lalu klik ikon Edit (pensil) pada barisnya.', 'url' => site_url('po/data') . '?manual_preview=po-masuk', 'selector' => '.po-action-buttons [data-manual-preview-href*="/po/edit/"], .po-action-buttons [onclick*="edit("]'],
                            ['title' => 'Tutup PO', 'desc' => 'Klik di sini kalau mau menutup semua sisa qty yang belum terkirim di PO ini sekaligus.', 'urlPattern' => site_url('po/edit'), 'selector' => '#tombolClosePo'],
                            ['title' => 'Close Item', 'desc' => 'Atau klik "Close Item" pada baris item tertentu (kalau masih ada sisa qty) buat menutup sisa qty item itu saja, bukan seluruh PO.', 'urlPattern' => site_url('po/edit'), 'selector' => '.btn-close-item'],
                            ['title' => 'Koreksi Qty PO', 'desc' => 'Kalau qty PO dari pelanggan ternyata salah input, klik "Koreksi Qty PO" di sini buat membetulkannya. Tombol ini cuma muncul pada item yang sudah pernah di-close.', 'urlPattern' => site_url('po/edit'), 'selector' => '.btn-koreksi-qty'],
                            ['title' => 'Buka Close', 'desc' => 'Klik ikon riwayat (jam) di sini buat lihat histori penutupan item ini -- di dalamnya ada tombol "Buka Close" kalau mau membuka lagi penutupan yang sudah dilakukan.', 'urlPattern' => site_url('po/edit'), 'selector' => '.po-close-history-toggle'],
                        ],
                    ],
                    [
                        'title' => 'Input PO Keluar',
                        'desc' => 'Cara membuat PO Keluar ke supplier atau vendor.',
                        'icon' => 'fas fa-truck-loading',
                        'steps' => [
                            ['title' => 'Buka Form Input PO Keluar', 'desc' => 'Klik di sini untuk membuat PO Keluar ke supplier atau vendor.', 'url' => site_url('poKeluar/data') . '?manual_preview=po-keluar', 'selector' => 'a[href*="poKeluar/input"]'],
                            ['title' => 'Isi No. PO', 'desc' => 'Isi nomor PO Keluar-nya di sini.', 'url' => site_url('poKeluar/input') . '?manual_preview=po-keluar', 'selector' => 'input[name="no_po"]'],
                            ['title' => 'Tanggal PO', 'desc' => 'Sudah terisi otomatis hari ini, ubah kalau perlu.', 'url' => site_url('poKeluar/input') . '?manual_preview=po-keluar', 'selector' => 'input[name="tgl_po"]'],
                            ['title' => 'Isi Nama Supplier/Vendor', 'desc' => 'Ketik nama supplier atau vendor tujuan PO Keluar ini di sini, lalu pilih dari daftar yang muncul.', 'url' => site_url('poKeluar/input') . '?manual_preview=po-keluar', 'selector' => '#supplierInput'],
                            ['title' => 'Pilih Jenis PO', 'desc' => "Pilih PO Produk, PO Jasa, atau PO Material di sini -- ini menentukan detail item yang bisa diisi di bawah. Khusus PO Produk, ada opsi tambahan 'Tampilkan kolom Material di print' yang muncul di bagian Detail Item.", 'url' => site_url('poKeluar/input') . '?manual_preview=po-keluar', 'selector' => '#jenisPo'],
                            ['title' => 'Pilih Jenis Transaksi', 'desc' => "Pilih jenis transaksinya di sini: 'Beli' kalau material dibeli dari supplier, atau 'Titip Proses' kalau barang dititip untuk diproses vendor.", 'url' => site_url('poKeluar/input') . '?manual_preview=po-keluar', 'selector' => '#jenisTransaksi'],
                            ['title' => 'PO Asal (Opsional)', 'desc' => 'Isi kalau PO ini lanjutan dari PO Keluar lain, mis. bahan dari Vendor A dikirim ke Vendor B ini untuk diproses.', 'url' => site_url('poKeluar/input') . '?manual_preview=po-keluar', 'selector' => '#poAsalInput'],
                            ['title' => 'PO Masuk Terkait (Opsional)', 'desc' => 'Isi kalau pembelian material ini memang untuk memenuhi PO Masuk (pesanan pelanggan) tertentu.', 'url' => site_url('poKeluar/input') . '?manual_preview=po-keluar', 'selector' => '#poMasukInput'],
                            ['title' => 'Kirim Langsung (Opsional)', 'desc' => 'Centang ini kalau materialnya dikirim langsung ke pihak lain, tanpa masuk stok TRE dulu.', 'url' => site_url('poKeluar/input') . '?manual_preview=po-keluar', 'selector' => '#kirimLangsung'],
                            ['title' => 'Keterangan (Opsional)', 'desc' => 'Catatan tambahan soal PO Keluar ini, opsional.', 'url' => site_url('poKeluar/input') . '?manual_preview=po-keluar', 'selector' => 'textarea[name="keterangan"]'],
                            ['title' => 'Label Detail (Opsional)', 'desc' => "Label kolom detail item saat PO di-print, mis. 'Lebar Sliting'. Opsional.", 'url' => site_url('poKeluar/input') . '?manual_preview=po-keluar', 'selector' => '#printSpecLabelHeader'],
                            ['title' => 'Cari Item', 'desc' => 'Ketik kode atau nama item yang dipesan di sini, lalu pilih dari daftar yang muncul.', 'url' => site_url('poKeluar/input') . '?manual_preview=po-keluar', 'selector' => '#pilihItemInput'],
                            ['title' => 'Info Print Item (Opsional)', 'desc' => 'Catatan tambahan khusus item ini yang muncul di print, opsional.', 'url' => site_url('poKeluar/input') . '?manual_preview=po-keluar', 'selector' => '#printSpecItem'],
                            ['title' => 'Isi Qty Item', 'desc' => 'Atur jumlah (Qty) item ini.', 'url' => site_url('poKeluar/input') . '?manual_preview=po-keluar', 'selector' => '#qtyItem'],
                            ['title' => 'Isi Harga Item', 'desc' => 'Isi harga satuan item ini.', 'url' => site_url('poKeluar/input') . '?manual_preview=po-keluar', 'selector' => '#hargaItem'],
                            ['title' => 'Tambahkan ke Daftar', 'desc' => 'Klik di sini untuk menambahkan item ke daftar.', 'url' => site_url('poKeluar/input') . '?manual_preview=po-keluar', 'selector' => '#tambahItem'],
                            ['title' => 'Pengaturan Cetak PO (Opsional)', 'desc' => 'Semua field di sini opsional, cuma buat pengaturan tampilan saat PO di-print: Shipping To, TOP, System Payment, Quot Number, Approved By, Discount, PPN 11%, PPH 23, dan Notes Cetak. Isi kalau perlu, atau lewati kalau tidak.', 'url' => site_url('poKeluar/input') . '?manual_preview=po-keluar', 'selector' => '.card.mb-3:has(#printFormatHint)'],
                            ['title' => 'Simpan PO Keluar', 'desc' => 'Ulangi buat item lain kalau perlu, lalu klik di sini untuk menyimpan.', 'url' => site_url('poKeluar/input') . '?manual_preview=po-keluar', 'selector' => '#formPoKeluar button[type="submit"]'],
                        ],
                    ],
                    [
                        'title' => 'Input Pengiriman',
                        'desc' => 'Cara mencatat pengiriman barang ke pelanggan atau ke vendor titip proses.',
                        'icon' => 'fas fa-shipping-fast',
                        'steps' => [
                            ['title' => 'Buka Form Input Pengiriman', 'desc' => 'Klik di sini untuk mencatat pengiriman barang, baik ke pelanggan maupun ke vendor titip proses.', 'url' => site_url('barangkeluar/data') . '?manual_preview=pengiriman', 'selector' => '[data-manual-preview-href*="permintaanPengiriman/langsung"], [onclick*="permintaanPengiriman/langsung"]'],
                            ['title' => 'Pilih No. PO', 'desc' => 'Pilih atau ketik No. PO yang mau dikirim di sini -- daftar item PO-nya nanti otomatis muncul di bawah.', 'url' => site_url('permintaanPengiriman/langsung') . '?manual_preview=pengiriman', 'selector' => '#noPoKirim'],
                            ['title' => 'Pilih Jenis Pengiriman', 'desc' => "Pilih 'Baru' kalau pengiriman motong stok gudang seperti biasa, atau 'Migrasi' kalau cuma catat surat jalan yang telat tanpa motong stok lagi.", 'url' => site_url('permintaanPengiriman/langsung') . '?manual_preview=pengiriman', 'selector' => '#sumberKirim'],
                            ['title' => 'Tanggal Pengiriman', 'desc' => 'Sudah terisi otomatis hari ini, ubah kalau perlu.', 'url' => site_url('permintaanPengiriman/langsung') . '?manual_preview=pengiriman', 'selector' => '#tanggalPengiriman'],
                            ['title' => 'Isi No. Surat Jalan', 'desc' => 'Isi nomor surat jalannya di sini. Item dengan nomor surat jalan yang sama nanti digabung jadi 1 surat jalan.', 'url' => site_url('permintaanPengiriman/langsung') . '?manual_preview=pengiriman', 'selector' => '#noDoKirim'],
                            ['title' => 'Klik Item PO', 'desc' => 'Klik salah satu baris item PO di sini untuk mengisi detail pengirimannya.', 'url' => site_url('permintaanPengiriman/langsung') . '?manual_preview=pengiriman', 'selector' => '#tabelItemPo'],
                            ['title' => 'Pilih Asal Gudang', 'desc' => 'Pilih gudang asal barang yang dikirim di sini.', 'url' => site_url('permintaanPengiriman/langsung') . '?manual_preview=pengiriman', 'selector' => '#gudangId'],
                            ['title' => 'Isi Qty Kirim', 'desc' => 'Atur jumlah (Qty) yang dikirim.', 'url' => site_url('permintaanPengiriman/langsung') . '?manual_preview=pengiriman', 'selector' => '#qtyKirim'],
                            ['title' => 'Tambahkan ke Rencana Kirim', 'desc' => 'Klik di sini untuk menambahkan ke daftar kirim sesi ini.', 'url' => site_url('permintaanPengiriman/langsung') . '?manual_preview=pengiriman', 'selector' => '#simpanRencana'],
                            ['title' => 'Selesaikan Pengiriman', 'desc' => 'Ulangi buat item lain kalau perlu, lalu klik di sini untuk menyimpan pengiriman.', 'url' => site_url('permintaanPengiriman/langsung') . '?manual_preview=pengiriman', 'selector' => '#tombolSelesaiKirim'],
                        ],
                    ],
                    [
                        'title' => 'Input Transfer Antar Gudang',
                        'desc' => 'Cara memindahkan stok produk/material dari satu gudang ke gudang lain.',
                        'icon' => 'fas fa-dolly-flatbed',
                        'steps' => [
                            ['title' => 'Buka Form Input Transfer', 'desc' => 'Klik di sini untuk memindahkan stok dari satu gudang ke gudang lain.', 'url' => site_url('permintaanBarangKirim/datakirim') . '?manual_preview=antar-gudang', 'selector' => '[data-manual-preview-href*="permintaanBarang/input"], [onclick*="permintaanBarang/input"]'],
                            ['title' => 'Tanggal Permintaan Transfer', 'desc' => 'Sudah terisi otomatis hari ini, ubah kalau perlu.', 'url' => site_url('permintaanBarang/input') . '?manual_preview=antar-gudang', 'selector' => '#tglpermintaan'],
                            ['title' => 'Isi No. Surat Jalan', 'desc' => 'Isi nomor surat jalan transfer ini di sini.', 'url' => site_url('permintaanBarang/input') . '?manual_preview=antar-gudang', 'selector' => '#permintaan'],
                            ['title' => 'Pilih Gudang Asal', 'desc' => 'Pilih gudang asal barang yang mau ditransfer di sini.', 'url' => site_url('permintaanBarang/input') . '?manual_preview=antar-gudang', 'selector' => '#gudang'],
                            ['title' => 'Pilih Jenis Item', 'desc' => 'Pilih Produk atau Material di sini.', 'url' => site_url('permintaanBarang/input') . '?manual_preview=antar-gudang', 'selector' => '#jenis_item'],
                            ['title' => 'Cari Item', 'desc' => 'Ketik kode atau nama produk/material yang mau dipindah di sini, lalu pilih dari daftar yang muncul.', 'url' => site_url('permintaanBarang/input') . '?manual_preview=antar-gudang', 'selector' => '#kodebarang'],
                            ['title' => 'Isi Qty', 'desc' => 'Atur jumlah (Qty) yang mau dipindah.', 'url' => site_url('permintaanBarang/input') . '?manual_preview=antar-gudang', 'selector' => '#jml'],
                            ['title' => 'Tambahkan ke Draft', 'desc' => 'Klik di sini untuk menambahkan ke draft.', 'url' => site_url('permintaanBarang/input') . '?manual_preview=antar-gudang', 'selector' => '#tombolSimpanItem'],
                            ['title' => 'Selesaikan Transfer', 'desc' => 'Ulangi buat item lain kalau perlu, lalu klik di sini untuk menyimpan.', 'url' => site_url('permintaanBarang/input') . '?manual_preview=antar-gudang', 'selector' => '#tombolSelesaiTransaksi'],
                        ],
                    ],
                    [
                        'title' => 'Catat Material Masuk',
                        'desc' => 'Cara mencatat material yang baru datang, termasuk adjustment stok material.',
                        'icon' => 'fas fa-boxes',
                        'steps' => [
                            ['title' => 'Buka Form Material Masuk', 'desc' => 'Klik di sini untuk mencatat material yang baru datang.', 'url' => site_url('materialmasuk/data') . '?manual_preview=material-masuk', 'selector' => '[data-manual-preview-href*="materialmasuk/input"], [onclick*="materialmasuk/input"]'],
                            ['title' => 'Tanggal', 'desc' => 'Sudah terisi otomatis hari ini, ubah kalau perlu.', 'url' => site_url('materialmasuk/input') . '?manual_preview=material-masuk', 'selector' => '#tglfaktur'],
                            ['title' => 'Pilih Sumber Material', 'desc' => "Pilih sumbernya di sini: 'Beli dari Supplier' (default), 'Adjustment Stok', atau 'Konsinyasi dari Pelanggan' -- field lain di bawah menyesuaikan pilihan ini.", 'url' => site_url('materialmasuk/input') . '?manual_preview=material-masuk', 'selector' => '#sumber_material'],
                            ['title' => 'No. Invoice (Opsional)', 'desc' => 'Isi nomor invoice dari supplier di sini, opsional.', 'url' => site_url('materialmasuk/input') . '?manual_preview=material-masuk', 'selector' => '#nofaktur'],
                            ['title' => 'Isi No. Surat Jalan', 'desc' => 'Isi nomor surat jalan dari supplier di sini -- wajib kecuali sumbernya Adjustment Stok.', 'url' => site_url('materialmasuk/input') . '?manual_preview=material-masuk', 'selector' => '#no_do'],
                            ['title' => 'Cari Supplier', 'desc' => "Ketik nama supplier di sini, pilih dari daftar yang muncul. Kalau belum terdaftar, klik tombol '+' di sampingnya.", 'url' => site_url('materialmasuk/input') . '?manual_preview=material-masuk', 'selector' => '#namasupplier'],
                            ['title' => 'Pilih Lokasi Gudang', 'desc' => 'Pilih gudang tujuan material ini di sini.', 'url' => site_url('materialmasuk/input') . '?manual_preview=material-masuk', 'selector' => '#gudang'],
                            ['title' => 'Pilih PO Keluar (Opsional)', 'desc' => 'Kalau material ini dari PO Keluar yang sudah dibuat, pilih di sini biar item-nya otomatis terisi. Opsional.', 'url' => site_url('materialmasuk/input') . '?manual_preview=material-masuk', 'selector' => '#po_keluar_input'],
                            ['title' => 'Cari Material', 'desc' => 'Ketik kode atau nama material yang datang di sini, lalu pilih dari daftar yang muncul.', 'url' => site_url('materialmasuk/input') . '?manual_preview=material-masuk', 'selector' => '#kodematerial'],
                            ['title' => 'Isi Qty', 'desc' => 'Atur jumlah (Qty) material yang datang.', 'url' => site_url('materialmasuk/input') . '?manual_preview=material-masuk', 'selector' => '#jml'],
                            ['title' => 'Tambahkan ke Draft', 'desc' => 'Klik di sini untuk menambahkan ke draft.', 'url' => site_url('materialmasuk/input') . '?manual_preview=material-masuk', 'selector' => '#tombolSimpanItem'],
                            ['title' => 'Simpan Material Masuk', 'desc' => 'Ulangi buat material lain kalau perlu, lalu klik di sini untuk menyimpan.', 'url' => site_url('materialmasuk/input') . '?manual_preview=material-masuk', 'selector' => '#tombolSelesaiTransaksi'],
                        ],
                    ],
                    [
                        'title' => 'Catat Produk Masuk',
                        'desc' => 'Cara mencatat produk yang baru selesai produksi, termasuk adjustment stok produk.',
                        'icon' => 'fas fa-box-open',
                        'steps' => [
                            ['title' => 'Buka Form Produk Masuk', 'desc' => 'Klik di sini untuk mencatat produk yang baru selesai produksi atau masuk stok.', 'url' => site_url('barangmasuk/data') . '?manual_preview=produk-masuk', 'selector' => '[data-manual-preview-href*="barangmasuk/input"], [onclick*="barangmasuk/input"]'],
                            ['title' => 'Pilih Sumber Produk', 'desc' => "Pilih sumbernya di sini: 'Beli dari Supplier' (default), 'Adjustment Stok', 'Produksi', atau 'Produksi Material Pelanggan' -- field lain di bawah menyesuaikan pilihan ini.", 'url' => site_url('barangmasuk/input') . '?manual_preview=produk-masuk', 'selector' => '#sumberProduk'],
                            ['title' => 'Tanggal Transaksi', 'desc' => 'Sudah terisi otomatis hari ini, ubah kalau perlu.', 'url' => site_url('barangmasuk/input') . '?manual_preview=produk-masuk', 'selector' => '#tglfaktur'],
                            ['title' => 'Pilih No PO (Opsional)', 'desc' => 'Pilih PO Keluar yang produknya datang di sini -- bisa pilih lebih dari satu. Wajib untuk sumber Beli dari Supplier.', 'url' => site_url('barangmasuk/input') . '?manual_preview=produk-masuk', 'selector' => '#nofaktur + .select2-container'],
                            ['title' => 'Cari Supplier', 'desc' => "Ketik nama supplier di sini, pilih dari daftar yang muncul. Kalau belum terdaftar, klik tombol '+' di sampingnya.", 'url' => site_url('barangmasuk/input') . '?manual_preview=produk-masuk', 'selector' => '#namasupplier'],
                            ['title' => 'Pilih Gudang Tujuan', 'desc' => 'Pilih gudang tujuan produk ini di sini.', 'url' => site_url('barangmasuk/input') . '?manual_preview=produk-masuk', 'selector' => '#gudang'],
                            ['title' => 'Klik Item PO Keluar', 'desc' => 'Klik salah satu baris item PO Keluar di sini buat ngisi form Kode Produk di bawah otomatis, lalu isi Qty yang datang.', 'url' => site_url('barangmasuk/input') . '?manual_preview=produk-masuk', 'selector' => '#tabelItemPoKeluar'],
                            ['title' => 'Isi Qty', 'desc' => 'Atur jumlah (Qty) produk yang datang.', 'url' => site_url('barangmasuk/input') . '?manual_preview=produk-masuk', 'selector' => '#jml'],
                            ['title' => 'Tambahkan ke Draft', 'desc' => 'Klik di sini untuk menambahkan ke draft.', 'url' => site_url('barangmasuk/input') . '?manual_preview=produk-masuk', 'selector' => '#tombolSimpanItem'],
                            ['title' => 'Simpan Produk Masuk', 'desc' => 'Ulangi buat produk lain kalau perlu, lalu klik di sini untuk menyimpan.', 'url' => site_url('barangmasuk/input') . '?manual_preview=produk-masuk', 'selector' => '#tombolSelesaiTransaksi'],
                        ],
                    ],
                    [
                        'title' => 'Buat Invoice Out',
                        'desc' => 'Cara membuat tagihan ke pelanggan.',
                        'icon' => 'fas fa-hand-holding-usd',
                        'steps' => [
                            ['title' => 'Buka Form Invoice Out', 'desc' => 'Klik di sini untuk membuat tagihan ke pelanggan dari surat jalan yang sudah terkirim.', 'url' => site_url('invoiceOut/data') . '?manual_preview=keuangan', 'selector' => 'a[href*="invoiceOut/create"]'],
                            ['title' => 'Pilih PO', 'desc' => 'Pilih PO yang mau ditagih di sini -- kalau ada beberapa surat jalan, nanti muncul daftarnya untuk dipilih dulu, baru item invoice-nya muncul di bawah.', 'url' => site_url('invoiceOut/create') . '?manual_preview=keuangan', 'selector' => '#pilihPoText'],
                            ['title' => 'Isi No. Invoice', 'desc' => 'Isi nomor invoice yang mau diterbitkan di sini.', 'url' => site_url('invoiceOut/create') . '?manual_preview=keuangan', 'selector' => 'input[name="invoice_no"]'],
                            ['title' => 'Tanggal Invoice', 'desc' => 'Sudah terisi otomatis hari ini, ubah kalau perlu.', 'url' => site_url('invoiceOut/create') . '?manual_preview=keuangan', 'selector' => 'input[name="invoice_date"]'],
                            ['title' => 'Isi Nama Penandatangan', 'desc' => 'Isi nama yang menandatangani invoice ini di sini.', 'url' => site_url('invoiceOut/create') . '?manual_preview=keuangan', 'selector' => 'input[name="signer_name"]'],
                            ['title' => 'Isi Jabatan Penandatangan', 'desc' => "Isi jabatannya di sini, mis. 'Direktur'.", 'url' => site_url('invoiceOut/create') . '?manual_preview=keuangan', 'selector' => 'input[name="signer_position"]'],
                            ['title' => 'PPN (Opsional)', 'desc' => 'Centang kalau invoice ini memakai PPN, lalu atur persentasenya.', 'url' => site_url('invoiceOut/create') . '?manual_preview=keuangan', 'selector' => '#ppnEnabled'],
                            ['title' => 'PPh 23 (Opsional)', 'desc' => 'Centang kalau invoice ini memakai PPh 23, lalu atur persentasenya.', 'url' => site_url('invoiceOut/create') . '?manual_preview=keuangan', 'selector' => '#pphEnabled'],
                            ['title' => 'DP (Opsional)', 'desc' => 'Centang kalau invoice ini memakai DP, lalu atur persentasenya.', 'url' => site_url('invoiceOut/create') . '?manual_preview=keuangan', 'selector' => '#dpEnabled'],
                            ['title' => 'Info Rekening (Opsional)', 'desc' => 'Info rekening ini muncul di invoice yang dicetak, sudah terisi default. Ubah kalau perlu (Pemilik Rekening, Nama Bank, No Rekening, NPWP).', 'url' => site_url('invoiceOut/create') . '?manual_preview=keuangan', 'selector' => 'input[name="bank_owner"]'],
                            ['title' => 'Cek/Ubah Harga', 'desc' => 'Harga di sini otomatis dari master produk. Ubah manual kalau ada harga khusus untuk invoice ini.', 'url' => site_url('invoiceOut/create') . '?manual_preview=keuangan', 'selector' => '#tabelItemInvoice .input-harga'],
                            ['title' => 'Simpan Invoice', 'desc' => 'Klik di sini untuk menyimpan invoice.', 'url' => site_url('invoiceOut/create') . '?manual_preview=keuangan', 'selector' => '#btnSimpanInvoice'],
                        ],
                    ],
                    [
                        'title' => 'Catat Invoice In',
                        'desc' => 'Cara mencatat tagihan dari supplier atau vendor.',
                        'icon' => 'fas fa-file-invoice',
                        'steps' => [
                            ['title' => 'Buka Form Invoice In', 'desc' => 'Klik di sini untuk mencatat tagihan dari supplier atau vendor.', 'url' => site_url('invoiceIn/data') . '?manual_preview=keuangan', 'selector' => 'a[href*="invoiceIn/create"]'],
                            ['title' => 'Pilih Sumber Tagihan', 'desc' => 'Pilih transaksi penerimaan atau PO Keluar yang mau ditagihkan di sini -- kalau dari PO Keluar dan ada beberapa surat jalan, nanti muncul daftarnya untuk dipilih dulu, baru item invoice-nya muncul di bawah.', 'url' => site_url('invoiceIn/create') . '?manual_preview=keuangan', 'selector' => '#pilihSumberText'],
                            ['title' => 'Isi No. Invoice Supplier', 'desc' => 'Isi nomor invoice dari supplier/vendor di sini.', 'url' => site_url('invoiceIn/create') . '?manual_preview=keuangan', 'selector' => 'input[name="invoice_no"]'],
                            ['title' => 'Tanggal Invoice', 'desc' => 'Sudah terisi otomatis hari ini, ubah kalau perlu.', 'url' => site_url('invoiceIn/create') . '?manual_preview=keuangan', 'selector' => 'input[name="invoice_date"]'],
                            ['title' => 'Upload File Invoice (Opsional)', 'desc' => 'Upload file invoice dari supplier di sini, opsional (PDF/JPG/PNG, maksimal 10 MB).', 'url' => site_url('invoiceIn/create') . '?manual_preview=keuangan', 'selector' => 'input[name="invoice_file"]'],
                            ['title' => 'PPN (Opsional)', 'desc' => 'Centang kalau invoice ini memakai PPN, lalu atur persentasenya.', 'url' => site_url('invoiceIn/create') . '?manual_preview=keuangan', 'selector' => '#ppnEnabled'],
                            ['title' => 'PPh 23 (Opsional)', 'desc' => 'Centang kalau invoice ini memakai PPh 23, lalu atur persentasenya.', 'url' => site_url('invoiceIn/create') . '?manual_preview=keuangan', 'selector' => '#pphEnabled'],
                            ['title' => 'DP (Opsional)', 'desc' => 'Centang kalau invoice ini memakai DP, lalu atur persentasenya.', 'url' => site_url('invoiceIn/create') . '?manual_preview=keuangan', 'selector' => '#dpEnabled'],
                            ['title' => 'Cek/Ubah Harga Satuan', 'desc' => 'Harga di sini otomatis dari PO Keluar (atau kosong kalau bukan dari PO Keluar). Sesuaikan dengan invoice supplier kalau beda.', 'url' => site_url('invoiceIn/create') . '?manual_preview=keuangan', 'selector' => '#detailIn .harga'],
                            ['title' => 'Simpan Invoice In', 'desc' => 'Klik di sini untuk menyimpan.', 'url' => site_url('invoiceIn/create') . '?manual_preview=keuangan', 'selector' => 'form[action*="invoiceIn/save"] button.btn-success'],
                        ],
                    ],
                ];
                foreach ($tutorials as $tutorial) : ?>
                    <div class="col-lg-6 mb-3 manual-item" data-keywords="<?= esc(strtolower($tutorial['title'] . ' ' . $tutorial['desc'])) ?>">
                        <div class="manual-card">
                            <div class="manual-card-header">
                                <span class="manual-icon"><i class="<?= esc($tutorial['icon']) ?>"></i></span>
                                <div><h5 class="manual-card-title"><?= esc($tutorial['title']) ?></h5><p class="manual-card-subtitle"><?= esc($tutorial['desc']) ?></p></div>
                            </div>
                            <div class="manual-card-body">
                                <p class="text-muted mb-3"><?= count($tutorial['steps']) ?> langkah -- klik Mulai, nanti akan muncul penunjuk langsung di halaman aslinya, tinggal ikuti tombol Selanjutnya.</p>
                                <button
                                    type="button"
                                    class="btn btn-primary btn-sm manual-tutorial-start"
                                    data-manual-tutorial="<?= esc(json_encode($tutorial['steps']), 'attr') ?>"
                                    data-manual-tutorial-title="<?= esc($tutorial['title'], 'attr') ?>"
                                >
                                    <i class="fas fa-play mr-1"></i> Mulai Tutorial
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>
        </div>

        <div class="tab-pane fade" id="faq" role="tabpanel">
            <div class="faq-list manual-group">
                <?php
                $faqs = [
                    ['q' => 'Kenapa saya tidak bisa login, muncul pesan "Akun ini sedang login di perangkat/browser lain"?', 'a' => 'Sistem membatasi 1 sesi login per akun. Logout dulu dari perangkat/browser lain yang masih pakai akun itu, atau tunggu beberapa saat sampai sesi lama otomatis berakhir.'],
                    ['q' => 'Kenapa menu tertentu tidak muncul di sidebar saya?', 'a' => 'Menu yang tampil tergantung hak akses (role) akun Anda. Kalau merasa perlu akses ke fitur tertentu, hubungi Admin/Management User untuk diaktifkan.'],
                    ['q' => 'Bagaimana cara mengganti password saya?', 'a' => "Buka menu Utility > Ganti Password, isi password lama dan password baru, lalu simpan."],
                    ['q' => 'Apa bedanya "Close PO" dengan "Close Item"?', 'a' => 'Close PO menutup semua sisa qty yang belum terkirim di satu PO sekaligus. Close Item cuma menutup sisa qty pada satu baris item tertentu di PO itu -- item lain di PO yang sama tetap berjalan normal.'],
                    ['q' => 'Kapan saya perlu pakai "Koreksi Qty PO", dan kenapa tombolnya kadang tidak muncul?', 'a' => 'Dipakai kalau qty PO dari pelanggan ternyata salah input. Tombol ini cuma muncul pada item yang statusnya sudah pernah di-close -- kalau item masih berjalan normal, ubah langsung qty-nya lewat halaman edit PO.'],
                    ['q' => 'Apa itu "Titip Proses" di PO Keluar?', 'a' => 'Jenis transaksi untuk barang/material milik TRE yang cuma dititip diproses/diolah vendor, bukan pembelian material baru dari vendor itu.'],
                    ['q' => 'Apa fungsi centang "Kirim Langsung" di PO Keluar?', 'a' => 'Dicentang kalau material dikirim langsung ke pihak lain (misalnya dari vendor A ke vendor B) tanpa masuk stok TRE dulu.'],
                    ['q' => 'Kenapa PO yang sudah saya buat tidak muncul di pilihan saat Input Pengiriman?', 'a' => 'Cuma PO yang masih ada sisa qty belum terkirim (atau qty migrasi) yang muncul di situ. Kalau PO sudah terkirim penuh, otomatis tidak muncul lagi di daftar pilihan.'],
                    ['q' => 'Apa bedanya jenis pengiriman "Baru" dan "Migrasi"?', 'a' => '"Baru" motong stok gudang seperti pengiriman normal. "Migrasi" cuma mencatat surat jalan yang telat diinput, tanpa motong stok lagi -- karena barangnya sudah lama terkirim di luar sistem.'],
                    ['q' => 'Kapan saya pakai "Adjustment Stok" di Material Masuk / Produk Masuk?', 'a' => 'Dipakai untuk koreksi stok awal atau stok yang tidak berasal dari transaksi normal (bukan dari pembelian/produksi) -- misalnya waktu pertama kali migrasi data ke sistem ini.'],
                    ['q' => 'Apa bedanya Invoice Out dan Invoice In?', 'a' => 'Invoice Out adalah tagihan yang dibuat TRE ke pelanggan (piutang). Invoice In adalah tagihan yang diterima TRE dari supplier/vendor (hutang).'],
                    ['q' => 'Kenapa saya tidak bisa membuat Invoice Out untuk PO tertentu?', 'a' => 'Invoice Out cuma bisa dibuat dari PO yang sudah punya qty terkirim (surat jalan) dan belum ditagihkan semuanya. Kalau PO belum ada pengiriman sama sekali, belum bisa ditagih.'],
                    ['q' => 'Bagaimana kalau 1 PO dikirim lewat beberapa surat jalan berbeda?', 'a' => 'Aman, sistem mendukung itu. Saat bikin invoice, Anda bisa pilih satu atau beberapa surat jalan sekaligus untuk digabung jadi 1 invoice.'],
                    ['q' => 'Bagaimana cara memindahkan stok dari satu gudang ke gudang lain?', 'a' => 'Pakai menu Transaksi Produk > Antar Gudang > Input Transfer. Pilih gudang asal, cari item, isi qty, lalu klik Selesaikan Transfer.'],
                    ['q' => 'Kenapa saya tidak bisa menghapus PO/data yang sudah ada Invoice-nya?', 'a' => 'Data yang sudah ditagihkan (ada Invoice Out/In terkait) dikunci supaya laporan keuangan tetap konsisten. Batalkan/hapus dulu Invoice-nya kalau memang perlu mengubah data di baliknya.'],
                    ['q' => 'Bagaimana cara melihat riwayat aktivitas user di sistem?', 'a' => 'Buka menu Utility > Log Aktivitas (kalau akun Anda punya akses ke situ) untuk melihat histori aksi yang dilakukan tiap user.'],
                    ['q' => 'Kenapa tur "Mulai Tutorial" di Alur TRE kadang tidak nyorot elemen tertentu?', 'a' => 'Beberapa langkah tutorial menunjuk ke field yang cuma muncul tergantung pilihan lain di form (misalnya field yang baru aktif setelah pilih opsi tertentu). Kalau langkah sebelumnya belum benar-benar dikerjakan, langkah itu bisa tampil tanpa sorotan spesifik.'],
                    ['q' => 'Apa yang terjadi kalau saya klik "Buka Close" pada riwayat penutupan item PO?', 'a' => 'Item yang sebelumnya ditutup (di-close) akan dibuka lagi qty-nya, sehingga bisa dikirim/dilanjutkan seperti biasa.'],
                    ['q' => 'Apa bedanya PO Produk, PO Jasa, dan PO Material di PO Keluar?', 'a' => 'Menentukan detail item yang bisa diisi di form: PO Produk untuk barang jadi, PO Jasa untuk pekerjaan/vendor, PO Material untuk material mentah. Pilihan ini juga mengubah kolom yang muncul di hasil cetak PO.'],
                    ['q' => 'Kenapa ada pilihan PPN, PPh 23, dan DP saat bikin Invoice?', 'a' => 'Itu pengaturan pajak/pembayaran opsional untuk invoice yang bersangkutan -- centang kalau memang berlaku, nominalnya dihitung otomatis dari persentase yang diisi.'],
                    ['q' => 'Supplier atau pelanggan saya belum terdaftar, gimana?', 'a' => "Klik tombol '+' di samping field Supplier/Pelanggan buat nambah data baru langsung dari form itu, tidak perlu pindah ke menu Master dulu."],
                    ['q' => 'Kenapa PO saya tidak bisa diedit / field-nya jadi abu-abu (terkunci)?', 'a' => 'Kemungkinan PO itu sudah dalam status terkunci (misalnya sudah pernah di-close sebagian). Perubahan qty di PO yang terkunci harus lewat aksi Close, Buka Close, atau Koreksi Qty di halaman itu, bukan diedit langsung.'],
                    ['q' => 'Bagaimana cara upload file invoice atau bukti transfer dari supplier?', 'a' => "Ada field 'Upload File Invoice' di form Catat Invoice In -- sifatnya opsional, format yang didukung PDF/JPG/JPEG/PNG, maksimal 10 MB."],
                ];
                foreach ($faqs as $i => $faq) : ?>
                    <div class="faq-item manual-item" data-keywords="<?= esc(strtolower($faq['q'] . ' ' . $faq['a'])) ?>">
                        <button type="button" class="faq-question" aria-expanded="false" data-target="#faqAnswer<?= $i ?>">
                            <span><?= esc($faq['q']) ?></span>
                            <i class="fas fa-chevron-down faq-chevron"></i>
                        </button>
                        <div class="faq-answer" id="faqAnswer<?= $i ?>">
                            <p><?= esc($faq['a']) ?></p>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>
        </div>
    </div>

    <div class="manual-preview-backdrop" id="manualPagePreview" aria-hidden="true">
        <div class="manual-preview-dialog" role="dialog" aria-modal="true" aria-labelledby="manualPreviewTitle">
            <div class="manual-preview-header">
                <div>
                    <h5 class="font-weight-bold mb-1" id="manualPreviewTitle">Preview</h5>
                    <p class="text-muted mb-0" id="manualPreviewSubtitle">Gambaran ringkas halaman inventory TRE.</p>
                </div>
                <button class="manual-preview-close" type="button" data-manual-preview-close aria-label="Tutup preview"><i class="fas fa-times"></i></button>
            </div>
            <div class="manual-preview-body">
                <iframe class="manual-preview-iframe" id="manualPreviewFrame" title="Preview halaman" src="about:blank"></iframe>
            </div>
        </div>
    </div>
    <div class="manual-empty text-muted" id="manualEmpty"><i class="fas fa-search mb-2"></i><div>Tidak ada panduan yang cocok dengan pencarian.</div></div>
</div>

<script>
    (function() {
        const searchInput = document.getElementById('manualSearch');
        const emptyState = document.getElementById('manualEmpty');

        function filterManual() {
            const query = (searchInput.value || '').trim().toLowerCase();
            const activePane = document.querySelector('.tab-pane.active');
            const items = activePane ? activePane.querySelectorAll('.manual-item') : [];
            let visibleCount = 0;

            items.forEach(function(item) {
                const keywords = item.getAttribute('data-keywords') || '';
                const visible = !query || keywords.indexOf(query) !== -1;
                item.style.display = visible ? '' : 'none';
                if (visible) visibleCount++;
            });

            emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
        }

        function setDropdownLimit(toggle, target) {
            const footerSpace = 78;
            const rect = toggle.getBoundingClientRect();
            const available = window.innerHeight - rect.bottom - footerSpace;
            const maxHeight = Math.max(170, Math.min(440, available));
            target.style.setProperty('--manual-dropdown-max', maxHeight + 'px');
        }

        document.querySelectorAll('.manual-section-toggle').forEach(function(toggle) {
            toggle.addEventListener('click', function() {
                const target = document.querySelector(toggle.getAttribute('data-target'));
                const isOpen = toggle.getAttribute('aria-expanded') === 'true';
                if (!target) return;

                if (!isOpen) setDropdownLimit(toggle, target);
                toggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
                target.classList.toggle('is-closed', isOpen);
                toggle.closest('.manual-section-card')?.classList.toggle('is-open', !isOpen);
            });
        });

        window.addEventListener('resize', function() {
            document.querySelectorAll('.manual-section-toggle[aria-expanded="true"]').forEach(function(toggle) {
                const target = document.querySelector(toggle.getAttribute('data-target'));
                if (target) setDropdownLimit(toggle, target);
            });
        });

        document.querySelectorAll('.faq-question').forEach(function(question) {
            question.addEventListener('click', function() {
                const answer = document.querySelector(question.getAttribute('data-target'));
                const item = question.closest('.faq-item');
                const isOpen = question.getAttribute('aria-expanded') === 'true';
                if (!answer) return;

                question.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
                item?.classList.toggle('is-open', !isOpen);
                if (isOpen) {
                    answer.classList.remove('is-open');
                    answer.style.maxHeight = '0px';
                } else {
                    answer.classList.add('is-open');
                    answer.style.maxHeight = Math.min(answer.scrollHeight, 360) + 'px';
                }
            });
        });

        const pagePreview = document.getElementById('manualPagePreview');
        const previewFrame = document.getElementById('manualPreviewFrame');
        const previewTitle = document.getElementById('manualPreviewTitle');
        const previewSubtitle = document.getElementById('manualPreviewSubtitle');

        function closePreview() {
            if (!pagePreview) return;
            pagePreview.classList.remove('is-open');
            pagePreview.setAttribute('aria-hidden', 'true');
        }

        function openPreview(title, subtitle, url) {
            if (!pagePreview || !previewFrame) return;
            if (previewTitle) previewTitle.textContent = title || 'Preview';
            if (previewSubtitle) previewSubtitle.textContent = subtitle || 'Gambaran ringkas halaman inventory TRE.';
            previewFrame.src = url || 'about:blank';
            pagePreview.classList.add('is-open');
            pagePreview.setAttribute('aria-hidden', 'false');
        }

        document.querySelectorAll('[data-manual-preview-url]').forEach(function(button) {
            button.addEventListener('click', function(event) {
                event.stopPropagation();
                openPreview(
                    button.getAttribute('data-manual-preview-title'),
                    button.getAttribute('data-manual-preview-subtitle'),
                    button.getAttribute('data-manual-preview-url')
                );
            });
        });

        document.querySelectorAll('[data-manual-preview-close]').forEach(function(button) {
            button.addEventListener('click', closePreview);
        });

        if (pagePreview) {
            pagePreview.addEventListener('click', function(event) {
                if (event.target !== pagePreview) return;
                closePreview();
            });
        }

        document.addEventListener('keydown', function(event) {
            if (!pagePreview || !pagePreview.classList.contains('is-open')) return;
            if (event.key === 'Escape') closePreview();
        });

        // Tur berjalan langsung di halaman aslinya (bukan di dalam preview iframe/modal).
        // State tur disimpan di sessionStorage supaya tetap jalan lintas reload halaman;
        // dibaca & dirender oleh coach-mark engine di main/layout.php.
        document.querySelectorAll('[data-manual-tutorial]').forEach(function(button) {
            button.addEventListener('click', function(event) {
                event.stopPropagation();
                let steps = [];
                try {
                    steps = JSON.parse(button.getAttribute('data-manual-tutorial') || '[]');
                } catch (e) {
                    steps = [];
                }
                if (!steps.length) return;
                sessionStorage.setItem('treTourActive', JSON.stringify({
                    title: button.getAttribute('data-manual-tutorial-title') || '',
                    steps: steps,
                    index: 0
                }));
                window.location.href = steps[0].url;
            });
        });

        if (searchInput) searchInput.addEventListener('input', filterManual);
        $('#manualTabs a[data-toggle="tab"]').on('shown.bs.tab', filterManual);

        // Balik dari tur (coach-mark) selalu ke sini dengan hash #alur --
        // buka langsung tab Alur TRE-nya, jangan diem di tab Fitur (default).
        // Pakai click() asli (bukan jQuery .tab('show')) supaya nggak perlu
        // mikirin apakah plugin $.fn.tab ke-register duluan atau nggak.
        // DIBUNGKUS window.load: <script> section 'isi' ini dirender lebih
        // awal dari <script src=".../bootstrap.bundle.min.js"> di layout,
        // jadi kalau click() dipanggil langsung di sini (bukan nunggu event),
        // classnya bootstrap yang nangkep klik buat switch tab BELUM ke-attach
        // sama sekali -- klik-nya jalan tapi nggak ngefek.
        window.addEventListener('load', function() {
            if (window.location.hash === '#alur') {
                const alurTabLink = document.querySelector('#manualTabs a[href="#alur"]');
                if (alurTabLink) alurTabLink.click();
            }
        });
    })();
</script>
<?= $this->endSection() ?>