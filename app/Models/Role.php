<?php

namespace App\Models;

use App\Traits\ExtraTapActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Role extends Model
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
    public function scopeSearch($query, $keyword)
    {
        return $query->where('name_en', 'ilike', '%' . $keyword . '%')
            ->orWhere('name_km', 'ilike', '%' . $keyword . '%');
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(RoleHasPermission::class);
    }

    public function scopeSomeOneAuthority($query, $user)
    {
        if ($user->isUnderSuperAdmin()) {
            return $query->where('id', '!=', 4); // Not citizen
        } else if ($user->isInstitutionUser()) {
            return $query->where('is_under_institution', true);
        }
        return empty_query($query);
    }

    public function scopeMyAuthority($query)
    {
        $user = get_user();
        if ($user instanceof User) {
            return $query->someOneAuthority($user);
        }
        return empty_query($query);
    }

    //endregion

    //region Relations
    //endregion

    //region GetAttributes
    //endregion
}
