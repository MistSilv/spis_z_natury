<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProduktSkany extends Model
{
    protected $table = 'produkt_skany';
    protected $fillable = ['product_id', 'user_id', 'region_id', 'quantity', 'barcode', 'scanned_at'];

    public function product() { return $this->belongsTo(Product::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function region() { return $this->belongsTo(Region::class); }
<<<<<<< HEAD
}
=======
}
>>>>>>> fc4ff3a2163296e26293897d79b059344df439af
