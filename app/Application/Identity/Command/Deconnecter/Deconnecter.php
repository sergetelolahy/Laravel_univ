<?php

declare(strict_types=1);

namespace App\Application\Identity\Command\Deconnecter;

use App\Domain\Identity\Services\TokenService;

final class Deconnecter
{
    public function __construct(private readonly TokenService $tokenService)
    {
    }

    public function execute(int $utilisateurId): void
    {
        $this->tokenService->revoquerTous($utilisateurId);
        $this->tokenService->revoquerCourant();
    }
}
