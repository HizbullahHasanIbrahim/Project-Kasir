<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;

class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        Order::unsetEventDispatcher();

        // Ambil user_id yang tersedia di database
        $userIds = User::pluck('id')->toArray();

        return [
            'user_id' => $this->faker->randomElement($userIds), // Pilih user_id secara acak
            'customer_id' => rand(0, 1) ? rand(1, 50) : null, // 50% kemungkinan memiliki customer
            'order_number' => $this->faker->unique()->bothify('ORD############'),
            'order_name' => ucfirst($this->faker->word),
            'discount' => 0, // Diskon dalam persen
            'total' => 0,
            'customer_cash' => 0,
            'change' => 0,
            'payment_method' => collect(\App\Enums\PaymentMethod::cases())->random(),
            'status' => collect(\App\Enums\OrderStatus::cases())->random(),
            'created_at' => $this->faker->dateTimeBetween('first day of January this year', 'now'),
            'updated_at' => fn (array $attributes) => $this->faker->dateTimeBetween($attributes['created_at'], 'now'),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Order $order) {
            $productIds = Product::query()->inRandomOrder()->take(rand(1, 5))->pluck('id');

            $orderDetails = $productIds->map(function ($productId) use ($order) {
                $quantity = rand(1, 10);
                $price = Product::find($productId)->price;
                $subtotal = $quantity * $price;

                return [
                    'order_id' => $order->id,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ];
            });

            OrderDetail::insert($orderDetails->toArray());

            // Hitung subtotal dari semua produk dalam order
            $subtotal = $orderDetails->sum('subtotal');

            // Jika ada customer, diskon 5%
            $discountPercentage = $order->customer_id ? 5 : 0;
            $discountValue = ($discountPercentage / 100) * $subtotal;

            // Hitung total setelah diskon
            $total = max(0, $subtotal - $discountValue);

            // Hitung customer_cash terlebih dahulu
            $customerCash = $total + rand(0, 50000);

            // Simpan perubahan ke database
            $order->update([
                'discount' => $discountPercentage, // Diskon dalam persen
                'total' => $total,
                'profit' => $total * 0.1,
                'customer_cash' => $customerCash, // Simpan customer_cash terlebih dahulu
            ]);

            // Hitung ulang change setelah customer_cash diperbarui
            $order->update([
                'change' => max(0, $customerCash - $total),
            ]);
        });
    }
}
