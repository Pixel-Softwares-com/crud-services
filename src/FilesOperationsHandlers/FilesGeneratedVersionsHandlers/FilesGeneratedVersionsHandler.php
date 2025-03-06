<?php

namespace CRUDServices\FilesOperationsHandlers\FilesGeneratedVersionsHandlers;

use CRUDServices\FilesOperationsHandlers\FilesGeneratedVersionsHandlers\FileVersionGeneratingStrategies\FileVersionGeneratingStrategy;
use CRUDServices\FilesOperationsHandlers\FilesGeneratedVersionsHandlers\FileVersionGeneratingStrategies\JPEGFileGeneratingStrategy;

class FilesGeneratedVersionsHandler
{
    protected ?FilesGeneratedVersionsHandler $instance = null;
    protected function __construct(){}


    public static function Singleton() : FilesGeneratedVersionsHandler
    {
        if(! static::$instance ) 
        {
            static::$instance = new static();
        }
        
        return static::$instance;
    }

    protected function filterExtenssion($extenssion) : ?string
    {
        if(!$extenssion && !is_string( $extenssion ) )
        {
            return null;
        }

        $extenssionDetails = explode("." , $extenssion);
        return end($extenssionDetails);
    }

    public function getFileVersionGeneratingStrategy($extenssion) : ?FileVersionGeneratingStrategy
    {
        $extenssion = $this->filterExtenssion($extenssion) ;
        return match($extenssion)
               { 
                   //the only supported extenssion copy strategy for now .. add the others later if there are
                   "jpg" => JPEGFileGeneratingStrategy::Singleton() ,
                   "jpeg" => JPEGFileGeneratingStrategy::Singleton() ,
                   default =>  null
               };
    }

    public function generateVersionForExtenssion(string $filePath , $extenssion )
    {
        $this->getFileVersionGeneratingStrategy($extenssion)?->generateVersion($filePath);
    }
  
    public function generateVersionForExtenssions(string $filePath , array $extenssions = [])
    {
        foreach($extenssions as $extenssion)
        {
            $this->generateVersionForExtenssion($filePath , $extenssion);
        }
    }

    public function getFileVersionDeletingStrategy($extenssion) : ?FileVersionGeneratingStrategy
    {
        $extenssion = $this->filterExtenssion($extenssion) ;
        return match($extenssion)
               { 
                   //the only supported extenssion copy strategy for now .. add the others later if there are
                   "jpg" => JPEGFileGeneratingStrategy::Singleton() ,
                   "jpeg" => JPEGFileGeneratingStrategy::Singleton() ,
                   default =>  null
               };
    }

    public function deleteFileVersion(string $filePath , $extenssion)
    {

    }
    public function deleteFileVersions(string $filePath , array $extenssions = [])
    {
        foreach($extenssions as $extenssion)
        {
            $this->deleteFileVersion($filePath , $extenssion);
        }
    }

}