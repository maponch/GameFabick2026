<?php

namespace App\Http\Controllers\Api\Admin\Reference;

use App\Http\Controllers\Controller;
use App\Models\CardLayout;

class CardLayoutController extends Controller
{
    public function index()
    {
        return response()->json(
            CardLayout::orderBy('name')->get(['id', 'slug', 'name', 'description', 'schema'])
        );
    }
}