<?php

declare(strict_types=1);

namespace App\Domain\Planning\Exceptions;

final class CreneauInvalideException extends PlanningException
{
    public function __construct(string $message = "Créneau invalide.")
    {
        parent::__construct($message);
    }

    public function statutHttp(): int
    {
        return 422;
    }
}
