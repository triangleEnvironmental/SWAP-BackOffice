<?php

namespace Database\Seeders;

use App\Models\Area;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $provinces = [
            'Banteay Meanchey' => 'បន្ទាយមានជ័យ',
            'Battambang' => 'បាត់ដំបង',
            'Kampong Cham' => 'កំពង់ចាម',
            'Kampong Chhnang' => 'កំពង់ឆ្នាំង',
            'Kampong Speu' => 'កំពង់ស្ពឺ',
            'Kampong Thom' => 'កំពង់ធំ',
            'Kampot' => 'កំពត',
            'Kandal' => 'កណ្ដាល',
            'Kep' => 'កែប',
            'Koh Kong' => 'កោះកុង',
            'Kratie' => 'ក្រចេះ',
            'Mondul Kiri' => 'មណ្ឌលគិរី',
            'Oddar Meanchey' => 'ឧត្តរមានជ័យ',
            'Pailin' => 'ប៉ែលិន',
            'Phnom Penh' => 'ភ្នំពេញ',
            'Preah Sihanouk' => 'ព្រះសីហនុ',
            'Preah Vihear' => 'ព្រះវិហារ',
            'Prey Veng' => 'ព្រៃវែង',
            'Pursat' => 'ពោធិ៍សាត់',
            'Ratanak Kiri' => 'រតនគិរី',
            'Siem Reap' => 'សៀមរាប',
            'Stung Treng' => 'ស្ទឹងត្រែង',
            'Svay Rieng' => 'ស្វាយរៀង',
            'Takeo' => 'តាកែវ',
            'Tboung Khmum' => 'ត្បូងឃ្មុំ',
        ];
        foreach (array_keys($provinces) as $province_name) {
            $full_path = database_path("seeders/provinces/$province_name.kml");
            $kml = file_get_contents($full_path);

            try {
                $multipolygon = kml_to_multipolygon($kml);
                $wkt = $multipolygon->toWKT();

                Area::query()
                    ->create([
                        'name_en' => $province_name,
                        'name_km' => $provinces[$province_name],
                        'area' => DB::raw("ST_GeomFromText('$wkt', 4326)"),
                        'is_administrative' => true,
                    ]);
            } catch (Exception $e) {
                dd($e);
            }
        }
    }
}
