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
        $year = $request->year ?? now()->year;
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
                'revenue_online' => "0",
                'revenue_in_restaurant' => "0",
            ];
        }

        $revenueData = Bill::selectRaw('
        DATE(order_date) as day, 
        SUM(CASE WHEN status = "completed" THEN total_amount ELSE 0 END) as revenue,
        COUNT(CASE WHEN status = "completed" THEN 1 ELSE NULL END) as completed_bills,
        COUNT(CASE WHEN status = "failed" THEN 1 ELSE NULL END) as failed_bills,
        SUM(CASE WHEN status = "completed" AND order_type = "online" THEN total_amount ELSE 0 END) as revenue_online,
        SUM(CASE WHEN status = "completed" AND order_type = "in_restaurant" THEN total_amount ELSE 0 END) as revenue_in_restaurant
    ')
            ->whereBetween('order_date', [$startDate, $endDate])
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        foreach ($allDays as $day => &$data) {
            if (isset($revenueData[$day])) {
                $data['revenue'] = $revenueData[$day]->revenue ?? "0";
                $data['completed_bills'] = $revenueData[$day]->completed_bills ?? 0;
                $data['failed_bills'] = $revenueData[$day]->failed_bills ?? 0;
                $data['revenue_online'] = $revenueData[$day]->revenue_online ?? "0";
                $data['revenue_in_restaurant'] = $revenueData[$day]->revenue_in_restaurant ?? "0";
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
                'month' => sprintf("%04d-%02d", $year, $month),
                'revenue' => 0,
                'completed_bills' => 0,
                'failed_bills' => 0,
                'revenue_online' => 0,
                'revenue_in_restaurant' => 0,
            ];
        }

        $billData = Bill::selectRaw(
            'MONTH(order_date) as month, 
        SUM(total_amount) as revenue, 
        SUM(CASE WHEN status = "completed" AND order_type = "online" THEN total_amount ELSE 0 END) as revenue_online, 
        SUM(CASE WHEN status = "completed" AND order_type = "in_restaurant" THEN total_amount ELSE 0 END) as revenue_in_restaurant, 
        COUNT(CASE WHEN status = "completed" THEN 1 END) as completed_bills, 
        COUNT(CASE WHEN status = "failed" THEN 1 END) as failed_bills'
        )
            ->whereYear('order_date', $year)
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        foreach ($allMonths as $month => &$data) {
            if (isset($billData[$month])) {
                $data['revenue'] = $billData[$month]->revenue ?? 0;
                $data['completed_bills'] = $billData[$month]->completed_bills ?? 0;
                $data['failed_bills'] = $billData[$month]->failed_bills ?? 0;
                $data['revenue_online'] = $billData[$month]->revenue_online ?? 0;
                $data['revenue_in_restaurant'] = $billData[$month]->revenue_in_restaurant ?? 0;
            }
        }

        $userMonths = [];
        for ($month = 1; $month <= 12; $month++) {
            $userMonths[$month] = [
                'month' => $month,
                'new_users' => 0
            ];
        }

        $userData = User::selectRaw('MONTH(created_at) as month, COUNT(*) as new_users')
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        foreach ($userMonths as $month => &$data) {
            if (isset($userData[$month])) {
                $data['new_users'] = $userData[$month]->new_users ?? 0;
            }
        }

        $customerMonths = [];
        for ($month = 1; $month <= 12; $month++) {
            $customerMonths[$month] = [
                'month' => $month,
                'new_customers' => 0
            ];
        }

        $customerData = Customer::selectRaw('MONTH(created_at) as month, COUNT(*) as new_customers')
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        foreach ($customerMonths as $month => &$data) {
            if (isset($customerData[$month])) {
                $data['new_customers'] = $customerData[$month]->new_customers ?? 0;
            }
        }

        return [
            'type' => 'monthly',
            'revenue' => array_values($allMonths),
            'new_users' => array_values($userMonths),
            'new_customers' => array_values($customerMonths),
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
