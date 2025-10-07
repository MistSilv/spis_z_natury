<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FakturyProdukt extends Model
{
    use HasFactory;

    protected $table = 'faktury_produkty';

    protected $fillable = [
        'faktura_id',
        'product_id', // opcjonalne powiązanie z katalogiem produktów
        'name',
        'price',
        'quantity',
        'unit',
        'barcode',
    ];

    /**
     * Relacja do faktury
     */
    public function faktura()
    {
        return $this->belongsTo(Faktura::class, 'faktura_id');
    }

    /**
     * Opcjonalna relacja do produktu z katalogu (products)
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
