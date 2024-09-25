<?php

namespace CRUDServices\Traits;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

trait ResponseHelpers
{

    protected function isValidHttpCode(int $code) : bool
    {
        return $code >= 100 && $code < 512;
    }
    protected function getOperationFailingResponseCode() : int
    {
        return 500;
    }
    protected function errorRespondingHandling(?Exception $exception = null , array $dataToReturn = []) : JsonResponse
    { 
        $code = $exception->getCode();

        if(!$this->isValidHttpCode($code))
        {
            $code = $this->getOperationFailingResponseCode();
        }

        return Response::error($exception->getMessage() , $code , $dataToReturn);
    }

}
   