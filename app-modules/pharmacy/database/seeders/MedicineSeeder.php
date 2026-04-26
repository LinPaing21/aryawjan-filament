<?php

namespace Stella\Pharmacy\Database\Seeders;

use Illuminate\Database\Seeder;
use Stella\Pharmacy\Models\Medicine;

class MedicineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Medicine::factory()->count(20)->create();
    }
}
