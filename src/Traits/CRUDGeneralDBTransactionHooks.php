<?php

namespace CRUDServices\Traits;

use Exception;

trait CRUDGeneralDBTransactionHooks
{

    /**
    * Methods For Customizing Some Operations If There Is Need To That
    *
    */
  
    protected function onAfterDbTransactionStart(): void
    {
        return;
    }
    
    protected function onBeforeDbCommit(): void
    {
        return;
    }
 

    //////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////
}
