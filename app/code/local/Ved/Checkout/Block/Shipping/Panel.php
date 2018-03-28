<?php
//namespace Ved\Checkout\Block\Shipping;
//use Mage\Core\Block\Template;

class Ved_Checkout_Block_Shipping_Panel extends Mage_Core_Block_Template
{
    public function getListAddress()
    {
        $addresses = array();
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $default_addressId = $customer->getDefaultShipping();
        $first = true;
        foreach ($customer->getAddresses() as $address) {
            $default = false;
            if (isset($default_addressId)) {
                if ($default_addressId == $address->getId()) {
                    $default = true;
                }
            } else {
                //Get Fist address
                if ($first) {
                    $default = true;
                    $first = false;
                }
            }
            $addresses[] = array(
                'id' => $address->getId(),
                'default' => $default,
                'name' => $address->getName(),
                'full_address' => $address->getStreetFull() . ', ' . ucfirst(mb_strtolower($address->getCity(), 'UTF-8')) . ', ' . $address->getRegion(),
                'country' => $address->getCountry(),
                'phone' => $address->getTelephone()
            );
        }
        return $addresses;
    }

    private function swap($cities, $i, $j)
    {
        $tmp = $cities[$i];
        $cities[$i] = $cities[$j];
        $cities[$j] = $tmp;
    }

    public function getListCities()
    {
        $cities = array();
        $collection = Mage::getModel('directory/region')->getResourceCollection()->addCountryFilter('VN')->load();
        if (count($collection) > 0) {
            foreach ($collection as $item) {
                $data = $item->getData();
                switch ($data['region_id']) {
                    case 818: // Đà Nẵng
                        $cities = array_slice($cities, 0, 2, true) +
                            array($data['region_id'] => $data['default_name']) +
                            array_slice($cities, 2, count($cities) - 2, true);
                        break;
                    case 827: // Hà Nội
                        $cities = array($data['region_id'] => $data['default_name']) + $cities;
                        break;
                    case 832: // Tp.Hồ Chí Minh
                        $cities = array_slice($cities, 0, 1, true) +
                            array($data['region_id'] => $data['default_name']) +
                            array_slice($cities, 1, count($cities) - 1, true);
                        break;
                    default:
                        $cities = $cities + array($data['region_id'] => $data['default_name']);
                        break;
                }
            }
        }
        return $cities;
    }

    public function getListDistricts($regionid)
    {
        $districts = array();
        try {
            $resource = Mage::getSingleton('core/resource');
            $readConnection = $resource->getConnection('core_read');
            $tableName = $resource->getTableName('directory_city');
            $query = "SELECT * FROM {$tableName} WHERE region_id ='{$regionid}'";
            $results = $readConnection->fetchAll($query);

            if (count($results) > 0) {
                foreach ($results as $item) {
                    $districts = $districts + array($item['city_id'] => $item['name']);
                }
            }
        } catch (Exception $e) {
            //do nothing
        }
        return $districts;
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }
}