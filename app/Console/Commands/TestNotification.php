<?php

namespace App\Console\Commands;

use App\Notifications\WasteTrackerPushNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use Kreait\Firebase\Messaging\MessageTarget;

class TestNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test notification';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Notification::route('FCM', ['dm8ugUDmR6Sfj6EzwWYjEt:APA91bGCtrFDxDEFmx3LXXpULvprLdXYPtYvxFtZShMOyE7ZVxwAxoOwDZugN7O8QiHzfvJbEzFZiZE4Ta_sk1knILz8z70GkaWSxVnd9nvVJ9McKd_c59G75Bdb9fkzvpyMvdvkYYuV'])
            ->route('FCMTargetType', MessageTarget::TOKEN)
            ->notify(new WasteTrackerPushNotification(
                'Test title',
                'Test body only please.',
                [
                    'name' => 'Tester'
                ],
            ));
        return 0;
    }
}
