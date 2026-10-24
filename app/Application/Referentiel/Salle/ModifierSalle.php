<?php

declare(strict_types=1);

namespace App\Application\Referentiel\Salle;

use App\Domain\Referentiel\Exceptions\ConflitException;
use App\Domain\Referentiel\Exceptions\DonneesInvalidesException;
use App\Domain\Referentiel\Exceptions\RessourceIntrouvableException;
use App\Domain\Referentiel\Repositories\SalleRepositoryInterface;

final class ModifierSalle
{
    public function __construct(private readonly SalleRepositoryInterface $repository)
    {
    }

    /**
     * @param array{nom?:string, capacite?:int} $donnees
     */
    public function execute(int $id, array $donnees): array
    {
        $salle = $this->repository->trouverParId($id);
        if ($salle === null) {
            throw new RessourceIntrouvableException('Salle', $id);
        }

        $nom = trim((string) ($donnees['nom'] ?? $salle->nom));
        $capacite = (int) ($donnees['capacite'] ?? $salle->capacite);

        if ($nom === '') {
            throw new DonneesInvalidesException('Le nom de la salle est obligatoire.');
        }

        if ($capacite <= 0) {
            throw new DonneesInvalidesException('La capacité de la salle doit être strictement positive.');
        }

        if ($this->repository->existeNom($nom, $id)) {
            throw new ConflitException('Une salle avec ce nom existe déjà.');
        }

        $updated = $this->repository->modifier($id, [
            'nom' => $nom,
            'capacite' => $capacite,
        ]);

        return [
            'id' => $updated->id,
            'nom' => $updated->nom,
            'capacite' => $updated->capacite,
        ];
    }
}
