-- Koreksi sumber surat jalan pada detail Invoice Out 40 pcs
--
-- Kondisi yang benar:
--   PO                 : BPLN30166
--   Produk             : KIT130-0204X
--   Qty                : 40 pcs
--   Surat jalan benar  : 0505004/TRE-FSCM/IX/2026
--
-- Kondisi yang salah pada dump:
--   invoice_out_detail.id = 735
--   source_no             = 050504/TRE-FSCM/IX/2026
--
-- Surat jalan 050504 (tanpa satu angka 0) adalah milik PO BPLN31141
-- dan berisi 960 pcs. Karena itu source_no invoice 40 pcs tidak boleh
-- memakai 050504, agar sistem tidak mengurangi 40 dari qty 960.

DELIMITER $$

DROP PROCEDURE IF EXISTS fix_invoice_source_40pcs$$

CREATE PROCEDURE fix_invoice_source_40pcs()
BEGIN
    DECLARE v_detail_count INT DEFAULT 0;
    DECLARE v_shipment_count INT DEFAULT 0;
    DECLARE v_invoice_po VARCHAR(200) DEFAULT NULL;
    DECLARE v_old_source VARCHAR(200) DEFAULT NULL;
    DECLARE v_updated INT DEFAULT 0;

    SELECT COUNT(*), MAX(io.po_no), MAX(iod.source_no)
      INTO v_detail_count, v_invoice_po, v_old_source
    FROM invoice_out_detail iod
    INNER JOIN invoice_out io ON io.id = iod.invoice_id
    WHERE iod.id = 735
      AND iod.product_code = 'KIT130-0204X'
      AND iod.qty = 40
      AND io.po_no = 'BPLN30166'
      AND iod.source_no = '050504/TRE-FSCM/IX/2026'
      AND io.status = 'AKTIF';

    SELECT COUNT(*)
      INTO v_shipment_count
    FROM detail_barangkeluar
    WHERE detfaktur = '0505004/TRE-FSCM/IX/2026'
      AND detpo = 'BPLN30166'
      AND detbrgkode = 'KIT130-0204X'
      AND detjml = 40;

    IF v_detail_count <> 1 OR v_shipment_count <> 1 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Target invoice atau surat jalan 40 pcs tidak sesuai: koreksi dibatalkan.';
    END IF;

    CREATE TABLE IF NOT EXISTS invoice_out_detail_backup_fix_20260925 LIKE invoice_out_detail;
    INSERT IGNORE INTO invoice_out_detail_backup_fix_20260925
    SELECT * FROM invoice_out_detail
    WHERE id = 735;

    START TRANSACTION;

    UPDATE invoice_out_detail
       SET source_no = '0505004/TRE-FSCM/IX/2026'
     WHERE id = 735
       AND source_no = '050504/TRE-FSCM/IX/2026'
       AND product_code = 'KIT130-0204X'
       AND qty = 40;
    SET v_updated = ROW_COUNT();

    IF v_updated <> 1 THEN
        ROLLBACK;
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Detail invoice 40 pcs tidak berhasil diperbaiki: transaksi dibatalkan.';
    END IF;

    COMMIT;

    SELECT 'OK' AS status,
           'Source surat jalan invoice 40 pcs sudah diubah ke 0505004/TRE-FSCM/IX/2026' AS message;
END$$

CALL fix_invoice_source_40pcs()$$

DROP PROCEDURE IF EXISTS fix_invoice_source_40pcs$$

DELIMITER ;

-- Verifikasi setelah koreksi:
-- SELECT iod.id, io.po_no, iod.product_code, iod.source_no, iod.qty
-- FROM invoice_out_detail iod
-- INNER JOIN invoice_out io ON io.id = iod.invoice_id
-- WHERE iod.id = 735;
