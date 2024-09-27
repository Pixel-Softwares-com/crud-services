<?php

namespace CRUDServices\CRUDComponents\CRUDRelationshipComponents;

use Illuminate\Database\Eloquent\Relations\Pivot;

class   ParticipatingRelationshipComponent extends RelationshipComponent
{
    protected array $pivotColumns = [];
    //protected ?String $pivotForignKeyName = null;


    public static function create(string $relationshipName , string $foreignKeyName = "" ) : ParticipatingRelationshipComponent
    {
        return new static($relationshipName  , $foreignKeyName);
    }

    public function hasPivotColumns() : bool
    {
        return !empty($this->getPivotColumns());
    }
    /**
     * @param array $pivotColumns
     * @return ParticipatingRelationshipComponent
     */
    public function setPivotColumns(array $pivotColumns): ParticipatingRelationshipComponent
    {
        $this->pivotColumns = $pivotColumns;
        return $this;
    }

    /**
     * @return array
     */
    public function getPivotColumns(): array
    {
        return $this->pivotColumns;
    }

    // public function appendPivotForeignKeyToRequestData(string $pivotForignKeyName ) : self
    // {
    //     $this->pivotForignKeyName = $pivotForignKeyName;
    //     return $this;
    // }
    
    // public function DoesNeedPivotForeignKeyRequestAppending() : bool
    // {
    //     return (bool) $this->pivotForignKeyName;
    // }


}
