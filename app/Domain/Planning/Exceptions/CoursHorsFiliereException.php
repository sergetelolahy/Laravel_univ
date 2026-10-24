<?php

declare(strict_types=1);

namespace App\Domain\Planning\Exceptions;

final class CoursHorsFiliereException extends PlanningException
{
    public function __construct(string $message = "Ce cours n'appartient pas à la filière du groupe.")
    {
        parent::__construct($message);
    }

    public function statutHttp(): int
    {
        return 422;
    }
}
