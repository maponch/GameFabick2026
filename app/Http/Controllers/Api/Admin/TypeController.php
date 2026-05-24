<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Type;

class TypeController extends Controller
{
    public function index()
    {
        return response()->json(
            Type::orderBy('name')->get(['id', 'name'])
        );
    }
}