<?php

namespace App\Http\Requests\Admin\Concerns;

use App\Models\GameObject;
use App\Models\GameTemplate;
use Illuminate\Validation\Validator;

trait ValidatesDeckMapping
{
    protected function validateDeckMapping(Validator $validator): void
    {
        $mapping = $this->input('existing_deck_mapping');
        if (!is_array($mapping) || count($mapping) === 0) {
            return;
        }

        $template = $this->route('template');
        if (!$template instanceof GameTemplate) {
            $template = GameTemplate::find($template);
        }
        if (!$template) {
            return;
        }

        // ID de l'objet en cours d'édition (à exclure du check)
        $currentObject = $this->route('object');
        $currentObjectId = $currentObject instanceof GameObject
            ? $currentObject->id
            : $currentObject;

        $otherObjectIds = $template->objects()
            ->when($currentObjectId, fn ($q) => $q->where('objects.id', '!=', $currentObjectId))
            ->pluck('objects.id');

        $taken = GameObject::whereIn('id', $otherObjectIds)
            ->whereNotNull('existing_deck_mapping')
            ->get(['id', 'name', 'existing_deck_mapping']);

        $conflicts = [];
        foreach ($mapping as $cardId) {
            foreach ($taken as $other) {
                $otherMapping = $other->existing_deck_mapping ?? [];
                if (in_array($cardId, $otherMapping, true)) {
                    $conflicts[$cardId] = $other->name;
                    break;
                }
            }
        }

        if (!empty($conflicts)) {
            $details = collect($conflicts)
                ->map(fn ($name, $card) => $this->cardLabel($card) . " (pris par « $name »)")
                ->implode(', ');
            $validator->errors()->add(
                'existing_deck_mapping',
                "Certaines cartes sont déjà attribuées : $details"
            );
        }
    }

    private function cardLabel(string $id): string
    {
        $jokers = [
            'joker-red'   => 'Joker rouge',
            'joker-black' => 'Joker noir',
        ];
        if (isset($jokers[$id])) {
            return $jokers[$id];
        }

        $ranks = [
            '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6',
            '7' => '7', '8' => '8', '9' => '9', '10' => '10',
            'J' => 'Valet', 'Q' => 'Dame', 'K' => 'Roi', 'A' => 'As',
        ];
        $suits = [
            'spades'   => 'Pique',
            'hearts'   => 'Cœur',
            'diamonds' => 'Carreau',
            'clubs'    => 'Trèfle',
        ];

        [$rank, $suit] = array_pad(explode('-', $id), 2, null);
        if (!isset($ranks[$rank], $suits[$suit])) {
            return $id;
        }
        return "{$ranks[$rank]} de {$suits[$suit]}";
    }
}