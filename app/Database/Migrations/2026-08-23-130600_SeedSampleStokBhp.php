<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SeedSampleStokBhp extends Migration
{
    public function up(): void
    {
        if (!$this->db->tableExists('stok_habis_pakai')) {
            return;
        }

        $rows = [
            ['kode' => 'BHP-GPA-001', 'nama' => 'Grinding Pisau Aktif/Pasif', 'qty' => 26],
            ['kode' => 'BHP-SGM-002', 'nama' => 'Shaft Gear (Bubut + Milling)', 'qty' => 8],
            ['kode' => 'BHP-BSB-003', 'nama' => 'Bushing + Spie (Bubut)', 'qty' => 4],
            ['kode' => 'BHP-PHMG-004', 'nama' => 'Pahat Holder 13X13.5 (Milling + Grinding)', 'qty' => 4],
            ['kode' => 'BHP-ST90135-005', 'nama' => 'Seal Teflon Dia 90 + Dia 135', 'qty' => 4],
            ['kode' => 'BHP-SM-006', 'nama' => 'Shaft Mandril (Bubut + Milling)', 'qty' => 2],
            ['kode' => 'BHP-USML-007', 'nama' => 'Unit Stehis (Milling + Bubut + Las)', 'qty' => 2],
            ['kode' => 'BHP-RAM-008', 'nama' => 'Repair As-Mandril', 'qty' => 2],
            ['kode' => 'BHP-PSG-009', 'nama' => 'Pisau Pasif/Aktif (Surface Grinding)', 'qty' => 54],
            ['kode' => 'BHP-RBP-010', 'nama' => 'Repair Baut Patah', 'qty' => 1],
            ['kode' => 'BHP-DPG-011', 'nama' => 'Dies Pervo (Grinding)', 'qty' => 3],
            ['kode' => 'BHP-GP-012', 'nama' => 'Grinding Pisau', 'qty' => 13],
            ['kode' => 'BHP-PH-013', 'nama' => 'Pahat', 'qty' => 1],
        ];

        // Saldo awal menggabungkan dua invoice pada gambar; item berulang dijumlahkan.
        $now = '2026-08-23 00:00:00';
        $builder = $this->db->table('stok_habis_pakai');

        foreach ($rows as $row) {
            if ($builder->where('kode', $row['kode'])->countAllResults() > 0) {
                continue;
            }

            $builder->insert([
                'kode' => $row['kode'],
                'nama' => $row['nama'],
                'satuan' => 'Pcs',
                'stok' => $row['qty'],
                'stok_minimum' => 0,
                'aktif' => 1,
                'created_by' => 'sample-invoice',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        if (!$this->db->tableExists('stok_habis_pakai')) {
            return;
        }

        $this->db->table('stok_habis_pakai')
            ->where('created_by', 'sample-invoice')
            ->delete();
    }
}
