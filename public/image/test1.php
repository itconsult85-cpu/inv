<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class ContohController extends Controller
{
    public function index()
    {
        $db = \Config\Database::connect();
        $query = $db->table('nama_tabel')
                     ->select('supplier, material, SUM(berat_barang_masuk) AS total_berat_masuk, SUM(berat_barang_keluar) AS total_berat_keluar')
                     ->groupBy('supplier, material')
                     ->get();
        $data = $query->getResult();
        
        // Mengirim data ke tampilan
        return view('contoh_view', ['data' => $data]);
    }
}

Pada contoh di atas, kita menambahkan kode query pada method index() di dalam controller ContohController.
Setelah itu, kita mengirim data ke tampilan contoh_view dengan menggunakan method view().
Data tersebut akan diakses pada tampilan dengan menggunakan variabel $data.

<?php

namespace App\Models;

use CodeIgniter\Model;

class ContohModel extends Model
{
    protected $table = 'nama_tabel';

    public function getBeratBarang()
    {
        $db = \Config\Database::connect();
        $query = $db->table($this->table)
                     ->select('supplier, material, SUM(berat_barang_masuk) AS total_berat_masuk, SUM(berat_barang_keluar) AS total_berat_keluar')
                     ->groupBy('supplier, material')
                     ->get();
        $data = $query->getResult();
        
        // Proses lainnya
        // ...
        
        // Mengembalikan data
        return $data;
    }
}

<?php

namespace App\Models;

use CodeIgniter\Database\Database;
use CodeIgniter\Model;

class NamaModel extends Model
{
    protected $table = 'nama_tabel_tujuan';
    protected $allowedFields = ['supplier', 'material', 'total_berat_masuk', 'total_berat_keluar'];

    public function updateTable()
    {
        $db = Database::connect();
        $builder = $db->table('nama_tabel');
        $builder->select('supplier, material, SUM(berat_barang_masuk) AS total_berat_masuk, SUM(berat_barang_keluar) AS total_berat_keluar');
        $builder->groupBy('supplier, material');
        $query = $builder->get();

        $data = $query->getResultArray();

        $this->truncate();

        if (!empty($data)) {
            $this->insertBatch($data);
        }
    }
}

<?php

namespace App\Models;

use CodeIgniter\Model;

class NgdataModel extends Model
{
    protected $table = 'ngdata';
    protected $allowedFields = ['totalberatmasuk', 'totalberatkeluar'];

    public function getNgdata()
    {
        $builder = $this->db->table('barangmasuk');
        $builder->join('materialkeluar', 'barangmasuk.supplier = materialkeluar.supplier and barangmasuk.jenismaterial = materialkeluar.jenismaterial', 'left');
        return $builder->get()->getResult();
    }

    public function updateNgdata($id, $data)
    {
        $this->where('id', $id);
        $this->set($data);
        return $this->update();
    }
}

<?php

namespace App\Controllers;

use App\Models\NgdataModel;
use CodeIgniter\Controller;

class NgdataController extends Controller
{
    public function index()
    {
        $ngdataModel = new NgdataModel();

        // Ambil data dari tabel ngdata
        $ngdata = $ngdataModel->select('supplier, jenismaterial, totalberatmasuk, totalberatkeluar')
            ->findAll();

        // Tampilkan data ke view
        return view('ngdata/index', compact('ngdata'));
    }

    public function update($id)
    {
        $ngdataModel = new NgdataModel();

        // Cek apakah method request adalah PUT
        if ($this->request->getMethod() === 'put') {

            // Ambil data yang diinput dari form
            $totalberatkeluar = $this->request->getVar('totalberatkeluar');

            // Validasi input data
            if (!is_numeric($totalberatkeluar)) {
                return redirect()->back()->with('error', 'Total Berat Keluar harus berupa angka!');
            }

            // Update data pada tabel ngdata
            $ngdataModel->update($id, [
                'totalberatkeluar' => $totalberatkeluar
            ]);

            return redirect()->back()->with('success', 'Data berhasil diupdate');
        }
    }
}

<!-- resources/views/ngdata/index.php -->
<h1>Daftar Data</h1>

<table>
    <thead>
        <tr>
            <th>Supplier</th>
            <th>Jenis Material</th>
            <th>Total Berat Masuk</th>
            <th>Total Berat Keluar</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($ngdata as $data): ?>
            <tr>
                <td><?= $data['supplier'] ?></td>
                <td><?= $data['jenismaterial'] ?></td>
                <td><?= $data['totalberatmasuk'] ?></td>
                <td><?= $data['totalberatkeluar'] ?></td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>

<!-- resources/views/ngdata/index.php -->
<h1>Daftar Data</h1>

<table>
    <thead>
        <tr>
            <th>Supplier</th>
            <th>Jenis Material</th>
            <th>Total Berat Masuk</th>
            <th>Total Berat Keluar</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($ngdata as $data): ?>
            <tr>
                <td><?= $data['supplier'] ?></td>
                <td><?= $data['jenismaterial'] ?></td>
                <td><?= $data['totalberatmasuk'] ?></td>
                <td>
                    <form action="<?= base_url('/ngdata/update/' . $data['id']) ?>" method="post">
                        <input type="hidden" name="_method" value="PUT">
                        <input type="number" name="totalberatkeluar" value="<?= $data['totalberatkeluar'] ?>">
                        <button type="submit">Update</button>
                    </form>
                </td>
                <td>
                    <!-- Tambahkan tombol delete jika diperlukan -->
                </td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>



///

SELECT supplier, jenismaterial, totalberatmasuk, NULL AS totalberatkeluar
FROM barangmasuk
UNION
SELECT supplier, jenismaterial, NULL AS totalberatmasuk, totalberatkeluar
FROM materialkeluar

$this->db->select('supplier, jenismaterial, totalberatmasuk, NULL AS totalberatkeluar')
->from('barangmasuk')
->union()
->select('supplier, jenismaterial, NULL AS totalberatmasuk, totalberatkeluar')
->from('materialkeluar')
->get();

$query = $db->table('barangmasuk')
->join('materialkeluar', 'barangmasuk.supplier = materialkeluar.supplier AND barangmasuk.jenismaterial = materialkeluar.jenismaterial')
->select('barangmasuk.supplier, barangmasuk.jenismaterial, barangmasuk.totalberatmasuk, materialkeluar.totalberatkeluar')
->get();

$data = $query->getResult();