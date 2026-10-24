<?php

namespace Database\Seeders;

use App\Domain\Identity\Enums\Role;
use App\Infrastructure\Persistence\Eloquent\Models\AnneeUniversitaire;
use App\Infrastructure\Persistence\Eloquent\Models\Cours;
use App\Infrastructure\Persistence\Eloquent\Models\Enseignant;
use App\Infrastructure\Persistence\Eloquent\Models\Filiere;
use App\Infrastructure\Persistence\Eloquent\Models\Salle;
use App\Infrastructure\Persistence\Eloquent\Models\Semestre;
use App\Infrastructure\Persistence\Eloquent\Models\Utilisateur;
use Illuminate\Database\Seeder;

class ReferentielSeeder extends Seeder
{
    public function run(): void
    {
        $filiere = Filiere::query()->updateOrCreate(
            ['code' => 'M1-IG'],
            ['nom' => 'Master 1 - Ingénierie de Gestion']
        );

        $annee = AnneeUniversitaire::query()->updateOrCreate(
            ['libelle' => '2026-2027'],
            [
                'date_debut' => '2026-09-01',
                'date_fin' => '2027-07-31',
                'courante' => true,
            ]
        );

        $annee->refresh();
        AnneeUniversitaire::query()->whereKeyNot($annee->id)->update(['courante' => false]);
        AnneeUniversitaire::query()->whereKey($annee->id)->update(['courante' => true]);

        $semestres = [
            ['filiere_id' => $filiere->id, 'numero' => 1],
            ['filiere_id' => $filiere->id, 'numero' => 2],
        ];

        foreach ($semestres as $semestreData) {
            Semestre::query()->updateOrCreate(
                [
                    'filiere_id' => $semestreData['filiere_id'],
                    'numero' => $semestreData['numero'],
                ],
                [
                    'filiere_id' => $semestreData['filiere_id'],
                    'numero' => $semestreData['numero'],
                ]
            );
        }

        $semestre1 = Semestre::query()->where('filiere_id', $filiere->id)->where('numero', 1)->firstOrFail();
        $semestre2 = Semestre::query()->where('filiere_id', $filiere->id)->where('numero', 2)->firstOrFail();

        $coursParSemestre = [
            $semestre1->id => [
                ['code' => 'IG101', 'intitule' => 'Algorithmique avancée', 'coefficient' => 2.5, 'credits' => 4],
                ['code' => 'IG102', 'intitule' => 'Bases de données', 'coefficient' => 3.0, 'credits' => 5],
                ['code' => 'IG103', 'intitule' => 'Architecture logicielle', 'coefficient' => 2.0, 'credits' => 4],
            ],
            $semestre2->id => [
                ['code' => 'IG201', 'intitule' => 'Gestion de projet', 'coefficient' => 2.0, 'credits' => 3],
                ['code' => 'IG202', 'intitule' => 'Modélisation métier', 'coefficient' => 4.0, 'credits' => 6],
                ['code' => 'IG203', 'intitule' => 'Analyse décisionnelle', 'coefficient' => 1.5, 'credits' => 2],
            ],
        ];

        foreach ($coursParSemestre as $semestreId => $cours) {
            foreach ($cours as $coursData) {
                Cours::query()->updateOrCreate(
                    ['code' => $coursData['code']],
                    [
                        'intitule' => $coursData['intitule'],
                        'semestre_id' => $semestreId,
                        'coefficient' => $coursData['coefficient'],
                        'credits' => $coursData['credits'],
                    ]
                );
            }
        }

        foreach ([
            ['nom' => 'A101', 'capacite' => 40],
            ['nom' => 'A102', 'capacite' => 30],
            ['nom' => 'LAB1', 'capacite' => 25],
        ] as $salleData) {
            Salle::query()->updateOrCreate(
                ['nom' => $salleData['nom']],
                ['capacite' => $salleData['capacite']]
            );
        }

        $enseignant = Utilisateur::query()->firstOrCreate(
            ['email' => 'enseignant@univ.test'],
            [
                'mot_de_passe_hash' => bcrypt('Password123!'),
                'role' => Role::ENSEIGNANT->value,
                'actif' => true,
            ]
        );

        $enseignantModel = $enseignant->enseignant()->firstOrCreate([], [
            'nom' => 'Martin',
            'prenom' => 'Claire',
            'specialite' => 'Base de données',
            'utilisateur_id' => $enseignant->id,
        ]);

        $cours = Cours::query()->orderBy('id')->limit(6)->get();
        foreach ($cours->take(2) as $coursModel) {
            $coursModel->enseignants()->syncWithoutDetaching([
                $enseignantModel->id => ['annee_id' => $annee->id],
            ]);
        }
    }
}
