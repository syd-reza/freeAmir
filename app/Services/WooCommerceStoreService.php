<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\WebsiteCustomer;
use App\Models\WebsiteOrder;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class WooCommerceStoreService
{
    public function __construct(private readonly ProductService $productService) {}

    public function baseUrl(): string
    {
        return rtrim((string) config('woocommerce.base_url'), '/');
    }

    public function storeApiUrl(): string
    {
        return $this->baseUrl().'/wp-json/wc/store/products';
    }

    /**
     * Fetch one page of products from the public WooCommerce Store API.
     *
     * @return array<int, array<string, mixed>>
     */
    public function fetchPage(int $page = 1): array
    {
        $options = [];
        $caBundle = config('woocommerce.ca_bundle');

        if (is_string($caBundle) && is_file($caBundle)) {
            $options['verify'] = $caBundle;
        }

        $request = Http::acceptJson()->timeout(30)->retry(2, 500)->withOptions($options);

        if (config('woocommerce.consumer_key') && config('woocommerce.consumer_secret')) {
            $request = $request->withBasicAuth(
                (string) config('woocommerce.consumer_key'),
                (string) config('woocommerce.consumer_secret')
            );
        }

        $response = $request->get($this->storeApiUrl(), [
            'per_page' => max(1, min(100, (int) config('woocommerce.per_page', 100))),
            'page' => $page,
        ]);

        if ($response->failed()) {
            throw new RuntimeException(__('The store product API returned :status.', ['status' => $response->status()]));
        }

        $products = $response->json();

        if (! is_array($products)) {
            throw new RuntimeException(__('The store product API returned an unexpected response.'));
        }

        return $products;
    }

    /**
     * Synchronise the store catalogue into the current fiscal year products.
     *
     * Accounting stock (quantity / warehouse stocks) is intentionally never
     * overwritten: the website stock is stored separately so invoices and
     * inventory cost flows stay consistent.
     *
     * @return array{created: int, updated: int, skipped: int, pages: int, total: int, errors: array<int, string>}
     */
    public function sync(bool $createMissing = true, int $maxPages = 50): array
    {
        $summary = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'pages' => 0, 'total' => 0, 'errors' => []];
        $group = $createMissing ? $this->defaultGroup() : null;

        for ($page = 1; $page <= $maxPages; $page++) {
            $items = $this->fetchPage($page);

            if ($items === []) {
                break;
            }

            $summary['pages']++;
            $summary['total'] += count($items);

            foreach ($items as $item) {
                try {
                    $result = $this->syncProduct($item, $createMissing, $group);
                    $summary[$result]++;
                } catch (\Throwable $exception) {
                    $summary['skipped']++;
                    $summary['errors'][] = ($item['name'] ?? '#'.$page).': '.$exception->getMessage();
                }
            }

            if (count($items) < (int) config('woocommerce.per_page', 100)) {
                break;
            }
        }

        $summary['finished_at'] = now()->toDateTimeString();
        Cache::put('store-sync:last', $summary, now()->addYear());

        return $summary;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return 'created'|'updated'|'skipped'
     */
    public function syncProduct(array $data, bool $createMissing, ?ProductGroup $group): string
    {
        $websiteId = isset($data['id']) ? (int) $data['id'] : 0;
        $sku = trim((string) ($data['sku'] ?? ''));
        $link = isset($data['permalink']) ? (string) $data['permalink'] : null;
        $name = trim(html_entity_decode(strip_tags((string) ($data['name'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        if ($websiteId === 0 || $name === '') {
            return 'skipped';
        }

        $product = Product::query()->where('website_id', $websiteId)->first();

        if (! $product && $link) {
            $product = Product::query()
                ->whereHas('productWebsites', fn ($query) => $query->where('link', $link))
                ->first();
        }

        if (! $product && $sku !== '') {
            $product = Product::query()->where('code', mb_substr($sku, 0, 20))->first();
        }

        $price = $this->price($data);
        $stock = $this->stock($data);

        if (! $product) {
            if (! $createMissing || ! $group) {
                return 'skipped';
            }

            $product = $this->productService->create([
                'code' => $this->availableCode($sku, $websiteId),
                'name' => mb_substr($name, 0, 60),
                'group' => $group->id,
                'quantity' => 0,
                'quantity_warning' => 0,
                'oversell' => 0,
                'selling_price' => $price,
                'vat' => (float) ($group->vat ?? config('woocommerce.sync_vat_rate', 10)),
                'description' => mb_substr(trim(strip_tags((string) ($data['short_description'] ?? ''))), 0, 150),
                'website_id' => $websiteId,
                'purchase_price' => 0,
                'website_price' => $price,
                'website_stock' => $stock,
                'website_synced_at' => now(),
                'websites' => $link ? [['link' => $link]] : [],
            ]);

            $this->rememberLink($product, $link);

            return 'created';
        }

        $product->forceFill([
            'website_id' => $websiteId,
            'name' => mb_substr($name, 0, 60),
            'selling_price' => $price,
            'website_price' => $price,
            'website_stock' => $stock,
            'website_synced_at' => now(),
        ])->save();

        if ($link) {
            $this->rememberLink($product, $link);
        }

        return 'updated';
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function price(array $data): float
    {
        $prices = $data['prices'] ?? [];
        $minorUnit = (int) ($prices['currency_minor_unit'] ?? 0);
        $raw = (float) ($prices['price'] ?? 0);

        return round($raw / (10 ** max(0, $minorUnit)), 2);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function stock(array $data): ?float
    {
        if (array_key_exists('is_in_stock', $data) && ! $data['is_in_stock']) {
            return 0.0;
        }

        $quantity = $data['stock_quantity'] ?? null;

        return is_numeric($quantity) ? (float) $quantity : null;
    }

    /**
     * Authenticated WooCommerce REST endpoint URL (wc/v3).
     */
    public function apiUrl(string $endpoint): string
    {
        return $this->baseUrl().'/wp-json/wc/v3/'.ltrim($endpoint, '/');
    }

    public function hasApiCredentials(): bool
    {
        return filled(config('woocommerce.consumer_key')) && filled(config('woocommerce.consumer_secret'));
    }

    private function apiRequest(): PendingRequest
    {
        if (! $this->hasApiCredentials()) {
            throw new RuntimeException(__('WooCommerce REST API keys are not configured.'));
        }

        $options = [];
        $caBundle = config('woocommerce.ca_bundle');

        if (is_string($caBundle) && is_file($caBundle)) {
            $options['verify'] = $caBundle;
        }

        return Http::acceptJson()
            ->timeout(30)
            ->retry(2, 500)
            ->withOptions($options)
            ->withBasicAuth(
                (string) config('woocommerce.consumer_key'),
                (string) config('woocommerce.consumer_secret')
            );
    }

    /**
     * Fetch one page of orders from the authenticated WooCommerce REST API.
     *
     * @return array<int, array<string, mixed>>
     */
    public function fetchOrders(int $page = 1, int $perPage = 100, ?string $after = null): array
    {
        $query = [
            'per_page' => max(1, min(100, $perPage)),
            'page' => max(1, $page),
            'orderby' => 'date',
            'order' => 'asc',
        ];

        if ($after) {
            $query['after'] = $after;
        }

        $response = $this->apiRequest()->get($this->apiUrl('orders'), $query);

        if ($response->failed()) {
            throw new RuntimeException(__('The store order API returned :status.', ['status' => $response->status()]));
        }

        $orders = $response->json();

        if (! is_array($orders)) {
            throw new RuntimeException(__('The store order API returned an unexpected response.'));
        }

        return $orders;
    }

    /**
     * Fetch one page of customers from the authenticated WooCommerce REST API.
     *
     * @return array<int, array<string, mixed>>
     */
    public function fetchCustomers(int $page = 1, int $perPage = 100): array
    {
        $response = $this->apiRequest()->get($this->apiUrl('customers'), [
            'per_page' => max(1, min(100, $perPage)),
            'page' => max(1, $page),
            'orderby' => 'registered_date',
            'order' => 'asc',
        ]);

        if ($response->failed()) {
            throw new RuntimeException(__('The store customer API returned :status.', ['status' => $response->status()]));
        }

        $customers = $response->json();

        if (! is_array($customers)) {
            throw new RuntimeException(__('The store customer API returned an unexpected response.'));
        }

        return $customers;
    }

    /**
     * Mirror website orders and their line items into local reporting tables.
     *
     * Orders are stored separately from accounting invoices so the books stay
     * untouched until an order is explicitly turned into an invoice.
     *
     * @return array{created: int, updated: int, items: int, pages: int, total: int, errors: array<int, string>, finished_at?: string}
     */
    public function syncOrders(int $maxPages = 50, ?string $after = null): array
    {
        $summary = ['created' => 0, 'updated' => 0, 'items' => 0, 'pages' => 0, 'total' => 0, 'errors' => []];
        $productMap = Product::query()->whereNotNull('website_id')->pluck('id', 'website_id');
        $perPage = max(1, min(100, (int) config('woocommerce.per_page', 100)));

        for ($page = 1; $page <= $maxPages; $page++) {
            $orders = $this->fetchOrders($page, $perPage, $after);

            if ($orders === []) {
                break;
            }

            $summary['pages']++;
            $summary['total'] += count($orders);

            foreach ($orders as $order) {
                try {
                    $result = $this->syncOrder($order, $productMap->all());
                    $summary[$result['state']]++;
                    $summary['items'] += $result['items'];
                } catch (\Throwable $exception) {
                    $summary['errors'][] = 'Order #'.($order['id'] ?? '?').': '.$exception->getMessage();
                }
            }

            if (count($orders) < $perPage) {
                break;
            }
        }

        $summary['finished_at'] = now()->toDateTimeString();
        Cache::put('store-orders-sync:last', $summary, now()->addYear());

        return $summary;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, int>  $productMap
     * @return array{state: 'created'|'updated', items: int}
     */
    public function syncOrder(array $data, array $productMap): array
    {
        $websiteId = (int) ($data['id'] ?? 0);

        if ($websiteId === 0) {
            throw new RuntimeException('Order has no website id.');
        }

        $billing = is_array($data['billing'] ?? null) ? $data['billing'] : [];
        $name = trim((string) ($billing['first_name'] ?? '').' '.(string) ($billing['last_name'] ?? ''));
        $order = WebsiteOrder::query()->firstOrNew(['website_id' => $websiteId]);
        $state = $order->exists ? 'updated' : 'created';

        $order->fill([
            'number' => (string) ($data['number'] ?? $websiteId),
            'status' => (string) ($data['status'] ?? 'unknown'),
            'currency' => (string) ($data['currency'] ?? 'IRT'),
            'total' => (float) ($data['total'] ?? 0),
            'discount_total' => (float) ($data['discount_total'] ?? 0),
            'shipping_total' => (float) ($data['shipping_total'] ?? 0),
            'total_tax' => (float) ($data['total_tax'] ?? 0),
            'payment_method' => $data['payment_method'] ?? null,
            'payment_method_title' => $data['payment_method_title'] ?? null,
            'website_customer_id' => ((int) ($data['customer_id'] ?? 0)) ?: null,
            'billing_name' => $name !== '' ? mb_substr($name, 0, 150) : null,
            'billing_phone' => $billing['phone'] ?? null,
            'billing_email' => $billing['email'] ?? null,
            'billing_city' => $billing['city'] ?? null,
            'date_created' => $data['date_created'] ?? null,
            'date_paid' => $data['date_paid'] ?? null,
            'synced_at' => now(),
        ])->save();

        $order->items()->delete();
        $itemCount = 0;

        foreach (($data['line_items'] ?? []) as $item) {
            if (! is_array($item)) {
                continue;
            }

            $websiteProductId = (int) ($item['product_id'] ?? 0);
            $itemName = trim((string) ($item['name'] ?? ''));
            $order->items()->create([
                'website_product_id' => $websiteProductId ?: null,
                'product_id' => $websiteProductId ? ($productMap[$websiteProductId] ?? null) : null,
                'name' => mb_substr(html_entity_decode(strip_tags($itemName), ENT_QUOTES | ENT_HTML5, 'UTF-8'), 0, 200),
                'sku' => mb_substr((string) ($item['sku'] ?? ''), 0, 80),
                'quantity' => (float) ($item['quantity'] ?? 0),
                'total' => (float) ($item['total'] ?? 0),
            ]);
            $itemCount++;
        }

        $order->forceFill(['item_count' => $itemCount])->save();

        return ['state' => $state, 'items' => $itemCount];
    }

    /**
     * Mirror website customers into a local reporting table.
     *
     * @return array{created: int, updated: int, pages: int, total: int, errors: array<int, string>}
     */
    public function syncCustomers(int $maxPages = 20): array
    {
        $summary = ['created' => 0, 'updated' => 0, 'pages' => 0, 'total' => 0, 'errors' => []];
        $perPage = max(1, min(100, (int) config('woocommerce.per_page', 100)));

        for ($page = 1; $page <= $maxPages; $page++) {
            $customers = $this->fetchCustomers($page, $perPage);

            if ($customers === []) {
                break;
            }

            $summary['pages']++;
            $summary['total'] += count($customers);

            foreach ($customers as $customer) {
                $websiteId = (int) ($customer['id'] ?? 0);

                if ($websiteId === 0) {
                    continue;
                }

                try {
                    $billing = is_array($customer['billing'] ?? null) ? $customer['billing'] : [];
                    $name = trim((string) ($customer['first_name'] ?? '').' '.(string) ($customer['last_name'] ?? ''));
                    $model = WebsiteCustomer::query()->firstOrNew(['website_id' => $websiteId]);
                    $state = $model->exists ? 'updated' : 'created';
                    $model->fill([
                        'name' => $name !== '' ? mb_substr($name, 0, 150) : mb_substr((string) ($billing['company'] ?? ''), 0, 150),
                        'email' => $customer['email'] ?? null,
                        'phone' => $billing['phone'] ?? null,
                        'city' => $billing['city'] ?? null,
                        'orders_count' => (int) ($customer['orders_count'] ?? 0),
                        'total_spent' => (float) ($customer['total_spent'] ?? 0),
                        'date_created' => $customer['date_created'] ?? null,
                        'synced_at' => now(),
                    ])->save();
                    $summary[$state]++;
                } catch (\Throwable $exception) {
                    $summary['errors'][] = 'Customer #'.$websiteId.': '.$exception->getMessage();
                }
            }

            if (count($customers) < $perPage) {
                break;
            }
        }

        $summary['finished_at'] = now()->toDateTimeString();
        Cache::put('store-customers-sync:last', $summary, now()->addYear());

        return $summary;
    }

    private function defaultGroup(): ProductGroup
    {
        return ProductGroup::query()->firstOrCreate([
            'name' => (string) config('woocommerce.default_group', 'کالاهای فروشگاه'),
            'fiscal_year_id' => getActiveFiscalYear(),
        ]);
    }

    private function rememberLink(Product $product, ?string $link): void
    {
        if (! $link) {
            return;
        }

        $product->productWebsites()->firstOrCreate(['link' => $link]);
    }

    private function availableCode(string $sku, int $websiteId): string
    {
        $code = $sku !== '' ? mb_substr($sku, 0, 20) : 'WP-'.$websiteId;

        if (! Product::query()->where('code', $code)->exists()) {
            return $code;
        }

        return mb_substr($code, 0, 12).'-'.$websiteId;
    }
}
