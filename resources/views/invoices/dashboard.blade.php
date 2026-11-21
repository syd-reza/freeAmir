<x-app-layout :title="__('Invoice Dashboard')">
    <x-show-message-bags />

    <main class="mt-8 space-y-4" data-invoice-dashboard>
        <section class="rounded-xl border border-base-300 bg-base-100 shadow-sm">
            <div class="grid gap-5 p-5 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-end">
                <div class="max-w-3xl">
                    <div class="mb-3 flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700 dark:text-emerald-300">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        {{ __('Sales and purchasing control room') }}
                    </div>
                    <h1 class="text-2xl font-black tracking-tight text-base-content sm:text-3xl">
                        {{ __('Invoice Dashboard') }}
                    </h1>
                    <p class="mt-2 text-sm leading-6 text-base-content/60">
                        {{ __('Sales and purchase invoices for :period.', ['period' => $periodLabel]) }}
                    </p>
                </div>

                <form action="{{ route('invoices.dashboard') }}" method="GET" class="grid w-full max-w-md grid-cols-2 gap-2 sm:grid-cols-3 xl:justify-self-end">
                    <div class="w-full [&_.input]:input-sm">
                        <x-text-input data-jdp title="{{ __('Start date') }}" input_name="start_date"
                            placeholder="{{ __('Start date') }}" readonly label_class="text-sm"
                            input_value="{{ old('start_date') ?? convertToJalali($filters['start_date'], true) }}"
                            label_text_class="text-gray-500 text-nowrap" input_class="datePicker"></x-text-input>
                    </div>
                    <div class="w-full [&_.input]:input-sm">
                        <x-text-input data-jdp title="{{ __('End date') }}" input_name="end_date"
                            placeholder="{{ __('End date') }}" readonly label_class="text-sm"
                            input_value="{{ old('end_date') ?? convertToJalali($filters['end_date'], true) }}"
                            label_text_class="text-gray-500 text-nowrap" input_class="datePicker"></x-text-input>
                    </div>
                    <div class="flex items-end gap-2 sm:col-span-1">
                        <button type="submit" class="btn btn-sm flex-1 btn-neutral sm:flex-none">{{ __('Apply') }}</button>
                        <a href="{{ route('invoices.dashboard') }}" class="btn btn-sm flex-1 btn-ghost sm:flex-none">{{ __('Reset') }}</a>
                    </div>
                </form>
            </div>

            <div class="flex flex-wrap items-center gap-2 border-t border-base-300 px-5 py-3">
                @can('invoices.create')
                    <a href="{{ route('invoices.create', ['invoice_type' => 'sell']) }}" class="btn btn-sm btn-success">
                        {{ __('Issue sell invoice') }}
                    </a>
                    <a href="{{ route('invoices.create', ['invoice_type' => 'buy']) }}" class="btn btn-sm btn-info">
                        {{ __('Issue buy invoice') }}
                    </a>
                @endcan
                @can('invoices.index')
                    <a href="{{ route('invoices.index', ['invoice_type' => 'sell']) }}" class="btn btn-sm btn-ghost">
                        {{ __('Sell List') }}
                    </a>
                    <a href="{{ route('invoices.index', ['invoice_type' => 'buy']) }}" class="btn btn-sm btn-ghost">
                        {{ __('Buy List') }}
                    </a>
                @endcan
            </div>
        </section>

        <section class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <x-metric-card
                :title="__('Net sales')"
                :value="$summary['net_sales']"
                :suffix="__('Rial')"
                :detail="__('Net of sell returns and voids')"
                :series="$invoiceTrend['sell']"
                tone="success"
                icon="sales" />
            <x-metric-card
                :title="__('Net purchases')"
                :value="$summary['net_purchases']"
                :suffix="__('Rial')"
                :detail="__('Net of buy returns')"
                :series="$invoiceTrend['buy']"
                tone="info"
                icon="buy" />
            <x-metric-card
                :title="__('Sales invoices')"
                :value="$summary['sales_count']"
                :suffix="__('Invoices')"
                :detail="__('Average: :amount Rial', ['amount' => formatNumber($summary['average_sale'])])"
                tone="success"
                icon="sales" />
            <x-metric-card
                :title="__('Purchase invoices')"
                :value="$summary['purchase_count']"
                :suffix="__('Invoices')"
                :detail="__('Average: :amount Rial', ['amount' => formatNumber($summary['average_purchase'])])"
                tone="info"
                icon="buy" />
        </section>

        <section class="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
            <article class="card border border-base-300 bg-base-100/90 shadow-sm">
                <div class="card-body">
                    <div>
                        <h2 class="card-title text-base">{{ __('Sales and purchase trend') }}</h2>
                        <p class="text-xs text-base-content/55">{{ __('Monthly invoice totals net of returns and voids') }}</p>
                    </div>
                    <div class="overflow-x-auto pb-2">
                        <x-charts.line-chart
                            chart-id="invoiceSalesPurchaseTrendChart"
                            class="mt-3"
                            style="min-width: {{ max(640, count($invoiceTrend['labels']) * 80) }}px"
                            height-class="h-72"
                            :labels="$invoiceTrend['labels']"
                            :show-legend="true"
                            :datasets="[
                                ['label' => __('Sell'), 'data' => $invoiceTrend['sell'], 'borderColor' => '#10b981', 'backgroundColor' => '#10b98120'],
                                ['label' => __('Buy'), 'data' => $invoiceTrend['buy'], 'borderColor' => '#0ea5e9', 'backgroundColor' => '#0ea5e914'],
                            ]" />
                    </div>
                </div>
            </article>

            <article class="card border border-base-300 bg-base-100/90 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-base">{{ __('Sales versus purchases') }}</h2>
                    <p class="text-xs text-base-content/55">{{ __('Share of sales and purchases in this period') }}</p>
                    <x-charts.pie-chart
                        chart-id="salesPurchaseShareChart"
                        class="mt-3"
                        height-class="h-72"
                        cutout="55%"
                        :labels="[__('Sell'), __('Buy')]"
                        :data="[(float) $summary['net_sales'], (float) $summary['net_purchases']]"
                        :colors="['#10b981', '#0ea5e9']" />
                </div>
            </article>
        </section>

        <section class="grid grid-cols-1 items-start gap-4 xl:grid-cols-2">
            <article class="card border border-base-300 bg-base-100/90 shadow-sm">
                <div class="card-body p-0">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-base-300 p-4">
                        <div>
                            <h2 class="card-title text-base">{{ __('Recent sales') }}</h2>
                            <p class="text-xs text-base-content/55">{{ __('Every sale is recorded as a sell invoice') }}</p>
                        </div>
                        @can('invoices.index')
                            <a href="{{ route('invoices.index', ['invoice_type' => 'sell']) }}" class="btn btn-sm btn-ghost">{{ __('Sell List') }}</a>
                        @endcan
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table table-zebra">
                            <thead>
                                <tr>
                                    <th>{{ __('Invoice Number') }}</th>
                                    <th>{{ __('Customer') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th class="text-end">{{ __('Amount') }} ({{ __('Rial') }})</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($latestSales as $invoice)
                                    <tr>
                                        <td>
                                            @can('invoices.show')
                                                <a href="{{ route('invoices.show', $invoice) }}" class="link link-hover">{{ localizeNumber($invoice->number) }}</a>
                                            @else
                                                {{ localizeNumber($invoice->number) }}
                                            @endcan
                                        </td>
                                        <td>{{ $invoice->customer?->name ?? '—' }}</td>
                                        <td>{{ formatDate($invoice->date) }}</td>
                                        <td class="text-end tabular-nums">{{ formatNumber($invoice->amount) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-base-content/60">{{ __('No sales invoices found') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </article>

            <article class="card border border-base-300 bg-base-100/90 shadow-sm">
                <div class="card-body p-0">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-base-300 p-4">
                        <div>
                            <h2 class="card-title text-base">{{ __('Recent purchases') }}</h2>
                            <p class="text-xs text-base-content/55">{{ __('Every purchase is recorded as a buy invoice') }}</p>
                        </div>
                        @can('invoices.index')
                            <a href="{{ route('invoices.index', ['invoice_type' => 'buy']) }}" class="btn btn-sm btn-ghost">{{ __('Buy List') }}</a>
                        @endcan
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table table-zebra">
                            <thead>
                                <tr>
                                    <th>{{ __('Invoice Number') }}</th>
                                    <th>{{ __('Customer') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th class="text-end">{{ __('Amount') }} ({{ __('Rial') }})</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($latestPurchases as $invoice)
                                    <tr>
                                        <td>
                                            @can('invoices.show')
                                                <a href="{{ route('invoices.show', $invoice) }}" class="link link-hover">{{ localizeNumber($invoice->number) }}</a>
                                            @else
                                                {{ localizeNumber($invoice->number) }}
                                            @endcan
                                        </td>
                                        <td>{{ $invoice->customer?->name ?? '—' }}</td>
                                        <td>{{ formatDate($invoice->date) }}</td>
                                        <td class="text-end tabular-nums">{{ formatNumber($invoice->amount) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-base-content/60">{{ __('No purchase invoices found') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </article>
        </section>
    </main>

    @pushOnce('scripts')
        <script type="module">
            jalaliDatepicker.startWatch();
        </script>
    @endPushOnce
</x-app-layout>
