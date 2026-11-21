@isset($storeSales)
    @if ($storeSales['orders_count'] > 0)
        <section aria-labelledby="store-sales-title" class="relative mt-4 overflow-hidden rounded-3xl border border-base-300 bg-base-100 p-5 shadow-sm sm:p-6">
            <div aria-hidden="true" class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-primary via-info to-success"></div>

            <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 id="store-sales-title" class="text-base font-bold text-base-content">{{ __('Site sales') }}</h2>
                    <p class="mt-1 text-xs leading-5 text-base-content/55">
                        {{ __('These orders and customers are read directly from your WooCommerce store.') }}
                    </p>
                </div>
                <a href="{{ route('website-orders.index') }}" class="mt-2 inline-flex w-fit items-center gap-1.5 rounded-full bg-base-200 px-2.5 py-1 text-xs text-base-content/70 transition hover:bg-base-300 sm:mt-0">
                    {{ __('Site orders') }}
                    <span aria-hidden="true">{{ app()->isLocale('fa') ? '←' : '→' }}</span>
                </a>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-base-200 bg-base-100 p-4">
                    <div class="text-xs text-base-content/55">{{ __('Site sales (Toman)') }}</div>
                    <div class="mt-1 text-xl font-bold text-base-content">{{ formatNumber($storeSales['sales']) }}</div>
                    <div class="mt-0.5 text-[11px] text-base-content/45">{{ __('Completed and processing orders') }}</div>
                </div>
                <div class="rounded-2xl border border-base-200 bg-base-100 p-4">
                    <div class="text-xs text-base-content/55">{{ __('Paid orders') }}</div>
                    <div class="mt-1 text-xl font-bold text-base-content">{{ localizeNumber($storeSales['paid_orders']) }} / {{ localizeNumber($storeSales['orders_count']) }}</div>
                    <div class="mt-0.5 text-[11px] text-base-content/45">{{ __('Cancelled') }}: {{ localizeNumber($storeSales['cancelled_orders']) }}</div>
                </div>
                <div class="rounded-2xl border border-base-200 bg-base-100 p-4">
                    <div class="text-xs text-base-content/55">{{ __('Average order (Toman)') }}</div>
                    <div class="mt-1 text-xl font-bold text-base-content">{{ formatNumber($storeSales['average_order']) }}</div>
                </div>
                <div class="rounded-2xl border border-base-200 bg-base-100 p-4">
                    <div class="text-xs text-base-content/55">{{ __('Site customers') }}</div>
                    <div class="mt-1 text-xl font-bold text-base-content">{{ localizeNumber($storeSales['customers_count']) }}</div>
                    <a href="{{ route('website-customers.index') }}" class="mt-0.5 inline-block text-[11px] text-primary hover:underline">{{ __('View customers') }}</a>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
                <div class="rounded-2xl border border-base-200 bg-base-100 p-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-base-content">{{ __('Last 14 days sales') }}</h3>
                        <span class="text-[11px] text-base-content/45">{{ __('Toman') }}</span>
                    </div>
                    <div class="mt-4 flex h-40 items-end gap-1">
                        @foreach ($storeSales['daily'] as $day)
                            <div class="flex h-full flex-1 flex-col items-center justify-end gap-1" title="{{ convertToJalali($day['date']) }}: {{ formatNumber($day['total']) }} {{ __('Toman') }}">
                                <div class="w-full rounded-t bg-primary/70 transition hover:bg-primary" style="height: {{ $day['height'] }}%"></div>
                                <span class="text-[10px] text-base-content/45">{{ $day['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-2xl border border-base-200 bg-base-100 p-4">
                    <h3 class="text-sm font-semibold text-base-content">{{ __('Best-selling site products') }}</h3>
                    <ul class="mt-2">
                        @forelse ($storeSales['top_products'] as $product)
                            <li class="flex items-center justify-between gap-3 border-b border-base-200 py-2 last:border-0">
                                <span class="truncate text-sm text-base-content/80">{{ $product->name }}</span>
                                <span class="shrink-0 text-xs text-base-content/55">
                                    {{ formatNumber($product->sold_quantity) }} ×
                                    <span class="text-base-content/35">|</span>
                                    {{ formatNumber($product->sold_total) }}
                                </span>
                            </li>
                        @empty
                            <li class="py-4 text-sm text-base-content/50">{{ __('No product sales recorded yet.') }}</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <div class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-base-content/55">
                <span>{{ __('Completed') }}: <strong class="text-base-content">{{ localizeNumber($storeSales['completed_orders']) }}</strong></span>
                <span>{{ __('Processing') }}: <strong class="text-base-content">{{ localizeNumber($storeSales['processing_orders']) }}</strong></span>
                <span>{{ __('Last site order') }}: <strong class="text-base-content">{{ $storeSales['last_order_at'] ? convertToJalali($storeSales['last_order_at'], true) : __('Never') }}</strong></span>
                <span>{{ __('Last sync') }}: <strong class="text-base-content">{{ $storeSales['last_synced_at'] ? convertToJalali($storeSales['last_synced_at'], true) : __('Never') }}</strong></span>
            </div>
        </section>
    @else
        <section class="mt-4 rounded-2xl border border-dashed border-base-300 bg-base-100 p-5">
            <h2 class="text-base font-bold text-base-content">{{ __('Site sales') }}</h2>
            <p class="mt-1 text-sm text-base-content/60">{{ __('No site orders have been synchronized yet.') }}</p>
            <a href="{{ route('store-sync.index') }}" class="btn btn-sm btn-primary mt-3">{{ __('Store sync') }}</a>
        </section>
    @endif
@endisset
