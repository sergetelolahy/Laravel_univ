<?php

declare(strict_types=1);

namespace App\Domain\Referentiel\Exceptions;

use App\Domain\Referentiel\Exceptions\DomainException;

final class RessourceIntrouvableException extends DomainException
{
    public function __construct(string $ressource, int $id)
    {
        parent::__construct(sprintf('%s introuvable avec l\'id %d.', $ressource, $id));
    }
}
