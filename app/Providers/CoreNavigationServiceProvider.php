<?php

namespace App\Providers;

use App\Extensions\MenuItem;
use App\Extensions\NavigationRegistry;
use Illuminate\Support\ServiceProvider;

class CoreNavigationServiceProvider extends ServiceProvider
{
    public function boot(NavigationRegistry $navigation): void
    {
        foreach ($this->definitions() as $definition) {
            $navigation->register(new MenuItem(...$definition));
        }
    }

    /** @return array<int, array<string, mixed>> */
    private function definitions(): array
    {
        $items = [];
        $group = static function (string $id, string $label, ?string $parent, int $priority, array $permissions = []) use (&$items): void {
            $items[] = compact('id', 'label', 'parent', 'priority', 'permissions');
        };
        $link = static function (string $id, string $label, string $route, ?string $parent, int $priority, array $permissions = [], array $routeParameters = []) use (&$items): void {
            $items[] = compact('id', 'label', 'route', 'parent', 'priority', 'permissions', 'routeParameters');
        };

        $link('core.home', 'Home', 'home', null, 100, ['home.*']);

        $group('core.store', 'Store', null, 150, ['invoices.create', 'store-sync.index', 'investors.index', 'profit-settlements.index']);
        $link('core.store.manual-order', 'Register manual order', 'invoices.create', 'core.store', 100, ['invoices.create'], ['invoice_type' => 'sell']);
        $link('core.store.sync', 'Store sync', 'store-sync.index', 'core.store', 200, ['store-sync.index']);
        $link('core.store.orders', 'Site orders', 'website-orders.index', 'core.store', 250, ['store-sync.index']);
        $link('core.store.customers', 'Site customers', 'website-customers.index', 'core.store', 260, ['store-sync.index']);
        $link('core.store.investors', 'Investors', 'investors.index', 'core.store', 300, ['investors.index']);
        $link('core.store.settlements', 'Weekly profit settlements', 'profit-settlements.index', 'core.store', 400, ['profit-settlements.index']);

        $group('core.invoices', 'Invoices', null, 300, [
            'invoices.dashboard', 'invoices.index', 'invoices.inactive', 'customers.create',
            'ancillary-costs.index', 'invoices.moadian-histories.index',
        ]);
        $link('core.invoices.dashboard', 'Invoice Dashboard', 'invoices.dashboard', 'core.invoices', 100, ['invoices.dashboard']);
        $group('core.invoices.sales', 'Sales', 'core.invoices', 200, ['invoices.index', 'invoices.moadian-histories.index']);
        $link('core.invoices.sales.sell', 'Sell List', 'invoices.index', 'core.invoices.sales', 100, ['invoices.index'], ['invoice_type' => 'sell']);
        $group('core.invoices.purchases', 'Purchases', 'core.invoices', 300, ['invoices.index']);
        $link('core.invoices.purchases.buy', 'Buy List', 'invoices.index', 'core.invoices.purchases', 100, ['invoices.index'], ['invoice_type' => 'buy']);

        $group('core.accounting', 'Accounting', null, 400, ['documents.index', 'documents.create', 'cheques.index']);
        $link('core.accounting.create-document', 'Create Document', 'documents.create', 'core.accounting', 100, ['documents.create']);
        $link('core.accounting.documents', 'Document List', 'documents.index', 'core.accounting', 200, ['documents.index']);
        $link('core.accounting.cheques', 'Cheque Management', 'cheques.index', 'core.accounting', 300, ['cheques.index']);

        $group('core.reports', 'Reports', null, 500, ['reports.*', 'budgets.*', 'commercial-ledgers.*', 'profit-settlements.*', 'store-sync.*', 'salary.payrolls.*']);
        $link('core.reports.overview', 'Reports overview', 'reports.index', 'core.reports', 50, ['reports.*']);
        $link('core.reports.site-sales', 'Site sales report', 'website-orders.index', 'core.reports', 100, ['store-sync.*']);
        $link('core.reports.weekly-profit', 'Weekly profit report', 'profit-settlements.index', 'core.reports', 150, ['profit-settlements.*']);
        $link('core.reports.team-payments', 'Team payment report', 'salary.payrolls.team-payments', 'core.reports', 175, ['salary.payrolls.*']);
        $link('core.reports.company-overview', 'Company overview', 'reports.company-overview', 'core.reports', 200, ['reports.*']);
        $link('core.reports.cost-income', 'Cost and Income Dashboard', 'reports.cost-income', 'core.reports', 300, ['reports.*']);
        $link('core.reports.monthly-workbench', 'Monthly Income and Expense Workbench', 'budgets.index', 'core.reports', 400, ['budgets.*']);
        $link('core.reports.commercial-ledgers', 'Commercial Ledgers', 'commercial-ledgers.index', 'core.reports', 500, ['commercial-ledgers.*']);
        $group('core.reports.accounting', 'Accounting', 'core.reports', 600, ['reports.*']);
        foreach ([
            ['documents', 'Documents Report', 'reports.documents'],
            ['journal', 'Journal', 'reports.journal'],
            ['ledger', 'Ledger', 'reports.ledger'],
            ['sub-ledger', 'Sub Ledger', 'reports.sub-ledger'],
            ['trial-balance', 'Trial Balance', 'reports.trial-balance'],
            ['inventory-turnover', 'Inventory Turnover', 'reports.inventory-turnover'],
        ] as $index => [$id, $label, $route]) {
            $link("core.reports.accounting.$id", $label, $route, 'core.reports.accounting', ($index + 1) * 100, ['reports.*']);
        }

        $group('core.warehouse', 'Warehouse', null, 600, ['warehouse.*', 'products.*', 'product-groups.*', 'services.*', 'service-groups.*']);
        $link('core.warehouse.stock', 'Shop warehouse', 'warehouse.single', 'core.warehouse', 100, ['warehouse.*']);
        foreach ([
            ['products', 'Products', 'products.index'], ['product-groups', 'Product Groups', 'product-groups.index'],
            ['services', 'Services', 'services.index'], ['service-groups', 'Service Groups', 'service-groups.index'],
        ] as $index => [$id, $label, $route]) {
            $permission = match ($id) {
                'products' => 'products.*',
                'product-groups' => 'product-groups.*',
                'services' => 'services.*',
                'service-groups' => 'service-groups.*',
            };
            $link("core.warehouse.$id", $label, $route, 'core.warehouse', ($index + 2) * 100, [$permission]);
        }

        $group('core.payroll', 'Payroll', null, 700, ['salary.payrolls.*', 'hr.employees.*']);
        $link('core.payroll.dashboard', 'Payroll dashboard', 'salary.payrolls.dashboard', 'core.payroll', 100, ['salary.payrolls.*']);
        $link('core.payroll.list', 'Payrolls', 'salary.payrolls.index', 'core.payroll', 200, ['salary.payrolls.*']);
        $link('core.payroll.team-payments', 'Team payments', 'salary.payrolls.team-payments', 'core.payroll', 300, ['salary.payrolls.*']);
        $link('core.payroll.members', 'Team members', 'hr.employees.index', 'core.payroll', 400, ['hr.employees.*']);

        $group('core.crm', 'CRM', null, 800, ['crm.dashboard', 'customers.index', 'customer-groups.index']);
        foreach ([
            ['dashboard', 'CRM Dashboard', 'crm.dashboard'], ['customers', 'Customers', 'customers.index'], ['customer-groups', 'Customer Groups', 'customer-groups.index'],
        ] as $index => [$id, $label, $route]) {
            $link("core.crm.$id", $label, $route, 'core.crm', ($index + 1) * 100, [$route]);
        }

        $group('core.management', 'Management', null, 900);
        $group('core.management.finance', 'Finance', 'core.management', 200, ['bank-accounts.index', 'banks.index', 'chequebooks.index', 'subjects.index', 'documents.sort-numbers']);
        foreach ([
            ['subjects', 'Subjects', 'subjects.index'], ['bank-accounts', 'Bank Accounts', 'bank-accounts.index'], ['banks', 'Banks', 'banks.index'],
            ['chequebooks', 'Chequebooks', 'chequebooks.index'], ['sort-documents', 'Sort Documents Number', 'documents.sort-numbers'],
        ] as $index => [$id, $label, $route]) {
            $link("core.management.finance.$id", $label, $route, 'core.management.finance', ($index + 1) * 100, [$route]);
        }
        $group('core.management.system', 'System', 'core.management', 300, ['fiscal-years.index', 'users.index', 'configs.index', 'backups.create']);
        foreach ([
            ['fiscal-years', 'Fiscal years', 'fiscal-years.index', ['fiscal-years.index']],
            ['users', 'Users', 'users.index', ['users.index']], ['configs', 'Configs', 'configs.index', ['configs.index']],
            ['backup', 'Backup', 'backups.create', ['backups.create']], ['upload-backup', 'Upload Backup', 'backups.upload', ['backups.create']],
        ] as $index => [$id, $label, $route, $permissions]) {
            $link("core.management.system.$id", $label, $route, 'core.management.system', ($index + 1) * 100, $permissions);
        }
        $link('core.management.about', 'about.about', 'about', 'core.management', 500);

        $hiddenSections = ['core.accounting', 'core.management', 'core.reports.accounting'];
        $items = array_values(array_filter($items, static function (array $definition) use ($hiddenSections): bool {
            foreach ($hiddenSections as $section) {
                if ($definition['id'] === $section || str_starts_with($definition['id'], $section.'.')) {
                    return false;
                }
            }

            return true;
        }));

        return array_map(static function (array $definition): array {
            $definition['permissionMode'] = 'any';
            $definition['visible'] = null;

            return $definition;
        }, $items);
    }
}
