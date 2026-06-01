<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $suits = ['spades', 'hearts', 'diamonds', 'clubs'];

    public function up(): void
    {
        $objects = DB::table('objects')->whereNotNull('existing_deck_mapping')->get();

        foreach ($objects as $obj) {
            $old = json_decode($obj->existing_deck_mapping, true);
            if (!is_array($old)) {
                continue;
            }

            $atomic = [];
            foreach ($old as $rank) {
                // Si la valeur ressemble déjà à un id atomique (contient un tiret), on la garde telle quelle
                if (str_contains((string) $rank, '-')) {
                    $atomic[] = $rank;
                    continue;
                }
                foreach ($this->suits as $suit) {
                    $atomic[] = "{$rank}-{$suit}";
                }
            }

            DB::table('objects')
                ->where('id', $obj->id)
                ->update(['existing_deck_mapping' => json_encode(array_values(array_unique($atomic)))]);
        }
    }

    public function down(): void
    {
        // Reconversion atomique → valeurs (regroupe par rank)
        $objects = DB::table('objects')->whereNotNull('existing_deck_mapping')->get();

        foreach ($objects as $obj) {
            $atomic = json_decode($obj->existing_deck_mapping, true);
            if (!is_array($atomic)) {
                continue;
            }

            $ranks = [];
            foreach ($atomic as $card) {
                $rank = explode('-', (string) $card)[0];
                $ranks[$rank] = true;
            }

            DB::table('objects')
                ->where('id', $obj->id)
                ->update(['existing_deck_mapping' => json_encode(array_keys($ranks))]);
        }
    }
};