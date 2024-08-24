<?php

namespace CRUDServices\CRUDServiceTypes\DataWriterCRUDServices\StoringServices;

use Exception;

abstract class MultiRowStoringService extends StoringService
{
    protected array $createdModels = [];

    /**
     * @return string
     * The key used to wrap all rows must be inserted in the same time at multiple storing operation
     * ex : "items" => [ [ 1.row keys] , [ 2.row keys ]  , ... etc. ]
     *
     * Override from child class when it is needed to change its value
     */
    protected function getMultiRowArrayWrappingKey() : string
    {
        return "items";
    }

    /**
     * @return void
     * getting the wrapped multi row data to start creating operation
     */
    protected function setWrappedDataArray() : void
    {
        $this->data = $this->data[ $this->getMultiRowArrayWrappingKey() ] ?? [];
    }
    protected function archiveCreatedSingleModel()
    {
        $this->createdModels[] =  $this->Model;
    }
    protected function customizeSingleModel() : void
    {
        return ;
    }

    /**
     * @return array
     */
    public function getCreatedModels(): array
    {
        return $this->createdModels;
    }
    /**
     * @param array $dataRow
     * @return StoringService
     * @throws Exception
     */
    protected function createConveniently(array $dataRow = []): StoringService
    {
        $this->setWrappedDataArray();
        foreach ($this->data as $row)
        {
            parent::createConveniently($row);
            $this->customizeSingleModel();
            $this->archiveCreatedSingleModel();
        }
        //If No Exception Is Thrown => The Given Rows Are Created
        return $this;
    }

}
