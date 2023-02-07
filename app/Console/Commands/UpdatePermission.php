<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class UpdatePermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:permission';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update permissions data from permission configuration to database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Artisan::call('db:seed', [
            '--class' => 'PermissionSeeder',
        ]);
        return 0;
    }
}
