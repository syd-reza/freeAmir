<?php

namespace App\Http\Controllers;

use App\Models\WebsiteCustomer;
use App\Models\WebsiteOrder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WebsiteOrderController extends Controller
{
    public function index(Request $request): View
    {
        $statuses = WebsiteOrder::statusOptions();
        $selectedStatus = $request->string('status')->toString();
        $search = trim($request->string('search')->toString());
        $query = WebsiteOrder::query();

        if (array_key_exists($selectedStatus, $statuses)) {
            $query->where('status', $selectedStatus);
        }

        if ($search !== '') {
            $like = '%'.$search.'%';
            $query->where(function ($builder) use ($like): void {
                $builder->where('number', 'like', $like)
                    ->orWhere('billing_name', 'like', $like)
                    ->orWhere('billing_phone', 'like', $like);
            });
        }

        return view('website-orders.index', [
            'orders' => $query->orderByDesc('date_created')->paginate(25)->withQueryString(),
            'statuses' => $statuses,
            'selectedStatus' => $selectedStatus,
            'search' => $search,
            'totalOrders' => WebsiteOrder::query()->count(),
            'paidSales' => (float) WebsiteOrder::query()->paid()->sum('total'),
            'paidOrders' => WebsiteOrder::query()->paid()->count(),
            'customersCount' => WebsiteCustomer::query()->count(),
        ]);
    }

    public function show(WebsiteOrder $websiteOrder): View
    {
        $websiteOrder->load(['items.product', 'customer']);

        return view('website-orders.show', [
            'order' => $websiteOrder,
        ]);
    }
}
