<?php

/**
 * Class Ved_Instalment_Model_Order_Instalment
 * @method string getProvider()
 * @method string getDescription()
 * @method  int getInstalmentId()
 */
class Ved_Instalment_Model_Order_Instalment extends Mage_Core_Model_Abstract
{
    public function removeByOrderId($orderId)
    {
        $this->getCollection()
            ->addFieldToFilter('order_id', $orderId)
            ->getFirstItem()
            ->delete();
    }

    protected function _beforeSave()
    {
        if (Mage::getSingleton('customer/session')->getUser())
            $this->setUpdatedBy(Mage::getSingleton('customer/session')->getUser()->getUserId());
        $this->setUpdatedAt(now());
        parent::_beforeSave();
    }

    protected function _construct()
    {
        $this->_init('ved_instalment/order_instalment');
    }

    public function updateOrCreate($data)
    {
        $this->setId(
            $this->getCollection()
                ->addFieldToFilter('order_id', $data['order_id'])
                ->getFirstItem()
                ->getId()
        );
        $this->addData([
            'status' => $data['status']
        ])->save();
    }

    public function getStatusLabel()
    {
        switch ($this->getData('status')):
            case 0:
                return "Chờ xác nhận";
            case 1:
                return "Đã xác nhận";
            case 2:
                return "Đơn hàng bị từ chối";
        endswitch;
    }
}