<?php

namespace Database\Seeders;

use App\Enums\Role;
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
        User::create([
            'name' => 'Admin Utama',
            'email' => 'admin@pestcontrol.com',
            'password' => Hash::make('password'),
            'role' => Role::Admin,
        ]);

        User::create([
            'name' => 'Atasan Manajer',
            'email' => 'atasan@pestcontrol.com',
            'password' => Hash::make('password'),
            'role' => Role::Atasan,
        ]);

        User::create([
            'name' => 'Teknisi Handal',
            'email' => 'teknisi@pestcontrol.com',
            'password' => Hash::make('password'),
            'role' => Role::Teknisi,
        ]);
    }
}
