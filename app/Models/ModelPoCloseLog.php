<?php

namespace App\Models;

use CodeIgniter\Model;

class ModelPoCloseLog extends Model
{
    protected $table = 'po_close_log';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'nopo_asal',
        'kodebrg',
        'namabarang',
        'qty_dipindah',
        'harga_satuan',
        'nopo_tujuan',
        'ditutup_oleh',
        'ditutup_pada',
        'reopened_by',
        'reopened_at',
        'reopen_note',
    ];
}
