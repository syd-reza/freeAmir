<x-app-layout :title="__('Team payment report')">
    <x-show-message-bags />

    <div class="flex flex-wrap items-center justify-between gap-4 px-1 pb-5">
        <div>
            <h1 class="text-xl font-bold text-base-content">{{ __('Team payment report') }}</h1>
            <p class="mt-0.5 text-sm text-base-content/50">
                {{ __('The team share of weekly profit and payroll payments made to each member.') }}
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('hr.employees.index') }}" class="btn btn-ghost btn-sm">{{ __('Team members') }}</a>
            <a href="{{ route('profit-settlements.index') }}" class="btn btn-primary btn-sm">{{ __('Weekly profit settlements') }}</a>
        </div>
    </div>

    <div class="mx-1 mb-4 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="card border border-base-200 bg-base-100 shadow-sm">
            <div class="card-body py-4">
                <span class="text-sm text-base-content/60">{{ __('Total team share') }}</span>
                <span class="text-2xl font-bold">{{ formatNumber($teamPayable) }}</span>
                <span class="text-xs text-base-content/50">{{ localizeNumber($settlements->count()) }} {{ __('weekly settlements') }}</span>
            </div>
        </div>
        <div class="card border border-base-200 bg-base-100 shadow-sm">
            <div class="card-body py-4">
                <span class="text-sm text-base-content/60">{{ __('Paid team share') }}</span>
                <span class="text-2xl font-bold text-success">{{ formatNumber($paidTeamPayable) }}</span>
            </div>
        </div>
        <div class="card border border-base-200 bg-base-100 shadow-sm">
            <div class="card-body py-4">
                <span class="text-sm text-base-content/60">{{ __('Remaining team share') }}</span>
                <span class="text-2xl font-bold text-warning">{{ formatNumber($pendingTeamPayable) }}</span>
            </div>
        </div>
        <div class="card border border-base-200 bg-base-100 shadow-sm">
            <div class="card-body py-4">
                <span class="text-sm text-base-content/60">{{ __('Payroll paid') }}</span>
                <span class="text-2xl font-bold">{{ formatNumber($payrollPaidTotal) }}</span>
            </div>
        </div>
    </div>

    <div class="mx-1 mb-4 rounded-lg border border-info/30 bg-info/10 p-4 text-sm leading-6">
        {{ __('The team share is divided equally between :count active team members. Each member currently has :amount of the recorded team profit share.', ['count' => localizeNumber($memberCount), 'amount' => formatNumber($profitSharePerMember)]) }}
    </div>

    <div class="card mx-1 mb-4 border border-base-200 bg-base-100 shadow-sm">
        <div class="card-body p-0">
            <h2 class="px-4 pt-4 text-base font-bold">{{ __('Share of each team member') }}</h2>
            <div class="overflow-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th class="px-4 py-3">{{ __('Team member') }}</th>
                            <th class="px-4 py-3">{{ __('Profit share') }}</th>
                            <th class="px-4 py-3">{{ __('Payroll paid') }}</th>
                            <th class="px-4 py-3">{{ __('Total received') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($members as $member)
                            <tr>
                                <td class="px-4 py-3 font-semibold">{{ $member['name'] }}</td>
                                <td class="px-4 py-3">{{ formatNumber($member['profit_share']) }}</td>
                                <td class="px-4 py-3">{{ formatNumber($member['payroll_paid']) }}</td>
                                <td class="px-4 py-3 font-semibold">{{ formatNumber($member['total']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-base-content/50">
                                    {{ __('No active team member is registered yet.') }}
                                    <a href="{{ route('hr.employees.create') }}" class="link link-primary">{{ __('Add team member') }}</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mx-1 border border-base-200 bg-base-100 shadow-sm">
        <div class="card-body p-0">
            <h2 class="px-4 pt-4 text-base font-bold">{{ __('Weekly settlements and team payouts') }}</h2>
            <div class="overflow-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th class="px-4 py-3">{{ __('Week') }}</th>
                            <th class="px-4 py-3">{{ __('Gross profit') }}</th>
                            <th class="px-4 py-3">{{ __('Team share') }}</th>
                            <th class="px-4 py-3">{{ __('VAT') }}</th>
                            <th class="px-4 py-3">{{ __('Payable') }}</th>
                            <th class="px-4 py-3">{{ __('Status') }}</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($settlements as $settlement)
                            <tr>
                                <td class="px-4 py-3 text-sm">{{ convertToJalali($settlement->week_start) }} - {{ convertToJalali($settlement->week_end) }}</td>
                                <td class="px-4 py-3">{{ formatNumber($settlement->gross_profit) }}</td>
                                <td class="px-4 py-3">{{ formatNumber($settlement->team_share) }}</td>
                                <td class="px-4 py-3">{{ formatNumber($settlement->team_vat) }}</td>
                                <td class="px-4 py-3 font-semibold">{{ formatNumber($settlement->totalTeamPayable()) }}</td>
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
                                <td colspan="7" class="px-4 py-8 text-center text-base-content/50">
                                    {{ __('No weekly settlement has been calculated yet.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if ($payrolls->isNotEmpty())
        <div class="card mx-1 mt-4 border border-base-200 bg-base-100 shadow-sm">
            <div class="card-body p-0">
                <h2 class="px-4 pt-4 text-base font-bold">{{ __('Payroll payments') }}</h2>
                <div class="overflow-auto">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th class="px-4 py-3">{{ __('Team member') }}</th>
                                <th class="px-4 py-3">{{ __('Year') }}</th>
                                <th class="px-4 py-3">{{ __('Month') }}</th>
                                <th class="px-4 py-3">{{ __('Net payment') }}</th>
                                <th class="px-4 py-3">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($payrolls as $payroll)
                                <tr>
                                    <td class="px-4 py-3">{{ trim(($payroll->employee?->first_name ?? '').' '.($payroll->employee?->last_name ?? '')) }}</td>
                                    <td class="px-4 py-3">{{ localizeNumber($payroll->year) }}</td>
                                    <td class="px-4 py-3">{{ localizeNumber($payroll->month) }}</td>
                                    <td class="px-4 py-3">{{ formatNumber($payroll->net_payment) }}</td>
                                    <td class="px-4 py-3">
                                        <span @class(['badge badge-sm', $payroll->statusBadgeClass()])>{{ $payroll->statusLabel() }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</x-app-layout>
