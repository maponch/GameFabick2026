<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGameTemplateRequest;
use App\Http\Requests\Admin\UpdateGameTemplateRequest;
use App\Http\Requests\Admin\ChangeGameTemplateStatusRequest;
use App\Models\GameTemplate;
use Illuminate\Support\Str;

class GameTemplateController extends Controller
{
    public function index()
    {
        $templates = GameTemplate::with('type')
            ->withCount('objects')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn ($t) => [
                'id'            => $t->id,
                'name'          => $t->name,
                'slug'          => $t->slug,
                'type'          => $t->type?->name,
                'status'        => $t->status,
                'objects_count' => $t->objects_count,
                'updated_at'    => $t->updated_at,
            ]);

        return response()->json($templates);
    }

    public function show(GameTemplate $template)
    {
        $template->load(['type', 'objects', 'formats']);

        return response()->json([
            'id'                     => $template->id,
            'name'                   => $template->name,
            'slug'                   => $template->slug,
            'description'            => $template->description,
            'rules'                  => $template->rules,
            'type_id'                => $template->type_id,
            'type'                   => $template->type?->name,
            'formats'                => $template->formats->map(fn ($f) => [
                'id'                    => $f->id,
                'name'                  => $f->name,
                'slug'                  => $f->slug,
            ]),
            'min_players'            => $template->min_players,
            'max_players'            => $template->max_players,
            'duration_min'           => $template->duration_min,
            'duration_max'           => $template->duration_max,
            'supports_existing_deck' => $template->supports_existing_deck,
            'status'                 => $template->status,
            'card_schema'            => $template->card_schema,
            'objects'                => $template->objects->map(fn ($o) => [
                'id'                    => $o->id,
                'name'                  => $o->name,
                'description'           => $o->description,
                'quantity'              => $o->quantity,
                'default_color'         => $o->default_color,
                'default_image_path'    => $o->default_image_path,
                'existing_deck_mapping' => $o->existing_deck_mapping,
                'custom_data'           => $o->custom_data,
            ]),
        ]);
    }

    public function store(StoreGameTemplateRequest $request)
    {
        $data = $request->validated();
        $formatIds = $data['format_ids'] ?? [];
        unset($data['format_ids']);

        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['created_by'] = $request->user()->id;
        $data['status'] = GameTemplate::STATUS_DRAFT;
        $data['card_schema'] = [];
        $data['supports_existing_deck'] = $data['supports_existing_deck'] ?? false;

        $template = GameTemplate::create($data);
        $template->formats()->attach($formatIds);

        return response()->json([
            'id'   => $template->id,
            'slug' => $template->slug,
        ], 201);
    }

    public function update(UpdateGameTemplateRequest $request, GameTemplate $template)
    {
        $data = $request->validated();

        $formatIds = $data['format_ids'] ?? null;
        unset($data['format_ids']);

        if (array_key_exists('slug', $data) && empty($data['slug'])) {
            $data['slug'] = $this->uniqueSlug($data['name'] ?? $template->name, $template->id);
        }

        $template->update($data);

        if ($formatIds !== null) {
            $template->formats()->sync($formatIds);
        }

        return response()->json(['id' => $template->id, 'slug' => $template->slug]);
    }

    public function changeStatus(ChangeGameTemplateStatusRequest $request, GameTemplate $template)
    {
        $template->update(['status' => $request->validated()['status']]);

        return response()->json(['id' => $template->id, 'status' => $template->status]);
    }

    public function destroy(GameTemplate $template)
    {
        if ($template->projects()->exists()) {
            return response()->json([
                'message' => 'Ce template est utilisé par des projets et ne peut pas être supprimé. Archivez-le à la place.',
            ], 409);
        }

        $template->objects()->detach();
        $template->delete();

        return response()->json(null, 204);
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;

        while (GameTemplate::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}