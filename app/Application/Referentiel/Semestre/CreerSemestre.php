<?php

declare(strict_types=1);

namespace App\Application\Referentiel\Semestre;

use App\Domain\Referentiel\Exceptions\ConflitException;
use App\Domain\Referentiel\Exceptions\DonneesInvalidesException;
use App\Domain\Referentiel\Repositories\SemestreRepositoryInterface;

final class CreerSemestre
{
    public function __construct(private readonly SemestreRepositoryInterface $repository)
    {
    }

    /**
     * @param array{filiere_id:int, numero:int} $donnees
     */
    public function execute(array $donnees): object
    {
        $filiereId = (int) ($donnees['filiere_id'] ?? 0);
        $numero = (int) ($donnees['numero'] ?? 0);

        if ($filiereId <= 0) {
            throw new DonneesInvalidesException('La filière du semestre est obligatoire.');
        }

        if ($numero < 1 || $numero > 10) {
            throw new DonneesInvalidesException('Le numéro de semestre doit être compris entre 1 et 10.');
        }

        if ($this->repository->existeNumeroPourFiliere($filiereId, $numero)) {
            throw new ConflitException('Ce numéro de semestre existe déjà pour cette filière.');
        }

        return $this->repository->creer([
            'filiere_id' => $filiereId,
            'numero' => $numero,
        ]);
    }
}
