<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Jetstream\Jetstream;

class PageController extends Controller
{
    public function about () {
        return Inertia::render('About/Show', [
            'about' => Page::aboutContent(),
            'can_update' => Gate::allows('edit-about'),
        ]);
    }

    public function terms () {
        return Inertia::render('TermsOfService/Show', [
            'terms' => Page::termsContent(),
            'can_update' => Gate::allows('edit-terms'),
        ]);
    }

    public function policy () {
        return Inertia::render('PrivacyPolicy/Show', [
            'policy' => Page::policyContent(),
            'can_update' => Gate::allows('edit-policy'),
        ]);
    }

    public function editAbout() {
        $about = Page::about();

        Gate::authorize('edit-about');

        return Inertia::render('About/Edit', compact('about'));
    }

    public function editTerms() {
        $terms = Page::terms();

        Gate::authorize('edit-terms');

        return Inertia::render('TermsOfService/Edit', compact('terms'));
    }

    public function editPolicy() {
        $policy = Page::policy();

        Gate::authorize('edit-policy');

        return Inertia::render('PrivacyPolicy/Edit', compact('policy'));
    }

    public function update(Request $request, $page_key)
    {
        $request->validate([
            'content_en' => 'required',
            'content_km' => '',
        ]);

        Gate::authorize("edit-$page_key");

        try {
            Page::query()
                ->updateOrCreate([
                    'key' => $page_key,
                ], [
                    'key' => $page_key,
                    'content_en' => $request->content_en,
                    'content_km' => $request->content_km,
                ]);

            return message_success()->withFlash([
                'success' => 'Page is updated successfully',
            ]);
        } catch (Exception $e) {
            return message_error($e);
        }
    }
}
