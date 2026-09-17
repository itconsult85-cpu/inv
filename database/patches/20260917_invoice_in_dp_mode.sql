-- Menyimpan pilihan cara menghitung DP pada Invoice In.
-- Nilai yang digunakan: percent atau amount.
-- Aman dijalankan berulang kali.
ALTER TABLE `invoice_in`
    ADD COLUMN IF NOT EXISTS `dp_mode` VARCHAR(10) NOT NULL DEFAULT 'percent' AFTER `dp_enabled`;
