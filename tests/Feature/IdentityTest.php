<?php

use App\Domain\Identity\Enums\Role;
use App\Infrastructure\Persistence\Eloquent\Models\Filiere;
use App\Infrastructure\Persistence\Eloquent\Models\Utilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->filiere = Filiere::query()->create([
        'code' => 'M1-IG',
        'nom' => 'Master 1 - Ingénierie de Gestion',
    ]);

    Utilisateur::query()->create([
        'email' => 'admin@univ.test',
        'mot_de_passe_hash' => bcrypt('Password123!'),
        'role' => Role::ADMIN->value,
        'actif' => true,
    ]);
});

it('login valide', function (): void {
    $user = Utilisateur::query()->create([
        'email' => 'login@univ.test',
        'mot_de_passe_hash' => bcrypt('Password123!'),
        'role' => Role::ADMIN->value,
        'actif' => true,
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'login@univ.test',
        'password' => 'Password123!',
    ]);

    $response->assertOk()
        ->assertJsonPath('message', 'Connexion réussie.')
        ->assertJsonPath('utilisateur.email', 'login@univ.test');

    $this->assertNotNull($user->fresh()->tokens()->first());
});

it('login deux fois de suite réussit et met a jour derniere_connexion', function (): void {
    $user = Utilisateur::query()->create([
        'email' => 'doublelogin@univ.test',
        'mot_de_passe_hash' => bcrypt('Password123!'),
        'role' => Role::ADMIN->value,
        'actif' => true,
    ]);

    $premier = $this->postJson('/api/auth/login', [
        'email' => 'doublelogin@univ.test',
        'password' => 'Password123!',
    ]);

    $premier->assertOk()->assertJsonPath('utilisateur.email', 'doublelogin@univ.test');

    $this->assertNotNull($user->fresh()->derniere_connexion);

    $second = $this->postJson('/api/auth/login', [
        'email' => 'doublelogin@univ.test',
        'password' => 'Password123!',
    ]);

    $second->assertOk()
        ->assertJsonPath('utilisateur.email', 'doublelogin@univ.test')
        ->assertJsonPath('utilisateur.role', Role::ADMIN->value);
});

it('mauvais mot de passe retourne 401', function (): void {
    Utilisateur::query()->create([
        'email' => 'badpass@univ.test',
        'mot_de_passe_hash' => bcrypt('Password123!'),
        'role' => Role::ADMIN->value,
        'actif' => true,
    ]);

    $this->postJson('/api/auth/login', [
        'email' => 'badpass@univ.test',
        'password' => 'wrong-password',
    ])->assertStatus(401)
        ->assertJsonPath('message', 'Identifiants invalides.');
});

it('compte inactif renvoie 403', function (): void {
    Utilisateur::query()->create([
        'email' => 'inactive@univ.test',
        'mot_de_passe_hash' => bcrypt('Password123!'),
        'role' => Role::ADMIN->value,
        'actif' => false,
    ]);

    $this->postJson('/api/auth/login', [
        'email' => 'inactive@univ.test',
        'password' => 'Password123!',
    ])->assertStatus(403)
        ->assertJsonPath('message', 'Le compte est inactif.');
});

it('/auth/me sans jeton retourne 401', function (): void {
    $this->getJson('/api/auth/me')
        ->assertStatus(401);
});

it('un ETUDIANT appelant POST /api/utilisateurs reçoit 403', function (): void {
    $etudiant = Utilisateur::query()->create([
        'email' => 'etu@univ.test',
        'mot_de_passe_hash' => bcrypt('Password123!'),
        'role' => Role::ETUDIANT->value,
        'actif' => true,
    ]);

    Sanctum::actingAs($etudiant);

    $this->postJson('/api/utilisateurs', [
        'email' => 'new@univ.test',
        'password' => 'Password123!',
        'role' => Role::ADMIN->value,
    ])->assertStatus(403)
        ->assertJsonPath('message', 'Accès refusé.');
});

