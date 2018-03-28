<?php

class Ved_Tracking_Block_Order extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->_headerText = Mage::helper('catalog')->__('Order Tracking');
        $this->setTemplate('tracking/order/index.phtml');
    }

    public function getStateName($state_code, $status_code) {
        switch ($state_code) {
            case 'new':
                return 'Đang xử lý';
                break;
            case 'processing':
                if ($status_code == 'delivered')
                    return 'Hoàn tất';
                else return 'Đang vận chuyển';
                break;
            case 'complete':
                return 'Hoàn tất';
                break;
            case 'canceled':
                return 'Đã hủy';
                break;
            default:
                return '';
                break;
        }
    }

}