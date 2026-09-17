SET @add_sumber_sql = (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `barangmasuk` ADD COLUMN `sumber` VARCHAR(30) NOT NULL DEFAULT ''beli'' AFTER `po_keluar_id`',
    'SELECT 1'
  )
  FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'barangmasuk'
    AND column_name = 'sumber'
);
PREPARE add_sumber_stmt FROM @add_sumber_sql;
EXECUTE add_sumber_stmt;
DEALLOCATE PREPARE add_sumber_stmt;

CREATE TABLE IF NOT EXISTS `retur_produk` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nomor_retur` VARCHAR(50) NOT NULL,
  `barang_masuk_faktur` VARCHAR(30) NOT NULL,
  `tgl_retur` DATE NOT NULL,
  `idsup` INT NULL,
  `gudang` INT NULL,
  `catatan` TEXT NULL,
  `user_id` VARCHAR(100) NULL,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`), UNIQUE KEY `uq_retur_produk_nomor` (`nomor_retur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `retur_produk_detail` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `retur_id` BIGINT UNSIGNED NOT NULL,
  `po_keluar_id` BIGINT UNSIGNED NULL,
  `barang_masuk_detail_id` BIGINT UNSIGNED NOT NULL,
  `kode_barang` VARCHAR(100) NOT NULL,
  `qty_retur` DECIMAL(18,3) NOT NULL,
  `keterangan` VARCHAR(255) NULL,
  PRIMARY KEY (`id`), KEY `idx_retur_produk_po` (`po_keluar_id`), KEY `idx_retur_produk_detail` (`barang_masuk_detail_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
