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
class Ved_Citydropdown_IndexController extends Mage_Core_Controller_Front_Action
{
    public function testAction()
    {
        $helper          = Ved::helper('citydropdown');
		echo $helper;
		//return $data.getCities(1);
    }	
	
	private function getCities($stateId)
	{
		$cities = array();
		try{
			//var_dump($stateId);
			$resource  =  Mage::getSingleton('core/resource');
			$readConnection =  $resource->getConnection('core_read');
			$tableName   = $resource->getTableName('directory_city');
			$query = "SELECT * FROM {$tableName} WHERE region_id ='{$stateId}'";
			$results =  $readConnection->fetchAll($query);
			
			if(count($results) > 0){
			  foreach($results as $city){
				  $cityId  = $city['city_id'];
				  $cityName = $city['name'];
				  $cities[$cityId]  = $cityName;
			  }
			}
		}catch(Exception $e) {
			//do nothing
		}
		
		//var_dump($cities);
		return $cities;
	}
	
	
	public function getCitiesAsDropdownAction()
    {
      	$stateId= $this->getRequest()->getParam('stateId');
		$selectedCity= $this->getRequest()->getParam('defaultCity');
        if(isset($stateId)){
            $cities =  $this->getCities($stateId);
            $options =  '';
            if(count($cities) > 0){
                foreach($cities as $city){
                    $isSelected = $selectedCity == $city ? ' selected="selected"' : null;
                    $options .= '<option value="' . $city . '"' . $isSelected . '>' . $city . '</option>';
                }
            }
            echo $options;
            die();
        }
        echo '';
    }
	
	public function getCitiesAction($stateId)
	{
		$cities = array();
		try{
			$stateId= $this->getRequest()->getParam('stateId');
			//var_dump($stateId);
			$resource  =  Mage::getSingleton('core/resource');
			$readConnection =  $resource->getConnection('core_read');
			$tableName   = $resource->getTableName('directory_city');
			$query = "SELECT * FROM {$tableName} WHERE region_id ='{$stateId}'";
			$results =  $readConnection->fetchAll($query);
			
			if(count($results) > 0){
				foreach ($results as $key => $value) {
					array_push($cities,$value['name']);
				}
			}
		}catch(Exception $e) {
			//do nothing
		}
		
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($cities));
	}
}