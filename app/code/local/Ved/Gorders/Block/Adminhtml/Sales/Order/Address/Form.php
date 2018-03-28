<?php

class Ved_Gorders_Block_Adminhtml_Sales_Order_Address_Form extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $cities = Mage::getModel('teko_amp/city')->getCollection()->load();
        foreach ($cities as $city) {
            $jsonData[$city->getData('region_id')][$city->getData('city_id')] = [
                'code' => $city->getData('code'),
                'name' => $city->getData('name')
            ];
        }
        $jsonData = json_encode($jsonData);
        return <<<HTML
        <tr>
            <td class="label">{$element->getLabelHtml()}</td>
            <td class="value">
                <input type="text" name="city" id="city" value="{$element->getValue()}">
                <select id="city_id" name="city_id" class="select required-entry">
                    <option value="">--Chọn quận huyện--</option>
                </select>
            </td>
        </tr>
        <script type="text/javascript">//<![CDATA[
            $('city_id').setAttribute('defaultValue', "{$element->getValue()}");
            new CityUpdater('country_id', 'region_id', 'city', 'city_id', $jsonData);
            //]]>
        </script>
HTML;
    }
}