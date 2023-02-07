<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(RoleSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(SectorSeeder::class);
        $this->call(ReportTypeSeeder::class);
        $this->call(AreaSeeder::class);
        $this->call(ReportStatusSeeder::class);
        $this->call(SystemConfigSeeder::class);
        $this->call(PermissionSeeder::class);
        $this->call(FaqSeeder::class);
        $this->call(FaqCategorySeeder::class);
        $this->call(PageSeeder::class);

//        $this->call(DataMigrationSeeder::class);
//        $this->call(ReportSeeder::class);
    }
}
