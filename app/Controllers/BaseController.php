<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = ['form'];

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        // $this->helpers = ['form', 'url'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        // $this->session = service('session');
    }

    /**
     * Memeriksa apakah sebuah master masih dipakai tabel lain.
     * Tabel/kolom lama yang tidak tersedia akan dilewati agar tetap kompatibel.
     */
    protected function periksaRelasiMaster(array $sumber, $nilai): array
    {
        $db = \Config\Database::connect();
        $pemakaian = [];

        foreach ($sumber as $item) {
            if (
                !$db->tableExists($item['table']) ||
                !$db->fieldExists($item['column'], $item['table'])
            ) {
                continue;
            }

            $builder = $db->table($item['table']);

            if (($item['csv'] ?? false) === true) {
                $builder->where(
                    'FIND_IN_SET(' . $db->escape((string) $nilai) . ', ' . $item['column'] . ') > 0',
                    null,
                    false
                );
            } else {
                $builder->where($item['column'], $nilai);
            }
            foreach ($item['where'] ?? [] as $kolom => $value) {
                $builder->where($kolom, $value);
            }

            $jumlah = $builder->countAllResults();
            if ($jumlah > 0) {
                $pemakaian[] = [
                    'label' => $item['label'] ?? $item['table'],
                    'jumlah' => $jumlah,
                ];
            }
        }

        return $pemakaian;
    }

    protected function pesanRelasiMaster(string $nama, array $pemakaian): string
    {
        $namaAman = htmlspecialchars($nama, ENT_QUOTES, 'UTF-8');
        $items = '';

        foreach ($pemakaian as $item) {
            $label = htmlspecialchars((string) $item['label'], ENT_QUOTES, 'UTF-8');
            $jumlah = (int) ($item['jumlah'] ?? 0);
            $items .= "<li><strong>{$label}</strong> ({$jumlah} data)</li>";
        }

        return "Data <strong>{$namaAman}</strong> tidak dapat diubah atau dihapus karena masih digunakan pada:"
            . "<ul style=\"text-align:left; margin:12px auto 0; width:fit-content;\">{$items}</ul>";
    }

    /**
     * brgmat dapat berisi satu material ("4") atau beberapa material ("4,15").
     * Beberapa fitur lama masih membutuhkan satu material utama, jadi ambil ID pertama.
     */
    protected function materialUtamaProduk($brgmat): int
    {
        $ids = array_values(array_filter(array_map(
            'intval',
            explode(',', (string) $brgmat)
        )));

        return (int) ($ids[0] ?? 0);
    }

    protected function produkTanpaBerat(string $kodeProduk): bool
    {
        if ($kodeProduk === '') {
            return false;
        }

        $db = \Config\Database::connect();
        if (!$db->tableExists('barang') || !$db->fieldExists('tanpa_berat', 'barang')) {
            return false;
        }

        $row = $db->table('barang')
            ->select('tanpa_berat')
            ->where('brgkode', $kodeProduk)
            ->get()
            ->getRowArray();

        return (int) ($row['tanpa_berat'] ?? 0) === 1;
    }

    protected function ensurePoKeluarJenisPoColumn(): void
    {
        $db = \Config\Database::connect();
        if ($db->tableExists('po_keluar') && !$db->fieldExists('jenis_po', 'po_keluar')) {
            \Config\Database::forge()->addColumn('po_keluar', [
                'jenis_po' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'default' => 'material',
                    'after' => 'jenis_transaksi',
                ],
            ]);

            $db->query("
                UPDATE po_keluar pk
                SET jenis_po = COALESCE((
                    SELECT dpk.tipe_item
                    FROM detail_po_keluar dpk
                    WHERE dpk.po_keluar_id = pk.id
                    ORDER BY FIELD(dpk.tipe_item, 'jasa', 'produk', 'material')
                    LIMIT 1
                ), 'material')
            ");
        }
    }

    protected function ensurePoKeluarPrintColumns(): void
    {
        $db = \Config\Database::connect();
        $forge = \Config\Database::forge();

        if ($db->tableExists('po_keluar')) {
            $columns = [
                'print_spec_label' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'approved_by'],
                'material_column_enabled' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'after' => 'print_spec_label'],
                'print_notes' => ['type' => 'TEXT', 'null' => true, 'after' => 'material_column_enabled'],
                'discount_enabled' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'print_notes'],
                'discount_amount' => ['type' => 'DECIMAL', 'constraint' => '18,2', 'default' => 0, 'after' => 'discount_enabled'],
                'ppn_included' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'discount_amount'],
                'pph23_enabled' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'ppn_included'],
                'pph23_amount' => ['type' => 'DECIMAL', 'constraint' => '18,2', 'default' => 0, 'after' => 'pph23_enabled'],
            ];

            foreach ($columns as $name => $definition) {
                if (!$db->fieldExists($name, 'po_keluar')) {
                    $forge->addColumn('po_keluar', [$name => $definition]);
                }
            }
        }

        if ($db->tableExists('detail_po_keluar') && !$db->fieldExists('print_spec', 'detail_po_keluar')) {
            $forge->addColumn('detail_po_keluar', [
                'print_spec' => [
                    'type' => 'VARCHAR',
                    'constraint' => 150,
                    'null' => true,
                    'after' => 'nama_item',
                ],
            ]);
        }
    }

    protected function ensureSumberMaterialColumns(): void
    {
        $db = \Config\Database::connect();
        $forge = \Config\Database::forge();

        if ($db->tableExists('barang') && !$db->fieldExists('sumber_material', 'barang')) {
            $afterBarangColumn = $db->fieldExists('tanpa_berat', 'barang') ? 'tanpa_berat' : 'brgmat';
            $forge->addColumn('barang', [
                'sumber_material' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'default' => 'tre',
                    'after' => $afterBarangColumn,
                ],
            ]);

            if ($db->fieldExists('tanpa_berat', 'barang')) {
                $db->query("UPDATE barang SET sumber_material = 'vendor' WHERE COALESCE(tanpa_berat, 0) = 1");
            }
        }

        if ($db->tableExists('po_keluar') && !$db->fieldExists('sumber_material_produksi', 'po_keluar')) {
            $forge->addColumn('po_keluar', [
                'sumber_material_produksi' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'default' => 'tre',
                    'after' => 'jenis_po',
                ],
            ]);
        }
    }
}
