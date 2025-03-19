<?php

use Illuminate\Support\Facades\Route;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;

Route::get('/orders/{order}/download-receipt', function (Order $order) {
    $pdf = Pdf::loadView('pdf.print-order', [
        'order' => $order,
    ]);

    return $pdf->stream('receipt-' . $order->order_number . '.pdf');
})->name('orders.download-receipt');
