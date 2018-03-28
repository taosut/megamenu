<?php

/**
 * Created by PhpStorm.
 * User: Linh
 * Date: 3/13/2017
 * Time: 5:00 PM
 */
class Ved_Customercare_IndexController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('content')->append($this->getLayout()->createBlock('customercare/customercare'));
        $this->renderLayout();
    }

    public function listAction()
    {
        $request = $this->getRequest();
        if ($request->getParam('query')) {
            
        }
        $question_cat_id = $this->getRequest()->getParam('question_cat_id');
        $block_question_list = $this->getLayout()->createBlock('customercare/list')->setData('question_cat_id', $question_cat_id);
        $result = array('question_list_html' => $block_question_list->toHtml());
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
}

?>