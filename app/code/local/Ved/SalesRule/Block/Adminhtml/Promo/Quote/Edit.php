<?php
class Ved_SalesRule_Block_Adminhtml_Promo_Quote_Edit extends Mage_Adminhtml_Block_Promo_Quote_Edit
{
    public function __construct()
    {
        parent::__construct();

        $this->_formScripts[] = "
            function ved_hide_all() {
                if ('ved_buyxgety' == $('rule_simple_action').value){
                    $('rule_promo_sku').up().up().show();
                }
                else
                {
                    $('rule_promo_sku').up().up().hide();
                }
            }
            ved_hide_all();
        ";
    }
}

