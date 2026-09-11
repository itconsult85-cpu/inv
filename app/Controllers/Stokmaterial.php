<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ModelStokMaterial;
use Config\Database;
use Hermawan\DataTables\DataTable;

class Stokmaterial extends BaseController
{
    protected $db;
    protected $modelStokMaterial;

    public function __construct()
    {
        $this->db = db_connect();
        $this->modelStokMaterial = new ModelStokMaterial();
    }

    public function index()
    {
        $materials = $this->modelStokMaterial->findAll();
        return view('materialstok/viewdatastokmaterial', ['materials' => $materials]);
    }

    public function data()
    {
        if ($this->request->isAJAX()) {

            $db = Database::connect();
            $builder = $db->table('stokmaterial')
                ->select('stokmaterial.kodematerial, stokmaterial.materialkatid, stokmaterial.namamaterial, stokmaterial.materialsatid, stokmaterial.gudang')
                ->select('SUM(CASE WHEN stokmaterial.gudang = 1 THEN stokmaterial.stok ELSE 0 END) AS totmascik')
                ->select('SUM(CASE WHEN stokmaterial.gudang = 2 THEN stokmaterial.stok ELSE 0 END) AS totmascir')
                ->select('SUM(stokmaterial.stok) AS totalstok')
                ->join('gudang', 'gudang.gdgid = stokmaterial.gudang', 'left')
                ->join('material', 'material.matid = stokmaterial.materialkatid', 'left')
                ->groupBy('stokmaterial.kodematerial, stokmaterial.materialkatid');

            return DataTable::of($builder)
                ->addNumbering('nomor')
                ->setSearchableColumns(['kodematerial'])

                ->format('totalstok', function ($value) {
                    return number_format($value, 0, ',', '.');
                })
                ->format('totmascik', function ($value) {
                    return number_format($value, 0, ',', '.');
                })
                ->format('totmascir', function ($value) {
                    return number_format($value, 0, ',', '.');
                })
                ->toJson(true);
        }
    }
}
