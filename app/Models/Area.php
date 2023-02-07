<?php

namespace App\Models;

use App\Traits\ExtraTapActivity;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use MStaack\LaravelPostgis\Eloquent\PostgisTrait;
use MStaack\LaravelPostgis\Geometries\MultiPolygon;
use MStaack\LaravelPostgis\Geometries\Point;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Area extends Model
{
    use HasFactory;
    use PostgisTrait;
    use LogsActivity;
    use ExtraTapActivity;

    //region Attributes

    protected $guarded = [];
    protected $postgisFields = [
        'area',
    ];

    protected $postgisTypes = [
        'area' => [
            'geomtype' => 'geography',
            'srid' => 4326
        ],
    ];

    //endregion

    //region Methods
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name_en', 'name_km', 'area'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public static function cambodiaBorder(): MultiPolygon
    {
        $all = DB::query()
            ->selectRaw("ST_AsText(ST_Union(area::geometry)) as cambodia")
            ->from('areas')
            ->where('is_administrative', true)
            ->first();

        return MultiPolygon::fromWKT($all->cambodia);
    }

    //endregion

    //region Scopes
    public function scopeSelectImportant($query, $extra = [])
    {
        return $query->select(['id', 'name_en', 'name_km', 'is_administrative', 'institution_id', ...$extra]);
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

    public function scopeSearch($query, $keyword)
    {
        return $query->where('name_en', 'ilike', '%' . $keyword . '%')
            ->orWhere('name_km', 'ilike', '%' . $keyword . '%');
    }

    public function scopeContainsPoint($query, $latOrPoint, $lng = null)
    {
        return $query
            ->whereNotNull('area')
            ->whereRaw(
                "ST_Contains(area::geometry, ST_GeomFromText(?, 4326))",
                [($latOrPoint instanceof Point ? $latOrPoint : new Point($latOrPoint, $lng))->toWKT()]
            );
    }

    public function scopeServiceArea($query)
    {
        return $query
            ->where('is_administrative', false);
    }

    public function scopeAdministrative($query)
    {
        return $query
            ->where('is_administrative', true);
    }
    //endregion

    //region Relations

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class, 'institution_id', 'id');
    }

    //endregion

    //region GetAttributes

    public function getKmlAttribute()
    {
        $area = $this->area;
        if ($area !== null) {
            $wkt = $area->toWKT();
            $result = DB::select("SELECT ST_AsKML(ST_GeomFromText('$wkt', 4326)) as kml");
            if (count($result) > 0) {
                return $result[0]->kml;
            }
        }
        return null;
    }

    public function getCentroidAttribute()
    {
        try {
            $wkt = DB::query()
                ->selectRaw("ST_AsText(ST_Centroid(area::geometry)) as centroid from areas WHERE id = ?", [$this->id])
                ->first()
                ->centroid;
            return Point::fromWKT($wkt);
        } catch (Exception $e) {
            return null;
        }
    }

    //endregion
}
