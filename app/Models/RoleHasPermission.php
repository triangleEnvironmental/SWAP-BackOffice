<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoleHasPermission extends Model
{
    use HasFactory;

    //region Attributes

    protected $guarded = [];

    //endregion

    //region Methods
    //endregion

    //region Scopes
    //endregion

    //region Relations

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    //endregion

    //region GetAttributes
    //endregion
}
