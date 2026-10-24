<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Identity\Repositories\UtilisateurRepository;
use App\Domain\Identity\Services\PasswordHasher;
use App\Domain\Identity\Services\TokenService;
use App\Domain\Referentiel\Repositories\AnneeUniversitaireRepositoryInterface;
use App\Domain\Referentiel\Repositories\CoursRepositoryInterface;
use App\Domain\Referentiel\Repositories\FiliereRepositoryInterface;
use App\Domain\Referentiel\Repositories\SalleRepositoryInterface;
use App\Domain\Referentiel\Repositories\SemestreRepositoryInterface;
use App\Domain\Scolarite\Repositories\EnseignementRepository;
use App\Domain\Scolarite\Repositories\EtudiantRepository;
use App\Domain\Scolarite\Repositories\GroupeRepository;
use App\Domain\Scolarite\Repositories\InscriptionRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentAnneeUniversitaireRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentCoursRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentEnseignementRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentEtudiantRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentFiliereRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentGroupeRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentInscriptionRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentSalleRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentSemestreRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentUtilisateurRepository;
use App\Infrastructure\Security\LaravelPasswordHasher;
use App\Infrastructure\Security\SanctumTokenService;
use Illuminate\Support\ServiceProvider;
use App\Domain\Planning\Repositories\SeanceRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentSeanceRepository;
use App\Domain\Planning\Repositories\PlanningContexte;
use App\Infrastructure\Planning\SqlPlanningContexte;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UtilisateurRepository::class, EloquentUtilisateurRepository::class);
        $this->app->bind(PasswordHasher::class, LaravelPasswordHasher::class);
        $this->app->bind(TokenService::class, SanctumTokenService::class);

        $this->app->bind(FiliereRepositoryInterface::class, EloquentFiliereRepository::class);
        $this->app->bind(AnneeUniversitaireRepositoryInterface::class, EloquentAnneeUniversitaireRepository::class);
        $this->app->bind(SemestreRepositoryInterface::class, EloquentSemestreRepository::class);
        $this->app->bind(CoursRepositoryInterface::class, EloquentCoursRepository::class);
        $this->app->bind(SalleRepositoryInterface::class, EloquentSalleRepository::class);

        $this->app->bind(EtudiantRepository::class, EloquentEtudiantRepository::class);
        $this->app->bind(GroupeRepository::class, EloquentGroupeRepository::class);
        $this->app->bind(InscriptionRepository::class, EloquentInscriptionRepository::class);
        $this->app->bind(EnseignementRepository::class, EloquentEnseignementRepository::class);

        $this->app->bind(SeanceRepository::class, EloquentSeanceRepository::class);
        $this->app->bind(PlanningContexte::class,SqlPlanningContexte::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
