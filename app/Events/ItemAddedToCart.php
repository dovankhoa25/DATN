<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ItemAddedToCart implements ShouldBroadcast
{
    use SerializesModels;

    public $cartItem;

    public function __construct($cartItem)
    {
        $this->cartItem = $cartItem;
    }

    public function broadcastOn()
    {
        return new Channel('cart.' . $this->cartItem->ma_bill);
    }

    public function broadcastAs()
    {
        return 'item.addedToCart';
    }

    public function broadcastWith()
    {
        return [
            'ma_bill' => $this->cartItem->ma_bill,
            'product_detail_id' => $this->cartItem->product_detail_id,
            'quantity' => $this->cartItem->quantity,
            'price' => $this->cartItem->price,
            'message' => 'Sản phẩm đã được thêm vào giỏ hàng.',
        ];
    }
}
