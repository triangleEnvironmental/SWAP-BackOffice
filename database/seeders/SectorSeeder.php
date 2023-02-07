<?php

namespace Database\Seeders;

use App\Models\Sector;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SectorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Sector::query()
            ->updateOrCreate([
                'id' => 1,
            ], [
                'id' => 1,
                'name_en' => 'Solid Waste',
                'name_km' => 'សំណល់រឹង',
                'icon_path' => null,
            ]);

        Sector::query()
            ->updateOrCreate([
                'id' => 2,
            ],
                [
                    'id' => 2,
                    'name_en' => 'Waste Water',
                    'name_km' => 'ទឹកសំអុយ',
                    'icon_path' => null,
                ]);
    }
}
