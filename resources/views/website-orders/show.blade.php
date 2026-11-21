<x-app-layout :title="__('Site order')">
    <x-show-message-bags />

    <div class="flex flex-wrap items-center justify-between gap-4 px-1 pb-5">
        <div>
            <h1 class="text-xl font-bold text-base-content">{{ __('Site order') }} <span dir="ltr">#{{ $order->number }}</span></h1>
            <p class="mt-0.5 text-sm text-base-content/50">
                {{ $order->date_created ? convertToJalali($order->date_created, true) : '-' }}
                <span dir="ltr">{{ $order->date_created?->format('H:i') }}</span>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span @class(['badge', $order->statusBadgeClass()])>{{ $order->statusLabel() }}</span>
            <a href="{{ route('website-orders.index') }}" class="btn btn-sm btn-ghost">{{ __('Back') }}</a>
        </div>
    </div>

    <div class="mx-1 grid grid-cols-1 gap-4 lg:grid-cols-3">
        <div class="card border border-base-200 bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-base">{{ __('Customer') }}</h2>
                <div class="text-sm leading-7">
                    <div class="font-semibold">{{ $order->billing_name ?: '-' }}</div>
                    <div dir="ltr" class="text-base-content/70">{{ $order->billing_phone ?: '-' }}</div>
                    <div dir="ltr" class="text-base-content/70">{{ $order->billing_email ?: '-' }}</div>
                    <div class="text-base-content/70">{{ $order->billing_city ?: '-' }}</div>
                    @if ($order->customer)
                        <a href="{{ route('website-customers.index', ['search' => $order->customer->phone ?: $order->customer->name]) }}" class="link link-primary text-xs">{{ __('View site customer') }}</a>
                    @endif
                </div>
            </div>
        </div>

        <div class="card border border-base-200 bg-base-100 shadow-sm lg:col-span-2">
            <div class="card-body">
                <h2 class="card-title text-base">{{ __('Payment and totals') }}</h2>
                <div class="grid grid-cols-1 gap-2 text-sm sm:grid-cols-2">
                    <div class="flex justify-between gap-3"><span class="text-base-content/60">{{ __('Payment method') }}</span><span>{{ $order->payment_method_title ?: '-' }}</span></div>
                    <div class="flex justify-between gap-3"><span class="text-base-content/60">{{ __('Discount') }}</span><span>{{ formatNumber($order->discount_total) }}</span></div>
                    <div class="flex justify-between gap-3"><span class="text-base-content/60">{{ __('Shipping') }}</span><span>{{ formatNumber($order->shipping_total) }}</span></div>
                    <div class="flex justify-between gap-3"><span class="text-base-content/60">{{ __('Tax') }}</span><span>{{ formatNumber($order->total_tax) }}</span></div>
                    <div class="flex justify-between gap-3 border-t border-base-200 pt-2 font-bold sm:col-span-2"><span>{{ __('Amount (Toman)') }}</span><span>{{ formatNumber($order->total) }}</span></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mx-1 mt-4 border border-base-200 bg-base-100 shadow-sm">
        <div class="card-body">
            <h2 class="card-title text-base">{{ __('Order items') }}</h2>
            <div class="overflow-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th class="px-4 py-3">{{ __('Product') }}</th>
                            <th class="px-4 py-3">{{ __('SKU') }}</th>
                            <th class="px-4 py-3">{{ __('Quantity') }}</th>
                            <th class="px-4 py-3">{{ __('Amount (Toman)') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr>
                                <td class="px-4 py-3">
                                    {{ $item->name }}
                                    @if ($item->product)
                                        <a href="{{ route('products.show', $item->product) }}" class="link link-primary text-xs">{{ __('Linked product') }}</a>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm" dir="ltr">{{ $item->sku ?: '-' }}</td>
                                <td class="px-4 py-3">{{ formatNumber($item->quantity) }}</td>
                                <td class="px-4 py-3">{{ formatNumber($item->total) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
