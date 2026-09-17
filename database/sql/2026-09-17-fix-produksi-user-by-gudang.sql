-- Koreksi data lama tabel produksi berdasarkan gudang.
-- Mapping sesuai permintaan:
--   Cirebon  -> userid admin 3
--   Cikarang -> userid dewi
--
-- Script ini menyimpan users.id ke produksi.iduser sehingga kompatibel
-- dengan database lama yang masih memakai kolom INT.
-- Jalankan setelah backup database.

START TRANSACTION;

-- Preview data yang akan diperbarui
SELECT
    p.no_produksi,
    p.gudang,
    g.gdgnama,
    p.iduser AS iduser_sebelumnya,
    u.userid AS userid_baru,
    u.id AS iduser_baru
FROM produksi p
JOIN gudang g ON g.gdgid = p.gudang
JOIN users u ON u.userid = CASE
    WHEN LOWER(TRIM(g.gdgnama)) = 'cirebon' THEN 'admin 3'
    WHEN LOWER(TRIM(g.gdgnama)) = 'cikarang' THEN 'dewi'
END
WHERE LOWER(TRIM(g.gdgnama)) IN ('cirebon', 'cikarang');

-- Terapkan mapping user berdasarkan nama gudang
UPDATE produksi p
JOIN gudang g ON g.gdgid = p.gudang
JOIN users u ON u.userid = CASE
    WHEN LOWER(TRIM(g.gdgnama)) = 'cirebon' THEN 'admin 3'
    WHEN LOWER(TRIM(g.gdgnama)) = 'cikarang' THEN 'dewi'
END
SET p.iduser = u.id
WHERE LOWER(TRIM(g.gdgnama)) IN ('cirebon', 'cikarang');

-- Verifikasi hasil sebelum commit
SELECT
    g.gdgnama,
    u.userid,
    u.usernama,
    COUNT(*) AS jumlah_produksi
FROM produksi p
JOIN gudang g ON g.gdgid = p.gudang
LEFT JOIN users u ON u.id = CAST(p.iduser AS UNSIGNED)
WHERE LOWER(TRIM(g.gdgnama)) IN ('cirebon', 'cikarang')
GROUP BY g.gdgnama, u.userid, u.usernama
ORDER BY g.gdgnama, u.userid;

COMMIT;
