<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Faktura extends Model
{
    use HasFactory;

    protected $table = 'faktury';

    protected $fillable = [
        'number',
        'data_wystawienia',
        'data_sprzedazy',
        'notes',
        'region_id',
    ];

    // Automatyczne rzutowanie pól na typ date (Carbon)
    protected $casts = [
        'data_wystawienia' => 'date',
        'data_sprzedazy' => 'date',
    ];

    // Relacja 1:N do produktów faktury
    public function produkty()
    {
        return $this->hasMany(FakturyProdukt::class, 'faktura_id');
    }

    // Relacja do regionu
    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

}
