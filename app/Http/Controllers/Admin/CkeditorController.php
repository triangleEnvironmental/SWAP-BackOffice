<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CkeditorController extends Controller
{
    public function upload_adapter(Request $request): JsonResponse
    {
        //  CHECK RESPONSE HERE : https://ckeditor.com/docs/ckeditor5/latest/features/images/image-upload/simple-upload-adapter.html#successful-upload
        if ($request->hasFile('upload')) {
            try {
                $file = $request->file('upload');

                $dir = 'ckeditor-images';
                $original_name = to_filename($file->getClientOriginalName());
                $file_name = $original_name;

                while (file_exists(public_path("storage/$dir/$file_name"))) {
                    $file_name = str::random(12) . '_' . $original_name;
                }

                $path = $file->storeAs(
                    $dir, $file_name, 'public'
                );

                $url = Storage::disk('public')->url($path);

                return new JsonResponse([
                    "url" => url($url),
                ], 200);
            } catch (Exception $e) {
                return new JsonResponse([
                    'error' => [
                        'message' => $e->getMessage(),
                    ],
                ]);
            }
        }

        return new JsonResponse([
            'error' => [
                'message' => 'Could not upload image. (Check file size)',
            ],
        ]);
    }
}
