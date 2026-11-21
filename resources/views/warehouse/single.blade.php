<x-app-layout :title="__('Shop warehouse')">
    <x-show-message-bags />

    <div class="flex flex-wrap items-center justify-between gap-4 px-1 pb-5">
        <div>
            <h1 class="text-xl font-bold text-base-content">{{ $warehouse->name }}</h1>
            <p class="mt-0.5 text-sm text-base-content/50">
                {{ __('All shop products are kept in this single warehouse.') }}
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('products.index') }}" class="btn btn-sm btn-ghost">{{ __('Products') }}</a>
            <a href="{{ route('products.report') }}" class="btn btn-sm btn-primary">{{ __('Warehouse report') }}</a>
        </div>
    </div>

    <div class="mx-1 mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="card border border-base-200 bg-base-100 shadow-sm">
            <div class="card-body py-4">
                <span class="text-sm text-base-content/60">{{ __('Products') }}</span>
                <span class="text-2xl font-bold">{{ localizeNumber($summary['products']) }}</span>
            </div>
        </div>
        <div class="card border border-base-200 bg-base-100 shadow-sm">
            <div class="card-body py-4">
                <span class="text-sm text-base-content/60">{{ __('Accounting stock') }}</span>
                <span class="text-2xl font-bold">{{ formatNumber($summary['quantity']) }}</span>
            </div>
        </div>
        <div class="card border border-base-200 bg-base-100 shadow-sm">
            <div class="card-body py-4">
                <span class="text-sm text-base-content/60">{{ __('Available on the site') }}</span>
                <span class="text-2xl font-bold text-success">{{ localizeNumber($summary['in_stock']) }}</span>
                <span class="text-xs text-base-content/50">{{ __('Out of stock on the site') }}: {{ localizeNumber($summary['out_of_stock']) }}</span>
            </div>
        </div>
        <div class="card border border-base-200 bg-base-100 shadow-sm">
            <div class="card-body py-4">
                <span class="text-sm text-base-content/60">{{ __('Products without purchase price') }}</span>
                <span class="text-2xl font-bold {{ $summary['missing_purchase_price'] > 0 ? 'text-warning' : '' }}">{{ localizeNumber($summary['missing_purchase_price']) }}</span>
            </div>
        </div>
    </div>

    <div class="card mx-1 border border-base-200 bg-base-100 shadow-sm">
        <div class="card-body gap-4">
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <label class="form-control">
                    <span class="label-text text-xs">{{ __('Search') }}</span>
                    <input type="search" name="search" value="{{ $search }}" class="input input-bordered input-sm" placeholder="{{ __('Product name or code') }}">
                </label>
                <button type="submit" class="btn btn-sm btn-primary">{{ __('Search') }}</button>
                @if ($search !== '')
                    <a href="{{ route('warehouse.single') }}" class="btn btn-sm btn-ghost">{{ __('Clear') }}</a>
                @endif
            </form>

            <div class="overflow-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th class="px-4 py-3">{{ __('Product') }}</th>
                            <th class="px-4 py-3">{{ __('Product group') }}</th>
                            <th class="px-4 py-3">{{ __('Accounting stock') }}</th>
                            <th class="px-4 py-3">{{ __('Website stock') }}</th>
                            <th class="px-4 py-3">{{ __('Purchase price') }}</th>
                            <th class="px-4 py-3">{{ __('Sale price') }}</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr>
                                <td class="px-4 py-3">
                                    <a href="{{ route('products.show', $product) }}" class="link link-primary font-semibold">{{ $product->name }}</a>
                                    <span class="block text-xs text-base-content/40" dir="ltr">{{ $product->code }}</span>
                                </td>
                                <td class="px-4 py-3">{{ $product->productGroup?->name ?: '—' }}</td>
                                <td class="px-4 py-3">{{ formatNumber($product->quantity) }}</td>
                                <td class="px-4 py-3">
                                    @if (is_null($product->website_stock))
                                        <span class="badge badge-ghost badge-sm">{{ __('Not announced') }}</span>
                                    @elseif ((float) $product->website_stock > 0)
                                        <span class="badge badge-success badge-sm">{{ formatNumber($product->website_stock) }}</span>
                                    @else
                                        <span class="badge badge-error badge-sm">{{ __('Out of stock') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 {{ (float) $product->purchase_price <= 0 ? 'text-warning' : '' }}">
                                    {{ (float) $product->purchase_price > 0 ? formatNumber($product->purchase_price) : __('Not set') }}
                                </td>
                                <td class="px-4 py-3">{{ formatNumber($product->selling_price) }}</td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('products.show', $product) }}" class="btn btn-xs btn-ghost">{{ __('View') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-base-content/50">{{ __('No products found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($products->hasPages())
                <div class="border-t border-base-200 pt-3">{{ $products->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
