<?php

namespace App\Http\Controllers\API\Moderator\V1;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\MasterNotification;
use App\Models\Media;
use App\Models\ModerationHistory;
use App\Models\Report;
use App\Models\ReportStatus;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class CommentController extends Controller
{
    public function delete(Request $request, $id)
    {
        $comment = Comment::query()
            ->findOrFail($id);

        Gate::authorize('delete-a-comment', $comment);

        try {
            $comment->delete();

            return message_success(null);
        } catch (Exception $e) {
            return message_error($e);
        }
    }

    public function create(Request $request)
    {
        $request->validate([
            'report_id' => 'required',
            'text' => 'required',
            'images' => 'array'
        ]);

        $report = Report::query()
            ->myAuthority()
            ->findOrFail($request->report_id);

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

            $comment = $comment->refresh();
            $comment->load([
                'medias' => fn($q) => $q->select(['medias.id', 'path', 'mediable_type', 'mediable_id']),
                'commenter' => fn($q) => $q
                    ->selectImportant()
                    ->with(['institution' => fn($q) => $q->select(['institutions.id', 'name_en', 'name_km', 'logo_path'])])
            ]);

            DB::commit();
            return message_success($comment);
        } catch (Exception $e) {
            DB::rollBack();
            return message_error($e);
        }
    }
}
