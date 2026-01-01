<?php

namespace CRUDServices\CRUDExceptionHandlers;

use Exception;

abstract class CRUDExceptionHandler
{
    /**
     * Handle the exception and return a JSON response
     *
     * @param Exception $exception
     * @return \Illuminate\Http\JsonResponse|null
     */
    abstract public static function handle(Exception $exception);

    /**
     * Check if the handler can handle the given exception
     *
     * @param Exception $exception
     * @return bool
     */
    abstract public static function canHandle(Exception $exception): bool;
}

