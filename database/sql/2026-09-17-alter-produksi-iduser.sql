-- Perbaikan pemetaan user pada tabel produksi.
-- iduser harus menyimpan userid teks dari session, misalnya: admin 3.
-- Jalankan sekali pada database aplikasi inv.

ALTER TABLE `produksi`
  MODIFY COLUMN `iduser` VARCHAR(100) NULL;
