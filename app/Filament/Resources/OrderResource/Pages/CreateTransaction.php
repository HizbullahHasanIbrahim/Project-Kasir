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
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;

class CreateTransaction extends Page
{
    protected static string $resource = OrderResource::class;
    protected static string $view = 'filament.resources.order-resource.pages.create-transaction';
    protected static ?string $model = Order::class; // Pastikan model sudah didefinisikan

    public Order $record;
    public mixed $selectedProduct;
    public int $quantityValue = 1;
    public $customer_cash = 0;
    public int $discount = 0; // Diskon dalam persen
    public string $barcode = '';
    public $isTransactionCreated = false;

    public function getTitle(): string
    {
        // Pastikan $this->record tidak null sebelum mengakses propertinya
        return "Order: " . ($this->record ? $this->record->order_number : 'Transaksi Baru');
    }

    public function mount(Order $record): void
    {
        // Pastikan record tidak null
        if (!$record) {
            abort(404, 'Record tidak ditemukan');
        }

        $this->record = $record;

        // Set diskon otomatis berdasarkan customer
        $this->discount = $this->record->customer_id ? 5 : 0;
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

        // Jika customer dipilih, set diskon ke 5%
        if ($this->record->customer_id) {
            $this->discount = 5;
        } else {
            // Jika customer tidak dipilih, set diskon ke 0%
            $this->discount = 0;
        }

        $discountValue = ($this->discount / 100) * $subtotal; // Hitung diskon dari persen
        $total = $subtotal - $discountValue;

        $this->record->update([
            'discount' => $this->discount, // Simpan diskon sebagai persen
            'total' => $total,
        ]);
    }

    public function finalizeOrder(): void
    {
        $this->isTransactionCreated = true;

        $this->updateOrder();

        $subtotal = $this->record->orderDetails->sum('subtotal');
        $discountValue = ($this->discount / 100) * $subtotal;
        $total = $subtotal - $discountValue;

        // Validasi apakah customer_cash diisi dan cukup untuk membayar total
        if (!$this->customer_cash || $this->customer_cash < $total) {
            Notification::make()
                ->title('Transaksi Gagal')
                ->body('Uang pelanggan tidak cukup untuk membayar transaksi!')
                ->danger()
                ->send();
            return;
        }

        // Hitung kembalian (change)
        $change = max($this->customer_cash - $total, 0);

        // Gunakan transaksi database untuk memastikan semua eksekusi aman
        DB::transaction(function () use ($total, $change) {
            // Simpan transaksi ke database
            $this->record->update([
                'customer_cash' => $this->customer_cash,
                'change' => $change,
                'status' => 'completed',
            ]);

            // Periksa stok sebelum mengurangi stok
            foreach ($this->record->orderDetails as $orderDetail) {
                $product = Product::find($orderDetail->product_id);
                if ($product) {
                    if ($product->stock_quantity < $orderDetail->quantity) {
                        Notification::make()
                            ->title('Stok Tidak Cukup')
                            ->body("Stok produk {$product->name} tidak mencukupi!")
                            ->danger()
                            ->send();

                        // Batalkan transaksi
                        throw new \Exception("Stok tidak mencukupi untuk {$product->name}");
                    }
                }
            }

            // Jika stok cukup, kurangi stok
            foreach ($this->record->orderDetails as $orderDetail) {
                $product = Product::find($orderDetail->product_id);
                if ($product) {
                    $product->decrement('stock_quantity', $orderDetail->quantity);
                }
            }
        });

        // Tampilkan notifikasi sukses
        Notification::make()
            ->title('Transaksi Berhasil')
            ->body("Transaksi dengan total Rp " . number_format($total, 0, ',', '.') . " berhasil diselesaikan!")
            ->success()
            ->send();

        // $this->redirect('/orders');
    }

    public function saveAsDraft(): void
    {
        $this->isTransactionCreated = true;
        $this->updateOrder();
        // $this->redirect('/orders');
    }

    public function updatedCustomerCash()
    {
        $subtotal = $this->record->orderDetails->sum('subtotal');
        $discountValue = ($this->discount / 100) * $subtotal;
        $this->total = $subtotal - $discountValue;
    }

    public function downloadReceipt()
    {
        // Generate PDF
        $pdf = Pdf::loadView('pdf.print-order', [
            'order' => $this->record,
        ]);

        // Download PDF
        return response()->streamDownload(
            function () use ($pdf) {
                echo $pdf->stream();
            },
            'receipt-' . $this->record->order_number . '.pdf'
        );
    }

    public function redirectToOrders()
    {
        return $this->redirect('/orders');
    }

    protected function getHeaderActions(): array
    {
        return [
            // Tombol Kembali
            Actions\Action::make('back')
                ->label('Kembali')
                ->color('secondary')
                ->url(fn () => url()->previous()),
        ];
    }
}
