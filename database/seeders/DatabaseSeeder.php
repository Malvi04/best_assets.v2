<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Category;
use App\Models\Medicine;
use App\Models\Tool;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Users
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@pestcontrol.com',
            'password' => Hash::make('password'),
            'role' => Role::Admin,
        ]);

        User::create([
            'name' => 'Atasan User',
            'email' => 'atasan@pestcontrol.com',
            'password' => Hash::make('password'),
            'role' => Role::Atasan,
        ]);

        User::create([
            'name' => 'Teknisi Budi',
            'email' => 'teknisi@pestcontrol.com',
            'password' => Hash::make('password'),
            'role' => Role::Teknisi,
        ]);

        // 2. Create Categories
        $catTools = Category::create([
            'name' => 'Peralatan Lapangan',
            'description' => 'Alat berat dan ringan untuk pekerjaan',
        ]);

        $catMeds = Category::create([
            'name' => 'Bahan Kimia',
            'description' => 'Obat-obatan dan bahan kimia',
        ]);

        // 3. Create Tools
        Tool::create([
            'category_id' => $catTools->id,
            'name' => 'Sprayer Pompa 5L',
            'stock' => 15,
            'unit' => 'Unit',
            'condition' => 'Baik',
            'description' => 'Alat penyemprot manual',
        ]);
        Tool::create([
            'category_id' => $catTools->id,
            'name' => 'Mesin Fogging Mini',
            'stock' => 5,
            'unit' => 'Unit',
            'condition' => 'Baik',
            'description' => 'Mesin fogging nyamuk',
        ]);

        // 4. Create Medicines
        Medicine::create([
            'category_id' => $catMeds->id,
            'name' => 'Racun Tikus Blok',
            'stock' => 50,
            'unit' => 'Pack',
            'description' => 'Umpan tikus',
        ]);
        Medicine::create([
            'category_id' => $catMeds->id,
            'name' => 'Cairan Pembunuh Nyamuk',
            'stock' => 20,
            'unit' => 'Liter',
            'description' => 'Obat fogging',
        ]);
    }
}
