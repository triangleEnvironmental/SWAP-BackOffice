<?php

namespace App\Models;

use App\Traits\ExtraTapActivity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Faq extends Model
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
            ->logOnly(['question_en', 'question_km', 'answer_en', 'answer_km', 'sector_id', 'categories'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
    //endregion

    //region Scopes
    public function scopeSelectImportant($query, $extra = [])
    {
        return $query->select(['id', 'question_en', 'question_km', 'sector_id', ...$extra]);
    }

    public function scopeSearch($query, $keyword)
    {
        return $query->where('question_en', 'ilike', '%' . $keyword . '%')
            ->orWhere('question_km', 'ilike', '%' . $keyword . '%');
//            ->orWhere('answer_en', 'ilike', '%' . $keyword . '%')
//            ->orWhere('answer_km', 'ilike', '%' . $keyword . '%');
    }

    public function scopeFilterByRequest($query, Request $request)
    {
        return $query
            ->when($request->filled('keyword'), function ($query) use ($request) {
                return $query->search($request->keyword);
            })
            ->when($request->filled('sector_id'), function ($query) use ($request) {
                $query->where('sector_id', $request->sector_id);
            })
            ->when($request->filled('faq_category_id'), function ($query) use ($request) {
                return $query->whereHas('categories', function ($query) use ($request) {
                    $query->where('faq_categories.id', $request->faq_category_id);
                });
            })
            ->when($request->filled('faq_category_ids'), function ($query) use ($request) {
                return $query->whereHas('categories', function ($query) use ($request) {
                    $query->whereIn('faq_categories.id', $request->faq_category_ids);
                });
            });
    }
    //endregion

    //region Relations
    public function sector()
    {
        return $this->belongsTo(Sector::class, 'sector_id', 'id');
    }

    public function categories()
    {
        return $this->belongsToMany(FaqCategory::class, 'faq_has_categories', 'faq_id', 'faq_category_id');
    }

    //endregion

    //region GetAttributes
    //endregion
}
