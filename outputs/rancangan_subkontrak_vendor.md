# Rancangan skema order proses vendor dan scrap

## Tujuan

Skema ini mengakomodasi material yang dibeli perusahaan tetapi dititipkan atau dikirim langsung ke vendor, diproses oleh satu atau beberapa vendor, menghasilkan material antara atau produk jadi, serta memiliki sisa dan scrap yang harus dapat direkonsiliasi. DDL MariaDB usulan berada pada [rancangan_subkontrak_vendor.sql](rancangan_subkontrak_vendor.sql). DDL tersebut **belum dijalankan**.

## Komponen tabel

- `gudang_lokasi_virtual` menandai baris `gudang` yang merupakan lokasi vendor atau lokasi transit. Satu lokasi vendor dipetakan ke `supplier.supid`; pemasok dan pemroses dapat memakai master supplier yang sama, tetapi aplikasi tetap perlu membedakan perannya.
- `bom_produk` dan `detail_bom_produk` menyimpan versi BOM produk dengan kuantitas material per unit. Ini diperlukan untuk membandingkan kebutuhan standar dengan pemakaian nyata. Kolom lama `barang.brgmat` berupa teks tidak dapat menggantikan BOM kuantitatif.
- `order_proses_vendor` merupakan satu tahap kerja di satu vendor. Contoh: satu order tamping di Vendor B, satu order potong di Vendor D, dan satu order perakitan frame guard di Vendor B. Jenis proses membedakan material-ke-material dan material-ke-produk. PO jasa ditautkan lewat `po_jasa_detail_id`.
- `order_proses_vendor_bahan` merekam input yang dialokasikan ke order, kuantitas yang dipakai, dikembalikan, masih tertahan di vendor, dan hilang/selisih. Sumber pembelian dapat ditelusuri melalui detail penerimaan; hasil proses vendor sebelumnya dapat menjadi sumber input tahap berikutnya.
- `order_proses_vendor_hasil` mencatat hasil baik berupa material antara maupun produk jadi. Untuk hasil material, masukkan sebagai stok di lokasi vendor. Untuk produk jadi yang sampai di perusahaan, gunakan alur penerimaan produk yang sudah ada.
- `order_proses_vendor_scrap` menautkan scrap ke bahan input atau hasil proses. Scrap dapat dikembalikan ke stok bahan, dibukukan sebagai material scrap, dijual, dibuang, atau masih ditahan vendor. Jika scrap ditimbun sebagai stok, wajib tentukan `material_scrap_id` dan gudang tujuan.
- `transfer_lokasi_material` dan `detail_transfer_lokasi_material` mencatat perpindahan material antargudang/vendor. Pengiriman Vendor D ke Vendor B melalui gudang transit sehingga saldo Vendor D berkurang pada saat dikirim, saldo transit bertambah, dan saldo Vendor B bertambah ketika barang diterima.
- `mutasi_stok_material` adalah jejak pergerakan material yang append-only. `stokmaterial` tetap dapat dipakai sebagai saldo ringkas/cache, tetapi harus diperbarui bersamaan dengan ledger di dalam transaksi database.

## Contoh alur Frame Guard

1. Catat PO material SGCC 0,4 dari Supplier A. Jika barang dikirim langsung ke Vendor B, penerimaan material masuk ke `materialmasuk` dengan lokasi gudang virtual Vendor B.
2. Buat `order_proses_vendor` jenis `MATERIAL_KE_MATERIAL` untuk tamping SGCC di Vendor B. Catat input SGCC dan hasil SGCC setelah tamping. Jika spesifikasi/identitas berubah, gunakan kode material hasil proses yang berbeda.
3. Catat PO material Galv Mesh dari Supplier C, dengan penerimaan langsung ke lokasi gudang virtual Vendor D.
4. Buat order `MATERIAL_KE_MATERIAL` untuk pemotongan di Vendor D. Catat input, hasil potongan baik, dan scrap.
5. Buat transfer dari lokasi Vendor D melalui transit ke Vendor B. Saat diterima, hasil potongan menambah saldo material di Vendor B.
6. Buat BOM Frame Guard yang berisi kuantitas standar SGCC hasil tamping dan Galv Mesh potong per unit. Buat order `MATERIAL_KE_PRODUK` di Vendor B, hubungkan BOM, lalu catat pemakaian aktual, produk baik, scrap, sisa, dan saldo yang tetap berada di vendor.
7. Saat Frame Guard dikirim ke perusahaan, catat penerimaan menggunakan alur penerimaan produk existing. Jangan menaikkan saldo gudang perusahaan hanya berdasarkan jumlah yang dilaporkan selesai oleh vendor sebelum barang diterima.

