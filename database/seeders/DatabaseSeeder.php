<?php

namespace Database\Seeders;

use App\Models\PengaturanSistem;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        PengaturanSistem::factory()->create([
            'mode' => 'otomatis',
            'tarif_per_kwh' => 45,
            'batas_per_kwh' => 10,2,
            'status_perangkat' => false
        ]);
    }
}
