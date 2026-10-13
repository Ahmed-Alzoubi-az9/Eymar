<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        return view('dashboard', [
            'productsCount' => $user->products()->count(),
            'ordersCount' => $user->role === 'customer'
                ? $user->receivedOrders()->count()
                : $user->salesOrders()->count(),
            'requestsCount' => $user->customerServiceRequests()->count(),
            'quotesCount' => $user->quotes()->count(),
        ]);
    }
}
