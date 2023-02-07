<?php

namespace App\Console\Commands;

use App\Models\FcmTokens;
use Illuminate\Console\Command;

class CleanFcmToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clean:fcm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'FCM Token can be duplicate when another account did not log out';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $fcmTokens = FcmTokens::where('app', 'citizen')->orderByDesc('created_at')->get();
        foreach ($fcmTokens as $fcmToken) {
            if ($fcmToken instanceof FcmTokens) {
                if (FcmTokens::query()->where('id', $fcmToken->id)->exists()) {
                    FcmTokens::where(function($query) use ($fcmToken) {
                        $query->where([
                            'app' => 'citizen',
                            'device_id' => $fcmToken->device_id,
                        ])->orWhere('token', $fcmToken->token);
                    })->where('id', '!=', $fcmToken->id)->delete();
                }
            }
        }

        $fcmTokens = FcmTokens::where('app', 'moderator')->orderByDesc('created_at')->get();
        foreach ($fcmTokens as $fcmToken) {
            if ($fcmToken instanceof FcmTokens) {
                if (FcmTokens::query()->where('id', $fcmToken->id)->exists()) {
                    FcmTokens::where(function($query) use ($fcmToken) {
                        $query->where([
                            'app' => 'moderator',
                            'device_id' => $fcmToken->device_id,
                        ])->orWhere('token', $fcmToken->token);
                    })->where('id', '!=', $fcmToken->id)->delete();
                }
            }
        }

        return 0;
    }
}
