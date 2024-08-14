<?php

namespace App\Services;

use App\Models\Order;
use App\Enums\OrderStatus;
use App\Notifications\OrderStatusUpdated;
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function updateOrderStatus(Order $order, string $newStatus, ?string $comments = null)
    {
      
        if (!$this->canUpdateStatus($order, $newStatus)) {
            throw new \Exception('Cannot update order status.');
        }

      
        $order->status = $newStatus;
        $order->save();

      
        $this->postUpdateActions($order, $comments);
    }

    protected function canUpdateStatus(Order $order, string $newStatus): bool
    {
      
        if ($newStatus === OrderStatus::CANCELLED && $order->status === OrderStatus::COMPLETED) {
            return false;
        }

        return true;
    }

    protected function postUpdateActions(Order $order, ?string $comments)
    {
       
        $order->user->notify(new OrderStatusUpdated($order, $comments));

       
        Log::info("Order {$order->id} the status updated to {$order->status}.");
    }
}
