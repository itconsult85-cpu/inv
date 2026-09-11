<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBhpTables extends Migration
{
    public function up(): void
    {
        $this->createTableIfMissing('stok_habis_pakai', [
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'kode' => ['type' => 'VARCHAR', 'constraint' => 100],
            'nama' => ['type' => 'VARCHAR', 'constraint' => 150],
            'satuan' => ['type' => 'VARCHAR', 'constraint' => 50],
            'stok' => ['type' => 'DECIMAL', 'constraint' => '18,3', 'default' => 0],
            'stok_minimum' => ['type' => 'DECIMAL', 'constraint' => '18,3', 'default' => 0],
            'aktif' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_by' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ], ['kode']);

        $this->createTableIfMissing('po_habis_pakai', [
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'nomor_po' => ['type' => 'VARCHAR', 'constraint' => 100],
            'tanggal_po' => ['type' => 'DATE'],
            'supplier_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'supplier_nama' => ['type' => 'VARCHAR', 'constraint' => 150],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'DIBUAT'],
            'catatan' => ['type' => 'TEXT', 'null' => true],
            'created_by' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ], ['nomor_po', 'supplier_id', 'tanggal_po']);

        $this->createTableIfMissing('po_habis_pakai_detail', [
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'po_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'stok_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'kode' => ['type' => 'VARCHAR', 'constraint' => 100],
            'nama' => ['type' => 'VARCHAR', 'constraint' => 150],
            'satuan' => ['type' => 'VARCHAR', 'constraint' => 50],
            'qty_pesan' => ['type' => 'DECIMAL', 'constraint' => '18,3', 'default' => 0],
            'qty_diterima' => ['type' => 'DECIMAL', 'constraint' => '18,3', 'default' => 0],
            'harga' => ['type' => 'DECIMAL', 'constraint' => '18,2', 'default' => 0],
            'subtotal' => ['type' => 'DECIMAL', 'constraint' => '20,2', 'default' => 0],
        ], ['po_id', 'stok_id']);

        $this->createTableIfMissing('penerimaan_po_habis_pakai', [
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'po_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'detail_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'stok_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'nomor_invoice' => ['type' => 'VARCHAR', 'constraint' => 100],
            'nomor_surat_jalan' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'qty' => ['type' => 'DECIMAL', 'constraint' => '18,3', 'default' => 0],
            'diterima_oleh' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'diterima_at' => ['type' => 'DATETIME', 'null' => true],
        ], ['po_id', 'detail_id', 'stok_id', 'nomor_invoice']);

        $this->createTableIfMissing('log_stok_habis_pakai', [
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'stok_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'permintaan_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'po_keluar_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'po_detail_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'nomor_invoice' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'nomor_surat_jalan' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'jenis' => ['type' => 'VARCHAR', 'constraint' => 20],
            'qty' => ['type' => 'DECIMAL', 'constraint' => '18,3', 'default' => 0],
            'stok_sebelum' => ['type' => 'DECIMAL', 'constraint' => '18,3', 'default' => 0],
            'stok_sesudah' => ['type' => 'DECIMAL', 'constraint' => '18,3', 'default' => 0],
            'user_id' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'catatan' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ], ['stok_id', 'permintaan_id', 'po_keluar_id', 'po_detail_id', 'created_at']);

        $this->createTableIfMissing('permintaan_stok_produksi', [
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'nomor' => ['type' => 'VARCHAR', 'constraint' => 100],
            'tanggal' => ['type' => 'DATE'],
            'peminta_id' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'DIAJUKAN'],
            'catatan' => ['type' => 'TEXT', 'null' => true],
            'approved_by' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'approved_at' => ['type' => 'DATETIME', 'null' => true],
            'approval_note' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ], ['nomor', 'tanggal', 'status']);

        $this->createTableIfMissing('permintaan_stok_produksi_detail', [
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'permintaan_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'stok_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'qty_diminta' => ['type' => 'DECIMAL', 'constraint' => '18,3', 'default' => 0],
            'qty_disetujui' => ['type' => 'DECIMAL', 'constraint' => '18,3', 'default' => 0],
            'catatan' => ['type' => 'TEXT', 'null' => true],
        ], ['permintaan_id', 'stok_id']);
    }

    public function down(): void
    {
        $this->forge->dropTable('permintaan_stok_produksi_detail', true);
        $this->forge->dropTable('permintaan_stok_produksi', true);
        $this->forge->dropTable('log_stok_habis_pakai', true);
        $this->forge->dropTable('penerimaan_po_habis_pakai', true);
        $this->forge->dropTable('po_habis_pakai_detail', true);
        $this->forge->dropTable('po_habis_pakai', true);
        $this->forge->dropTable('stok_habis_pakai', true);
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
