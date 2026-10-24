<?php

declare(strict_types=1);

namespace App\Domain\Planning\Exceptions;

final class ReferenceIntrouvableException extends PlanningException
{
    public function __construct(string $message = "Référence inexistante.")
    {
        parent::__construct($message);
    }

    public function statutHttp(): int
    {
        return 422;
    }
}
