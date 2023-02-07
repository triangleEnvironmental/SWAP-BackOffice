<?php

namespace App\Http\Controllers\API\Citizen\V1;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\Sector;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use MStaack\LaravelPostgis\Geometries\Point;

class FaqController extends Controller
{
    public function list(Request $request)
    {
        try {
            $faqs = Faq::query()
                ->selectImportant()
                ->when($request->filled('sector_id'), function ($query) use ($request) {
                    $query->where('sector_id', $request->sector_id);
                })
                ->paginate(10)
                ->appends(request()->query());

            return message_success($faqs);
        } catch (Exception $e) {
            return message_error($e);
        }
    }

    public function detail(Request $request, $id)
    {
        $faq = Faq::query()->findOrFail($id);

        try {
            $faq->load([
                'sector' => fn($q) => $q->selectImportant(),
                'categories' => fn($q) => $q->select(['faq_categories.id', 'faq_categories.name_en', 'faq_categories.name_km']),
            ]);

            return message_success($faq);
        } catch (Exception $e) {
            return message_error($e);
        }
    }
}
