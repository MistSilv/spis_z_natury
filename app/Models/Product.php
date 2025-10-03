<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Product extends Model
{
    protected $fillable = ['id_abaco', 'name', 'unit_id'];

    public function unit() { return $this->belongsTo(Unit::class); }
    public function barcodes() { return $this->hasMany(Barcode::class); }
    public function produktSkany() { return $this->hasMany(ProduktSkany::class); }
    public function prices(){ return $this->hasMany(ProductPriceHistory::class, 'product_id'); }
    public function latestPrice(){ return $this->hasOne(ProductPriceHistory::class)->latestOfMany('changed_at'); }
    public function priceAt($datetime)
    {
        $scanDateTime = \Carbon\Carbon::parse($datetime);
        
        
        // Pobierz wszystkie dostępne ceny przed datą skanu
        $availablePrices = $this->prices()
            ->where('changed_at', '<=', $scanDateTime)
            ->where('price', '>', 0)
            ->orderBy('changed_at', 'desc')
            ->get();
        
        
        foreach ($availablePrices as $price) {
        }
        
        $priceRecord = $availablePrices->first();
        $finalPrice = $priceRecord?->price ?? 0;
        

        
        return $finalPrice;
    }

    public function isUsed(): bool
    {
        return $this->produktSkany()->exists()
            || \DB::table('spis_produkty')->where('name', $this->name)->exists()
            || \DB::table('spis_produkty_tmp')->where('product_id', $this->id)->exists()
            || \DB::table('produkty_filtr_tmp')->where('product_id', $this->id)->exists();
    }


}
