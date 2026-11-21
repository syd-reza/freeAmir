<x-app-layout :title="__('Store sync')">
    <x-show-message-bags />

    <div class="flex flex-wrap items-center justify-between gap-4 px-1 pb-5">
        <div>
            <h1 class="text-xl font-bold text-base-content">{{ __('Store sync') }}</h1>
            <p class="mt-0.5 text-sm text-base-content/50">{{ __('Import product prices and website stock from your WooCommerce store automatically.') }}</p>
        </div>

        <form action="{{ route('store-sync.store') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary btn-sm">{{ __('Sync now') }}</button>
        </form>
    </div>

    <div class="mx-1 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="card border border-base-200 bg-base-100 shadow-sm">
            <div class="card-body">
                <span class="text-sm text-base-content/60">{{ __('Store address') }}</span>
                <a href="{{ $baseUrl }}" target="_blank" class="link link-primary break-all" dir="ltr">{{ $baseUrl }}</a>
                <span class="mt-2 text-xs text-base-content/50" dir="ltr">{{ $apiUrl }}</span>
            </div>
        </div>
        <div class="card border border-base-200 bg-base-100 shadow-sm">
            <div class="card-body">
                <span class="text-sm text-base-content/60">{{ __('Products linked to the store') }}</span>
                <span class="text-2xl font-bold">{{ localizeNumber($syncedProducts) }} / {{ localizeNumber($totalProducts) }}</span>
                <span class="text-xs text-base-content/50">{{ __('Products are matched by website ID, product link, then SKU.') }}</span>
            </div>
        </div>
        <div class="card border border-base-200 bg-base-100 shadow-sm">
            <div class="card-body">
                <span class="text-sm text-base-content/60">{{ __('Last sync') }}</span>
                <span class="text-lg font-semibold">{{ $lastSync['finished_at'] ?? __('Never') }}</span>
                <span class="text-xs text-base-content/50">{{ __('Automatic sync runs every 30 minutes.') }}</span>
            </div>
        </div>
    </div>

    @if ($lastSync)
        <div class="card mx-1 mt-4 border border-base-200 bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-base">{{ __('Last sync result') }}</h2>
                <div class="grid grid-cols-2 gap-3 md:grid-cols-5">
                    <div class="rounded-lg bg-base-200/60 p-3">
                        <div class="text-xs text-base-content/60">{{ __('Created') }}</div>
                        <div class="text-lg font-bold">{{ localizeNumber($lastSync['created'] ?? 0) }}</div>
                    </div>
                    <div class="rounded-lg bg-base-200/60 p-3">
                        <div class="text-xs text-base-content/60">{{ __('Updated') }}</div>
                        <div class="text-lg font-bold">{{ localizeNumber($lastSync['updated'] ?? 0) }}</div>
                    </div>
                    <div class="rounded-lg bg-base-200/60 p-3">
                        <div class="text-xs text-base-content/60">{{ __('Skipped') }}</div>
                        <div class="text-lg font-bold">{{ localizeNumber($lastSync['skipped'] ?? 0) }}</div>
                    </div>
                    <div class="rounded-lg bg-base-200/60 p-3">
                        <div class="text-xs text-base-content/60">{{ __('Pages') }}</div>
                        <div class="text-lg font-bold">{{ localizeNumber($lastSync['pages'] ?? 0) }}</div>
                    </div>
                    <div class="rounded-lg bg-base-200/60 p-3">
                        <div class="text-xs text-base-content/60">{{ __('Store products') }}</div>
                        <div class="text-lg font-bold">{{ localizeNumber($lastSync['total'] ?? 0) }}</div>
                    </div>
                </div>

                @if (! empty($lastSync['errors']))
                    <div class="mt-4 rounded-lg bg-warning/10 p-3 text-sm">
                        <div class="mb-1 font-semibold">{{ __('Skipped rows') }}</div>
                        <ul class="list-inside list-disc space-y-1">
                            @foreach (array_slice($lastSync['errors'], 0, 10) as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div class="mx-1 mt-4 rounded-lg border border-info/30 bg-info/10 p-4 text-sm leading-6">
        <p class="font-semibold">{{ __('How the sync works') }}</p>
        <p>{{ __('The sale price is taken from the store. The website stock is stored separately and never overwrites the accounting stock; warehouse quantities still change only through buy and sell invoices.') }}</p>
        <p>{{ __('Enter the purchase price on each product so the weekly profit report can calculate the real margin.') }}</p>
    </div>

    <div class="mx-1 mt-4 rounded-lg border border-success/30 bg-success/10 p-4 text-sm leading-6">
        <p class="font-semibold">{{ __('Site orders and customers') }}</p>
        @if ($ordersConfigured)
            <p>{{ __('The store REST API is connected: :orders orders and :customers customers are synchronized.', ['orders' => localizeNumber($syncedOrders), 'customers' => localizeNumber($syncedCustomers)]) }}</p>
            <form action="{{ route('store-sync.orders') }}" method="POST" class="mt-2">
                @csrf
                <button type="submit" class="btn btn-sm btn-primary">{{ __('Sync orders and customers') }}</button>
            </form>
            @if ($lastOrderSync)
                <p class="mt-2 text-xs text-base-content/60">
                    {{ __('Last orders sync') }}: {{ $lastOrderSync['finished_at'] ?? '-' }} — {{ localizeNumber($lastOrderSync['total'] ?? 0) }} {{ __('orders') }}، {{ localizeNumber($lastCustomerSync['total'] ?? $syncedCustomers) }} {{ __('customers') }}
                </p>
            @endif
            @if (! empty($lastOrderSync['errors']))
                <ul class="mt-2 list-inside list-disc text-xs text-warning">
                    @foreach (array_slice($lastOrderSync['errors'], 0, 5) as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
        @else
            <p>{{ __('Connect the store REST API by adding WOOCOMMERCE_CONSUMER_KEY and WOOCOMMERCE_CONSUMER_SECRET to the .env file.') }}</p>
        @endif
    </div>
</x-app-layout>
