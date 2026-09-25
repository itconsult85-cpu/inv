-- Koreksi data upload ganda untuk PO BPLI44725
-- Surat jalan: 080520/TRE-FSCM/IX/2026
-- Produk: KIT074-0501S
--
-- Berdasarkan dump database 2026-09-24:
--   detail_po 862 = qty 33.042, detkirim 5.400
--   detail_po 863 = qty 65.000, detkirim 5.400 (duplikasi upload)
-- Karena detail_barangkeluar memakai foreign key ke detail_po.detnopo
-- (bukan ke detail_po.id), baris 863 tidak boleh dihapus selama PO ini
-- masih memiliki surat jalan. Baris tersebut dinetralisasi menjadi qty 0.
--   outstanding 1113 dan 1114 sama-sama mencatat pengiriman 5.400
--
-- Hasil yang benar:
--   qty PO      = 98.042
--   qty terkirim = 5.400
--   qty sisa     = 92.642
--
-- Jalankan pada database aktif setelah backup penuh database tersedia.
-- Script membuat salinan backup khusus sebelum mengubah/menghapus baris.

DELIMITER $$

DROP PROCEDURE IF EXISTS fix_duplicate_upload_bpli44725$$

CREATE PROCEDURE fix_duplicate_upload_bpli44725()
BEGIN
    DECLARE v_detail_count INT DEFAULT 0;
    DECLARE v_outstanding_count INT DEFAULT 0;
    DECLARE v_detail_updated INT DEFAULT 0;
    DECLARE v_detail_deleted INT DEFAULT 0;
    DECLARE v_outstanding_updated INT DEFAULT 0;
    DECLARE v_outstanding_deleted INT DEFAULT 0;

    SELECT COUNT(*) INTO v_detail_count
    FROM detail_po
    WHERE id IN (862, 863)
      AND detnopo = 'BPLI44725'
      AND detkodebrg = 'KIT074-0501S';

    SELECT COUNT(*) INTO v_outstanding_count
    FROM outstanding
    WHERE id IN (1113, 1114)
      AND nopo = 'BPLI44725'
      AND kodebrg = 'KIT074-0501S';

    IF v_detail_count <> 2 OR v_outstanding_count <> 2 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Data target tidak sesuai dump: koreksi dibatalkan.';
    END IF;

    CREATE TABLE IF NOT EXISTS detail_po_backup_fix_20260925 LIKE detail_po;
    INSERT IGNORE INTO detail_po_backup_fix_20260925
    SELECT * FROM detail_po
    WHERE id IN (862, 863)
      AND detnopo = 'BPLI44725'
      AND detkodebrg = 'KIT074-0501S';

    CREATE TABLE IF NOT EXISTS outstanding_backup_fix_20260925 LIKE outstanding;
    INSERT IGNORE INTO outstanding_backup_fix_20260925
    SELECT * FROM outstanding
    WHERE id IN (1113, 1114)
      AND nopo = 'BPLI44725'
      AND kodebrg = 'KIT074-0501S';

    START TRANSACTION;

    UPDATE detail_po
    SET detqty = 98042,
        detkirim = 5400,
        detkurang = 92642,
        detsubtotal = 1205.9166,
        detharga = 46569950,
        detkirim_awal = 0,
        detinvoice_awal = 0
    WHERE id = 862
      AND detnopo = 'BPLI44725'
      AND detkodebrg = 'KIT074-0501S'
      AND detqty = 33042
      AND detkirim = 5400
      AND detkirim_awal = 0;
    SET v_detail_updated = ROW_COUNT();

    -- Pertahankan baris 863 untuk memenuhi foreign key, tetapi nolkan semua
    -- nilai kuantitas dan nominal agar tidak lagi dihitung sebagai detail PO.
    UPDATE detail_po
    SET detqty = 0,
        detkirim = 0,
        detkurang = 0,
        detsubtotal = 0,
        detharga = 0,
        detkirim_awal = 0,
        detinvoice_awal = 0
    WHERE id = 863
      AND detnopo = 'BPLI44725'
      AND detkodebrg = 'KIT074-0501S'
      AND detqty = 65000
      AND detkirim = 5400
      AND detkirim_awal = 0;
    SET v_detail_deleted = ROW_COUNT();

    UPDATE outstanding
    SET qty = 98042,
        terkirim = 5400,
        kekurangan = 92642,
        outharga = 44004950,
        outkirim = 2565000
    WHERE id = 1113
      AND nopo = 'BPLI44725'
      AND kodebrg = 'KIT074-0501S'
      AND qty = 65000
      AND terkirim = 5400;
    SET v_outstanding_updated = ROW_COUNT();

    DELETE FROM outstanding
    WHERE id = 1114
      AND nopo = 'BPLI44725'
      AND kodebrg = 'KIT074-0501S'
      AND qty = 33042
      AND terkirim = 5400;
    SET v_outstanding_deleted = ROW_COUNT();

    IF v_detail_updated <> 1 OR v_detail_deleted <> 1
       OR v_outstanding_updated <> 1 OR v_outstanding_deleted <> 1 THEN
        ROLLBACK;
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Koreksi tidak lengkap: transaksi dibatalkan.';
    END IF;

    COMMIT;

    SELECT 'OK' AS status,
           'BPLI44725 / KIT074-0501S berhasil digabung menjadi qty 98042 dengan terkirim 5400' AS message;
END$$

CALL fix_duplicate_upload_bpli44725()$$

DROP PROCEDURE IF EXISTS fix_duplicate_upload_bpli44725$$

DELIMITER ;

-- Verifikasi setelah koreksi:
-- SELECT id, detnopo, detkodebrg, detqty, detkirim, detkurang,
--        detkirim_awal, detinvoice_awal, detsubtotal, detharga
-- FROM detail_po
-- WHERE detnopo = 'BPLI44725' AND detkodebrg = 'KIT074-0501S';
--
-- SELECT id, nopo, kodebrg, qty, terkirim, kekurangan, outharga, outkirim
-- FROM outstanding
-- WHERE nopo = 'BPLI44725' AND kodebrg = 'KIT074-0501S';

-- Catatan: invoice_out_detail yang sudah dibuat tidak dihapus oleh script ini.
-- Koreksi hanya menyatukan sumber PO dan outstanding agar histori invoice tetap aman.
