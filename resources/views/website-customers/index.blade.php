<x-app-layout :title="__('Site customers')">
    <x-show-message-bags />

    <div class="flex flex-wrap items-center justify-between gap-4 px-1 pb-5">
        <div>
            <h1 class="text-xl font-bold text-base-content">{{ __('Site customers') }}</h1>
            <p class="mt-0.5 text-sm text-base-content/50">{{ __('Customers read directly from your WooCommerce store.') }}</p>
        </div>
        <a href="{{ route('website-orders.index') }}" class="btn btn-sm btn-ghost">{{ __('Site orders') }}</a>
    </div>

    <div class="mx-1 mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="card border border-base-200 bg-base-100 shadow-sm">
            <div class="card-body py-4">
                <span class="text-sm text-base-content/60">{{ __('Site customers') }}</span>
                <span class="text-2xl font-bold">{{ localizeNumber($totalCustomers) }}</span>
            </div>
        </div>
        <div class="card border border-base-200 bg-base-100 shadow-sm">
            <div class="card-body py-4">
                <span class="text-sm text-base-content/60">{{ __('Total customer purchases (Toman)') }}</span>
                <span class="text-2xl font-bold">{{ formatNumber($totalSpent) }}</span>
            </div>
        </div>
    </div>

    <div class="card mx-1 border border-base-200 bg-base-100 shadow-sm">
        <div class="card-body gap-4">
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <label class="form-control">
                    <span class="label-text text-xs">{{ __('Search') }}</span>
                    <input type="search" name="search" value="{{ $search }}" class="input input-bordered input-sm" placeholder="{{ __('Name, phone or email') }}">
                </label>
                <button type="submit" class="btn btn-sm btn-primary">{{ __('Search') }}</button>
                @if ($search !== '')
                    <a href="{{ route('website-customers.index') }}" class="btn btn-sm btn-ghost">{{ __('Clear') }}</a>
                @endif
            </form>

            <div class="overflow-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th class="px-4 py-3">{{ __('Name') }}</th>
                            <th class="px-4 py-3">{{ __('Phone') }}</th>
                            <th class="px-4 py-3">{{ __('Email') }}</th>
                            <th class="px-4 py-3">{{ __('City') }}</th>
                            <th class="px-4 py-3">{{ __('Orders') }}</th>
                            <th class="px-4 py-3">{{ __('Total purchases (Toman)') }}</th>
                            <th class="px-4 py-3">{{ __('Registered at') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($customers as $customer)
                            <tr>
                                <td class="px-4 py-3 font-semibold">{{ $customer->name ?: '-' }}</td>
                                <td class="px-4 py-3" dir="ltr">{{ $customer->phone ?: '-' }}</td>
                                <td class="px-4 py-3 text-sm" dir="ltr">{{ $customer->email ?: '-' }}</td>
                                <td class="px-4 py-3">{{ $customer->city ?: '-' }}</td>
                                <td class="px-4 py-3">{{ localizeNumber($customer->orders_count) }}</td>
                                <td class="px-4 py-3">{{ formatNumber($customer->total_spent) }}</td>
                                <td class="px-4 py-3 text-sm">{{ $customer->date_created ? convertToJalali($customer->date_created, true) : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-base-content/50">{{ __('No site customers have been synchronized yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($customers->hasPages())
                <div class="border-t border-base-200 pt-3">{{ $customers->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
