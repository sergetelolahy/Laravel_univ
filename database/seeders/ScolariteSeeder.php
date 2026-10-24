<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Identity\Enums\Role;
use App\Infrastructure\Persistence\Eloquent\Models\AnneeUniversitaire;
use App\Infrastructure\Persistence\Eloquent\Models\Cours;
use App\Infrastructure\Persistence\Eloquent\Models\Etudiant;
use App\Infrastructure\Persistence\Eloquent\Models\Filiere;
use App\Infrastructure\Persistence\Eloquent\Models\Groupe;
use App\Infrastructure\Persistence\Eloquent\Models\InscriptionCours;
use App\Infrastructure\Persistence\Eloquent\Models\Semestre;
use App\Infrastructure\Persistence\Eloquent\Models\Utilisateur;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ScolariteSeeder extends Seeder
{
    public function run(): void
    {
        $filiere = Filiere::query()->firstOrCreate(
            ['code' => 'M1-IG'],
            ['nom' => 'Master 1 - Ingénierie de Gestion']
        );

        $annee = AnneeUniversitaire::query()->where('courante', true)->first();
        if ($annee === null) {
            $annee = AnneeUniversitaire::query()->updateOrCreate(
                ['libelle' => '2026-2027'],
                [
                    'date_debut' => '2026-09-01',
                    'date_fin' => '2027-07-31',
                    'courante' => true,
                ]
            );
        }

        $groupe1 = Groupe::query()->updateOrCreate(
            [
                'nom' => 'G1',
                'filiere_id' => $filiere->id,
                'annee_id' => $annee->id,
            ],
            [
                'effectif_max' => 6,
            ]
        );

        $groupe2 = Groupe::query()->updateOrCreate(
            [
                'nom' => 'G2',
                'filiere_id' => $filiere->id,
                'annee_id' => $annee->id,
            ],
            [
                'effectif_max' => 6,
            ]
        );

        $semestre1 = Semestre::query()->where('filiere_id', $filiere->id)->where('numero', 1)->first();
        if ($semestre1 === null) {
            $semestre1 = Semestre::query()->create([
                'filiere_id' => $filiere->id,
                'numero' => 1,
            ]);
        }

        $coursCodes = ['IG101', 'IG102', 'IG103'];
        $coursList = [];
        foreach ($coursCodes as $code) {
            $coursList[] = Cours::query()->firstOrCreate(
                ['code' => $code],
                [
                    'intitule' => 'Cours ' . $code,
                    'semestre_id' => $semestre1->id,
                    'coefficient' => 2.0,
                    'credits' => 4,
                ]
            );
        }

        $etudiants = [];
        for ($i = 1; $i <= 10; $i++) {
            $pad = str_pad((string) $i, 4, '0', STR_PAD_LEFT);
            $email = "etudiant{$i}@univ.test";
            $matricule = "ET{$pad}";

            $utilisateur = Utilisateur::query()->updateOrCreate(
                ['email' => $email],
                [
                    'mot_de_passe_hash' => Hash::make('Password123!'),
                    'role' => Role::ETUDIANT->value,
                    'actif' => true,
                ]
            );

            $etudiant = Etudiant::query()->updateOrCreate(
                ['utilisateur_id' => $utilisateur->id],
                [
                    'matricule' => $matricule,
                    'nom' => "EtudiantNom{$i}",
                    'prenom' => "EtudiantPrenom{$i}",
                    'date_naissance' => '2003-01-01',
                    'filiere_id' => $filiere->id,
                ]
            );

            $etudiants[] = $etudiant;

            // 5 premiers dans G1, 5 suivants dans G2
            $groupe = $i <= 5 ? $groupe1 : $groupe2;
            $autreGroupe = $i <= 5 ? $groupe2 : $groupe1;

            DB::table('etudiant_groupe')
                ->where('etudiant_id', $etudiant->id)
                ->where('groupe_id', $autreGroupe->id)
                ->delete();

            $dejaInscritGroupe = DB::table('etudiant_groupe')
                ->where('etudiant_id', $etudiant->id)
                ->where('groupe_id', $groupe->id)
                ->exists();

            if (!$dejaInscritGroupe) {
                DB::table('etudiant_groupe')->insert([
                    'etudiant_id' => $etudiant->id,
                    'groupe_id' => $groupe->id,
                ]);
            }

            // Inscription aux 3 cours du semestre 1
            foreach ($coursList as $cours) {
                InscriptionCours::query()->updateOrCreate(
                    [
                        'etudiant_id' => $etudiant->id,
                        'cours_id' => $cours->id,
                        'annee_id' => $annee->id,
                    ],
                    [
                        'date_inscription' => now(),
                    ]
                );
            }
        }
    }
}
