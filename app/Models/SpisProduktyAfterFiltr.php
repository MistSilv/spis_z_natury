<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpisProduktyAfterFiltr extends Model
{
    protected $table = 'spis_produkty_after_filtr';
    public $timestamps = false;

    protected $fillable = [
        'spis_id', 'user_id', 'name', 'price', 'quantity', 'unit',
        'barcode', 'added_at',
    ];
}
