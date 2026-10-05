<?php
require dirname(__DIR__) . '/preload.php';
$db = \Config\Database::connect();
foreach (['po_keluar','detail_po_keluar','retur_material_detail','detail_materialmasuk','materialmasuk','stokmaterial'] as $table) {
    echo $table . '=' . ($db->tableExists($table) ? 'YES' : 'NO') . PHP_EOL;
    if ($db->tableExists($table)) echo implode(',', $db->getFieldNames($table)) . PHP_EOL;
}
if (!$db->tableExists('retur_material_detail')) exit;
$ng = $db->table('retur_material_detail rd')
    ->select('COALESCE(rd.po_keluar_id, dmm.po_keluar_id) AS po_keluar_id, dmm.detmatkode AS kode_item, SUM(rd.qty_retur) AS qty_ng', false)
    ->join('detail_materialmasuk dmm', 'dmm.id = rd.material_masuk_detail_id', 'inner')
    ->groupBy('COALESCE(rd.po_keluar_id, dmm.po_keluar_id), dmm.detmatkode', false)->getCompiledSelect();
$replacement = $db->table('detail_materialmasuk dmm')
    ->select('dmm.po_keluar_id, dmm.detmatkode AS kode_item, SUM(dmm.detjml) AS qty_pengganti', false)
    ->join('materialmasuk mm', 'mm.faktur = dmm.detfaktur', 'inner')
    ->where('mm.sumber', 'retur_ng')
    ->groupBy('dmm.po_keluar_id, dmm.detmatkode')->getCompiledSelect();
$stock = $db->table('stokmaterial')->select('materialid, SUM(stok) AS stok_material', false)->groupBy('materialid')->getCompiledSelect();
$builder = $db->table('po_keluar pk')
    ->select('pk.id, pk.no_po, pk.supplier_nama, pk.tgl_po, dpk.id AS detail_id, dpk.kode_item, dpk.nama_item, ng.qty_ng, COALESCE(rep.qty_pengganti, 0) AS qty_pengganti, (ng.qty_ng - COALESCE(rep.qty_pengganti, 0)) AS qty_sisa, COALESCE(sm.stok_material, 0) AS stok_material', false)
    ->join('detail_po_keluar dpk', "dpk.po_keluar_id = pk.id AND LOWER(dpk.tipe_item) = 'material'", 'inner', false)
    ->join("({$ng}) ng", 'ng.po_keluar_id = pk.id AND ng.kode_item = dpk.kode_item', 'inner', false)
    ->join("({$replacement}) rep", 'rep.po_keluar_id = pk.id AND rep.kode_item = dpk.kode_item', 'left', false)
    ->join("({$stock}) sm", 'sm.materialid = dpk.kode_item', 'left', false)
    ->where("UPPER(pk.status) = 'NG'", null, false)
    ->where('(ng.qty_ng - COALESCE(rep.qty_pengganti, 0)) > 0', null, false);
echo "SQL:\n" . $builder->getCompiledSelect(false) . PHP_EOL;
$result = $builder->get()->getResultArray();
echo "ROWS=" . count($result) . PHP_EOL;
print_r($result);
