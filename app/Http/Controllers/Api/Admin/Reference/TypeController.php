<?php

namespace App\Http\Controllers\Api\Admin\Reference;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Reference\StoreTypeRequest;
use App\Http\Requests\Admin\Reference\UpdateTypeRequest;
use App\Models\Type;
use Illuminate\Http\Request;

class TypeController extends Controller
{
    public function index(Request $request)
    {
        $query = Type::query();

        if ($request->boolean('include_archived')) {
            $query->withTrashed();
        }

        return response()->json(
            $query->orderBy('name')->get()->map(fn ($t) => [
                'id'          => $t->id,
                'name'        => $t->name,
                'archived'    => $t->trashed(),
                'usage_count' => $t->templates()->count(),
            ])
        );
    }

    public function store(StoreTypeRequest $request)
    {
        $type = Type::create($request->validated());

        return response()->json(['id' => $type->id, 'name' => $type->name], 201);
    }

    public function update(UpdateTypeRequest $request, Type $type)
    {
        $type->update($request->validated());

        return response()->json(['id' => $type->id, 'name' => $type->name]);
    }

    public function destroy(Type $type)
    {
        $type->delete();

        return response()->json(null, 204);
    }

    public function restore(int $id)
    {
        $type = Type::onlyTrashed()->findOrFail($id);
        $type->restore();

        return response()->json(['id' => $type->id, 'name' => $type->name]);
    }
}