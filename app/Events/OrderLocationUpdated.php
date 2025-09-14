<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class OrderLocationUpdated implements ShouldBroadcast
{
    use SerializesModels;

    public $orderId;
    public $orderType; // "orders" or "custom_orders"
    public $latitude;
    public $longitude;

    /**
     * Create a new event instance.
     */
    public function __construct($orderId, $orderType, $latitude, $longitude)
    {
        $this->orderId   = $orderId;
        $this->orderType = $orderType;
        $this->latitude  = $latitude;
        $this->longitude = $longitude;
    }

    /**
     * The channel this event will broadcast on.
     */
    public function broadcastOn(): Channel
    {
        // Example: orders.orders.123 or orders.custom_orders.45
        return new Channel("orders.{$this->orderType}.{$this->orderId}");
    }

    /**
     * Optional: change the event name for frontend
     */
    public function broadcastAs(): string
    {
        return 'OrderLocationUpdated';
    }
}
