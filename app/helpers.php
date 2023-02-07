<?php

use App\Classes\Permissions;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

//use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\Pure;
use MStaack\LaravelPostgis\Geometries\LineString;
use MStaack\LaravelPostgis\Geometries\MultiPolygon;
use MStaack\LaravelPostgis\Geometries\Point;
use MStaack\LaravelPostgis\Geometries\Polygon;
use MStaack\LaravelPostgis\Geometries\Geometry;
use Spinen\Geometry\Geometries\MultiPolygon as SpinenMultipolygon;
use Spinen\Geometry\Geometries\Polygon as SpinenPolygon;
use Spinen\Geometry\Geometry as SpinenGeometry;
use Spinen\Geometry\Support\TypeMapper;

if (!function_exists('not_found')) {
    function not_found($thing)
    {
        return response_error(
            new Exception("$thing not found"),
            404
        );
    }
}

if (!function_exists('response_success')) {
    function response_success($data)
    {
        return response(
            gzencode(json_encode(
                [
                    "data" => $data,
                ]
            )),
        )->withHeaders([
            'Content-Encoding' => 'gzip'
        ]);
    }
}

if (!function_exists('response_error')) {
    function response_error($exception, $status_code = 500, $data = null)
    {
        return response([
            "data" => $data,
            "message" => $exception->getMessage(),
        ], $status_code);
    }
}

if (!function_exists('create_point')) {
    #[Pure] function create_point($lat, $lng): Point
    {
        return new Point($lat, $lng);
    }
}

if (!function_exists('create_linestring')) {
    function create_linestring($linestrings): LineString
    {
        return new LineString(array_map(function ($linestring) {
            return create_point($linestring[1], $linestring[0]);
        }, $linestrings));
    }
}

if (!function_exists('create_polygon')) {
    function create_polygon($linestrings): Polygon
    {
        return new Polygon(array_map(function ($linestring) {
            return create_linestring($linestring);
        }, $linestrings));
    }
}

if (!function_exists('create_multipolygon')) {
    function create_multipolygon($polygons): MultiPolygon
    {
        return new Multipolygon(array_map(function ($polygon) {
            return create_polygon($polygon);
        }, $polygons));
    }
}

if (!function_exists('kml_to_multipolygon')) {
    /**
     * @throws Exception
     */
    function kml_to_multipolygon($kml_string): MultiPolygon
    {
        $geometry = new SpinenGeometry(new geoPHP(), new TypeMapper());
        $area = $geometry->parseKml($kml_string);
        $geo_json = json_decode($area->out('json')); // Doc here : https://github.com/phayes/geoPHP/wiki/Example-format-converter
        if ($geo_json->type === 'MultiPolygon') {
            return create_multipolygon($geo_json->coordinates);
        } else if ($geo_json->type === 'Polygon') {
            return create_multipolygon([$geo_json->coordinates]);
        }
        throw new Exception("Cannot find area geo data");
    }
}

if (!function_exists('to_filename')) {
    function to_filename($str)
    {
        return Str::replace(['{', '}', '\\', '*', '?', '/', '!', "'", '"', ':', '@', '`', '|', '='], '_', $str);
    }
}

if (!function_exists('first_index_where')) {
    function first_index_where($collection, $callback): int
    {
        $index = 0;
        foreach ($collection as $item) {
            if ($callback($item)) {
                return $index;
            }
            $index++;
        }
        return -1;
    }
}

if (!function_exists('available_roles')) {
    function available_roles(): Collection|array
    {
        return Role::query()->get();
    }
}

if (!function_exists('permissions_of')) {
    function permissions_of(User $user): Collection
    {
        return $user->role->permissions()->pluck('permission');
    }
}

if (!function_exists('message_success')) {
    function message_success($data = [], $status = 200): JsonResponse|RedirectResponse
    {
        if (Request::wantsJson()) {
            if ($data instanceof LengthAwarePaginator || $data instanceof CursorPaginator) {
                $json = $data->toArray();
                unset($json['links']);
                return new JsonResponse($json, $status);
            } else {
                return new JsonResponse([
                    'message' => 'Success',
                    'data' => $data
                ], $status);
            }
        }
        return back();
    }
}

if (!function_exists('get_user_id')) {
    function get_user_id()
    {
        return Auth::id() ?? Auth::guard('web')->id() ?? Auth::guard('sanctum')->id();
    }
}

if (!function_exists('get_user')) {
    function get_user()
    {
        $id = Auth::id() ?? Auth::guard('web')->id() ?? Auth::guard('sanctum')->id();
        if ($id == null) {
            return null;
        }
        return User::find($id);
    }
}

/**
 * Key : danger, success, info, warning
 */
if (!function_exists('message_error')) {
    function message_error($errors, $data = null, $status = null)
    {
        if (Request::wantsJson()) {
            return new JsonResponse([
                'message' => $errors instanceof Exception ? $errors->getMessage() : $errors,
                'data' => $data,
            ], $status != null ? $status : ($errors instanceof Exception ? 500 : 400));
        } else {
            if ($errors instanceof Exception) {
                return back()
                    ->withErrors($data)
                    ->withFlash([
                        'danger' => $errors->getMessage(),
                    ]);
            } else {
                return back()
                    ->withErrors($data)
                    ->withFlash(is_array($errors) ?
                        $errors :
                        [
                            'danger' => $errors
                        ]);
            }
        }
    }
}

if (!function_exists('permit')) {
    #[Pure] function permit(string|array $name, $closure = null): Permissions
    {
        return new Permissions($name, $closure);
    }
}

if (!function_exists('geo_db_raw')) {
    function geo_db_raw(Geometry $geom)
    {
        $wkt = $geom->toWKT();
        return DB::raw("ST_GeomFromText('$wkt', 4326)");
    }
}

if (!function_exists('empty_query')) {
    function empty_query($query)
    {
        return $query->where("id", '<', 0);
    }
}

if (!function_exists('generate_profile')) {
    function generate_profile($name)
    {
        return url('gravatar?full_name=' . urlencode($name));
    }
}

if (!function_exists('request_field_is_true')) {
    function request_field_is_true($request, $field_name)
    {
        return $request->filled($field_name) && $request->{$field_name} === 'true';
    }
}

if (!function_exists('is_citizen_request')) {
    function is_citizen_request()
    {
        return Str::contains(request()->getRequestUri(), 'api/citizen/');
    }
}

if (!function_exists('is_moderator_request')) {
    function is_moderator_request()
    {
        return Str::contains(request()->getRequestUri(), 'api/moderator/');
    }
}

if (!function_exists('filename_sanitizer')) {
    function filename_sanitizer($unsafeFilename){

        // our list of "unsafe characters", add/remove characters if necessary
        $dangerousCharacters = array(" ", '"', "'", "&", "/", "\\", "?", "#");

        // every forbidden character is replaced by an underscore
        $safe_filename = str_replace($dangerousCharacters, '_', $unsafeFilename);

        return preg_replace("/_+/", '_', $safe_filename);
    }
}
