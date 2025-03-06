<?php

namespace  CRUDServices\CRUDServiceTypes\DeletingServices\Traits;
 
use CRUDServices\CRUDServiceTypes\DeletingServices\DeletingStrategies\DeletingStrategy;

trait DeletingServiceCustomHooks
{

    /**
    * Methods For Customizing Some Operations If There Is Need To That
    *
    */
    protected function doAfterOperationStart(DeletingStrategy $deletingStg): void
    {
        return ;
    }

    protected function doAfterDbTransactionStart(DeletingStrategy $deletingStg): void
    {
        return;
    }
    
    protected function doBeforeDbTransactionCommiting(DeletingStrategy $deletingStg): void
    {
        return;
    }
   
}
