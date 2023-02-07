<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModerationHistory extends Model
{
    use HasFactory;
    use SoftDeletes;

    //region Attributes

    protected $guarded = [];

    //endregion

    //region Methods
    //endregion

    //region Scopes
    //endregion

    //region Relations
    public function fromStatus()
    {
        return $this->belongsTo(ReportStatus::class, 'from_status_id', 'id');
    }

    public function toStatus()
    {
        return $this->belongsTo(ReportStatus::class, 'to_status_id', 'id');
    }

    public function moderator()
    {
        return $this->belongsTo(User::class, 'moderated_by_user_id', 'id');
    }

    public function report()
    {
        return $this->belongsTo(Report::class, 'report_id', 'id');
    }

    public function comment()
    {
        return $this->belongsTo(Comment::class, 'comment_id', 'id');
    }

    public function masterNotification()
    {
        return $this->belongsTo(MasterNotification::class, 'master_notification_id', 'id');
    }
    //endregion

    //region GetAttributes
    //endregion
}
