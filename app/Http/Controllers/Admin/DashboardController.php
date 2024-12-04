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
        $startDate = $this->parseStartDate($request->input('start_date'));
        $endDate = $this->parseEndDate($request->input('end_date'), $startDate);

        $dateRanges = $this->getDefaultDateRanges($currentDate);

        return response()->json([
            'total' => $this->getTotalStatistics(),
            'filtered' => $this->getFilteredStatistics($startDate, $endDate),
            'default' => [
                'last_month' => $this->getStatisticsByRange($dateRanges['last_month']),
                'current_month' => $this->getStatisticsByRange($dateRanges['current_month']),
                'today' => $this->getStatisticsByRange($dateRanges['today']),
            ],
        ]);
    }

    private function parseStartDate($startDate)
    {
        return $startDate ? Carbon::parse($startDate) : null;
    }

    private function parseEndDate($endDate, $startDate)
    {
        if ($endDate) {
            return Carbon::parse($endDate);
        }

        return $startDate ? now() : null;
    }

    private function getDefaultDateRanges($currentDate)
    {
        $startOfMonth = $currentDate->copy()->startOfMonth();
        $todayStart = Carbon::today();
        $todayEnd = Carbon::now()->endOfDay();

        return [
            'last_month' => [
                'start' => $currentDate->copy()->subMonth()->startOfMonth(),
                'end' => $currentDate->copy()->subMonth()->endOfMonth(),
            ],
            'current_month' => [
                'start' => $startOfMonth,
                'end' => $currentDate,
            ],
            'today' => [
                'start' => $todayStart,
                'end' => $todayEnd,
            ],
        ];
    }

    private function getStatistics($model, $startDate = null, $endDate = null)
    {
        if ($startDate && $endDate) {
            return $model::whereBetween('created_at', [$startDate, $endDate])->count();
        }

        return $model::count();
    }

    private function getGuestStatistics($startDate = null, $endDate = null)
    {
        $query = Table::query();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return [
            'min_guest' => $query->sum('min_guest'),
            'max_guest' => $query->sum('max_guest'),
        ];
    }

    private function getTotalStatistics()
    {
        return [
            'users' => User::count(),
            'products' => Product::count(),
            'orders' => TimeOrderTable::count(),
            'tables' => Table::count(),
            'bills' => Bill::count(),
            'guests' => $this->getGuestStatistics(),
        ];
    }

    private function getFilteredStatistics($startDate, $endDate)
    {
        return [
            'users' => $this->getStatistics(User::class, $startDate, $endDate),
            'products' => $this->getStatistics(Product::class, $startDate, $endDate),
            'orders' => $this->getStatistics(TimeOrderTable::class, $startDate, $endDate),
            'tables' => $this->getStatistics(Table::class, $startDate, $endDate),
            'bills' => $this->getStatistics(Bill::class, $startDate, $endDate),
            'guests' => $this->getGuestStatistics($startDate, $endDate),
        ];
    }

    private function getStatisticsByRange($dateRange)
    {
        return [
            'users' => $this->getStatistics(User::class, $dateRange['start'], $dateRange['end']),
            'products' => $this->getStatistics(Product::class, $dateRange['start'], $dateRange['end']),
            'orders' => $this->getStatistics(TimeOrderTable::class, $dateRange['start'], $dateRange['end']),
            'tables' => $this->getStatistics(Table::class, $dateRange['start'], $dateRange['end']),
            'bills' => $this->getStatistics(Bill::class, $dateRange['start'], $dateRange['end']),
            'guests' => $this->getGuestStatistics($dateRange['start'], $dateRange['end']),
        ];
    }
}
