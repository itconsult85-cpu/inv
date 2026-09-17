<?php

namespace App\Models;

use CodeIgniter\Model;

class ModelInvoiceIn extends Model
{
    protected $table = 'invoice_in';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'invoice_no', 'invoice_date', 'supplier_id', 'supplier_name',
        'supplier_address', 'supplier_phone', 'source_type', 'source_no',
        'invoice_file', 'invoice_original_name', 'invoice_uploaded_at',
        'bukti_transfer_file', 'bukti_transfer_original_name', 'bukti_transfer_uploaded_at',
        'subtotal', 'ppn', 'ppn_enabled', 'ppn_percent',
        'pph23', 'pph_enabled', 'pph_percent',
        'dp_enabled', 'dp_mode', 'dp_percent', 'dp_amount',
        'grand_total', 'status', 'tanggal_lunas', 'created_by',
    ];
}
