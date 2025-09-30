<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPriceHistory extends Model
{
    protected $table = 'product_prices_history';

    protected $fillable = ['product_id', 'price', 'changed_at'];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public $timestamps = false; // bo masz własne pole `changed_at`

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
