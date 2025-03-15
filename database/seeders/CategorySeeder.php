<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        collect([
            'Makanan',
            'Minuman',
            'Peralatan Masak',
            'Kopi',
            'Teh',
            'Camilan',
            'Permen',
            'Roti',
            'Kue',
            'Sarapan',
            'Mie Instan',
            'Makanan Beku',
            'Makanan Ringan',
            'Bumbu Dapur',
            'Susu & Olahannya',
            'Daging & Seafood',
            'Sayur & Buah',
            'Makanan Kaleng',
            'Makanan Organik',
            'Cokelat & Manisan',
            'Air Mineral',
            'Minuman Bersoda',
            'Minuman Berenergi',
            'Jus & Sirup',
            'Teh Herbal',
            'Kopi Instan',
            'Produk Kesehatan',
            'Vitamin & Suplemen',
            'Makanan Bayi',
            'Snack Sehat',
        ])->each(fn ($category) => \App\Models\Category::query()->create(['name' => $category ]));
    }
}
