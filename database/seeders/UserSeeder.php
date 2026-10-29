<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            // 3 Akun Inti Sesuai Spesifikasi UAS
            [
                'name' => 'Administrator Gudang',
                'email' => 'admin@gudangku.test',
                'password' => 'Admin@12345',
                'role' => 'admin',
                'is_active' => true,
            ],
            [
                'name' => 'Manager Operasional',
                'email' => 'manager@gudangku.test',
                'password' => 'Manager@12345',
                'role' => 'manager',
                'is_active' => true,
            ],
            [
                'name' => 'Staff Gudang 1',
                'email' => 'staff@gudangku.test',
                'password' => 'Staff@12345',
                'role' => 'staff',
                'is_active' => true,
            ],
            // Staff Tambahan (Realistis)
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@gudangku.test',
                'password' => 'Staff@12345',
                'role' => 'staff',
                'is_active' => true,
            ],
            [
                'name' => 'Siti Rahmawati',
                'email' => 'siti.rahma@gudangku.test',
                'password' => 'Staff@12345',
                'role' => 'staff',
                'is_active' => true,
            ],
            [
                'name' => 'Joko Anwar',
                'email' => 'joko.anwar@gudangku.test',
                'password' => 'Staff@12345',
                'role' => 'staff',
                'is_active' => true,
            ],
            [
                'name' => 'Dewi Lestari (Non-aktif)',
                'email' => 'dewi.nonaktif@gudangku.test',
                'password' => 'Staff@12345',
                'role' => 'staff',
                'is_active' => false,
            ],
        ];

        foreach ($users as $userData) {
            $user = User::firstOrNew(['email' => $userData['email']]);
            $user->name = $userData['name'];
            $user->password = Hash::make($userData['password']);
            // Menggunakan forceFill karena role dan is_active sengaja tidak ada di $fillable
            $user->forceFill([
                'role' => $userData['role'],
                'is_active' => $userData['is_active'],
            ])->save();
        }
    }
}
