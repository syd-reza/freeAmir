<x-app-layout :title="__('Profit share invoice')">
    <div class="mx-auto max-w-3xl bg-base-100 p-6 print:p-0">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b border-base-300 pb-4">
            <div>
                <h1 class="text-xl font-bold">{{ config('active-company-name', config('app.name')) }}</h1>
                <p class="text-sm text-base-content/60">{{ __('Weekly profit share invoice') }}</p>
            </div>
            <div class="text-sm">
                <div>{{ __('Number') }}: <span dir="ltr">{{ $settlement->documentNumber() }}</span></div>
                <div>{{ __('Date') }}: {{ convertToJalali($settlement->week_end) }}</div>
                <div>{{ __('Week') }}: {{ convertToJalali($settlement->week_start) }} - {{ convertToJalali($settlement->week_end) }}</div>
            </div>
        </div>

        <div class="mb-4 rounded-lg bg-base-200/60 p-3 text-sm">
            <span class="font-semibold">{{ __('Invoice for') }}:</span>
            {{ $party === 'team' ? __('Team') : __('Investor') }}
        </div>

        <table class="table table-bordered w-full">
            <thead>
                <tr>
                    <th>{{ __('Description') }}</th>
                    <th>{{ __('Amount') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ __('Weekly sales revenue') }}</td>
                    <td>{{ formatNumber($settlement->sales_revenue) }}</td>
                </tr>
                <tr>
                    <td>{{ __('Cost of goods sold') }}</td>
                    <td>{{ formatNumber($settlement->sales_cost) }}</td>
                </tr>
                <tr>
                    <td>{{ __('Gross profit') }}</td>
                    <td>{{ formatNumber($settlement->gross_profit) }}</td>
                </tr>
                @if ($party === 'team')
                    <tr>
                        <td>{{ __('Team share') }} ({{ localizeNumber($settlement->team_percent) }}%)</td>
                        <td>{{ formatNumber($settlement->team_share) }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('VAT') }} ({{ localizeNumber($settlement->vat_percent) }}%)</td>
                        <td>{{ formatNumber($settlement->team_vat) }}</td>
                    </tr>
                    <tr class="font-bold">
                        <td>{{ __('Total payable') }}</td>
                        <td>{{ formatNumber($settlement->totalTeamPayable()) }}</td>
                    </tr>
                @else
                    @foreach ($investors as $investor)
                        <tr>
                            <td>{{ $investor->name }} - {{ localizeNumber($investor->share_percent) }}%</td>
                            <td>{{ formatNumber($investor->allocated_share) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td>{{ __('Investor share') }}</td>
                        <td>{{ formatNumber($settlement->investor_share) }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('VAT') }} ({{ localizeNumber($settlement->vat_percent) }}%)</td>
                        <td>{{ formatNumber($settlement->investor_vat) }}</td>
                    </tr>
                    <tr class="font-bold">
                        <td>{{ __('Total payable') }}</td>
                        <td>{{ formatNumber($settlement->totalInvestorPayable()) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <p class="mt-4 text-xs text-base-content/50">
            {{ __('This invoice records the weekly partnership profit share. It is a settlement statement and is not posted again to the accounting ledger.') }}
        </p>

        <div class="mt-6 flex gap-2 print:hidden">
            <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">{{ __('Print') }}</button>
            <button type="button" class="btn btn-ghost btn-sm" onclick="window.close()">{{ __('Close') }}</button>
        </div>
    </div>

    @if ($autoPrint)
        <script>
            window.addEventListener('load', () => window.print());
        </script>
    @endif
</x-app-layout>
