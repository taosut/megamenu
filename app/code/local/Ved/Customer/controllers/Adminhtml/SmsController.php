<?php
class Ved_Customer_Adminhtml_SmsController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction(){
        $this->_redirect("*/*/history");
    }

    public function newAction(){
        $this->_title($this->__("Managing SMS"));
        $this->loadLayout()
            ->_setActiveMenu("adminhtml/sms")
            ->_addBreadcrumb(
                Mage::helper("adminhtml")->__("SMS  Manager"),
                Mage::helper("adminhtml")->__("SMS Manager")
            );
        //Create new model
        $id = $this->getRequest()->getParam("id");
        $model = Mage::getModel("ved_customer/smsmessage")->load($id);

        $data = Mage::getSingleton("adminhtml/session")->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        Mage::register("current_smsmessage", $model);

//        $this->getLayout()->getBlock('head')->addJs('jquery/jquery-1.12.2.min.js');
//        $this->getLayout()->getBlock('head')->addJs('jquery/no-conflict.js');


        $edit_block = $this->getLayout()->createBlock('ved_customer/sms_edit');
        $tabs_block = $this->getLayout()->createBlock('ved_customer/sms_tabs');
        $this->_addContent($edit_block)
            ->_addLeft($tabs_block);
        $this->renderLayout();
    }
    public function editAction(){
        $id = $this->getRequest()->getParam("id");
        $model = Mage::getModel("ved_customer/smsmessage")->load($id);

        if(isset($model)){
            $model->receiver = str_replace(",",PHP_EOL,$model->receiver);
            //Save to register
            Mage::register("current_smsmessage", $model);

            $this->loadLayout();

//            $this->getLayout()->getBlock('head')->addJs('jquery/jquery-1.12.2.min.js');
//            $this->getLayout()->getBlock('head')->addJs('jquery/no-conflict.js');

            $edit_block = $this->getLayout()->createBlock('ved_customer/sms_edit');
            $tabs_block = $this->getLayout()->createBlock('ved_customer/sms_tabs');
            $this->_addContent($edit_block)->_addLeft($tabs_block);
            $this->renderLayout();
        } else {
            Mage::getSingleton("adminhtml/session")->addError(Mage::helper("ved_customer")->__("Sms message does not exist."));
            $this->_redirect("*/*/");
        }
    }
    public function saveAction(){
        $id =  Mage::app()->getRequest()->getParam('entity_id');
        $receivers = Mage::app()->getRequest()->getParam('receiver');
        $content =  Mage::app()->getRequest()->getParam('content');
        $timetable =  Mage::app()->getRequest()->getParam('timetable');

        $receivers = str_replace("-","",$receivers);
        $receivers = str_replace(array(' ',';',','),PHP_EOL,$receivers);
        $list = array();
        $list = $list + array_map('trim', explode(PHP_EOL,$receivers));
        $list = array_filter($list);

        $user = Mage::getSingleton('admin/session')->getUser();
        if(isset($user)){

            //Load current message if updated

            date_default_timezone_set('Asia/Ho_Chi_Minh');
            if(isset($id)){
                $smslog = Mage::getModel('ved_customer/smsmessage')->load($id);
            }

            if(isset($smslog)){
                if($smslog->status >0){
                    Mage::getSingleton("adminhtml/session")->addError(Mage::helper("ved_customer")->__("Cannot edit sent message model."));
                }else{
                    $smslog->receiver = implode(",",$list);
                    $smslog->content = $content;
                    if(!isset($timetable)){
                        $timetable = date("Y-m-d H:i:s",time()-10);
                    }

                    $smslog->timetable = $timetable;
                    $smslog->created = date("Y-m-d H:i:s",time());
                    $smslog->userid = $user->getId();

                    if(strtotime($timetable) > time()){
                        $smslog->status = 0;
                        $smslog->result = "waiting";
                    }else{
                        $smslog->status = 1;
                        $smslog->result = $this->sendSMS($list,$content);
                    }

                    $smslog->save();
                    Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("ved_customer")->__("Sms message was successfully saved"));
                    Mage::getSingleton("adminhtml/session")->setSmsmessageData(false);  //Clear sesssion
                }

            }else{
                Mage::getSingleton("adminhtml/session")->addError(Mage::helper("ved_customer")->__("Cannot load model."));
            }

            if ($this->getRequest()->getParam("back")) {
                $this->_redirect("*/*/edit", array("id" => $smslog->getId()));
                return;
            }
            $this->_redirect("*/*/history");
        }else{
            Mage::getSingleton("adminhtml/session")->addError(Mage::helper("ved_customer")->__("Please login to send sms."));
            $this->_redirect("*/*/");
        }
    }

    public function sendSMS($list, $content)
    {
        $error = "";
        $count = 0;
        foreach ($list as $phonenumber) {
            if ($this->sendSMSToPhone($phonenumber, $content)) {
                $count++;
            } else {
                $error .= Mage::helper("suppliernew")->__("%s failed. Please check phone number;", $phonenumber);
            }
        }
        if (empty($error)) {
            return Mage::helper("suppliernew")->__("%s sent successfully.", $count);
        }
        return $error;
    }

    public function sendSMSToPhone($phone, $message)
    {
        return Mage::sendSMS($phone, $message);
    }
    public function historyAction(){
        $this->_title($this->__("Managing SMS"));
        $this->loadLayout()
            ->_setActiveMenu("adminhtml/history")
            ->_addBreadcrumb(
                Mage::helper("adminhtml")->__("SMS  Manager"),
                Mage::helper("adminhtml")->__("SMS Manager")
            );
        $grid = $this->getLayout()->createBlock('ved_customer/sms_history');
        $this->_addContent($grid);
        $this->renderLayout();
    }

    public function deleteAction()
    {
        if ($this->getRequest()->getParam("id") > 0) {
            $id  = $this->getRequest()->getParam("id");
            try {
                $model = Mage::getModel("ved_customer/smsmessage")->load($id);
                if(isset($model)){
                    if($model->status == 0){
                        $model->delete();
                        Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("ved_customer")->__("Sms message was successfully deleted"));
                    }else{
                        Mage::getSingleton("adminhtml/session")->addError(Mage::helper("ved_customer")->__("You cannot delete sent messages."));
                    }
                }else{
                    Mage::getSingleton("adminhtml/session")->addError(Mage::helper("ved_customer")->__("Not find sms message to delete."));
                }
                $this->_redirect("*/*/");
            } catch (Exception $e) {
                Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                $this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
            }
        }
        $this->_redirect("*/*/");
    }

    public function testAction(){
        $collection = Mage::getModel("ved_customer/smsmessage")->getCollection();
        $items = $collection->getSelect();
    }
}