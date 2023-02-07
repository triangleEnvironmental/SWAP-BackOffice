<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\ModerationHistory;
use App\Models\Report;
use App\Models\ReportStatus;
use App\Models\ReportType;
use App\Models\User;
use App\Services\FirebaseAuthService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DataMigrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Users
//        Create firebase auth user
        try {
            DB::beginTransaction();
            $fs = new FirebaseAuthService();
            $auth_users_json = json_decode(file_get_contents(resource_path('data_migration/exported_data/auth_users.json')));
            $users_json = json_decode(file_get_contents(resource_path('data_migration/exported_data/users.json')));
            foreach ($auth_users_json as $auth_user_json) {
                $uid = $auth_user_json->uid ?? null;
                $phone = $auth_user_json->phoneNumber ?? null;

                // Some data has no phone
                if ($phone == null) {
//                    dump("$uid has no phone");
                    continue;
                }

                $user_json = $users_json->{$uid} ?? null;

                $fs->seed_user_from_phone_number($phone, $user_json?->firstname ?? null, $user_json?->lastname ?? null, $user_json?->address->position?->_latitude ?? null, $user_json?->address?->position?->_longitude ?? null, $user_json?->address?->address ?? null, $uid, 'POSTGRES');
            }

            $default_report_type = ReportType::query()->firstWhere('name_en', 'Others');

            // Reports
            $reports_json = json_decode(file_get_contents(resource_path('data_migration/exported_data/events.json')));
            foreach ($reports_json as $key => $report_json) {

                if (!property_exists($report_json, 'position')) {
                    continue;
                }

                // Report type
//                $subtype = $report_json->subTypeEvent;
                $type_json = $report_json->typeofEvent;
//                $subtype_json = $report_json->subTypeEvent ?? null;
                $report_type = ReportType::query()
                    ->firstWhere(function ($query) use ($type_json) {
                        $query->where('name_en', $type_json)
                            ->orWhere('name_km', $type_json);
                    });

                if ($report_type == null) {
                    if ($type_json == 'select_sub_type' || $type_json == null) {
                        $report_type = $default_report_type;
                    } else {
                        throw new Exception("No report type for $type_json");
                    }
                }

                if ($report_json->status != null) {
                    $report_json->status = str_replace(' ', '_', $report_json->status);
                }

                // Report status
                $report_status = ReportStatus::query()->firstWhere('key', $report_json->status ?? null);
                if ($report_status == null) {
                    throw new Exception("No status from {$report_json->status}");
                }

                // Create report
                $report = Report::query()
                    ->create([
                        'description' => $report_json->description ?? null,
                        'location' => geo_db_raw(create_point($report_json->position->_latitude, $report_json->position->_longitude)),
                        'count_like' => $reports_json->numberLike ?? 0,
                        'report_type_id' => $report_type->id,
                        'reported_by_user_id' => $report_json->customer == null ? null : User::query()->firstWhere('firebase_uid', $report_json->customer)?->id,
                        'report_status_id' => $report_status->id,
                        'created_at' => Carbon::parse($report_json->created->_seconds),
                    ]);

                // Moderation history
                $history = collect([
                    [
                        'status' => 'open',
                        'date' => $report_json->dateOpen?->_seconds ?? null,
                    ],
                    [
                        'status' => 'in_progress',
                        'date' => $report_json->dateProgress?->_seconds ?? null,
                    ],
                    [
                        'status' => 'resolved',
                        'date' => $report_json->dateResolved?->_seconds ?? null,
                    ],
                    [
                        'status' => 'disapproved',
                        'date' => $report_json->dateDisapproved?->_seconds ?? null,
                    ],
                ]);

                $history = $history->filter(function ($h) {
                    return $h['date'] != null;
                });

                $history = $history->sort(function ($h) {
                    return $h['date'];
                });

                $status = ReportStatus::query()->firstWhere('key', 'moderation');
                foreach ($history as $h) {
                    dump($h['status']);
                    $next_status = ReportStatus::query()->firstWhere('key', $h['status']);
                    if ($next_status == null) {
                        continue;
                    }
                    ModerationHistory::query()
                        ->create([
                            'from_status_id' => $status->id,
                            'to_status_id' => $next_status->id,
                            'report_id' => $report->id,
                            'moderated_by_user_id' => null,
                            'comment_id' => null,
                            'created_at' => Carbon::parse($h['date']),
                        ]);
                    $status = $next_status;
                }

                // Images
                $images = $report_json->images ?? [];

                foreach ($images as $image) {
                    $base64 = $image->base64;
                    $bin = base64_decode($base64);
                    $filename = Str::random(12);
                    $path = "report_images/$filename.png";
                    Storage::disk('public')->put($path, $bin);

                    Media::query()
                        ->create([
                            'path' => $path,
                            'mediable_type' => Report::class,
                            'mediable_id' => $report->id,
                            'mime_type' => 'image/png',
                            'byte_size' => 0,
                        ]);
                }
            }
            DB::commit();
        } catch (Exception $e) {
            dump($e);
            DB::rollBack();
        }
    }
}
