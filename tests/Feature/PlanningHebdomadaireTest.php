<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\Models\Utilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PlanningHebdomadaireTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_edt_hebdomadaire_est_prepare_valide_publie_et_affiche_aux_etudiants(): void
    {
        $this->seed();

        $enseignantUser = Utilisateur::query()->where('email', 'enseignant@univ.test')->firstOrFail();
        $scolarite = Utilisateur::query()->where('email', 'scolarite@univ.test')->firstOrFail();
        $admin = Utilisateur::query()->where('email', 'admin@univ.test')->firstOrFail();
        $etudiant = Utilisateur::query()->where('email', 'etudiant@univ.test')->firstOrFail();
        $enseignant = DB::table('enseignant')->where('utilisateur_id', $enseignantUser->id)->firstOrFail();
        $filiere = DB::table('filiere')->where('code', 'L1-ISLI')->firstOrFail();
        $cours = DB::table('cours')->where('code', 'IG101')->firstOrFail();
        $groupe = DB::table('groupe')->where('nom', 'G1')->where('filiere_id', $filiere->id)->firstOrFail();
        $salle = DB::table('salle')->where('nom', 'A101')->firstOrFail();
        $semaine = '2026-11-16';

        Sanctum::actingAs($enseignantUser);
        $this->postJson('/api/edt/disponibilites', [
            'semaine_debut' => $semaine,
            'disponibilites' => [[
                'debut' => '2026-11-16 08:00:00',
                'fin' => '2026-11-16 10:00:00',
                'note' => 'Disponibilité de démonstration',
            ]],
        ])->assertCreated();

        Sanctum::actingAs($scolarite);
        $this->getJson('/api/edt/disponibilites?semaine_debut='.$semaine)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.enseignant_id', $enseignant->id);

        $response = $this->postJson('/api/edt/hebdomadaires', [
            'filiere_id' => $filiere->id,
            'semaine_debut' => $semaine,
        ])->assertCreated();
        $planningId = $response->json('data.id');

        $seancePayload = [
            'cours_id' => $cours->id,
            'groupe_id' => $groupe->id,
            'enseignant_id' => $enseignant->id,
            'salle_id' => $salle->id,
            'debut' => '2026-11-16 08:00:00',
            'fin' => '2026-11-16 09:00:00',
        ];
        $this->postJson("/api/edt/hebdomadaires/{$planningId}/seances", $seancePayload)->assertCreated();
        $this->postJson("/api/edt/hebdomadaires/{$planningId}/seances", $seancePayload)
            ->assertStatus(409)
            ->assertJsonPath('message', 'Le créneau choisi entre en conflit avec une autre proposition hebdomadaire.');

        $this->postJson("/api/edt/hebdomadaires/{$planningId}/soumettre")->assertOk();
        $this->postJson("/api/edt/hebdomadaires/{$planningId}/valider")
            ->assertOk();

        Sanctum::actingAs($etudiant);
        $this->getJson('/api/etudiants/me/emploi-du-temps?du=2026-11-16&au=2026-11-22')
            ->assertOk()
            ->assertJsonCount(0, 'data.jours');

        Sanctum::actingAs($admin);
        $this->postJson('/api/seances', [
            'cours_id' => $cours->id,
            'groupe_id' => $groupe->id,
            'enseignant_id' => $enseignant->id,
            'salle_id' => $salle->id,
            'debut' => '2026-11-16 08:00:00',
            'fin' => '2026-11-16 09:00:00',
        ])->assertMethodNotAllowed();
        $this->postJson("/api/edt/hebdomadaires/{$planningId}/publier")
            ->assertOk()
            ->assertJsonPath('nombre_seances', 1);
        $this->assertDatabaseHas('edt_hebdomadaire', [
            'id' => $planningId,
            'statut' => 'PUBLIEE',
            'publie_par' => $admin->id,
        ]);
        $this->assertDatabaseHas('seance', [
            'edt_seance_id' => DB::table('edt_hebdomadaire_seance')->where('edt_id', $planningId)->value('id'),
            'cours_id' => $cours->id,
            'groupe_id' => $groupe->id,
        ]);

        Sanctum::actingAs($etudiant);
        $this->getJson('/api/etudiants/me/emploi-du-temps?du=2026-11-16&au=2026-11-22')
            ->assertOk()
            ->assertJsonPath('data.jours.0.seances.0.cours.code', 'IG101');
    }

    public function test_disponibilites_et_seances_respectent_les_roles_et_les_creaneaux_declares(): void
    {
        $this->seed();

        $enseignantUser = Utilisateur::query()->where('email', 'enseignant@univ.test')->firstOrFail();
        $scolarite = Utilisateur::query()->where('email', 'scolarite@univ.test')->firstOrFail();
        $enseignant = DB::table('enseignant')->where('utilisateur_id', $enseignantUser->id)->firstOrFail();
        $filiere = DB::table('filiere')->where('code', 'L1-ISLI')->firstOrFail();
        $cours = DB::table('cours')->where('code', 'IG101')->firstOrFail();
        $groupe = DB::table('groupe')->where('nom', 'G1')->where('filiere_id', $filiere->id)->firstOrFail();
        $salle = DB::table('salle')->where('nom', 'A101')->firstOrFail();

        Sanctum::actingAs($enseignantUser);
        $this->postJson('/api/edt/hebdomadaires', [
            'filiere_id' => $filiere->id,
            'semaine_debut' => '2026-11-16',
        ])->assertForbidden();

        $this->postJson('/api/edt/disponibilites', [
            'semaine_debut' => '2026-11-16',
            'disponibilites' => [[
                'debut' => '2026-11-17 08:00:00',
                'fin' => '2026-11-17 10:00:00',
            ]],
        ])->assertCreated();

        $this->postJson('/api/edt/disponibilites', [
            'semaine_debut' => '2026-11-16',
            'disponibilites' => [[
                'debut' => '2026-11-23 08:00:00',
                'fin' => '2026-11-23 10:00:00',
            ]],
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['disponibilites']);

        Sanctum::actingAs($scolarite);
        $planningId = $this->postJson('/api/edt/hebdomadaires', [
            'filiere_id' => $filiere->id,
            'semaine_debut' => '2026-11-16',
        ])->assertCreated()->json('data.id');

        $this->postJson("/api/edt/hebdomadaires/{$planningId}/seances", [
            'cours_id' => $cours->id,
            'groupe_id' => $groupe->id,
            'enseignant_id' => $enseignant->id,
            'salle_id' => $salle->id,
            'debut' => '2026-11-16 08:00:00',
            'fin' => '2026-11-16 09:00:00',
        ])->assertUnprocessable()
            ->assertJsonPath('message', 'Le créneau ne se trouve pas dans les disponibilités déclarées par cet enseignant.');

        Sanctum::actingAs($scolarite);
        $this->postJson("/api/edt/hebdomadaires/{$planningId}/publier")->assertForbidden();
    }

    public function test_la_semaine_doit_commencer_un_lundi_et_la_scolarite_ne_peut_pas_soumettre_un_edt_vide(): void
    {
        $this->seed();

        $scolarite = Utilisateur::query()->where('email', 'scolarite@univ.test')->firstOrFail();
        $filiere = DB::table('filiere')->where('code', 'L1-ISLI')->firstOrFail();
        Sanctum::actingAs($scolarite);

        $this->postJson('/api/edt/hebdomadaires', [
            'filiere_id' => $filiere->id,
            'semaine_debut' => '2026-11-17',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['semaine_debut']);

        $planningId = $this->postJson('/api/edt/hebdomadaires', [
            'filiere_id' => $filiere->id,
            'semaine_debut' => '2026-11-16',
        ])->assertCreated()->json('data.id');

        $this->postJson("/api/edt/hebdomadaires/{$planningId}/soumettre")
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Ajoutez au moins une séance avant de soumettre cet emploi du temps.');
    }
}
