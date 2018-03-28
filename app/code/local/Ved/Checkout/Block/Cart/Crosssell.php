<?php

class Ved_Checkout_Block_Cart_Crosssell extends Mage_Checkout_Block_Cart_Crosssell
{
    protected $_maxItemCount = 9;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('checkout/cart/crosssell.phtml');
    }

    public function setMaxItems($value = 9)
    {
        $this->_maxItemCount = $value;
    }
}
