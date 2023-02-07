<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\Sector;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class FaqController extends Controller
{
    public function listPage(Request $request)
    {
        $sector_options = Sector::query()
            ->orderBy('name_en')
            ->get();

        $faq_category_options = FaqCategory::query()
            ->orderBy('name_en')
            ->get();

        $faqs = Faq::query()
            ->orderByDesc('id')
            ->with([
                'categories' => fn($q) => $q->select(['faq_categories.id', 'faq_categories.name_en', 'faq_categories.name_km']),
                'sector' => fn($q) => $q->selectImportant(),
            ])
            ->filterByRequest($request)
            ->paginate(10)
            ->appends(request()->query());

        $faqs->each(function($faq) {
            $faq->can_update = Gate::allows('update-faq', $faq);
            $faq->can_delete = Gate::allows('delete-faq', $faq);
        });

        return Inertia::render(
            'Faq/List',
            compact('faqs', 'sector_options', 'faq_category_options'),
        );
    }

    public function createPage()
    {
        $sector_options = Sector::query()
            ->select(['id', 'name_en', 'name_km'])
            ->get();

        $faq_category_options = FaqCategory::query()
            ->select(['id', 'name_en', 'name_km'])
            ->get();

        return Inertia::render('Faq/Create', compact(
            'sector_options',
            'faq_category_options',
        ));
    }

    public function create(Request $request)
    {
        $request->validate([
            'question_en' => 'required',
            'question_km' => '',
            'answer_en' => 'required',
            'answer_km' => '',
            'sector_id' => 'required',
            'faq_category_ids' => 'array',
        ]);

        try {
            if (Sector::query()->find($request->sector_id) == null) {
                return message_error([
                    'sector_id' => 'This sector does not exist',
                ]);
            }

            $faq = Faq::query()
                ->create([
                    'question_en' => $request->question_en,
                    'question_km' => $request->question_km,
                    'answer_en' => $request->answer_en,
                    'answer_km' => $request->answer_km,
                    'sector_id' => $request->sector_id,
                ]);

            $faq->categories()->sync($request->faq_category_ids);

            return message_success([])
                ->withFlash(['success' => 'FAQ has been created']);
        } catch (Exception $e) {
            return message_error($e);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'question_en' => 'required',
            'question_km' => '',
            'answer_en' => 'required',
            'answer_km' => '',
            'sector_id' => 'required',
            'faq_category_ids' => 'array',
        ]);

        $faq = Faq::query()->findOrFail($id);

        try {
            if (Sector::query()->find($request->sector_id) == null) {
                return message_error([
                    'sector_id' => 'This sector does not exist',
                ]);
            }

            $faq->update([
                'question_en' => $request->question_en,
                'question_km' => $request->question_km,
                'answer_en' => $request->answer_en,
                'answer_km' => $request->answer_km,
                'sector_id' => $request->sector_id,
            ]);

            $faq->categories()->sync($request->faq_category_ids);

            return message_success([])
                ->withFlash(['success' => 'FAQ has been updated']);
        } catch (Exception $e) {
            return message_error($e);
        }
    }

    public function editPage(Request $request, $id)
    {
        $data = Faq::query()
            ->with([
                'categories' => fn($q) => $q->select(['faq_categories.id', 'name_en', 'name_km']),
            ])
            ->findOrFail($id);

        $sector_options = Sector::query()
            ->select(['id', 'name_en', 'name_km'])
            ->get();

        $faq_category_options = FaqCategory::query()
            ->select(['id', 'name_en', 'name_km'])
            ->get();

        return Inertia::render('Faq/Create', compact(
            'data',
            'sector_options',
            'faq_category_options'
        ));
    }

    public function delete(Request $request, $id)
    {
        $faq = Faq::query()
            ->findOrFail($id);

        Gate::allows('delete-faq', $faq);

        try {
            $faq->delete();

            return message_success([])
                ->withFlash(['success' => 'FAQ has been deleted']);
        } catch (Exception $e) {
            return message_error($e);
        }
    }
}
