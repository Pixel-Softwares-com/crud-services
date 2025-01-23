<?php

namespace CRUDServices\RelationshipsHandlers\Traits;

use CRUDServices\CRUDComponents\CRUDRelationshipComponents\ParticipatingRelationshipComponent;
use CRUDServices\Interfaces\ParticipatesToRelationships;
use CRUDServices\RelationshipsHandlers\RelationshipsHandler;
use Illuminate\Database\Eloquent\Model;
use Exception;

trait ParticipatingRelationshipMethods
{
    abstract protected function ParticipatingRelationshipRowsChildClassHandling(Model $model , ParticipatingRelationshipComponent $relationship ,array $ParticipatingRelationshipFinalData ) : void;


    protected function getParticipatingRelationshipForeignIDsIndexedArray(array $RelationshipRequestData ) : array
    {
        return array_filter($RelationshipRequestData, 'is_numeric');
    }

    protected function appendParsedParticipatingRelationshipRow(array $dataRow ,  ParticipatingRelationshipComponent $relationship , array $arrayToOverride = []) : array
    {
        $foreignColumnName = $relationship->getForeignKeyName() ;
        if(!array_key_exists($foreignColumnName , $dataRow)){return $arrayToOverride;}

        $foreignColumnValue = $dataRow[$foreignColumnName];
        $pivotColumnsValues = [];

        foreach ( $relationship->getPivotColumns() as $column)
        {
            $pivotColumnsValues[$column] = $dataRow[$column] ?? null;
        }

        $arrayToOverride[$foreignColumnValue] = $pivotColumnsValues;
        return  $arrayToOverride ;
    }

    /**
     * @throws Exception
     */
    protected function getParticipatingRelationshipForeignIDsAssocArray( ParticipatingRelationshipComponent $relationship , array $RelationshipRequestData  = []) : array
    {
        $rows = [];
        $RelationshipDataRows = $this->convertToMultipleArray( $RelationshipRequestData );
  
        foreach ($RelationshipDataRows as $row)
        {
            $this->validateRelationshipSingleRowKeys($row , $relationship);
            $rows = $this->appendParsedParticipatingRelationshipRow($row , $relationship  , $rows);
        }
        return $rows;
    }

    /**
     * @throws Exception
     */
    protected function getParticipatingRelationshipFinalData(array $dataRow , ParticipatingRelationshipComponent $relationship ) : array  
    {
        $RelationshipRequestData = $this->getRelationshipRequestDataArray($dataRow , $relationship->getRelationshipName());

        if(empty($RelationshipRequestData))
        {
            return [];
        }

        if(  $relationship->hasPivotColumns() )
        {
            return $this->getParticipatingRelationshipForeignIDsAssocArray($relationship , $RelationshipRequestData );
        }

        return $this->getParticipatingRelationshipForeignIDsIndexedArray($RelationshipRequestData);

    }

    /**
     * @param Model $model
     * @param ParticipatingRelationshipComponent $relationship
     * @param array $dataRow
     * @return RelationshipsHandler|ParticipatingRelationshipMethods
     * @throws Exception
     */
    protected function HandleParticipatingRelationshipRows( Model $model , ParticipatingRelationshipComponent $relationship , array $dataRow ) : self
    {
        if($this->doesRelationshipNeedHandling($dataRow , $relationship->getRelationshipName()))
        {
            /**
             * It only will be handled if its data sent with request 
             */
            $ParticipatingRelationshipFinalData = $this->getParticipatingRelationshipFinalData($dataRow , $relationship);
            $this->ParticipatingRelationshipRowsChildClassHandling($model , $relationship ,$ParticipatingRelationshipFinalData );
        }
        return $this;
    }

    protected function IsParticipatingRelationshipComponent($relationship) : bool
    {
        return $relationship instanceof ParticipatingRelationshipComponent;
    }
    /**
     * @param array $dataRow
     * @param Model $model
     * @return RelationshipsHandler|ParticipatingRelationshipMethods
     * @throws Exception
     */
    protected function HandleModelParticipatingRelationships(array $dataRow , Model $model) : self
    {
        if(!$this::DoesItParticipateToRelationships($model) ) { return $this;}

        /**@var Model | ParticipatesToRelationships $model*/
        foreach ($model->getParticipatingRelationships() as $relationship)
        {
            if($this->IsParticipatingRelationshipComponent($relationship) )
            {
                $this->HandleParticipatingRelationshipRows($model, $relationship, $dataRow);
            }
        }
        return $this;
    }


}
