<?php

namespace Database\Seeders;

use App\Models\ReportType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReportTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public
    function run()
    {
        $data = [
//            [
//                'Late waste collection following pick up schedule',
//                'ការប្រមូលយឺត មិនទៀងទាត់តាមកាលវិភាគប្រមូលសំរាម',
//                true,
//            ],
//            [
//                'Missing pick up schedule',
//                'ខកខានយកសំរាមចេញ ពេលគេមកប្រមូលសំរាម',
//                true,
//            ],
//            [
//                'Overflow leachate on the streets from collection truck',
//                'ទឹកស្អុយហូរចេញពីឡានសំរាមមកលើផ្លូវ',
//                false,
//            ],
//            [
//                'Waste collectors broke/took my bin',
//                'អ្នកប្រមូលសំរាម ធ្វើអោយបែកឬយកធុងសំរាមខ្ញុំ',
//                true,
//            ],
//            [
//                'Misbehavior of waste collectors',
//                'អ្នកប្រមូលសំរាមមានអាកប្បកិរិយាមិនល្អ',
//                true,
//            ],
//            [
//                'Collection truck drove dangerously or too fast',
//                'ឡានប្រមូលសំរាមបើកបរក្នុងល្បឿនលឿនដែលអាចបង្កគ្រោះថ្នាក់',
//                false,
//            ],
//            [
//                'Remaining waste after collection',
//                'ប្រមូលសំរាមមិនអស់',
//                true,
//            ],
//            [
//                'Others',
//                'ផ្សេងៗ',
//                false,
//            ],
//            [
//                'Open burning along the roads',
//                'ដុតសំរាមតាមផ្លូវ',
//                false,
//            ],
//            [
//                'Open burning nearby water sources',
//                'ដុតសំរាមនៅក្បែរប្រភពទឺក',
//                false,
//            ],
//            [
//                'Open burning at an empty lot',
//                'ដុតសំរាមនៅដីទំនេរ',
//                false,
//            ],
//            [
//                'Illegal disposal/waste pile along the road',
//                'គំនរសំរាមនៅតាមផ្លូវ',
//                false,
//            ],
//            [
//                'Illegal disposal/waste pile nearby/in water source',
//                'គំនរសំរាមនៅជិត ឬនៅក្នុង ប្រភពទឹក',
//                false,
//            ],
//            [
//                'Illegal disposal/waste pile in an empty lot',
//                'គំនរសំរាមនៅដីទំនេរ',
//                false,
//            ],
            [
                'Illegal disposal',
                'ការទុកដាក់សំរាមខុសច្បាប់/មិនត្រឹមត្រូវ',
                false,
            ],
            [
                'Open burning',
                'ដុតសំរាម',
                false,
            ],
            [
                'Waste collection',
                'ការប្រមូលសំរាម',
                true,
            ],
            [
                'Others',
                'ផ្សេងៗ',
                false,
            ],
        ];

        foreach ($data as $reportType) {
            ReportType::query()
                ->updateOrCreate([
                    'name_en' => $reportType[0],
                ], [
                    'name_en' => $reportType[0],
                    'name_km' => $reportType[1],
                    'is_private' => $reportType[2],
                    'sector_id' => 1,
                ]);
        }
    }
}
