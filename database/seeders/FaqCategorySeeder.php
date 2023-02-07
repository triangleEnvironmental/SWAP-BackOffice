<?php

namespace Database\Seeders;

use App\Models\FaqCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FaqCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        FaqCategory::query()
            ->updateOrCreate([
                "name_en" => "Tidiness",
            ], [
                "name_en" => "Tidiness",
                "name_km" => "សណ្ដាប់ធ្នាប់",
            ]);

        FaqCategory::query()
            ->updateOrCreate([
                "name_en" => "Hygiene",
            ], [
                "name_en" => "Hygiene",
                "name_km" => "អនាម័យ",
            ]);

        FaqCategory::query()
            ->updateOrCreate([
                "name_en" => "Environment",
            ], [
                "name_en" => "Environment",
                "name_km" => "បរិស្ថាន",
            ]);

        FaqCategory::query()
            ->updateOrCreate([
                "name_en" => "Creativity",
            ], [
                "name_en" => "Creativity",
                "name_km" => "គំនិតច្នៃប្រឌិត",
            ]);
    }
}
