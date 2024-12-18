<?php

namespace App\Events;

use App\Models\BillDetail;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;


class ItemAddedToBill implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $billDetails;

    public function __construct($billDetails)
    {
        $this->billDetails = $billDetails;
    }

    public function broadcastOn()
    {
        return new Channel('bill.' . $this->billDetails['bill_id']);
    }

    public function broadcastAs()
    {
        return 'item.added';
    }
}
