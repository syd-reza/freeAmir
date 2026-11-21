<?php

namespace App\Console\Commands;

use App\Services\WooCommerceStoreService;
use Illuminate\Console\Command;

class SyncStoreProductsCommand extends Command
{
    protected $signature = 'store:sync {--no-create : Only update products that already exist} {--pages=50 : Maximum number of API pages to read}';

    protected $description = 'Import prices and website stock from the WooCommerce store';

    public function handle(WooCommerceStoreService $service): int
    {
        $this->info('Syncing products from '.$service->baseUrl());

        try {
            $summary = $service->sync(
                createMissing: ! $this->option('no-create') && (bool) config('woocommerce.create_missing_products', true),
                maxPages: max(1, (int) $this->option('pages')),
            );
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());
            report($exception);

            return self::FAILURE;
        }

        $this->table(
            ['Created', 'Updated', 'Skipped', 'Pages', 'Products'],
            [[$summary['created'], $summary['updated'], $summary['skipped'], $summary['pages'], $summary['total']]]
        );

        foreach (array_slice($summary['errors'], 0, 10) as $error) {
            $this->warn($error);
        }

        return self::SUCCESS;
    }
}
