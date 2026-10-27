<?php

namespace App\Exceptions;

use App\Helpers\Helpers;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\ValidationException as ValidationValidationException;
use LogicException;
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
    }

    public function render($request, Throwable $exception)
    {
        //error por defecto
        $code = 500;
        $message = 'Error desconocido. '.get_class($exception).': '.$exception->getMessage();
        $state = false;

        //errores del modelo
        if ($exception instanceof ModelNotFoundException)
        {
            $state = false;
            $code = 404;
            $message = 'Datos no encontrados.';
        }

        //Respuesta sin datos
        if ($exception instanceof LogicException)
        {
            $state = true;
            $code = 203;
            $message = $exception->getMessage();
        }

        //errores de validación en la base de datos
        if ($exception instanceof QueryException)
        {
            $state = false;
            $code = 500;
            $message = 'Error en la consulta. '.$exception->getMessage();
        }

        //errores de validación en la request
        if ($exception instanceof ValidationException)
        {
            $state = false;
            $code = 422;
            $message = $exception->errors();
        }
        return Helpers::BackResponse(null,$message, $code, $state);
    }
}
