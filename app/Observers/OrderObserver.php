<?php

namespace App\Observers;

use App\Models\Order;
use App\Notifications\OrderStatusUpdated;
use App\Models\AuditLog;
use App\Models\User;

class OrderObserver
{


    public function creating(Order $order)
    {
        //some logic before create
        $order->reference_number = strtoupper(uniqid('ORD-'));
    }


    public function created(Order $order)
    {
        //some logic after created

        $order->user->notify(new OrderCreated($order));
    }


    public function updating(Order $order)
    {
        if ($order->isDirty('status') && $order->status === 'cancelled') {

            //some logic while updating
        }
    }

    public function updated(Order $order)
    {

        if ($order->isDirty('status')) {
            $order->user->notify(new OrderStatusUpdated($order));
        }


        if ($order->status === 'shipped') {
            $order->items->each(function ($item) {
                $item->product->decrement('stock', $item->quantity);
            });
        }


        AuditLog::create([
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'action' => 'Order status updated to ' . $order->status,
        ]);


        if ($order->status === 'cancelled') {
            $admin = User::where('role', 'admin')->first();
            $admin->notify(new OrderCancelled($order));
        }
    }
}
