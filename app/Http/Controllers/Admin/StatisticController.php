<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StatisticRequest;
use App\Models\Bill;
use App\Models\Customer;
use App\Models\User;
use DateInterval;
use DatePeriod;
use DateTime;
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

        $allDays = [];
        $period = new DatePeriod(
            new DateTime($startDate),
            new DateInterval('P1D'),
            (new DateTime($endDate))->modify('+1 day')
        );
        foreach ($period as $date) {
            $allDays[$date->format('Y-m-d')] = [
                'day' => $date->format('Y-m-d'),
                'revenue' => "0",
                'completed_bills' => 0,
                'failed_bills' => 0,
            ];
        }

        $revenueData = Bill::selectRaw('
        DATE(order_date) as day, 
        SUM(CASE WHEN status = "completed" THEN total_amount ELSE 0 END) as revenue,
        COUNT(CASE WHEN status = "completed" THEN 1 ELSE NULL END) as completed_bills,
        COUNT(CASE WHEN status = "failed" THEN 1 ELSE NULL END) as failed_bills')
            ->whereBetween('order_date', [$startDate, $endDate])
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        foreach ($allDays as $day => &$data) {
            if (isset($revenueData[$day])) {
                $data = array_merge($data, $revenueData[$day]->toArray());
            }
        }

        $userData = User::selectRaw('DATE(created_at) as day, COUNT(*) as new_users')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $allUserDays = [];
        foreach ($allDays as $day => $data) {
            $allUserDays[] = [
                'day' => $day,
                'new_users' => $userData[$day]->new_users ?? 0,
            ];
        }

        $customerData = Customer::selectRaw('DATE(created_at) as day, COUNT(*) as new_customers')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $allCustomerDays = [];
        foreach ($allDays as $day => $data) {
            $allCustomerDays[] = [
                'day' => $day,
                'new_customers' => $customerData[$day]->new_customers ?? 0,
            ];
        }

        return [
            'type' => 'daily',
            'revenue' => array_values($allDays),
            'new_users' => $allUserDays,
            'new_customers' => $allCustomerDays,
        ];
    }


    private function getMonthlyStatistics($year)
    {
        $allMonths = [];
        for ($month = 1; $month <= 12; $month++) {
            $allMonths[$month] = [
                'month' => $month,
                'revenue' => 0,
                'new_users' => 0,
                'new_customers' => 0,
            ];
        }

        $revenueData = Bill::selectRaw('MONTH(order_date) as month, SUM(total_amount) as revenue')
            ->whereYear('order_date', $year)
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        $userData = User::selectRaw('MONTH(created_at) as month, COUNT(*) as new_users')
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        $customerData = Customer::selectRaw('MONTH(created_at) as month, COUNT(*) as new_customers')
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        foreach ($allMonths as $month => &$data) {
            if (isset($revenueData[$month])) {
                $data['revenue'] = $revenueData[$month]->revenue;
            }
            if (isset($userData[$month])) {
                $data['new_users'] = $userData[$month]->new_users;
            }
            if (isset($customerData[$month])) {
                $data['new_customers'] = $customerData[$month]->new_customers;
            }
        }

        return [
            'type' => 'monthly',
            'revenue' => array_values($allMonths),
            'new_users' => array_map(fn($monthData) => ['month' => $monthData['month'], 'new_users' => $monthData['new_users']], $allMonths),
            'new_customers' => array_map(fn($monthData) => ['month' => $monthData['month'], 'new_customers' => $monthData['new_customers']], $allMonths),
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
