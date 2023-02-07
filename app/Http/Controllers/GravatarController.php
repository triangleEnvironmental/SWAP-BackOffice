<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravolt\Avatar\Avatar;

class GravatarController extends Controller
{
    public function generate(Request $request)
    {
        $full_name = $request->full_name ?? '--';
        $original_full_name = $full_name;

        if ($full_name != '--') {
            $full_name = preg_replace("/[ \-_+.]+/i", " ", $full_name);
            if (str_contains(trim($full_name), ' ')) {
                $full_name = ' ' . trim($full_name);
                $full_name = trim(preg_replace("/( .)([^ ]+)/ui", '${1}', $full_name));
            } else {
                $full_name = preg_replace("/[^ក-អឥ-ឪa-z0-9]+/ui", '', $full_name);
            }
        }

        $avatar = new Avatar();

        $color_list = config('laravolt.avatar.backgrounds');
        $color_index = crc32($original_full_name) % count($color_list);

        $image = $avatar
            ->create($full_name)
            ->setBackground($color_list[$color_index])
            ->setDimension(512)
            ->setFontSize(200)
            ->setShape('square')
            ->setBorder(0, 'white')
            ->setFont(resource_path('font/nokora.ttf'))
            ->getImageObject();

        $tmp_directory = storage_path('app/public/profile_photos/');
        if (!file_exists($tmp_directory)) {
            mkdir($tmp_directory, 0755, true);
        }
        $tmp_location = $tmp_directory . 'default.png';
        $image->save($tmp_location);

        return response(
            file_get_contents($tmp_location),
            200,
            array(
                'Content-Type' => 'image/jpeg',
                'Content-Disposition' => 'inline; filename="' . $original_full_name . '.png"' // 'attachment; filename="' . $id . '.jpg"'
            )
        );

    }
}
