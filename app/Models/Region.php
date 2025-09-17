<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $fillable = ['code', 'name', 'notes'];

    public function users() { return $this->hasMany(User::class); }
    public function produktSkany() { return $this->hasMany(ProduktSkany::class); }
    public function spisZNatury() { return $this->hasMany(SpisZNatury::class); }
    public function spisProdukty() { return $this->hasMany(SpisProdukty::class); }
}