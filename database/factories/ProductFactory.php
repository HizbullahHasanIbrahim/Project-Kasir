<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;
use App\Models\Category;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        // Daftar produk berdasarkan kategori
        $kategori = [
            'Makanan' => ['Nasi Goreng', 'Rendang', 'Ayam Bakar', 'Soto Ayam', 'Gado-gado'],
            'Minuman' => ['Es Teh Manis', 'Kopi Susu', 'Jus Alpukat', 'Air Mineral', 'Wedang Jahe'],
            'Peralatan Masak' => ['Panci', 'Teflon', 'Pisau Dapur', 'Sendok Sayur', 'Wajan'],
            'Kopi' => ['Kopi Arabika', 'Kopi Robusta', 'Kopi Luwak', 'Espresso', 'Kopi Tubruk'],
            'Teh' => ['Teh Hijau', 'Teh Hitam', 'Teh Melati', 'Teh Tarik', 'Teh Lemon'],
            'Camilan' => ['Keripik Pisang', 'Makaroni Pedas', 'Popcorn Caramel', 'Kacang Panggang', 'Stik Keju'],
            'Permen' => ['Permen Mint', 'Permen Kopiko', 'Permen Coklat', 'Permen Karet', 'Permen Jahe'],
            'Roti' => ['Roti Tawar', 'Roti Gandum', 'Croissant', 'Bagel', 'Roti Sobek'],
            'Kue' => ['Kue Brownies', 'Kue Lapis', 'Kue Nastar', 'Kue Cubit', 'Kue Lumpur'],
            'Sarapan' => ['Sereal', 'Pancake', 'Omelette', 'Bubur Ayam', 'Nasi Uduk'],
            'Mie Instan' => ['Indomie Goreng', 'Mie Kuah Soto', 'Mie Kari Ayam', 'Ramen Instan', 'Mie Goreng Pedas'],
            'Makanan Beku' => ['Nugget Ayam', 'Sosis Sapi', 'Daging Beku', 'Ikan Fillet', 'Kentang Goreng Beku'],
            'Makanan Ringan' => ['Wafer Coklat', 'Biskuit Gandum', 'Chips Pedas', 'Kacang Garam', 'Coklat Batang'],
            'Bumbu Dapur' => ['Garam', 'Merica Bubuk', 'Gula Pasir', 'Ketumbar', 'Pala'],
            'Susu & Olahannya' => ['Susu Full Cream', 'Keju Cheddar', 'Yogurt Stroberi', 'Mentega', 'Krim Kental'],
            'Daging & Seafood' => ['Daging Sapi', 'Daging Ayam', 'Udang Segar', 'Ikan Salmon', 'Cumi-cumi'],
            'Sayur & Buah' => ['Wortel', 'Bayam', 'Apel', 'Jeruk', 'Alpukat'],
            'Makanan Kaleng' => ['Sarden Kaleng', 'Kornet Sapi', 'Kacang Merah Kaleng', 'Jamur Kaleng', 'Buah Kaleng'],
            'Makanan Organik' => ['Beras Merah', 'Tepung Almond', 'Madu Murni', 'Minyak Zaitun', 'Granola Organik'],
            'Cokelat & Manisan' => ['Cokelat Batang', 'Kacang Coklat', 'Marshmallow', 'Praline', 'Cokelat Bubuk'],
            'Air Mineral' => ['Aqua', 'Le Minerale', 'Ades', 'Nestle Pure Life', 'Club'],
            'Minuman Bersoda' => ['Coca Cola', 'Sprite', 'Fanta', 'Pepsi', 'Sarsi'],
            'Minuman Berenergi' => ['Kratingdaeng', 'Red Bull', 'Extra Joss', 'Hemaviton Jreng', 'M-150'],
            'Jus & Sirup' => ['Jus Jeruk', 'Jus Mangga', 'Sirup Cocopandan', 'Sirup Vanila', 'Jus Buah Naga'],
            'Teh Herbal' => ['Teh Rosella', 'Teh Chamomile', 'Teh Daun Mint', 'Teh Sereh', 'Teh Ginseng'],
            'Kopi Instan' => ['Kopi ABC', 'Nescafe Classic', 'Torabika Cappuccino', 'Good Day Mocacinno', 'Kapal Api Special'],
            'Produk Kesehatan' => ['Obat Flu', 'Paracetamol', 'Multivitamin C', 'Obat Batuk Herbal', 'Tolak Angin'],
            'Vitamin & Suplemen' => ['Vitamin C 1000mg', 'Vitamin D3', 'Omega-3', 'Zinc Tablet', 'Probiotik'],
            'Makanan Bayi' => ['Bubur Bayi', 'Sereal Bayi', 'Susu Formula', 'Biskuit Bayi', 'Puré Buah'],
            'Snack Sehat' => ['Granola Bar', 'Kacang Almond', 'Oatmeal Cookies', 'Kacang Hijau Rebus', 'Buah Kering'],
        ];

        // Ambil kategori acak yang sudah ada di database
        $category = Category::inRandomOrder()->first();

        if (!$category) {
            // Jika tidak ada kategori, buat kategori default
            $category = Category::create(['name' => 'Makanan']);
        }

        // Pilih nama produk sesuai kategori (fallback jika kategori belum ada di daftar)
        $namaProduk = $kategori[$category->name][array_rand($kategori[$category->name])] ?? 'Produk Default';

        return [
            'category_id' => $category->id,
            'name' => $namaProduk,
            'barcode' => $this->faker->unique()->numerify('#############'),
            'description' => $this->faker->sentence(),
            'stock_quantity' => rand(10, 2000),
            'cost_price' => $costPrice = $this->faker->numberBetween(5000, 50000),
            'price' => $costPrice + ($costPrice * (20 / 100)),
        ];
    }
}
