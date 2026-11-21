<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebsiteOrderItem extends Model
{
    protected $fillable = [
        'website_order_id',
        'website_product_id',
        'product_id',
        'name',
        'sku',
        'quantity',
        'total',
    ];

    protected $casts = [
        'quantity' => 'float',
        'total' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(WebsiteOrder::class, 'website_order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
