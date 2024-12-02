<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Product;
use App\Models\Table;
use App\Models\TimeOrderTable;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $currentDate = now();
        $startOfMonth = $currentDate->startOfMonth();

        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : null;
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : null;

        if ($startDate && !$endDate) {
            $endDate = $currentDate;
        } elseif (!$startDate && $endDate) {
            $startDate = Carbon::minValue();
        }

        $defaultLastMonthStart = $currentDate->copy()->subMonth()->startOfMonth();
        $defaultLastMonthEnd = $defaultLastMonthStart->copy()->endOfMonth();
        // $defaultCurrentMonthStart = $startOfMonth;
        // $defaultCurrentMonthEnd = $currentDate;
        $defaultCurrentMonthStart = now()->startOfMonth();
        $defaultCurrentMonthEnd = now();

        $statistics = function ($model, $startDate, $endDate) {
            if ($startDate && $endDate) {
                return $model::whereBetween('created_at', [$startDate, $endDate])->count();
            }
            return $model::count();
        };

        $userCount = User::count();
        $productCount = Product::count();
        $orderCount = TimeOrderTable::count();
        $tableCount = Table::count();
        $billCount = Bill::count();

        $filteredUserCount = $statistics(User::class, $startDate, $endDate);
        $filteredProductCount = $statistics(Product::class, $startDate, $endDate);
        $filteredOrderCount = $statistics(TimeOrderTable::class, $startDate, $endDate);
        $filteredTableCount = $statistics(Table::class, $startDate, $endDate);
        $filteredBillCount = $statistics(Bill::class, $startDate, $endDate);

        $lastMonthUserCount = $statistics(User::class, $defaultLastMonthStart, $defaultLastMonthEnd);
        $currentMonthUserCount = $statistics(User::class, $defaultCurrentMonthStart, $defaultCurrentMonthEnd);

        return response()->json([
            'total' => [
                'users' => $userCount,
                'products' => $productCount,
                'orders' => $orderCount,
                'tables' => $tableCount,
                'bills' => $billCount,
            ],
            'filtered' => [
                'users' => $filteredUserCount,
                'products' => $filteredProductCount,
                'orders' => $filteredOrderCount,
                'tables' => $filteredTableCount,
                'bills' => $filteredBillCount,
            ],
            'default' => [
                'last_month' => [
                    'users' => $lastMonthUserCount,
                    'products' => $statistics(Product::class, $defaultLastMonthStart, $defaultLastMonthEnd),
                    'orders' => $statistics(TimeOrderTable::class, $defaultLastMonthStart, $defaultLastMonthEnd),
                    'tables' => $statistics(Table::class, $defaultLastMonthStart, $defaultLastMonthEnd),
                    'bills' => $statistics(Bill::class, $defaultLastMonthStart, $defaultLastMonthEnd),
                ],
                'current_month' => [
                    'users' => $currentMonthUserCount,
                    'products' => $statistics(Product::class, $defaultCurrentMonthStart, $defaultCurrentMonthEnd),
                    'orders' => $statistics(TimeOrderTable::class, $defaultCurrentMonthStart, $defaultCurrentMonthEnd),
                    'tables' => $statistics(Table::class, $defaultCurrentMonthStart, $defaultCurrentMonthEnd),
                    'bills' => $statistics(Bill::class, $defaultCurrentMonthStart, $defaultCurrentMonthEnd),
                ],
            ],
        ]);
    }
}
