<?php

declare(strict_types=1);

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
use Illuminate\Support\Facades\DB;

class ReferentielSeeder extends Seeder
{
    private const FILIERES = [
        ['code' => 'L1-ISLI', 'nom' => 'L1 Informatique - ISLI'],
        ['code' => 'L1-ISRI', 'nom' => 'L1 Informatique - ISRI'],
        ['code' => 'L1-ISII', 'nom' => 'L1 Informatique - ISII'],
        ['code' => 'L1-MDI', 'nom' => 'L1 Métier Digital'],
        ['code' => 'M1-GID', 'nom' => 'M1 GID'],
        ['code' => 'M1-OCC', 'nom' => 'M1 OCC'],
        ['code' => 'M1-ASI', 'nom' => 'M1 ASI'],
        ['code' => 'M1-IA', 'nom' => 'M1 IA'],
    ];

    private const COURS_DEMO = [
        'L1-ISLI' => [
            1 => ['Initiation à l’algorithmique', 'Fondamentaux des bases de données', 'Conception de logiciels'],
            2 => ['Développement web', 'Gestion de projet informatique', 'Modélisation des systèmes'],
        ],
        'L1-ISRI' => [
            1 => ['Initiation aux réseaux', 'Systèmes d’exploitation', 'Bases de données - démonstration'],
            2 => ['Architecture réseau', 'Routage et services réseau', 'Sécurité réseau - initiation'],
        ],
        'L1-ISII' => [
            1 => ['Initiation à la programmation', 'Modélisation des données', 'Systèmes d’information'],
            2 => ['Systèmes distribués', 'Tests logiciels', 'Gestion des données'],
        ],
        'L1-MDI' => [
            1 => ['Culture numérique', 'Création de contenus numériques', 'Communication digitale'],
            2 => ['Publication web', 'Expérience utilisateur', 'Projet numérique'],
        ],
        'M1-GID' => [
            1 => ['Méthodes de gestion de projet', 'Aide à la décision', 'Gouvernance des systèmes d’information'],
            2 => ['Management stratégique', 'Gouvernance des données', 'Conduite de l’innovation'],
        ],
        'M1-OCC' => [
            1 => ['Conception orientée objet', 'Modélisation avancée', 'Architecture logicielle'],
            2 => ['Qualité logicielle', 'Intégration continue', 'Projet de conception'],
        ],
        'M1-ASI' => [
            1 => ['Administration des systèmes', 'Fondamentaux de la sécurité', 'Réseaux informatiques'],
            2 => ['Gestion des risques', 'Réponse aux incidents', 'Audit de sécurité'],
        ],
        'M1-IA' => [
            1 => ['Programmation pour les données', 'Probabilités et statistiques', 'Préparation des données'],
            2 => ['Introduction à l’apprentissage automatique', 'Visualisation des données', 'IA responsable'],
        ],
    ];

    private const CODES_ISLI = [
        1 => ['IG101', 'IG102', 'IG103'],
        2 => ['IG201', 'IG202', 'IG203'],
    ];

    public function run(): void
    {
        DB::transaction(function (): void {
            $filieres = [];
            foreach (self::FILIERES as $filiereData) {
                $filieres[$filiereData['code']] = Filiere::query()->updateOrCreate(
                    ['code' => $filiereData['code']],
                    ['nom' => $filiereData['nom'], 'actif' => true]
                );
            }

            $annee = AnneeUniversitaire::query()->updateOrCreate(
                ['libelle' => '2026-2027'],
                [
                    'date_debut' => '2026-09-01',
                    'date_fin' => '2027-07-31',
                    'courante' => true,
                ]
            );
            AnneeUniversitaire::query()->whereKeyNot($annee->id)->update(['courante' => false]);

            $semestres = [];
            foreach ($filieres as $code => $filiere) {
                foreach ([1, 2] as $numero) {
                    $semestres[$code][$numero] = Semestre::query()->updateOrCreate(
                        ['filiere_id' => $filiere->id, 'numero' => $numero],
                        ['filiere_id' => $filiere->id, 'numero' => $numero]
                    );
                }
            }

            $cours = [];
            foreach (self::COURS_DEMO as $filiereCode => $coursParSemestre) {
                foreach ($coursParSemestre as $numero => $intitules) {
                    foreach ($intitules as $index => $intitule) {
                        $code = $filiereCode === 'L1-ISLI'
                            ? self::CODES_ISLI[$numero][$index]
                            : sprintf('DEMO-%s-S%d-%02d', substr($filiereCode, 3), $numero, $index + 1);

                        $cours[] = Cours::query()->updateOrCreate(
                            ['code' => $code],
                            [
                                'intitule' => '[DEMO] '.$intitule,
                                'semestre_id' => $semestres[$filiereCode][$numero]->id,
                                'coefficient' => [2.0, 3.0, 2.0][$index],
                                'credits' => [4, 5, 4][$index],
                            ]
                        );
                    }
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

            $utilisateur = Utilisateur::query()->updateOrCreate(
                ['email' => 'enseignant@univ.test'],
                [
                    'mot_de_passe_hash' => bcrypt('Password123!'),
                    'role' => Role::ENSEIGNANT->value,
                    'actif' => true,
                ]
            );
            $enseignant = Enseignant::query()->updateOrCreate(
                ['utilisateur_id' => $utilisateur->id],
                [
                    'nom' => 'Martin',
                    'prenom' => 'Claire',
                    'specialite' => 'Enseignement de démonstration',
                ]
            );

            foreach ($cours as $coursModel) {
                $coursModel->enseignants()->syncWithoutDetaching([
                    $enseignant->id => ['annee_id' => $annee->id],
                ]);
            }
        });
    }
}
