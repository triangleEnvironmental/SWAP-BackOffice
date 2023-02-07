<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FcmTokens extends Model
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
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    //endregion

    //region GetAttributes
    //endregion
}
