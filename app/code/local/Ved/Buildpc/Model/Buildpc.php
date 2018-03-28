<?php
class Ved_Buildpc_Model_Buildpc extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('Ved_Buildpc/buildpc');
    }

    public function saveItem($product_id, $category_id, $quantity)
    {
        $detail = Mage::getModel('Ved_Buildpc/detail');
        $detail->setData(array(
            'parent_id' => $this->getId(),
            'product_id' => $product_id,
            'category_id' => $category_id,
            'store_id' => Mage::app()->getStore()->getId(),
            'quantity' => $quantity
        ))->save();
        return $this;
    }

    public function getAllItem()
    {
        $details = Mage::getModel('Ved_Buildpc/detail')->getCollection()
            ->addFieldToFilter('parent_id', $this->getId());
        return $details;
    }
}