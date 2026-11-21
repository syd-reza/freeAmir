<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\WebsiteCustomer;
use App\Models\WebsiteOrder;
use App\Services\WooCommerceStoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class StoreSyncController extends Controller
{
    public function index(WooCommerceStoreService $service): View
    {
        return view('store-sync.index', [
            'baseUrl' => $service->baseUrl(),
            'apiUrl' => $service->storeApiUrl(),
            'lastSync' => Cache::get('store-sync:last'),
            'syncedProducts' => Product::query()->whereNotNull('website_id')->count(),
            'totalProducts' => Product::query()->count(),
            'ordersConfigured' => $service->hasApiCredentials(),
            'syncedOrders' => WebsiteOrder::query()->count(),
            'syncedCustomers' => WebsiteCustomer::query()->count(),
            'lastOrderSync' => Cache::get('store-orders-sync:last'),
            'lastCustomerSync' => Cache::get('store-customers-sync:last'),
        ]);
    }

    public function store(WooCommerceStoreService $service): RedirectResponse
    {
        try {
            $summary = $service->sync(
                createMissing: (bool) config('woocommerce.create_missing_products', true),
                maxPages: 50,
            );
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with('error', __('Store sync failed: :message', ['message' => $exception->getMessage()]));
        }

        return back()->with('success', __('Store sync finished: :created created, :updated updated, :skipped skipped.', [
            'created' => $summary['created'],
            'updated' => $summary['updated'],
            'skipped' => $summary['skipped'],
        ]));
    }

    public function storeOrders(WooCommerceStoreService $service): RedirectResponse
    {
        try {
            $orders = $service->syncOrders(maxPages: 50);
            $customers = $service->syncCustomers(maxPages: 20);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with('error', __('Orders and customers sync failed: :message', ['message' => $exception->getMessage()]));
        }

        return back()->with('success', __('Orders and customers sync finished: :orders orders, :customers customers.', [
            'orders' => $orders['total'],
            'customers' => $customers['total'],
        ]));
    }
}
