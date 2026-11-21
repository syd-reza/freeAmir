<x-app-layout :title="__('Reports overview')">
    <x-show-message-bags />

    <div class="px-1 pb-5">
        <h1 class="text-xl font-bold text-base-content">{{ __('Reports overview') }}</h1>
        <p class="mt-0.5 text-sm text-base-content/50">{{ __('Every report of the store, the team and the accounting books in one place.') }}</p>
    </div>

    @php
        $storeReports = [
            ['title' => __('Site sales report'), 'description' => __('Orders, customers and sold items read from the website.'), 'route' => 'website-orders.index'],
            ['title' => __('Weekly profit report'), 'description' => __('Weekly sales profit and its split between the team and the investor.'), 'route' => 'profit-settlements.index'],
            ['title' => __('Team payment report'), 'description' => __('Team profit share and payroll payments made to each member.'), 'route' => 'salary.payrolls.team-payments'],
            ['title' => __('Company overview'), 'description' => __('Income, cost, banks and inventory in one dashboard.'), 'route' => 'reports.company-overview'],
            ['title' => __('Cost and Income Dashboard'), 'description' => __('Monthly income and cost with a full-year forecast.'), 'route' => 'reports.cost-income'],
            ['title' => __('Monthly Income and Expense Workbench'), 'description' => __('Plan and compare monthly budgets with actual values.'), 'route' => 'budgets.index'],
            ['title' => __('Commercial Ledgers'), 'description' => __('Electronic ledgers prepared for tax and audit.'), 'route' => 'commercial-ledgers.index'],
        ];
    @endphp

    <section class="mx-1">
        <h2 class="mb-3 text-base font-bold text-base-content">{{ __('Store and team reports') }}</h2>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($storeReports as $report)
                <a href="{{ route($report['route']) }}" class="group rounded-2xl border border-base-200 bg-base-100 p-4 shadow-sm transition hover:border-primary/40 hover:shadow">
                    <div class="flex items-start justify-between gap-2">
                        <span class="font-semibold text-base-content group-hover:text-primary">{{ $report['title'] }}</span>
                        <span aria-hidden="true" class="text-base-content/30">{{ app()->isLocale('fa') ? '←' : '→' }}</span>
                    </div>
                    <p class="mt-1 text-xs leading-5 text-base-content/55">{{ $report['description'] }}</p>
                </a>
            @endforeach
        </div>
    </section>
</x-app-layout>
