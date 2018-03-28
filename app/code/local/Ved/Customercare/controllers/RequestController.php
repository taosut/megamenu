<?php

class Ved_Customercare_RequestController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('content')->append($this->getLayout()->createBlock('customercare/request'));
        $this->renderLayout();
    }

    public function saveAction()
    {
        // check if data sent
        if ($data = $this->getRequest()->getPost() && $this->validateData()) {
            // init model and set data
            $model = Mage::getModel('tv_faq/question');
            $model->setData($this->getRequest()->getPost());
            // try to save it
            try {
                // save the data
                $model->save();
                // display success message
                Mage::getSingleton('core/session')->setData('form', array());
                Mage::getSingleton('core/session')->addSuccess(Mage::helper('tv_faq')->__('Câu hỏi đã lưu thành công'));
                $this->_redirect('*/');
                return;
            } catch (Exception $e) {
                // display error message
                Mage::getSingleton('adminhtml/session')->addException($e, $e->getMessage());
                // save data in session
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                // redirect to edit form
                $this->_redirect('*/*/');
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    private function validateData()
    {
        $rules = array(
            'phone' => array(
                'rule' => 'NotEmpty',
                'message' => 'Bạn phải nhập số điện thoại'
            ),
            'title' => array(
                'rule' => 'NotEmpty',
                'message' => 'Trường này không được để trống'
            ),
            'type' => array(
                'rule' => 'NotEmpty',
                'message' => 'Trường này không được để trống'
            ),
        );
        $validate = Mage::helper('tv_faq')->validateRequest($rules, $this->getRequest());
        Mage::getSingleton('core/session')->addData(array('form' => $validate));
        foreach ($validate as $value) {
            if (isset($value['error']) && $value['error'])
                return false;
        }
        return true;
    }
}

?>