<?php
namespace App\Models;
use CodeIgniter\Model;
class ModelDetailPoHabisPakai extends Model { protected $table='po_habis_pakai_detail'; protected $primaryKey='id'; protected $returnType='array'; protected $allowedFields=['po_id','stok_id','kode','nama','satuan','qty_pesan','qty_diterima','harga','subtotal']; }
