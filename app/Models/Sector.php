<?php

namespace App\Models;

use App\Traits\ExtraTapActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Sector extends Model
{
    use HasFactory;
    use SoftDeletes;
    use LogsActivity;
    use ExtraTapActivity;

    //region Attributes

    protected $guarded = [];

    protected $appends = [
        'icon_url',
    ];

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
    public function scopeSelectImportant($query, $extra = [])
    {
        return $query->select(['id', 'name_en', 'name_km', 'icon_path', ...$extra]);
    }

    public function scopeCoverLocation($query, $latitude, $longitude)
    {
        return $query->whereHas('institutions', function ($query) use ($latitude, $longitude) {
            return $query->whoAuthorizesLocation($latitude, $longitude);
        });
    }

    public function scopeSearch($query, $keyword)
    {
        return $query->where('name_en', 'ilike', '%' . $keyword . '%')
            ->orWhere('name_km', 'ilike', '%' . $keyword . '%');
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
                    return $query->whereIn('id', $institution->sectors()->pluck('sectors.id'));
                }
            }
        }
        return empty_query($query);
    }
    //endregion

    //region Relations
    public function reportTypes()
    {
        return $this->hasMany(ReportType::class, 'sector_id', 'id');
    }

    public function institutions()
    {
        return $this->belongsToMany(Institution::class, 'institution_has_sectors', 'sector_id', 'institution_id');
    }
    //endregion

    //region GetAttributes
    public function getIconUrlAttribute()
    {
        if ($this->icon_path == null) {
            return null;
        }

        return Storage::disk('public')->url($this->icon_path);
    }
    //endregion
}
