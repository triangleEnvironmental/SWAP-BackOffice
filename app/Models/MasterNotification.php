<?php

namespace App\Models;

use App\Traits\ExtraTapActivity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class MasterNotification extends Model
{
    use HasFactory;
    use LogsActivity;
    use ExtraTapActivity;

    //region Attributes

    protected $guarded = [];

    //endregion

    //region Methods

    protected static function boot()
    {
        self::created(function ($model) {
            if ($model->platform == 'citizen') {
                $userQuery = User::query()->isCitizen();
            } else if ($model->platform == 'moderator') {
                $userQuery = User::query()->notCitizen();
            } else {
                Log::warning("Master notification {$model->id} has no specified platform", [
                    'id' => $model->id,
                ]);
                return;
            }
            switch ($model->targetable_type) {
                case User::class:
                    // send to citizen
                    $user = User::query()->find($model->targetable_id);
                    if ($user == null) {
                        Log::warning('Cannot find user', [
                            'targetable_id' => $model->targetable_id,
                        ]);
                        return;
                    }
                    $userQuery = $userQuery
                        ->where('id', $model->targetable_id);
                    break;
                case Area::class:
                    // send to citizen address in area
                    $area = Area::query()->find($model->targetable_id);
                    if ($area == null) {
                        Log::warning('Cannot find area', [
                            'targetable_id' => $model->targetable_id,
                        ]);
                        return;
                    }
                    $userQuery = $userQuery
                        ->underArea($area->area);
                    break;
                case Institution::class:
                    // send to citizen address in institution service area
                    $institution = Institution::query()->find($model->targetable_id);
                    if ($institution == null) {
                        Log::warning('Cannot find institution', [
                            'targetable_id' => $model->targetable_id,
                        ]);
                        return;
                    }
                    $userQuery = $userQuery
                        ->underAnInstitution($institution);
                    break;
                case null:
                    // send to all citizen
                    $userQuery = $userQuery
                        ->isCitizen();
                    break;
                default:
                    Log::warning('Unknown targetable type for sending notifications', [
                        'targetable_type' => $model->targetable_type,
                    ]);
                    return;
            }
            $users = $userQuery->get();

            $users->each(function ($user) use ($model) {
                Notification::fromMaster($model, $user);
            });

            $model->update([
                'count_total_target_users' => $users->count(),
            ]);
        });

        parent::boot();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logUnguarded()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    //endregion

    //region Scopes
    public function scopeSelectImportant($query, $extra = [])
    {
        return $query->select([
            'id',
            'title',
            'description',
            'notificationable_type',
            'notificationable_id',
            ...$extra,
        ]);
    }

    public function scopeSearch($query, $keyword)
    {
        return $query->where('title', 'ilike', '%' . $keyword . '%')
            ->orWhere('description', 'ilike', '%' . $keyword . '%')
            ->orWhereHasMorph('targetable', [User::class], function ($query) use ($keyword) {
                $query->whereRaw("CONCAT_WS(' ',name,last_name) ILIKE ?", ["%$keyword%"]);
            })
            ->orWhereHasMorph('targetable', [Area::class], function ($query) use ($keyword) {
                $query->where('name_en', 'ilike', "%$keyword%")
                    ->orWhere('name_km', 'ilike', "%$keyword%");
            })
            ->orWhereHasMorph('targetable', [Institution::class], function ($query) use ($keyword) {
                $query->where('name_en', 'ilike', "%$keyword%")
                    ->orWhere('name_km', 'ilike', "%$keyword%");
            });
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

    public function scopeCitizen($query)
    {
        return $query->where('platform', 'citizen');
    }

    public function scopeModerator($query)
    {
        return $query->where('platform', 'moderator');
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
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id', 'id');
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class, 'institution_id', 'id');
    }

    public function notificationable()
    {
        return $this->morphTo('notificationable');
    }

    public function targetable()
    {
        return $this->morphTo('targetable');
    }
    //endregion

    //region GetAttributes
    //endregion
}
