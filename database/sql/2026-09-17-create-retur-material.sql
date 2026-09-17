-- Retur Material NG ke Supplier/Vendor
-- Jalankan pada database aplikasi inv.
-- Script ini idempotent: aman dijalankan ulang karena memakai CREATE TABLE IF NOT EXISTS.

CREATE TABLE IF NOT EXISTS `retur_material` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nomor_retur` VARCHAR(50) NOT NULL,
  `material_masuk_faktur` VARCHAR(50) NOT NULL,
  `tgl_retur` DATE NOT NULL,
  `idsup` INT NULL,
  `gudang` INT NULL,
  `catatan` TEXT NULL,
  `user_id` VARCHAR(100) NULL,
  `created_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `retur_material_nomor_retur` (`nomor_retur`),
  KEY `retur_material_material_masuk_faktur` (`material_masuk_faktur`),
  KEY `retur_material_tgl_retur` (`tgl_retur`),
  KEY `retur_material_idsup` (`idsup`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `retur_material_detail` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `retur_id` BIGINT UNSIGNED NOT NULL,
  `material_masuk_detail_id` BIGINT UNSIGNED NOT NULL,
  `idmat` INT NOT NULL,
  `materialid` INT NOT NULL,
  `detmatkode` INT NOT NULL,
  `qty_retur` DECIMAL(18,3) NOT NULL DEFAULT 0,
  `keterangan` VARCHAR(255) NULL,
  PRIMARY KEY (`id`),
  KEY `retur_material_detail_retur_id` (`retur_id`),
  KEY `retur_material_detail_masuk_id` (`material_masuk_detail_id`),
  KEY `retur_material_detail_idmat` (`idmat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
