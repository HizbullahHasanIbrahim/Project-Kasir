<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Transaksi</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { padding: 8px; border-bottom: 1px solid #ddd; text-align: left; }
        th { font-weight: bold; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        .border-top { border-top: 1px solid #000; }
        .center { text-align: center; }
    </style>
</head>
<body>
    <h2 class="center">Cashier</h2>
    <p class="center">{{ now()->format('d F Y H:i') }}</p>

    <table>
        <tr>
            <td class="bold">Kasir</td>
            <td>: {{ $order->user->name ?? 'Tidak diketahui' }}</td>
        </tr>
        <tr>
            <td class="bold">Pelanggan</td>
            <td>: {{ $order->customer->name ?? 'Umum' }}</td>
        </tr>
        <tr>
            <td class="bold">No. Order</td>
            <td>: {{ $order->order_number }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nama</th>
                <th class="right">Qty</th>
                <th class="right">Harga</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->orderDetails as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->product->name }}</td>
                <td class="right">{{ $item->quantity }} x Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($item->quantity * $item->price, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @php
        $subtotal = $order->orderDetails->sum('subtotal');
        $discount = ($order->discount / 100) * $subtotal;
        $total = $subtotal - $discount;
    @endphp

    <table>
        <tr>
            <td colspan="3" class="bold">Total</td>
            <td class="right bold">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td colspan="3" class="bold">Diskon ({{ $order->discount }}%)</td>
            <td class="right">Rp {{ number_format($discount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td colspan="3" class="bold border-top">Total Akhir</td>
            <td class="right bold">Rp {{ number_format($total, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td colspan="3" class="bold">Tunai</td>
            <td class="right">Rp {{ number_format($order->customer_cash ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td colspan="3" class="bold">Kembalian</td>
            <td class="right">Rp {{ number_format($order->change ?? 0, 0, ',', '.') }}</td>
        </tr>
    </table>

    <p class="center">Terima kasih telah berbelanja!</p>
</body>
</html>
