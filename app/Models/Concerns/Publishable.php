<?php

namespace App\Models\Concerns;

trait Publishable
{
    public function publishabilityReport(): array
    {
        $missing = [];

        if (empty(trim((string) $this->description)) || mb_strlen($this->description) < 10) {
            $missing[] = 'description trop courte (minimum 10 caractères)';
        }

        if (empty(trim((string) $this->rules)) || mb_strlen($this->rules) < 50) {
            $missing[] = 'règles trop courtes (minimum 50 caractères)';
        }

        $this->loadMissing(['formats', 'objects']);

        $formats = $this->formats;
        $formatSlugs = $formats->pluck('slug')->all();

        if ($formats->isEmpty()) {
            $missing[] = 'au moins un format de jeu';
        }

        if (in_array('impression', $formatSlugs, true)) {
            if ($this->objects->count() < 2) {
                $missing[] = 'au moins 2 cartes (format impression)';
            }
        }

        if (in_array('cartes-classiques', $formatSlugs, true)) {
            $orphans = [];
            foreach ($this->objects as $object) {
                $mappingCount = is_array($object->existing_deck_mapping)
                    ? count($object->existing_deck_mapping)
                    : 0;
                if ($mappingCount < $object->quantity) {
                    $orphans[] = "\"{$object->name}\" ({$mappingCount}/{$object->quantity})";
                }
            }
            if (!empty($orphans)) {
                $missing[] = 'mappings pense-bête insuffisants : ' . implode(', ', $orphans);
            }
        }
        if (!empty($this->card_schema)) {
            $requiredFields = collect($this->card_schema)->where('required', true);
            if ($requiredFields->isNotEmpty()) {
                $incomplete = [];
                foreach ($this->objects as $object) {
                    $missingFields = [];
                    foreach ($requiredFields as $field) {
                        $value = $object->custom_data[$field['key']] ?? null;
                        if ($value === null || $value === '') {
                            $missingFields[] = $field['label'];
                        }
                    }
                    if (!empty($missingFields)) {
                        $incomplete[] = "\"{$object->name}\" (manque : " . implode(', ', $missingFields) . ')';
                    }
                }
                if (!empty($incomplete)) {
                    $missing[] = 'champs personnalisés requis non remplis : ' . implode(' ; ', $incomplete);
                }
            }
        }
        return [
            'ready'   => empty($missing),
            'missing' => $missing,
        ];
    }
}