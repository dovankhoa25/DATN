<?php

namespace App\Events;

use App\Models\BillDetail;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ItemConfirmedByAdmin implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $billDetail;

    public function __construct(BillDetail $billDetail)
    {
        $this->billDetail = $billDetail;
        Log::info('Đang phát sự kiện ItemConfirmedByAdmin', ['billDetail' => $billDetail]);
    }

    public function broadcastOn()
    {
        return new Channel('bill.' . $this->billDetail->bill_id);
    }

    public function broadcastAs()
    {
        return 'item.confirmed';
    }
}
