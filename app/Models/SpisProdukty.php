<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpisProdukty extends Model
{
    protected $table = 'spis_produkty';
    public $timestamps = false;
    protected $fillable = ['spis_id', 'region_id', 'user_id', 'name', 'price', 'quantity', 'unit', 'barcode', 'added_at'];

    public function spis() { return $this->belongsTo(SpisZNatury::class, 'spis_id'); }
    public function user() { return $this->belongsTo(User::class); }
    public function region() { return $this->belongsTo(Region::class); }
}
