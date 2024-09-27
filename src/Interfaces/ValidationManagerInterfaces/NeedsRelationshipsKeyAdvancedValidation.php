<?php

namespace CRUDServices\Interfaces\ValidationManagerInterfaces;

interface NeedsRelationshipsKeyAdvancedValidation
{
    /**
     * @return array
     * Must return a validation rules array for the relationship passed into the method
     * ["relationshipKey used in CRUDRelationship object" => rules array ]
     */
    public function getRelationshipKeyAdvancedValidationRules(string $relationshipName , array $data = [] ) : array;
}
