<?php

namespace App\Models;

use App\Traits\ExtraTapActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ReportStatus extends Model
{
    use HasFactory;
    use SoftDeletes;
    use LogsActivity;
    use ExtraTapActivity;

    //region Attributes

    protected $guarded = [];
    public static array $visible_on_app = ['open', 'in_progress', 'resolved'];

    //endregion

    //region Methods
    public static function firstStatus($fields = null)
    {
        return self::moderation($fields);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logUnguarded()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public static function moderation($fields = null)
    {
        return ReportStatus::query()
            ->when(is_array($fields), fn($q) => $q->select($fields))
            ->firstWhere('key', 'moderation');
    }

    public static function open($fields = null)
    {
        return ReportStatus::query()
            ->when(is_array($fields), fn($q) => $q->select($fields))
            ->firstWhere('key', 'open');
    }

    public static function inProgress($fields = null)
    {
        return ReportStatus::query()
            ->when(is_array($fields), fn($q) => $q->select($fields))
            ->firstWhere('key', 'in_progress');
    }

    public static function resolved($fields = null)
    {
        return ReportStatus::query()
            ->when(is_array($fields), fn($q) => $q->select($fields))
            ->firstWhere('key', 'resolved');
    }

    public static function disapproved($fields = null)
    {
        return ReportStatus::query()
            ->when(is_array($fields), fn($q) => $q->select($fields))
            ->firstWhere('key', 'disapproved');
    }

    //endregion

    //region Scopes
    public function scopeSearch($query, $keyword)
    {
        return $query->where('name_en', 'ilike', '%' . $keyword . '%')
            ->orWhere('name_km', 'ilike', '%' . $keyword . '%');
    }

    public function scopeSelectImportant($query, $extra = [])
    {
        return $query->select(['id', 'name_en', 'name_km', 'color', 'key', ...$extra]);
    }
    //endregion

    //region Relations
    //endregion

    //region GetAttributes
    //endregion
}
