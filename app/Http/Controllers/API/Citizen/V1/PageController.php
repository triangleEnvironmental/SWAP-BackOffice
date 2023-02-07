<?php

namespace App\Http\Controllers\API\Citizen\V1;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\Institution;
use App\Models\Page;
use App\Models\Sector;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MStaack\LaravelPostgis\Geometries\Point;

class PageController extends Controller
{
    public function home_data(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        try {
            $point = create_point($request->latitude, $request->longitude);

            $municipalities = Institution::query()
                ->selectImportant(['is_municipality'])
                ->municipality()
                ->whoAuthorizesLocation($point->getLat(), $point->getLng())
                ->get();

            $service_providers = Institution::query()
                ->selectImportant(['is_service_provider'])
                ->serviceProvider()
                ->whoAuthorizesLocation($point->getLat(), $point->getLng())
                ->get();

            $sectors = Sector::query()
                ->selectImportant()
                ->coverLocation($request->latitude, $request->longitude)
                ->with([
                    'reportTypes' => fn($q) => $q
                        ->selectImportant(['is_private'])
                        ->whetherCanPrivate($request, $request->latitude, $request->longitude),
                ])
                ->whereHas('reportTypes', function ($query) use ($request) {
                    $query
                        ->selectImportant(['is_private'])
                        ->whetherCanPrivate($request, $request->latitude, $request->longitude);
                })
                ->get();

            $faqs = Faq::query()
                ->select([
                    'id',
                    'question_en',
                    'question_km',
                    'sector_id',
                ])
                ->with([
                    'sector' => fn($q) => $q->select(['id', 'name_en', 'name_km', 'icon_path']),
                ])
                ->where(function ($query) use ($request, $sectors) {
                    $user = get_user();
                    if ($sectors->count() == 0 && $user instanceof User && $user->location instanceof Point) {
                        return $query->whereHas('sector', function ($query) use ($user) {
                            $location = $user->location;
                            $query->coverLocation($location->getLat(), $location->getLng());
                        });
                    } else {
                        return $query->whereIn('sector_id', $sectors->pluck('id'));
                    }
                })
                ->inRandomOrder(rand(0, 100))
                ->take(5)
                ->get();

            return message_success(
                compact(
                    'municipalities',
                    'service_providers',
                    'sectors',
                    'faqs',
                )
            );
        } catch (Exception $e) {
            return message_error($e);
        }
    }

    public function about()
    {
        return message_success(Page::about());
    }

    public function terms()
    {
        return message_success(Page::terms());
    }

    public function policy()
    {
        return message_success(Page::policy());
    }

}
