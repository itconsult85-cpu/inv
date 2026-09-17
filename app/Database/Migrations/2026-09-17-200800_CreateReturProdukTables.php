<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateReturProdukTables extends Migration
{
    public function up(): void
    {
        if (!$this->db->tableExists('barangmasuk') || !$this->db->fieldExists('sumber', 'barangmasuk')) {
            $this->forge->addColumn('barangmasuk', ['sumber' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'beli', 'after' => 'po_keluar_id']]);
        }
        if (!$this->db->tableExists('retur_produk')) {
            $this->forge->addField(['id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true], 'nomor_retur' => ['type' => 'VARCHAR', 'constraint' => 50], 'barang_masuk_faktur' => ['type' => 'VARCHAR', 'constraint' => 30], 'tgl_retur' => ['type' => 'DATE'], 'idsup' => ['type' => 'INT', 'null' => true], 'gudang' => ['type' => 'INT', 'null' => true], 'catatan' => ['type' => 'TEXT', 'null' => true], 'user_id' => ['type' => 'VARCHAR', 'null' => true], 'created_at' => ['type' => 'DATETIME', 'null' => true], 'updated_at' => ['type' => 'DATETIME', 'null' => true]]);
            $this->forge->addKey('id', true); $this->forge->addUniqueKey('nomor_retur'); $this->forge->createTable('retur_produk');
        }
        if (!$this->db->tableExists('retur_produk_detail')) {
            $this->forge->addField(['id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true], 'retur_id' => ['type' => 'BIGINT', 'unsigned' => true], 'po_keluar_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true], 'barang_masuk_detail_id' => ['type' => 'BIGINT', 'unsigned' => true], 'kode_barang' => ['type' => 'VARCHAR', 'constraint' => 100], 'qty_retur' => ['type' => 'DECIMAL', 'constraint' => '18,3'], 'keterangan' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true]]);
            $this->forge->addKey('id', true); $this->forge->addKey('retur_id'); $this->forge->addKey('po_keluar_id'); $this->forge->createTable('retur_produk_detail');
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('retur_produk_detail', true); $this->forge->dropTable('retur_produk', true);
    }
}
