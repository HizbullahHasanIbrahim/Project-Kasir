<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Transaksi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #f9f9f9, #e0e0e0);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .receipt {
            max-width: 400px;
            margin: 0 auto;
            background-color: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            border: 1px solid #e0e0e0;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            color: #333;
            font-weight: 700;
            letter-spacing: -1px;
        }
        .header p {
            margin: 5px 0;
            color: #777;
            font-size: 14px;
        }
        .info {
            margin-bottom: 20px;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 8px;
        }
        .info p {
            margin: 8px 0;
            color: #555;
            font-size: 14px;
        }
        .info .bold {
            font-weight: 600;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            font-weight: 600;
            background-color: #f5f5f5;
            color: #333;
        }
        .right {
            text-align: right;
        }
        .bold {
            font-weight: 600;
        }
        .border-top {
            border-top: 2px solid #000;
        }
        .total {
            margin-top: 20px;
        }
        .total td {
            font-size: 16px;
            padding: 12px;
        }
        .thank-you {
            margin-top: 25px;
            text-align: center;
            font-size: 18px;
            color: #333;
            font-weight: 600;
            padding: 15px;
            background: linear-gradient(135deg, #f5f5f5, #e0e0e0);
            border-radius: 8px;
        }
        .icon {
            margin-right: 8px;
            color: #555;
        }
        .gradient-text {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <h1 class="gradient-text">Cashier</h1>
            <p><i class="fas fa-calendar-alt icon"></i>{{ now()->format('d F Y H:i') }}</p>
        </div>

        <div class="info">
            <p><span class="bold"><i class="fas fa-user icon"></i>Kasir:</span> {{ $order->user->name ?? 'Tidak diketahui' }}</p>
            <p><span class="bold"><i class="fas fa-users icon"></i>Pelanggan:</span> {{ $order->customer->name ?? 'Umum' }}</p>
            <p><span class="bold"><i class="fas fa-receipt icon"></i>No. Order:</span> {{ $order->order_number }}</p>
        </div>

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

        <table class="total">
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

        <div class="thank-you">
            <p>Terima kasih telah berbelanja! <i class="fas fa-smile-beam"></i></p>
        </div>
    </div>
</body>
</html>