it('SCOLARITE créant un ADMIN reçoit 403', function (): void {
    $scolarite = Utilisateur::query()->create([
        'email' => 'sco@univ.test',
        'mot_de_passe_hash' => bcrypt('Password123!'),
        'role' => Role::SCOLARITE->value,
        'actif' => true,
    ]);

    Sanctum::actingAs($scolarite);

    $this->postJson('/api/utilisateurs', [
        'email' => 'admin2@univ.test',
        'password' => 'Password123!',
        'role' => Role::ADMIN->value,
    ])->assertStatus(403)
        ->assertJsonPath('message', 'Accès refusé.');
});

it('création d\'un étudiant par SCOLARITE réussie', function (): void {
    $scolarite = Utilisateur::query()->create([
        'email' => 'sco2@univ.test',
        'mot_de_passe_hash' => bcrypt('Password123!'),
        'role' => Role::SCOLARITE->value,
        'actif' => true,
    ]);

    Sanctum::actingAs($scolarite);

    $response = $this->postJson('/api/utilisateurs', [
        'email' => 'etudiant_new@univ.test',
        'password' => 'Password123!',
        'role' => Role::ETUDIANT->value,
        'matricule' => 'ET0002',
        'nom' => 'Lemoine',
        'prenom' => 'Sophie',
        'filiere_id' => $this->filiere->id,
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('message', 'Utilisateur créé avec succès.')
        ->assertJsonPath('utilisateur.email', 'etudiant_new@univ.test')
        ->assertJsonPath('profil.type', 'etudiant');

    $this->assertDatabaseHas('etudiant', ['matricule' => 'ET0002']);
});

it('email dupliqué renvoie 409', function (): void {
    $scolarite = Utilisateur::query()->create([
        'email' => 'sco3@univ.test',
        'mot_de_passe_hash' => bcrypt('Password123!'),
        'role' => Role::SCOLARITE->value,
        'actif' => true,
    ]);

    Sanctum::actingAs($scolarite);

    Utilisateur::query()->create([
        'email' => 'duplicate@univ.test',
        'mot_de_passe_hash' => bcrypt('Password123!'),
        'role' => Role::ETUDIANT->value,
        'actif' => true,
    ]);

    $this->postJson('/api/utilisateurs', [
        'email' => 'duplicate@univ.test',
        'password' => 'Password123!',
        'role' => Role::ETUDIANT->value,
        'matricule' => 'ET0003',
        'nom' => 'Dupont',
        'prenom' => 'Jean',
        'filiere_id' => $this->filiere->id,
    ])->assertStatus(409)
        ->assertJsonPath('message', 'Cet email est déjà utilisé.');
});

it('logout révoque le jeton', function (): void {
    $user = Utilisateur::query()->create([
        'email' => 'logout@univ.test',
        'mot_de_passe_hash' => bcrypt('Password123!'),
        'role' => Role::ADMIN->value,
        'actif' => true,
    ]);

    Sanctum::actingAs($user);

    $token = $user->createToken('auth-token')->plainTextToken;

    $this->withToken($token)->postJson('/api/auth/logout')
        ->assertOk()
        ->assertJsonPath('message', 'Déconnexion réussie.');

    $this->assertNull($user->fresh()->tokens()->first());
});

it('désactivation d\'un compte puis login refusé', function (): void {
    $user = Utilisateur::query()->create([
        'email' => 'disable@univ.test',
        'mot_de_passe_hash' => bcrypt('Password123!'),
        'role' => Role::ADMIN->value,
        'actif' => true,
    ]);

    $this->assertDatabaseHas('utilisateur', ['email' => 'disable@univ.test', 'actif' => true]);

    $user->update(['actif' => false]);

    $this->postJson('/api/auth/login', [
        'email' => 'disable@univ.test',
        'password' => 'Password123!',
    ])->assertStatus(403)
        ->assertJsonPath('message', 'Le compte est inactif.');
});
