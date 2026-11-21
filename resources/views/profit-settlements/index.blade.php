<x-app-layout :title="__('Weekly profit settlements')">
    <x-show-message-bags />

    <div class="flex flex-wrap items-center justify-between gap-4 px-1 pb-5">
        <div>
            <h1 class="text-xl font-bold text-base-content">{{ __('Weekly profit settlements') }}</h1>
            <p class="mt-0.5 text-sm text-base-content/50">{{ __('Weekly profit is split :percent% for the team and the rest for the investor.', ['percent' => localizeNumber($teamPercent)]) }}</p>
        </div>
        <a href="{{ route('investors.index') }}" class="btn btn-ghost btn-sm">{{ __('Investors') }}</a>
    </div>

    <div class="mx-1 mb-4 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="card border border-base-200 bg-base-100 shadow-sm">
            <div class="card-body py-4">
                <span class="text-sm text-base-content/60">{{ __('Total recorded profit') }}</span>
                <span class="text-2xl font-bold">{{ formatNumber($yearProfit) }}</span>
            </div>
        </div>
        <div class="card border border-base-200 bg-base-100 shadow-sm">
            <div class="card-body py-4">
                <span class="text-sm text-base-content/60">{{ __('Total team share') }}</span>
                <span class="text-2xl font-bold">{{ formatNumber($yearTeam) }}</span>
            </div>
        </div>
        <div class="card border border-base-200 bg-base-100 shadow-sm">
            <div class="card-body py-4">
                <span class="text-sm text-base-content/60">{{ __('Total investor share') }}</span>
                <span class="text-2xl font-bold">{{ formatNumber($yearInvestor) }}</span>
            </div>
        </div>
    </div>

    <div class="card mx-1 mb-4 border border-base-200 bg-base-100 shadow-sm">
        <div class="card-body">
            <h2 class="card-title text-base">{{ __('Calculate or refresh a week') }}</h2>
            <p class="text-sm text-base-content/60">
                {{ __('The current week runs from :start to :end. Profit is sale revenue minus the purchase cost recorded on the invoice items.', [
                    'start' => convertToJalali($defaultWeekStart),
                    'end' => convertToJalali($weekEnd),
                ]) }}
            </p>
            <form action="{{ route('profit-settlements.store') }}" method="POST" class="mt-2 flex flex-wrap items-end gap-3">
                @csrf
                <div class="w-48">
                    <x-input name="week_start" title="{{ __('Any date in the week (Jalali)') }}" :value="old('week_start', convertToJalali($defaultWeekStart))" placeholder="1405/08/25" />
                </div>
                <button type="submit" class="btn btn-primary btn-sm">{{ __('Calculate weekly settlement') }}</button>
            </form>
            <p class="mt-2 text-xs text-base-content/50">{{ __('VAT rate: :rate%', ['rate' => localizeNumber($vatPercent)]) }}</p>
        </div>
    </div>

    <div class="card mx-1 border border-base-200 bg-base-100 shadow-sm">
        <div class="card-body p-0">
            <div class="overflow-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th class="px-4 py-3">{{ __('Week') }}</th>
                            <th class="px-4 py-3">{{ __('Sales') }}</th>
                            <th class="px-4 py-3">{{ __('Cost') }}</th>
                            <th class="px-4 py-3">{{ __('Profit') }}</th>
                            <th class="px-4 py-3">{{ __('Team share') }}</th>
                            <th class="px-4 py-3">{{ __('Investor share') }}</th>
                            <th class="px-4 py-3">{{ __('Status') }}</th>
                            <th class="px-4 py-3">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($settlements as $settlement)
                            <tr>
                                <td class="px-4 py-3">
                                    {{ convertToJalali($settlement->week_start) }} - {{ convertToJalali($settlement->week_end) }}
                                    <div class="text-xs text-base-content/40">{{ $settlement->documentNumber() }}</div>
                                </td>
                                <td class="px-4 py-3">{{ formatNumber($settlement->sales_revenue) }}</td>
                                <td class="px-4 py-3">{{ formatNumber($settlement->sales_cost) }}</td>
                                <td class="px-4 py-3">{{ formatNumber($settlement->gross_profit) }}</td>
                                <td class="px-4 py-3">{{ formatNumber($settlement->team_share) }}</td>
                                <td class="px-4 py-3">{{ formatNumber($settlement->investor_share) }}</td>
                                <td class="px-4 py-3">
                                    <span @class([
                                        'badge badge-sm',
                                        'badge-ghost' => $settlement->status === 'draft',
                                        'badge-info' => $settlement->status === 'confirmed',
                                        'badge-success' => $settlement->status === 'paid',
                                    ])>{{ $settlement->statusLabel() }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('profit-settlements.show', $settlement) }}" class="btn btn-xs btn-info">{{ __('View') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-base-content/50">{{ __('No weekly settlement has been calculated yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($settlements->hasPages())
                <div class="border-t border-base-200 px-5 py-4">
                    {!! $settlements->links() !!}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
