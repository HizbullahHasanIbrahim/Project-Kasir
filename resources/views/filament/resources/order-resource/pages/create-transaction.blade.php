<x-filament-panels::page>
    <style>
        /* Your existing styles */
        .grid-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            padding: 20px;
        }

        .section {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .section-heading {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .section-description {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
        }

        .barcode-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .order-details {
            margin-top: 20px;
        }

        .order-table {
            width: 100%;
            border-collapse: collapse;
        }

        .order-table th,
        .order-table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        .order-table th {
            background-color: #f5f5f5;
        }

        .quantity-input,
        .discount-input,
        .cash-input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .remove-button {
            background-color: #ff4d4d;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }

        .remove-button:hover {
            background-color: #ff1a1a;
        }

        .no-products {
            text-align: center;
            color: #999;
            padding: 20px;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .save-draft-button,
        .create-transaction-button,
        .print-receipt-button,
        .finish-button,
        .kembali-button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
        }

        .save-draft-button {
            background-color: #f0f0f0;
            color: #333;
        }

        .save-draft-button:hover {
            background-color: #ddd;
        }

        .create-transaction-button {
            background-color: #211C84; /* Warna seperti tombol Kembali */
            color: white;
        }

        .create-transaction-button:hover {
            background-color: #1A166B; /* Warna hover yang lebih gelap */
        }

        .print-receipt-button {
            background-color: #f0f0f0; /* Warna seperti tombol Simpan sebagai Draft */
            color: #333;
        }

        .print-receipt-button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .print-receipt-button:hover:not(:disabled) {
            background-color: #ddd; /* Warna hover yang lebih gelap */
        }

        .finish-button {
            background-color: #211C84; /* Warna seperti tombol Kembali */
            color: white;
        }

        .finish-button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .finish-button:hover:not(:disabled) {
            background-color: #1A166B; /* Warna hover yang lebih gelap */
        }

        .kembali-button {
            background-color: #211C84;
            color: #fff;
        }

        .text-right {
            text-align: right;
        }

        .col-name {
            width: 40%;
        }

        .col-quantity {
            width: 20%;
        }

        .col-price {
            width: 20%;
        }

        .col-action {
            width: 20%;
        }

        .cash-input-container {
            display: flex;
            align-items: center;
        }

        .currency-symbol {
            margin-right: 5px;
            font-weight: bold;
        }

        .cash-input {
            flex: 1;
        }
    </style>

    {{-- <!-- Tombol Kembali -->
    <div style="margin-bottom: 20px;">
        <a href="{{ url()->previous() }}" class="kembali-button" style="text-decoration: none;">
            Kembali
        </a>
    </div> --}}

    <div class="grid-container">
        <div class="section">
            <div class="section-heading">Pindai Barcode</div>
            <div class="section-description">Pindai barcode produk untuk menambahkannya produk.</div>
            <input
                type="text"
                class="barcode-input"
                wire:model.defer="barcode"
                wire:keydown.enter="addProductByBarcode"
                placeholder="Pindai barcode dan tekan Enter"
                autofocus
            />
        </div>

        <div class="section">
            <div class="section-heading">Pilih Produk</div>
            <div class="section-description">Pilih produk.</div>
            {{ $this->form }}
        </div>

        <div class="section">
            <div class="section-heading">Detail Pesanan</div>
            <div class="order-details">
                <form wire:submit="finalizeOrder">
                    <table class="order-table">
                        <colgroup>
                            <col class="col-name">
                            <col class="col-quantity">
                            <col class="col-price">
                            <col class="col-action">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Jumlah</th>
                                <th>Harga</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse ($record->orderDetails as $orderDetail)
                            <tr>
                                <td>
                                    <div class="product-name">{{ $orderDetail->product->name }}</div>
                                    <div class="product-stock">Stok saat ini: {{ $orderDetail->product->stock_quantity }}</div>
                                </td>
                                <td>
                                    <input
                                        class="quantity-input"
                                        type="number"
                                        value="{{ $orderDetail->quantity }}"
                                        wire:change="updateQuantity({{ $orderDetail->id }}, $event.target.value)"
                                        min="1"
                                        max="{{ $orderDetail->product->stock_quantity }}"
                                    />
                                </td>
                                <td class="text-right">Rp. {{ number_format($orderDetail->price * $orderDetail->quantity, 0, ',', '.') }}</td>
                                <td>
                                    <button type="button" wire:click="removeProduct({{ $orderDetail->id }})" class="remove-button">
                                        &#10005; <!-- Unicode for 'X' -->
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="no-products">Tidak ada produk yang dipilih.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </form>
            </div>
        </div>

        <div class="section">
            <div class="section-heading">Ringkasan Pembayaran</div>
                <div class="order-details">
                    <form wire:submit="finalizeOrder">
                        <table class="order-table">
                            <tfoot>
                                <tr>
                                    <th colspan="2" class="text-right">Subtotal</th>
                                    <td class="text-right">Rp. {{ number_format($record->orderDetails->sum('subtotal'), 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <th colspan="2" class="text-right">Diskon (%)</th>
                                    <td class="text-right">
                                        <input
                                            class="discount-input"
                                            type="number"
                                            wire:model.lazy="discount"
                                            min="0"
                                            max="100"
                                            placeholder="Diskon (%)"
                                        />
                                    </td>
                                </tr>
                                <tr>
                                    <th colspan="2" class="text-right">Total</th>
                                    <td class="text-right">
                                        @php
                                            $subtotal = $record->orderDetails->sum('subtotal');
                                            $discountValue = ($discount / 100) * $subtotal;
                                            $total = $subtotal - $discountValue;
                                        @endphp
                                        Rp. {{ number_format($total, 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr>
                                    <th colspan="2" class="text-right">Uang Customer</th>
                                    <td class="text-right">
                                        <div class="cash-input-container">
                                            <span class="currency-symbol">Rp.</span>
                                            <input
                                                class="cash-input"
                                                type="number"
                                                wire:model.lazy="customer_cash"
                                                min="0"
                                                placeholder="Masukkan jumlah uang"
                                            />
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th colspan="2" class="text-right">Kembalian</th>
                                    <td class="text-right">Rp. {{ number_format(max(0, $customer_cash - $total), 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>

                        <div class="form-actions">
                            @if (!$isTransactionCreated)
                                <button type="button" class="save-draft-button" wire:click="saveAsDraft">Simpan sebagai Draft</button>
                                <button type="submit" class="create-transaction-button">Buat Transaksi</button>
                            @else
                                <button type="button" class="print-receipt-button" wire:click="downloadReceipt">Cetak Nota</button>
                                <button type="button" class="finish-button" wire:click="redirectToOrders">Selesai</button>
                            @endif
                        </div>
                    </form>
                </div>
        </div>
    </div>
</x-filament-panels::page>
