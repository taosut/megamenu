<?php

class Teko_Amp_Model_Instock_Option extends Mage_Eav_Model_Entity_Attribute_Source_Table
{
    public function getAllOptions()
    {
        return [
            [
                'label' => '--- Chọn ---',
                'value' => ''
            ], [
                'label' => 'Còn hàng',
                'value' => '1'
            ], [
                'label' => 'Hàng trưng bày',
                'value' => '2'
            ], [
                'label' => 'Hết hàng',
                'value' => '3'
            ], [
                'label' => 'Hàng sắp về',
                'value' => '4'
            ], [
                'label' => 'Ngừng kinh doanh',
                'value' => '5'
            ], [
                'label' => 'Xả kho',
                'value' => '6'
            ]
        ];
    }
}