<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;

class OrderStats extends BaseWidget
{
    use InteractsWithPageFilters;

    protected function getStats(): array
    {
        $totalOrders = Order::count();
        $completedOrders = Order::where('status', 'completed')->count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $cancelledOrders = Order::where('status', 'cancelled')->count();

        return [
            Stat::make('Total Orders', $totalOrders)
                ->icon('heroicon-o-shopping-bag')
                ->chart($this->getOrderTrends())
                ->color('primary'),

            Stat::make('Completed Orders', $completedOrders)
                ->icon('heroicon-o-check-circle')
                ->chart($this->getOrderTrends('completed'))
                ->color('success'),

            Stat::make('Pending Orders', $pendingOrders)
                ->icon('heroicon-o-clock')
                ->chart($this->getOrderTrends('pending'))
                ->color('warning'),

            Stat::make('Cancelled Orders', $cancelledOrders)
                ->icon('heroicon-o-x-circle')
                ->chart($this->getOrderTrends('cancelled'))
                ->color('danger'),
        ];
    }

    private function getOrderTrends($status = null): array
    {
        $orders = collect();

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $query = Order::whereDate('created_at', $date);

            if ($status) {
                $query->where('status', $status);
            }

            $orders->push($query->count());
        }

        return $orders->toArray();
    }
}
