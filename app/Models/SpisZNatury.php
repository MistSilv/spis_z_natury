<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpisZNatury extends Model
{
    protected $table = 'spis_z_natury';
    protected $fillable = ['user_id', 'region_id', 'name', 'description'];

    public function user() { return $this->belongsTo(User::class); }
    public function region() { return $this->belongsTo(Region::class); }
    public function spisProdukty() { return $this->hasMany(SpisProdukty::class, 'spis_id'); }
}
<<<<<<< HEAD
=======

>>>>>>> fc4ff3a2163296e26293897d79b059344df439af
