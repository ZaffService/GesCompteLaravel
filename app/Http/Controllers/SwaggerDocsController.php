<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;

class SwaggerDocsController extends Controller
{
    public function getJson()
    {
        $path = storage_path('api-docs/api-docs.json');

        if (!File::exists($path)) {
            abort(404, 'API documentation not found');
        }

        $content = File::get($path);

        return response($content, 200)
            ->header('Content-Type', 'application/json')
            ->header('Access-Control-Allow-Origin', '*');
    }
}
