<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
// $routes->get('/', 'Home::index');

$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(true);
$routes->set404Override();

$routes->get('/', 'Login::index');
$routes->post('login/cekUser', 'Login::cekUser');
$routes->get('main', 'Main::index');
$routes->get('main/index', 'Main::index');
$routes->post('stok/cetakLaporan', 'Stok::cetakLaporan');
$routes->get('laporan', 'Laporan::index');
$routes->get('laporan/index', 'Laporan::index');
$routes->get('laporan/cetak-raw-produk', 'Laporan::cetak_raw_produk');
$routes->post('laporan/cetak-raw-produk-periode', 'Laporan::cetak_raw_produk_periode');
$routes->post('laporan/tampilGrafikRawProduk', 'Laporan::tampilGrafikRawProduk');
$routes->get('laporan/cetak-barang-masuk', 'Laporan::cetak_barang_masuk');
$routes->post('laporan/cetak-barang-masuk-periode', 'Laporan::cetak_barang_masuk_periode');
$routes->post('laporan/tampilGrafikBarangMasuk', 'Laporan::tampilGrafikBarangMasuk');
$routes->get('laporan/cetak-barang-keluar', 'Laporan::cetak_barang_keluar');
$routes->post('laporan/cetak-barang-keluar-periode', 'Laporan::cetak_barang_keluar_periode');
$routes->post('laporan/tampilGrafikBarangKeluar', 'Laporan::tampilGrafikBarangKeluar');
$routes->get('websocket', 'WebSocketController::index');

