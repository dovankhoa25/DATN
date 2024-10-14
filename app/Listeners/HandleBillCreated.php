<?php

namespace App\Listeners;

use App\Events\BillCreated;
use App\Jobs\SendBillCreatedEmail;
use App\Mail\BillCreatedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class HandleBillCreated implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct()
    {
        //
    }


    public function handle(BillCreated $event)
    {

        $bill = $event->bill;

        // meo sác nhận 
        SendBillCreatedEmail::dispatch($bill);

        // cập nhật điểm 


    }
}
