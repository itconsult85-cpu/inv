CREATE TABLE material_summary (
    id INT(11) AUTO_INCREMENT,
    supplier VARCHAR(50) NOT NULL,
    material VARCHAR(50) NOT NULL,
    berat_barang_masuk DECIMAL(10,2) NOT NULL DEFAULT 0,
    berat_barang_keluar DECIMAL(10,2) NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
);


public function getData()
{
    $materialModel = new MaterialModel();
    $materialData = $materialModel->findAll();

    $materialSummaryModel = new MaterialSummaryModel();

    foreach ($materialData as $material) {
        $materialSummary = $materialSummaryModel->where([
            'supplier' => $material['supplier'],
            'material' => $material['material']
        ])->first();

        if (!$materialSummary) {
            $materialSummaryModel->insert([
                'supplier' => $material['supplier'],
                'material' => $material['material'],
                'berat_barang_masuk' => $material['berat_barang_masuk'],
                'berat_barang_keluar' => $material['berat_barang_keluar']
            ]);
        } else {
            $materialSummaryModel->update($materialSummary['id'], [
                'berat_barang_masuk' => $materialSummary['berat_barang_masuk'] + $material['berat_barang_masuk'],
                'berat_barang_keluar' => $materialSummary['berat_barang_keluar'] + $material['berat_barang_keluar']
            ]);
        }
    }

    $data = [
        'materialData' => $materialData
    ];

    return view('material_view', $data);
}
<!-- Pada contoh kode di atas, setelah data dari tabel material diambil, kode akan melakukan pengulangan untuk setiap data material dan melakukan pengecekan apakah data tersebut sudah ada di tabel baru atau belum.
Jika belum, maka data akan disimpan dengan nilai berat barang masuk dan berat barang keluar sesuai dengan data tersebut.
Namun, jika data sudah ada, maka nilai berat barang masuk dan berat barang keluar akan ditambahkan dengan nilai yang ada di tabel baru. -->

public function updateData($id)
{
    $materialSummaryModel = new MaterialSummaryModel();

    $data = [
        'berat_barang_masuk' => $this->request->getPost('berat_barang_masuk'),
        'berat_barang_keluar' => $this->request->getPost('berat_barang_keluar')
    ];

    $materialSummaryModel->update($id, $data);

    return redirect()->to('/material');
}

<?php

namespace App\Models;

use CodeIgniter\Model;

class MaterialSummaryModel extends Model
{
    protected $table = 'material_summary';
    protected $allowedFields = ['supplier', 'material', 'berat_barang_masuk', 'berat_barang_keluar'];

    public function getMaterialSummaries()
    {
        return $this->findAll();
    }

    public function getMaterialSummary($id)
    {
        return $this->where(['id' => $id])->first();
    }

    public function insertMaterialSummary($data)
    {
        return $this->insert($data);
    }

    public function updateMaterialSummary($id, $data)
    {
        return $this->update($id, $data);
    }

    public function deleteMaterialSummary($id)
    {
        return $this->delete($id);
    }
}

// Pada contoh kode di atas, model MaterialSummaryModel akan melakukan operasi CRUD pada tabel material_summary.
// Model tersebut memiliki fungsi untuk mendapatkan semua data pada tabel material_summary, mendapatkan data dengan id tertentu, menyimpan data baru, mengupdate data dengan id tertentu, dan menghapus data dengan id tertentu.
// Anda dapat menyesuaikan kode tersebut dengan kebutuhan Anda.


<form action="<?php echo base_url('/material/update/'.$materialSummary['id']); ?>" method="post">
    <div class="form-group">
        <label for="berat_barang_masuk">Berat Barang Masuk:</label>
        <input type="text" class="form-control" id="berat_barang_masuk" name="berat_barang_masuk" value="<?php echo $materialSummary['berat_barang_masuk']; ?>">
    </div>
    <div class="form-group">
        <label for="berat_barang_keluar">Berat Barang Keluar:</label>
        <input type="text" class="form-control" id="berat_barang_keluar" name="berat_barang_keluar" value="<?php echo $materialSummary['berat_barang_keluar']; ?>">
    </div>
    <button type="submit" class="btn btn-primary">Update</button>
</form>


public function deleteData($id)
{
    $materialSummaryModel = new MaterialSummaryModel();
    $materialSummaryModel->delete($id);

    return redirect()->to('/material');
}


<a href="<?php echo base_url('/material/delete/'.$materialSummary['id']); ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this data?')">Delete</a>

<table class="table">
    <thead>
        <tr>
            <th>Supplier</th>
            <th>Material</th>
            <th>Berat Barang Masuk</th>
            <th>Berat Barang Keluar</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($materialSummaries as $materialSummary) { ?>
            <tr>
                <td><?php echo $materialSummary['supplier']; ?></td>
                <td><?php echo $materialSummary['material']; ?></td>
                <td><?php echo $materialSummary['berat_barang_masuk']; ?></td>
                <td><?php echo $materialSummary['berat_barang_keluar']; ?></td>
                <td>
                    <a href="<?php echo base_url('/material/edit/'.$materialSummary['id']); ?>" class="btn btn-warning">Edit</a>
                    <a href="<?php echo base_url('/material/delete/'.$materialSummary['id']); ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this data?')">Delete</a>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>
