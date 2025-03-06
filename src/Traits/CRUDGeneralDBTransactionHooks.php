<?php

namespace CRUDServices\Traits;

use Exception;

trait CRUDGeneralDBTransactionHooks
{

    /**
    * Methods For Customizing Some Operations If There Is Need To That
    *
    */

    protected function doAfterOperationStart(): void
    {
        return;
    }

    protected function doAfterDbTransactionStart(): void
    {
        return;
    }
    
    protected function doBeforeDbTransactionCommiting(): void
    {
        return;
    }
 

    //////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////
}
