<?php

declare(strict_types=1);

namespace App\Domain\Planning\Exceptions;

final class GroupeOccupeException extends PlanningException
{
    public function __construct(string $message = "Ce groupe a déjà une séance sur ce créneau.")
    {
        parent::__construct($message);
    }

    public function statutHttp(): int
    {
        return 409;
    }
}
