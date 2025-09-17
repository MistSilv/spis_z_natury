<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['id_abaco', 'name', 'price', 'unit_id'];

    public function unit() { return $this->belongsTo(Unit::class); }
    public function barcodes() { return $this->hasMany(Barcode::class); }
    public function produktSkany() { return $this->hasMany(ProduktSkany::class); }
}
