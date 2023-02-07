<?php

namespace App\Http\Controllers\API\Citizen\V1;

use App\Http\Controllers\Controller;
use App\Models\Sector;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use MStaack\LaravelPostgis\Geometries\Point;

class SectorController extends Controller
{
    public function get_available(Request $request)
    {
        $request->validate([
            'latitude' => 'required',
            'longitude' => 'required'
        ]);

        try {
            $user = get_user();

            $available_sectors = Sector::query()
                ->selectImportant()
                ->coverLocation($request->latitude, $request->longitude)
                ->get();

            if ($available_sectors->count() == 0 && $user instanceof User && $user->location instanceof Point) {
                $location = $user->location;

                $available_sectors = Sector::query()
                    ->selectImportant()
                    ->coverLocation($location->getLat(), $location->getLng())
                    ->get();
            }

            return message_success($available_sectors);
        } catch (Exception $e) {
            return message_error($e);
        }
    }
}
