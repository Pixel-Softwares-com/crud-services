<?php

namespace CRUDServices\Traits;

use Exception;

trait CRUDGeneralRespondingHooks
{

    /**
    * Methods For Customizing Some Operations If There Is Need To That
    *
    */
 
    protected function doBeforeSuccessResponding() : void
    {
        return;
    }
    /**
     * @return array
     * For Overriding It From Child Services
     */
    protected function getSuccessResponseData() : array
    {
        return [];
    }
    protected function doBeforeErrorResponding(?Exception $e = null) : void
    {
        return;
    }

    //////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////
}
