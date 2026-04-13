<?php

namespace App\Services;

use App\Models\Order;

class OrderNumberGenerator
{
    public function generate(): string
    {
        $year = now()->format('Y');
        $sequence = (int) Order::query()
            ->whereYear('created_at', now()->year)
            ->count() + 1;

        do {
            $orderNumber = sprintf('AGF-%s-%05d', $year, $sequence);
            $sequence++;
        } while (Order::query()->where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }
}
