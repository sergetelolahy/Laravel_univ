<?php

declare(strict_types=1);

namespace App\Application\Referentiel\Cours;

use App\Domain\Referentiel\Exceptions\RessourceIntrouvableException;
use App\Domain\Referentiel\Repositories\CoursRepositoryInterface;

final class ObtenirCours
{
    public function __construct(private readonly CoursRepositoryInterface $repository)
    {
    }

    public function execute(int $id): object
    {
        $cours = $this->repository->trouverParId($id);
        if ($cours === null) {
            throw new RessourceIntrouvableException('Cours', $id);
        }

        return $cours;
    }
}
