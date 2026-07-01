<?php

namespace Database\Seeders;

use App\Models\GameFormat;
use Illuminate\Database\Seeder;

class GameFormatSeeder extends Seeder
{
    public function run(): void
    {
        $formats = [
            ['name' => 'Cartes à imprimer',      'slug' => 'impression'],
            ['name' => 'Jeu de cartes classique', 'slug' => 'cartes-classiques'],
            ['name' => 'Dés',                     'slug' => 'des'],
            ['name' => 'Sablier',                 'slug' => 'sablier'],
            ['name' => 'Corde',                   'slug' => 'corde'],
        ];

        foreach ($formats as $format) {
            GameFormat::firstOrCreate(['slug' => $format['slug']], $format);
        }
    }
}