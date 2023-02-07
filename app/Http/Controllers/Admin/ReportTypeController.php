<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\ReportType;
use App\Models\Sector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ReportTypeController extends Controller
{
    public function listPage(Request $request)
    {
        $sector_options = Sector::query()
            ->orderBy('name_en')
            ->get();

        $report_types = ReportType::query()
            ->with(['sector'])
            ->when($request->filled('keyword'), function ($query) use ($request) {
                $query->search($request->keyword);
            })
            ->when($request->filled('sector_id'), function ($query) use ($request) {
                $query->where('sector_id', $request->sector_id);
            })
            ->orderBy('name_en')
            ->paginate(10)
            ->appends(request()->query());

        $report_types->each(function ($type) {
            $type->can_delete = Gate::allows('delete-report-type');
            $type->can_update = Gate::allows('update-report-type');
        });

        return Inertia::render(
            'ReportType/List',
            compact('report_types', 'sector_options'),
        );
    }

    public function createPage()
    {
        $sector_options = Sector::query()
            ->orderBy('name_en')
            ->get();

        return Inertia::render('ReportType/Create',
            compact('sector_options')
        );
    }

    public function create(Request $request)
    {
        $request->validate([
            'name_en' => 'required',
            'name_km' => '',
            'is_private' => 'required',
            'sector_id' => 'required',
        ]);

        try {
            $report_type = ReportType::query()
                ->create($request->only(['name_en', 'name_km', 'is_private', 'sector_id']));

            return message_success([])
                ->withFlash(['success' => 'Report type has been created']);
        } catch (Exception $e) {
            return message_error($e);
        }
    }

    public function delete(Request $request, $id)
    {
        $reportType = ReportType::query()
            ->findOrFail($id);

        try {
            $reportType->delete();

            return message_success([])
                ->withFlash(['success' => 'Report type has been deleted']);
        } catch (Exception $e) {
            return message_error($e);
        }
    }

    public function editPage(Request $request, $id)
    {
        $sector_options = Sector::query()
            ->orderBy('name_en')
            ->get();

        $data = ReportType::query()
            ->findOrFail($id);

        return Inertia::render('ReportType/Create', compact('data', 'sector_options'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name_en' => 'required',
            'name_km' => '',
            'is_private' => 'required',
            'sector_id' => 'required',
        ]);

        $report_type = ReportType::query()
            ->findOrFail($id);

        try {
            $report_type->update(
                $request->only(['name_en', 'name_km', 'is_private', 'sector_id'])
            );

            return message_success([])
                ->withFlash(['success' => 'Report type has been updated']);
        } catch (Exception $e) {
            return message_error($e);
        }
    }
}
