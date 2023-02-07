<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\Sector;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class SectorController extends Controller
{
    public function listPage(Request $request)
    {
        $sectors = Sector::query()
            ->when($request->filled('keyword'), function ($query) use ($request) {
                $query->search($request->keyword);
            })
            ->orderBy('name_en')
            ->paginate(10)
            ->appends(request()->query());

        $sectors->each(function ($sector) {
            $sector->can_delete = Gate::allows('delete-sector');
            $sector->can_update = Gate::allows('update-sector');
        });

        return Inertia::render(
            'Sector/List',
            compact('sectors'),
        );
    }

    public function createPage(Request $request)
    {
        return Inertia::render('Sector/Create');
    }

    public function editPage(Request $request, $id)
    {
        $data = Sector::query()
            ->findOrFail($id);

        return Inertia::render('Sector/Create', compact('data'));
    }

    public function create(Request $request)
    {
        $request->validate([
            'name_en' => 'required',
            'name_km' => '',
            'icon' => 'nullable|mimes:jpeg,jpg,png,gif',
        ]);

        try {

            $data = [
                'name_en' => $request->name_en,
                'name_km' => $request->name_km,
            ];

            if ($request->hasFile('icon')) {
                $dir = 'sector_icons';
                $file_name = str::random(12) . '.png';

                $data['icon_path'] = $request->file('icon')->storeAs(
                    $dir, $file_name, 'public'
                );
            }

            $sector = Sector::query()
                ->create($data);

            return message_success([])
                ->withFlash(['success' => 'Sector has been created']);
        } catch (Exception $e) {
            return message_error($e);
        }
    }

    public function delete(Request $request, $id)
    {
        $sector = Sector::query()
            ->findOrFail($id);

        try {

            $sector->delete();

            return message_success([])
                ->withFlash(['success' => 'Sector has been deleted']);
        } catch (Exception $e) {
            return message_error($e);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name_en' => 'required',
            'name_km' => '',
            'icon' => 'nullable|mimes:jpeg,jpg,png,gif',
        ]);

        $sector = Sector::query()
            ->findOrFail($id);

        try {

            $data = [
                'name_en' => $request->name_en,
                'name_km' => $request->name_km,
            ];

            if ($request->hasFile('icon')) {
                $dir = 'sector_icons';
                $file_name = str::random(12) . '.png';

                $data['icon_path'] = $request->file('icon')->storeAs(
                    $dir, $file_name, 'public'
                );
            }

            $sector->update($data);

            return message_success([])
                ->withFlash(['success' => 'Sector has been updated']);
        } catch (Exception $e) {
            return message_error($e);
        }
    }

    public function deleteIcon(Request $request, $id)
    {
        $sector = Sector::query()
            ->findOrFail($id);

        try {

            if ($sector->icon_path == null) {
                return message_success([])
                    ->withFlash(['warning' => 'This sector has no icon']);
            }

            Storage::disk('public')
                ->delete($sector->icon_path);

            $sector->update([
                'icon_path' => null
            ]);

            return message_success([])
                ->withFlash(['info' => 'Icon has been deleted']);
        } catch (Exception $e) {
            return message_error($e);
        }
    }
}
