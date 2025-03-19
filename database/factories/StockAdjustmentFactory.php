<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;
use App\Models\StockAdjustment;

class StockAdjustmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StockAdjustment::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $productId = Product::query()->inRandomOrder()->value('id');
        $quantityAdjusted = $this->faker->numberBetween(-50, 500);

        // Daftar alasan penyesuaian stok
        $reasons = [
            'Koreksi stok karena kesalahan input',
            'Barang rusak atau kedaluwarsa',
            'Penyesuaian setelah audit stok',
            'Penerimaan barang dari supplier',
            'Retur barang dari pelanggan',
            'Pemakaian internal',
            'Hilang atau dicuri'
        ];

        return [
            'product_id' => $productId,
            'quantity_adjusted' => $quantityAdjusted,
            'reason' => $this->faker->randomElement($reasons), // Pilih alasan secara acak
        ];
    }

    public function configure(): StockAdjustmentFactory
    {
        return $this->afterCreating(function (StockAdjustment $adjustment) {
            $product = $adjustment->product;
            $product->stock_quantity += $adjustment->quantity_adjusted;
            $product->save();
        });
    }
}
