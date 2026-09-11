-- Material alternatif produk: detail berat terpakai, wise, dan berat produk jadi per material.
-- Jalankan setelah dump inventory utama.
-- Aman dijalankan pada database yang sudah memiliki kolom-kolom ini karena memakai IF NOT EXISTS.

ALTER TABLE `berat_material`
    ADD COLUMN IF NOT EXISTS `berat_produk_jadi` DECIMAL(15,4) NULL AFTER `berat`,
    ADD COLUMN IF NOT EXISTS `wise` DECIMAL(10,4) NULL AFTER `berat_produk_jadi`;

-- Migrasi data lama: berat produk jadi dan wise lama disalin ke detail material utama.
-- Data detail material alternatif yang sudah ada tidak ditimpa.
UPDATE `berat_material` bm
LEFT JOIN `berat` b
    ON b.`kodeprd` = bm.`kodeprd`
LEFT JOIN `barang` p
    ON p.`brgkode` = bm.`kodeprd`
SET
    bm.`berat_produk_jadi` = COALESCE(bm.`berat_produk_jadi`, b.`berat`),
    bm.`wise` = COALESCE(bm.`wise`, CASE
        WHEN CAST(SUBSTRING_INDEX(COALESCE(p.`brgmat`, ''), ',', 1) AS UNSIGNED) = bm.`matid`
        THEN p.`wise`
        ELSE NULL
    END)
WHERE bm.`berat_produk_jadi` IS NULL OR bm.`wise` IS NULL;

-- Verifikasi contoh:
-- SELECT kodeprd, matid, berat, wise, berat_produk_jadi
-- FROM berat_material ORDER BY kodeprd, matid;
