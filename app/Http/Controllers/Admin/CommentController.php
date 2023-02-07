<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\MasterNotification;
use App\Models\Media;
use App\Models\Report;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class CommentController extends Controller
{
    public function list(Request $request, $report_id)
    {
        $report = Report::query()
            ->myAuthority()
            ->findOrFail($report_id);

        Gate::authorize('moderate-a-report', $report);

        try {
            $comments = $report
                ->comments()
                ->with([
                    'medias',
                    'commenter' => fn($q) => $q
                        ->selectImportant()
                        ->with(['institution' => fn($q) => $q->select(['institutions.id', 'name_en', 'logo_path'])])
                ])
                ->orderByDesc('created_at')
                ->paginate(10)
                ->appends(request()->query());

            $comments->each(function ($comment) {
               $comment->can_delete = Gate::allows('delete-a-comment', $comment);
            });

            return message_success($comments);
        } catch (Exception $e) {
            return message_error($e);
        }
    }

    public function create(Request $request, $report_id)
    {
        $request->validate([
            'text' => 'required',
            'images' => 'array'
        ]);

        $report = Report::query()
            ->myAuthority()
            ->findOrFail($report_id);

        Gate::authorize('moderate-a-report', $report);

        try {
            DB::beginTransaction();
            $comment = Comment::create([
                'text' => $request->text,
                'report_id' => $report->id,
                'commented_by_user_id' => get_user_id(),
                'is_public' => true,
            ]);

            if ($request->hasFile('images')) {
                foreach ($request->images as $file) {
                    $dir = 'comment_images';
                    $file_name = Str::random(12) . '.png';

                    $path = $file->storeAs(
                        $dir, $file_name, 'public'
                    );

                    Media::query()
                        ->create([
                            'path' => $path,
                            'mediable_type' => Comment::class,
                            'mediable_id' => $comment->id,
                            'mime_type' => 'image/png',
                            'byte_size' => $file->getSize(),
                        ]);
                }
            }

            $user = get_user();
            $institution = $user->institution;
            $commenter_display_name = $institution ? $institution->name_en : $user->full_name;
            $reporter = $report->reporter;

            if ($reporter instanceof User) {
                MasterNotification::create([
                    'title' => "$commenter_display_name commented on your report",
                    'description' => $comment->text,
                    'notificationable_type' => Report::class,
                    'notificationable_id' => $report->id,
                    'targetable_type' => User::class,
                    'targetable_id' => $reporter->id,
                    'count_total_target_users' => 1,
                    'platform' => 'citizen',
                    'institution_id' => $institution?->id,
                    'created_by_user_id' => $user->id,
                ]);
            }

            DB::commit();
            return message_success([]);
//                ->withFlash([
//                    'success' => 'Comment successfully',
//                ]);
        } catch (Exception $e) {
            DB::rollBack();
            return message_error($e);
        }
    }

    public function delete(Request $request, $id)
    {
        $comment = Comment::query()
            ->findOrFail($id);

        Gate::authorize('delete-a-comment', $comment);

        try {
            $comment->delete();

            return message_success([])
                ->withFlash(['success' => 'Comment has been deleted']);
        } catch (Exception $e) {
            return message_error($e);
        }
    }
}
