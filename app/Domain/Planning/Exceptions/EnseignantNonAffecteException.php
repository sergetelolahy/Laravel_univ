<?php

declare(strict_types=1);

namespace App\Domain\Planning\Exceptions;

final class EnseignantNonAffecteException extends PlanningException
{
    public function __construct(string $message = "Cet enseignant n'est pas affecté à ce cours pour l'année du groupe.")
    {
        parent::__construct($message);
    }

    public function statutHttp(): int
    {
        return 422;
    }
}
