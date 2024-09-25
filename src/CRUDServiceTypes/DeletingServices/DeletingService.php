<?php

namespace CRUDServices\CRUDServiceTypes\DeletingServices;

use CRUDServices\CRUDService;
use CRUDServices\CRUDServiceTypes\DeletingServices\DeletingStrategies\DeletingStrategy;
use CRUDServices\CRUDServiceTypes\DeletingServices\DeletingStrategies\ForceDeletingStg;
use CRUDServices\CRUDServiceTypes\DeletingServices\DeletingStrategies\SoftDeletingStg;
use CRUDServices\CRUDServiceTypes\DeletingServices\Traits\DeletingServiceCustomHooks;
use CRUDServices\CRUDServiceTypes\DeletingServices\Traits\HelperTrait;
use CRUDServices\Traits\CRUDCustomisationGeneralHooks;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use Illuminate\Database\Eloquent\Model;

abstract class DeletingService extends CRUDService
{
    use CRUDCustomisationGeneralHooks , HelperTrait,   DeletingServiceCustomHooks;
    protected Collection  $modelsToDelete  ;

    protected bool $forcedDeletingOperation = true;
    protected ?DeletingStrategy $deletingStrategy  = null;

    abstract protected function getModelDeletingSuccessMessage() : string;


    public function __construct(Collection | Model | null $modelsToDelete)
    {
        parent::__construct();
        $this->setModelsToDelete($modelsToDelete);
    }

    /**
     * @param Model|Collection|null $modelsToDelete
     * @return $this
     */
    public function setModelsToDelete(Model|Collection|null $modelsToDelete): self
    {
        $this->modelsToDelete = $this->convertToCollection( $modelsToDelete );
        return $this;
    }

    protected function getNotDeletedArray() : array
    {
        return $this->initDeletingStg()->getNotDeleted() ;
    }

    protected function getDeletingFailedResponseMessage() : string
    {
        return "Failed to delete models have provided keys";
    }
    protected function getDeletingFailedResponseCode() : int
    {
        return 500;
    }
    protected function errorRespondingHandling(?Exception $e = null) : JsonResponse
    { 
        $code = $e->getCode();
        if($code == 0)
        {
            $code = $this->getDeletingFailedResponseCode();
        }

        return Response::error($e->getMessage() , $code , $this->getNotDeletedArray());
    }


    protected function initForceDeletingStg() : DeletingStrategy
    {
        return new ForceDeletingStg($this->modelsToDelete);
    }
    protected function initSoftDeletingStg() : DeletingStrategy
    {
        return new SoftDeletingStg( $this->modelsToDelete);
    }
    protected function initDeletingStg() : DeletingStrategy
    {
        if(!$this->deletingStrategy)
        {
            $this->deletingStrategy = $this->forcedDeletingOperation ? $this->initForceDeletingStg() : $this->initSoftDeletingStg();
        }
        return  $this->deletingStrategy;
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function DeleteConveniently() : void
    {
        $deletingStg = $this->initDeletingStg();

        /**
         * This will be false if some models are not deleted without development enviroment error or database constraints error
         * so need to show the end user the models not deleted
         */
        if(! $deletingStg->delete() )
        {
            throw new Exception( $this->getDeletingFailedResponseMessage() ); 
        }
    }

    protected function setForcedDeletingStatus(bool $forcedDeleted) : void
    {
        $this->forcedDeletingOperation = $forcedDeleted;
    }
    /**
     * @param bool $forcedDeleting
     * @return JsonResponse
     */
    public function delete(bool $forcedDeleting = true) : JsonResponse
    {
        try {
                $this->setForcedDeletingStatus($forcedDeleting);

                $this->doBeforeOperationStart();

                $this->DeleteConveniently();

                $this->doBeforeSuccessResponding();

                //Response After getting Success
                return Response::success($this->getSuccessResponseData() , [$this->getModelDeletingSuccessMessage()]);

        }catch (Exception $e)
        {
                $this->doBeforeErrorResponding($e);
                return $this->errorRespondingHandling($e);

        }
    }

}
