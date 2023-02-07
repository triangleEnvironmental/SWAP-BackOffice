<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Media extends Model
{
    use HasFactory;

    //region Attributes

    protected $table = 'medias';
    protected $guarded = [];
    protected $appends = [
        'media_url',
    ];

    //endregion

    //region Methods
    //endregion

    //region Scopes
    //endregion

    //region Relations
    //endregion

    //region GetAttributes

    public function getMediaUrlAttribute()
    {
        if (Str::startsWith($this->path, 'https://')) {
            return $this->path;
        } else {
            return Storage::disk('public')->url($this->path);
        }
    }

    //endregion
}
