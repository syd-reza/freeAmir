<?php

namespace App\Http\Controllers;

use App\Models\Investor;
use App\Models\InvestorCapitalTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InvestorController extends Controller
{
    public function index(): View
    {
        $investors = Investor::query()->withCount('capitalTransactions')->orderBy('name')->get();

        return view('investors.index', [
            'investors' => $investors,
            'totalCapital' => (float) $investors->sum('capital_amount'),
        ]);
    }

    public function create(): View
    {
        return view('investors.create', ['investor' => new Investor]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateInvestor($request);

        DB::transaction(function () use ($validated) {
            $initialCapital = (float) ($validated['initial_capital'] ?? 0);
            unset($validated['initial_capital']);

            $validated['capital_amount'] = $initialCapital;
            $investor = Investor::create($validated);

            if ($initialCapital > 0) {
                $investor->capitalTransactions()->create([
                    'type' => 'deposit',
                    'amount' => $initialCapital,
                    'date' => now()->toDateString(),
                    'description' => __('Initial capital'),
                ]);
            }
        });

        return redirect()->route('investors.index')->with('success', __('Investor created successfully.'));
    }

    public function edit(Investor $investor): View
    {
        return view('investors.edit', compact('investor'));
    }

    public function update(Request $request, Investor $investor): RedirectResponse
    {
        $validated = $this->validateInvestor($request, $investor);
        unset($validated['initial_capital']);

        $investor->update($validated);

        return redirect()->route('investors.index')->with('success', __('Investor updated successfully.'));
    }

    public function show(Investor $investor): View
    {
        $investor->load('capitalTransactions');

        return view('investors.show', compact('investor'));
    }

    public function destroy(Investor $investor): RedirectResponse
    {
        $investor->delete();

        return redirect()->route('investors.index')->with('success', __('Investor deleted successfully.'));
    }

    public function storeCapital(Request $request, Investor $investor): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:deposit,withdraw'],
            'amount' => ['required', 'numeric', 'min:1'],
            'date' => ['required', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:200'],
        ]);

        $amount = (float) $validated['amount'];

        if ($validated['type'] === 'withdraw' && $amount > (float) $investor->capital_amount) {
            throw ValidationException::withMessages([
                'amount' => __('The withdrawal amount cannot exceed the current capital.'),
            ]);
        }

        DB::transaction(function () use ($investor, $validated, $amount) {
            $investor->capitalTransactions()->create([
                'type' => $validated['type'],
                'amount' => $amount,
                'date' => jalaliInputToGregorian($validated['date']),
                'description' => $validated['description'] ?? null,
            ]);

            $investor->capital_amount = $validated['type'] === 'deposit'
                ? (float) $investor->capital_amount + $amount
                : (float) $investor->capital_amount - $amount;
            $investor->save();
        });

        return redirect()->route('investors.show', $investor)->with('success', __('Capital transaction recorded successfully.'));
    }

    public function destroyCapital(Investor $investor, InvestorCapitalTransaction $transaction): RedirectResponse
    {
        abort_unless((int) $transaction->investor_id === (int) $investor->id, 404);

        DB::transaction(function () use ($investor, $transaction) {
            $investor->capital_amount = $transaction->type === 'deposit'
                ? (float) $investor->capital_amount - (float) $transaction->amount
                : (float) $investor->capital_amount + (float) $transaction->amount;
            $investor->save();

            $transaction->delete();
        });

        return redirect()->route('investors.show', $investor)->with('success', __('Capital transaction deleted successfully.'));
    }

    private function validateInvestor(Request $request, ?Investor $investor = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'national_id' => ['nullable', 'string', 'max:20'],
            'share_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'initial_capital' => ['nullable', 'numeric', 'min:0'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['initial_capital'] = $validated['initial_capital'] ?? 0;

        return $validated;
    }
}
