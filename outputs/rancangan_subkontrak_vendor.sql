-- RANCANGAN DDL (belum diterapkan ke database)
-- Target: MariaDB 10.11.14 dari dump yang diberikan.
--
-- Asumsi integrasi tabel lama:
--   gudang.gdgid             INT UNSIGNED
--   supplier.supid           INT
--   material.matid           INT
--   barang.brgkode           CHAR(100) utf8mb3_general_ci
--   po_keluar.id             BIGINT UNSIGNED
--   detail_po_keluar.id      BIGINT UNSIGNED
--   detail_materialmasuk.id  BIGINT (signed)
--
-- Alur posting yang wajib dilakukan aplikasi dalam transaksi database:
--   1) tulis mutasi_stok_material dan perbarui stokmaterial sebagai saldo ringkas;
--   2) transfer ke vendor: lokasi asal -> lokasi transit -> gudang virtual vendor;
--   3) konsumsi proses mengurangi stok vendor; hasil material menambah stok vendor;
--   4) hasil produk jadi diterima melalui alur penerimaan produk yang sudah ada.
-- Jangan menjalankan DDL ini langsung pada database produksi sebelum review dan backup.

CREATE TABLE IF NOT EXISTS `gudang_lokasi_virtual` (
    `gudang_id` INT(11) UNSIGNED NOT NULL,
    `jenis_lokasi` ENUM('VENDOR', 'TRANSIT') NOT NULL,
    `vendor_supid` INT(11) DEFAULT NULL,
    `kode_lokasi` VARCHAR(50) NOT NULL,
    `aktif` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`gudang_id`),
    UNIQUE KEY `uq_gudang_lokasi_virtual_ref` (`gudang_id`, `vendor_supid`),
    UNIQUE KEY `uq_gudang_vendor_kode` (`vendor_supid`, `kode_lokasi`),
    KEY `idx_gudang_lokasi_jenis` (`jenis_lokasi`, `aktif`),
    CONSTRAINT `fk_gudang_lokasi_virtual_gudang`
        FOREIGN KEY (`gudang_id`) REFERENCES `gudang` (`gdgid`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_gudang_lokasi_virtual_vendor`
        FOREIGN KEY (`vendor_supid`) REFERENCES `supplier` (`supid`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `chk_gudang_lokasi_virtual_vendor`
        CHECK ((`jenis_lokasi` = 'VENDOR' AND `vendor_supid` IS NOT NULL)
            OR (`jenis_lokasi` = 'TRANSIT' AND `vendor_supid` IS NULL))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- BOM kuantitatif. barang.brgmat tetap dapat dipakai untuk kompatibilitas lama,
-- tetapi tidak cukup untuk menghitung kebutuhan/scrap karena tidak menyimpan qty.
CREATE TABLE IF NOT EXISTS `bom_produk` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `kode_produk` CHAR(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
    `versi` SMALLINT(5) UNSIGNED NOT NULL DEFAULT 1,
    `status` ENUM('DRAFT', 'AKTIF', 'NONAKTIF') NOT NULL DEFAULT 'DRAFT',
    `berlaku_mulai` DATE DEFAULT NULL,
    `berlaku_sampai` DATE DEFAULT NULL,
    `catatan` VARCHAR(500) DEFAULT NULL,
    `created_by` VARCHAR(100) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_bom_produk_versi` (`kode_produk`, `versi`),
    CONSTRAINT `fk_bom_produk_barang`
        FOREIGN KEY (`kode_produk`) REFERENCES `barang` (`brgkode`)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `detail_bom_produk` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `bom_produk_id` BIGINT(20) UNSIGNED NOT NULL,
    `material_id` INT(11) NOT NULL,
    `qty_per_unit` DECIMAL(18,4) NOT NULL,
    `toleransi_scrap_persen` DECIMAL(7,4) NOT NULL DEFAULT 0.0000,
    `satuan_kode` VARCHAR(30) NOT NULL,
    `catatan` VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_detail_bom_material` (`bom_produk_id`, `material_id`),
    CONSTRAINT `fk_detail_bom_produk_header`
        FOREIGN KEY (`bom_produk_id`) REFERENCES `bom_produk` (`id`)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_detail_bom_material`
        FOREIGN KEY (`material_id`) REFERENCES `material` (`matid`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `chk_detail_bom_qty` CHECK (`qty_per_unit` > 0 AND `toleransi_scrap_persen` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `order_proses_vendor` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `nomor_order` VARCHAR(50) NOT NULL,
    `jenis_proses` ENUM('MATERIAL_KE_MATERIAL', 'MATERIAL_KE_PRODUK') NOT NULL,
    `vendor_supid` INT(11) NOT NULL,
    `gudang_vendor_id` INT(11) UNSIGNED NOT NULL,
    `po_jasa_detail_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `bom_produk_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `status` ENUM('DRAFT', 'MENUNGGU_MATERIAL', 'DIKIRIM', 'DIPROSES', 'SELESAI_SEBAGIAN', 'SELESAI', 'BATAL') NOT NULL DEFAULT 'DRAFT',
    `tanggal_order` DATE NOT NULL,
    `tanggal_target_selesai` DATE DEFAULT NULL,
    `tanggal_selesai` DATETIME DEFAULT NULL,
    `qty_target` DECIMAL(18,4) DEFAULT NULL,
    `satuan_target` VARCHAR(30) DEFAULT NULL,
    `catatan` TEXT DEFAULT NULL,
    `created_by` VARCHAR(100) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_order_proses_nomor` (`nomor_order`),
    KEY `idx_order_proses_vendor_status` (`vendor_supid`, `status`, `tanggal_order`),
    KEY `idx_order_proses_po_jasa` (`po_jasa_detail_id`),
    CONSTRAINT `fk_order_proses_vendor_supplier`
        FOREIGN KEY (`vendor_supid`) REFERENCES `supplier` (`supid`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_order_proses_vendor_lokasi`
        FOREIGN KEY (`gudang_vendor_id`, `vendor_supid`)
        REFERENCES `gudang_lokasi_virtual` (`gudang_id`, `vendor_supid`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_order_proses_po_jasa_detail`
        FOREIGN KEY (`po_jasa_detail_id`) REFERENCES `detail_po_keluar` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_order_proses_bom`
        FOREIGN KEY (`bom_produk_id`) REFERENCES `bom_produk` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `chk_order_proses_bom`
        CHECK (`jenis_proses` <> 'MATERIAL_KE_PRODUK' OR `bom_produk_id` IS NOT NULL)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Satu baris per material/sumber lot yang dialokasikan untuk order proses.
-- qty_dipakai mencakup bagian bahan yang berubah menjadi output baik maupun scrap.
CREATE TABLE IF NOT EXISTS `order_proses_vendor_bahan` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_proses_id` BIGINT(20) UNSIGNED NOT NULL,
    `material_id` INT(11) NOT NULL,
    `materialmasuk_detail_id` BIGINT(20) DEFAULT NULL,
    `hasil_proses_asal_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `qty_rencana` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
    `qty_dialokasikan` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
    `qty_dipakai` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
    `qty_dikembalikan` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
    `qty_sisa_di_vendor` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
    `qty_hilang` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
    `satuan_kode` VARCHAR(30) NOT NULL,
    `catatan` VARCHAR(500) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_op_bahan_order` (`order_proses_id`),
    KEY `idx_op_bahan_material` (`material_id`),
    KEY `idx_op_bahan_receipt` (`materialmasuk_detail_id`),
    KEY `idx_op_bahan_hasil_asal` (`hasil_proses_asal_id`),
    CONSTRAINT `fk_op_bahan_order`
        FOREIGN KEY (`order_proses_id`) REFERENCES `order_proses_vendor` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_op_bahan_material`
        FOREIGN KEY (`material_id`) REFERENCES `material` (`matid`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_op_bahan_material_masuk`
        FOREIGN KEY (`materialmasuk_detail_id`) REFERENCES `detail_materialmasuk` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `chk_op_bahan_qty_nonnegative`
        CHECK (`qty_rencana` >= 0 AND `qty_dialokasikan` >= 0 AND `qty_dipakai` >= 0
            AND `qty_dikembalikan` >= 0 AND `qty_sisa_di_vendor` >= 0 AND `qty_hilang` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `order_proses_vendor_hasil` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_proses_id` BIGINT(20) UNSIGNED NOT NULL,
    `jenis_hasil` ENUM('MATERIAL', 'PRODUK') NOT NULL,
    `material_id` INT(11) DEFAULT NULL,
    `kode_produk` CHAR(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
    `gudang_hasil_id` INT(11) UNSIGNED NOT NULL,
    `qty_target` DECIMAL(18,4) DEFAULT NULL,
    `qty_baik` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
    `satuan_kode` VARCHAR(30) NOT NULL,
    `catatan` VARCHAR(500) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_op_hasil_order` (`order_proses_id`),
    KEY `idx_op_hasil_material` (`material_id`),
    KEY `idx_op_hasil_produk` (`kode_produk`),
    CONSTRAINT `fk_op_hasil_order`
        FOREIGN KEY (`order_proses_id`) REFERENCES `order_proses_vendor` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_op_hasil_material`
        FOREIGN KEY (`material_id`) REFERENCES `material` (`matid`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_op_hasil_produk`
        FOREIGN KEY (`kode_produk`) REFERENCES `barang` (`brgkode`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_op_hasil_gudang`
        FOREIGN KEY (`gudang_hasil_id`) REFERENCES `gudang` (`gdgid`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `chk_op_hasil_item`
        CHECK ((`jenis_hasil` = 'MATERIAL' AND `material_id` IS NOT NULL AND `kode_produk` IS NULL)
            OR (`jenis_hasil` = 'PRODUK' AND `material_id` IS NULL AND `kode_produk` IS NOT NULL)),
    CONSTRAINT `chk_op_hasil_qty` CHECK (`qty_baik` >= 0 AND (`qty_target` IS NULL OR `qty_target` >= 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `order_proses_vendor_bahan`
    ADD CONSTRAINT `fk_op_bahan_hasil_asal`
        FOREIGN KEY (`hasil_proses_asal_id`) REFERENCES `order_proses_vendor_hasil` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT;

CREATE TABLE IF NOT EXISTS `order_proses_vendor_scrap` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_proses_id` BIGINT(20) UNSIGNED NOT NULL,
    `bahan_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `hasil_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `material_scrap_id` INT(11) DEFAULT NULL,
    `qty_scrap` DECIMAL(18,4) NOT NULL,
    `satuan_kode` VARCHAR(30) NOT NULL,
    `jenis_scrap` ENUM('DAPAT_DIGUNAKAN', 'DAUR_ULANG', 'WASTE') NOT NULL,
    `status_disposisi` ENUM('MENUNGGU', 'KEMBALI_KE_STOK', 'STOK_SCRAP', 'DIJUAL', 'DIBUANG', 'DITAHAN_VENDOR') NOT NULL DEFAULT 'MENUNGGU',
    `gudang_tujuan_id` INT(11) UNSIGNED DEFAULT NULL,
    `tanggal_disposisi` DATETIME DEFAULT NULL,
    `catatan` VARCHAR(500) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_op_scrap_order` (`order_proses_id`),
    KEY `idx_op_scrap_bahan` (`bahan_id`),
    KEY `idx_op_scrap_hasil` (`hasil_id`),
    CONSTRAINT `fk_op_scrap_order`
        FOREIGN KEY (`order_proses_id`) REFERENCES `order_proses_vendor` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_op_scrap_bahan`
        FOREIGN KEY (`bahan_id`) REFERENCES `order_proses_vendor_bahan` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_op_scrap_hasil`
        FOREIGN KEY (`hasil_id`) REFERENCES `order_proses_vendor_hasil` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_op_scrap_material`
        FOREIGN KEY (`material_scrap_id`) REFERENCES `material` (`matid`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_op_scrap_gudang`
        FOREIGN KEY (`gudang_tujuan_id`) REFERENCES `gudang` (`gdgid`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `chk_op_scrap_sumber`
        CHECK ((`bahan_id` IS NOT NULL AND `hasil_id` IS NULL)
            OR (`bahan_id` IS NULL AND `hasil_id` IS NOT NULL)),
    CONSTRAINT `chk_op_scrap_qty` CHECK (`qty_scrap` > 0),
    CONSTRAINT `chk_op_scrap_stok`
        CHECK (`status_disposisi` <> 'STOK_SCRAP'
            OR (`material_scrap_id` IS NOT NULL AND `gudang_tujuan_id` IS NOT NULL)),
    CONSTRAINT `chk_op_scrap_return_location`
        CHECK (`status_disposisi` <> 'KEMBALI_KE_STOK' OR `gudang_tujuan_id` IS NOT NULL)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `transfer_lokasi_material` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `nomor_transfer` VARCHAR(50) NOT NULL,
    `gudang_asal_id` INT(11) UNSIGNED NOT NULL,
    `gudang_transit_id` INT(11) UNSIGNED NOT NULL,
    `gudang_tujuan_id` INT(11) UNSIGNED NOT NULL,
    `order_proses_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `status` ENUM('DRAFT', 'DIKIRIM', 'DITERIMA_SEBAGIAN', 'DITERIMA', 'BATAL') NOT NULL DEFAULT 'DRAFT',
    `tanggal_kirim` DATETIME DEFAULT NULL,
    `tanggal_terima` DATETIME DEFAULT NULL,
    `dikirim_oleh` VARCHAR(100) DEFAULT NULL,
    `diterima_oleh` VARCHAR(100) DEFAULT NULL,
    `catatan` VARCHAR(500) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_transfer_lokasi_nomor` (`nomor_transfer`),
    KEY `idx_transfer_lokasi_status` (`status`, `tanggal_kirim`),
    KEY `idx_transfer_lokasi_order` (`order_proses_id`),
    CONSTRAINT `fk_transfer_lokasi_asal`
        FOREIGN KEY (`gudang_asal_id`) REFERENCES `gudang` (`gdgid`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_transfer_lokasi_transit`
        FOREIGN KEY (`gudang_transit_id`) REFERENCES `gudang` (`gdgid`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_transfer_lokasi_tujuan`
        FOREIGN KEY (`gudang_tujuan_id`) REFERENCES `gudang` (`gdgid`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_transfer_lokasi_order`
        FOREIGN KEY (`order_proses_id`) REFERENCES `order_proses_vendor` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `chk_transfer_lokasi_berbeda`
        CHECK (`gudang_asal_id` <> `gudang_tujuan_id`
            AND `gudang_asal_id` <> `gudang_transit_id`
            AND `gudang_tujuan_id` <> `gudang_transit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `detail_transfer_lokasi_material` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `transfer_id` BIGINT(20) UNSIGNED NOT NULL,
    `material_id` INT(11) NOT NULL,
    `order_bahan_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `order_hasil_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `qty_dikirim` DECIMAL(18,4) NOT NULL,
    `qty_diterima` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
    `qty_selisih` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
    `satuan_kode` VARCHAR(30) NOT NULL,
    `catatan` VARCHAR(500) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_detail_transfer_header` (`transfer_id`),
    KEY `idx_detail_transfer_material` (`material_id`),
    CONSTRAINT `fk_detail_transfer_header`
        FOREIGN KEY (`transfer_id`) REFERENCES `transfer_lokasi_material` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_detail_transfer_material`
        FOREIGN KEY (`material_id`) REFERENCES `material` (`matid`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_detail_transfer_order_bahan`
        FOREIGN KEY (`order_bahan_id`) REFERENCES `order_proses_vendor_bahan` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_detail_transfer_order_hasil`
        FOREIGN KEY (`order_hasil_id`) REFERENCES `order_proses_vendor_hasil` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `chk_detail_transfer_sumber`
        CHECK (`order_bahan_id` IS NULL OR `order_hasil_id` IS NULL),
    CONSTRAINT `chk_detail_transfer_qty`
        CHECK (`qty_dikirim` > 0 AND `qty_diterima` >= 0 AND `qty_diterima` <= `qty_dikirim` AND `qty_selisih` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Ledger pergerakan material. stokmaterial tetap menjadi saldo ringkas yang
-- diperbarui bersama ledger dalam transaksi aplikasi (row lock saat posting).
CREATE TABLE IF NOT EXISTS `mutasi_stok_material` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `idempotency_key` VARCHAR(120) NOT NULL,
    `material_id` INT(11) NOT NULL,
    `gudang_id` INT(11) UNSIGNED NOT NULL,
    `jenis_mutasi` ENUM('PEMBELIAN_MASUK', 'TRANSFER_KELUAR', 'TRANSFER_MASUK', 'PAKAI_PROSES', 'HASIL_PROSES', 'KEMBALI_SISA', 'SCRAP_MASUK', 'SCRAP_KELUAR', 'PENYESUAIAN') NOT NULL,
    `qty_masuk` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
    `qty_keluar` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
    `satuan_kode` VARCHAR(30) NOT NULL,
    `order_proses_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `transfer_detail_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `scrap_id` BIGINT(20) UNSIGNED DEFAULT NULL,
    `sumber_tabel` VARCHAR(50) DEFAULT NULL,
    `sumber_id` BIGINT(20) DEFAULT NULL,
    `sumber_detail_id` BIGINT(20) DEFAULT NULL,
    `catatan` VARCHAR(500) DEFAULT NULL,
    `created_by` VARCHAR(100) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_mutasi_stok_idempotency` (`idempotency_key`),
    KEY `idx_mutasi_stok_lokasi_waktu` (`material_id`, `gudang_id`, `created_at`),
    KEY `idx_mutasi_stok_order` (`order_proses_id`),
    KEY `idx_mutasi_stok_transfer` (`transfer_detail_id`),
    KEY `idx_mutasi_stok_scrap` (`scrap_id`),
    CONSTRAINT `fk_mutasi_stok_material`
        FOREIGN KEY (`material_id`) REFERENCES `material` (`matid`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_mutasi_stok_gudang`
        FOREIGN KEY (`gudang_id`) REFERENCES `gudang` (`gdgid`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_mutasi_stok_order`
        FOREIGN KEY (`order_proses_id`) REFERENCES `order_proses_vendor` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_mutasi_stok_transfer_detail`
        FOREIGN KEY (`transfer_detail_id`) REFERENCES `detail_transfer_lokasi_material` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_mutasi_stok_scrap`
        FOREIGN KEY (`scrap_id`) REFERENCES `order_proses_vendor_scrap` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `chk_mutasi_stok_qty`
        CHECK ((`qty_masuk` > 0 AND `qty_keluar` = 0)
            OR (`qty_keluar` > 0 AND `qty_masuk` = 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Peningkatan presisi yang direkomendasikan untuk mendukung kg/pecahan.
-- Jalankan hanya setelah audit aplikasi, backup, dan rekonsiliasi data:
-- ALTER TABLE `stokmaterial` MODIFY `stok` DECIMAL(18,4) NOT NULL;
-- ALTER TABLE `detail_materialmasuk` MODIFY `detjml` DECIMAL(18,4) DEFAULT NULL;
-- ALTER TABLE `detail_materialkeluar` MODIFY `detjml` DECIMAL(18,4) DEFAULT NULL;
-- Pertimbangkan juga mengganti kolom total bertipe DOUBLE menjadi DECIMAL
-- setelah semua pemakaian kolom diperiksa.

-- Sebelum memasang unique key pada saldo ringkas, cek dan rapikan duplikat:
-- SELECT materialid, gudang, COUNT(*) jumlah FROM stokmaterial
-- GROUP BY materialid, gudang HAVING COUNT(*) > 1;
-- Setelah bersih, pertimbangkan:
-- ALTER TABLE `stokmaterial`
--   ADD UNIQUE KEY `uq_stokmaterial_material_gudang` (`materialid`, `gudang`);
