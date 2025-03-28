<?php

namespace CRUDServices\FilesOperationsHandlers;

use CRUDServices\FilesOperationsHandlers\Traits\FileInfoProcessingGeneralMethods;
use CRUDServices\FilesOperationsHandlers\Traits\FilesInfoValidationGeneralMethods;
use CRUDServices\Interfaces\MustUploadModelFiles;
use Illuminate\Database\Eloquent\Model;

abstract class FilesHandler
{
    use FileInfoProcessingGeneralMethods , FilesInfoValidationGeneralMethods;

    protected null | MustUploadModelFiles | Model $model = null;

    protected function __construct(){ }
    abstract static public function singleton() : FilesHandler;

    /**
     * @param Model $model
     * @return $this
     */
    protected function setModel(Model $model) : FilesHandler
    {
        $this->model = $model ;
        return $this;
    }

    static public function MustUploadModelFiles(Model $model) : bool
    {
        return $model instanceof MustUploadModelFiles;
    }

    static public function isItUploadedFilePropName(MustUploadModelFiles $model , string $propName) : bool
    {
        $filesInfoArray = $model->getModelFileInfoArray();
        $filesInfoArray = array_filter($filesInfoArray , 'is_array');
        
        foreach($filesInfoArray as $filesInfoArray)
        {
            if(
                ($subArray["ModelPathPropName"] ?? null ) === $propName
                ||
                ($subArray["RequestKeyName"] ?? null ) === $propName
              )
            {
                return true;    
            } 
        }

        return false;
    }
}
