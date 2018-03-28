<?php
error_reporting(E_ALL);
ini_set("display_errors", 0);
require_once 'Mage/Adminhtml/controllers/Sales/Order/ShipmentController.php';

class Ved_Vandon_Adminhtml_Sales_Order_ShipmentController extends Mage_Adminhtml_Sales_Order_ShipmentController {


    public function saveAction() {

        $shippingModel = Mage::getModel('vandon/shipping'); 
        $order_detail = $shippingModel->order_detail($this->getRequest()->getParam('order_id'));

        $order_detail['shipping_method_id'] = '';       
        $order_detail['cod_fee']            = 0;       
        $order_detail['cod_fee_payer']      = 0;       
        $order_detail['delivery_fee_payer'] = 0;       
        $order_detail['payment_method']     = 0;       
        $order_detail['source']             = 'ZOMART';                            
        $order_detail['recipient_email']      = 'recipient@gmail.com.vn'; 
                     
        $send_to_vandon = $shippingModel->call_api('create', $order_detail);
        
        $data = $this->getRequest()->getPost('shipment');  
        if (!empty($data['comment_text'])) {
            Mage::getSingleton('adminhtml/session')->setCommentText($data['comment_text']);
        }

        try {
            $shipment = $this->_initShipment();
            if (!$shipment) {
                $this->_forward('noRoute');
                return;
            }

            $shipment->register();
            $comment = '';
            if (!empty($data['comment_text'])) {
                $shipment->addComment(
                    $data['comment_text'],
                    isset($data['comment_customer_notify']),
                    isset($data['is_visible_on_front'])
                );
                if (isset($data['comment_customer_notify'])) {
                    $comment = $data['comment_text'];
                }
            }

            if (!empty($data['send_email'])) {
                $shipment->setEmailSent(true);
            }

            $shipment->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
            $responseAjax = new Varien_Object();
            $isNeedCreateLabel = isset($data['create_shipping_label']) && $data['create_shipping_label'];

            if ($isNeedCreateLabel && $this->_createShippingLabel($shipment)) {
                $responseAjax->setOk(true);
            }

            //if call vandon system ok
            if($send_to_vandon['result'] == 'Success'){
                //$this->_saveShipment($shipment);
                $shipment->sendEmail(!empty($data['send_email']), $comment);

                $shipmentCreatedMessage = $this->__('The shipment has been created.');
                $labelCreatedMessage    = $this->__('The shipping label has been created.');

                $this->_getSession()->addSuccess($isNeedCreateLabel ? $shipmentCreatedMessage . ' ' . $labelCreatedMessage
                : $shipmentCreatedMessage); 
            }
            
            Mage::getSingleton('adminhtml/session')->getCommentText(true);
        } catch (Mage_Core_Exception $e) {
            if ($isNeedCreateLabel) {
                $responseAjax->setError(true);
                $responseAjax->setMessage($e->getMessage());
            } else {
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect('*/*/new', array('order_id' => $this->getRequest()->getParam('order_id')));
            }
        } catch (Exception $e) {
            Mage::logException($e);
            if ($isNeedCreateLabel) {
                $responseAjax->setError(true);
                $responseAjax->setMessage(
                    Mage::helper('sales')->__('An error occurred while creating shipping label.'));
            } else {
                $this->_getSession()->addError($this->__('Cannot save shipment.'));
                $this->_redirect('*/*/new', array('order_id' => $this->getRequest()->getParam('order_id')));
            }

        }
        if ($isNeedCreateLabel) {
            $this->getResponse()->setBody($responseAjax->toJson());
        } else {
            //$this->_redirect('*/sales_order/view', array('order_id' => $shipment->getOrderId()));
            if($send_to_vandon['result'] == 'Success'){
                $pl = base64_encode($send_to_vandon['pl']);
                $this->_redirect('*/sales_order_shipment/confirm', array('order_id' => $shipment->getOrderId(), 'pl' => $pl));
            }else{
                $this->_getSession()->addError($this->__('Cannot save shipment: Call WS to create vandon failed | '.$send_to_vandon['extra']));  
                $this->_redirect('*/sales_order/view', array('order_id' => $shipment->getOrderId()));
            }                 
        }
        Mage::log("Save");
    }

    public function confirmAction(){
        $this->loadLayout();
        $this->renderLayout();
    }
    
    function sendconfirmAction(){
        die('Dang cho API tu DUOC');
        $order_id = $this->getRequest()->getParam('order_id');
        $pl       = base64_decode($this->getRequest()->getParam('order_id'));
        
        $data = array();
        $data['reference_id'] =  $order_id;
        $data['confirm']      =  1;    //Dong y
        
        $shippingModel = Mage::getModel('vandon/shipping');   
        $confirm_to_vandon = $shippingModel->call_api('confirm', $data);
        var_dump($confirm_to_vandon);
        die('aa');
        if($confirm_to_vandon['result'] == 'Success'){
            $this->_getSession()->addSuccess($this->__('The shipment has been confirmed to vandon system'));
            $this->_redirect('*/sales_order/view', array('order_id' => $order_id));
        }else{
            $this->_getSession()->addError($this->__('Cannot confirm with vandon system: '.$confirm_to_vandon['extra']));
            $this->_redirect('*/sales_order_shipment/confirm', array('order_id' => $order_id, 'pl' => $pl));   
        } 
    }
}

?>