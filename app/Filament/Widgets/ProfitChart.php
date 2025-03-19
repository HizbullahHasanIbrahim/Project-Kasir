<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;

class ProfitChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $pollingInterval = null;
    protected static ?string $heading = 'Statistik Penjualan Harian';
    protected static ?string $maxHeight = '350px';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $createdFrom = new Carbon($this->filters['start_date'] ?? now()->startOfMonth());
        $createdTo = new Carbon($this->filters['end_date'] ?? now());

        // Buat range tanggal dari awal hingga akhir periode
        $dateRange = collect();
        $currentDate = $createdFrom->copy();
        while ($currentDate <= $createdTo) {
            $dateRange->put($currentDate->format('Y-m-d'), [
                'total_profit' => 0,
                'total_revenue' => 0,
            ]);
            $currentDate->addDay();
        }

        // Ambil data dari database
        $query = Order::query()
            ->where('status', OrderStatus::COMPLETED)
            ->selectRaw("DATE(created_at) as tanggal,
                         SUM(profit) as total_profit,
                         SUM(total) as total_revenue")
            ->whereBetween('created_at', [$createdFrom, $createdTo])
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        // Gabungkan hasil query dengan rentang tanggal yang sudah dibuat
        $query->each(function ($item) use ($dateRange) {
            $dateRange[$item->tanggal] = [
                'total_profit' => $item->total_profit,
                'total_revenue' => $item->total_revenue,
            ];
        });

        // Ambil data untuk chart
        $profits = $dateRange->pluck('total_profit')->toArray();
        $revenues = $dateRange->pluck('total_revenue')->toArray();
        $labels = $dateRange->keys()->map(fn ($tanggal) => Carbon::parse($tanggal)->translatedFormat('d M'))->toArray();

        // Hitung rata-rata
        $averageProfit = count($profits) > 0 ? array_sum($profits) / count($profits) : 0;
        $averageRevenue = count($revenues) > 0 ? array_sum($revenues) / count($revenues) : 0;

        return [
            'datasets' => [
                [
                    'label' => 'Profit',
                    'data' => $profits,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, .1)',
                    'fill' => 'start',
                    'tension' => 0, // Garis tajam
                ],
                [
                    'label' => 'Revenue',
                    'data' => $revenues,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, .1)',
                    'fill' => 'start',
                    'tension' => 0, // Garis tajam
                ],
            ],
            'labels' => $labels,
            'averageProfit' => number_format($averageProfit, 0, ',', '.'),
            'averageRevenue' => number_format($averageRevenue, 0, ',', '.'),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getFooter(): ?string
    {
        $data = $this->getData();
        return "📊 Rata-rata profit: Rp {$data['averageProfit']} | Rata-rata revenue: Rp {$data['averageRevenue']}";
    }
}


