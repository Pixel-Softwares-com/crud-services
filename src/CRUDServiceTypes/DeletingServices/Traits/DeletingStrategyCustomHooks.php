<?php

namespace  CRUDServices\CRUDServiceTypes\DeletingServices\Traits;
  
trait DeletingStrategyCustomHooks
{
    protected $callbackAfterDBTransactionStart = null;
    protected $callbackBeforeDBTransactionCommiting = null;
    /**
    * Methods For Customizing Some Operations If There Is Need To That
    *
    */
   
    protected function onAfterDbTransactionStart(): void
    {
        if(is_callable($this->callbackAfterDBTransactionStart))
        {
            call_user_func($this->callbackAfterDBTransactionStart );
        }
    }

    protected function onBeforeDbCommit(): void
    {
        if(is_callable($this->callbackBeforeDBTransactionCommiting))
        {
            call_user_func($this->callbackBeforeDBTransactionCommiting );
        }
    }
 
    public function callAfterDbTransactionStart(callable $callback) : void
    {
        $this->callbackAfterDBTransactionStart = $callback;
    }
 
    public function callBeforeDBTransactionCommiting(callable $callback) : void
    {
        $this->callbackBeforeDBTransactionCommiting = $callback;
    } 
   
}
