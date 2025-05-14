<?php

namespace CRUDServices\RelationshipsHandlers\RelationshipsHandlerTypes;

use CRUDServices\CRUDComponents\CRUDRelationshipComponents\OwnedRelationshipComponent;
use CRUDServices\CRUDComponents\CRUDRelationshipComponents\ParticipatingRelationshipComponent;
use CRUDServices\FilesOperationsHandlers\FilesHandler;
use CRUDServices\Helpers\Helpers;
use CRUDServices\RelationshipsHandlers\RelationshipsHandler;
use Exception;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class RelationshipsUpdatingHandler extends RelationshipsHandler
{
    protected string $primaryColumnName = "";
    protected string $ModelClass = "";

    /**
     * @var array
     * This Array Will Be Like : ["model" => $model  , "data" => ModelNewDataArray]
     */
    protected array $UpdatableModelDataMap = [];

    /**
     * @var array
     * This Array Will Be Like : [ ModelId => $model ]
     * To Use Array Keys In One Deleting Query Statement , And Use Model To Check If There Is Need To Delete Its Files
     */
    protected array $DeletableModelsMap = [];
    protected array $updatableModelsData = [];
    protected array $NewModelsData = [];
    protected array $updatingConditionColumns = [];
    protected  Collection | EloquentCollection | null $modelsCollection = null;

    /**
     * @return bool
     */
    protected function prepareModelOldFilesToDelete() : bool
    {
        /** If Model Doesn't Have Any File .... Nothing To Be Deleted After Deleting The Model*/
        if(!FilesHandler::MustUploadModelFiles( Arr::first($this->DeletableModelsMap) )){return true;}
        $this->initOldFilesDeletingHandler();

        foreach ($this->DeletableModelsMap as $model)
        {
            $this->oldFilesDeletingHandler->prepareModelOldFilesToDelete($model);
        }
        return true;
    }

    /**
     * @param Model $model
     * @return void
     */
    protected function setPrimaryColumnName(Model $model): void
    {
        $this->primaryColumnName = $model->getKeyName();
    }

    /**
     * @return bool
     * 
     * once it return false .... there is no data in database ... so no need to delete any thing
     */
    protected function setModelClass(): bool
    {
        if(!$this->modelsCollection){return false;}

        $model = $this->modelsCollection->first();
        $this->ModelClass = get_class($model);
        $this->setPrimaryColumnName($model);
        return true;
    }

    /**
     * @throws Exception
     */
    protected function deletedModelsHandling() : void
    {
        $deletedModelKeyValues = array_keys($this->DeletableModelsMap);
        
        /**
         * - the condition only will be applied once deleting needed data are found + the model class is ready to use in deleting query  .
         * - once there is a problem in setting model class ==> it will be because there is no data in database need to be deleted 
         */
        if(!empty($deletedModelKeyValues) && $this->setModelClass())
        {

            /** We Need To Get Old File's paths from the models before deleting them */
            $this->prepareModelOldFilesToDelete();

            if(! $this->ModelClass::whereIn($this->primaryColumnName , $deletedModelKeyValues)->delete() )
            {
                Helpers::throwException( "Failed To Delete old Models");
            }
        }
    }

    /**
     * @param Model $model
     * @param array $data
     * @param OwnedRelationshipComponent $relationship
     * @return bool
     * @throws Exception
     */
    protected function updateSingleModel(Model $model ,  array $data , OwnedRelationshipComponent $relationship ) : void
    {
        $this->validateRelationshipSingleRowKeys($data ,$relationship , $model);

        $model = $this->ModelFilesHandling($model , $data);
        if(!$model->save())
        {
            Helpers::throwException( "Failed To Update Relationship Entry !");
        }
        $this->HandleModelRelationships( $data ,  $model);
    }

    /**
     * @param array $UpdatableModelDataMap
     * $UpdatableModelDataMap : is A Static Array Because There is A Recursion When There Is Need To Update The Model's Relationships
     * And All Model Maps And Data 's Arrays Will Be Empty Every "OwnedRelationshipRowsChildClassHandling" 's method Calling
     * @param OwnedRelationshipComponent $relationship
     * @return bool
     * @throws Exception
     */
    protected function startToUpdateModels(array $UpdatableModelDataMap , OwnedRelationshipComponent $relationship): void
    {
        foreach ($UpdatableModelDataMap as $ModelInfoArray)
        {
            if(!empty($ModelInfoArray["data"]))
            {
                $this->updateSingleModel( $ModelInfoArray["model"] ,  $ModelInfoArray["data"] , $relationship);
            }
        }
    }

    protected function prepareNewModelsToCreation(Model $parentModel , string $relationship) : self
    {
        foreach ($this->NewModelsData as $row)
        {
            $this->addToUpdatableModelDataMap($this->getRelationshipModelInstance($parentModel , $relationship) , $row);
        }
        $this->NewModelsData = [];
        return $this;
    } 
    
    protected function addToDeletableModelsMap( Model $model)
    {
        $this->DeletableModelsMap[ $model->getKey() ] = $model;
    }

    protected function unsetUpdatableDataRow(int $rowIndex) : void
    {
        unset($this->updatableModelsData[$rowIndex]);
    }
    protected function addToUpdatableModelDataMap(Model $model , array $dataRow)
    {
        $this->UpdatableModelDataMap[] = ["model" => $model , "data" => $dataRow];
    }

    protected function checkModelColumnValue(Model $model , array $dataRow , string $column) : bool
    {
        return $model->{$column} == $dataRow[$column];
    }

    protected function addToModelMaps( Model $model) : void
    {
        foreach ($this->updatableModelsData as $index => $row)
        {
            $modelStatus = true;
            foreach ($this->updatingConditionColumns as $column)
            {
                $modelStatus &= $this->checkModelColumnValue($model , $row ,$column);
            }
            /**
             * If Model Status Is True ... That Means The Model Has Data To Be Updated
             */
            if($modelStatus)
            {
                $this->addToUpdatableModelDataMap(  $model , $row);
                $this->unsetUpdatableDataRow($index); // it is binded to its own model ... no need to loop on it for the next models 
                return;
            }
        }
        /** Otherwise , The Model Is Requested To Be Deleted */
        $this->addToDeletableModelsMap( $model );
    }

    /**
     * @return $this
     *
     * In This Method : All Of Collection's Models Will Be Checked :
     * If Any Model Has Data With Its updatingConditionColumns column's values ... Model Will Be Updated Later
     * ELSE : Model Will Be Deleted Later
     *
     */
    protected function CollectionModelsClassification() : self
    {

        $this->modelsCollection?->each(function( $model)
        {
            $this->addToModelMaps($model);
        });

        return $this;
    }

    protected function setRelationshipDataCollection( Model $parentModel , string $relationship ) : self
    {
        $modelOrCollection = $parentModel->{$relationship};
        if(!$modelOrCollection){return $this;}

        if($modelOrCollection instanceof Model)
        {
            $modelOrCollection  =  collect()->add($modelOrCollection);
        }
        $this->modelsCollection = $modelOrCollection->count() > 0 ? $modelOrCollection : null;
        return $this;
    }

    protected function setUpdatingConditionColumns(OwnedRelationshipComponent $relationship)
    {
        $this->updatingConditionColumns = $relationship->getUpdatingConditionColumns();
    }

    protected function classifyDataRowAsUpdatableModelData(array $dataRow) : void
    {
        $this->updatableModelsData[] = $dataRow;
    }

    protected function classifyDataRowAsNewData(array $dataRow) : void
    {
        if(!empty($dataRow))
        {
            $this->NewModelsData[] = $dataRow;
        }
    }

    protected function classifyDataRowsAsNewData(array $dataRows) : void
    {
        foreach($dataRows as $dataRow)
        {
            $this->classifyDataRowAsNewData($dataRow);
        }
    }

    protected function DataRowsArrayClassification(array $relationshipMultipleRows , OwnedRelationshipComponent $relationship )  : self
    {
        //no relation models is found in database , and there is a data comes to be inserted to databse
        // => so we need to handle the data rows as a new data
        if(!$this->modelsCollection && !empty($relationshipMultipleRows))
        {
            $this->classifyDataRowsAsNewData($relationshipMultipleRows);
            return $this;
        }
 
        /**
         * at this point of execution :
         * - if the $modelsCollection is null => $relationshipMultipleRows is an empty array => no data row will be classified
         * - if the $modelsCollection is not null and the $relationshipMultipleRows is an empty array 
         * => no data row will be classified
         * 
         * result : the data rows will be classified only if there is a data && there are relationship models comes from DB
        */
        $this->setUpdatingConditionColumns($relationship);

        foreach ($relationshipMultipleRows as  $row)
        {
            /**
             * at this point of execution :
             * there are relationship models comes from DB + there are data comes from request
             * we need to separate the updatable data rows and mark the others as a new data 
             * result : if any data row doesn't matter the condition bellow ... it is a new data row
             */
            if(Arr::has($row , $this->updatingConditionColumns))
            {
                $this->classifyDataRowAsUpdatableModelData($row);
                continue;
            }
 
            $this->classifyDataRowAsNewData($row); 
        } 

        return $this;
    }

    /**
     * @param Model $model
     * @param OwnedRelationshipComponent $relationship
     * @param array $relationshipMultipleRows
     * @return bool
     * @throws Exception
     */
    protected function OwnedRelationshipRowsChildClassHandling(Model $model, OwnedRelationshipComponent $relationship , array $relationshipMultipleRows   ) : void
    {
        $this->restartUpdater();

        //setting relationship models
        $this->setRelationshipDataCollection($model , $relationship->getRelationshipName() );

        $this->DataRowsArrayClassification($relationshipMultipleRows , $relationship )
             ->CollectionModelsClassification()        
             ->prepareNewModelsToCreation($model, $relationship->getRelationshipName() );
        $this->deletedModelsHandling();
        $this->startToUpdateModels($this->UpdatableModelDataMap , $relationship);
    }

    protected function restartUpdater() : void
    {
        $this->UpdatableModelDataMap = [];
        $this->updatableModelsData = [];
        $this->DeletableModelsMap = [];
    }

    /**
     * @param Model $model
     * @param ParticipatingRelationshipComponent $relationship
     * @param array $ParticipatingRelationshipFinalData
     * @throws Exception
     */
    protected function ParticipatingRelationshipRowsChildClassHandling(Model $model, ParticipatingRelationshipComponent $relationship , array $ParticipatingRelationshipFinalData): void
    {
        $model->{$relationship->getRelationshipName()}()->sync( $ParticipatingRelationshipFinalData );
    }
}
