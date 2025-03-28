<?php

namespace CRUDServices\CRUDComponents\CRUDRelationshipComponents;

class OwnedRelationshipComponent extends RelationshipComponent
{

    protected bool $foreignKeyMustBeAppned = false; 
    protected array $updatingConditionColumns = [];

    public static function create(string $relationshipName , string $foreignKeyName) : OwnedRelationshipComponent
    {
        return new static($relationshipName , $foreignKeyName);
    }

    /**
     * @param array $updatingConditionColumns
     * @return $this
     */
    public function setUpdatingConditionColumns(array $updatingConditionColumns): OwnedRelationshipComponent
    {
        $this->updatingConditionColumns = $updatingConditionColumns;
        return $this;
    }
 
    /**
     * @return array
     */
    public function getUpdatingConditionColumns(): array
    {
        if(empty($this->updatingConditionColumns))
        {
            return ["id"];
        }
        return $this->updatingConditionColumns;
    }
  
    public function appendForignKeyToRequestData() : self
    {
        $this->foreignKeyMustBeAppned = true;
        return $this;
    }
    
    public function DoesNeedForeignKeyRequestAppending() : bool
    {
        return $this->foreignKeyMustBeAppned;
    }

}
