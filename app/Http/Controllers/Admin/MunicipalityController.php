<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Institution;
use App\Models\Sector;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class MunicipalityController extends Controller
{
    public function areaPage(Request $request, $id)
    {
        $institution = Institution::query()
            ->municipality()
            ->findOrFail($id);

        Gate::authorize('view-a-municipality-area', $institution);

        $data = Area::query()
            ->whereBelongsTo($institution)
            ->orderByDesc('created_at')
            ->paginate(10)
            ->appends(request()->query());

        $data->each(function ($area) use ($institution) {
            $area->append('centroid');
            $area->can_update = Gate::allows('update-a-municipality-area', $institution);
            $area->can_delete = Gate::allows('update-a-municipality-area', $institution);
            $area->can_notify = Gate::allows('send-notification-to-area', $area);
        });

        return Inertia::render(
            'Municipality/Area',
            compact('institution', 'data'),
        );
    }

    public function createArea(Request $request, $id)
    {
        $request->validate([
            'name_en' => 'required',
            'name_km' => '',
            'kml' => 'required|file',
        ]);

        $multipolygon = null;

        $municipality = Institution::query()
            ->municipality()
            ->findOrFail($id);

        try {
            $kml_content = $request->file('kml')->getContent();
            $multipolygon = kml_to_multipolygon($kml_content);
        } catch (Exception $e) {
        }

        if ($multipolygon == null) {
            return message_error('Failed to parse KML file');
        }

        try {
            $area = Area::query()->create([
                'name_en' => $request->name_en,
                'name_km' => $request->name_km,
                'area' => $multipolygon,
                'is_administrative' => false,
                'institution_id' => $municipality->id,
            ]);

            return message_success([])->withFlash(['success' => 'New area has been added']);
        } catch (Exception $e) {
            return message_error($e);
        }
    }

    public function updateArea(Request $request, $id, $area_id)
    {
        $request->validate([
            'name_en' => 'required',
            'name_km' => '',
            'kml' => 'nullable|file',
        ]);

        $municipality = Institution::query()
            ->municipality()
            ->findOrFail($id);

        $area = $municipality
            ->serviceAreas()
            ->findOrFail($area_id);

        $body = $request->only(['name_en', 'name_km']);

        if ($request->hasFile('kml')) {
            try {
                $kml_content = $request->file('kml')->getContent();
                $multipolygon = kml_to_multipolygon($kml_content);
                $body['area'] = $multipolygon;
            } catch (Exception $e) {
                return message_error('Failed to parse KML file');
            }
        }

        try {
            $area->update($body);
            return message_success([])->withFlash(['success' => 'Area has been updated']);
        } catch (Exception $e) {
            return message_error($e);
        }
    }

    public function deleteArea(Request $request, $id, $area_id)
    {
        $municipality = Institution::query()
            ->municipality()
            ->findOrFail($id);

        $area = $municipality
            ->serviceAreas()
            ->findOrFail($area_id);

        try {
            $area->delete();

            return message_success([])
                ->withFlash(['success' => 'Service area has been deleted']);
        } catch (Exception $e) {
            return message_error($e);
        }
    }

    public function listPage(Request $request)
    {
        $municipalities = Institution::query()
            ->with([
                'sectors' => fn($q) => $q->select(['sectors.id', 'sectors.name_en', 'sectors.name_km']),
            ])
            ->municipality()
            ->myAuthority()
            ->when($request->filled('keyword'), function ($query) use ($request) {
                $query->search($request->keyword);
            })
            ->orderByDesc('id')
            ->paginate(10)
            ->appends(request()->query());

        $municipalities->each(function ($municipality) {
            $municipality->can_delete = Gate::allows('delete-municipality');
            $municipality->can_update = Gate::allows('update-a-municipality', $municipality);
            $municipality->can_view_area = Gate::allows('view-a-municipality-area', $municipality);
        });

        return Inertia::render(
            'Municipality/List',
            compact('municipalities'),
        );
    }

    public function createPage(Request $request)
    {
        $sector_options = Sector::query()
            ->select(['id', 'name_en', 'name_km'])
            ->get();

        return Inertia::render('Municipality/Create', compact(
            'sector_options'
        ));
    }

    public function create(Request $request)
    {
        $request->validate([
            'name_en' => 'required',
            'name_km' => '',
            'logo' => 'nullable|mimes:jpeg,jpg,png,gif',
            'description_en' => '',
            'description_km' => '',
            'sector_ids' => 'array',
        ]);

        try {
            $data = [
                'name_en' => $request->name_en,
                'name_km' => $request->name_km,
                'description_en' => $request->description_en,
                'description_km' => $request->description_km,
                'is_service_provider' => false,
                'is_municipality' => true,
            ];

            if ($request->hasFile('logo')) {
                $dir = 'institution_logos';
                $file_name = str::random(12) . '.png';

                $data['logo_path'] = $request->file('logo')->storeAs(
                    $dir, $file_name, 'public'
                );
            }

            $municipality = Institution::query()
                ->create($data);

            $municipality->sectors()->sync($request->sector_ids);

            return message_success([])
                ->withFlash(['success' => 'Municipality has been created']);
        } catch (Exception $e) {
            return message_error($e);
        }
    }

    public function editPage(Request $request, $id)
    {
        $data = Institution::query()
            ->municipality()
            ->with([
                'sectors' => fn($q) => $q->select(['sectors.id', 'sectors.name_en', 'sectors.name_km']),
            ])
            ->findOrFail($id);

        $data->can_view_area = Gate::allows('view-a-municipality-area', $data);
        $data->can_update = Gate::allows('update-a-municipality', $data);

        $sector_options = Sector::query()
            ->select(['id', 'name_en', 'name_km'])
            ->get();

        return Inertia::render('Municipality/Create', compact(
            'sector_options',
            'data',
        ));
    }

    public function editMyMunicipalityPage(Request $request, $id)
    {
        $user = get_user();

        if ($user->institution_id != $id) {
            return message_error('This is not your municipality');
        }

        return $this->editPage($request, $id);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name_en' => 'required',
            'name_km' => '',
            'logo' => 'nullable|mimes:jpeg,jpg,png,gif',
            'description_en' => '',
            'description_km' => '',
            'sector_ids' => 'array',
        ]);

        $municipality = Institution::query()
            ->municipality()
            ->findOrFail($id);

        Gate::authorize('update-a-municipality', $municipality);

        try {

            $data = [
                'name_en' => $request->name_en,
                'name_km' => $request->name_km,
                'description_en' => $request->description_en,
                'description_km' => $request->description_km,
            ];

            if ($request->hasFile('logo')) {
                $dir = 'institution_logos';
                $file_name = str::random(12) . '.png';

                $data['logo_path'] = $request->file('logo')->storeAs(
                    $dir, $file_name, 'public'
                );
            }

            $municipality->update($data);

            $municipality->sectors()->sync($request->sector_ids);

            return message_success([])
                ->withFlash(['success' => 'Municipality has been updated']);
        } catch (Exception $e) {
            return message_error($e);
        }
    }

    public function deleteLogo(Request $request, $id)
    {
        $municipality = Institution::query()
            ->municipality()
            ->findOrFail($id);

        Gate::authorize('update-a-municipality', $municipality);

        try {

            if ($municipality->logo_path == null) {
                return message_success([])
                    ->withFlash(['warning' => 'This municipality has no logo']);
            }

            Storage::disk('public')
                ->delete($municipality->logo_path);

            $municipality->update([
                'logo_path' => null
            ]);

            return message_success([])
                ->withFlash(['info' => 'Logo has been deleted']);
        } catch (Exception $e) {
            return message_error($e);
        }
    }

    public function delete(Request $request, $id)
    {
        $municipality = Institution::query()
            ->municipality()
            ->findOrFail($id);

        Gate::allows('delete-municipality', $municipality);

        try {
            $municipality->delete();

            return message_success([])
                ->withFlash(['success' => 'Municipality has been deleted']);
        } catch (Exception $e) {
            return message_error($e);
        }
    }
}
