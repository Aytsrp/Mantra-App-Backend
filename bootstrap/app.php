<?php

use App\Exceptions\ApiException;
use App\Http\Middleware\ForceJsonResponse;
use App\Support\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [ForceJsonResponse::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(fn (Request $r) => $r->is('api/*') || $r->expectsJson());
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return match (true) {
                $e instanceof ApiException => ApiResponse::error($e->errorCode, $e->getMessage(), $e->status, $e->fields),
                $e instanceof ValidationException => ApiResponse::error('VALIDATION_ERROR', 'Data tidak valid', 422, $e->errors()),
                $e instanceof AuthenticationException => ApiResponse::error('UNAUTHENTICATED', 'Sesi tidak valid, silakan masuk kembali', 401),
                $e instanceof ModelNotFoundException,
                $e instanceof NotFoundHttpException => ApiResponse::error('NOT_FOUND', 'Data tidak ditemukan', 404),
                default => ApiResponse::error('SERVER_ERROR', config('app.debug') ? $e->getMessage() : 'Terjadi kesalahan pada server', 500),
            };
        });
    })->create();