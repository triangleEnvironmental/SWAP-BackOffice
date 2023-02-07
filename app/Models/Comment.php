<?php

namespace App\Models;

use App\Traits\ExtraTapActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Comment extends Model
{
    use HasFactory;
    use LogsActivity;
    use ExtraTapActivity;

    //region Attributes

    protected $guarded = [];

    //endregion

    //region Methods
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['text'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
    //endregion

    //region Scopes
    public function scopeShouldDisplayToUser($query, $user)
    {
        $report = $this->report;
        // For private report
        if ($report instanceof Report && !$report->isPublic()) {
            if ($user instanceof User) {
                if ($report->reported_by_user_id == $user->id || $user->isModerator()) {
                    return $query;
                }
            }
            return empty_query($query);
        }
        return $query;
    }

    //endregion

    //region Relations
    public function medias()
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function commenter()
    {
        return $this->belongsTo(User::class, 'commented_by_user_id', 'id');
    }

    public function report()
    {
        return $this->belongsTo(Report::class, 'report_id', 'id');
    }

    //endregion

    //region GetAttributes
    //endregion
}
