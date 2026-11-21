<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProfitSettlement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProfitSettlementService
{
    /**
     * Iranian business weeks run from Saturday to Friday.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    public static function weekRange(Carbon $date): array
    {
        $start = $date->copy()->startOfWeek(Carbon::SATURDAY)->startOfDay();

        return [$start, $start->copy()->addDays(6)->startOfDay()];
    }

    /**
     * Split a weekly profit between the team and the investor and add VAT.
     *
     * @return array{team_share: float, investor_share: float, team_vat: float, investor_vat: float}
     */
    public static function split(float $profit, float $teamPercent, float $vatPercent): array
    {
        $profit = round(max(0, $profit), 2);
        $teamPercent = max(0, min(100, $teamPercent));
        $vatPercent = max(0, $vatPercent);

        $teamShare = round($profit * $teamPercent / 100, 2);
        $investorShare = round($profit - $teamShare, 2);

        return [
            'team_share' => $teamShare,
            'investor_share' => $investorShare,
            'team_vat' => round($teamShare * $vatPercent / 100, 2),
            'investor_vat' => round($investorShare * $vatPercent / 100, 2),
        ];
    }

    /**
     * @return array{sales_revenue: float, sales_cost: float, gross_profit: float}
     */
    public function calculate(Carbon $start, Carbon $end): array
    {
        $invoices = Invoice::query()
            ->where('invoice_type', InvoiceType::SELL)
            ->whereIn('status', InvoiceStatus::approvedOrSettled())
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->with('items')
            ->get();

        $revenue = 0.0;
        $cost = 0.0;

        foreach ($invoices as $invoice) {
            foreach ($invoice->items as $item) {
                $net = ((float) $item->quantity * (float) $item->unit_price) - (float) $item->discount;
                $revenue += $net;

                if ($item->itemable_type !== Product::class) {
                    continue;
                }

                $unitCost = (float) ($item->unit_cost ?? 0);

                if ($unitCost <= 0) {
                    $unitCost = (float) ($item->cog_after ?? 0);
                }

                $cost += (float) $item->quantity * $unitCost;
            }
        }

        $revenue = round($revenue, 2);
        $cost = round($cost, 2);

        return [
            'sales_revenue' => $revenue,
            'sales_cost' => $cost,
            'gross_profit' => round($revenue - $cost, 2),
        ];
    }

    public function generate(Carbon $weekStart, ?User $user = null, ?float $teamPercent = null, ?float $vatPercent = null): ProfitSettlement
    {
        [$start, $end] = self::weekRange($weekStart);
        $teamPercent ??= (float) config('woocommerce.team_share_percent', 50);
        $vatPercent ??= (float) config('woocommerce.settlement_vat_percent', 10);

        $totals = $this->calculate($start, $end);
        $split = self::split($totals['gross_profit'], $teamPercent, $vatPercent);

        $existing = ProfitSettlement::query()->whereDate('week_start', $start->toDateString())->first();

        if ($existing && in_array($existing->status, ['confirmed', 'paid'], true)) {
            throw ValidationException::withMessages([
                'week_start' => __('This weekly settlement is already confirmed and cannot be recalculated.'),
            ]);
        }

        return DB::transaction(function () use ($existing, $start, $end, $totals, $split, $teamPercent, $vatPercent, $user) {
            $settlement = $existing ?? new ProfitSettlement(['week_start' => $start->toDateString()]);

            $settlement->fill([
                'week_start' => $start->toDateString(),
                'week_end' => $end->toDateString(),
                'sales_revenue' => $totals['sales_revenue'],
                'sales_cost' => $totals['sales_cost'],
                'gross_profit' => $totals['gross_profit'],
                'team_percent' => $teamPercent,
                'team_share' => $split['team_share'],
                'investor_share' => $split['investor_share'],
                'vat_percent' => $vatPercent,
                'team_vat' => $split['team_vat'],
                'investor_vat' => $split['investor_vat'],
                'created_by' => $settlement->created_by ?? $user?->id,
            ]);

            if (! $settlement->exists) {
                $settlement->status = 'draft';
            }

            $settlement->save();

            return $settlement;
        });
    }
}
