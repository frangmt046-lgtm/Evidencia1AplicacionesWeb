<?php

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Activity 13: role-based access for the Administrative Dashboard
        $middleware->alias([
            'active' => \App\Http\Middleware\EnsureUserIsActive::class,
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
        ]);

        // Guests that open a protected page go to the login screen;
        // logged-in employees that open /login go to their dashboard.
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson()
        );


        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            if ($e->getPrevious() instanceof ModelNotFoundException) {
                $model = class_basename($e->getPrevious()->getModel());

                return response()->json(['message' => "{$model} not found."], 404);
            }

            return response()->json(['message' => 'Endpoint not found.'], 404);
        });
    })->create();
