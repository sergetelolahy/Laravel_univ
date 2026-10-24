<?php

declare(strict_types=1);

namespace App\Domain\Planning\Exceptions;

final class ElementIntrouvableException extends PlanningException
{
    public function __construct(string $message = "Élément introuvable.")
    {
        parent::__construct($message);
    }

    public function statutHttp(): int
    {
        return 404;
    }
}
