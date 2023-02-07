<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FaqCategory;
use App\Models\ReportType;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class FaqCategoryController extends Controller
{
    public function listPage(Request $request)
    {
        $faq_categories = FaqCategory::query()
            ->orderBy('name_en')
            ->filterByRequest($request)
            ->paginate(10)
            ->appends(request()->query());

        $faq_categories->each(function ($category) {
            $category->can_update = Gate::allows('update-faq-category');
            $category->can_delete = Gate::allows('delete-faq-category');
        });

        return Inertia::render(
            'FaqCategory/List',
            compact('faq_categories'),
        );
    }

    public function createPage()
    {
        return Inertia::render('FaqCategory/Create');
    }

    public function create(Request $request)
    {
        $request->validate([
            'name_en' => 'required',
            'name_km' => '',
        ]);

        try {
            $faq_category = FaqCategory::query()
                ->create([
                    'name_en' => $request->name_en,
                    'name_km' => $request->name_km,
                ]);

            return message_success()
                ->withFlash(['success' => 'FAQ category has been created']);
        } catch (Exception $e) {
            return message_error($e);
        }
    }

    public function delete(Request $request, $id)
    {
        $faq_category = FaqCategory::query()
            ->findOrFail($id);

        Gate::allows('delete-faq-category', $faq_category);

        try {
            $faq_category->delete();

            return message_success([])
                ->withFlash(['success' => 'FAQ category has been deleted']);
        } catch (Exception $e) {
            return message_error($e);
        }
    }

    public function editPage(Request $request, $id)
    {
        $data = FaqCategory::query()
            ->findOrFail($id);

        Gate::allows('update-faq-category', $data);

        return Inertia::render('FaqCategory/Create', compact('data'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name_en' => 'required',
            'name_km' => '',
        ]);

        $faq_category = FaqCategory::query()
            ->findOrFail($id);

        Gate::allows('update-faq-category', $faq_category);

        try {
            $faq_category->update(
                $request->only(['name_en', 'name_km'])
            );

            return message_success([])
                ->withFlash(['success' => 'FAQ category has been updated']);
        } catch (Exception $e) {
            return message_error($e);
        }
    }
}
