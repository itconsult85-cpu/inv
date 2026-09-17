<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateReturMaterialTables extends Migration
{
    public function up(): void
    {
        $this->createTableIfMissing('retur_material', [
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'nomor_retur' => ['type' => 'VARCHAR', 'constraint' => 50],
            'material_masuk_faktur' => ['type' => 'VARCHAR', 'constraint' => 50],
            'tgl_retur' => ['type' => 'DATE'],
            'idsup' => ['type' => 'INT', 'null' => true],
            'gudang' => ['type' => 'INT', 'null' => true],
            'catatan' => ['type' => 'TEXT', 'null' => true],
            'user_id' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ], ['nomor_retur', 'material_masuk_faktur', 'tgl_retur', 'idsup']);

        $this->createTableIfMissing('retur_material_detail', [
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'retur_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'material_masuk_detail_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'idmat' => ['type' => 'INT'],
            'materialid' => ['type' => 'INT'],
            'detmatkode' => ['type' => 'INT'],
            'qty_retur' => ['type' => 'DECIMAL', 'constraint' => '18,3', 'default' => 0],
            'keterangan' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
        ], ['retur_id', 'material_masuk_detail_id', 'idmat']);
    }

    public function down(): void
    {
        $this->forge->dropTable('retur_material_detail', true);
        $this->forge->dropTable('retur_material', true);
    }

    private function createTableIfMissing(string $table, array $fields, array $keys = []): void
    {
        if ($this->db->tableExists($table)) {
            return;
        }

        $this->forge->addField($fields);
        $this->forge->addPrimaryKey('id');
        foreach ($keys as $key) {
            $this->forge->addKey($key);
        }
        $this->forge->createTable($table, true);
    }
}
