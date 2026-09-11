<?php

namespace App\Models;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\Model;

class Modelngdata extends Model
{
    protected $table = "ngdata";
    protected $column_order = array(null, 'idsup', 'tgl', 'matjenis', 'beratmatkeluar', 'beratmatmasuk', 'beratng', null);
    protected $column_search = array('matnama', 'tgl', 'supnama');
    protected $order = array('matnama' => 'ASC');
    protected $request;
    protected $db;
    protected $dt;

    function __construct(IncomingRequest $request)
    {
        parent::__construct();
        $this->db = db_connect();
        $this->request = $request;
    }
    private function _get_datatables_query($tglawal, $tglakhir)
    {
        if ($tglawal == '' && $tglakhir == '') {
            $this->dt = $this->db->table($this->table)
                ->join('supplier', 'idsup=supid', 'left')
                ->join('material', 'matid=matjenis');
        } else {
            $this->dt = $this->db->table($this->table)
                ->join('supplier', 'idsup=supid', 'left')
                ->join('material', 'matid=matjenis')
                ->where('tgl >=', $tglawal)
                ->where('tgl <=', $tglakhir);
        }
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
        if ($this->request->getPost('order')) {
            $this->dt->orderBy($this->column_order[$this->request->getPost('order')['0']['column']], $this->request->getPost('order')['0']['dir']);
        } else if (isset($this->order)) {
            $order = $this->order;
            $this->dt->orderBy(key($order), $order[key($order)]);
        }
    }
    function get_datatables($tglawal, $tglakhir)
    {
        $this->_get_datatables_query($tglawal, $tglakhir);
        if ($this->request->getPost('length') != -1)
            $this->dt->limit($this->request->getPost('length'), $this->request->getPost('start'));
        $this->dt->select('matjenis, tgl, supnama, matnama, SUM(beratmatmasuk) AS total_beratmatmasuk, SUM(beratmatkeluar) AS total_beratmatkeluar, (SUM(beratmatmasuk) - SUM(beratmatkeluar)) AS selisih');
        $this->dt->groupBy(['matnama', 'supnama']);
        $query = $this->dt->get();
        return $query->getResult();
    }
    function count_filtered($tglawal, $tglakhir)
    {
        $this->_get_datatables_query($tglawal, $tglakhir);
        return $this->dt->countAllResults();
    }
    public function count_all($tglawal, $tglakhir)
    {
        if ($tglawal == '' && $tglakhir == '') {
            $tbl_storage = $this->db->table($this->table)
                ->join('supplier', 'idsup=supid', 'left')
                ->join('material', 'matid=matjenis');
        } else {
            $tbl_storage = $this->db->table($this->table)
                ->join('supplier', 'idsup=supid', 'left')
                ->join('material', 'matid=matjenis')
                ->where('tgl >=', $tglawal)
                ->where('tgl <=', $tglakhir);
        }

        return $tbl_storage->countAllResults();
    }
}
