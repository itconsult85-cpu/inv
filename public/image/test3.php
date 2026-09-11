<?php

namespace App\Models;

use CodeIgniter\Model;

class NgdataModel extends Model
{
    protected $table = 'ngdata';
    protected $allowedFields = ['supplier', 'jenismaterial', 'totalberatmasuk', 'totalberatkeluar'];

    public function getData()
    {
        $db = \Config\Database::connect();

        $query = "INSERT INTO ngdata (supplier, jenismaterial, totalberatmasuk, totalberatkeluar)
                  SELECT supplier, jenismaterial, SUM(totalberatmasuk), SUM(totalberatkeluar)
                  FROM (
                      SELECT supplier, jenismaterial, totalberatmasuk, 0 AS totalberatkeluar FROM barangmasuk
                      UNION ALL
                      SELECT supplier, jenismaterial, 0 AS totalberatmasuk, totalberatkeluar FROM materialkeluar
                  ) AS tmp
                  GROUP BY supplier, jenismaterial";

        $db->query($query);

        return $this->findAll();
    }
}


<?php

namespace App\Controllers;

use App\Models\NgdataModel;

class Ngdata extends BaseController
{
    public function index()
    {
        $model = new NgdataModel();

        $data = [
            'ngdata' => $model->getData()
        ];

        return view('ngdata/index', $data);
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

//cara kedua
<?php

namespace App\Models;

use CodeIgniter\Model;

class NgdataModel extends Model
{
    protected $table = 'ngdata';
    protected $primaryKey = 'id';
    protected $allowedFields = ['supplier', 'jenismaterial', 'totalberatmasuk', 'totalberatkeluar'];


public function getNgdata()
{
    $builder = $this->db->table('ngdata');
    $builder->select('*');
    $builder->join('barangmasuk', 'ngdata.supplier = barangmasuk.supplier AND ngdata.jenismaterial = barangmasuk.jenismaterial', 'left');
    $builder->join('materialkeluar', 'ngdata.supplier = materialkeluar.supplier AND ngdata.jenismaterial = materialkeluar.jenismaterial', 'left');
    $query = $builder->get();
    return $query->getResult();
}
}

public function index()
{
    $ngdata = $this->ngdata_model->getNgdata();
    return view('ngdata/index', ['ngdata' => $ngdata]);
}

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
        <?php foreach ($ngdata as $data) : ?>
            <tr>
                <td><?= $data->supplier ?></td>
                <td><?= $data->jenismaterial ?></td>
                <td><?= $data->totalberatmasuk ?></td>
                <td><?= $data->totalberatkeluar ?></td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>

//cara ketiga

<?php

namespace App\Controllers;

use App\Models\Modelbarangmasuk;
use App\Models\ModelMaterialKeluar;
use App\Models\Modelng;

class NgdataController extends BaseController
{
    public function index()
    {
        $bmModel = new Modelbarangmasuk();
        $mkModel = new ModelMaterialKeluar();
        $ngModel = new Modelng();

        $bmData = $bmModel->findAll();
        $mkData = $mkModel->findAll();

        // loop through barangmasuk data
        foreach ($bmData as $bm) {
            $ngData = [
                'supplier' => $bm['supplier'],
                'jenismaterial' => $bm['jenismaterial'],
                'totalberatmasuk' => $bm['totalberatmasuk'],
                'totalberatkeluar' => null // set totalberatkeluar to null for now
            ];

            // check if data with same supplier and jenismaterial already exists in ngdata table
            $existingNgData = $ngModel->where('supplier', $bm['supplier'])
                ->where('jenismaterial', $bm['jenismaterial'])
                ->first();

            if ($existingNgData) {
                // if data already exists, update totalberatmasuk and totalberatkeluar
                $ngData['totalberatmasuk'] += $existingNgData['totalberatmasuk'];
                $ngData['totalberatkeluar'] = $existingNgData['totalberatkeluar'] + $mkModel->where('supplier', $bm['supplier'])
                    ->where('jenismaterial', $bm['jenismaterial'])
                    ->sum('totalberatkeluar');
                $ngModel->update($existingNgData['id'], $ngData);
            } else {
                // if data does not exist, insert new data to ngdata table
                $ngModel->insert($ngData);
            }
        }
        
        return view('ngdata/index');
    }
}

?>
