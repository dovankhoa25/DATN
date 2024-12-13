<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StatisticRequest;
use App\Models\Bill;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StatisticController extends Controller
{
    public function index(StatisticRequest $request)
    {
        $year = $request->year ?? 2024;
        $month = $request->month;
        $quarter = $request->quarter;

        if ($month) {
            $data = $this->getDailyStatistics($year, $month);
        } elseif ($quarter) {
            $data = $this->getQuarterlyStatistics($year, $quarter);
        } else {
            $data = $this->getMonthlyStatistics($year);
        }

        return response()->json($data);
    }

    private function getDailyStatistics($year, $month)
    {
        $startDate = "$year-$month-01";
        $endDate = date("Y-m-t", strtotime($startDate));

        $revenueData = Bill::selectRaw('
        DATE(order_date) as day, 
        SUM(CASE WHEN status = "completed" THEN total_amount ELSE 0 END) as revenue,
        COUNT(CASE WHEN status = "completed" THEN 1 ELSE NULL END) as completed_bills,
        COUNT(CASE WHEN status = "failed" THEN 1 ELSE NULL END) as failed_bills
    ')
            ->whereBetween('order_date', [$startDate, $endDate])
            ->groupBy('day')
            ->get();

        $userData = User::selectRaw('DATE(created_at) as day, COUNT(*) as new_users')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('day')
            ->get();

        $customerData = Customer::selectRaw('DATE(created_at) as day, COUNT(*) as new_customers')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('day')
            ->get();

        return [
            'type' => 'daily',
            'revenue' => $revenueData,
            'new_users' => $userData,
            'new_customers' => $customerData,
        ];
    }

    private function getMonthlyStatistics($year)
    {
        $revenueData = Bill::selectRaw('MONTH(order_date) as month, SUM(total_amount) as revenue')
            ->whereYear('order_date', $year)
            ->groupBy('month')
            ->get();

        $userData = User::selectRaw('MONTH(created_at) as month, COUNT(*) as new_users')
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->get();

        $customerData = Customer::selectRaw('MONTH(created_at) as month, COUNT(*) as new_customers')
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->get();

        return [
            'type' => 'monthly',
            'revenue' => $revenueData,
            'new_users' => $userData,
            'new_customers' => $customerData,
        ];
    }


    private function getQuarterlyStatistics($year, $quarter)
    {
        $months = [
            1 => [1, 2, 3],
            2 => [4, 5, 6],
            3 => [7, 8, 9],
            4 => [10, 11, 12],
        ];

        $revenueData = Bill::selectRaw('MONTH(order_date) as month, SUM(total_amount) as revenue')
            ->whereYear('order_date', $year)
            ->whereIn(DB::raw('MONTH(order_date)'), $months[$quarter])
            ->groupBy('month')
            ->get();

        $userData = User::selectRaw('MONTH(created_at) as month, COUNT(*) as new_users')
            ->whereYear('created_at', $year)
            ->whereIn(DB::raw('MONTH(created_at)'), $months[$quarter])
            ->groupBy('month')
            ->get();

        $customerData = Customer::selectRaw('MONTH(created_at) as month, COUNT(*) as new_customers')
            ->whereYear('created_at', $year)
            ->whereIn(DB::raw('MONTH(created_at)'), $months[$quarter])
            ->groupBy('month')
            ->get();

        return [
            'type' => 'quarterly',
            'revenue' => $revenueData,
            'new_users' => $userData,
            'new_customers' => $customerData,
        ];
    }
}