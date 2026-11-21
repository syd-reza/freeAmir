<?php

namespace App\Http\Controllers;

use App\Models\Investor;
use App\Models\ProfitSettlement;
use App\Services\ProfitSettlementService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfitSettlementController extends Controller
{
    public function __construct(private readonly ProfitSettlementService $service) {}

    public function index(): View
    {
        [$weekStart, $weekEnd] = ProfitSettlementService::weekRange(Carbon::now());

        $settlements = ProfitSettlement::query()->orderByDesc('week_start')->paginate(20);

        return view('profit-settlements.index', [
            'settlements' => $settlements,
            'defaultWeekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'teamPercent' => (float) config('woocommerce.team_share_percent', 50),
            'vatPercent' => (float) config('woocommerce.settlement_vat_percent', 10),
            'yearProfit' => (float) ProfitSettlement::query()->sum('gross_profit'),
            'yearTeam' => (float) ProfitSettlement::query()->sum('team_share'),
            'yearInvestor' => (float) ProfitSettlement::query()->sum('investor_share'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'week_start' => ['required', 'string', 'max:20'],
        ]);

        $weekStart = Carbon::parse(jalaliInputToGregorian($validated['week_start']));

        $settlement = $this->service->generate($weekStart, $request->user());

        return redirect()->route('profit-settlements.show', $settlement)
            ->with('success', __('Weekly settlement calculated successfully.'));
    }

    public function show(ProfitSettlement $profitSettlement): View
    {
        $investors = Investor::query()->where('is_active', true)->orderBy('name')->get();

        $totalInvestorPercent = (float) $investors->sum('share_percent');
        $allocations = $investors->map(function (Investor $investor) use ($profitSettlement, $totalInvestorPercent) {
            $weight = $totalInvestorPercent > 0 ? (float) $investor->share_percent / $totalInvestorPercent : 0;
            $investor->allocated_share = round((float) $profitSettlement->investor_share * $weight, 2);
            $investor->allocated_vat = round((float) $profitSettlement->investor_vat * $weight, 2);

            return $investor;
        });

        return view('profit-settlements.show', [
            'settlement' => $profitSettlement,
            'investors' => $allocations,
        ]);
    }

    public function confirm(ProfitSettlement $profitSettlement): RedirectResponse
    {
        if ($profitSettlement->status === 'draft') {
            $profitSettlement->update(['status' => 'confirmed']);
        }

        return back()->with('success', __('Weekly settlement confirmed.'));
    }

    public function markPaid(ProfitSettlement $profitSettlement): RedirectResponse
    {
        $profitSettlement->update(['status' => 'paid']);

        return back()->with('success', __('Weekly settlement marked as paid.'));
    }

    public function print(Request $request, ProfitSettlement $profitSettlement, string $party): View
    {
        abort_unless(in_array($party, ['team', 'investor'], true), 404);

        $investors = $party === 'investor'
            ? Investor::query()->where('is_active', true)->orderBy('name')->get()
            : collect();

        $totalInvestorPercent = (float) $investors->sum('share_percent');
        $investors->each(function (Investor $investor) use ($profitSettlement, $totalInvestorPercent) {
            $weight = $totalInvestorPercent > 0 ? (float) $investor->share_percent / $totalInvestorPercent : 0;
            $investor->allocated_share = round((float) $profitSettlement->investor_share * $weight, 2);
            $investor->allocated_vat = round((float) $profitSettlement->investor_vat * $weight, 2);
        });

        return view('profit-settlements.print', [
            'settlement' => $profitSettlement,
            'party' => $party,
            'investors' => $investors,
            'autoPrint' => $request->boolean('autoprint'),
        ]);
    }
}
