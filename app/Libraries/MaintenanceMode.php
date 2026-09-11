<?php

namespace App\Libraries;

/**
 * Nyimpen fitur mana aja yang lagi "sedang dalam perbaikan" -- dipakai pas
 * lagi benerin sesuatu di production biar user lain lihat halaman
 * maintenance yang jelas, bukan fitur yang error/setengah jadi. Per fitur
 * (feature_key dari AccessControl), bukan satu website penuh -- fitur lain
 * tetap jalan normal buat user lain selagi 1 fitur lagi dibenerin.
 */
class MaintenanceMode
{
    private const TABLE = 'feature_maintenance';

    /**
     * Beberapa sub-halaman punya tombol/kartu sendiri di UI (misalnya
     * "Reporting Margin" adalah kartu terpisah di halaman Keuangan) tapi
     * secara permission masih nempel di 1 feature_key yang sama dengan
     * induknya -- kalau cuma diandelin ke AccessControl::featureKeyForRoute()
     * (yang kerja di level fitur), Reporting bakal ke-anggap bagian dari
     * "Keuangan" dan nggak bisa dimatiin sendiri-sendiri. Didaftarin di sini
     * biar Mode Maintenance bisa lebih presisi dari permission-nya.
     */
    private static function extraTargets(): array
    {
        return [
            [
                'key' => 'order.invoice_hub.reporting',
                'label' => 'Reporting Margin',
                'section_label' => 'TRANSAKSI ORDER',
                'patterns' => ['invoicehub/reporting'],
                'after_feature_key' => 'order.invoice_hub',
            ],
        ];
    }

    /**
     * Daftar lengkap target yang bisa dimaintenance-kan -- 1 per fitur
     * AccessControl (base_patterns-nya), ditambah extraTargets() yang
     * disisipkan tepat setelah fitur induknya biar urutan di halaman admin
     * masuk akal.
     */
    public static function targets(): array
    {
        $extraByParent = [];
        foreach (self::extraTargets() as $extra) {
            $extraByParent[$extra['after_feature_key']][] = $extra;
        }

        $targets = [];
        foreach (AccessControl::sections() as $section) {
            if ($section['key'] === 'dashboard') {
                continue;
            }

            foreach ($section['features'] as $feature) {
                $targets[] = [
                    'key' => $feature['key'],
                    'label' => $feature['label'],
                    'section_label' => $section['label'],
                    'patterns' => $feature['base_patterns'],
                ];

                foreach ($extraByParent[$feature['key']] ?? [] as $extra) {
                    $targets[] = $extra;
                }
            }
        }

        return $targets;
    }

    /**
     * Cari target (fitur ATAU sub-halaman) yang cocok sama sebuah URL.
     * extraTargets() dicek duluan karena pattern-nya lebih spesifik (mis.
     * "invoicehub/reporting" harus menang ketimbang "invoicehub" punya
     * Keuangan yang juga cocok lewat wildcard).
     */
    public static function targetForRoute(string $uri): ?array
    {
        $uri = strtolower(trim($uri, '/ '));

        foreach (self::extraTargets() as $target) {
            foreach ($target['patterns'] as $pattern) {
                if (AccessControl::matchesPattern($uri, $pattern) || AccessControl::matchesPattern($uri, $pattern . '/*')) {
                    return ['key' => $target['key'], 'label' => $target['label']];
                }
            }
        }

        return AccessControl::featureKeyForRoute($uri);
    }

    public static function ensureTable(): void
    {
        $db = db_connect();
        if ($db->tableExists(self::TABLE)) {
            return;
        }

        $forge = \Config\Database::forge();
        $forge->addField([
            'feature_key' => ['type' => 'VARCHAR', 'constraint' => 100],
            'feature_label' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'pesan' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'updated_by' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $forge->addPrimaryKey('feature_key');
        $forge->createTable(self::TABLE, true);
    }

    /**
     * @return array<string, array<string, mixed>> feature_key => baris
     */
    public static function activeMap(): array
    {
        self::ensureTable();

        $map = [];
        foreach (db_connect()->table(self::TABLE)->get()->getResultArray() as $row) {
            $map[$row['feature_key']] = $row;
        }

        return $map;
    }

    public static function info(string $featureKey): ?array
    {
        self::ensureTable();

        return db_connect()->table(self::TABLE)->where('feature_key', $featureKey)->get()->getRowArray();
    }

    public static function isActive(string $featureKey): bool
    {
        return self::info($featureKey) !== null;
    }

    public static function activate(string $featureKey, string $featureLabel, ?string $pesan, string $updatedBy): void
    {
        self::ensureTable();
        $db = db_connect();
        $data = [
            'feature_key' => $featureKey,
            'feature_label' => $featureLabel,
            'pesan' => $pesan !== null && trim($pesan) !== '' ? trim($pesan) : null,
            'updated_by' => $updatedBy,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($db->table(self::TABLE)->where('feature_key', $featureKey)->countAllResults() > 0) {
            $db->table(self::TABLE)->where('feature_key', $featureKey)->update($data);
        } else {
            $db->table(self::TABLE)->insert($data);
        }
    }

    public static function deactivate(string $featureKey): void
    {
        self::ensureTable();
        db_connect()->table(self::TABLE)->where('feature_key', $featureKey)->delete();
    }
}
