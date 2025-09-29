<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpisProduktyTmp extends Model
{
    use HasFactory;

    protected $table = 'spis_produkty_tmp';

    protected $fillable = [
        'spis_id',
        'user_id',
        'product_id',
        'region_id',
        'name',
        'price',
        'quantity',
        'unit',
        'barcode',
        'scanned_at',
        'added_at',
    ];

    protected $casts = [
    'scanned_at' => 'datetime',
    'added_at' => 'datetime',
];


    public function spis()
    {
        return $this->belongsTo(SpisZNatury::class, 'spis_id');
    }

    public function user()
{
    return $this->belongsTo(User::class, 'user_id');

    
}

public function product()
{
    return $this->belongsTo(Produkt::class, 'product_id');
}


public function region()
{
    return $this->belongsTo(Region::class, 'region_id');
}


}
