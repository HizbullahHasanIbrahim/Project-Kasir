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
        $currentMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        // Data bulan ini
        $totalOrders = Order::count();
        $completedOrders = Order::where('status', 'completed')->count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $cancelledOrders = Order::where('status', 'cancelled')->count();

        // Data bulan lalu
        $lastMonthTotalOrders = Order::whereBetween('created_at', [$lastMonth, $currentMonth])->count();
        $lastMonthCompleted = Order::whereBetween('created_at', [$lastMonth, $currentMonth])->where('status', 'completed')->count();
        $lastMonthPending = Order::whereBetween('created_at', [$lastMonth, $currentMonth])->where('status', 'pending')->count();
        $lastMonthCancelled = Order::whereBetween('created_at', [$lastMonth, $currentMonth])->where('status', 'cancelled')->count();

        return [
            Stat::make('Total Orders', $totalOrders)
                ->description($this->compareStats($totalOrders, $lastMonthTotalOrders))
                ->descriptionColor($this->getColor($totalOrders, $lastMonthTotalOrders))
                ->icon('heroicon-o-shopping-bag')
                ->chart($this->getOrderTrends())
                ->color('primary'),

            Stat::make('Completed Orders', $completedOrders)
                ->description($this->compareStats($completedOrders, $lastMonthCompleted))
                ->descriptionColor($this->getColor($completedOrders, $lastMonthCompleted))
                ->icon('heroicon-o-check-circle')
                ->chart($this->getOrderTrends('completed'))
                ->color('success'),

            Stat::make('Pending Orders', $pendingOrders)
                ->description($this->compareStats($pendingOrders, $lastMonthPending))
                ->descriptionColor($this->getColor($pendingOrders, $lastMonthPending))
                ->icon('heroicon-o-clock')
                ->chart($this->getOrderTrends('pending'))
                ->color('warning'),

            Stat::make('Cancelled Orders', $cancelledOrders)
                ->description($this->compareStats($cancelledOrders, $lastMonthCancelled))
                ->descriptionColor($this->getColor($cancelledOrders, $lastMonthCancelled, false))
                ->icon('heroicon-o-x-circle')
                ->chart($this->getOrderTrends('cancelled'))
                ->color('danger'),
        ];
    }

    private function compareStats(int $current, int $previous): string
    {
        if ($previous == 0) {
            return $current > 0 ? 'Naik dari bulan lalu' : 'Belum ada perubahan';
        }

        $difference = $current - $previous;
        $percentage = round(($difference / max($previous, 1)) * 100, 2);

        if ($difference > 0) {
            return "Naik {$percentage}% dibanding bulan lalu";
        } elseif ($difference < 0) {
            return "Turun {$percentage}% dibanding bulan lalu";
        } else {
            return 'Tidak ada perubahan';
        }
    }

    private function getColor(int $current, int $previous, bool $positive = true): string
    {
        if ($previous == 0) {
            return 'gray';
        }

        $difference = $current - $previous;
        if ($difference > 0) {
            return $positive ? 'success' : 'danger';
        } elseif ($difference < 0) {
            return $positive ? 'danger' : 'success';
        }

        return 'gray';
    }

    private function getOrderTrends($status = null): array
    {
        $dates = collect();
        $orders = collect();

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $dates->push($date);

            $query = Order::whereDate('created_at', $date);
            if ($status) {
                $query->where('status', $status);
            }

            $orders->push($query->count());
        }

        return $orders->toArray();
    }
}
