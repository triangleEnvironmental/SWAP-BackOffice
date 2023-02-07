<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Page extends Model
{
    use HasFactory;

    //region Attributes

    protected $guarded = [];

    //endregion

    //region Methods
    private static function getMarkdownPage($key, $locale = 'en'): string
    {
        $page = Page::firstWhere('key', $key);
        if ($page instanceof Page) {
            if ($locale == 'km') {
                return $page->content_km ?? $page->content_en;
            }
            return $page->content_en;
        }

        $path = resource_path("markdown/$key.md");

        if (file_exists($path)) {
            return Str::markdown(file_get_contents($path));
        }

        return "Page not found";
    }

    public static function aboutContent($locale = 'en')
    {
        return self::getMarkdownPage('about');
    }

    public static function termsContent($locale = 'en')
    {
        return self::getMarkdownPage('terms');
    }

    public static function policyContent($locale = 'en')
    {
        return self::getMarkdownPage('policy');
    }

    public static function about()
    {
        return Page::firstWhere('key', 'about');
    }

    public static function terms()
    {
        return Page::firstWhere('key', 'terms');
    }

    public static function policy()
    {
        return Page::firstWhere('key', 'policy');
    }

    //endregion
}
