<?php

declare(strict_types=1);

namespace App\Application\Referentiel\Filiere;

use App\Domain\Referentiel\Exceptions\ConflitException;
use App\Domain\Referentiel\Exceptions\DonneesInvalidesException;
use App\Domain\Referentiel\Repositories\FiliereRepositoryInterface;

final class CreerFiliere
{
    public function __construct(private readonly FiliereRepositoryInterface $repository)
    {
    }

    /**
     * @param array{code:string, nom:string} $donnees
     */
    public function execute(array $donnees): array
    {
        $code = trim((string) ($donnees['code'] ?? ''));
        $nom = trim((string) ($donnees['nom'] ?? ''));

        if ($code === '' || $nom === '') {
            throw new DonneesInvalidesException('Le code et le nom de la filière sont obligatoires.');
        }

        if ($this->repository->existeCode($code)) {
            throw new ConflitException('Une filière avec ce code existe déjà.');
        }

        if ($this->repository->existeNom($nom)) {
            throw new ConflitException('Une filière avec ce nom existe déjà.');
        }

        $filiere = $this->repository->creer([
            'code' => $code,
            'nom' => $nom,
        ]);

        return [
            'id' => $filiere->id,
            'code' => $filiere->code,
            'nom' => $filiere->nom,
        ];
    }
}
