<?php

class Ved_AdminApi_Model_Resource_Store_Collection extends Mage_Core_Model_Mysql4_Store_Collection
{
    /**
     * @param array $codes
     * @return $this
     */
    public function addCodeToFilter($codes)
    {
        return $this->addFieldToFilter('main_table.code', array('in' => $codes));
    }

    /**
     * @param array|string $arrRequiredFields
     * @return array
     */
    public function toArray($arrRequiredFields)
    {
        if (is_array($arrRequiredFields))
            return parent::toArray($arrRequiredFields());
        else {
            $result = [];
            foreach ($this as $item)
                $result[] = $item[$arrRequiredFields];
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getStoreIds()
    {
        return $this->load()->toArray('store_id');
    }
}