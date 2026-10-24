<?php

declare(strict_types=1);

namespace App\Domain\Planning\Exceptions;

final class SeanceUtiliseeException extends PlanningException
{
    public function __construct(string $message = "Suppression impossible : des données dépendent de cette séance.")
    {
        parent::__construct($message);
    }

    public function statutHttp(): int
    {
        return 409;
    }
}
