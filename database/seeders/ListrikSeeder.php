<?php

namespace Database\Seeders;

use App\Models\PengaturanSistem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ListrikSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
           PengaturanSistem::create([
            'mode' => 'manual',
            'tarif_per_kwh' => 415,
            'batas_kwh' => 2,
            'status_perangkat' => false
        ]);
    }
}
