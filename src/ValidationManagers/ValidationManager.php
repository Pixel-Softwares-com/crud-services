<?php

namespace CRUDServices\ValidationManagers;

use CRUDServices\CRUDComponents\CRUDRelationshipComponents\RelationshipComponent;
use CRUDServices\Interfaces\ValidationManagerInterfaces\NeedsModelKeyAdvancedValidation;
use CRUDServices\Interfaces\ValidationManagerInterfaces\NeedsRelationshipsKeyAdvancedValidation;
use ValidatorLib\JSONValidator;
use ValidatorLib\Validator;
use ValidatorLib\CustomFormRequest\BaseFormRequest;
use Illuminate\Database\Eloquent\Model;
use Exception;

abstract class ValidationManager
{
    protected static array|null $instances = [];
    protected string $baseRequestFormClass = "";
    protected ?BaseFormRequest $requestForm = null;
    protected ?Validator $validator = null;

    protected function __construct()
    {
    }

    public static function Singleton() : ValidationManager
    {
        $ValidationManagerClass = static::class;
        if(array_key_exists($ValidationManagerClass , static::$instances))
        {
            return static::$instances[$ValidationManagerClass];
        }
        return static::$instances[$ValidationManagerClass] = new static();
    }

    /**
     * @param string $baseRequestFormClass
     * @return $this
     *
     * Must Be Used in the first use of the ValidationManager instance ... otherwise an exception will be thrown
     */
    public function setBaseRequestFormClass(string $baseRequestFormClass): self
    {
        $this->baseRequestFormClass = $baseRequestFormClass;
        return $this;
    }

    /**
     * @return Validator|null
     */
    public function getValidator(): ?Validator
    {
        return $this->validator;
    }

    /**
     * @param bool $newInstance
     * @return Validator
     * @throws Exception
     */
    protected function initValidator(bool $newInstance = false): Validator
    {
        if(!$this->validator || $newInstance)
        {
            /**
             * @var JSONValidator $this->validator
             *
             * IMPORTANT NOTE : If $this->requestForm is not set ... Validator will already throw an Exception
             *                  So you need always to set it at least in the first use of ValidationManager
             */
            $this->validator = new JSONValidator($this->baseRequestFormClass);
        }

        //return the validatior after passing the request form class
        return $this->validator->changeRequestClass($this->baseRequestFormClass);
        
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getRequestValidData(): array
    {
        return $this->initValidator()->getRequestData();
    }

    /**
     * @param array $data
     * @return $this
     * @throws Exception
     *
     * Use it when you need to validate a single row ... otherwise the ValidationManager will use the request data automatically
     */
    public function setValidatorData(array $data) : self
    {
        $this->initValidator()->setRequestData($data);
        return $this;
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function startGeneralValidation() : self
    {
        $this->initValidator()->applyBailRule()->validate();
        return $this;
    }

    /**
     * @param array $singleDataRow
     * @param array $keyValidationRules
     * @return void
     * @throws Exception
     */
    protected function validateSingleRowKeys(array $singleDataRow = [] , array $keyValidationRules = []) : void
    {
        if(!empty($keyValidationRules))
        {
            $this->validator->applyCustomRuleSet($keyValidationRules)->applyBailRule()->setRequestData($singleDataRow)->validate();
        }
    }

    /**
     * @param array $singleDataRow (Can be empty to allow the child class to adding some values if it is needed , ex : ignoring values in updating operation)
     * @return $this
     * @throws Exception
     */
    public function validateSingleModelRowKeys(array $singleDataRow = []) : self
    {
        /** Getting the object prop in Validator .... so no new object is initialized */
        $requestForm = $this->initValidator()->getRequestFormOb();

        if($requestForm instanceof NeedsModelKeyAdvancedValidation)
        {
            $keyValidationRules =  $requestForm->getModelKeyAdvancedValidationRules($singleDataRow);
            $this->validateSingleRowKeys($singleDataRow , $keyValidationRules);
        }
        return $this;
    }

    /**
     * @param NeedsRelationshipsKeyAdvancedValidation $requestForm
     * @param RelationshipComponent $relationship
     * @param array $singleDataRow
     * @return array
     */
    protected function getRelationshipKeyValidationRules(NeedsRelationshipsKeyAdvancedValidation $requestForm , RelationshipComponent | string $relationshipName, array $singleDataRow = []) : array
    {
        if($relationshipName instanceof RelationshipComponent)
        {
            $relationshipName = $relationshipName->getRelationshipName();
        }
        
        return $requestForm->getRelationshipKeyAdvancedValidationRules($relationshipName , $singleDataRow);
    }

    /**
     * @param RelationshipComponent $relationship
     * @param array $singleDataRow
     * @param Model|null $relationshipModel (this parameter is used in child class )
     * @return $this
     * @throws Exception
     */
    public function validateRelationshipSingleRowKeys(RelationshipComponent | string $relationshipName , array $singleDataRow = []  , ?Model $relationshipModel = null) : self
    {
        /** Getting the object prop in Validator .... so no new object is initialized */
        $requestForm = $this->initValidator()->getRequestFormOb();

        if($requestForm instanceof NeedsRelationshipsKeyAdvancedValidation)
        {
            $relationshipDBValidationRules = $this->getRelationshipKeyValidationRules($requestForm , $relationshipName , $singleDataRow) ;
            $this->validateSingleRowKeys($singleDataRow , $relationshipDBValidationRules);
        }
        return $this;
    }

}
