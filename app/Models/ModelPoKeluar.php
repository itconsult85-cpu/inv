<?php

namespace App\Models;

use CodeIgniter\Model;

class ModelPoKeluar extends Model
{
    protected $table = 'po_keluar';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'no_po',
        'tgl_po',
        'idsup',
        'supplier_nama',
        'keterangan',
        'total_qty',
        'total_nominal',
        'status',
        'jenis_transaksi',
        'jenis_po',
        'sumber_material_produksi',
        'po_asal',
        'kirim_langsung',
        'po_masuk_terkait',
        'created_by',
        'top',
        'system_payment',
        'shipping_to',
        'quot_number',
        'approved_by',
        'print_spec_label',
        'material_column_enabled',
        'print_notes',
        'discount_enabled',
        'discount_amount',
        'ppn_included',
        'pph23_enabled',
        'pph23_amount',
    ];
}
