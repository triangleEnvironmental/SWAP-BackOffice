<?php

namespace Database\Seeders;

use App\Models\ReportStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReportStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        ReportStatus::query()
            ->updateOrCreate([
                'id' => 1,
            ], [
                'id' => 1,
                'key' => 'moderation',
                'name_en' => 'Moderation',
                'name_km' => 'សម្របសម្រួល',
                'color' => '#199cff',
            ]);

        ReportStatus::query()
            ->updateOrCreate([
                'id' => 2,
            ], [
                'id' => 2,
                'key' => 'open',
                'name_en' => 'Open',
                'name_km' => 'របាយការណ៍ថ្មី',
                'color' => '#f1526e',
            ]);

        ReportStatus::query()
            ->updateOrCreate([
                'id' => 3,
            ], [
                'id' => 3,
                'key' => 'in_progress',
                'name_en' => 'In Progress',
                'name_km' => 'កំពុងដំណើរការ',
                'color' => '#fda005',
            ]);

        ReportStatus::query()
            ->updateOrCreate([
                'id' => 4,
            ], [
                'id' => 4,
                'key' => 'resolved',
                'name_en' => 'Resolved',
                'name_km' => 'បានបញ្ចប់',
                'color' => '#00c689',
            ]);

        ReportStatus::query()
            ->updateOrCreate([
                'id' => 5,
            ], [
                'id' => 5,
                'key' => 'disapproved',
                'name_en' => 'Disapproved',
                'name_km' => 'បានបដិសេធ',
                'color' => '#000000',
            ]);
    }
}
