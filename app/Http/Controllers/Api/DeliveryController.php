<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Orders; // Assuming normal orders
use App\Models\CustomOrder; // Assuming custom orders
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    /**
     * Get the destination (lat/lng) for a specific order.
     *
     * @param int $id The order ID
     * @param Request $request To get the order type (normal/custom)
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrderDestination(Request $request, $id)
    {
        $type = $request->query('type', 'normal'); // Expect 'normal' or 'custom'

        if ($type === 'normal') {
            $order = Orders::with('user.school')->find($id);
        } else {
            $order = CustomOrder::with('user.school')->find($id);
        }

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Assuming school details (lat/lng, address) are linked via the user model
        $destination = [
            'address' => $order->user->school->address ?? $order->delivery_address, // Fallback if school not linked
            'latitude' => (float) ($order->user->school->lat ?? $order->delivery_lat), // Assuming lat/lng on order if no school
            'longitude' => (float) ($order->user->school->lng ?? $order->delivery_lng),
        ];

        // You might store lat/lng directly on the Order/CustomOrder model if delivery locations vary per order
        // If so, adjust the above lines to:
        // $destination = [
        //     'address' => $order->delivery_address,
        //     'latitude' => (float) $order->delivery_lat,
        //     'longitude' => (float) $order->delivery_lng,
        // ];


        if (is_null($destination['latitude']) || is_null($destination['longitude'])) {
            return response()->json(['message' => 'Destination coordinates not available for this order.'], 400);
        }

        return response()->json(['destination' => $destination]);
    }

    /**
     * Receives and stores (or broadcasts) the driver's current location.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
public function updateLocation(Request $request, $orderId) // Changed from trackDriverLocation
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'order_type' => 'required|in:normal,custom', // Make sure this is still here
        ]);

        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');
        $orderType = $request->input('order_type');

        if ($orderType === 'normal') {
            $order = Orders::find($orderId);
        } else {
            $order = CustomOrder::find($orderId);
        }

        if (!$order) {
            return response()->json(['message' => 'Order not found for the given ID and type.'], 404);
        }

        // --- IMPORTANT: ADD DATABASE COLUMNS IF YOU HAVEN'T ---
        // Ensure your 'orders' and 'custom_orders' tables have 'driver_latitude' and 'driver_longitude'
        $order->driver_latitude = $latitude;
        $order->driver_longitude = $longitude;
        $order->save();

        return response()->json(['message' => 'Driver location updated successfully!', 'order' => $order]);
    }
}