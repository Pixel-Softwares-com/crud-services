<?php

namespace CRUDServices\FilesOperationsHandlers\FilesGeneratedVersionsHandlers\FileVersionDeletingStrategies;

abstract class FileVersionDeletingStrategy
{
    protected array $instances = [];
    protected function __construct(){}


    public static function Singleton() : FileVersionDeletingStrategy
    {
        if(! array_key_exists( static::class , static::$instances ))
        {
            static::$instances[ static::class ] = new static();
        }
        
        return static::$instances[ static::class ];
    }
    
    abstract public function deleteFileVersion(string $filePath);
}