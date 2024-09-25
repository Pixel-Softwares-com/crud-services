<?php

namespace CRUDServices\CRUDComponents\CRUDRelationshipComponents;

use Illuminate\Database\Eloquent\Relations\Pivot;

class   ParticipatingRelationshipComponent extends RelationshipComponent
{
    protected array $pivotColumns = [];
    protected bool $hasPivotColumns = false;
    protected ?String $pivotForignKeyName = null;


    public static function create(string $relationshipName , string $foreignKeyName = "" ) : ParticipatingRelationshipComponent
    {
        return new static($relationshipName  , $foreignKeyName);
    }

    public function hasPivotColumns() : bool
    {
        return $this->hasPivotColumns;
    }
    /**
     * @param array $pivotColumns
     * @return ParticipatingRelationshipComponent
     */
    public function setPivotColumns(array $pivotColumns): ParticipatingRelationshipComponent
    {
        $this->pivotColumns = $pivotColumns;
        $this->hasPivotColumns = true;
        return $this;
    }

    /**
     * @return array
     */
    public function getPivotColumns(): array
    {
        return $this->pivotColumns;
    }

    public function appendPivotForeignKeyToRequestData(string $pivotForignKeyName ) : self
    {
        $this->pivotForignKeyName = $pivotForignKeyName;
        return $this;
    }
    
    public function DoesNeedPivotForeignKeyRequestAppending() : bool
    {
        return (bool) $this->pivotForignKeyName;
    }


}
