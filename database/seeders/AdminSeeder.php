<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User; // Yeh line add karein
use Illuminate\Support\Facades\Hash; // Yeh line add karein

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('123456'),
            'role' => 'admin'
        ]);
    }
}