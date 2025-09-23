<?php

namespace App\Exceptions;

use Froiden\RestAPI\Exceptions\ApiException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\ValidationException;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Throwable;

class Handler extends ExceptionHandler
{

    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        ApiException::class
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        // Registers a callback to handle ApiException, returning a JSON response with a 403 status code.
        $this->renderable(function (ApiException $e, $request) {
            return response()->json($e, 403);
        });

        // Registers a callback to handle reportable exceptions, currently empty but allows for future reporting logic.
        $this->reportable(function (Throwable $e) {
            //
        });

        // Registers a callback to handle exceptions with a TokenMismatchException as the previous exception, redirecting to the login route.
        $this->renderable(function (\Exception $e) {
            if ($e->getPrevious() instanceof \Illuminate\Session\TokenMismatchException) {
                return redirect()->route('login');
            }
        });

        // Registers a callback to handle InvalidSignatureException, rendering a custom 'link-expired' error view with a 403 status code.
        $this->renderable(function (InvalidSignatureException $e) {
            return response()->view('errors.link-expired', [], 403);
        });
    }

    /**
     * Report or log an exception, integrating with Sentry if available and enabled.
     *
     * @param Throwable $exception The exception to report
     * @return void
     */
    public function report(Throwable $exception)
    {
        if (app()->bound('sentry') && $this->shouldReport($exception) && config('services.sentry.enabled')) {
            app('sentry')->captureException($exception);
        }

        parent::report($exception);
    }

    /**
     * Convert a validation exception into a JSON response with error details.
     *
     * @param \Illuminate\Http\Request $request The current HTTP request
     * @param \Illuminate\Validation\ValidationException $exception The validation exception
     * @return \Illuminate\Http\JsonResponse
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        return response()->json([
            'message' => __('validation.givenDataInvalid'),
            'errors' => $exception->errors(),
        ], $exception->status);
    }

    /**
     * Render an exception into an HTTP response, handling TokenMismatchException specifically.
     *
     * @param \Illuminate\Http\Request $request The current HTTP request
     * @param Throwable $exception The exception to render
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof TokenMismatchException) {
            return redirect(route('login'))->with('message', 'You page session expired. Please try again');
        }

        return parent::render($request, $exception);
    }

}