## Rekonsiliasi kuantitas

Pada penutupan satu baris input proses, aplikasi perlu memastikan:

`qty_dialokasikan = qty_dipakai + qty_dikembalikan + qty_sisa_di_vendor + qty_hilang`

Scrap adalah bagian dari `qty_dipakai`, bukan tambahan yang boleh dihitung dua kali. Jumlah scrap pada tabel scrap harus tidak melebihi bahan yang dipakai. Hasil baik dan scrap kemudian dipakai menghitung yield proses. Jika unit hasil berbeda dari unit input, tetapkan konversi unit yang disetujui; jangan membandingkan kg, meter, lembar, dan pcs secara langsung.

## Catatan kompatibilitas dengan dump

Dump menunjukkan MariaDB 10.11.14. Tabel lama yang dipakai rancangan adalah `gudang.gdgid` (`INT UNSIGNED`), `supplier.supid` (`INT`), `material.matid` (`INT`), `barang.brgkode` (`CHAR(100)`), `po_keluar.id` dan `detail_po_keluar.id` (`BIGINT UNSIGNED`), serta `detail_materialmasuk.id` (`BIGINT` signed). DDL disusun mengikuti tipe tersebut.

Presisi kuantitas perlu dibenahi sebelum implementasi: `stokmaterial.stok` masih `DECIMAL(10,0)`, sedangkan `detail_materialmasuk.detjml` dan `detail_materialkeluar.detjml` masih integer. DDL memberi contoh perubahan ke `DECIMAL(18,4)` dalam bentuk komentar agar tidak dijalankan tanpa audit. `detail_produksi.qty_material` dan detail PO Keluar sudah mendukung pecahan.

## Aturan implementasi penting

1. Gunakan gudang Cikarang/Cirebon untuk stok fisik perusahaan, gudang virtual per vendor untuk barang milik perusahaan yang berada di vendor, dan satu gudang virtual transit untuk transfer yang belum diterima.
2. Jangan memakai satu saldo gabungan "Gudang Vendor" tanpa identitas vendor; itu akan mencampur stok Vendor B dengan Vendor D.
3. Saat input PO/penerimaan vendor, order proses, mutasi stok, scrap, dan transfer diposting, lakukan validasi dan update saldo dalam transaksi database. Gunakan row lock saat menghitung saldo dan `idempotency_key` agar retry AJAX tidak menggandakan mutasi.
4. Validasi aplikasi harus memastikan lokasi pada order benar-benar dipetakan ke vendor yang sama, PO jasa adalah PO jasa vendor terkait, hasil input antarproses cocok materialnya, serta transfer tidak melebihi saldo sumber.
5. Jangan hapus order/transfer yang sudah diposting. Batalkan dengan mutasi reversal dan alasan audit.
6. Sebelum membuat unique key `stokmaterial(materialid, gudang)`, periksa dan bereskan baris saldo ganda terlebih dahulu.

## Tahap implementasi yang disarankan

1. Finalisasi satuan dasar per material (misalnya kg untuk gulungan, lembar atau pcs untuk hasil potong) dan kode material hasil tamping/potong.
2. Tambahkan lokasi virtual dan uji transfer bahan internal → vendor → transit → vendor.
3. Implementasikan order proses material-ke-material beserta hasil dan scrap.
4. Masukkan BOM Frame Guard, lalu implementasikan perakitan material-ke-produk.
5. Hubungkan PO jasa dan penerimaan produk; migrasikan/ubah presisi kuantitas setelah uji rekonsiliasi serta backup.
