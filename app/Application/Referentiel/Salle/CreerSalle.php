<?php

declare(strict_types=1);

namespace App\Application\Referentiel\Salle;

use App\Domain\Referentiel\Exceptions\ConflitException;
use App\Domain\Referentiel\Exceptions\DonneesInvalidesException;
use App\Domain\Referentiel\Repositories\SalleRepositoryInterface;

final class CreerSalle
{
    public function __construct(private readonly SalleRepositoryInterface $repository)
    {
    }

    /**
     * @param array{nom:string, capacite:int} $donnees
     */
    public function execute(array $donnees): array
    {
        $nom = trim((string) ($donnees['nom'] ?? ''));
        $capacite = (int) ($donnees['capacite'] ?? 0);

        if ($nom === '') {
            throw new DonneesInvalidesException('Le nom de la salle est obligatoire.');
        }

        if ($capacite <= 0) {
            throw new DonneesInvalidesException('La capacité de la salle doit être strictement positive.');
        }

        if ($this->repository->existeNom($nom)) {
            throw new ConflitException('Une salle avec ce nom existe déjà.');
        }

        $salle = $this->repository->creer([
            'nom' => $nom,
            'capacite' => $capacite,
        ]);

        return [
            'id' => $salle->id,
            'nom' => $salle->nom,
            'capacite' => $salle->capacite,
        ];
    }
}
