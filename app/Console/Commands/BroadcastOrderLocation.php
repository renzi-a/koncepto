<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Events\OrderLocationUpdated;

class BroadcastOrderLocation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Example usage:
     * php artisan broadcast:order-location 20 orders 14.1234 120.5678
     */
    protected $signature = 'broadcast:order-location {orderId} {orderType} {latitude} {longitude}';

    protected $description = 'Broadcast order location updates to Echo channels';

    public function handle()
    {
        $orderId   = $this->argument('orderId');
        $orderType = $this->argument('orderType');
        $latitude  = $this->argument('latitude');
        $longitude = $this->argument('longitude');

        broadcast(new OrderLocationUpdated($orderId, $orderType, $latitude, $longitude));

        $this->info("Broadcasted location for order {$orderId} ({$orderType}) at {$latitude}, {$longitude}");
    }
}
