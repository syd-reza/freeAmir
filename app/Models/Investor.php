<?php

namespace App\Models;

use App\Models\Scopes\FiscalYearScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Investor extends Model
{
    protected $fillable = [
        'fiscal_year_id',
        'name',
        'phone',
        'national_id',
        'capital_amount',
        'share_percent',
        'is_active',
        'notes',
    ];

    protected $attributes = [
        'capital_amount' => 0,
        'share_percent' => 50,
        'is_active' => true,
    ];

    protected $casts = [
        'capital_amount' => 'decimal:2',
        'share_percent' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new FiscalYearScope);

        static::creating(function (Investor $investor) {
            $investor->fiscal_year_id ??= getActiveFiscalYear();
        });
    }

    public function capitalTransactions(): HasMany
    {
        return $this->hasMany(InvestorCapitalTransaction::class)->latest('date')->latest('id');
    }
}
