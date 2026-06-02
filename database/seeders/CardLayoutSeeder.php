<?php

namespace Database\Seeders;

use App\Models\CardLayout;
use Illuminate\Database\Seeder;

class CardLayoutSeeder extends Seeder
{
    public function run(): void
    {
        $layouts = [
            [
                'slug'        => 'title-text',
                'name'        => 'Titre et description',
                'description' => 'Carte simple avec un titre et une description. Mise en page minimale.',
                'schema'      => [],
            ],
        ];

        foreach ($layouts as $layout) {
            CardLayout::firstOrCreate(['slug' => $layout['slug']], $layout);
        }
    }
}