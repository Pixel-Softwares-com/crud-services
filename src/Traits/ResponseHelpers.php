<?php

namespace CRUDServices\Traits;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

trait ResponseHelpers
{

    protected function isValidHttpCode($code) : bool
    {
        /**
         * code maybe a http valid code or not ... it is the code comes with the exception while throwing it
         */
        $code = intval($code);
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
            /** Once if the code is sql error code or any invalid http response code  ... a default code will be returned to end user */
            
            $code = $this->getOperationFailingResponseCode();
        }

        return Response::error($exception->getMessage() , $code , $dataToReturn);
    }

}
   