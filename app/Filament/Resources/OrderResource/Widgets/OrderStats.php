<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Enums\OrderStatus;
use App\Filament\Resources\OrderResource\Pages\ListOrders;
use App\Models\Order;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Carbon;

class OrderStats extends BaseWidget
{
    use InteractsWithPageTable;

    public static function canView(): bool
    {
        return true;
    }

    protected function getStats(): array
    {
        $createdFrom = $this->tableFilters['created_at']['created_from'] ?? now()->startOfMonth();
        $createdTo = $this->tableFilters['created_at']['created_until'] ?? now()->endOfMonth();

        // Order Count Trend
        $orderTrend = Trend::model(Order::class)
            ->between(start: $createdFrom, end: $createdTo)
            ->perDay()
            ->count();

        // Profit Trend
        $profitTrend = Trend::query(Order::query()->where('status', OrderStatus::COMPLETED))
            ->between(start: now()->startOfYear(), end: now()->endOfYear())
            ->perMonth()
            ->sum('profit');

        // Total Revenue Trend
        $totalTrend = Trend::query(Order::query()->where('status', OrderStatus::COMPLETED))
            ->between(start: now()->startOfYear(), end: now()->endOfYear())
            ->perMonth()
            ->sum('total');

        return [
            Stat::make('Total Orders', $this->getPageTableQuery()->count())
                ->chart($orderTrend->map(fn (TrendValue $item) => $item->aggregate)->toArray())
                ->icon('heroicon-o-shopping-bag')
                ->description('Total pesanan bulan ini.')
                ->descriptionColor('gray')
                ->color('info'),

            Stat::make('Total Revenue', 'Rp ' . number_format(
                Order::query()
                    ->where('status', OrderStatus::COMPLETED)
                    ->when(
                        $this->tableFilters['created_at']['created_from'] && $this->tableFilters['created_at']['created_until'],
                        fn ($query) => $query->whereDate('created_at', '>=', $createdFrom)->whereDate('created_at', '<=', $createdTo)
                    )
                    ->sum('total'), 0, ',', '.'
            ))
                ->chart($totalTrend->map(fn (TrendValue $item) => $item->aggregate)->toArray())
                ->icon('heroicon-o-banknotes')
                ->description('Pendapatan bulan ini.')
                ->descriptionColor('gray')
                ->color('success'),

            Stat::make('Total Profit', 'Rp ' . number_format(
                Order::query()
                    ->where('status', OrderStatus::COMPLETED)
                    ->when(
                        $this->tableFilters['created_at']['created_from'] && $this->tableFilters['created_at']['created_until'],
                        fn ($query) => $query->whereDate('created_at', '>=', $createdFrom)->whereDate('created_at', '<=', $createdTo)
                    )
                    ->sum('profit'), 0, ',', '.'
            ))
                ->chart($profitTrend->map(fn (TrendValue $item) => $item->aggregate)->toArray())
                ->icon('heroicon-o-currency-dollar') // Ganti ikon
                ->description('Keuntungan bulan ini.')
                ->descriptionColor('gray')
                ->color('warning'),
        ];
    }

    protected function getTablePage(): string
    {
        return ListOrders::class;
    }
}
