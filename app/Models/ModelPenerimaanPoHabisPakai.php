<?php
namespace App\Models;
use CodeIgniter\Model;
class ModelPenerimaanPoHabisPakai extends Model { protected $table='penerimaan_po_habis_pakai'; protected $primaryKey='id'; protected $returnType='array'; protected $allowedFields=['po_id','detail_id','stok_id','nomor_invoice','nomor_surat_jalan','qty','diterima_oleh','diterima_at']; }
