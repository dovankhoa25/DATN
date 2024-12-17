<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ItemCancelledFromBill implements ShouldBroadcast
{
    use SerializesModels;

    public $billId;
    public $items;

    public function __construct($data)
    {
        $this->billId = $data['bill_id'];
        $this->items = $data['cancelled_items'];
    }

    public function broadcastOn()
    {
        return new Channel('bill.' . $this->billId);
    }

    public function broadcastAs()
    {
        return 'item.cancelled';
    }

    public function broadcastWith()
    {
        return [
            'bill_id' => $this->billId,
            'items' => $this->items,
            'message' => 'Sản phẩm đã được hủy khỏi hóa đơn.',
        ];
    }
}