$routes->get('data', 'DataController::index');
$routes->post('data/get_filtered_data', 'Data::get_filtered_data');
$routes->get('permintaanPengiriman/data', 'PermintaanPengiriman::data');
$routes->get('permintaanPengiriman/input', 'PermintaanPengiriman::input');
$routes->get('permintaanPengiriman/langsung', 'PermintaanPengiriman::langsung');
$routes->get('permintaanPengiriman/langsung/(:segment)', 'PermintaanPengiriman::langsung/$1');
$routes->get('permintaanPengiriman/proses/(:segment)', 'PermintaanPengiriman::proses/$1');
$routes->get('permintaanPengiriman/pilih-cetak/(:segment)', 'PermintaanPengiriman::pilihCetak/$1');
$routes->get('permintaanPengiriman/cetak/(:segment)', 'PermintaanPengiriman::cetak/$1');
$routes->get('permintaanBarang/cetak/(:segment)', 'PermintaanBarang::cetak/$1');
$routes->get('permintaanBarang/modalCariMaterial', 'PermintaanBarang::modalCariMaterial');
$routes->post('permintaanBarang/listDataMaterial', 'PermintaanBarang::listDataMaterial');
$routes->post('permintaanBarang/ambilDataMaterial', 'PermintaanBarang::ambilDataMaterial');
$routes->get('kebutuhanmaterial/index', 'KebutuhanMaterial::index');
$routes->get('kebutuhanmaterial/data', 'KebutuhanMaterial::data');
$routes->get('kebutuhanmaterial/cetak', 'KebutuhanMaterial::cetak');
$routes->get('materialterbuang/index', 'MaterialTerbuang::index');
$routes->get('materialterbuang/data', 'MaterialTerbuang::data');
$routes->get('poKeluar/data', 'PoKeluar::data');
$routes->get('poHabisPakai/index', 'PoHabisPakai::index');
$routes->post('poHabisPakai/simpan', 'PoHabisPakai::simpan');
$routes->get('poHabisPakai/edit/(:num)', 'PoHabisPakai::edit/$1');
$routes->post('poHabisPakai/update/(:num)', 'PoHabisPakai::update/$1');
$routes->post('poHabisPakai/hapus/(:num)', 'PoHabisPakai::hapus/$1');
$routes->post('poHabisPakai/terima', 'PoHabisPakai::terima');
$routes->post('poHabisPakai/updatePenerimaan/(:num)', 'PoHabisPakai::updatePenerimaan/$1');
$routes->post('poHabisPakai/hapusPenerimaan/(:num)', 'PoHabisPakai::hapusPenerimaan/$1');
$routes->get('poKeluar/input', 'PoKeluar::input');
$routes->post('poKeluar/simpan', 'PoKeluar::simpan');
$routes->get('poKeluar/detail/(:num)', 'PoKeluar::detail/$1');
$routes->get('poKeluar/edit/(:num)', 'PoKeluar::edit/$1');
$routes->post('poKeluar/update/(:num)', 'PoKeluar::update/$1');
$routes->get('poKeluar/cetak/(:num)', 'PoKeluar::cetak/$1');
$routes->get('poKeluar/pilihanAktif/(:segment)', 'PoKeluar::pilihanAktif/$1');
$routes->post('poKeluar/batal/(:num)', 'PoKeluar::batal/$1');
$routes->post('poKeluar/hapus/(:num)', 'PoKeluar::hapus/$1');
$routes->get('po/import', 'Po::import');
$routes->get('po/import-preview', 'Po::import');
$routes->post('po/listData', 'Po::listData');
$routes->post('po/import-preview', 'Po::previewImport');
$routes->post('po/import-simpan', 'Po::simpanImport');
$routes->post('po/tampilDataTemp', 'Po::tampilDataTemp');
$routes->post('po/hapusDraftPo', 'Po::hapusDraftPo');
$routes->post('po/ambilDataBarang', 'Po::ambilDataBarang');
$routes->post('po/listDataBarang', 'Po::listDataBarang');
$routes->post('po/simpanItem', 'Po::simpanItem');
$routes->post('po/selesaiTransaksi', 'Po::selesaiTransaksi');
$routes->post('po/hapusItem', 'Po::hapusItem');
$routes->get('po/modalCariBarang', 'Po::modalCariBarang');
$routes->post('po/modalPo', 'Po::modalPo');
$routes->post('po/ambilTotalBerat', 'Po::ambilTotalBerat');
$routes->post('po/ambilTotalHarga', 'Po::ambilTotalHarga');
$routes->post('po/tampilDataDetail', 'Po::tampilDataDetail');
$routes->post('po/hapusItemDetail', 'Po::hapusItemDetail');
$routes->post('po/editItem', 'Po::editItem');
$routes->post('po/updateNopo', 'Po::updateNopo');
$routes->post('po/updatePelanggan', 'Po::updatePelanggan');
$routes->post('po/simpanItemDetail', 'Po::simpanItemDetail');
$routes->post('po/reopenCloseLog', 'Po::reopenCloseLog');
$routes->post('po/koreksiQtyItem', 'Po::koreksiQtyItem');
$routes->get('invoiceIn/file/(:num)', 'InvoiceIn::file/$1');
$routes->get('invoiceIn/uploadFileInvoice/(:num)', 'InvoiceIn::uploadFileInvoice/$1');
$routes->post('invoiceIn/simpanFileInvoice/(:num)', 'InvoiceIn::simpanFileInvoice/$1');
$routes->post('invoiceIn/hapusFileInvoice/(:num)', 'InvoiceIn::hapusFileInvoice/$1');
$routes->get('invoiceIn/uploadBuktiTransfer/(:num)', 'InvoiceIn::uploadBuktiTransfer/$1');
$routes->post('invoiceIn/simpanBuktiTransfer/(:num)', 'InvoiceIn::simpanBuktiTransfer/$1');
$routes->get('invoiceIn/buktiTransfer/(:num)', 'InvoiceIn::buktiTransfer/$1');
$routes->post('invoiceIn/cancel/(:num)', 'InvoiceIn::cancel/$1');
$routes->post('invoiceIn/hapus/(:num)', 'InvoiceIn::hapus/$1');
$routes->get('invoiceOut/create', 'InvoiceOut::create');
$routes->get('invoiceOut/create/(:segment)', 'InvoiceOut::create/$1');
$routes->post('invoiceOut/save', 'InvoiceOut::save');
$routes->get('invoiceOut/pembayaran', 'InvoiceOut::pembayaran');
$routes->post('invoiceOut/simpanPembayaran', 'InvoiceOut::simpanPembayaran');
$routes->post('invoiceOut/tandaiLunas/(:num)', 'InvoiceOut::tandaiLunas/$1');
$routes->post('invoiceOut/cancel/(:num)', 'InvoiceOut::cancel/$1');
$routes->post('invoiceOut/hapus/(:num)', 'InvoiceOut::hapus/$1');
$routes->get('invoiceHub', 'InvoiceHub::index');
$routes->get('invoiceHub/reporting', 'InvoiceHub::reporting');
$routes->post('invoiceHub/reporting', 'InvoiceHub::reporting');
$routes->get('panduan', 'Panduan::index');
$routes->get('panduan/index', 'Panduan::index');
$routes->get('logaktivitas', 'LogAktivitas::index');
$routes->get('logaktivitas/index', 'LogAktivitas::index');
$routes->get('logaktivitas/listdata', 'LogAktivitas::listData');
$routes->get('logaktivitas/listArchive', 'LogAktivitas::listArchive');
$routes->get('logaktivitas/downloadArchive/(:num)', 'LogAktivitas::downloadArchive/$1');
$routes->post('logaktivitas/simpanEmailPenerima', 'LogAktivitas::simpanEmailPenerima');
$routes->get('cron/arsip-log-mingguan', 'Cron::arsipLogMingguan');
$routes->get('users/akses', 'Users::akses');
$routes->post('users/simpanakses', 'Users::simpanakses');


