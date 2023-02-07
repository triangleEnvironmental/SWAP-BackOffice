<?php

namespace App\Models;

use App\Traits\ExtraTapActivity;
use Carbon\Carbon;
use Closure;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use MStaack\LaravelPostgis\Eloquent\PostgisTrait;
use MStaack\LaravelPostgis\Geometries\Geometry;
use MStaack\LaravelPostgis\Geometries\MultiPolygon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Report extends Model
{
    use HasFactory;
    use PostgisTrait;
    use SoftDeletes;
    use LogsActivity;
    use ExtraTapActivity;

    //region Attributes

    protected $guarded = [];

    protected $postgisFields = [
        'location',
    ];

    protected $postgisTypes = [
        'location' => [
            'geomtype' => 'geography',
            'srid' => 4326
        ],
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

    public function isPublic()
    {
        return !$this->isPrivate();
    }

    public function isPrivate()
    {
        return $this->reportType?->is_private;
    }

    public static function getClusters(Closure $reportQuery, Closure $getReportByIds, float $eps = 0.5, int $minpoints = 5)
    {
        $clustered_info = Report::getClusteredInfo($reportQuery, $eps, $minpoints);
        $result = collect();

        foreach ($clustered_info as $cluster) {
            // If cluster id Null, it will be list of points that not clustered
            if ($cluster->cid === null) {
                $ids = explode(',', trim($cluster->ids_in_cluster, '{}'));
                $getReportByIds($ids)
                    ->each(function ($report) use (&$result) {
                        $result->push($report);
                    });
            } else {
                $result->push([
                    'cid' => $cluster->cid,
                    'is_cluster' => true,
                    'count' => $cluster->count,
                    'bounding' => json_decode($cluster->cluster_bounding, true),
                    'location' => json_decode($cluster->cluster_centroid, true),
                ]);
            }
        }
        return $result;
    }

    public static function getClusteredInfo(Closure $reportQuery, float $eps, int $minpoints)
    {
        /** Raw Query
         * $res = DB::select(
         *     <<<SQL
         *         SELECT ct.cid,
         *                ST_AsText(ct.cluster_geom) AS cluster_points,
         *                ST_AsText(ST_Envelope(ct.cluster_geom)) AS cluster_bounding,
         *                ST_AsText(ST_Centroid(ct.cluster_geom)) AS cluster_centroid,
         *                ct.ids_in_cluster
         *         FROM (
         *             SELECT sq.cid,
         *                    ST_Collect(sq.location::geometry) AS cluster_geom,
         *                    array_agg(sq.id) AS ids_in_cluster
         *             FROM (
         *                 SELECT id,
         *                        ST_ClusterDBSCAN(location::geometry, eps := :eps, minpoints := :minpoints) over () as cid,
         *                        location
         *                 FROM reports
         *             ) sq
         *         GROUP BY cid) ct
         *     SQL,
         *     ['eps' => $eps, 'minpoints' => $minpoints]
         * );
         */

        $query = Report::query()
            ->selectRaw(
                'id, ST_ClusterDBSCAN(location::geometry, eps := ?, minpoints := ?) over () as cid, location',
                [$eps, $minpoints]
            );

        $sub = $reportQuery($query) ?? $query;

        $sub2 = DB::query()
            ->select([
                'cid',
                DB::raw('ST_Collect(location::geometry) AS cluster_geom'),
                DB::raw('array_agg(id) AS ids_in_cluster'),
                DB::raw('count(id) AS count')
            ])
            ->fromSub($sub, 'sq')
            ->groupBy('cid');

        return DB::query()
            ->select([
                'cid',
                'count',
                DB::raw('ST_AsGeoJson(ST_Envelope(cluster_geom)) AS cluster_bounding'),
                DB::raw('ST_AsGeoJson(ST_Centroid(cluster_geom)) AS cluster_centroid'),
                'ids_in_cluster',
            ])
            ->fromSub($sub2, 'ct')
            ->get();
    }

    //endregion

    //region Scopes
    public function scopeSearch($query, $keyword)
    {
        return $query->where('description', 'ilike', '%' . $keyword . '%');
    }

    public function scopeFilterByRequest($query, Request $request)
    {
        if ($request->filled('lat') && $request->filled('lng')) {
            $institution_ids = Institution::query()
                ->whoAuthorizesLocation($request->lat, $request->lng)
                ->pluck('id');

	    if ($institution_ids->count() == 0) {
                return empty_query($query);
            }

            $request->merge([
                'institution_ids' => $institution_ids,
            ]);
        }

        return $query
            ->when($request->filled('keyword'), function ($query) use ($request) {
                return $query->search($request->keyword);
            })
            ->when($request->filled('status_id'), function ($query) use ($request) {
                return $query->where('report_status_id', $request->status_id);
            })
            ->when($request->filled('sector_ids'), function ($query) use ($request) {
                return $query->whereHas('reportType', function ($query) use ($request) {
                    $query->whereIn('report_types.sector_id', $request->sector_ids);
                });
            })
            ->when($request->filled('report_type_ids'), function ($query) use ($request) {
                return $query->whereIn('report_type_id', $request->report_type_ids);
            })
            ->when($request->filled('status_ids'), function ($query) use ($request) {
                return $query->whereIn('report_status_id', $request->status_ids);
            })
            ->when($request->filled('institution_ids'), function ($query) use ($request) {
//                return $query->whereIn('report_status_id', $request->status_ids);
                return $query->where(function ($query) use ($request) {
                    foreach ($request->institution_ids as $institution_id) {
                        $query->orWhere(function ($query) use ($institution_id) {
                            $query->underInstitution($institution_id);
                        });
                    }
                });
            })
            ->when($request->filled('area_ids'), function ($query) use ($request) {
                return $query->where(function ($query) use ($request) {
                    foreach ($request->area_ids as $area_id) {
                        $query->orWhere(function ($query) use ($area_id) {
                            $area = Area::find($area_id);
                            $query->underArea($area->area);
                        });
                    }
                });
            })
            ->when($request->filled('date_range'), function ($query) use ($request) {
                return $query->whereBetween('created_at', [
                    Carbon::parse($request->date_range[0])->tz(config('app.timezone'))->startOfDay(),
                    Carbon::parse($request->date_range[1])->tz(config('app.timezone'))->endOfDay(),
                ]);
            })
            ->when(request_field_is_true($request, 'assigned_me'), function ($query) {
                return $query->where('assignee_id', get_user_id());
            });
    }

    public function scopePublic($query, $isPublic = true)
    {
        return $query->whereHas('reportType', function ($query) use ($isPublic) {
            $query->where('is_private', !$isPublic);
        });
    }

    public function scopeIgnored($query)
    {
        $showPrivateReport = SystemConfig::durationToDisplayIgnoredReport();
//        return $query->noModeration()
        return $query->unfinished()
            ->where('created_at', '<=', Carbon::now()->sub('days', $showPrivateReport));
    }

    public function scopeUnfinished($query)
    {
        $unfinishedStatuses = collect([ReportStatus::open(), ReportStatus::moderation(), ReportStatus::inProgress()]);
        return $query->whereIn('report_status_id', $unfinishedStatuses->pluck('id'));
    }

    public function scopeNoModeration($query)
    {
        return $query->whereDoesntHave('moderationHistories');
    }

    public function scopeShowOnApp($query, $report_by_me_only = false)
    {
        return $query
            ->when(!$report_by_me_only, function ($query) {
                $visibleStatusIds = ReportStatus::query()
                    ->whereIn('key', ReportStatus::$visible_on_app)
                    ->pluck('id');
                $visibleRange = SystemConfig::durationRangeForVisibleReport();
                $from = Carbon::now()->sub('days', $visibleRange[1]);
                $to = Carbon::now()->sub('days', $visibleRange[0]);
                return $query->whereBetween('created_at', [$from, $to])
                    ->whereIn('report_status_id', $visibleStatusIds);
            })
            ->when($report_by_me_only, function ($query) {
                return $query->where('reported_by_user_id', get_user_id());
            })
            ->where(function ($query) {
                return $query->public()
                    ->orWhere(function ($query) { // This condition won't show moderation because we limit only 3 statuses
                        return $query->public(false)
                            ->ignored();
                    })
                    ->when(get_user_id(), fn($query) => $query->orWhere('reported_by_user_id', get_user_id()));
            });
    }

    public function scopeUnderInstitution($query, $institution_id)
    {
        $institution = Institution::query()->find($institution_id);
        if ($institution instanceof Institution) {
            $sector_ids = $institution->sectors()->pluck('sectors.id');
            $serviceArea = $institution->serviceAreaMultiPolygon;
            if ($serviceArea instanceof MultiPolygon) {
                return $query->whereHas('reportType', function ($query) use ($sector_ids) {
                    return $query->whereIn('report_types.sector_id', $sector_ids);
                })
                    ->underArea($serviceArea);
            }
        }
        return empty_query($query);
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
                    $sector_ids = $institution->sectors()->pluck('sectors.id');
                    $serviceArea = $institution->serviceAreaMultiPolygon;
                    if ($serviceArea instanceof MultiPolygon) {
                        return $query->whereHas('reportType', function ($query) use ($sector_ids) {
                            return $query->whereIn('report_types.sector_id', $sector_ids);
                        })
                            ->underArea($serviceArea);
                    }
                }
            }
        }
        return empty_query($query);
    }

    public function scopeUnderArea($query, ?Geometry $area)
    {
        if (!$area) {
            return empty_query($query);
        }
        return $query->whereRaw(
            "ST_Contains(ST_GeomFromText(?, 4326), reports.location::geometry)",
            [$area->toWKT()]
        );
    }

    public function scopeIsResolved($query)
    {
        return $query->where('report_status_id', ReportStatus::resolved()->id);
    }

    public function scopeIsInProgress($query)
    {
        return $query->where('report_status_id', ReportStatus::inProgress()->id);
    }

    public function scopeIsOpen($query)
    {
        return $query->where('report_status_id', ReportStatus::open()->id);
    }

    public function scopeIsModeration($query)
    {
        return $query->where('report_status_id', ReportStatus::moderation()->id);
    }

    public function scopeLoadRelations($query)
    {
        return $query->with([
            'reporter' => fn($q) => $q->select(['id', 'name', 'last_name', 'role_id']),
            'reportType' => fn($q) => $q->selectImportant(),
            'status' => fn($q) => $q->selectImportant(),
            'medias' => fn($q) => $q->select(['id', 'path', 'mediable_type', 'mediable_id']),
        ]);
    }

    public function scopeSelectImportant($query, $extra = [])
    {
        return $query->select((['id', 'description', 'location', 'report_status_id', 'report_type_id', 'reported_by_user_id', 'created_at', ...$extra]));
    }

    public function scopeIsAnonymous($query)
    {
        return $query->whereNull('reported_by_user_id');
    }

    //endregion

    //region Relations
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id', 'id');
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigner_id', 'id');
    }

    public function medias()
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by_user_id', 'id');
    }

    public function reportType()
    {
        return $this->belongsTo(ReportType::class);
    }

    public function status()
    {
        return $this->belongsTo(ReportStatus::class, 'report_status_id', 'id');
    }

    public function moderationHistories()
    {
        return $this->hasMany(ModerationHistory::class, 'report_id', 'id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'report_id', 'id');
    }

    //endregion

    //region GetAttributes

    public function getSectorAttribute()
    {
        return $this->reportType?->sector()->select([
            'id',
            'name_en',
            'name_km',
            'icon_path',
        ])->first();
    }

    public function getProvinceAttribute()
    {
        $areas = Area::query()
            ->select(['id', 'name_en', 'name_km', 'institution_id', 'is_administrative'])
            ->containsPoint($this->location)
            ->orderByDesc('is_administrative')
            ->get();

        if ($areas->count() > 0) {
            $result = $areas->first();
            if ($result->is_administrative) {
                $result['label'] = 'Province/City';
                return $result;
            } else {
                $municipalities = $areas->filter(function(Area $area, $key) {
                    return $area->institution->is_municipality ?? false;
                });
                if ($municipalities->count() > 0) {
                    $result = $municipalities->first();
                    $result['label'] = 'Municipality';
                } else {
                    $result = $areas->first();
                    $result['label'] = 'Service Provider';
                }
                return $result;
            }
        }
        return null;
    }

    public function getCitizenViewModerationHistoryAttribute()
    {
        $moderation_history = $this
            ->moderationHistories()
            ->orderByDesc('created_at')
            ->get();
        $history = [];
        foreach ($moderation_history as $moderation) {
            $moderator = $moderation->moderator;
            $profile = $moderator?->profile_photo_url;
            $name = $moderator ? [
                'name_en' => $moderator->full_name,
                'name_km' => $moderator->full_name,
            ] : null;
            $institution = $moderator?->institution;
            if ($institution instanceof Institution) {
                $profile = $institution->logo_url;
                $name = [
                    'name_en' => $institution->name_en,
                    'name_km' => $institution->name_km
                ];
            }
            $history[] = [
                'id' => $moderation->id,
                'profile' => $profile,
                'name' => $name,
                'action' => 'updated',
                'old_status' => $moderation
                    ->fromStatus()
                    ->selectImportant()
                    ->first(),
                'new_status' => $moderation
                    ->toStatus()
                    ->selectImportant()
                    ->first(),
                'date' => $moderation->created_at,
            ];

        }

        $reporter = $this->reporter;
        $history[] = [
            'id' => null,
            'profile' => $reporter?->profile_photo_url,
            'name' => $reporter ? [
                'name_en' => $reporter->full_name,
                'name_km' => $reporter->full_name,
            ] : null,
            'action' => 'created',
            'old_status' => null,
            'new_status' => ReportStatus::moderation(['id', 'name_en', 'name_km', 'color']),
            'date' => $this->created_at,
        ];

        return $history;
    }

    public function getModerationHistoriesAttribute()
    {
        return $this->moderationHistories()
            ->select(['id', 'from_status_id', 'to_status_id', 'moderated_by_user_id', 'master_notification_id', 'created_at'])
            ->with([
                'fromStatus' => fn($q) => $q->select(['id', 'name_en', 'color']),
                'toStatus' => fn($q) => $q->select(['id', 'name_en', 'color']),
                'moderator' => fn($q) => $q
                    ->select(['id', 'name', 'profile_photo_path', 'institution_id'])
                    ->with([
                        'institution' => fn($q) => $q->select(['id', 'name_en'])
                    ]),
                'masterNotification' => fn($q) => $q->select(['id', 'title', 'description']),
            ])
            ->orderByDesc('created_at')
            ->get();
    }
    //endregion
}
