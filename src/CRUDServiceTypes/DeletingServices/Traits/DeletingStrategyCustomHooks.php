<?php

namespace  CRUDServices\CRUDServiceTypes\DeletingServices\Traits;
  
trait DeletingStrategyCustomHooks
{
    protected $callbackAfterOperationStart = null;
    protected $callbackAfterDBTransactionStart = null;
    protected $callbackBeforeDBTransactionCommiting = null;
    /**
    * Methods For Customizing Some Operations If There Is Need To That
    *
    */
    protected function doAfterOperationStart(): void
    {
        if(is_callable($this->callbackAfterOperationStart))
        {
            call_user_func($this->callbackAfterOperationStart );
        }
    }
 
    protected function doAfterDbTransactionStart(): void
    {
        if(is_callable($this->callbackAfterDBTransactionStart))
        {
            call_user_func($this->callbackAfterDBTransactionStart );
        }
    }

    protected function doBeforeDbTransactionCommiting(): void
    {
        if(is_callable($this->callbackBeforeDBTransactionCommiting))
        {
            call_user_func($this->callbackBeforeDBTransactionCommiting );
        }
    }

    public function callAfterOperaionStart(callable $callback) : void
    {
        $this->callbackAfterOperationStart = $callback;
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
