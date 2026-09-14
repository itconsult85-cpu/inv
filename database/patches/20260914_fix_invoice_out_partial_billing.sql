-- Fix Invoice Out partial billing.
--
-- invoice_out_detail_unique_guard sebelumnya dibuat UNIQUE pada
-- (source_no|product_code). Itu melarang satu surat jalan + produk ditagihkan
-- dalam lebih dari satu Invoice Out, padahal aplikasi menghitung dan
-- mengizinkan qty tersisa (partial billing). Dump 2026-09-14 menunjukkan
-- contoh: 40 sudah ditagihkan dan 920 masih tersisa untuk kombinasi yang sama.
--
-- Patch ini tidak mengubah atau menghapus data. Constraint UNIQUE diganti
-- dengan index biasa agar sisa qty dapat dibuatkan invoice berikutnya.
-- Aman dijalankan berulang kali.

SET @index_exists := (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'invoice_out_detail'
      AND index_name = 'invoice_out_detail_unique_guard'
);

SET @drop_sql := IF(
    @index_exists > 0,
    'ALTER TABLE `invoice_out_detail` DROP INDEX `invoice_out_detail_unique_guard`',
    'SELECT 1'
);

PREPARE stmt FROM @drop_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @lookup_index_exists := (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'invoice_out_detail'
      AND index_name = 'invoice_out_detail_source_product_idx'
);

SET @add_sql := IF(
    @lookup_index_exists = 0,
    'ALTER TABLE `invoice_out_detail` ADD KEY `invoice_out_detail_source_product_idx` (`source_no`, `product_code`)',
    'SELECT 1'
);

PREPARE stmt FROM @add_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verifikasi setelah patch:
-- SHOW INDEX FROM `invoice_out_detail`;
-- SELECT invoice_id, source_no, product_code, qty, status
-- FROM `invoice_out_detail`
-- WHERE source_no = '050504/TRE-FSCM/IX/2026'
--   AND product_code = 'KIT130-0204X';
