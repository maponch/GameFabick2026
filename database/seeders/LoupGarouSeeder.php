<?php

namespace Database\Seeders;

use App\Models\GameObject;
use App\Models\GameTemplate;
use App\Models\Type;
use Illuminate\Database\Seeder;

class LoupGarouSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Type "Ambiance" si pas déjà créé
        $type = Type::firstOrCreate(['name' => 'Ambiance']);

        // 2. Template Loup-Garou
        $template = GameTemplate::create([
            'name'                   => 'Loup-Garou de Thiercelieux',
            'slug'                   => 'loup-garou',
            'description'            => 'Un village est attaqué chaque nuit par des loups-garous cachés parmi les habitants. Les villageois doivent les démasquer avant qu\'il ne soit trop tard.',
            'rules'                  => "## Déroulement\n\nLe jeu alterne phases nuit et jour.\n\n**La nuit** : le maître du jeu (narrateur) appelle les rôles spéciaux qui agissent dans l'ombre.\n\n**Le jour** : tous les joueurs débattent et votent pour éliminer un suspect.\n\n## Victoire\n\n- **Villageois** : éliminer tous les loups-garous\n- **Loups-garous** : être en nombre égal ou supérieur aux villageois",
            'type_id'                => $type->id,
            'min_players'            => 8,
            'max_players'            => 18,
            'duration_min'           => 30,
            'duration_max'           => 60,
            'supports_existing_deck' => true,
            'is_published'           => true,
        ]);

        // 3. Objets (rôles)
        $roles = [
            [
                'name'                  => 'Villageois',
                'description'           => 'Simple villageois. Vote pour éliminer les loups-garous le jour.',
                'quantity'              => 6,
                'default_color'         => '#8BC34A',
                'existing_deck_mapping' => ['2', '3', '4', '5', '6', '7'],
            ],
            [
                'name'                  => 'Loup-Garou',
                'description'           => 'La nuit, élimine un villageois en commun accord avec les autres loups.',
                'quantity'              => 2,
                'default_color'         => '#B71C1C',
                'existing_deck_mapping' => ['K'],
            ],
            [
                'name'                  => 'Voyante',
                'description'           => 'Chaque nuit, peut voir le rôle d\'un autre joueur.',
                'quantity'              => 1,
                'default_color'         => '#9C27B0',
                'existing_deck_mapping' => ['Q'],
            ],
            [
                'name'                  => 'Sorcière',
                'description'           => 'Possède une potion de vie et une potion de mort, utilisables une seule fois chacune.',
                'quantity'              => 1,
                'default_color'         => '#673AB7',
                'existing_deck_mapping' => ['J'],
            ],
            [
                'name'                  => 'Chasseur',
                'description'           => 'S\'il est éliminé, peut emporter un autre joueur avec lui dans la mort.',
                'quantity'              => 1,
                'default_color'         => '#FF9800',
                'existing_deck_mapping' => ['10'],
            ],
            [
                'name'                  => 'Cupidon',
                'description'           => 'La première nuit, désigne deux amoureux. Si l\'un meurt, l\'autre meurt aussi.',
                'quantity'              => 1,
                'default_color'         => '#E91E63',
                'existing_deck_mapping' => ['9'],
            ],
        ];

        foreach ($roles as $role) {
            $object = GameObject::create($role);
            $template->objects()->attach($object->id);
        }
    }
}