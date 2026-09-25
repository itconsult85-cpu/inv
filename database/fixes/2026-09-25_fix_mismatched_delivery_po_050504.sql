-- Koreksi ketidaksesuaian PO pada header surat jalan
-- Surat jalan : 050504/TRE-FSCM/IX/2026
-- PO yang benar berdasarkan detail_barangkeluar: BPLN31141
--
-- Kondisi dump:
--   barangkeluar.faktur = 050504/TRE-FSCM/IX/2026, detpo = BPLN30166
--   detail_barangkeluar.detfaktur = 050504/TRE-FSCM/IX/2026, detpo = BPLN31141
--
-- Dampak:
--   Halaman progress memakai barangkeluar.detpo sehingga surat jalan tidak
--   muncul pada PO BPLN31141.
--   Invoice Out memakai detail_barangkeluar sehingga qty 960 terbaca, lalu
--   dikurangi invoice aktif 40 dan menampilkan sisa 920.
--
-- Script ini hanya memperbaiki header barangkeluar. Histori invoice dan detail
-- pengiriman tidak dihapus atau diubah.

DELIMITER $$

DROP PROCEDURE IF EXISTS fix_mismatched_delivery_po_050504$$

CREATE PROCEDURE fix_mismatched_delivery_po_050504()
BEGIN
    DECLARE v_header_count INT DEFAULT 0;
    DECLARE v_detail_count INT DEFAULT 0;
    DECLARE v_detail_po_count INT DEFAULT 0;
    DECLARE v_old_po VARCHAR(200) DEFAULT NULL;
    DECLARE v_updated INT DEFAULT 0;

    SELECT COUNT(*), MAX(detpo)
      INTO v_header_count, v_old_po
    FROM barangkeluar
    WHERE faktur = '050504/TRE-FSCM/IX/2026';

    SELECT COUNT(*), COUNT(DISTINCT detpo)
      INTO v_detail_count, v_detail_po_count
    FROM detail_barangkeluar
    WHERE detfaktur = '050504/TRE-FSCM/IX/2026'
      AND detpo = 'BPLN31141';

    IF v_header_count <> 1 OR v_detail_count = 0 OR v_detail_po_count <> 1
       OR v_old_po <> 'BPLN30166' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Target surat jalan tidak sesuai: koreksi dibatalkan.';
    END IF;

    CREATE TABLE IF NOT EXISTS barangkeluar_backup_fix_20260925 LIKE barangkeluar;
    INSERT IGNORE INTO barangkeluar_backup_fix_20260925
    SELECT * FROM barangkeluar
    WHERE faktur = '050504/TRE-FSCM/IX/2026';

    START TRANSACTION;

    UPDATE barangkeluar
       SET detpo = 'BPLN31141'
     WHERE faktur = '050504/TRE-FSCM/IX/2026'
       AND detpo = 'BPLN30166';
    SET v_updated = ROW_COUNT();

    IF v_updated <> 1 THEN
        ROLLBACK;
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Header surat jalan tidak berhasil diperbaiki: transaksi dibatalkan.';
    END IF;

    COMMIT;

    SELECT 'OK' AS status,
           'Header surat jalan 050504/TRE-FSCM/IX/2026 sudah disamakan ke PO BPLN31141' AS message;
END$$

CALL fix_mismatched_delivery_po_050504()$$

DROP PROCEDURE IF EXISTS fix_mismatched_delivery_po_050504$$

DELIMITER ;

-- Verifikasi setelah koreksi:
-- SELECT faktur, detpo, tglfaktur, qtykeluar
-- FROM barangkeluar
-- WHERE faktur = '050504/TRE-FSCM/IX/2026';
--
-- SELECT detfaktur, detpo, detbrgkode, detjml
-- FROM detail_barangkeluar
-- WHERE detfaktur = '050504/TRE-FSCM/IX/2026';
