<?php

namespace App\Http\Controllers\Api\Admin\Reference;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Reference\StoreGameFormatRequest;
use App\Http\Requests\Admin\Reference\UpdateGameFormatRequest;
use App\Models\GameFormat;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GameFormatController extends Controller
{
    public function index(Request $request)
    {
        $query = GameFormat::query();

        if ($request->boolean('include_archived')) {
            $query->withTrashed();
        }

        return response()->json(
            $query->orderBy('name')->get()->map(fn ($f) => [
                'id'          => $f->id,
                'name'        => $f->name,
                'slug'        => $f->slug,
                'archived'    => $f->trashed(),
                'usage_count' => $f->templates()->count(),
            ])
        );
    }

    public function store(StoreGameFormatRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = $this->uniqueSlug($data['name']);

        $format = GameFormat::create($data);

        return response()->json(['id' => $format->id, 'name' => $format->name, 'slug' => $format->slug], 201);
    }

    public function update(UpdateGameFormatRequest $request, GameFormat $format)
    {
        $format->update($request->validated());

        return response()->json(['id' => $format->id, 'name' => $format->name, 'slug' => $format->slug]);
    }

    public function destroy(GameFormat $format)
    {
        $format->delete();

        return response()->json(null, 204);
    }

    public function restore(int $id)
    {
        $format = GameFormat::onlyTrashed()->findOrFail($id);
        $format->restore();

        return response()->json(['id' => $format->id, 'name' => $format->name, 'slug' => $format->slug]);
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;

        while (GameFormat::withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}