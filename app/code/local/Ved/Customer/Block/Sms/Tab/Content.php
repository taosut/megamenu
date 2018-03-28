<?php

/**
 * Created by PhpStorm.
 * User: binh
 * Date: 4/22/2016
 * Time: 5:58 AM
 */
class Ved_Customer_Block_Sms_Tab_Content extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _construct()
    {
        parent::_construct();
        $form = new Varien_Data_Form();
        $this->setForm($form);
        return $this;
    }

    protected function _prepareForm()
    {
        //Load save value
        $data = null;
        if (Mage::getSingleton("adminhtml/session")->getSmsmessageData()) {
            $data = Mage::getSingleton("adminhtml/session")->getSmsmessageData(); //Get data from session
            Mage::getSingleton("adminhtml/session")->getSmsmessageData(null); //Clear session
        } elseif (Mage::registry("current_smsmessage")) {
            $data = Mage::registry("current_smsmessage")->getData();  //Get data from registry
        }


        $fieldset = $this->getForm()->addFieldset('sms_content',
            array('legend' => Mage::helper('ved_customer')->__('Sms content')));


        $fieldset->addField('entity_id', 'hidden', array(
            'label' => Mage::helper('ved_customer')->__('Id'),
            'required' => false,
            'name' => 'entity_id',
        ));

        $fieldset->addField('content', 'textarea', array(
            'label' => Mage::helper('ved_customer')->__('Sms content'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'content',
        ))->setAfterElementHtml('
                    <small id="content_words_count"></small>
                    <small id="content_comment"></small>
                    <script type=\"text/javascript\">
                           $j("#content").on("keydown", function(e) {
                                alert(this.value);
                                var words = $.trim(this.value).length ? this.value.match(/\S+/g).length : 0;
                                if (words <= 4) {
                                    $("#content_words_count").text("Word:" + words);
                                }else{
                                    $("#content_comment").text("Tổng số từ đã vượt quá 200");
                                    if (e.which !== 8) e.preventDefault();
                                }
                            });
                    </script>');

        if ($data) {
            $this->getForm()->setValues($data);
        }
        return parent::_prepareForm();
    }
}