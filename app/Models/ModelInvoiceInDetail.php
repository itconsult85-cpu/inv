<?php

namespace App\Models;

use CodeIgniter\Model;

class ModelInvoiceInDetail extends Model
{
    protected $table = 'invoice_in_detail';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'invoice_id', 'item_type', 'no_surat_jalan', 'item_code', 'item_name', 'qty', 'unit',
        'unit_price', 'amount', 'created_at',
    ];
}
