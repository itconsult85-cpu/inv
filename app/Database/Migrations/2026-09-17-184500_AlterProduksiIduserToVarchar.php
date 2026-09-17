<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterProduksiIduserToVarchar extends Migration
{
    public function up(): void
    {
        if (!$this->db->tableExists('produksi') || !$this->db->fieldExists('iduser', 'produksi')) {
            return;
        }

        $this->forge->modifyColumn('produksi', [
            'iduser' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
        ]);
    }

    public function down(): void
    {
        if (!$this->db->tableExists('produksi') || !$this->db->fieldExists('iduser', 'produksi')) {
            return;
        }

        // Jangan mengubah balik ke INT secara otomatis karena data baru dapat
        // berisi userid berbentuk teks, misalnya "admin 3".
    }
}
