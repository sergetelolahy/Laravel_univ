<?php

declare(strict_types=1);

namespace App\Domain\Referentiel\Exceptions;

use App\Domain\Referentiel\Exceptions\DomainException;

final class ConflitException extends DomainException
{
    public function __construct(string $message = 'Conflit de données.')
    {
        parent::__construct($message);
    }
}
