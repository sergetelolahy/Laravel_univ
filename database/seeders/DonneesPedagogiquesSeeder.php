<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Models\AnneeUniversitaire;
use App\Infrastructure\Persistence\Eloquent\Models\Etudiant;
use App\Infrastructure\Persistence\Eloquent\Models\Filiere;
use App\Infrastructure\Persistence\Eloquent\Models\Groupe;
use App\Infrastructure\Persistence\Eloquent\Models\Salle;
use App\Infrastructure\Persistence\Eloquent\Models\Utilisateur;
use App\Models\Evaluation;
use App\Models\Note;
use App\Models\Seance;
use DateTimeImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DonneesPedagogiquesSeeder extends Seeder
{
    private const FILIERES_DEMO = [
        'L1-ISLI',
        'L1-ISRI',
        'L1-ISII',
        'L1-MDI',
        'M1-GID',
        'M1-OCC',
        'M1-ASI',
        'M1-IA',
    ];

    private const CRENEAUX = [
        '08:00:00',
        '09:00:00',
        '10:00:00',
        '14:00:00',
        '15:00:00',
        '16:00:00',
    ];

    public function run(): void
    {
        DB::transaction(function (): void {
            $annee = AnneeUniversitaire::query()->where('libelle', '2026-2027')->firstOrFail();
            $filieres = Filiere::query()
                ->whereIn('code', self::FILIERES_DEMO)
                ->get()
                ->keyBy('code');

            $enseignant = Utilisateur::query()
                ->where('email', 'enseignant@univ.test')
                ->firstOrFail()
                ->enseignant()
                ->firstOrFail();
            $salle = Salle::query()->where('nom', 'A101')->firstOrFail();

            $groupes = Groupe::query()
                ->where('nom', 'G1')
                ->where('annee_id', $annee->id)
                ->whereIn('filiere_id', $filieres->pluck('id'))
                ->get()
                ->keyBy('filiere_id');

            $cours = DB::table('cours as c')
                ->join('semestre as s', 's.id', '=', 'c.semestre_id')
                ->join('filiere as f', 'f.id', '=', 's.filiere_id')
                ->whereIn('f.code', self::FILIERES_DEMO)
                ->where('s.numero', 1)
                ->select('c.id', 'c.code', 'f.code as filiere_code')
                ->orderBy('f.code')
                ->orderBy('c.code')
                ->get();

            if ($cours->count() !== count(self::FILIERES_DEMO) * 3) {
                throw new RuntimeException('Chaque parcours de démonstration doit avoir trois cours au semestre 1.');
            }

            $etudiantsParFiliere = [];
            foreach ($filieres as $filiereCode => $filiere) {
                $groupe = $groupes->get($filiere->id);
                if ($groupe === null) {
                    throw new RuntimeException("Le groupe G1 de {$filiereCode} doit être créé avant les données pédagogiques.");
                }

                $etudiantsParFiliere[$filiereCode] = Etudiant::query()
                    ->join('etudiant_groupe as eg', 'eg.etudiant_id', '=', 'etudiant.id')
                    ->where('eg.groupe_id', $groupe->id)
                    ->select('etudiant.*')
                    ->get();
            }

            $seancesParCode = [];
            // Ces créneaux sont fictifs ; ils ne proviennent pas des emplois du temps officiels.
            $dateDebutDemo = new DateTimeImmutable('2026-10-19');

            foreach ($cours as $index => $coursRow) {
                $filiere = $filieres->get($coursRow->filiere_code);
                $groupe = $groupes->get($filiere->id);
                $etudiants = $etudiantsParFiliere[$coursRow->filiere_code];

                $evaluation = Evaluation::query()->updateOrCreate(
                    [
                        'cours_id' => $coursRow->id,
                        'annee_id' => $annee->id,
                        'type' => 'DEMO_CC',
                    ],
                    [
                        'ponderation' => 20,
                        'date_eval' => '2026-10-21',
                    ]
                );

                foreach ($etudiants as $etudiant) {
                    Note::query()->updateOrCreate(
                        [
                            'evaluation_id' => $evaluation->id,
                            'etudiant_id' => $etudiant->id,
                        ],
                        [
                            'valeur' => number_format(10 + (($etudiant->id + $coursRow->id) % 8), 2, '.', ''),
                            'saisie_par' => $enseignant->id,
                        ]
                    );
                }

                $jour = $dateDebutDemo->modify('+'.intdiv($index, count(self::CRENEAUX)).' days');
                $debut = $jour->format('Y-m-d').' '.self::CRENEAUX[$index % count(self::CRENEAUX)];
                $fin = (new DateTimeImmutable($debut))->modify('+1 hour')->format('Y-m-d H:i:s');

                $seancesParCode[$coursRow->code] = Seance::query()->updateOrCreate(
                    [
                        'cours_id' => $coursRow->id,
                        'groupe_id' => $groupe->id,
                        'enseignant_id' => $enseignant->id,
                        'salle_id' => $salle->id,
                        'debut' => $debut,
                    ],
                    ['fin' => $fin]
                );
            }

            $etudiantDemo = Etudiant::query()
                ->where('utilisateur_id', Utilisateur::query()->where('email', 'etudiant@univ.test')->value('id'))
                ->firstOrFail();
            $seanceDemo = $seancesParCode['IG101'] ?? null;
            if ($seanceDemo === null) {
                throw new RuntimeException('La séance de démonstration IG101 est manquante.');
            }

            DB::table('absence')->updateOrInsert(
                [
                    'seance_id' => $seanceDemo->id,
                    'etudiant_id' => $etudiantDemo->id,
                ],
                ['justifiee' => true]
            );
        });
    }
}
