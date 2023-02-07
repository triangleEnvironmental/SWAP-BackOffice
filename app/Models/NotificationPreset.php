<?php

namespace App\Models;

use App\Traits\ExtraTapActivity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class NotificationPreset extends Model
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
            ->logUnguarded()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
    //endregion

    //region Scopes
    public function scopeSearch($query, $keyword)
    {
        return $query->where('title', 'ilike', '%' . $keyword . '%')
            ->orWhere('description', 'ilike', '%' . $keyword . '%');
    }

    public function scopeFilterByRequest($query, Request $request)
    {
        return $query
            ->when($request->filled('keyword'), function ($query) use ($request) {
                return $query->search($request->keyword);
            })
            ->when($request->filled('institution_id'), function ($query) use ($request) {
                return $query->where('institution_id', $request->institution_id);
            })
            ->when($request->filled('date_range'), function ($query) use ($request) {
                return $query->whereBetween('created_at', [
                    Carbon::parse($request->date_range[0])->tz(config('app.timezone'))->startOfDay(),
                    Carbon::parse($request->date_range[1])->tz(config('app.timezone'))->endOfDay(),
                ]);
            });
    }

    public function scopeMyAuthority($query)
    {
        $user = get_user();
        if ($user instanceof User) {
            if ($user->isUnderSuperAdmin()) {
                return $query;
            } else {
                $institution = $user->institution;
                if ($institution instanceof Institution) {
                    return $query->where('institution_id', $institution->id);
                }
            }
        }
        return empty_query($query);
    }

    //endregion

    //region Relations

    public function institution()
    {
        return $this->belongsTo(Institution::class, 'institution_id', 'id');
    }

    //endregion

    //region GetAttributes
    //endregion
}
