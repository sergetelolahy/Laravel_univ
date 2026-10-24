<?php

declare(strict_types=1);

namespace App\Application\Planning;

final class Periode
{
    /**
     * Sans paramètres : la semaine en cours (lundi au dimanche).
     * Avec une seule borne : 7 jours autour de cette borne.
     *
     * @return array{0:string,1:string}
     */
    public static function resoudre(?string $du, ?string $au): array
    {
        if ($du === null && $au === null) {
            return [
                (new \DateTimeImmutable('monday this week'))->format('Y-m-d'),
                (new \DateTimeImmutable('sunday this week'))->format('Y-m-d'),
            ];
        }

        $du ??= (new \DateTimeImmutable((string) $au))->modify('-6 days')->format('Y-m-d');
        $au ??= (new \DateTimeImmutable((string) $du))->modify('+6 days')->format('Y-m-d');

        return [$du, $au];
    }
}
