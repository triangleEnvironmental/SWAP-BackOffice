<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $keys = ['about', 'terms', 'policy'];

        foreach ($keys as $key) {
            $page = Page::query()
                ->firstWhere('key', $key);
            if ($page === null) {
                Page::query()
                    ->create([
                        'key' => $key,
                        'content_en' => Str::replace('http://localhost:8000', config('app.url'), file_get_contents(resource_path("markdown/rendered/$key.txt"))),
//                        'content_en' => Str::markdown(file_get_contents(resource_path("markdown/$key.md"))),
                    ]);
            }
        }
    }
}
