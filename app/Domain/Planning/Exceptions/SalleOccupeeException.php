<?php

declare(strict_types=1);

namespace App\Domain\Planning\Exceptions;

final class SalleOccupeeException extends PlanningException
{
    public function __construct(string $message = "Salle déjà occupée sur ce créneau.")
    {
        parent::__construct($message);
    }

    public function statutHttp(): int
    {
        return 409;
    }
}
