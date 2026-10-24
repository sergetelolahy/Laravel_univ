<?php

declare(strict_types=1);

namespace App\Application\Planning\SallesDisponibles;

use App\Domain\Planning\Entities\SalleDisponible;
use App\Domain\Planning\Exceptions\CreneauInvalideException;
use App\Domain\Planning\Repositories\SeanceRepository;

final class SallesDisponibles
{
    public function __construct(private readonly SeanceRepository $seances)
    {
    }

    /** @return SalleDisponible[] */
    public function execute(\DateTimeImmutable $debut, \DateTimeImmutable $fin, int $capaciteMin): array
    {
        if ($fin <= $debut) {
            throw new CreneauInvalideException('La fin doit être postérieure au début.');
        }

        return $this->seances->sallesDisponibles(
            $debut->format('Y-m-d H:i:s'),
            $fin->format('Y-m-d H:i:s'),
            $capaciteMin
        );
    }
}
