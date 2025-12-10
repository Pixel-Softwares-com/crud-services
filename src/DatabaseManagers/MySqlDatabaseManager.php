<?php

namespace CRUDServices\DatabaseManagers;


use Illuminate\Support\Facades\DB;
use Throwable;

class MySqlDatabaseManager
{
    protected static function returnBackForeignKeyChecks() : void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    protected static function stopForeignKeyChecks() : void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
    }

    protected static function executeWithoutForeignKeyChecks(callable $callback) : void
    {
        static::stopForeignKeyChecks();
        $callback();
        static::returnBackForeignKeyChecks();
    }

    protected static function truncateTable(string $tableName ) : void
    {
        DB::table($tableName)->truncate();
    }

    protected static function deleteAllFromTable(string $tableName) : void
    {
        DB::table($tableName)->delete();
    }

    protected static function deleteAllFromTableWithoutForeignKeyChecks(string $tableName) : void
    {
        static::executeWithoutForeignKeyChecks(function() use ($tableName)
        {
            static::deleteAllFromTable($tableName);
        });
    }

    public static function truncateDBTable(string $tableName) : void
    {
        static::tryInDBTransaction(function() use($tableName)
        {
            static::truncateTable($tableName);
        });
    }

    protected static function rollbackTransaction() : void
    {
        DB::rollback();
    }
    
    protected static function commitTransaction() : void
    {
        DB::commit();
    }

    protected static function beginTransaction() : void
    {
        DB::beginTransaction();
    }
    
    protected static function tryInDBTransaction(callable $callback) : mixed
    {
        try{
            
            static::beginTransaction();

            $result = $callback();

            static::commitTransaction();

            return $result;

        }catch(Throwable $exception)
        {
            static::rollbackTransaction();

            throw $exception;
        }

    }

    public static function deleteAllFromDBTable(string $tableName , bool $stopingForeignKeyChecks = true) : void
    {
        static::tryInDBTransaction(function() use($tableName , $stopingForeignKeyChecks)
        {
            if($stopingForeignKeyChecks)
            {
                static::deleteAllFromTableWithoutForeignKeyChecks($tableName);
                
            }else
            {
                static::deleteAllFromTable($tableName);
            }
        });
    }
}