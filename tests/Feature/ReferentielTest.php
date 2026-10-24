<?php

namespace Tests\Feature;

use App\Domain\Identity\Enums\Role;
use App\Infrastructure\Persistence\Eloquent\Models\AnneeUniversitaire;
use App\Infrastructure\Persistence\Eloquent\Models\Cours;
use App\Infrastructure\Persistence\Eloquent\Models\Filiere;
use App\Infrastructure\Persistence\Eloquent\Models\Salle;
use App\Infrastructure\Persistence\Eloquent\Models\Semestre;
use App\Infrastructure\Persistence\Eloquent\Models\Utilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReferentielTest extends TestCase
{
    use RefreshDatabase;

    protected Utilisateur $admin;
    protected Utilisateur $scolarite;
    protected Utilisateur $etudiant;
    protected Filiere $filiere;
    protected AnneeUniversitaire $annee;
    protected Semestre $semestre;
    protected Cours $cours;
    protected Salle $salle;
    protected Utilisateur $enseignantUser;
    protected mixed $enseignant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Utilisateur::query()->create([
            'email' => 'admin-ref@univ.test',
            'mot_de_passe_hash' => bcrypt('Password123!'),
            'role' => Role::ADMIN->value,
            'actif' => true,
        ]);

        $this->scolarite = Utilisateur::query()->create([
            'email' => 'scolarite-ref@univ.test',
            'mot_de_passe_hash' => bcrypt('Password123!'),
            'role' => Role::SCOLARITE->value,
            'actif' => true,
        ]);

        $this->etudiant = Utilisateur::query()->create([
            'email' => 'etudiant-ref@univ.test',
            'mot_de_passe_hash' => bcrypt('Password123!'),
            'role' => Role::ETUDIANT->value,
            'actif' => true,
        ]);

        $this->filiere = Filiere::query()->create([
            'code' => 'M1-IG',
            'nom' => 'Master 1 - Ingénierie de Gestion',
        ]);

        $this->annee = AnneeUniversitaire::query()->create([
            'libelle' => '2026-2027',
            'date_debut' => '2026-09-01',
            'date_fin' => '2027-07-31',
            'courante' => true,
        ]);

        $this->semestre = Semestre::query()->create([
            'filiere_id' => $this->filiere->id,
            'numero' => 1,
        ]);

        $this->cours = Cours::query()->create([
            'code' => 'IG101',
            'intitule' => 'Algorithmique avancée',
            'semestre_id' => $this->semestre->id,
            'coefficient' => 2.5,
            'credits' => 4,
        ]);

        $this->salle = Salle::query()->create([
            'nom' => 'A101',
            'capacite' => 40,
        ]);

        $this->enseignantUser = Utilisateur::query()->create([
            'email' => 'enseignant-ref@univ.test',
            'mot_de_passe_hash' => bcrypt('Password123!'),
            'role' => Role::ENSEIGNANT->value,
            'actif' => true,
        ]);

        $this->enseignant = $this->enseignantUser->enseignant()->create([
            'nom' => 'Martin',
            'prenom' => 'Claire',
            'specialite' => 'Base de données',
        ]);
    }

    public function test_admin_crud_complet_des_ressources_referentielles(): void
    {
        Sanctum::actingAs($this->admin);

        $filiereResponse = $this->postJson('/api/filieres', [
            'code' => 'M2-IG',
            'nom' => 'Master 2 - Ingénierie de Gestion',
        ]);
        $filiereResponse->assertStatus(201)->assertJsonPath('data.code', 'M2-IG');

        $this->getJson('/api/filieres')->assertStatus(200);
        $filiereId = $filiereResponse->json('data.id');
        $this->putJson('/api/filieres/' . $filiereId, [
            'code' => 'M2-IG-ALT',
            'nom' => 'Master 2 - Ingénierie de Gestion alt',
        ])->assertStatus(200)->assertJsonPath('data.code', 'M2-IG-ALT');

        $anneeResponse = $this->postJson('/api/annees', [
            'libelle' => '2027-2028',
            'date_debut' => '2027-09-01',
            'date_fin' => '2028-07-31',
        ]);
        $anneeResponse->assertStatus(201)->assertJsonPath('data.libelle', '2027-2028');
        $anneeId = $anneeResponse->json('data.id');
        $this->getJson('/api/annees')->assertStatus(200);
        $this->putJson('/api/annees/' . $anneeId, [
            'libelle' => '2028-2029',
            'date_debut' => '2027-09-01',
            'date_fin' => '2028-07-31',
        ])->assertStatus(200)->assertJsonPath('data.libelle', '2028-2029');

        $semestreResponse = $this->postJson('/api/semestres', [
            'filiere_id' => $this->filiere->id,
            'numero' => 2,
        ]);
        $semestreResponse->assertStatus(201)->assertJsonPath('data.numero', 2);
        $semestreId = $semestreResponse->json('data.id');
        $this->getJson('/api/semestres')->assertStatus(200)
            ->assertJsonPath('data.0.libelle', 'Semestre 1')
            ->assertJsonPath('data.0.filiere.code', 'M1-IG');
        $this->getJson('/api/semestres')->assertStatus(200)
            ->assertJsonPath('data.0.libelle', 'Semestre 1')
            ->assertJsonPath('data.0.filiere.code', 'M1-IG');
        $this->putJson('/api/semestres/' . $semestreId, [
            'filiere_id' => $this->filiere->id,
            'numero' => 3,
        ])->assertStatus(200)->assertJsonPath('data.numero', 3);

        $coursResponse = $this->postJson('/api/cours', [
            'code' => 'IG102',
            'intitule' => 'Bases de données',
            'semestre_id' => $semestreId,
            'coefficient' => 3.5,
            'credits' => 5,
        ]);
        $coursResponse->assertStatus(201)->assertJsonPath('data.code', 'IG102');
        $coursId = $coursResponse->json('data.id');
        $this->getJson('/api/cours')->assertStatus(200)
            ->assertJsonPath('data.0.semestre.filiere.nom', 'Master 1 - Ingénierie de Gestion');
        $this->getJson('/api/cours')->assertStatus(200)
            ->assertJsonPath('data.0.semestre.filiere.nom', 'Master 1 - Ingénierie de Gestion');
        $this->putJson('/api/cours/' . $coursId, [
            'code' => 'IG102-BIS',
            'intitule' => 'Bases de données avancées',
            'semestre_id' => $semestreId,
            'coefficient' => 4,
            'credits' => 5,
        ])->assertStatus(200)->assertJsonPath('data.code', 'IG102-BIS');

        $salleResponse = $this->postJson('/api/salles', [
            'nom' => 'LAB2',
            'capacite' => 20,
        ]);
        $salleResponse->assertStatus(201)->assertJsonPath('data.nom', 'LAB2');
        $salleId = $salleResponse->json('data.id');
        $this->getJson('/api/salles')->assertStatus(200);
        $this->putJson('/api/salles/' . $salleId, [
            'nom' => 'LAB2-NEW',
            'capacite' => 25,
        ])->assertStatus(200)->assertJsonPath('data.nom', 'LAB2-NEW');

        $this->deleteJson('/api/filieres/' . $filiereId)->assertStatus(204);
        $this->deleteJson('/api/annees/' . $anneeId)->assertStatus(204);
        $this->deleteJson('/api/cours/' . $coursId)->assertStatus(204);
        $this->deleteJson('/api/semestres/' . $semestreId)->assertStatus(204);
        $this->deleteJson('/api/salles/' . $salleId)->assertStatus(204);
    }

    public function test_liste_des_semestres_est_eager_loaded_et_stable_en_nombre_de_requetes(): void
    {
        Sanctum::actingAs($this->admin);

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->getJson('/api/semestres')->assertStatus(200);
        $smallCount = count(DB::getQueryLog());

        foreach (range(2, 9) as $numero) {
            Semestre::query()->create([
                'filiere_id' => $this->filiere->id,
                'numero' => $numero,
            ]);
        }

        DB::flushQueryLog();
        $this->getJson('/api/semestres')->assertStatus(200);
        $largeCount = count(DB::getQueryLog());

        $this->assertSame($smallCount, $largeCount);
        $this->assertLessThanOrEqual(10, $largeCount);
    }

    public function test_scolarite_et_etudiant_sont_refuses_en_ecriture(): void
    {
        Sanctum::actingAs($this->scolarite);

        $this->postJson('/api/filieres', ['code' => 'AN1', 'nom' => 'Annexe 1'])->assertStatus(403);
        $this->putJson('/api/filieres/' . $this->filiere->id, ['code' => 'M1-IG-2', 'nom' => 'Master 1 alt'])->assertStatus(403);
        $this->deleteJson('/api/filieres/' . $this->filiere->id)->assertStatus(403);
        $this->postJson('/api/annees', ['libelle' => '2028-2029', 'date_debut' => '2028-09-01', 'date_fin' => '2029-07-31'])->assertStatus(403);
        $this->putJson('/api/annees/' . $this->annee->id, ['libelle' => '2028-2029', 'date_debut' => '2028-09-01', 'date_fin' => '2029-07-31'])->assertStatus(403);
        $this->deleteJson('/api/annees/' . $this->annee->id)->assertStatus(403);
        $this->postJson('/api/semestres', ['filiere_id' => $this->filiere->id, 'numero' => 2])->assertStatus(403);
        $this->putJson('/api/semestres/' . $this->semestre->id, ['filiere_id' => $this->filiere->id, 'numero' => 2])->assertStatus(403);
        $this->deleteJson('/api/semestres/' . $this->semestre->id)->assertStatus(403);
        $this->postJson('/api/cours', ['code' => 'IG999', 'intitule' => 'Test', 'semestre_id' => $this->semestre->id, 'coefficient' => 1.5, 'credits' => 2])->assertStatus(403);
        $this->putJson('/api/cours/' . $this->cours->id, ['code' => 'IG999-BIS', 'intitule' => 'Test bis', 'semestre_id' => $this->semestre->id, 'coefficient' => 2, 'credits' => 3])->assertStatus(403);
        $this->deleteJson('/api/cours/' . $this->cours->id)->assertStatus(403);
        $this->postJson('/api/salles', ['nom' => 'S103', 'capacite' => 20])->assertStatus(403);
        $this->putJson('/api/salles/' . $this->salle->id, ['nom' => 'S103', 'capacite' => 18])->assertStatus(403);
        $this->deleteJson('/api/salles/' . $this->salle->id)->assertStatus(403);

        Sanctum::actingAs($this->etudiant);
        $this->getJson('/api/filieres')->assertStatus(200);
        $this->getJson('/api/cours')->assertStatus(200);
        $this->getJson('/api/salles')->assertStatus(403);
    }

    public function test_doublon_de_code_ou_nom_filiere_renvoie_409(): void
    {
        Sanctum::actingAs($this->admin);

        $this->postJson('/api/filieres', ['code' => 'M1-IG', 'nom' => 'Autre nom'])->assertStatus(409);
        $this->postJson('/api/filieres', ['code' => 'M9-IG', 'nom' => 'Master 1 - Ingénierie de Gestion'])->assertStatus(409);
    }

    public function test_donnees_invalides_renvoient_422(): void
    {
        Sanctum::actingAs($this->admin);

        $this->postJson('/api/cours', ['code' => 'IG900', 'intitule' => 'Coefficient zéro', 'semestre_id' => $this->semestre->id, 'coefficient' => 0, 'credits' => 2])->assertStatus(422);
        $this->postJson('/api/annees', ['libelle' => '2026-2028', 'date_debut' => '2026-09-01', 'date_fin' => '2026-09-01'])->assertStatus(422);
        $this->postJson('/api/salles', ['nom' => 'SERRURE', 'capacite' => 0])->assertStatus(422);
    }

    public function test_suppressions_structurelles_interdites_renvoient_409(): void
    {
        Sanctum::actingAs($this->admin);

        Semestre::query()->create(['filiere_id' => $this->filiere->id, 'numero' => 2]);
        $this->deleteJson('/api/filieres/' . $this->filiere->id)->assertStatus(409)->assertJsonPath('message', 'Suppression impossible : des données en dépendent');

        $semestreAvecCours = Semestre::query()->create(['filiere_id' => $this->filiere->id, 'numero' => 3]);
        Cours::query()->create(['code' => 'IG204', 'intitule' => 'Cours lié au semestre', 'semestre_id' => $semestreAvecCours->id, 'coefficient' => 2, 'credits' => 4]);
        $this->deleteJson('/api/semestres/' . $semestreAvecCours->id)->assertStatus(409)->assertJsonPath('message', 'Suppression impossible : des données en dépendent');
    }

    public function test_ne_garde_qu_une_annee_courante(): void
    {
        Sanctum::actingAs($this->admin);

        $annee2 = AnneeUniversitaire::query()->create([
            'libelle' => '2027-2028',
            'date_debut' => '2027-09-01',
            'date_fin' => '2028-07-31',
            'courante' => false,
        ]);

        $this->patchJson('/api/annees/' . $this->annee->id . '/courante')->assertStatus(200);
        $this->patchJson('/api/annees/' . $annee2->id . '/courante')->assertStatus(200);
        $this->assertSame(1, AnneeUniversitaire::query()->where('courante', true)->count());
    }

    public function test_affectation_enseignant_puis_liste_enseignants_du_cours(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/cours/' . $this->cours->id . '/enseignants', [
            'enseignant_id' => $this->enseignant->id,
            'annee_id' => $this->annee->id,
        ]);
        $response->assertStatus(200)->assertJsonPath('message', 'L’enseignant a été affecté au cours.');

        $this->getJson('/api/cours/' . $this->cours->id . '/enseignants?annee_id=' . $this->annee->id)
            ->assertStatus(200)
            ->assertJsonPath('data.0.id', $this->enseignant->id)
            ->assertJsonPath('data.0.email', 'enseignant-ref@univ.test');

        $this->postJson('/api/cours/' . $this->cours->id . '/enseignants', [
            'enseignant_id' => $this->enseignant->id,
            'annee_id' => $this->annee->id,
        ])->assertStatus(409);
    }
}