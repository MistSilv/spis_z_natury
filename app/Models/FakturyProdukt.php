<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;

class FakturyProdukt extends Model
{
    use HasFactory;

    protected $table = 'faktury_produkty';

    protected $fillable = [
        'faktura_id',
        'product_id',
        'name',
        'price_net',
        'price_gross',
        'vat',
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
     * Relacja do katalogowego produktu
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Getter ceny brutto
     */
    public function getPriceGrossAttribute($value)
    {
        $priceNet = $this->attributes['price_net'] ?? null;
        $vat = $this->attributes['vat'] ?? null;

        Log::info("FakturyProdukt@getPriceGrossAttribute", [
            'stored_value' => $value,
            'price_net' => $priceNet,
            'vat' => $vat
        ]);

        // jeśli VAT jest null → price_gross = null
        if (is_null($vat)) {
            Log::info("FakturyProdukt@getPriceGrossAttribute - brak VAT, zwracamy null");
            return null;
        }

        // jeśli w bazie jest price_gross i VAT istnieje, zwróć stored
        if (!is_null($value)) {
            return $value;
        }

        // przelicz price_gross, jeśli mamy VAT i price_net
        if (!is_null($priceNet)) {
            $gross = round($priceNet * (1 + $vat / 100), 2);
            Log::info("FakturyProdukt@getPriceGrossAttribute - przeliczono", ['price_gross' => $gross]);
            return $gross;
        }

        return null;
    }

    /**
     * Getter VAT
     */
    public function getVatAttribute($value)
    {
        $priceNet = $this->attributes['price_net'] ?? null;
        $priceGross = $this->attributes['price_gross'] ?? null;

        Log::info("FakturyProdukt@getVatAttribute", [
            'stored_value' => $value,
            'price_net' => $priceNet,
            'price_gross' => $priceGross
        ]);

        if (!is_null($value)) {
            return $value;
        }

        if (!is_null($priceNet) && !is_null($priceGross) && $priceNet != 0) {
            $calculatedVat = round((($priceGross / $priceNet) - 1) * 100, 2);
            Log::info("FakturyProdukt@getVatAttribute - przeliczono VAT", ['vat' => $calculatedVat]);
            return $calculatedVat;
        }

        Log::info("FakturyProdukt@getVatAttribute - brak price_net/price_gross, zwracamy null");
        return null;
    }
}
