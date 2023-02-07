<?php

namespace App\Models;

use App\Traits\ExtraTapActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use MStaack\LaravelPostgis\Geometries\MultiPolygon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Institution extends Model
{
    use HasFactory;
    use SoftDeletes;
    use LogsActivity;
    use ExtraTapActivity;

    //region Attributes

    protected $guarded = [];

    protected $appends = [
        'logo_url'
    ];

    //endregion

    //region Methods
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name_en', 'name_km', 'description_en', 'description_km', 'logo_path', 'is_service_provider', 'is_municipality', 'sectors'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
    //endregion

    //region Scopes
    public function scopeSelectImportant($query, $extra = [])
    {
        return $query->select(['id', 'name_en', 'name_km', 'logo_path', ...$extra]);
    }

    public function scopeSearch($query, $keyword)
    {
        return $query->where('name_en', 'ilike', '%' . $keyword . '%')
            ->orWhere('name_km', 'ilike', '%' . $keyword . '%');
    }

    public function scopeServiceProvider($query)
    {
        return $query->where('is_service_provider', true);
    }

    public function scopeMunicipality($query)
    {
        return $query->where('is_municipality', true);
    }

    public function scopeMyAuthority($query) {
        $user = get_user();

        if ($user instanceof User) {
            if ($user->isUnderSuperAdmin()) {
                return $query;
            } else if ($user->isInstitutionUser() && $user->institution_id) {
            // } else if ($user->isInstitutionAdmin() && $user->institution_id) {
                return $query->where('id', $user->institution_id);
            }
        }

        return empty_query($query);
    }

    public function scopeWhoAuthorizesReport($query, Report $report)
    {
        $sector = $report->sector;
        return $query->whereHas('serviceAreas', function ($query) use ($report) {
            return $query->containsPoint($report->location);
        })->whereHas('sectors', function($query) use ($sector) {
            return $query->where('sectors.id', $sector->id);
        });
    }

    public function scopeWhoAuthorizesCitizen($query, User $citizen)
    {
        if ($citizen->location == null) {
            return empty_query($query);
        }
        return $query->whereHas('serviceAreas', function ($query) use ($citizen) {
            return $query->containsPoint($citizen->location);
        });
    }

    public function scopeWhoAuthorizesLocation($query, $latitude, $longitude)
    {
        return $query->whereHas('serviceAreas', function ($query) use ($latitude, $longitude) {
            return $query->containsPoint(create_point($latitude, $longitude));
        });
    }

    //endregion

    //region Relations
    public function users()
    {
        return $this->hasMany(User::class, 'institution_id', 'id');
    }

    public function sectors()
    {
        return $this->belongsToMany(Sector::class, 'institution_has_sectors', 'institution_id', 'sector_id');
    }

    public function serviceAreas()
    {
        return $this->hasMany(Area::class, 'institution_id', 'id');
    }

    //endregion

    //region GetAttributes

    public function getServiceAreaMultiPolygonAttribute()
    {
        $all = DB::query()
            ->selectRaw("ST_AsText(ST_Union(area::geometry)) as service_area")
            ->from('areas')
            ->where('institution_id', $this->id)
            ->first();

        if (!$all->service_area) {
            return null;
        }

        return MultiPolygon::fromWKT($all->service_area);
    }

    public function getLogoUrlAttribute()
    {
        if (!$this->logo_path) {
            return generate_profile($this->name_en);
        }
        if (Str::startsWith($this->logo_path, 'https://')) {
            return $this->logo_path;
        } else {
            return Storage::disk('public')->url($this->logo_path);
        }
    }

    //endregion

}
