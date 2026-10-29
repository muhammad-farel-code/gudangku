<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'PT Surya Abadi Nusantara',
                'contact_person' => 'Hendra Wijaya',
                'phone' => '081234567890',
                'email' => 'sales@suryaabadi.co.id',
                'address' => 'Kawasan Industri Pulogadung Blok B No. 12, Jakarta Timur',
            ],
            [
                'name' => 'CV Mega Office Supplies',
                'contact_person' => 'Rina Sugiarto',
                'phone' => '082198765432',
                'email' => 'order@megaoffice.co.id',
                'address' => 'Jl. Mangga Dua Raya No. 45, Jakarta Pusat',
            ],
            [
                'name' => 'PT Prima Pangan Mandiri',
                'contact_person' => 'Ahmad Fauzi',
                'phone' => '085712341234',
                'email' => 'distribusi@primapangan.com',
                'address' => 'Jl. Raya Bogor KM 28, Cimanggis, Depok',
            ],
            [
                'name' => 'CV Bersih Kilau Sejahtera',
                'contact_person' => 'Ratna Sari',
                'phone' => '087855566677',
                'email' => 'kontak@bersihkilau.co.id',
                'address' => 'Kawasan Pergudangan Taman Tekno Blok D No. 8, BSD, Tangerang Selatan',
            ],
            [
                'name' => 'PT Logistik Box Sentosa',
                'contact_person' => 'Dedi Kurniawan',
                'phone' => '081399887766',
                'email' => 'marketing@logistikbox.com',
                'address' => 'Jl. Industri Cikarang Barat No. 101, Bekasi',
            ],
            [
                'name' => 'PT Tech Solusindo Global',
                'contact_person' => 'Kevin Pratama',
                'phone' => '081122334455',
                'email' => 'support@techsolusindo.com',
                'address' => 'Ruko Harco Mangga Dua Blok E No. 22, Jakarta Pusat',
            ],
        ];

        foreach ($suppliers as $sup) {
            Supplier::updateOrCreate(
                ['name' => $sup['name']],
                [
                    'contact_person' => $sup['contact_person'],
                    'phone' => $sup['phone'],
                    'email' => $sup['email'],
                    'address' => $sup['address'],
                ]
            );
        }
    }
}
