<?php

namespace CRUDServices\FilesOperationsHandlers\FilesGeneratedVersionsHandlers\FileVersionGeneratingStrategies;

abstract class FileVersionGeneratingStrategy
{
    protected array $instances = [];
    protected function __construct(){}


    public static function Singleton() : FileVersionGeneratingStrategy
    {
        if(! array_key_exists( static::class , static::$instances ))
        {
            static::$instances[ static::class ] = new static();
        }
        
        return static::$instances[ static::class ];
    }
    
    abstract public function generateVersion(string $filePath);
}