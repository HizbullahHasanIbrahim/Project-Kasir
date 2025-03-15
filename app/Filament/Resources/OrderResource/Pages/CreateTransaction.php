<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\Page;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\DB;

class CreateTransaction extends Page
{
    protected static string $resource = OrderResource::class;
    protected static string $view = 'filament.resources.order-resource.pages.create-transaction';

    public Order $record;
    public mixed $selectedProduct;
    public int $quantityValue = 1;
    public $customer_cash = 0;
    public int $discount = 0; // Diskon dalam persen
    public string $barcode = '';

    public function getTitle(): string
    {
        return "Order: {$this->record->order_number}";
    }

    public function addProductByBarcode(): void
    {
        if (!$this->barcode) {
            return;
        }

        $product = Product::where('barcode', $this->barcode)->first();

        if (!$product) {
            Notification::make()
                ->title('Produk tidak ditemukan!')
                ->danger()
                ->send();
            return;
        }

        // Cek apakah produk sudah ada di order
        $orderDetail = $this->record->orderDetails()->where('product_id', $product->id)->first();

        if ($orderDetail) {
            // Jika sudah ada, tambahkan jumlahnya
            $orderDetail->update([
                'quantity' => $orderDetail->quantity + 1,
                'subtotal' => ($orderDetail->quantity + 1) * $orderDetail->price,
            ]);
        } else {
            // Jika belum ada, tambahkan sebagai produk baru
            $this->record->orderDetails()->create([
                'order_id' => $this->record->id,
                'product_id' => $product->id,
                'quantity' => 1,
                'price' => $product->price,
                'subtotal' => $product->price,
            ]);
        }

        Notification::make()
            ->title('Produk ditambahkan!')
            ->body("Produk {$product->name} berhasil ditambahkan ke transaksi.")
            ->success()
            ->send();

        // Reset input barcode setelah scan
        $this->barcode = '';
    }



    protected function getFormSchema(): array
    {
        return [
            Select::make('selectedProduct')
                ->label('Pilih Produk')
                ->searchable()
                ->preload()
                ->options(Product::pluck('name', 'id')->toArray())
                ->live()
                ->afterStateUpdated(function ($state) {
                    $product = Product::find($state);
                    $this->record->orderDetails()->updateOrCreate(
                        [
                            'order_id' => $this->record->id,
                            'product_id' => $state,
                        ],
                        [
                            'quantity' => $this->quantityValue,
                            'price' => $product->price,
                            'subtotal' => $product->price * $this->quantityValue,
                        ]
                    );
                }),
        ];
    }

    public function updateQuantity(OrderDetail $orderDetail, $quantity): void
    {
        if ($quantity > 0) {
            $orderDetail->update([
                'quantity' => $quantity,
                'subtotal' => $orderDetail->price * $quantity,
            ]);
        }
    }

    public function removeProduct(OrderDetail $orderDetail): void
    {
        $orderDetail->delete();
        $this->dispatch('productRemoved');
    }

    public function updateOrder(): void
    {
        $subtotal = $this->record->orderDetails->sum('subtotal');
        $discountValue = ($this->discount / 100) * $subtotal; // Hitung diskon dari persen
        $total = $subtotal - $discountValue;

        $this->record->update([
            'discount' => $this->discount, // Simpan diskon sebagai persen
            'total' => $total,
        ]);
    }

    public function finalizeOrder(): void
    {
        $this->updateOrder();

        $subtotal = $this->record->orderDetails->sum('subtotal');
        $discountValue = ($this->discount / 100) * $subtotal;
        $total = $subtotal - $discountValue;

        // Validasi apakah customer_cash cukup untuk membayar total
        if ($this->customer_cash < $total) {
            Notification::make()
                ->title('Transaksi Gagal')
                ->body('Uang pelanggan tidak cukup untuk membayar transaksi!')
                ->danger()
                ->send();

            return;
        }

        // Hitung kembalian (change)
        $change = max($this->customer_cash - $total, 0);

        // Simpan ke database
        $this->record->update([
            'customer_cash' => $this->customer_cash,
            'change' => $change,
            'status' => 'completed',
        ]);

        // Kurangi stok produk setelah transaksi selesai
        foreach ($this->record->orderDetails as $orderDetail) {
            $product = Product::find($orderDetail->product_id);
            if ($product) {
                if ($product->stock_quantity >= $orderDetail->quantity) {
                    $product->decrement('stock_quantity', $orderDetail->quantity);
                } else {
                    Notification::make()
                        ->title('Stok Tidak Cukup')
                        ->body("Stok produk {$product->name} tidak mencukupi!")
                        ->danger()
                        ->send();
                    return;
                }
            }
        }

        Notification::make()
            ->title('Transaksi Berhasil')
            ->body("Transaksi dengan total Rp {$total} berhasil diselesaikan!")
            ->success()
            ->send();

        $this->redirect('/orders');
    }

    public function saveAsDraft(): void
    {
        $this->updateOrder();
        $this->redirect('/orders');
    }

    public function updatedCustomerCash()
    {
        $subtotal = $this->record->orderDetails->sum('subtotal');
        $discountValue = ($this->discount / 100) * $subtotal;
        $this->total = $subtotal - $discountValue;
    }
}
