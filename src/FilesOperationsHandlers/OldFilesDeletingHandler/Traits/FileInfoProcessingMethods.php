<?php

namespace CRUDServices\FilesOperationsHandlers\OldFilesDeletingHandler\Traits;

trait FileInfoProcessingMethods
{
    protected function processFileInfoArrayRequiredValues(array $fileInfo) : array | null
    {
            $fileInfo = $this->setMultiUploadingValue($fileInfo);
            $fileInfo = $this->setModelPathPropNameValue($fileInfo);
            return  $this->setFolderName($fileInfo);
    }
}
