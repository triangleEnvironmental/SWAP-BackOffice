<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\FirebaseAuthService;
use Exception;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Exception\FirebaseException;
use MStaack\LaravelPostgis\Geometries\Point;
use Throwable;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Admin
        User::query()
            ->create([
                'name' => 'Administrator',
                'email' => 'admin@admin.com',
                'password' => Hash::make('123123123'),
                'role_id' => 1,
            ]);
    }
}
