<?php

declare(strict_types=1);

namespace App\Application\Referentiel\Filiere;

use App\Domain\Referentiel\Exceptions\RessourceIntrouvableException;
use App\Domain\Referentiel\Repositories\FiliereRepositoryInterface;

final class ObtenirFiliere
{
    public function __construct(private readonly FiliereRepositoryInterface $repository)
    {
    }

    public function execute(int $id): array
    {
        $filiere = $this->repository->trouverParId($id);
        if ($filiere === null) {
            throw new RessourceIntrouvableException('Filiere', $id);
        }

        return [
            'id' => $filiere->id,
            'code' => $filiere->code,
            'nom' => $filiere->nom,
        ];
    }
}
