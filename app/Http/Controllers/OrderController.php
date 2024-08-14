<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function updateStatus(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);

        $newStatus = $request->input('status');
        $comments = $request->input('comments');

        try {
            $this->orderService->updateOrderStatus($order, $newStatus, $comments);
            return response()->json(['message' => 'Order status updated successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
