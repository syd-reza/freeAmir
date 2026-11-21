<?php

namespace App\Http\Controllers;

use App\Models\WebsiteCustomer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WebsiteCustomerController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());
        $query = WebsiteCustomer::query()->withCount('orders');

        if ($search !== '') {
            $like = '%'.$search.'%';
            $query->where(function ($builder) use ($like): void {
                $builder->where('name', 'like', $like)
                    ->orWhere('phone', 'like', $like)
                    ->orWhere('email', 'like', $like);
            });
        }

        return view('website-customers.index', [
            'customers' => $query->orderByDesc('total_spent')->paginate(30)->withQueryString(),
            'search' => $search,
            'totalCustomers' => WebsiteCustomer::query()->count(),
            'totalSpent' => (float) WebsiteCustomer::query()->sum('total_spent'),
        ]);
    }
}
