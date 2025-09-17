<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barcode extends Model
{
    protected $fillable = ['product_id', 'barcode'];
    public function product() { return $this->belongsTo(Product::class); }
}
<<<<<<< HEAD
=======

>>>>>>> fc4ff3a2163296e26293897d79b059344df439af
