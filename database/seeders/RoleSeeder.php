<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Role:
     *  1 -> super admin
     *  2 -> admin -> under SP or municipality
     *  3 -> member -> under SP or municipality
     *  4 -> citizen
     *
     * @return void
     */

    public function run()
    {
        Role::query()
            ->updateOrCreate([
                'id' => 1,
            ], [
                'id' => 1,
                'name_en' => 'Super Admin',
                'name_km' => 'អ្នកគ្រប់គ្រងប្រព័ន្ធ',
                'is_deletable' => false,
            ]);

        Role::query()
            ->updateOrCreate([
                'id' => 2,
            ], [
                'id' => 2,
                'name_en' => 'Admin',
                'name_km' => 'អ្នកគ្រប់គ្រងស្ថាប័ន',
                'is_under_institution' => true,
                'is_deletable' => false,
            ]);

        Role::query()
            ->updateOrCreate([
                'id' => 3,
            ], [
                'id' => 3,
                'name_en' => 'Member',
                'name_km' => 'សមាជិក',
                'is_under_institution' => true,
                'is_deletable' => false,
            ]);

        Role::query()
            ->updateOrCreate([
                'id' => 4,
            ], [
                'id' => 4,
                'name_en' => 'Citizen',
                'name_km' => 'ពលរដ្ឋ',
                'is_deletable' => false,
            ]);
    }
}
