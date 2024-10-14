<?php

namespace App\Jobs;

use App\Mail\BillCreatedMail;
use App\Models\Bill;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendBillCreatedEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $bill;

    public function __construct(Bill $bill)
    {
        $this->bill = $bill;
    }

    public function handle()
    {
        Mail::to($this->bill->user->email)->send(new BillCreatedMail($this->bill));
    }
}
