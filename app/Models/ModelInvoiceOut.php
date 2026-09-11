<?php

namespace App\Models;

use CodeIgniter\Model;

class ModelInvoiceOut extends Model
{
    protected $table = 'invoice_out';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'invoice_no', 'invoice_date', 'po_no', 'po_date', 'customer_id',
        'customer_name', 'customer_address', 'customer_phone', 'customer_fax',
        'customer_to', 'signer_name', 'signer_position', 'subtotal',
        'ppn', 'ppn_enabled', 'ppn_percent', 'pph23', 'pph_enabled', 'pph_percent',
        'dp_enabled', 'dp_percent', 'dp_amount', 'grand_total', 'paid_total',
        'bank_owner', 'bank_name', 'bank_account', 'bank_npwp',
        'status', 'status_bayar', 'tanggal_lunas', 'created_by',
    ];
}
