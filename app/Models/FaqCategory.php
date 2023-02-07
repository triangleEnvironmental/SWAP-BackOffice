<?php

namespace App\Models;

use App\Traits\ExtraTapActivity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class FaqCategory extends Model
{
    use HasFactory;
    use LogsActivity;
    use ExtraTapActivity;

    //region Attributes

    protected $guarded = [];

    //endregion

    //region Methods
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name_en', 'name_km'])
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

    public function scopeFilterByRequest($query, Request $request)
    {
        return $query
            ->when($request->filled('keyword'), function ($query) use ($request) {
                return $query->search($request->keyword);
            });
    }
    //endregion

    //region Relations
    //endregion

    //region GetAttributes
    //endregion
}
