<?php

namespace App\Models;

use CodeIgniter\Model;

class ModelInvoiceOutDetail extends Model
{
    protected $table = 'invoice_out_detail';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'invoice_id', 'product_code', 'source_no', 'status', 'product_name', 'qty', 'unit',
        'unit_price', 'amount', 'material_cost_snapshot', 'material_cost_detail_snapshot',
        'material_cost_complete_snapshot', 'created_at',
    ];
}
