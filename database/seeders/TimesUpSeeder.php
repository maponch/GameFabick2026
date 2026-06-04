<?php

namespace Database\Seeders;

use App\Models\GameObject;
use App\Models\GameTemplate;
use App\Models\Type;
use Illuminate\Database\Seeder;

class TimesUpSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Type "Ambiance" si pas déjà créé
        $type = Type::firstOrCreate(['name' => 'Ambiance']);

        // 2. Template Time's Up
        $template = GameTemplate::create([
            'name'                   => "Time's Up",
            'slug'                   => 'times-up',
            'description'            => 'Un jeu d\'ambiance en équipes où il faut faire deviner des personnalités célèbres en trois manches : description libre, un seul mot, puis mime.',
            'rules'                  => "## Préparation\n\nFormez deux équipes. Mélangez les cartes personnalités et placez-les en pile au centre.\n\n## Déroulement\n\nLe jeu se joue en **trois manches** avec les mêmes cartes à chaque fois.\n\n**Manche 1 — Description libre** : le joueur actif fait deviner un maximum de personnalités à son équipe en 30 secondes, en utilisant autant de mots qu'il le souhaite (sauf le nom de la personnalité).\n\n**Manche 2 — Un seul mot** : mêmes cartes, mais le joueur ne peut prononcer qu'**un seul mot** par carte.\n\n**Manche 3 — Mime** : mêmes cartes, uniquement en mimant, sans parler.\n\n## Victoire\n\nL'équipe avec le plus de cartes devinées au total des trois manches remporte la partie.",
            'type_id'                => $type->id,
            'min_players'            => 4,
            'max_players'            => 12,
            'duration_min'           => 30,
            'duration_max'           => 45,
            'status'                 => 'published',
        ]);

        // 3. Personnalités (40 cartes identiques visuellement)
        $personalities = [
            'Albert Einstein',
            'Napoléon Bonaparte',
            'Marilyn Monroe',
            'Michael Jackson',
            'Elvis Presley',
            'Charlie Chaplin',
            'Leonardo da Vinci',
            'Cléopâtre',
            'Walt Disney',
            'Bruce Lee',
            'Harry Potter',
            'Sherlock Holmes',
            'Mario',
            'Dark Vador',
            'Spider-Man',
            'Batman',
            'Pikachu',
            'Shrek',
            'Mickey Mouse',
            'Tintin',
            'Cristiano Ronaldo',
            'Lionel Messi',
            'Mike Tyson',
            'Usain Bolt',
            'Taylor Swift',
            'Beyoncé',
            'Johnny Hallyday',
            'Jean-Claude Van Damme',
            'MrBeast',
            'Squeezie',
            'Tour Eiffel',
            'Statue de la Liberté',
            'Big Ben',
            'Colisée',
            'Pyramides de Gizeh',
            'Simba',
            "Buzz l'Éclair",
            'Gollum',
            'Homer Simpson',
            "Bob l'Éponge",
        ];

        foreach ($personalities as $name) {
            $object = GameObject::create([
                'name'                  => $name,
                'description'           => null,
                'quantity'              => 1,
                'default_color'         => '#FF6F00',
                'existing_deck_mapping' => null,
            ]);
            $template->objects()->attach($object->id);
        }
    }
}