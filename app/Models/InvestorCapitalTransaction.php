<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestorCapitalTransaction extends Model
{
    protected $fillable = [
        'investor_id',
        'type',
        'amount',
        'date',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    public function investor(): BelongsTo
    {
        return $this->belongsTo(Investor::class);
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'deposit' => __('Capital deposit'),
            'withdraw' => __('Capital withdrawal'),
            default => $this->type,
        };
    }
}
