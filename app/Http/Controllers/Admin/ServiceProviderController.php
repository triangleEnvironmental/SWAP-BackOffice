<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Institution;
use App\Models\Sector;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ServiceProviderController extends Controller
{

    public function areaPage(Request $request, $id)
    {
        $institution = Institution::query()
            ->serviceProvider()
            ->findOrFail($id);

        Gate::authorize('view-a-service-provider-area', $institution);

        $data = Area::query()
            ->whereBelongsTo($institution)
            ->when($request->filled('keyword'), function ($query) use ($request) {
                $query->search($request->keyword);
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->appends(request()->query());

        $data->each(function ($area) use ($institution) {
            $area->append('centroid');
            $area->can_update = Gate::allows('update-a-service-provider-area', $institution);
            $area->can_delete = Gate::allows('update-a-service-provider-area', $institution);
            $area->can_notify = Gate::allows('send-notification-to-area', $area);
        });

        return Inertia::render(
            'ServiceProvider/Area',
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

        $sp = Institution::query()
            ->serviceProvider()
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
                'institution_id' => $sp->id,
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

        $sp = Institution::query()
            ->serviceProvider()
            ->findOrFail($id);

        $area = $sp
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
        $sp = Institution::query()
            ->serviceProvider()
            ->findOrFail($id);

        $area = $sp
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
        $service_providers = Institution::query()
            ->with([
                'sectors' => fn($q) => $q->select(['sectors.id', 'sectors.name_en', 'sectors.name_km']),
            ])
            ->serviceProvider()
            ->myAuthority()
            ->when($request->filled('keyword'), function ($query) use ($request) {
                $query->search($request->keyword);
            })
            ->orderByDesc('id')
            ->paginate(10)
            ->appends(request()->query());

        $service_providers->each(function ($service_provider) {
            $service_provider->can_delete = Gate::allows('delete-service-provider');
            $service_provider->can_update = Gate::allows('update-a-service-provider', $service_provider);
            $service_provider->can_view_area = Gate::allows('view-a-service-provider-area', $service_provider);
        });

        return Inertia::render(
            'ServiceProvider/List',
            compact('service_providers'),
        );
    }

    public function createPage(Request $request)
    {
        $sector_options = Sector::query()
            ->select(['id', 'name_en', 'name_km'])
            ->get();

        return Inertia::render('ServiceProvider/Create', compact(
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
                'is_service_provider' => true,
                'is_municipality' => false,
            ];

            if ($request->hasFile('logo')) {
                $dir = 'institution_logos';
                $file_name = str::random(12) . '.png';

                $data['logo_path'] = $request->file('logo')->storeAs(
                    $dir, $file_name, 'public'
                );
            }

            $sp = Institution::query()
                ->create($data);

            $sp->sectors()->sync($request->sector_ids);

            return message_success([])
                ->withFlash(['success' => 'Service provider has been created']);
        } catch (Exception $e) {
            return message_error($e);
        }
    }

    public function editPage(Request $request, $id)
    {
        $data = Institution::query()
            ->serviceProvider()
            ->with([
                'sectors' => fn($q) => $q->select(['sectors.id', 'sectors.name_en', 'sectors.name_km']),
            ])
            ->findOrFail($id);

        $data->can_view_area = Gate::allows('view-a-service-provider-area', $data);
        $data->can_update = Gate::allows('update-a-service-provider', $data);

        $sector_options = Sector::query()
            ->select(['id', 'name_en', 'name_km'])
            ->get();

        return Inertia::render('ServiceProvider/Create', compact(
            'sector_options',
            'data',
        ));
    }

    public function editMyServiceProviderPage(Request $request, $id)
    {
        $user = get_user();

        if ($user->institution_id != $id) {
            return message_error('This is not your service provider');
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

        $sp = Institution::query()
            ->serviceProvider()
            ->findOrFail($id);

        Gate::authorize('update-a-service-provider', $sp);

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

            $sp->update($data);

            $sp->sectors()->sync($request->sector_ids);

            return message_success([])
                ->withFlash(['success' => 'Service provider has been updated']);
        } catch (Exception $e) {
            return message_error($e);
        }
    }

    public function delete(Request $request, $id)
    {
        $sp = Institution::query()
            ->serviceProvider()
            ->findOrFail($id);

        Gate::allows('delete-service-provider', $sp);

        try {
            $sp->delete();

            return message_success([])
                ->withFlash(['success' => 'Service provider has been deleted']);
        } catch (Exception $e) {
            return message_error($e);
        }
    }

    public function deleteLogo(Request $request, $id)
    {
        $sp = Institution::query()
            ->serviceProvider()
            ->findOrFail($id);

        Gate::authorize('update-a-service-provider', $sp);

        try {
            if ($sp->logo_path == null) {
                return message_success([])
                    ->withFlash(['warning' => 'Service provider has no logo']);
            }

            Storage::disk('public')
                ->delete($sp->logo_path);

            $sp->update([
                'logo_path' => null
            ]);

            return message_success([])
                ->withFlash(['info' => 'Logo has been deleted']);
        } catch (Exception $e) {
            return message_error($e);
        }
    }
}
