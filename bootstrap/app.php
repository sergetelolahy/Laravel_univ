<?php

use App\Domain\Identity\Exceptions\AccesRefuseException;
use App\Domain\Identity\Exceptions\CompteInactifException;
use App\Domain\Identity\Exceptions\EmailDejaUtiliseException;
use App\Domain\Identity\Exceptions\IdentifiantsInvalidesException;
use App\Domain\Identity\Exceptions\UtilisateurIntrouvableException;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (IdentifiantsInvalidesException $exception, Request $request): mixed {
            if ($request->is('api/*')) {
                return response()->json(['message' => $exception->getMessage()], 401);
            }

            return null;
        });

        $exceptions->render(function (CompteInactifException $exception, Request $request): mixed {
            if ($request->is('api/*')) {
                return response()->json(['message' => $exception->getMessage()], 403);
            }

            return null;
        });

        $exceptions->render(function (AccesRefuseException $exception, Request $request): mixed {
            if ($request->is('api/*')) {
                return response()->json(['message' => $exception->getMessage()], 403);
            }

            return null;
        });

        $exceptions->render(function (EmailDejaUtiliseException $exception, Request $request): mixed {
            if ($request->is('api/*')) {
                return response()->json(['message' => $exception->getMessage()], 409);
            }

            return null;
        });

        $exceptions->render(function (UtilisateurIntrouvableException $exception, Request $request): mixed {
            if ($request->is('api/*')) {
                return response()->json(['message' => $exception->getMessage()], 404);
            }

            return null;
        });
    })->create();
