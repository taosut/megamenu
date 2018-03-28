<?php

/**
 * Class Ved_AdminApi_Model_Resource_Region_Collection
 */
class Ved_AdminApi_Model_Resource_Region_Collection extends Mage_Directory_Model_Mysql4_Region_Collection
{
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
     * @param array|string $region
     * @return $this
     */
    public function addAreaToFilter($region)
    {
        if (!empty($region)) {
            $condition = is_array($region) ? array('in' => $region) : $region;
            $this->addFieldToFilter(array('main_table.code', 'main_table.area'), array($condition, $condition));
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getCodes()
    {
        $this->getSelect()->group('main_table.code');
        return $this->load()->toArray('code');
    }
}
