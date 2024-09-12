<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Product;
use App\Models\Table;
use App\Models\TimeOrderTable;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {

        $userCount = User::count();
        $productCount = Product::count();
        $orderCount = TimeOrderTable::count();
        $tableCount = Table::count();
        $billCount = Bill::count();

        return response()->json([
            'users' => $userCount,
            'products' => $productCount,
            'orders' => $orderCount,
            'tables' => $tableCount,
            'bills' => $billCount,
        ]);
        

    }
}
