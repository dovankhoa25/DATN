<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Statistics\BillStatisticService;
use App\Services\Statistics\UserStatisticService;
use App\Models\Bill;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;

class StatisticController extends Controller
{
    private $userService;
    private $billService;
    private $customerService;

    public function __construct(
        UserStatisticService $userService,
        BillStatisticService $billService,
        // CustomerStatisticService $customerService
    ) {
        $this->userService = $userService;
        $this->billService = $billService;
        // $this->customerService = $customerService;
    }

    public function getStatistics(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $groupBy = $request->input('group_by');

        if (!$startDate || !$endDate || !$groupBy) {
            return response()->json(['error' => 'Missing parameters'], 400);
        }

        $userStatistics = $this->userService->getStatisticsByTime($startDate, $endDate, $groupBy);

        return response()->json([
            'users' => $userStatistics,
        ]);
    }
}
