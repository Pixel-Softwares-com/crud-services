<?php

namespace CRUDServices\RelationshipsHandlers\RelationshipsHandlerTypes;

use CRUDServices\CRUDComponents\CRUDRelationshipComponents\OwnedRelationshipComponent;
use CRUDServices\CRUDComponents\CRUDRelationshipComponents\ParticipatingRelationshipComponent;
use CRUDServices\RelationshipsHandlers\RelationshipsHandler;
use Illuminate\Database\Eloquent\Model;
use Exception;

class RelationshipsCreationHandler extends RelationshipsHandler
{

    /**
     * @param Model $model
     * @param OwnedRelationshipComponent $relationship
     * @param array $relationshipMultipleRows
     * @return bool
     * @throws Exception
     */
    protected function OwnedRelationshipRowsChildClassHandling(Model $model , OwnedRelationshipComponent $relationship , array $relationshipMultipleRows  ): void
    {
        foreach ($relationshipMultipleRows as $row)
        {
            $RelationshipModelInstance = $this->getRelationshipModelInstance($model , $relationship->getRelationshipName());
            $this->validateRelationshipSingleRowKeys($row , $relationship ,$RelationshipModelInstance );

            $RelationshipModelInstance = $this->ModelFilesHandling($RelationshipModelInstance , $row);

            if( $RelationshipModelInstance->save() )
            {
                $this->HandleModelRelationships( $row ,  $RelationshipModelInstance);
            }
        } 
    }

    /**
     * @throws Exception
     */
    protected function ParticipatingRelationshipRowsChildClassHandling(Model $model , ParticipatingRelationshipComponent $relationship  , array $ParticipatingRelationshipFinalData ): void
    {
        if(!empty($ParticipatingRelationshipFinalData))
        {
            $model->{$relationship->getRelationshipName()}()->attach( $ParticipatingRelationshipFinalData );
        }
    }
}
