<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = ['code', 'name', 'notes'];
    public function products() { return $this->hasMany(Product::class); }
}
<<<<<<< HEAD
=======

>>>>>>> fc4ff3a2163296e26293897d79b059344df439af
