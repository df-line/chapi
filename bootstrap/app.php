<?php

use App\Http\Middleware\DefaultHeaderAcceptJSON;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\Request;
use Symfony\Component\Routing\Exception\RouteNotFoundException;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        #health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware)
    {
        $middleware->api([DefaultHeaderAcceptJSON::class]);
        //$middleware->append(DefaultHeaderAcceptJSON::class);
    })
    ->withExceptions(function (Exceptions $exceptions)
    {
        $exceptions->render(function (Throwable $e, Request $request)
        {
            if ($request->is('api/*'))
            {
                // Default status code and error message
                $statusCode = 500;
                $errorMessage = 'Server Error';
                $errors = null;

                if ($e instanceof RouteNotFoundException)
                {
                    $statusCode = 404;
                    $errorMessage = 'Unknown endpoint or invalid method';
                }

                if ($e instanceof AuthorizationException)
                {
                    $statusCode = 403;
                    $errorMessage = 'This action is unauthorized.';
                }


                if ($e instanceof AuthenticationException)
                {
                    $statusCode = 401;
                    $errorMessage = 'Unauthorized';
                }

                if ($e instanceof ValidationException) {
                    $statusCode = 422;
                    $errorMessage = 'Data validation failure';
                    $errors = $e->errors();
                }

                // Handle specific exceptions to return the correct status code and message
                if ($e instanceof HttpException) {
                    $statusCode = $e->getStatusCode();
                    $errorMessage = $e->getMessage() ?: 'Request Error';
                }

                if ($e instanceof NotFoundHttpException) {
                    $statusCode = 404;
                    $errorMessage = 'Resource not found.';
                }

                $response = [
                    'error' => $errorMessage,
                ];

                // If there are specific validation errors, add them to the response
                if (!is_null($errors))
                    $response['errors'] = $errors;

                return new JsonResponse($response, $statusCode);
            }

            return false;
        });

    })->create();
