<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPenerimaanIdToLogStokHabisPakai extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('log_stok_habis_pakai') && !in_array('penerimaan_id', $this->db->getFieldNames('log_stok_habis_pakai'), true)) {
            $this->forge->addColumn('log_stok_habis_pakai', ['penerimaan_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true, 'after' => 'stok_id']]);
            $this->db->query('ALTER TABLE log_stok_habis_pakai ADD INDEX idx_log_stok_penerimaan (penerimaan_id)');
        }
    }

    public function down(): void
    {
        if ($this->db->tableExists('log_stok_habis_pakai') && in_array('penerimaan_id', $this->db->getFieldNames('log_stok_habis_pakai'), true)) {
            $this->forge->dropColumn('log_stok_habis_pakai', 'penerimaan_id');
        }
    }
}
