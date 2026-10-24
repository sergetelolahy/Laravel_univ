<?php

declare(strict_types=1);

namespace App\Domain\Referentiel\Exceptions;

use App\Domain\Referentiel\Exceptions\DomainException;

final class DonneesInvalidesException extends DomainException
{
    public function __construct(string $message = 'Données invalides.')
    {
        parent::__construct($message);
    }
}
