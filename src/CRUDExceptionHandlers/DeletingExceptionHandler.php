<?php

namespace CRUDServices\CRUDExceptionHandlers;

use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Throwable;

class DeletingExceptionHandler extends CRUDExceptionHandler
{
    /**
     * Handle QueryException related to deleting operations
     *
     * @param Exception $exception
     * @return \Illuminate\Http\JsonResponse|null
     */
    public static function handle(Exception $exception)
    {
        // Check if current exception or previous exception is a deleting error
        if (static::canHandle($exception))
        {
            return static::renderDeletingErrorResponse();
        }

        return null;
    }

    /**
     * Check if the handler can handle the given exception
     *
     * @param Exception $exception
     * @return bool
     */
    public static function canHandle(Exception $exception): bool
    {
        return static::isCurrentExceptionDeletingFailingException($exception)
               ||
               static::isPreviousExceptionDeletingFailingException($exception);
    }

    /**
     * Check if the current exception is a deleting failing exception
     *
     * @param Throwable $exception
     * @return bool
     */
    protected static function isCurrentExceptionDeletingFailingException(Throwable $exception): bool
    {
        return static::isItDeletingFailingException($exception);
    }

    /**
     * Check if the previous exception is a deleting failing exception
     *
     * @param Throwable $exception
     * @return bool
     */
    protected static function isPreviousExceptionDeletingFailingException(Throwable $exception): bool
    {
        if ($prevException = $exception->getPrevious())
        {
            return static::isItDeletingFailingException($prevException);
        }
        
        return false;
    }

    /**
     * Check if the exception is a deleting failing exception
     * This checks for foreign key constraint violations
     *
     * @param Throwable $exception
     * @return bool
     */
    protected static function isItDeletingFailingException(Throwable $exception): bool
    {
        $errorMessage = $exception->getMessage();

        return (static::isItQueryException($exception) && ($exception->getCode() == 23000 || $exception->getCode() == '23000')) 
                ||
                str_contains($errorMessage, 'SQLSTATE[23000]')
                ||
                str_contains($errorMessage, 'Integrity constraint violation')
                ||
                str_contains($errorMessage, 'foreign key constraint fails')
                ||
                str_contains($errorMessage, 'Cannot delete or update a parent row');
    }

    /**
     * Check if the exception is a QueryException
     *
     * @param Throwable $exception
     * @return bool
     */
    protected static function isItQueryException(Throwable $exception): bool
    {
        return $exception instanceof QueryException;
    }

    /**
     * Render JSON response for deleting errors using PixelResponseExtender
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected static function renderDeletingErrorResponse()
    {
        return Response::error(
            'This record is not available for deletion.',
            422
        );
    }
}
