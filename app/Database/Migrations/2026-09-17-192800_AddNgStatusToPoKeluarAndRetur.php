<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNgStatusToPoKeluarAndRetur extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('detail_po_keluar') && !$this->db->fieldExists('status', 'detail_po_keluar')) {
            $this->forge->addColumn('detail_po_keluar', [
                'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'NORMAL', 'after' => 'qty_masuk'],
            ]);
        }

        if ($this->db->tableExists('retur_material_detail') && !$this->db->fieldExists('po_keluar_id', 'retur_material_detail')) {
            $this->forge->addColumn('retur_material_detail', [
                'po_keluar_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true, 'after' => 'retur_id'],
            ]);
        }
    }

    public function down(): void
    {
        if ($this->db->tableExists('retur_material_detail') && $this->db->fieldExists('po_keluar_id', 'retur_material_detail')) {
            $this->forge->dropColumn('retur_material_detail', 'po_keluar_id');
        }
        if ($this->db->tableExists('detail_po_keluar') && $this->db->fieldExists('status', 'detail_po_keluar')) {
            $this->forge->dropColumn('detail_po_keluar', 'status');
        }
    }
}
