<?php

namespace CRUDServices\Traits;

use Exception;

trait CRUDCustomisationGeneralHooks
{

    /**
    * Methods For Customizing Some Operations If There Is Need To That
    *
    */

    protected function doBeforeOperationStart(): void
    {
        return;
    }

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
