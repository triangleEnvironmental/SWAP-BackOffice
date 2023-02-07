<?php

namespace App\Models;

use App\Traits\ExtraTapActivity;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SystemConfig extends Model
{
    use HasFactory;
    use LogsActivity;
    use ExtraTapActivity;

    //region Attributes

    protected $guarded = [];

    public static string $durationRangeForShowingReportKey = 'DURATION_RANGE_SHOWING_REPORT_DAYS';
    public static string $durationToDisplayIgnoredPrivateReportKey = 'IGNORED_PRIVATE_REPORT_DURATION_DAYS';

    //endregion

    //region Methods
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logUnguarded()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public static function setDurationRangeForVisibleReport(array $value)
    {
        return SystemConfig::query()
            ->updateOrCreate([
                'key' => SystemConfig::$durationRangeForShowingReportKey,
            ], [
                'key' => SystemConfig::$durationRangeForShowingReportKey,
                'value' => json_encode($value),
            ]);
    }

    public static function durationRangeForVisibleReport()
    {
        $default = [0, 60];
        // It should be json of array of 2 integers
        $config = SystemConfig::query()->firstWhere('key', SystemConfig::$durationRangeForShowingReportKey);

        if ($config == null) {
            return $default;
        }

        try {
            return json_decode($config->value);
        } catch (Exception $exception) {
            return $default;
        }
    }

    public static function setDurationToDisplayIgnoredReport(int $value)
    {
        return SystemConfig::query()
            ->updateOrCreate([
                'key' => SystemConfig::$durationToDisplayIgnoredPrivateReportKey,
            ], [
                'key' => SystemConfig::$durationToDisplayIgnoredPrivateReportKey,
                'value' => "$value",
            ]);
    }

    public static function durationToDisplayIgnoredReport(): int
    {
        $default = 20;
        // It should be string format of integer
        $config = SystemConfig::query()->firstWhere('key', SystemConfig::$durationToDisplayIgnoredPrivateReportKey);

        if ($config == null) {
            return $default;
        }

        try {
            return intval($config->value);
        } catch (Exception $exception) {
            return $default;
        }
    }

    //endregion

    //region Scopes
    //endregion

    //region Relations
    //endregion

    //region GetAttributes
    //endregion

}
