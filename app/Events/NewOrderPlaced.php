<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewOrderPlaced
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;

    public function __construct($order)
    {
        $this->order = $order; // Truyền thông tin đơn hàng
    }

    public function broadcastOn()
    {
        return ['orders-channel']; // Kênh mà event sẽ được phát ra
    }

    public function broadcastAs()
    {
        return 'new-order'; // Tên của sự kiện được broadcast
    }
}
