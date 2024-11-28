<?php

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => 'false',
                    'message' => 'Route/Resource Not found',
                    'data' => null,
                    'errors' => null,
                ], 404);
            }
        });
        $exceptions->render(function (RouteNotFoundException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => 'false',
                    'message' => 'Unauthenticated Request!',
                    'data' => null,
                    'errors' => null,
                ], 404);
            }
        });
        // Handle Validation Errors (422)
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => 'false',
                    'message' => 'Validation Error',
                    'data' => null,
                    'errors' => $e->errors(), // This will return the validation errors
                ], 422);
            }
        });
        // Model Not Found Exception
        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => 'false',
                    'message' => 'Resource Not Found',
                    'data' => null,
                    'errors' => null,
                ], 404);
            }
        });

    })->create();
