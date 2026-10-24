<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Identity\Enums\Role;
use App\Infrastructure\Persistence\Eloquent\Models\AnneeUniversitaire;
use App\Infrastructure\Persistence\Eloquent\Models\Cours;
use App\Infrastructure\Persistence\Eloquent\Models\Enseignant;
use App\Infrastructure\Persistence\Eloquent\Models\Etudiant;
use App\Infrastructure\Persistence\Eloquent\Models\Filiere;
use App\Infrastructure\Persistence\Eloquent\Models\Groupe;
use App\Infrastructure\Persistence\Eloquent\Models\InscriptionCours;
use App\Infrastructure\Persistence\Eloquent\Models\Salle;
use App\Infrastructure\Persistence\Eloquent\Models\Semestre;
use App\Infrastructure\Persistence\Eloquent\Models\Utilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ScolariteTest extends TestCase
{
    use RefreshDatabase;

    protected Utilisateur $admin;
    protected Utilisateur $scolarite;
    protected Utilisateur $etudiantUserA;
    protected Etudiant $etudiantA;
    protected Utilisateur $etudiantUserB;
    protected Etudiant $etudiantB;
    protected Utilisateur $etudiantUserAutreFiliere;
    protected Etudiant $etudiantAutreFiliere;
    protected Utilisateur $enseignantUserAffecte;
    protected Enseignant $enseignantAffecte;
    protected Utilisateur $enseignantUserNonAffecte;
    protected Enseignant $enseignantNonAffecte;

    protected Filiere $filiereIG;
    protected Filiere $filiereGL;
    protected AnneeUniversitaire $anneeCourante;
    protected AnneeUniversitaire $anneeAutre;
    protected Semestre $semestre1;
    protected Semestre $semestreAutreFiliere;
    protected Cours $cours1;
    protected Cours $cours2;
    protected Cours $coursAutreFiliere;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filiereIG = Filiere::query()->create([
            'code' => 'M1-IG',
            'nom' => 'Master 1 - Ingénierie de Gestion',
        ]);

        $this->filiereGL = Filiere::query()->create([
            'code' => 'M1-GL',
            'nom' => 'Master 1 - Génie Logiciel',
        ]);

        $this->anneeCourante = AnneeUniversitaire::query()->create([
            'libelle' => '2026-2027',
            'date_debut' => '2026-09-01',
            'date_fin' => '2027-07-31',
            'courante' => true,
        ]);

        $this->anneeAutre = AnneeUniversitaire::query()->create([
            'libelle' => '2025-2026',
            'date_debut' => '2025-09-01',
            'date_fin' => '2026-07-31',
            'courante' => false,
        ]);

        $this->semestre1 = Semestre::query()->create([
            'filiere_id' => $this->filiereIG->id,
            'numero' => 1,
        ]);

        $this->semestreAutreFiliere = Semestre::query()->create([
            'filiere_id' => $this->filiereGL->id,
            'numero' => 1,
        ]);

        $this->cours1 = Cours::query()->create([
            'code' => 'IG101',
            'intitule' => 'Algorithmique',
            'semestre_id' => $this->semestre1->id,
            'coefficient' => 2.0,
            'credits' => 4,
        ]);

        $this->cours2 = Cours::query()->create([
            'code' => 'IG102',
            'intitule' => 'Base de données',
            'semestre_id' => $this->semestre1->id,
            'coefficient' => 3.0,
            'credits' => 5,
        ]);

        $this->coursAutreFiliere = Cours::query()->create([
            'code' => 'GL101',
            'intitule' => 'Génie Logiciel Avancé',
            'semestre_id' => $this->semestreAutreFiliere->id,
            'coefficient' => 2.0,
            'credits' => 4,
        ]);

        $this->admin = Utilisateur::query()->create([
            'email' => 'admin-sco@univ.test',
            'mot_de_passe_hash' => bcrypt('Password123!'),
            'role' => Role::ADMIN->value,
            'actif' => true,
        ]);

        $this->scolarite = Utilisateur::query()->create([
            'email' => 'scolarite-sco@univ.test',
            'mot_de_passe_hash' => bcrypt('Password123!'),
            'role' => Role::SCOLARITE->value,
            'actif' => true,
        ]);

        $this->etudiantUserA = Utilisateur::query()->create([
            'email' => 'etudiant-a@univ.test',
            'mot_de_passe_hash' => bcrypt('Password123!'),
            'role' => Role::ETUDIANT->value,
            'actif' => true,
        ]);
        $this->etudiantA = Etudiant::query()->create([
            'utilisateur_id' => $this->etudiantUserA->id,
            'matricule' => 'ET0001',
            'nom' => 'Rakoto',
            'prenom' => 'Jean',
            'filiere_id' => $this->filiereIG->id,
        ]);

        $this->etudiantUserB = Utilisateur::query()->create([
            'email' => 'etudiant-b@univ.test',
            'mot_de_passe_hash' => bcrypt('Password123!'),
            'role' => Role::ETUDIANT->value,
            'actif' => true,
        ]);
        $this->etudiantB = Etudiant::query()->create([
            'utilisateur_id' => $this->etudiantUserB->id,
            'matricule' => 'ET0002',
            'nom' => 'Rabe',
            'prenom' => 'Paul',
            'filiere_id' => $this->filiereIG->id,
        ]);

        $this->etudiantUserAutreFiliere = Utilisateur::query()->create([
            'email' => 'etudiant-gl@univ.test',
            'mot_de_passe_hash' => bcrypt('Password123!'),
            'role' => Role::ETUDIANT->value,
            'actif' => true,
        ]);
        $this->etudiantAutreFiliere = Etudiant::query()->create([
            'utilisateur_id' => $this->etudiantUserAutreFiliere->id,
            'matricule' => 'ET0003',
            'nom' => 'Rasoa',
            'prenom' => 'Marie',
            'filiere_id' => $this->filiereGL->id,
        ]);

        $this->enseignantUserAffecte = Utilisateur::query()->create([
            'email' => 'prof-affecte@univ.test',
            'mot_de_passe_hash' => bcrypt('Password123!'),
            'role' => Role::ENSEIGNANT->value,
            'actif' => true,
        ]);
        $this->enseignantAffecte = Enseignant::query()->create([
            'utilisateur_id' => $this->enseignantUserAffecte->id,
            'nom' => 'Andria',
            'prenom' => 'Michel',
            'specialite' => 'Informatique',
        ]);

        $this->enseignantUserNonAffecte = Utilisateur::query()->create([
            'email' => 'prof-nonaffecte@univ.test',
            'mot_de_passe_hash' => bcrypt('Password123!'),
            'role' => Role::ENSEIGNANT->value,
            'actif' => true,
        ]);
        $this->enseignantNonAffecte = Enseignant::query()->create([
            'utilisateur_id' => $this->enseignantUserNonAffecte->id,
            'nom' => 'Randria',
            'prenom' => 'Luc',
            'specialite' => 'Maths',
        ]);

        // Affectation de l'enseignant au cours 1 pour l'année courante
        DB::table('enseignant_cours')->insert([
            'enseignant_id' => $this->enseignantAffecte->id,
            'cours_id' => $this->cours1->id,
            'annee_id' => $this->anneeCourante->id,
        ]);
    }

    /**
     * 1. Inscription par la scolarité (201) ; même inscription deux fois (409) ; même cours une autre année (201)
     */
    public function test_inscription_par_scolarite_et_contraintes_doublon(): void
    {
        for ($exec = 1; $exec <= 2; $exec++) {
            Sanctum::actingAs($this->scolarite);

            $cours = $exec === 1 ? $this->cours1 : $this->cours2;

            // Inscription par la scolarité (201)
            $response = $this->postJson('/api/inscriptions', [
                'etudiant_id' => $this->etudiantA->id,
                'cours_id' => $cours->id,
                'annee_id' => $this->anneeCourante->id,
            ]);
            $response->assertStatus(201)
                ->assertJsonPath('data.etudiant_id', $this->etudiantA->id)
                ->assertJsonPath('data.cours_id', $cours->id);

            // Même inscription deux fois (409)
            $doublon = $this->postJson('/api/inscriptions', [
                'etudiant_id' => $this->etudiantA->id,
                'cours_id' => $cours->id,
                'annee_id' => $this->anneeCourante->id,
            ]);
            $doublon->assertStatus(409);

            // Même cours une autre année (201)
            $autreAnnee = $this->postJson('/api/inscriptions', [
                'etudiant_id' => $this->etudiantA->id,
                'cours_id' => $cours->id,
                'annee_id' => $this->anneeAutre->id,
            ]);
            $autreAnnee->assertStatus(201)
                ->assertJsonPath('data.etudiant_id', $this->etudiantA->id)
                ->assertJsonPath('data.annee_id', $this->anneeAutre->id);
        }
    }

    /**
     * 2. Étudiant qui s'inscrit lui-même pour l'année courante (201) ;
     *    qui tente d'inscrire un autre étudiant (seule sa propre inscription est créée) ;
     *    pour une autre année que la courante (422) ;
     *    à un cours d'une autre filière (422)
     */
    public function test_etudiant_inscription_regles_et_contraintes(): void
    {
        for ($exec = 1; $exec <= 2; $exec++) {
            Sanctum::actingAs($this->etudiantUserA);

            $cours = $exec === 1 ? $this->cours1 : $this->cours2;

            // Étudiant qui s'inscrit lui-même pour l'année courante (201)
            $resp1 = $this->postJson('/api/inscriptions', [
                'etudiant_id' => $this->etudiantA->id,
                'cours_id' => $cours->id,
                'annee_id' => $this->anneeCourante->id,
            ]);
            $resp1->assertStatus(201)
                ->assertJsonPath('data.etudiant_id', $this->etudiantA->id);

            // Étudiant qui tente d'inscrire un autre étudiant : seule sa propre inscription est créée
            // Ici l'étudiant A tente d'inscrire l'étudiant B au cours 1 (déjà inscrit pour A, donc conflit 409 pour A)
            // Ou pour un nouveau cours où A n'est pas inscrit mais donne l'id de B :
            // Créons un cours temporaire pour ce test afin d'isoler
            $coursTmp = Cours::query()->create([
                'code' => 'TMP' . $exec,
                'intitule' => 'Cours Temporaire ' . $exec,
                'semestre_id' => $this->semestre1->id,
                'coefficient' => 1.0,
                'credits' => 2,
            ]);

            $respAutre = $this->postJson('/api/inscriptions', [
                'etudiant_id' => $this->etudiantB->id,
                'cours_id' => $coursTmp->id,
                'annee_id' => $this->anneeCourante->id,
            ]);
            // La réponse doit être 201 mais l'inscription créée appartient à etudiantA
            $respAutre->assertStatus(201)
                ->assertJsonPath('data.etudiant_id', $this->etudiantA->id);

            $this->assertDatabaseHas('inscription_cours', [
                'etudiant_id' => $this->etudiantA->id,
                'cours_id' => $coursTmp->id,
                'annee_id' => $this->anneeCourante->id,
            ]);
            $this->assertDatabaseMissing('inscription_cours', [
                'etudiant_id' => $this->etudiantB->id,
                'cours_id' => $coursTmp->id,
                'annee_id' => $this->anneeCourante->id,
            ]);

            // Pour une autre année que la courante (422)
            $respAnneeNonCourante = $this->postJson('/api/inscriptions', [
                'etudiant_id' => $this->etudiantA->id,
                'cours_id' => $cours->id,
                'annee_id' => $this->anneeAutre->id,
            ]);
            $respAnneeNonCourante->assertStatus(422);

            // À un cours d'une autre filière (422)
            $respAutreFiliere = $this->postJson('/api/inscriptions', [
                'etudiant_id' => $this->etudiantA->id,
                'cours_id' => $this->coursAutreFiliere->id,
                'annee_id' => $this->anneeCourante->id,
            ]);
            $respAutreFiliere->assertStatus(422);
        }
    }

    /**
     * 3. Inscription en lot avec un doublon : rien n'est enregistré (409)
     */
    public function test_inscription_en_lot_avec_doublon_refuse_et_atomique(): void
    {
        for ($exec = 1; $exec <= 2; $exec++) {
            Sanctum::actingAs($this->scolarite);

            $coursTmp1 = Cours::query()->create([
                'code' => 'LOT1_' . $exec,
                'intitule' => 'Lot 1',
                'semestre_id' => $this->semestre1->id,
                'coefficient' => 1.0,
                'credits' => 2,
            ]);
            $coursTmp2 = Cours::query()->create([
                'code' => 'LOT2_' . $exec,
                'intitule' => 'Lot 2',
                'semestre_id' => $this->semestre1->id,
                'coefficient' => 1.0,
                'credits' => 2,
            ]);

            // Lot contenant un doublon (même étudiant, même cours deux fois dans le payload)
            $payload = [
                'inscriptions' => [
                    [
                        'etudiant_id' => $this->etudiantA->id,
                        'cours_id' => $coursTmp1->id,
                        'annee_id' => $this->anneeCourante->id,
                    ],
                    [
                        'etudiant_id' => $this->etudiantB->id,
                        'cours_id' => $coursTmp2->id,
                        'annee_id' => $this->anneeCourante->id,
                    ],
                    [
                        'etudiant_id' => $this->etudiantA->id,
                        'cours_id' => $coursTmp1->id,
                        'annee_id' => $this->anneeCourante->id,
                    ],
                ],
            ];

            $response = $this->postJson('/api/inscriptions/lot', $payload);
            $response->assertStatus(409);

            // Vérifier que RIEN n'a été inséré (atomicité)
            $this->assertDatabaseMissing('inscription_cours', [
                'cours_id' => $coursTmp1->id,
            ]);
            $this->assertDatabaseMissing('inscription_cours', [
                'cours_id' => $coursTmp2->id,
            ]);
        }
    }

    /**
     * 4. GET /cours/{id}/inscrits : enseignant affecté au cours (200), non affecté (403), scolarité (200)
     */
    public function test_liste_inscrits_du_cours_selon_role_et_affectation(): void
    {
        // Créer une inscription sur cours1
        DB::table('inscription_cours')->insert([
            'etudiant_id' => $this->etudiantA->id,
            'cours_id' => $this->cours1->id,
            'annee_id' => $this->anneeCourante->id,
            'date_inscription' => now()->format('Y-m-d H:i:s'),
        ]);

        for ($exec = 1; $exec <= 2; $exec++) {
            // Enseignant affecté au cours (200)
            Sanctum::actingAs($this->enseignantUserAffecte);
            $respAffecte = $this->getJson('/api/cours/' . $this->cours1->id . '/inscrits');
            $respAffecte->assertStatus(200)
                ->assertJsonPath('message', 'Liste des inscrits récupérée avec succès.');

            // Enseignant non affecté (403)
            Sanctum::actingAs($this->enseignantUserNonAffecte);
            $respNonAffecte = $this->getJson('/api/cours/' . $this->cours1->id . '/inscrits');
            $respNonAffecte->assertStatus(403);

            // Scolarité (200)
            Sanctum::actingAs($this->scolarite);
            $respSco = $this->getJson('/api/cours/' . $this->cours1->id . '/inscrits');
            $respSco->assertStatus(200)
                ->assertJsonPath('message', 'Liste des inscrits récupérée avec succès.');
        }
    }

    /**
     * 5. Groupes :
     *    - ajout d'étudiants (200)
     *    - dépassement d'effectif_max (422)
     *    - étudiant déjà dans un autre groupe de la même année (409)
     *    - étudiant d'une autre filière (422)
     *    - suppression d'un groupe non vide (409)
     */
    public function test_groupes_regles_gestion_et_contraintes(): void
    {
        for ($exec = 1; $exec <= 2; $exec++) {
            Sanctum::actingAs($this->scolarite);

            $groupePlein = Groupe::query()->create([
                'nom' => 'G_LIM_' . $exec,
                'filiere_id' => $this->filiereIG->id,
                'annee_id' => $this->anneeCourante->id,
                'effectif_max' => 1,
            ]);

            $groupeAutre = Groupe::query()->create([
                'nom' => 'G_SEC_' . $exec,
                'filiere_id' => $this->filiereIG->id,
                'annee_id' => $this->anneeCourante->id,
                'effectif_max' => 5,
            ]);

            // Ajout d'étudiant valide (200)
            $respAjout = $this->postJson('/api/groupes/' . $groupePlein->id . '/etudiants', [
                'etudiant_ids' => [$this->etudiantA->id],
            ]);
            $respAjout->assertStatus(200)
                ->assertJsonPath('message', 'Étudiants ajoutés au groupe avec succès.');

            // Dépassement d'effectif_max (422) : le groupePlein a une capacité de 1 et est déjà plein
            $respDepassement = $this->postJson('/api/groupes/' . $groupePlein->id . '/etudiants', [
                'etudiant_ids' => [$this->etudiantB->id],
            ]);
            $respDepassement->assertStatus(422);

            // Étudiant déjà dans un autre groupe de la même année (409)
            // L'étudiant A est déjà dans groupePlein, on tente de l'ajouter dans groupeAutre
            $respDejaGroupe = $this->postJson('/api/groupes/' . $groupeAutre->id . '/etudiants', [
                'etudiant_ids' => [$this->etudiantA->id],
            ]);
            $respDejaGroupe->assertStatus(409);

            // Étudiant d'une autre filière (422)
            $respAutreFiliere = $this->postJson('/api/groupes/' . $groupeAutre->id . '/etudiants', [
                'etudiant_ids' => [$this->etudiantAutreFiliere->id],
            ]);
            $respAutreFiliere->assertStatus(422);

            // Suppression d'un groupe non vide (409)
            $respSupprNonVide = $this->deleteJson('/api/groupes/' . $groupePlein->id);
            $respSupprNonVide->assertStatus(409);

            // Nettoyage pour permettre la 2ème itération
            DB::table('etudiant_groupe')->where('groupe_id', $groupePlein->id)->delete();
            $groupePlein->delete();
            $groupeAutre->delete();
        }
    }

    /**
     * 6. Permissions :
     *    - /etudiants/me : étudiant (200), enseignant (403), sans jeton (401)
     *    - GET /etudiants : étudiant (403)
     *    - PUT /etudiants/{id} par admin et scolarité (200)
     */
    public function test_etudiants_routes_et_permissions(): void
    {
        for ($exec = 1; $exec <= 2; $exec++) {
            // /etudiants/me : sans jeton (401)
            app('auth')->forgetGuards();
            $respSansJeton = $this->getJson('/api/etudiants/me');
            $respSansJeton->assertStatus(401);

            // /etudiants/me : enseignant (403)
            Sanctum::actingAs($this->enseignantUserAffecte);
            $respProf = $this->getJson('/api/etudiants/me');
            $respProf->assertStatus(403);

            // /etudiants/me : étudiant (200)
            Sanctum::actingAs($this->etudiantUserA);
            $respEtudiant = $this->getJson('/api/etudiants/me');
            $respEtudiant->assertStatus(200)
                ->assertJsonPath('data.matricule', 'ET0001');

            // GET /etudiants : étudiant (403)
            $respListEtudiant = $this->getJson('/api/etudiants');
            $respListEtudiant->assertStatus(403);

            // PUT /etudiants/{id} par admin (200)
            Sanctum::actingAs($this->admin);
            $respPutAdmin = $this->putJson('/api/etudiants/' . $this->etudiantA->id, [
                'nom' => 'RakotoModifieAdmin' . $exec,
            ]);
            $respPutAdmin->assertStatus(200)
                ->assertJsonPath('data.nom', 'RakotoModifieAdmin' . $exec);

            // PUT /etudiants/{id} par scolarité (200)
            Sanctum::actingAs($this->scolarite);
            $respPutSco = $this->putJson('/api/etudiants/' . $this->etudiantA->id, [
                'nom' => 'RakotoModifieSco' . $exec,
            ]);
            $respPutSco->assertStatus(200)
                ->assertJsonPath('data.nom', 'RakotoModifieSco' . $exec);
        }
    }
}
