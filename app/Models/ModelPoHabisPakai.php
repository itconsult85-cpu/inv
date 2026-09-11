<?php
namespace App\Models;
use CodeIgniter\Model;
class ModelPoHabisPakai extends Model { protected $table='po_habis_pakai'; protected $primaryKey='id'; protected $returnType='array'; protected $allowedFields=['nomor_po','tanggal_po','supplier_id','supplier_nama','status','catatan','created_by','created_at','updated_at']; }
