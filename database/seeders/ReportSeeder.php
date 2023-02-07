<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Media;
use App\Models\ModerationHistory;
use App\Models\Report;
use App\Models\ReportStatus;
use App\Models\ReportType;
use App\Models\User;
use Faker\Factory;
use Faker\Provider\Lorem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use MStaack\LaravelPostgis\Geometries\MultiPoint;

class ReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $total = 1000;
        $cambodia = Area::cambodiaBorder();

        $random_location = DB::query()
            ->selectRaw(
                "ST_AsText(ST_GeneratePoints(ST_GeomFromText(?, 4326), ?, 1996)) as point",
                [$cambodia->toWKT(), $total]
            )
            ->first();

        $multipoints = MultiPoint::fromWKT($random_location->point);
        $points = $multipoints->getPoints();

        $this->command->getOutput()->progressStart(count($points));
        foreach ($points as $point) {
            $status_id = ReportStatus::query()->inRandomOrder()->first()->id;

            $report = Report::query()
                ->create([
                    'description' => Lorem::paragraph(rand(2, 6)),
                    'location' => geo_db_raw($point),
                    'count_like' => 0,
                    'report_type_id' => ReportType::query()->inRandomOrder()->first()->id,
                    'reported_by_user_id' => rand(0, 1) == 0 ? null : User::query()->inRandomOrder()->isCitizen()->first()->id,
                    'report_status_id' => $status_id,
                ]);

            $images_count = rand(1, 4);
            for ($j = 0; $j < $images_count; $j++) {
                $random = Str::random(6);
                Media::query()
                    ->create([
                        'path' => "https://loremflickr.com/1000/800?random=$random",
                        'mediable_type' => Report::class,
                        'mediable_id' => $report->id,
                        'mime_type' => 'image/png',
                        'byte_size' => 0,
                    ]);
            }

            if ($status_id > 1) {
                ModerationHistory::query()
                    ->create([
                        'from_status_id' => 1,
                        'to_status_id' => $status_id,
                        'report_id' => $report->id,
                        'moderated_by_user_id' => 1,
                    ]);
            }
            $this->command->getOutput()->progressAdvance();
        }
        $this->command->getOutput()->progressFinish();
    }
}
