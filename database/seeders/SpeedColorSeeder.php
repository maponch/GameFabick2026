<?php

namespace Database\Seeders;

use App\Models\GameFormat;
use App\Models\GameObject;
use App\Models\GameTemplate;
use App\Models\Type;
use Illuminate\Database\Seeder;

class SpeedColorSeeder extends Seeder
{
    public function run(): void
    {
        $type = Type::firstOrCreate(['name' => 'Familiale']);

        $template = GameTemplate::create([
            'name'         => 'Speed Color',
            'slug'         => 'speed-color',
            'description'  => 'Un jeu d\'observation et de rapidité. Soyez le premier à repérer la couleur ou le symbole correspondant !',
            'rules'        => "## Préparation\n\nMélangez toutes les cartes et distribuez-les face cachée à parts égales entre les joueurs.\n\n## Déroulement\n\nÀ tour de rôle, chaque joueur retourne sa carte du dessus.\n\n**Dès que deux cartes visibles partagent la même couleur OU le même symbole**, le premier joueur à crier la couleur (ou le symbole) commune remporte les deux piles correspondantes.\n\n## Stratégie\n\nGardez l'œil ouvert sur toutes les piles, pas seulement les vôtres !\n\n## Victoire\n\nLe joueur qui possède toutes les cartes en fin de partie gagne. Si le temps manque, c'est le joueur avec le plus de cartes qui l'emporte.",
            'type_id'      => $type->id,
            'min_players'  => 2,
            'max_players'  => 6,
            'duration_min' => 5,
            'duration_max' => 15,
            'status'       => 'published',
            'card_layout'  => 'speed-color',
            'card_schema'  => [
                ['key' => 'symbole', 'label' => 'Symbole', 'type' => 'select', 'required' => true,
                 'options' => ['★', '●', '▲', '■']],
            ],
        ]);

        $template->formats()->sync(
            GameFormat::whereIn('slug', ['impression', 'cartes-classiques'])->pluck('id')->all()
        );

        $colors = [
            ['name' => 'Rouge', 'color' => '#E53935', 'deck' => ['A-hearts', 'A-diamonds']],
            ['name' => 'Bleu',  'color' => '#1E88E5', 'deck' => ['K-spades', 'K-clubs']],
            ['name' => 'Vert',  'color' => '#43A047', 'deck' => ['Q-spades', 'Q-clubs']],
            ['name' => 'Jaune', 'color' => '#FDD835', 'deck' => ['J-hearts', 'J-diamonds']],
        ];
        $symbols = ['★', '●', '▲', '■'];

        foreach ($colors as $color) {
            foreach ($symbols as $symbol) {
                $object = GameObject::create([
                    'name'                  => $color['name'],
                    'description'           => "Carte {$color['name']} avec symbole {$symbol}.",
                    'quantity'              => 2,
                    'default_color'         => $color['color'],
                    'existing_deck_mapping' => $color['deck'],
                    'custom_data'           => ['symbole' => $symbol],
                ]);
                $template->objects()->attach($object->id);
            }
        }
    }
}