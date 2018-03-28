<?php
/**
 * Created by PhpStorm.
 * User: binh
 * Date: 3/19/2016
 * Time: 8:43 AM
 */

class Ved_Customer_Block_Address extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function getListCities(){
        $cities = array();
        $collection = Mage::getModel('directory/region')->getResourceCollection()->addCountryFilter('VN')->load();
        if(count($collection)>0){
            foreach($collection as $item){
                $data = $item->getData();
                $cities = $cities + array($data['region_id']=>$data['default_name']);
            }
        }
        return $cities;
    }
    public function getListDistricts($regionid){
        $districts = array();
        try{
            $resource  =  Mage::getSingleton('core/resource');
            $readConnection =  $resource->getConnection('core_read');
            $tableName   = $resource->getTableName('directory_city');
            $query = "SELECT * FROM {$tableName} WHERE region_id ='{$regionid}'";
            $results =  $readConnection->fetchAll($query);

            if(count($results) > 0){
                foreach ($results as $item) {
                    $districts = $districts + array($item['city_id']=>$item['name']);
                }
            }
        }catch(Exception $e) {
            //do nothing
        }
        return $districts;
    }
}