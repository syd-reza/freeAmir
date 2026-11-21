<?php

namespace App\Models;

use App\Models\Scopes\FiscalYearScope;
use Illuminate\Database\Eloquent\Model;

class ProfitSettlement extends Model
{
    protected $fillable = [
        'fiscal_year_id',
        'created_by',
        'week_start',
        'week_end',
        'sales_revenue',
        'sales_cost',
        'gross_profit',
        'team_percent',
        'team_share',
        'investor_share',
        'vat_percent',
        'team_vat',
        'investor_vat',
        'status',
        'notes',
    ];

    protected $casts = [
        'week_start' => 'date',
        'week_end' => 'date',
        'sales_revenue' => 'decimal:2',
        'sales_cost' => 'decimal:2',
        'gross_profit' => 'decimal:2',
        'team_percent' => 'decimal:2',
        'team_share' => 'decimal:2',
        'investor_share' => 'decimal:2',
        'vat_percent' => 'decimal:2',
        'team_vat' => 'decimal:2',
        'investor_vat' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new FiscalYearScope);

        static::creating(function (ProfitSettlement $settlement) {
            $settlement->fiscal_year_id ??= getActiveFiscalYear();
        });
    }

    public function documentNumber(): string
    {
        return sprintf('PS-%d-%06d', (int) $this->fiscal_year_id, (int) $this->id);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'confirmed' => __('Confirmed'),
            'paid' => __('Paid'),
            default => __('Draft'),
        };
    }

    public function totalTeamPayable(): float
    {
        return (float) $this->team_share + (float) $this->team_vat;
    }

    public function totalInvestorPayable(): float
    {
        return (float) $this->investor_share + (float) $this->investor_vat;
    }
}
