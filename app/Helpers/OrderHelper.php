<?php

namespace App\Helpers;

use App\Models\Order;
use Carbon\Carbon;

class OrderHelper
{
    public static function generateSequentialNumber(): string
    {
        $tanggal = Carbon::now()->format('Ymd');
        $count = Order::whereDate('created_at', Carbon::today())->count() + 1;
        $nomorUrut = str_pad($count, 4, '0', STR_PAD_LEFT);

        return "ORD{$tanggal}{$nomorUrut}";
    }
}
