<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WebsiteCustomer extends Model
{
    protected $fillable = [
        'website_id',
        'name',
        'email',
        'phone',
        'city',
        'orders_count',
        'total_spent',
        'date_created',
        'synced_at',
    ];

    protected $casts = [
        'orders_count' => 'integer',
        'total_spent' => 'decimal:2',
        'date_created' => 'datetime',
        'synced_at' => 'datetime',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(WebsiteOrder::class, 'website_customer_id', 'website_id');
    }
}
