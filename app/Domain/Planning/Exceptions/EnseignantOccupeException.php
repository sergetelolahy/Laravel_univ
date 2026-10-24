<?php

declare(strict_types=1);

namespace App\Domain\Planning\Exceptions;

final class EnseignantOccupeException extends PlanningException
{
    public function __construct(string $message = "Enseignant déjà affecté à une autre séance sur ce créneau.")
    {
        parent::__construct($message);
    }

    public function statutHttp(): int
    {
        return 409;
    }
}
