<?php

namespace App\Console\Commands;

use App\Services\WooCommerceStoreService;
use Illuminate\Console\Command;

class SyncStoreOrdersCommand extends Command
{
    protected $signature = 'store:sync-orders {--pages=50 : Maximum number of API pages to read} {--after= : Only read orders created after this date (Y-m-d)}';

    protected $description = 'Import orders and customers from the WooCommerce store';

    public function handle(WooCommerceStoreService $service): int
    {
        if (! $service->hasApiCredentials()) {
            $this->error('WooCommerce REST API keys are not configured.');

            return self::FAILURE;
        }

        $this->info('Syncing orders and customers from '.$service->baseUrl());

        try {
            $orders = $service->syncOrders(
                maxPages: max(1, (int) $this->option('pages')),
                after: $this->option('after') ?: null,
            );
            $customers = $service->syncCustomers();
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());
            report($exception);

            return self::FAILURE;
        }

        $this->table(
            ['Created', 'Updated', 'Items', 'Pages', 'Orders'],
            [[$orders['created'], $orders['updated'], $orders['items'], $orders['pages'], $orders['total']]]
        );
        $this->line('Customers: '.$customers['total'].' (created '.$customers['created'].', updated '.$customers['updated'].')');

        foreach (array_slice($orders['errors'], 0, 10) as $error) {
            $this->warn($error);
        }

        return self::SUCCESS;
    }
}
