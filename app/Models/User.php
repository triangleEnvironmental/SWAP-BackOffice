<?php

namespace App\Models;

use App\Traits\ExtraTapActivity;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Kreait\Firebase\Messaging\MessageTarget;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Laravolt\Avatar\Avatar;
use MStaack\LaravelPostgis\Eloquent\PostgisTrait;
use MStaack\LaravelPostgis\Geometries\Geometry;
use MStaack\LaravelPostgis\Geometries\MultiPolygon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use PostgisTrait;
    use SoftDeletes;
    use LogsActivity;
    use ExtraTapActivity;

    //region Attributes

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name', // Will be used as both Name and First name
        'last_name',
        'email',
        'email_verified_at',
        'password',
        'phone_number',
        'location',
        'address',
        'firebase_uid',
        'role_id',
        'institution_id',
        'current_team_id',
        'profile_photo_path',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
        'full_name',
        'citizen_area_info',
    ];

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
    public function getFcmTokens()
    {
        return $this->fcmTokens()->pluck('token');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'last_name', 'email', 'password', 'phone_number', 'location', 'address', 'role_id', 'institution_id', 'profile_photo_path', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected function defaultProfilePhotoUrl()
    {
        return generate_profile($this->full_name);
    }

    /**
     * Assuming that you have a database table which stores device tokens.
     */
    public function deviceTokens(): HasMany
    {
        return $this->hasMany(FcmTokens::class);
    }

    public function routeNotificationForFCM($notification): string|array|null
    {
        return $this->deviceTokens->pluck('token')->toArray();
    }

    public function routeNotificationForFCMTargetType($notification): ?string
    {
        return MessageTarget::TOKEN;
    }

    // Roles that can access moderator app and backoffice
    public function isModerator(): bool
    {
        return !$this->isCitizen();
    }

    public function isCitizen(): bool
    {
        return $this->role_id == 4;
    }

    // Roles that created by super admin (include super admin) but not under any institution
    public function isUnderSuperAdmin(): bool
    {
        return !$this->isCitizen() && !$this->isInstitutionUser();
    }

    public function isSuperAdmin(): bool
    {
        return $this->role_id === 1;
    }

    public function isInstitutionUser(): bool
    {
        return $this->role->is_under_institution;
    }

    public function isInstitutionAdmin(): bool
    {
        return $this->role_id === 2;
    }

    public function isInstitutionMember(): bool
    {
        return $this->role_id === 3;
    }

    //endregion

    //region Scopes
    public function scopeSearch($query, $keyword)
    {
        return $query->where('name', 'ilike', '%' . $keyword . '%')
            ->orWhere('email', 'ilike', '%' . $keyword . '%');
    }

    public function scopeIsCitizen($query)
    {
        return $query->where('role_id', 4);
    }

    public function scopeNotCitizen($query)
    {
        return $query->where('role_id', '!=', 4);
    }

    public function scopeMyCitizenAuthority($query)
    {
        $user = get_user();
        if ($user instanceof User) {
            $query = $query
                ->isCitizen();
            if ($user->isUnderSuperAdmin()) {
                return $query;
            } else if ($user->institution_id) {
                $institution = $user->institution;
                if ($institution instanceof Institution) {
                    $serviceArea = $institution->serviceAreaMultiPolygon;
                    if ($serviceArea instanceof MultiPolygon) {
                        return $query->whereRaw(
                            "ST_Contains(ST_GeomFromText(?, 4326), users.location::geometry)",
                            [$serviceArea->toWKT()]
                        );
                    }
                }
            }
        }
        return empty_query($query);
    }

    public function scopeHasPermission($query, $permission)
    {
        return $query->whereHas('role', function ($query) use ($permission) {
            return $query->whereHas('permissions', function ($query) use ($permission) {
                return $query->where('permission', $permission);
            });
        });
    }

    public function scopeMyUserAuthority($query)
    {
        $user = get_user();
        if ($user instanceof User) {
            $query = $query
                ->notCitizen();
            if ($user->isUnderSuperAdmin()) {
                return $query;
            } else if ($user->isInstitutionUser() && $user->institution_id) {
                return $query
                    ->where('institution_id', $user->institution_id);
            }
        }
        return empty_query($query);
    }

    public function scopeUnderArea($query, Geometry $area)
    {
        return $query->whereRaw(
            "ST_Contains(ST_GeomFromText(?, 4326), users.location::geometry)",
            [$area->toWKT()]
        );
    }

    public function scopeUnderAnInstitution($query, Institution $institution)
    {
        $areas = $institution->getServiceAreaMultiPolygonAttribute();
        if ($areas instanceof MultiPolygon) {
            return $query->whereRaw(
                "ST_Contains(ST_GeomFromText(?, 4326), users.location::geometry)",
                [$areas->toWKT()]
            );
        }
        return $query;
    }

    public function scopeInstitutionAdmin($query)
    {
        return $query->where('role_id', 2);
    }

    public function scopeInstitutionUser($query)
    {
        return $query->whereIn('role_id', [2, 3]);
    }

    public function scopeUnderSuperAdmin($query)
    {
        return $query->whereNotIn('role_id', [2, 3, 4]);
    }

    public function scopeSelectImportant($query, $extra = [])
    {
        return $query->select(['users.id', 'name', 'last_name', 'institution_id', 'profile_photo_path', ...$extra]);
    }

    //endregion

    //region Relations
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function fcmTokens()
    {
        return $this->hasMany(FcmTokens::class, 'user_id', 'id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'target_user_id', 'id');
    }
    //endregion

    //region GetAttributes

    public function getPermissionsAttribute(): Collection
    {
        return $this->role?->permissions()->pluck('permission') ?? collect();
    }

    public function getFullNameAttribute()
    {
        if ($this->name == null && $this->last_name == null) {
            return null;
        }
        return trim("$this->name $this->last_name", ' ');
    }

    public function getCitizenAreaInfoAttribute()
    {
        if (is_citizen_request() && $this->isCitizen() && $this->location != null) {
            return [
                'service_providers' => Institution::query()
                    ->select(['id', 'name_en', 'name_km', 'logo_path'])
                    ->with([
                        'sectors' => fn($q) => $q->select(['sectors.id', 'sectors.name_en', 'sectors.name_km', 'icon_path']),
                    ])
                    ->serviceProvider()
                    ->whoAuthorizesCitizen($this)
                    ->get(),
                'municipalities' => Institution::query()
                    ->select(['id', 'name_en', 'name_km', 'logo_path'])
                    ->with([
                        'sectors' => fn($q) => $q->select(['sectors.id', 'sectors.name_en', 'sectors.name_km', 'icon_path']),
                    ])
                    ->municipality()
                    ->whoAuthorizesCitizen($this)
                    ->get(),
                'provinces' => Area::query()
                    ->select(['id', 'name_en', 'name_km'])
                    ->administrative()
                    ->containsPoint($this->location)
                    ->get(),
            ];
        }
        return null;
    }
    //endregion
}
