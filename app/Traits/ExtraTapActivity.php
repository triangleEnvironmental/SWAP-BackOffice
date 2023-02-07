<?php

namespace App\Traits;

use Illuminate\Support\Facades\Request;
use Spatie\Activitylog\Models\Activity;

trait ExtraTapActivity
{
    public function tapActivity(Activity $activity) {
        $activity->ip_address = Request::ip();
        if (Request::hasHeader('X-CLIENT-INFO')) {
            $activity->client_info = Request::header('X-CLIENT-INFO');
        }
    }
}
