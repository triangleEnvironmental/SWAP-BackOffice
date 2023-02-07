<?php

namespace App\Http\Controllers\API\Moderator\V1;

use App\Http\Controllers\Controller;
use App\Models\ModerationHistory;
use Exception;
use Illuminate\Http\Request;

class ModerationController extends Controller
{
    public function history(Request $request)
    {
        try {
            $history = ModerationHistory::query()
                ->select(['id', 'from_status_id', 'to_status_id', 'moderated_by_user_id', 'master_notification_id', 'report_id', 'created_at'])
                ->whereHas('report')
                ->with([
                    'report' => fn($q) => $q->select([
                        'id',
                        'description',
                        'location',
                        'report_type_id',
                        'reported_by_user_id',
                        'report_status_id',
                        'created_at',
                        'assignee_id',
                    ])->with([
                        'medias' => fn($q) => $q->select(['id', 'path', 'mediable_type', 'mediable_id']),
                    ]),
                    'fromStatus' => fn($q) => $q->select(['id', 'name_en', 'color']),
                    'toStatus' => fn($q) => $q->select(['id', 'name_en', 'color']),
                    'moderator' => fn($q) => $q
                        ->select(['id', 'name', 'profile_photo_path', 'institution_id'])
                        ->with([
                            'institution' => fn($q) => $q->select(['id', 'name_en'])
                        ]),
                    'masterNotification' => fn($q) => $q->select(['id', 'title', 'description']),
                ])
                ->where('moderated_by_user_id', get_user_id())
                ->orderByDesc('created_at')
                ->paginate(10)
                ->appends(request()->query());
            return message_success($history);
        } catch (Exception $e) {
            return message_error($e);
        }
    }
}
