<?php

declare(strict_types=1);

namespace App\Application\Identity\Query\ListerUtilisateurs;

use App\Domain\Identity\Enums\Role;
use App\Domain\Identity\Repositories\UtilisateurRepository;

final class ListerUtilisateurs
{
    public function __construct(private readonly UtilisateurRepository $utilisateurRepository)
    {
    }

    /**
     * @return array<int, mixed>
     */
    public function execute(?Role $role, int $page = 1, int $parPage = 20): array
    {
        return $this->utilisateurRepository->lister($role, $page, $parPage);
    }
}
