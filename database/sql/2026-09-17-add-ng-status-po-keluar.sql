-- Tambahan fitur status NG dan penerimaan pengganti NG pada PO Keluar.
-- Jalankan sekali setelah backup database.

ALTER TABLE `detail_po_keluar`
  ADD COLUMN `status` VARCHAR(20) NOT NULL DEFAULT 'NORMAL' AFTER `qty_masuk`;

ALTER TABLE `retur_material_detail`
  ADD COLUMN `po_keluar_id` BIGINT UNSIGNED NULL AFTER `retur_id`;

-- Hubungkan retur yang sudah ada ke PO Keluar asal melalui detail Material Masuk.
UPDATE retur_material_detail rd
JOIN detail_materialmasuk dmm ON dmm.id = rd.material_masuk_detail_id
SET rd.po_keluar_id = dmm.po_keluar_id
WHERE rd.po_keluar_id IS NULL
  AND dmm.po_keluar_id IS NOT NULL;

-- Tandai PO dan item PO yang sudah memiliki retur NG.
UPDATE po_keluar pk
JOIN retur_material_detail rd ON rd.po_keluar_id = pk.id
SET pk.status = 'NG';

UPDATE detail_po_keluar dpk
JOIN retur_material_detail rd
  ON rd.po_keluar_id = dpk.po_keluar_id
 AND dpk.tipe_item = 'material'
JOIN detail_materialmasuk dmm
  ON dmm.id = rd.material_masuk_detail_id
 AND dmm.detmatkode = dpk.kode_item
SET dpk.status = 'NG';
