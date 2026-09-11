<?php

namespace App\Models;

use CodeIgniter\Model;

class ModelLogStokHabisPakai extends Model
{
    protected $table = 'log_stok_habis_pakai';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['stok_id', 'permintaan_id', 'po_keluar_id', 'po_detail_id', 'nomor_invoice', 'nomor_surat_jalan', 'jenis', 'qty', 'stok_sebelum', 'stok_sesudah', 'user_id', 'catatan', 'created_at'];
}
