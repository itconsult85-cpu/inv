<?php

namespace App\Models;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\Model;

class ModelDataStokMaterial extends Model
{
    protected $table = "stokmaterial";
    protected $column_order = array(null, 'id', 'materialid', 'kodematerial', 'namamaterial', 'materialkatid', 'materialsatid', 'gudang', null);
    protected $column_search = array('kodematerial', 'gudang');
    protected $order = array('kodematerial' => 'ASC');
    protected $request;
    protected $db;
    protected $dt;

    function __construct(IncomingRequest $request)
    {
        parent::__construct();
        $this->db = db_connect();
        $this->request = $request;

        $this->dt = $this->db->table($this->table);
        // ->join('barang', 'brgkode=kodematerial', 'left')
        // ->join('gudang', 'gdgid=gudang', 'left');
    }
    private function _get_datatables_query()
    {
        $i = 0;
        foreach ($this->column_search as $item) {
            if ($this->request->getPost('search')['value']) {
                if ($i === 0) {
                    $this->dt->groupStart();
                    $this->dt->like($item, $this->request->getPost('search')['value']);
                } else {
                    $this->dt->orLike($item, $this->request->getPost('search')['value']);
                }
                if (count($this->column_search) - 1 == $i)
                    $this->dt->groupEnd();
            }
            $i++;
        }
        $this->dt->groupBy('materialid');
        if ($this->request->getPost('order')) {
            $this->dt->orderBy(
                $this->column_order[$this->request->getPost('order')['0']['column']],
                $this->request->getPost('order')['0']['dir']
            );
        } else if (isset($this->order)) {
            $order = $this->order;
            $this->dt->orderBy(key($order), $order[key($order)]);
        }
    }
    function get_datatables()
    {
        $this->_get_datatables_query();
        if ($this->request->getPost('length') != -1)
            $this->dt->limit($this->request->getPost('length'), $this->request->getPost('start'));
        $query = $this->dt->get();
        return $query->getResult();
    }
    function count_filtered()
    {
        $this->_get_datatables_query();
        return $this->dt->countAllResults();
    }
    public function count_all()
    {
        $tbl_storage = $this->db->table($this->table);
        // ->join('barang', 'brgkode=kodematerial', 'left')
        // ->join('gudang', 'gdgid=gudang', 'left');
        return $tbl_storage->countAllResults();
    }
}
