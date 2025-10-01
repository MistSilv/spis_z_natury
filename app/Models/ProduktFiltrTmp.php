<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProduktFiltrTmp extends Model
{
    use HasFactory;

    protected $table = 'produkty_filtr_tmp'; // nazwa tabeli

    protected $fillable = [
        'user_id',
        'region_id',
        'product_id',
        'produkt_skany_id',
        'name',
        'price',
        'quantity',
        'unit',
        'barcode',
        'scanned_at',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
        'price'      => 'decimal:2',
        'quantity'   => 'decimal:2',
    ];

    // === Relacje ===
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scan()
    {
        return $this->belongsTo(ProduktSkany::class, 'produkt_skany_id');
    }
}
