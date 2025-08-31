<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Handle API requests specifically
        $this->renderable(function (Throwable $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->handleApiException($e, $request);
            }
        });

        // Handle 404 errors globally for API routes
        $this->renderable(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Endpoint not found',
                    'error' => 'The requested API endpoint does not exist',
                    'status_code' => 404,
                ], 404);
            }
        });
    }

    /**
     * Handle API exceptions and return JSON responses.
     */
    protected function handleApiException(Throwable $e, Request $request)
    {
        if ($e instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
                'status_code' => 422,
            ], 422);
        }

        if ($e instanceof AuthenticationException) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'error' => 'Authentication required',
                'status_code' => 401,
            ], 401);
        }

        if ($e instanceof AuthorizationException) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'error' => 'You do not have permission to perform this action',
                'status_code' => 403,
            ], 403);
        }

        if ($e instanceof ModelNotFoundException) {
            // Check if it's a Property model not found
            if (str_contains($e->getMessage(), 'Property')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Property not found',
                    'error' => 'The requested property does not exist',
                    'status_code' => 404,
                ], 404);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Resource not found',
                'error' => 'The requested resource does not exist',
                'status_code' => 404,
            ], 404);
        }

        if ($e instanceof NotFoundHttpException) {
            // Check if it's a Property model not found from route binding
            if (str_contains($e->getMessage(), 'Property') || str_contains($e->getMessage(), 'No query results for model')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Property not found',
                    'error' => 'The requested property does not exist',
                    'status_code' => 404,
                ], 404);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Endpoint not found',
                'error' => 'The requested API endpoint does not exist',
                'status_code' => 404,
            ], 404);
        }

        if ($e instanceof MethodNotAllowedHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Method not allowed',
                'error' => 'The HTTP method is not supported for this endpoint',
                'status_code' => 405,
            ], 405);
        }

        if ($e instanceof QueryException) {
            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'error' => 'A database error occurred',
                'status_code' => 500,
            ], 500);
        }

        // Handle any other exceptions
        return response()->json([
            'success' => false,
            'message' => 'Server error',
            'error' => config('app.debug') ? $e->getMessage() : 'An unexpected error occurred',
            'status_code' => 500,
        ], 500);
    }
}
