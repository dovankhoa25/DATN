<?php

namespace App\Listeners;

use App\Events\BillCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleBillCreated implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct()
    {
        //
    }


    public function handle(BillCreated $event)
    {

        // Gửi email, cập nhật điểm thưởng, thông báo tới hệ thống, v.v.
        $bill = $event->bill;

        // Ví dụ: Gửi email thông báo đơn hàng đã được tạo
        // \Mail::to($bill->user->email)->send(new BillCreatedMail($bill));

        // Cập nhật điểm thưởng hoặc các tác vụ khác
    }
}
