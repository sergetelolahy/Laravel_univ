<?php

use App\Http\Controllers\Api\AnneeUniversitaireController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CoursController;
use App\Http\Controllers\Api\EnseignantController;
use App\Http\Controllers\Api\EtudiantController;
use App\Http\Controllers\Api\FiliereController;
use App\Http\Controllers\Api\GroupeController;
use App\Http\Controllers\Api\InscriptionController;
use App\Http\Controllers\Api\SalleController;
use App\Http\Controllers\Api\SemestreController;
use App\Http\Controllers\Api\UtilisateurController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SeanceController;
use App\Http\Controllers\Api\EmploiDuTempsController;
use App\Http\Controllers\Api\SalleDisponibiliteController;
use App\Http\Controllers\Api\EvaluationController;
use App\Http\Controllers\Api\NoteController;
use App\Http\Controllers\Api\AbsenceController;
use App\Http\Controllers\Api\EtudiantConsultationController;
use App\Http\Controllers\Api\ValidationSemestreController;


Route::controller(AuthController::class)->prefix('auth')->group(function (): void {
    Route::post('/login', 'login')->middleware('throttle:20,1');
    Route::post('/logout', 'logout')->middleware(['auth:sanctum']);
    Route::get('/me', 'me')->middleware(['auth:sanctum']);
});

Route::controller(UtilisateurController::class)->prefix('utilisateurs')->group(function (): void {
    Route::post('/', 'store')->middleware(['auth:sanctum', 'role:ADMIN,SCOLARITE']);
    Route::get('/', 'index')->middleware(['auth:sanctum', 'role:ADMIN']);
    Route::patch('/{id}/desactiver', 'desactiver')->middleware(['auth:sanctum', 'role:ADMIN']);
});

