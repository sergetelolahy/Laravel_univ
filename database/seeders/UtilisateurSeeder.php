<?php

namespace Database\Seeders;

use App\Domain\Identity\Enums\Role;
use App\Infrastructure\Persistence\Eloquent\Models\Filiere;
use App\Infrastructure\Persistence\Eloquent\Models\Utilisateur;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UtilisateurSeeder extends Seeder
{
    public function run(): void
    {
        $filiere = Filiere::query()->firstOrCreate(
            ['code' => 'M1-IG'],
            ['nom' => 'Master 1 - Ingénierie de Gestion'],
        );

        $comptes = [
            [
                'email' => 'admin@univ.test',
                'role' => Role::ADMIN->value,
                'actif' => true,
            ],
            [
                'email' => 'scolarite@univ.test',
                'role' => Role::SCOLARITE->value,
                'actif' => true,
            ],
            [
                'email' => 'enseignant@univ.test',
                'role' => Role::ENSEIGNANT->value,
                'actif' => true,
                'profil' => [
                    'nom' => 'Martin',
                    'prenom' => 'Claire',
                    'specialite' => 'Base de données',
                ],
            ],
            [
                'email' => 'etudiant@univ.test',
                'role' => Role::ETUDIANT->value,
                'actif' => true,
                'profil' => [
                    'matricule' => 'ET0001',
                    'nom' => 'Durand',
                    'prenom' => 'Alice',
                    'date_naissance' => '2002-05-14',
                    'filiere_id' => $filiere->id,
                ],
            ],
        ];

        foreach ($comptes as $compte) {
            $utilisateur = Utilisateur::query()->create([
                'email' => $compte['email'],
                'mot_de_passe_hash' => Hash::make('Password123!'),
                'role' => $compte['role'],
                'actif' => $compte['actif'],
            ]);

            if (($compte['role'] ?? null) === Role::ENSEIGNANT->value) {
                $utilisateur->enseignant()->create([
                    'nom' => $compte['profil']['nom'],
                    'prenom' => $compte['profil']['prenom'],
                    'specialite' => $compte['profil']['specialite'],
                ]);
            }

            if (($compte['role'] ?? null) === Role::ETUDIANT->value) {
                $utilisateur->etudiant()->create([
                    'matricule' => $compte['profil']['matricule'],
                    'nom' => $compte['profil']['nom'],
                    'prenom' => $compte['profil']['prenom'],
                    'date_naissance' => $compte['profil']['date_naissance'],
                    'filiere_id' => $compte['profil']['filiere_id'],
                ]);
            }
        }
    }
}
