<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Identity\Enums\Role;
use App\Infrastructure\Persistence\Eloquent\Models\AnneeUniversitaire;
use App\Infrastructure\Persistence\Eloquent\Models\Etudiant;
use App\Infrastructure\Persistence\Eloquent\Models\Filiere;
use App\Infrastructure\Persistence\Eloquent\Models\Groupe;
use App\Infrastructure\Persistence\Eloquent\Models\InscriptionCours;
use App\Infrastructure\Persistence\Eloquent\Models\Utilisateur;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class ScolariteSeeder extends Seeder
{
    private const DEMO_ETUDIANTS = [
        'etudiant@univ.test' => 'L1-ISLI',
        'etudiant1@univ.test' => 'L1-ISRI',
        'etudiant2@univ.test' => 'L1-ISII',
        'etudiant3@univ.test' => 'L1-MDI',
        'etudiant4@univ.test' => 'M1-GID',
        'etudiant5@univ.test' => 'M1-OCC',
        'etudiant6@univ.test' => 'M1-ASI',
        'etudiant7@univ.test' => 'M1-IA',
        'etudiant8@univ.test' => 'L1-ISLI',
        'etudiant9@univ.test' => 'L1-ISRI',
        'etudiant10@univ.test' => 'L1-ISII',
    ];

    public function run(): void
    {
        DB::transaction(function (): void {
            $annee = AnneeUniversitaire::query()->updateOrCreate(
                ['libelle' => '2026-2027'],
                [
                    'date_debut' => '2026-09-01',
                    'date_fin' => '2027-07-31',
                    'courante' => true,
                ]
            );
            AnneeUniversitaire::query()->whereKeyNot($annee->id)->update(['courante' => false]);

            $codesFilieres = array_values(array_unique(self::DEMO_ETUDIANTS));
            $filieres = Filiere::query()
                ->whereIn('code', $codesFilieres)
                ->where('actif', true)
                ->get()
                ->keyBy('code');

            foreach ($codesFilieres as $code) {
                if (! $filieres->has($code)) {
                    throw new RuntimeException("Le parcours actif {$code} doit être créé avant les données de scolarité.");
                }
            }

            $etudiants = [];
            for ($i = 1; $i <= 10; $i++) {
                $email = "etudiant{$i}@univ.test";
                $utilisateur = Utilisateur::query()->updateOrCreate(
                    ['email' => $email],
                    [
                        'mot_de_passe_hash' => Hash::make('Password123!'),
                        'role' => Role::ETUDIANT->value,
                        'actif' => true,
                    ]
                );

                $etudiants[$email] = Etudiant::query()->updateOrCreate(
                    ['utilisateur_id' => $utilisateur->id],
                    [
                        'matricule' => 'ET'.str_pad((string) ($i + 1000), 4, '0', STR_PAD_LEFT),
                        'nom' => "EtudiantNom{$i}",
                        'prenom' => "EtudiantPrenom{$i}",
                        'date_naissance' => '2003-01-01',
                        'filiere_id' => $filieres->get(self::DEMO_ETUDIANTS[$email])->id,
                    ]
                );
            }

            $utilisateurPrincipal = Utilisateur::query()
                ->where('email', 'etudiant@univ.test')
                ->firstOrFail();
            $etudiants['etudiant@univ.test'] = $utilisateurPrincipal->etudiant()->updateOrCreate(
                [],
                [
                    'matricule' => 'ET0001',
                    'nom' => 'Durand',
                    'prenom' => 'Alice',
                    'date_naissance' => '2002-05-14',
                    'filiere_id' => $filieres->get(self::DEMO_ETUDIANTS['etudiant@univ.test'])->id,
                ]
            );

            $coursParFiliere = DB::table('cours as c')
                ->join('semestre as s', 's.id', '=', 'c.semestre_id')
                ->join('filiere as f', 'f.id', '=', 's.filiere_id')
                ->whereIn('f.code', $codesFilieres)
                ->where('s.numero', 1)
                ->select('f.code as filiere_code', 'c.id', 'c.code')
                ->orderBy('c.code')
                ->get()
                ->groupBy('filiere_code');

            foreach ($codesFilieres as $code) {
                if ($coursParFiliere->get($code, collect())->count() !== 3) {
                    throw new RuntimeException("Le parcours {$code} doit avoir trois cours de démonstration au semestre 1.");
                }
            }

            $groupes = [];
            foreach ($filieres as $code => $filiere) {
                $groupes[$code] = Groupe::query()->updateOrCreate(
                    [
                        'nom' => 'G1',
                        'filiere_id' => $filiere->id,
                        'annee_id' => $annee->id,
                    ],
                    ['effectif_max' => 40]
                );
            }

            $etudiantIds = array_map(static fn (Etudiant $etudiant): int => $etudiant->id, $etudiants);
            $groupesAnnee = Groupe::query()
                ->where('annee_id', $annee->id)
                ->whereIn('filiere_id', $filieres->pluck('id'))
                ->pluck('id');

            DB::table('etudiant_groupe')
                ->whereIn('etudiant_id', $etudiantIds)
                ->whereIn('groupe_id', $groupesAnnee)
                ->delete();

            $affectations = [];
            foreach (self::DEMO_ETUDIANTS as $email => $filiereCode) {
                $affectations[] = [
                    'etudiant_id' => $etudiants[$email]->id,
                    'groupe_id' => $groupes[$filiereCode]->id,
                ];
            }
            DB::table('etudiant_groupe')->insert($affectations);

            $tousLesCoursDemo = $coursParFiliere->flatten(1)->pluck('id')->all();
            foreach (self::DEMO_ETUDIANTS as $email => $filiereCode) {
                $coursCibles = $coursParFiliere->get($filiereCode)->pluck('id')->all();
                $coursObsoletes = array_values(array_diff($tousLesCoursDemo, $coursCibles));

                $aDesNotes = DB::table('inscription_cours as i')
                    ->join('evaluation as ev', function ($join): void {
                        $join->on('ev.cours_id', '=', 'i.cours_id')
                            ->on('ev.annee_id', '=', 'i.annee_id');
                    })
                    ->join('note as n', function ($join): void {
                        $join->on('n.evaluation_id', '=', 'ev.id')
                            ->on('n.etudiant_id', '=', 'i.etudiant_id');
                    })
                    ->where('i.etudiant_id', $etudiants[$email]->id)
                    ->where('i.annee_id', $annee->id)
                    ->whereIn('i.cours_id', $coursObsoletes)
                    ->exists();

                if ($aDesNotes) {
                    throw new RuntimeException("Les inscriptions de démonstration de {$email} ne peuvent pas être déplacées car elles possèdent déjà des notes.");
                }

                InscriptionCours::query()
                    ->where('etudiant_id', $etudiants[$email]->id)
                    ->where('annee_id', $annee->id)
                    ->whereIn('cours_id', $tousLesCoursDemo)
                    ->whereNotIn('cours_id', $coursCibles)
                    ->delete();

                foreach ($coursCibles as $coursId) {
                    InscriptionCours::query()->updateOrCreate(
                        [
                            'etudiant_id' => $etudiants[$email]->id,
                            'cours_id' => $coursId,
                            'annee_id' => $annee->id,
                        ],
                        ['date_inscription' => now()]
                    );
                }
            }
        });
    }
}
