<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WebsiteOrder extends Model
{
    /**
     * Statuses that count as real sales for store reports.
     */
    public const PAID_STATUSES = ['completed', 'processing'];

    protected $fillable = [
        'website_id',
        'number',
        'status',
        'currency',
        'total',
        'discount_total',
        'shipping_total',
        'total_tax',
        'payment_method',
        'payment_method_title',
        'website_customer_id',
        'billing_name',
        'billing_phone',
        'billing_email',
        'billing_city',
        'date_created',
        'date_paid',
        'item_count',
        'synced_at',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'shipping_total' => 'decimal:2',
        'total_tax' => 'decimal:2',
        'date_created' => 'datetime',
        'date_paid' => 'datetime',
        'synced_at' => 'datetime',
        'item_count' => 'integer',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(WebsiteOrderItem::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(WebsiteCustomer::class, 'website_customer_id', 'website_id');
    }

    public function scopePaid(Builder $query): void
    {
        $query->whereIn('status', self::PAID_STATUSES);
    }

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return [
            'pending' => __('Pending payment'),
            'processing' => __('Processing'),
            'on-hold' => __('On hold'),
            'completed' => __('Completed'),
            'cancelled' => __('Cancelled'),
            'refunded' => __('Refunded'),
            'failed' => __('Failed'),
        ];
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? $this->status;
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'completed' => 'badge-success',
            'processing' => 'badge-info',
            'pending', 'on-hold' => 'badge-warning',
            'cancelled', 'failed' => 'badge-error',
            default => 'badge-ghost',
        };
    }
}