$routes->get('/kategori/hapus/(:any)', 'Kategori::index');
$routes->delete('/kategori/hapus/(:any)', 'Kategori::hapus/$1');

$routes->get('/po/hapus/(:any)', 'Po::index');
$routes->delete('/po/hapus/(:any)', 'Po::hapus/$1');

$routes->get('/permintaanbarang/hapus/(:any)', 'Permintaan::index');
$routes->delete('/permintaanbarang/hapus/(:any)', 'Permintaan::hapus/$1');

$routes->get('/satuan/hapus/(:any)', 'Satuan::index');
$routes->delete('/satuan/hapus/(:any)', 'Satuan::hapus/$1');

$routes->get('/berat/hapus/(:any)', 'Berat::index');
$routes->delete('/berat/hapus/(:any)', 'Berat::hapus/$1');

$routes->get('/gudang/hapus/(:any)', 'Gudang::index');
$routes->delete('/gudang/hapus/(:any)', 'Gudang::hapus/$1');

$routes->get('/material/hapus/(:any)', 'Material::index');
$routes->delete('/material/hapus/(:any)', 'Material::hapus/$1');

$routes->get('/materialmasuk/hapus/(:any)', 'Material::index');
$routes->delete('/material masuk/hapus/(:any)', 'Material::hapus/$1');

$routes->get('/barang/hapus/(:any)', 'Barang::index');
$routes->delete('/barang/hapus/(:any)', 'Barang::hapus/$1');

$routes->get('produksi/edit/(:segment)', 'Produksi::edit/$1');
$routes->post('produksi/ambilDataBarangProduksi', 'Produksi::ambilDataBarangProduksi');
$routes->post('produksi/materialProduk', 'Produksi::materialProduk');
$routes->post('produksi/update', 'Produksi::update');
$routes->get('baranghabispakai/index', 'BarangHabisPakai::index');
$routes->post('baranghabispakai/simpanBarang', 'BarangHabisPakai::simpanBarang');
$routes->post('baranghabispakai/updateBarang', 'BarangHabisPakai::updateBarang');
$routes->post('baranghabispakai/hapusBarang', 'BarangHabisPakai::hapusBarang');
$routes->post('baranghabispakai/terimaBarangVendor', 'BarangHabisPakai::terimaBarangVendor');
$routes->post('baranghabispakai/simpanPermintaan', 'BarangHabisPakai::simpanPermintaan');
$routes->post('baranghabispakai/setujui/(:num)', 'BarangHabisPakai::setujui/$1');
$routes->post('baranghabispakai/tolak/(:num)', 'BarangHabisPakai::tolak/$1');
$routes->get('barangmasuk/pilihanPoKeluarAktif', 'Barangmasuk::pilihanPoKeluarAktif');

$routes->post('cari-barang-masuk', 'BarangMasukController::cariBarangMasuk');

$routes->get('permintaanBarang/cetakPeriode', 'PermintaanBarang::cetakPeriode');
$routes->get('barangkeluar/cetak-do/(:segment)', 'Barangkeluar::cetakDo/$1');
$routes->get('barangkeluar/detail-do/(:segment)', 'Barangkeluar::detailDo/$1');
$routes->post('barangkeluar/dokumen-pengiriman', 'Barangkeluar::dokumenPengiriman');
$routes->post('barangkeluar/simpan-dokumen-pengiriman', 'Barangkeluar::simpanDokumenPengiriman');
$routes->post('barangkeluar/hapus-dokumen-pengiriman', 'Barangkeluar::hapusDokumenPengiriman');
$routes->get('barangkeluar/file-btb/(:num)', 'Barangkeluar::fileBtb/$1');
$routes->post('barangkeluar/ubah-no-surat-jalan', 'Barangkeluar::ubahNoSuratJalan');

if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
