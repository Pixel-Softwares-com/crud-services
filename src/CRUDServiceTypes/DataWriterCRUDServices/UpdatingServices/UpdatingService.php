<?php

namespace CRUDServices\CRUDServiceTypes\DataWriterCRUDServices\UpdatingServices;


use CRUDServices\CRUDServiceTypes\DataWriterCRUDServices\DataWriterCRUDService;
use CRUDServices\CRUDServiceTypes\DataWriterCRUDServices\UpdatingServices\Traits\RelationshipsUpdatingMethods;
use CRUDServices\Helpers\Helpers;
use CRUDServices\Traits\CRUDGeneralDBTransactionHooks;
use CRUDServices\ValidationManagers\ManagerTypes\UpdatingValidationManager;
use CRUDServices\ValidationManagers\ValidationManager;
use Illuminate\Database\Eloquent\Model;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

abstract class UpdatingService extends DataWriterCRUDService
{
    use RelationshipsUpdatingMethods , CRUDGeneralDBTransactionHooks;

    abstract protected function getRequestClass() : string;
    abstract protected function getModelUpdatingFailingErrorMessage() : string;
    abstract protected function getModelUpdatingSuccessMessage() : string;

    protected function getValidationManager(): ValidationManager
    {
        return UpdatingValidationManager::Singleton()->setModel($this->Model);
    }

    public function __construct(Model $Model)
    {
        $this->Model = $Model;
        parent::__construct();
    }

    /**
     * @return DataWriterCRUDService
     * @throws Exception
     */
    protected function updateModel() : DataWriterCRUDService
    {
        $this->validateModelRowKeys($this->data);

        $this->Model = $this->prepareModelFilesToUpload($this->data, $this->Model);

        $this->doBeforeSavingCurrentModelProps($this->data);

        if(!$this->Model->save())
        {
            Helpers::throwException( $this->getModelUpdatingFailingErrorMessage());
        }
        return $this->HandleModelRelationships($this->data , $this->Model);
    }

    /**
     * @return JsonResponse
     */
    public function update() : JsonResponse
    {
        try {
            $this->doBeforeValidation();
            $this->startGeneralValidation()->setRequestValidData();

            DB::beginTransaction();
            
            $this->doAfterOperationStart();
            $this->doAfterDbTransactionStart();

            $this->updateModel();

            /**
             * Files Handling Before DB transaction's committing
             * to avoid committing if files uploading or deleting failed
             */
            $this->uploadFiles();
            $this->deleteOldFiles();

            $this->doBeforeDbTransactionCommiting();

            //If No Exception Is Thrown From Previous Operations ... All Thing Is OK
            //So Database Transaction Will Be Commit
            DB::commit();

            $this->doBeforeSuccessResponding();
            //Response After getting Success
            return Response::success($this->getSuccessResponseData() , [$this->getModelUpdatingSuccessMessage() ] );
        }catch (Exception $exception)
        {
            //When An Exception Is Thrown ....  Database Transaction Will Be Rollback
            DB::rollBack();

            $this->doBeforeErrorResponding($exception);

            //Response The Error Messages By Exception Messages
            return $this->errorRespondingHandling($exception);
        }
    }

}
