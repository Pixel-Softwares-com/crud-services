<?php

namespace CRUDServices\FilesOperationsHandlers\OldFilesDeletingHandler\Traits;

use CRUDServices\FilesOperationsHandlers\OldFilesDeletingHandler\OldFilesDeletingHandler;
use CRUDServices\OldFilesInfoManager\OldFilesInfoManager;

trait OldFilesInfoManagerMethods
{
    /**
     * @var array
     * Must Be Like :
     * [ "fileName" => "fileRelevantPath" ]
     */
    protected array $filesToDelete = [];

    protected ?OldFilesInfoManager $oldFilesInfoManager = null;
    protected function initOldFilesInfoManager() : OldFilesInfoManager
    {
        if(!$this->oldFilesInfoManager){$this->oldFilesInfoManager = new OldFilesInfoManager();}
        return $this->oldFilesInfoManager;
    }

    public function addOldFileToDeletingQueue(string $fileName , string $fileRelevantPath) : OldFilesDeletingHandler
    {
        $this->filesToDelete[$fileName] = $fileRelevantPath;
        return $this;
    }

    public function restartOldFilesHandler() : bool
    {
        $this->filesToDelete = [];
        return true;
    }

    protected function informOldFilesInfoManager() : bool
    { 
        $this->initOldFilesInfoManager();
        foreach ($this->filesToDelete as $fileName => $fileRelevantPath)
        {
            $this->oldFilesInfoManager->addOldFileInfo($fileName , $fileRelevantPath);
        }

        /** If Failed To Write Anything To Info JSON File ... There Is A Problem And Nothing To Do By job*/
        return $this->oldFilesInfoManager->SaveChanges();
    }

}
