<!DOCTYPE html>
<html>
<head>
    <title>Laporan Transaksi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            word-wrap: break-word;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            text-align: center;
        }
        .badge-gray {
            background-color: #e5e7eb;
            color: #374151;
        }
        .badge-green {
            background-color: #d1fae5;
            color: #065f46;
        }
        .badge-red {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .text-ellipsis {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>
<body>
    <h1>Laporan Transaksi</h1>
    <table>
        <thead>
            <tr>
                <th style="width: 12%;">Order Number</th>
                <th style="width: 15%;">Order Name</th>
                <th style="width: 18%;">Customer Name</th>
                <th style="width: 10%;">User</th>
                <th style="width: 8%;">Discount (%)</th>
                <th style="width: 10%;">Total</th>
                <th style="width: 10%;">Profit</th>
                <th style="width: 10%;">Customer Cash</th>
                <th style="width: 10%;">Change</th>
                <th style="width: 10%;">Payment Method</th>
                <th style="width: 10%;">Status</th>
                <th style="width: 12%;">Created At</th>
                <th style="width: 12%;">Updated At</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $record)
            <tr>
                <td class="text-ellipsis">{{ $record->order_number }}</td>
                <td class="text-ellipsis">{{ $record->order_name }}</td>
                <td class="text-ellipsis">{{ $record->customer?->name ?? 'N/A' }}</td>
                <td class="text-ellipsis">{{ $record->user?->name ?? 'N/A' }}</td>
                <td class="text-center">{{ $record->discount }}%</td>
                <td class="text-right">Rp {{ number_format($record->total ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($record->profit ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($record->customer_cash ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($record->change ?? 0, 0, ',', '.') }}</td>
                <td class="text-center">
                    <span class="badge badge-gray">{{ $record->payment_method ?? 'N/A' }}</span>
                </td>
                <td class="text-center">
                    @if($record->status === 'completed')
                        <span class="badge badge-green">{{ $record->status }}</span>
                    @elseif($record->status === 'cancelled')
                        <span class="badge badge-red">{{ $record->status }}</span>
                    @else
                        <span class="badge badge-gray">{{ $record->status ?? 'N/A' }}</span>
                    @endif
                </td>
                <td class="text-center">{{ $record->created_at->format('d M Y H:i') }}</td>
                <td class="text-center">{{ $record->updated_at->format('d M Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
