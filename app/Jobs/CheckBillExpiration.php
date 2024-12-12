<?php

namespace App\Jobs;

use App\Models\Bill;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckBillExpiration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $billId;

    public function __construct($billId)
    {
        $this->billId = $billId;
    }

    public function handle()
    {
        try {
            $bill = Bill::find($this->billId);

            if ($bill && $bill->qr_expiration && now()->greaterThan($bill->qr_expiration)) {
                $bill->payment_status = 'failed';
                $bill->status = 'failed';
                $bill->save();
            } else {
            }
        } catch (\Exception $e) {
            Log::error('Error status: ' . $e->getMessage());
        }
    }
}
