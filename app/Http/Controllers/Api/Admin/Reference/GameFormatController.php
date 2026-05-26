<?php

namespace App\Http\Controllers\Api\Admin\Reference;

use App\Http\Controllers\Controller;
use App\Models\GameFormat;

class GameFormatController extends Controller
{
    public function index()
    {
        return response()->json(
            GameFormat::orderBy('name')->get(['id', 'name', 'slug'])
        );
    }
}