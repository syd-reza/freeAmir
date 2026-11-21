<x-app-layout :title="__('Weekly settlement') . ' ' . $settlement->documentNumber()">
    <x-show-message-bags />

    <div class="flex flex-wrap items-center justify-between gap-4 px-1 pb-5">
        <div>
            <h1 class="text-xl font-bold text-base-content">{{ __('Weekly settlement') }} {{ $settlement->documentNumber() }}</h1>
            <p class="mt-0.5 text-sm text-base-content/50">
                {{ convertToJalali($settlement->week_start) }} - {{ convertToJalali($settlement->week_end) }}
                <span class="badge badge-sm badge-ghost">{{ $settlement->statusLabel() }}</span>
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('profit-settlements.index') }}" class="btn btn-ghost btn-sm">{{ __('Back') }}</a>
            <a href="{{ route('profit-settlements.print', [$settlement, 'team']) }}" target="_blank" class="btn btn-outline btn-sm">{{ __('Team invoice') }}</a>
            <a href="{{ route('profit-settlements.print', [$settlement, 'investor']) }}" target="_blank" class="btn btn-outline btn-sm">{{ __('Investor invoice') }}</a>
            @if ($settlement->status === 'draft')
                <form action="{{ route('profit-settlements.confirm', $settlement) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm">{{ __('Confirm') }}</button>
                </form>
            @elseif ($settlement->status === 'confirmed')
                <form action="{{ route('profit-settlements.mark-paid', $settlement) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm">{{ __('Mark as paid') }}</button>
                </form>
            @endif
            @if ($settlement->status === 'draft')
                <form action="{{ route('profit-settlements.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="week_start" value="{{ convertToJalali($settlement->week_start) }}">
                    <button type="submit" class="btn btn-warning btn-sm">{{ __('Recalculate') }}</button>
                </form>
            @endif
        </div>
    </div>

    <div class="mx-1 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="card border border-base-200 bg-base-100 shadow-sm">
            <div class="card-body py-4">
                <span class="text-sm text-base-content/60">{{ __('Sales revenue') }}</span>
                <span class="text-2xl font-bold">{{ formatNumber($settlement->sales_revenue) }}</span>
            </div>
        </div>
        <div class="card border border-base-200 bg-base-100 shadow-sm">
            <div class="card-body py-4">
                <span class="text-sm text-base-content/60">{{ __('Cost of goods') }}</span>
                <span class="text-2xl font-bold">{{ formatNumber($settlement->sales_cost) }}</span>
            </div>
        </div>
        <div class="card border border-base-200 bg-base-100 shadow-sm">
            <div class="card-body py-4">
                <span class="text-sm text-base-content/60">{{ __('Gross profit') }}</span>
                <span class="text-2xl font-bold">{{ formatNumber($settlement->gross_profit) }}</span>
            </div>
        </div>
    </div>

    <div class="card mx-1 mt-4 border border-base-200 bg-base-100 shadow-sm">
        <div class="card-body p-0">
            <h2 class="px-4 pt-4 text-base font-bold">{{ __('Profit split') }}</h2>
            <div class="overflow-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th class="px-4 py-3">{{ __('Party') }}</th>
                            <th class="px-4 py-3">{{ __('Share') }}</th>
                            <th class="px-4 py-3">{{ __('VAT') }}</th>
                            <th class="px-4 py-3">{{ __('Payable') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="px-4 py-3">{{ __('Team') }} ({{ localizeNumber($settlement->team_percent) }}%)</td>
                            <td class="px-4 py-3">{{ formatNumber($settlement->team_share) }}</td>
                            <td class="px-4 py-3">{{ formatNumber($settlement->team_vat) }}</td>
                            <td class="px-4 py-3 font-semibold">{{ formatNumber($settlement->totalTeamPayable()) }}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3">{{ __('Investor') }} ({{ localizeNumber(100 - (float) $settlement->team_percent) }}%)</td>
                            <td class="px-4 py-3">{{ formatNumber($settlement->investor_share) }}</td>
                            <td class="px-4 py-3">{{ formatNumber($settlement->investor_vat) }}</td>
                            <td class="px-4 py-3 font-semibold">{{ formatNumber($settlement->totalInvestorPayable()) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="px-4 pb-4 text-xs text-base-content/50">{{ __('VAT rate') }}: {{ localizeNumber($settlement->vat_percent) }}%</p>
        </div>
    </div>

    <div class="card mx-1 mt-4 border border-base-200 bg-base-100 shadow-sm">
        <div class="card-body p-0">
            <h2 class="px-4 pt-4 text-base font-bold">{{ __('Investor allocation') }}</h2>
            <div class="overflow-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th class="px-4 py-3">{{ __('Name') }}</th>
                            <th class="px-4 py-3">{{ __('Share percent') }}</th>
                            <th class="px-4 py-3">{{ __('Amount') }}</th>
                            <th class="px-4 py-3">{{ __('VAT') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($investors as $investor)
                            <tr>
                                <td class="px-4 py-3">{{ $investor->name }}</td>
                                <td class="px-4 py-3">{{ localizeNumber($investor->share_percent) }}%</td>
                                <td class="px-4 py-3">{{ formatNumber($investor->allocated_share) }}</td>
                                <td class="px-4 py-3">{{ formatNumber($investor->allocated_vat) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-base-content/50">{{ __('Register an investor so the profit share can be allocated.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if ($settlement->notes)
        <div class="mx-1 mt-4 rounded-lg bg-base-200/60 p-4 text-sm">{{ $settlement->notes }}</div>
    @endif
</x-app-layout>