Route::middleware(['auth:sanctum'])->group(function (): void {
    Route::controller(EtudiantController::class)->prefix('etudiants')->group(function (): void {
        Route::get('/me', 'me')->middleware('role:ETUDIANT');
        Route::get('/me/inscriptions', 'mesInscriptions')->middleware('role:ETUDIANT');
        Route::get('/', 'index')->middleware('role:ADMIN,SCOLARITE,ENSEIGNANT');
        Route::get('/{id}', 'show')->middleware('role:ADMIN,SCOLARITE,ENSEIGNANT');
        Route::put('/{id}', 'update')->middleware('role:ADMIN,SCOLARITE');
    });

    Route::controller(GroupeController::class)->prefix('groupes')->group(function (): void {
        Route::get('/', 'index')->middleware('role:ADMIN,SCOLARITE,ENSEIGNANT,ETUDIANT');
        Route::post('/', 'store')->middleware('role:ADMIN,SCOLARITE');
        Route::get('/{id}', 'show')->middleware('role:ADMIN,SCOLARITE,ENSEIGNANT,ETUDIANT');
        Route::put('/{id}', 'update')->middleware('role:ADMIN,SCOLARITE');
        Route::delete('/{id}', 'destroy')->middleware('role:ADMIN,SCOLARITE');
        Route::get('/{id}/etudiants', 'etudiants')->middleware('role:ADMIN,SCOLARITE,ENSEIGNANT,ETUDIANT');
        Route::post('/{id}/etudiants', 'ajouterEtudiants')->middleware('role:ADMIN,SCOLARITE');
        Route::delete('/{id}/etudiants/{etudiantId}', 'retirerEtudiant')->middleware('role:ADMIN,SCOLARITE');
    });

    Route::controller(InscriptionController::class)->prefix('inscriptions')->group(function (): void {
        Route::get('/', 'index')->middleware('role:ADMIN,SCOLARITE,ENSEIGNANT,ETUDIANT');
        Route::post('/', 'store')->middleware('role:ADMIN,SCOLARITE,ETUDIANT');
        Route::post('/lot', 'storeLot')->middleware('role:ADMIN,SCOLARITE');
        Route::delete('/{id}', 'destroy')->middleware('role:ADMIN,SCOLARITE');
    });

    Route::controller(FiliereController::class)->prefix('filieres')->group(function (): void {
        Route::get('/', 'index');
        Route::get('/{id}', 'show');
        Route::post('/', 'store')->middleware('role:ADMIN');
        Route::put('/{id}', 'update')->middleware('role:ADMIN');
        Route::delete('/{id}', 'destroy')->middleware('role:ADMIN');
    });

    Route::controller(AnneeUniversitaireController::class)->prefix('annees')->group(function (): void {
        Route::get('/', 'index');
        Route::get('/courante', 'courante');
        Route::get('/{id}', 'show');
        Route::post('/', 'store')->middleware('role:ADMIN');
        Route::put('/{id}', 'update')->middleware('role:ADMIN');
        Route::delete('/{id}', 'destroy')->middleware('role:ADMIN');
        Route::patch('/{id}/courante', 'setCourante')->middleware('role:ADMIN');
    });

    Route::controller(SemestreController::class)->prefix('semestres')->group(function (): void {
        Route::get('/', 'index');
        Route::get('/{id}', 'show');
        Route::post('/', 'store')->middleware('role:ADMIN');
        Route::put('/{id}', 'update')->middleware('role:ADMIN');
        Route::delete('/{id}', 'destroy')->middleware('role:ADMIN');
    });

    Route::controller(CoursController::class)->prefix('cours')->group(function (): void {
        Route::get('/', 'index');
        Route::get('/{id}', 'show');
        Route::post('/', 'store')->middleware('role:ADMIN');
        Route::put('/{id}', 'update')->middleware('role:ADMIN');
        Route::delete('/{id}', 'destroy')->middleware('role:ADMIN');
        Route::post('/{id}/enseignants', 'attachEnseignant')->middleware('role:ADMIN');
        Route::delete('/{id}/enseignants/{enseignantId}', 'detachEnseignant')->middleware('role:ADMIN');
        Route::get('/{id}/enseignants', 'enseignants')->middleware('role:ADMIN,SCOLARITE');
        Route::get('/{id}/inscrits', 'inscritsDuCours')->middleware('role:ADMIN,SCOLARITE,ENSEIGNANT');
    });

    Route::controller(SalleController::class)->prefix('salles')->group(function (): void {
        Route::get('/', 'index')->middleware('role:ADMIN,SCOLARITE,ENSEIGNANT');
        Route::get('/{id}', 'show')->middleware('role:ADMIN,SCOLARITE,ENSEIGNANT');
        Route::post('/', 'store')->middleware('role:ADMIN');
        Route::put('/{id}', 'update')->middleware('role:ADMIN');
        Route::delete('/{id}', 'destroy')->middleware('role:ADMIN');
    });

    Route::controller(EnseignantController::class)->prefix('enseignants')->group(function (): void {
        Route::get('/', 'index')->middleware('role:ADMIN,SCOLARITE');
    });

    Route::controller(SalleController::class)->prefix('salles')->group(function (): void {
        Route::get('/', 'index')
            ->middleware('role:ADMIN,SCOLARITE,ENSEIGNANT');

        Route::get('/{id}', 'show')
            ->whereNumber('id')
            ->middleware('role:ADMIN,SCOLARITE,ENSEIGNANT');

        Route::post('/', 'store')
            ->middleware('role:ADMIN');

        Route::put('/{id}', 'update')
            ->whereNumber('id')
            ->middleware('role:ADMIN');

        Route::delete('/{id}', 'destroy')
            ->whereNumber('id')
            ->middleware('role:ADMIN');
    });

    // Emploi du temps personnel d'un enseignant
    Route::get(
        '/enseignants/me/emploi-du-temps',
        [EmploiDuTempsController::class, 'enseignant']
    )->middleware('role:ENSEIGNANT');

    // Salles disponibles : placer cette route avant les routes /salles/{id}
    Route::get(
        '/salles/disponibles',
        [SalleDisponibiliteController::class, 'disponibles']
    )->middleware('role:ADMIN,SCOLARITE');

    // Emploi du temps personnel d'un étudiant
    Route::get(
        '/etudiants/me/emploi-du-temps',
        [EmploiDuTempsController::class, 'etudiant']
    )->middleware('role:ETUDIANT');

    Route::get(
    '/salles/{id}/occupation',
    [SalleDisponibiliteController::class, 'occupation']
    )->middleware('role:ADMIN,SCOLARITE');

    Route::middleware('auth:sanctum')->group(function () {
    Route::get('/evaluations', [EvaluationController::class, 'index'])
        ->middleware('role:ADMIN,SCOLARITE,ENSEIGNANT');

    Route::post('/evaluations', [EvaluationController::class, 'store'])
        ->middleware('role:ADMIN,SCOLARITE,ENSEIGNANT');

    Route::post('/evaluations/{evaluation}/notes', [NoteController::class, 'storeBatch'])
        ->middleware('role:ENSEIGNANT');

    Route::post('/seances/{seance}/absences', [AbsenceController::class, 'storeBatch'])
        ->middleware('role:ENSEIGNANT,SCOLARITE');
    });


        // Jour 6 : validation du semestre
        Route::middleware('auth:sanctum')->group(function (): void {
            Route::post(
                '/semestres/{semestre}/validation',
                [ValidationSemestreController::class, 'store']
            )->middleware('role:ADMIN,SCOLARITE');
        });

        // Jour 6 : consultation personnelle de l’étudiant
        Route::get(
            '/etudiant/mes-notes',
            [EtudiantConsultationController::class, 'notes']
        )->middleware('role:ETUDIANT');

        Route::get(
            '/etudiant/mes-moyennes',
            [EtudiantConsultationController::class, 'moyennes']
        )->middleware('role:ETUDIANT');

        Route::get(
            '/etudiant/mon-emploi-du-temps',
            [EtudiantConsultationController::class, 'emploiDuTemps']
        )->middleware('role:ETUDIANT');
});




