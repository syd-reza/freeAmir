@isset($storeOverview)
    @if ($storeOverview['count'] > 0)
        <section aria-labelledby="store-overview-title" class="relative overflow-hidden rounded-3xl border border-base-300 bg-base-100 p-5 shadow-sm sm:p-6">
            <div aria-hidden="true" class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-success via-info to-primary"></div>

            <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 id="store-overview-title" class="text-base font-bold text-base-content">{{ __('Store overview') }}</h2>
                    <p class="mt-1 text-xs leading-5 text-base-content/55">
                        {{ __('These values come directly from the products synchronized with your website.') }}
                    </p>
                </div>
                <a href="{{ route('store-sync.index') }}" class="mt-2 inline-flex w-fit items-center gap-1.5 rounded-full bg-base-200 px-2.5 py-1 text-xs text-base-content/70 transition hover:bg-base-300 sm:mt-0">
                    {{ __('Store sync') }}
                    <span aria-hidden="true">{{ app()->isLocale('fa') ? '←' : '→' }}</span>
                </a>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-5">
                <div class="rounded-2xl border border-base-200 bg-base-100 p-4">
                    <div class="text-xs text-base-content/55">{{ __('Store products') }}</div>
                    <div class="mt-1 text-xl font-bold text-base-content">{{ localizeNumber($storeOverview['count']) }}</div>
                </div>
                <div class="rounded-2xl border border-base-200 bg-base-100 p-4">
                    <div class="text-xs text-base-content/55">{{ __('Average site price') }}</div>
                    <div class="mt-1 text-xl font-bold text-base-content">{{ formatNumber($storeOverview['average_price']) }}</div>
                </div>
                <div class="rounded-2xl border border-base-200 bg-base-100 p-4">
                    <div class="text-xs text-base-content/55">{{ __('Lowest site price') }}</div>
                    <div class="mt-1 text-xl font-bold text-base-content">{{ formatNumber($storeOverview['min_price']) }}</div>
                </div>
                <div class="rounded-2xl border border-base-200 bg-base-100 p-4">
                    <div class="text-xs text-base-content/55">{{ __('Highest site price') }}</div>
                    <div class="mt-1 text-xl font-bold text-base-content">{{ formatNumber($storeOverview['max_price']) }}</div>
                </div>
                <div class="rounded-2xl border border-base-200 bg-base-100 p-4">
                    <div class="text-xs text-base-content/55">{{ __('Out of stock on the site') }}</div>
                    <div class="mt-1 text-xl font-bold text-base-content">{{ localizeNumber($storeOverview['out_of_stock']) }}</div>
                </div>
            </div>

            <div class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-base-content/55">
                <span>{{ __('In stock on the site') }}: <strong class="text-base-content">{{ localizeNumber($storeOverview['in_stock']) }}</strong></span>
                <span>{{ __('Stock not published by the site') }}: <strong class="text-base-content">{{ localizeNumber($storeOverview['unknown_stock']) }}</strong></span>
                <span>{{ __('Last sync') }}: <strong class="text-base-content">{{ $storeOverview['last_synced_at'] ? convertToJalali($storeOverview['last_synced_at'], true) : __('Never') }}</strong></span>
                @if ($storeOverview['purchase_price_missing'] > 0)
                    <span class="text-warning">
                        {{ __(':count products still need a purchase price; profit reports stay inaccurate until it is filled.', ['count' => localizeNumber($storeOverview['purchase_price_missing'])]) }}
                    </span>
                @endif
            </div>
        </section>
    @endif
@endisset
