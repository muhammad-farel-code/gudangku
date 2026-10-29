<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Elektronik',
                'description' => 'Peralatan dan perkakas elektronik gudang serta operasional',
            ],
            [
                'name' => 'Alat Tulis Kantor',
                'description' => 'Kebutuhan ATK untuk administrasi dan operasional inventaris',
            ],
            [
                'name' => 'Makanan & Minuman Kemasan',
                'description' => 'Produk konsumsi siap saji dan minuman dalam kemasan terstandar',
            ],
            [
                'name' => 'Perlengkapan Rumah Tangga',
                'description' => 'Peralatan dan perabot kebutuhan rumah tangga dan fasilitas',
            ],
            [
                'name' => 'Kebersihan',
                'description' => 'Cairan pembersih, sanitasi, dan alat kebersihan area gudang',
            ],
            [
                'name' => 'Perkakas',
                'description' => 'Peralatan mekanikal, kunci, pertukangan, dan perawatan fasilitas',
            ],
            [
                'name' => 'Kemasan & Packaging',
                'description' => 'Kardus, lakban, bubble wrap, dan material packing pengiriman',
            ],
            [
                'name' => 'Aksesoris Komputer',
                'description' => 'Perangkat periferal, kabel jaringan, adapter, dan aksesoris PC',
            ],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['name' => $cat['name']],
                ['description' => $cat['description']]
            );
        }
    }
}
