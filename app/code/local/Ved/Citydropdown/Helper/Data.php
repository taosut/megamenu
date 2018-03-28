<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    VED
 * @package     VED_Citydropdown
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @generator   http://www.mgt-commerce.com/kickstarter/ Mgt Kickstarter
 */

class Ved_Citydropdown_Helper_Data extends Mage_Core_Helper_Abstract
{
public function getCities($stateId)
    {
        $resource  =  Mage::getSingleton('core/resource');
        $readConnection =  $resource->getConnection('core_read');
        $tableName   = $resource->getTableName('directory_city');
        $query = "SELECT * FROM {$tableName} WHERE state_id ='{$stateId}'";
        $results =  $readConnection->fetchAll($query);
        $cities = array();
        if(count($results) > 0){
          foreach($results as $city){
              $cityId  = $city['city_id'];
              $cityName = $city['city_name'];
              $cities[$cityId]  = $cityName;
          }
        }
        return $cities;
    }
 
    public function getCitiesAsDropdown($selectedCity = '',$stateId)
    {
        $cities =  $this->getCities($stateId);
        $options =  '';
        if(count($cities) > 0){
            foreach($cities as $city){
               $isSelected = $selectedCity == $city ? ' selected="selected"' : null;
               $options .= '<option value="' . $city . '"' . $isSelected . '>' . $city . '</option>';
           }
        }   
        return $options;
    }
}