<?php

namespace Database\Seeders;

use App\Models\GameFormat;
use App\Models\GameObject;
use App\Models\GameTemplate;
use App\Models\Type;
use Illuminate\Database\Seeder;

class QuizExpressSeeder extends Seeder
{
    public function run(): void
    {
        $type = Type::firstOrCreate(['name' => 'Reflexion']);

        $template = GameTemplate::create([
            'name'         => 'Quiz Express',
            'slug'         => 'quiz-express',
            'description'  => 'Un jeu de questions-réponses rapide pour tester ses connaissances en équipes ou en solo.',
            'rules'        => "## Préparation\n\nFormez 2 à 4 équipes. Mélangez les cartes question et placez-les face cachée au centre.\n\n## Déroulement\n\nÀ tour de rôle, chaque équipe pioche une carte. Un membre lit la question à voix haute. L'équipe a **15 secondes** pour répondre.\n\n- **Bonne réponse** : l'équipe garde la carte.\n- **Mauvaise réponse** : la carte est défaussée et la question revient à l'équipe suivante (avec 10 secondes).\n\n## Difficulté\n\nLes cartes sont marquées Facile, Moyen ou Difficile. Les questions difficiles valent **2 points** au lieu d'1.\n\n## Victoire\n\nLa première équipe à 10 points remporte la partie.",
            'type_id'      => $type->id,
            'min_players'  => 2,
            'max_players'  => 8,
            'duration_min' => 15,
            'duration_max' => 25,
            'status'       => 'published',
            'card_layout'  => 'quiz-card',
            'card_schema'  => [
                ['key' => 'question',   'label' => 'Question',   'type' => 'textarea', 'required' => true],
                ['key' => 'reponse',    'label' => 'Réponse',    'type' => 'textarea', 'required' => true],
                ['key' => 'difficulte', 'label' => 'Difficulté', 'type' => 'select',   'required' => true,
                 'options' => ['Facile', 'Moyen', 'Difficile']],
            ],
        ]);

        $template->formats()->sync(
            GameFormat::whereIn('slug', ['impression'])->pluck('id')->all()
        );

        $questions = [
            ['name' => 'Géographie',  'q' => 'Quelle est la capitale de l\'Australie ?',                                 'r' => 'Canberra',           'd' => 'Moyen'],
            ['name' => 'Histoire',    'q' => 'En quelle année a eu lieu la chute du mur de Berlin ?',                    'r' => '1989',               'd' => 'Facile'],
            ['name' => 'Sciences',    'q' => 'Quel est l\'élément chimique de symbole "Au" ?',                           'r' => 'L\'or',              'd' => 'Facile'],
            ['name' => 'Cinéma',      'q' => 'Qui a réalisé "Pulp Fiction" ?',                                           'r' => 'Quentin Tarantino',  'd' => 'Facile'],
            ['name' => 'Littérature', 'q' => 'Qui a écrit "Les Misérables" ?',                                           'r' => 'Victor Hugo',        'd' => 'Facile'],
            ['name' => 'Sport',       'q' => 'Combien de joueurs sur un terrain de football en simultané ?',             'r' => '22 (11 par équipe)', 'd' => 'Facile'],
            ['name' => 'Musique',     'q' => 'Quel groupe a sorti l\'album "The Dark Side of the Moon" ?',               'r' => 'Pink Floyd',         'd' => 'Moyen'],
            ['name' => 'Géographie',  'q' => 'Quel est le plus long fleuve du monde ?',                                  'r' => 'L\'Amazone',         'd' => 'Moyen'],
            ['name' => 'Sciences',    'q' => 'Quelle planète est surnommée la "planète rouge" ?',                        'r' => 'Mars',               'd' => 'Facile'],
            ['name' => 'Histoire',    'q' => 'Quel empereur romain a fait construire le Colisée ?',                      'r' => 'Vespasien (terminé par Titus)', 'd' => 'Difficile'],
            ['name' => 'Art',         'q' => 'Qui a peint "La Persistance de la mémoire" ?',                             'r' => 'Salvador Dalí',      'd' => 'Moyen'],
            ['name' => 'Géographie',  'q' => 'Combien de continents y a-t-il ?',                                         'r' => '7 (selon le modèle classique)', 'd' => 'Facile'],
            ['name' => 'Sciences',    'q' => 'Quelle est la vitesse approximative de la lumière dans le vide ?',         'r' => '299 792 km/s',       'd' => 'Difficile'],
            ['name' => 'Cinéma',      'q' => 'Quel acteur a joué Jack Sparrow ?',                                        'r' => 'Johnny Depp',        'd' => 'Facile'],
            ['name' => 'Histoire',    'q' => 'Qui était président des États-Unis à la fin de la Seconde Guerre mondiale ?', 'r' => 'Harry S. Truman',    'd' => 'Difficile'],
        ];

        foreach ($questions as $q) {
            $object = GameObject::create([
                'name'                  => $q['name'],
                'description'           => null,
                'quantity'              => 1,
                'default_color'         => '#3F51B5',
                'existing_deck_mapping' => null,
                'custom_data'           => [
                    'question'   => $q['q'],
                    'reponse'    => $q['r'],
                    'difficulte' => $q['d'],
                ],
            ]);
            $template->objects()->attach($object->id);
        }
    }
}