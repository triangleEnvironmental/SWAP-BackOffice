<?php

namespace App\Models;

use App\Notifications\WasteTrackerPushNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification as Notify;
use Kreait\Firebase\Messaging\MessageTarget;

class Notification extends Model
{
    use HasFactory;

    //region Attributes

    protected $guarded = [];

    //endregion

    //region Methods
    protected static function boot()
    {
        self::created(function ($model) {
            $master = $model->master;
            $user = $model->targetUser;

            if ($model instanceof Notification && $user instanceof User) {
                $tokens = $user->getFcmTokens();
                if ($tokens->count() > 0) {
                    $payload = Notification::query()
                        ->select(['id', 'read_at', 'master_notification_id'])
                        ->with([
                            'master' => fn($q) => $q->select(['id', 'title', 'notificationable_type', 'notificationable_id']),
                        ])
                        ->find($model->id);

                    $routing = null;

                    if ($master->platform == 'citizen') {
//                        if ($master->notificationable_type == Report::class) {
//                            $routing = [
//                                'name' => '/report_detail',
//                                'arguments' => [
//                                    'id' => $master->notificationable_id,
//                                ],
//                            ];
//                        } else {
                        $routing = [
                            'name' => '/notification_detail',
                            'arguments' => [
                                'id' => $model->id,
                            ],
                        ];
//                        }
                    } else if ($master->platform == 'moderator') {
                        if ($master->notificationable_type == Report::class) {
                            $routing = [
                                'name' => '/report_detail',
                                'arguments' => [
                                    'id' => $master->notificationable_id,
                                ],
                            ];
                        }
                    }

                    Notify::route('FCM', $user->getFcmTokens()->toArray())
                        ->route('FCMTargetType', MessageTarget::TOKEN)
                        ->notify(new WasteTrackerPushNotification(
                            $master->title,
                            $master->description,
                            [
                                'data' => json_encode($payload->toArray()),
                                'routing' => json_encode($routing),
                            ],
                        ));
                }
            }
        });
        parent::boot();
    }

    static function fromMaster(MasterNotification $master, User $user)
    {
        return Notification::create([
            'master_notification_id' => $master->id,
            'target_user_id' => $user->id,
        ]);
    }
    //endregion

    //region Scopes
    public function scopeSelectImportant($query, $extra = [])
    {
        return $query->select([
            'id',
            'read_at',
            'master_notification_id',
            'created_at',
            ...$extra,
        ]);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }
    //endregion

    //region Relations
    public function master()
    {
        return $this->belongsTo(MasterNotification::class, 'master_notification_id', 'id');
    }

    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id', 'id');
    }
    //endregion

    //region GetAttributes
    //endregion
}
