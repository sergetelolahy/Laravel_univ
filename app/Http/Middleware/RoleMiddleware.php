<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\Identity\Enums\Role;
use App\Domain\Identity\Exceptions\AccesRefuseException;
use Closure;
use Illuminate\Http\Request;

final class RoleMiddleware
{
    /**
     * @param  array<string>  $roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        $utilisateur = $request->user();

        if ($utilisateur === null) {
            throw new AccesRefuseException();
        }

        $rolesAutorises = array_map(static fn (string $role): Role => Role::from($role), $roles);
        $roleUtilisateur = $utilisateur->role;

        if (! in_array($roleUtilisateur, $rolesAutorises, true)) {
            throw new AccesRefuseException();
        }

        return $next($request);
    }
}
