<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Enums\Role;
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
