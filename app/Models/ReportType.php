<?php

namespace App\Models;

use App\Traits\ExtraTapActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use MStaack\LaravelPostgis\Geometries\Point;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ReportType extends Model
{
    use HasFactory;
    use SoftDeletes;
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
    public function scopeWhetherCanPrivate($query, Request $request, $latitude, $longitude)
    {
        $user = get_user();
        if ($user == null) {
            return $query->public();
        }

        $location = $user->location;
        if ($location instanceof Point) {
            $if_user_stay_in_their_address_area = Area::query()
                ->serviceArea()
                ->containsPoint($latitude, $longitude)
                ->containsPoint($location->getLat(), $location->getLng())
                ->exists();

            if ($if_user_stay_in_their_address_area) {
                return $query;
            } else {
                return $query->public();
            }
        }
        return $query->public();
    }

    public function scopeSelectImportant($query, $extra = [])
    {
        return $query->select(['id', 'name_en', 'name_km', 'sector_id', 'is_private', ...$extra]);
    }

    public function scopeSearch($query, $keyword)
    {
        return $query->where('name_en', 'ilike', '%' . $keyword . '%')
            ->orWhere('name_km', 'ilike', '%' . $keyword . '%');
    }

    public function scopePrivate($query)
    {
        return $query->where('is_private', true);
    }

    public function scopePublic($query)
    {
        return $query->where('is_private', false);
    }
    //endregion

    //region Relations
    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }
    //endregion

    //region GetAttributes

    public function getMediaUrlAttribute()
    {
        if (Str::startsWith($this->path, 'https://')) {
            return $this->icon_path;
        } else {
            return Storage::disk('public')->url($this->icon_path);
        }
    }

    //endregion
}
