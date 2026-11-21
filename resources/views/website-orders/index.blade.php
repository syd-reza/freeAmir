<x-app-layout :title="__('Site orders')">
    <x-show-message-bags />

    <div class="flex flex-wrap items-center justify-between gap-4 px-1 pb-5">
        <div>
            <h1 class="text-xl font-bold text-base-content">{{ __('Site orders') }}</h1>
            <p class="mt-0.5 text-sm text-base-content/50">{{ __('Orders read directly from your WooCommerce store.') }}</p>
        </div>
        <form action="{{ route('store-sync.orders') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary btn-sm">{{ __('Sync orders and customers') }}</button>
        </form>
    </div>

    <div class="mx-1 mb-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="card border border-base-200 bg-base-100 shadow-sm">
            <div class="card-body py-4">
                <span class="text-sm text-base-content/60">{{ __('Site sales (Toman)') }}</span>
                <span class="text-2xl font-bold">{{ formatNumber($paidSales) }}</span>
                <span class="text-xs text-base-content/50">{{ localizeNumber($paidOrders) }} {{ __('paid orders') }}</span>
            </div>
        </div>
        <div class="card border border-base-200 bg-base-100 shadow-sm">
            <div class="card-body py-4">
                <span class="text-sm text-base-content/60">{{ __('All site orders') }}</span>
                <span class="text-2xl font-bold">{{ localizeNumber($totalOrders) }}</span>
            </div>
        </div>
        <div class="card border border-base-200 bg-base-100 shadow-sm">
            <div class="card-body py-4">
                <span class="text-sm text-base-content/60">{{ __('Site customers') }}</span>
                <span class="text-2xl font-bold">{{ localizeNumber($customersCount) }}</span>
                <a href="{{ route('website-customers.index') }}" class="link link-primary text-xs">{{ __('View customers') }}</a>
            </div>
        </div>
    </div>

    <div class="card mx-1 border border-base-200 bg-base-100 shadow-sm">
        <div class="card-body gap-4">
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <label class="form-control">
                    <span class="label-text text-xs">{{ __('Status') }}</span>
                    <select name="status" class="select select-bordered select-sm">
                        <option value="">{{ __('All') }}</option>
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="form-control">
                    <span class="label-text text-xs">{{ __('Search') }}</span>
                    <input type="search" name="search" value="{{ $search }}" class="input input-bordered input-sm" placeholder="{{ __('Order number, name or phone') }}">
                </label>
                <button type="submit" class="btn btn-sm btn-primary">{{ __('Filter') }}</button>
                @if ($search !== '' || $selectedStatus !== '')
                    <a href="{{ route('website-orders.index') }}" class="btn btn-sm btn-ghost">{{ __('Clear') }}</a>
                @endif
            </form>

            <div class="overflow-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th class="px-4 py-3">{{ __('Order number') }}</th>
                            <th class="px-4 py-3">{{ __('Date') }}</th>
                            <th class="px-4 py-3">{{ __('Customer') }}</th>
                            <th class="px-4 py-3">{{ __('Phone') }}</th>
                            <th class="px-4 py-3">{{ __('Items') }}</th>
                            <th class="px-4 py-3">{{ __('Amount (Toman)') }}</th>
                            <th class="px-4 py-3">{{ __('Status') }}</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr>
                                <td class="px-4 py-3 font-semibold" dir="ltr">#{{ $order->number }}</td>
                                <td class="px-4 py-3 text-sm">
                                    {{ $order->date_created ? convertToJalali($order->date_created, true) : '-' }}
                                    <span class="block text-xs text-base-content/45" dir="ltr">{{ $order->date_created?->format('H:i') }}</span>
                                </td>
                                <td class="px-4 py-3">{{ $order->billing_name ?: '-' }}</td>
                                <td class="px-4 py-3 text-sm" dir="ltr">{{ $order->billing_phone ?: '-' }}</td>
                                <td class="px-4 py-3">{{ localizeNumber($order->item_count) }}</td>
                                <td class="px-4 py-3 font-semibold">{{ formatNumber($order->total) }}</td>
                                <td class="px-4 py-3">
                                    <span @class(['badge badge-sm', $order->statusBadgeClass()])>{{ $order->statusLabel() }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('website-orders.show', $order) }}" class="btn btn-xs btn-info">{{ __('View') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-base-content/50">{{ __('No site orders have been synchronized yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($orders->hasPages())
                <div class="border-t border-base-200 pt-3">{{ $orders->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
