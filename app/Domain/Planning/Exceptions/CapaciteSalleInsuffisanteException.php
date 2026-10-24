<?php

declare(strict_types=1);

namespace App\Domain\Planning\Exceptions;

final class CapaciteSalleInsuffisanteException extends PlanningException
{
    public function __construct(string $message = "La capacité de la salle est insuffisante pour l'effectif du groupe.")
    {
        parent::__construct($message);
    }

    public function statutHttp(): int
    {
        return 422;
    }
}
