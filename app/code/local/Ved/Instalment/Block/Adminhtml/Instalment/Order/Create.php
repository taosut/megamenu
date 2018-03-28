<?php

/**
 * Created by PhpStorm.
 * User: buivandung
 * Date: 25/10/2017
 * Time: 11:30
 */
class Ved_Instalment_Block_Adminhtml_Instalment_Order_Create extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_instalment_order';
        $this->_blockGroup = 'ved_instalment';
        $this->_mode = 'create';
        parent::__construct();
        $this->_removeButton('save');
        $this->_addButton('delete', [
            'label' => 'Hủy đơn trả góp',
            'class' => 'delete',
            'onclick' => 'deleteConfirm(\'Bạn có chắc chắn chuyển đơn trả góp sang đơn thường?\', \'' . $this->getDeleteUrl() . '\')'
        ]);
        $this->_addButton('add_new', array(
            'label' => 'Xác nhận',
            'onclick' => 'submitEditForm()',
            'class' => 'save'
        ));
    }

    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return string
     */
    public function getHeaderText()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);
        $incrementId = $order->getIncrementId();

        return Mage::helper('sales')->__('Cập nhật trạng thái trả góp %s', $incrementId);
    }

    /**
     * Back button url getter
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl(
            '*/sales_order/view',
            array('order_id' => $this->getRequest()->getParam('order_id'))
        );
    }

    public function getDeleteUrl()
    {
        return $this->getUrl(
            '*/instalment_order/cancel',
            array('order_id' => $this->getRequest()->getParam('order_id'))
        );
    }
}